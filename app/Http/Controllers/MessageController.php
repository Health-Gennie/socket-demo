<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Fetch the list of users except the authenticated user
    public function getUsers()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        return response()->json($users);
    }

    public function startConversation(Request $request)
    {
        $userId = $request->input('user_id');
        $authUserId = Auth::id();

        // Check if a conversation already exists
        $conversation = Conversation::where(function ($query) use ($authUserId, $userId) {
            $query->where('user_one', $authUserId)
                  ->where('user_two', $userId);
        })->orWhere(function ($query) use ($authUserId, $userId) {
            $query->where('user_one', $userId)
                  ->where('user_two', $authUserId);
        })->first();

        if (!$conversation) {
            // Create a new conversation
            $conversation = Conversation::create([
                'user_one' => $authUserId,
                'user_two' => $userId
            ]);
        }

        return response()->json($conversation);
    }

    public function sendMessage(Request $request)
    {
        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'user_id' => Auth::id(),
            'message' => $request->message
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Message Sent!']);
    }

    public function getMessages($conversationId)
    {
        $conversation = Conversation::with('messages.user')->find($conversationId);

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        return response()->json($conversation->messages);
    }
    public function showChat()
    {
        return view('chat');
    }
}
