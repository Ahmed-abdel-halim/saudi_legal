<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct(private ChatService $chatService) {}
    
    /**
     * Get list of conversations for current user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $conversations = $user->conversations()
            ->with(['participant1', 'participant2', 'lastMessage'])
            ->latest('updated_at')
            ->get()
            ->map(function ($conversation) use ($user) {
                $otherParticipant = $conversation->getOtherParticipant($user->id); // Returns ID? No, logic in model returns ID.
                // Wait, model logic: return $this->participant_1 == $userId ? $this->participant_2 : $this->participant_1;
                // But participant_1 is ID or relation? In migration it is ID. Relationships are participantOne/Two.
                
                // Let's get the USER object.
                $otherUser = $conversation->participant_1 == $user->id 
                    ? $conversation->participantTwo 
                    : $conversation->participantOne;
                
                return [
                    'id' => $conversation->id,
                    'participant_name' => $otherUser->name ?? 'Unknown',
                    'participant_avatar' => $otherUser->avatar_path ?? null,
                    'last_message' => $conversation->lastMessage ? $conversation->lastMessage->content : null,
                    'last_message_at' => $conversation->lastMessage ? $conversation->lastMessage->created_at->diffForHumans() : null,
                    'unread_count' => $conversation->unreadCountFor($user->id),
                    'status' => $conversation->status,
                    'contract_type' => $conversation->contract_type,
                ];
            });
            
        return response()->json(['conversations' => $conversations]);
    }
    
    /**
     * Get messages for a conversation
     */
    public function show(Request $request, $id)
    {
        $conversation = Conversation::findOrFail($id);
        
        // Authorize
        if (!$conversation->isParticipant(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Mark as read
        $conversation->markAsReadBy(Auth::id());
        
        $messages = $conversation->messages()
            ->with('sender')
            ->oldest()
            ->paginate(50);
            
        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'contract_type' => $conversation->contract_type,
                'contract_id' => $conversation->contract_id,
            ],
            'messages' => $messages
        ]);
    }
    
    /**
     * Send a message
     */
    public function store(Request $request, $id)
    {
        $request->validate(['content' => 'required|string|max:2000']);
        
        $conversation = Conversation::findOrFail($id);
        
        if (!$conversation->isParticipant(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Check if conversation is active? 
        if ($conversation->status !== Conversation::STATUS_ACTIVE) {
            return response()->json(['error' => 'Conversation is closed'], 403);
        }
        
        $message = $this->chatService->sendMessage(
            $conversation,
            Auth::id(),
            $request->content
        );
        
        return response()->json(['message' => $message]);
    }
}
