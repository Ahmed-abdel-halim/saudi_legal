<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiMessageFeedback;
use Illuminate\Http\Request;

class AdminAiFeedbackController extends Controller
{
    /**
     * Display a listing of user feedbacks on AI responses.
     */
    public function index(Request $request)
    {
        $query = AiMessageFeedback::with(['user', 'conversation'])
            ->when($request->filled('rating'), function ($q) use ($request) {
                return $q->where('rating', $request->rating);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->search);
                return $q->where(function ($sub) use ($search) {
                    $sub->where('reason', 'LIKE', "%{$search}%")
                        ->orWhere('user_query', 'LIKE', "%{$search}%")
                        ->orWhere('ai_response', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'LIKE', "%{$search}%")
                              ->orWhere('email', 'LIKE', "%{$search}%");
                        });
                });
            });

        $stats = [
            'total_feedback' => AiMessageFeedback::count(),
            'total_likes'    => AiMessageFeedback::where('rating', 'like')->count(),
            'total_dislikes' => AiMessageFeedback::where('rating', 'dislike')->count(),
            'with_reasons'   => AiMessageFeedback::whereNotNull('reason')->where('reason', '!=', '')->count(),
        ];

        $feedbacks = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.ai_feedback.index', compact('feedbacks', 'stats'));
    }

    /**
     * Remove a feedback entry.
     */
    public function destroy($id)
    {
        $feedback = AiMessageFeedback::findOrFail($id);
        $feedback->delete();

        return back()->with('success', 'تم حذف التقييم بنجاح.');
    }
}
