<?php

namespace App\Http\Controllers\Dashboard\Expert;

use App\Http\Controllers\Controller;
use App\Models\LegalQaPair;
use App\Models\LegalRecord;
use App\Models\LegalCitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LegalTaskController extends Controller
{
    /**
     * Display the current legal QA pair for review.
     * Transitioned from LegalTask to LegalQaPair (Structured Data).
     */
    public function index(Request $request)
    {
        $expert = Auth::user();

        // 1. Look for a QA pair currently being processed by this expert
        $currentQA = LegalQaPair::where('reviewer_id', $expert->id)
            ->where('review_status', 'Processing')
            ->with(['record', 'citations.article'])
            ->first();

        // 2. If no active task, fetch a new Pending one
        if (!$currentQA) {
            $currentQA = LegalQaPair::where('review_status', 'Pending')
                ->whereNull('reviewer_id')
                ->with(['record', 'citations.article'])
                ->first();

            if ($currentQA) {
                $currentQA->update([
                    'reviewer_id' => $expert->id,
                    'review_status' => 'Processing',
                ]);
            }
        }

        // 3. Data mapping for the view (keeping the variable names compatible if possible)
        $task = null;
        $mentionedArticles = collect();

        if ($currentQA) {
            // Map LegalQaPair + LegalRecord to a virtual "Task" object for the Blade view
            $task = (object) [
                'id'              => $currentQA->id,
                'qa_id'           => $currentQA->qa_id,
                'question'        => $currentQA->question,
                'proposed_answer' => $currentQA->generated_answer,
                'case_text'       => $currentQA->record->full_text ?? '',
                'case_reference'  => $currentQA->record->source_reference ?? 'مرجع قضائي',
                'tags'            => $currentQA->record->tags ?? [],
                'sub_domain'      => $currentQA->record->sub_domain ?? 'قانون عام',
                // Keep these for compatibility even if empty, as we now use citations table
                'law_system_name'   => '', 
                'law_article_number'=> '',
            ];

            // Get citations from the new relational table (specifically for this QA pair)
            $citations = $currentQA->citations->count() > 0 
                ? $currentQA->citations 
                : $currentQA->record->citations;

            $mentionedArticles = $citations->map(function($c) {
                if ($c->article) return $c->article;
                
                // Fallback for unlinked citations: Create a virtual object
                return (object) [
                    'id' => 'temp-' . $c->id,
                    'legislation_title' => $c->system_name,
                    'article_title' => $c->article_number ? "المادة {$c->article_number}" : '',
                    'content' => 'نص المادة غير متوفر في قاعدة البيانات الحالية. المرجع: ' . $c->system_name
                ];
            })->unique(fn($a) => is_object($a) ? ($a->id ?? $a->legislation_title) : $a);
        }

        $stats = $this->getExpertStats($expert);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'task' => $task,
                'mentioned_articles' => $mentionedArticles,
                'stats' => $stats
            ]);
        }

        return view('dashboard.expert.legal_workbench', [
            'task' => $task,
            'mentioned_articles' => $mentionedArticles,
            'stats' => $stats,
            'earnings_today' => $stats['earnings_today'] ?? 0
        ]);
    }

    /**
     * Display all legal records reviewed by this expert.
     */
    public function history(Request $request)
    {
        $expert = Auth::user();

        $reviews = LegalQaPair::where('reviewer_id', $expert->id)
            ->whereIn('review_status', ['Approved', 'Modified', 'Rejected'])
            ->with(['record', 'citations.article'])
            ->orderBy('reviewed_at', 'desc')
            ->paginate(15);

        return view('dashboard.expert.legal_history', compact('reviews'));
    }

    /**
     * Submit human review results (Approved / Modified / Rejected)
     */
    public function submit(Request $request)
    {
        $request->validate([
            'task_id'         => 'required|exists:legal_qa_pairs,id',
            'is_correct'      => 'required|boolean',
            'correct_answer'  => 'required_if:is_correct,false|nullable|string',
            'expert_comment'  => 'nullable|string|max:1000',
            'tags'            => 'nullable|array',
        ]);

        $qa = LegalQaPair::where('id', $request->task_id)
            ->where('reviewer_id', Auth::id())
            ->firstOrFail();

        $status = $request->is_correct ? 'Approved' : 'Modified';

        $qa->update([
            'review_status'    => $status,
            'corrected_answer' => $request->is_correct ? null : $request->correct_answer,
            'reviewed_at'      => now(),
            'time_spent'       => $request->input('time_spent'),
        ]);

        // Optional: Update record tags if they changed
        if ($request->has('tags')) {
            $qa->record->update(['tags' => $request->tags]);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'تم حفظ المراجعة بنجاح، شكراً لك.',
            'next_url' => route('dashboard.expert.legal_workbench')
        ]);
    }

    /**
     * Skip the current task (Return to Pending and find another)
     */
    public function skip(Request $request)
    {
        $expert = Auth::user();
        $currentId = $request->task_id;

        // 1. Return current task to Pending
        $qa = LegalQaPair::where('id', $currentId)
            ->where('reviewer_id', $expert->id)
            ->first();

        if ($qa) {
            $qa->update([
                'review_status' => 'Pending',
                'reviewer_id'   => null
            ]);
        }

        // 2. Try to find the NEXT task (ID > currentId) to avoid showing the same one
        $nextQA = LegalQaPair::where('review_status', 'Pending')
            ->whereNull('reviewer_id')
            ->where('id', '>', $currentId)
            ->first();

        // 3. If no "Next" one, just take any available Pending one that isn't the one we just skipped
        if (!$nextQA) {
            $nextQA = LegalQaPair::where('review_status', 'Pending')
                ->whereNull('reviewer_id')
                ->where('id', '!=', $currentId)
                ->first();
        }

        // 4. Lock the new one for this expert
        if ($nextQA) {
            $nextQA->update([
                'reviewer_id'   => $expert->id,
                'review_status' => 'Processing',
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Previous task navigation (Simplified for QA Pairs)
     */
    public function previous(Request $request)
    {
        $expert = Auth::user();

        // Find last reviewed item
        $lastQA = LegalQaPair::where('reviewer_id', $expert->id)
            ->whereIn('review_status', ['Approved', 'Modified', 'Rejected'])
            ->orderBy('reviewed_at', 'desc')
            ->first();

        if ($lastQA) {
            // Return current processing item to Pending
            LegalQaPair::where('reviewer_id', $expert->id)
                ->where('review_status', 'Processing')
                ->update(['review_status' => 'Pending', 'reviewer_id' => null]);

            // Re-open the last one
            $lastQA->update([
                'review_status' => 'Processing'
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Expert statistics for today
     */
    private function getExpertStats($expert)
    {
        $completedToday = LegalQaPair::where('reviewer_id', $expert->id)
            ->whereIn('review_status', ['Approved', 'Modified', 'Rejected'])
            ->whereDate('reviewed_at', Carbon::today())
            ->count();

        return [
            'completed_today' => $completedToday,
            'earnings_today'  => $completedToday * 2.00, // 2 SAR per question
            'pending_tasks'   => LegalQaPair::where('review_status', 'Pending')
                ->count(),
        ];
    }
}
