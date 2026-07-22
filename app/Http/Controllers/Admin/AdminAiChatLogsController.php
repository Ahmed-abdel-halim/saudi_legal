<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiTask;
use App\Models\LegalTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAiChatLogsController extends Controller
{
    /**
     * Display listing of all AI conversations and stats.
     */
    public function index(Request $request)
    {
        $query = AiConversation::with(['user', 'messages'])
            ->withCount('messages')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->search);
                return $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('uuid', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'LIKE', "%{$search}%")
                              ->orWhere('email', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('messages', function ($m) use ($search) {
                            $m->where('message', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('user_type'), function ($q) use ($request) {
                if ($request->user_type === 'registered') {
                    return $q->whereNotNull('user_id');
                } elseif ($request->user_type === 'guest') {
                    return $q->whereNull('user_id');
                }
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                return $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                return $q->whereDate('created_at', '<=', $request->date_to);
            });

        $stats = [
            'total_conversations' => AiConversation::count(),
            'total_messages'      => AiMessage::count(),
            'user_conversations'  => AiConversation::whereNotNull('user_id')->count(),
            'guest_conversations' => AiConversation::whereNull('user_id')->count(),
        ];

        $conversations = $query->orderBy('updated_at', 'desc')->paginate(15);

        return view('admin.ai_chats.index', compact('conversations', 'stats'));
    }

    /**
     * Display a specific AI conversation log.
     */
    public function show($uuid)
    {
        $conversation = AiConversation::with(['user', 'messages'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('admin.ai_chats.show', compact('conversation'));
    }

    /**
     * Convert a user message & AI response into an Expert Workbench Task (RLHF).
     */
    public function convertToTask(Request $request, $messageId)
    {
        $userMessage = AiMessage::findOrFail($messageId);

        if ($userMessage->role !== 'user') {
            return back()->with('error', 'يجب إرجاع تحويل سؤال المستخدم فقط.');
        }

        // Get the assistant response following this user message if exists
        $assistantMessage = AiMessage::where('ai_conversation_id', $userMessage->ai_conversation_id)
            ->where('id', '>', $userMessage->id)
            ->where('role', 'assistant')
            ->orderBy('id', 'asc')
            ->first();

        $question = $userMessage->message;
        $proposedAnswer = $assistantMessage ? $assistantMessage->message : '';

        // Find a default client ID for task owner
        $clientId = auth()->id() ?? User::first()?->id;

        DB::beginTransaction();
        try {
            // Create legacy AI Task
            $aiTask = AiTask::create([
                'task_type'          => 'legal_verification',
                'original_data'      => $question,
                'ai_suggestion'      => $proposedAnswer,
                'client_id'          => $clientId,
                'status'             => 'pending',
                'consensus_status'   => 'pending',
                'required_responses' => 2,
                'task_domain'        => 'law',
                'allow_all_roles'    => true,
            ]);

            // Create Legal Task linked for Workbench
            $firstCitation = null;
            if ($assistantMessage && !empty($assistantMessage->citations)) {
                $firstCitation = $assistantMessage->citations[0] ?? null;
            }

            LegalTask::create([
                'task_id'            => $aiTask->id,
                'source_type'        => 'user_chat_log',
                'source_id'          => $userMessage->id,
                'task_type'          => 'verification',
                'status'             => 'pending',
                'question'           => $question,
                'proposed_answer'    => $proposedAnswer,
                'law_system_name'    => $firstCitation['law_system'] ?? 'نظام غير محدد',
                'law_article_number' => $firstCitation['article_number'] ?? 'غير محدد',
                'law_article_text'   => $firstCitation['text'] ?? null,
                'case_text'          => $question,
                'case_reference'     => 'محادثة بوت #' . $userMessage->ai_conversation_id,
                'domain'             => 'law',
                'source_file'        => 'ai_chat_conversations',
            ]);

            DB::commit();

            return back()->with('success', 'تم تحويل استفسار المستخدم بنجاح إلى مهمة تقييم ومراجعة للخبراء في لوحة الـ Workbench!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء تحويل الاستفسار: ' . $e->getMessage());
        }
    }
}
