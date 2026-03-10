<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        
        $conversations = Conversation::where('participant_1', $userId)
            ->orWhere('participant_2', $userId)
            ->with(['lastMessage', 'participant1', 'participant2'])
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->limit(1)
            )
            ->get();

        return view('chat.index', compact('conversations'));
    }

    public function show($id)
    {
        $userId = Auth::id();
        $conversation = Conversation::with(['messages.sender', 'participant1', 'participant2'])
            ->findOrFail($id);

        if ($conversation->participant_1 != $userId && $conversation->participant_2 != $userId) {
            abort(403);
        }

        // Mark messages as read
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('chat.show', compact('conversation'));
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $conversation = Conversation::findOrFail($id);
        $userId = Auth::id();

        if ($conversation->participant_1 != $userId && $conversation->participant_2 != $userId) {
            abort(403);
        }

        $chatService = app(\App\Services\ChatService::class);
        $message = $chatService->sendMessage(
            $conversation,
            $userId,
            $request->content,
            false // receiverIsPresent can be improved later via presence channel, false to ensure notification
        );

        if ($request->wantsJson()) {
            $message->load('sender');
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender ? $message->sender->name : 'System',
                    'sender_avatar' => $message->sender ? $message->sender->avatar_path : null,
                    'created_at' => $message->created_at->toIso8601String(),
                ]
            ]);
        }

        return back();
    }

    public function destroy($id)
    {
        $conversation = Conversation::findOrFail($id);
        $userId = Auth::id();

        // Ensure only participants can delete the chat
        if ($conversation->participant_1 != $userId && $conversation->participant_2 != $userId) {
            abort(403);
        }

        // Delete associated messages first
        Message::where('conversation_id', $conversation->id)->delete();
        
        // Delete the conversation itself
        $conversation->delete();

        return back()->with('success', __('Chat deleted successfully'));
    }
}
