<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LegalTask;
use App\Models\AiResponse;
use Illuminate\Http\Request;

class AdminExpertController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'expert');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('expert_domain', 'like', "%{$search}%")
                  ->orWhere('expert_specialization', 'like', "%{$search}%");
            });
        }

        if ($request->filled('domain')) {
            $query->where('expert_domain', $request->domain);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        if ($request->filled('hire')) {
            $query->where('is_active_for_hire', $request->hire === 'yes' ? 1 : 0);
        }

        $experts = $query->withCount([
            'legalTasks' => function ($q) {
                $q->where('status', 'completed');
            },
            'aiResponses',
            'linguisticTasks',
            'legalQaPairs'
        ])->orderBy('rating_average', 'desc')->paginate(20);

        $totalExperts     = User::where('role', 'expert')->count();
        $activeExperts    = User::where('role', 'expert')->where('is_active', 1)->count();
        $forHireExperts   = User::where('role', 'expert')->where('is_active_for_hire', 1)->count();
        $domains          = User::where('role', 'expert')->whereNotNull('expert_domain')
                                ->distinct()->pluck('expert_domain');

        return view('admin.experts.index', compact(
            'experts', 'totalExperts', 'activeExperts', 'forHireExperts', 'domains'
        ));
    }

    /**
     * عرض تفاصيل تدقيق الخبير
     */
    public function tasks($id)
    {
        $expert = User::where('role', 'expert')->findOrFail($id);

        $legalTasks = LegalTask::where('expert_id', $id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->paginate(15, ['*'], 'legal_page');

        $aiResponses = AiResponse::where('expert_id', $id)
            ->with('task')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'ai_page');

        $legalQaPairs = \App\Models\LegalQaPair::where('reviewer_id', $id)
            ->whereIn('review_status', ['Approved', 'Modified', 'Rejected'])
            ->with(['record'])
            ->orderBy('reviewed_at', 'desc')
            ->paginate(15, ['*'], 'qa_page');

        return view('admin.experts.tasks', compact('expert', 'legalTasks', 'aiResponses', 'legalQaPairs'));
    }

    public function toggleStatus($id)
    {
        $expert = User::where('role', 'expert')->findOrFail($id);
        $expert->is_active = !$expert->is_active;
        $expert->save();

        $msg = $expert->is_active ? 'Expert activated successfully.' : 'Expert suspended successfully.';
        return back()->with('success', $msg);
    }

    public function destroy($id)
    {
        $expert = User::where('role', 'expert')->findOrFail($id);
        $expert->delete();
        return back()->with('success', 'Expert permanently deleted.');
    }
}
