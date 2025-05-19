<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\User;
use App\Models\Conversation;

class ChatController extends Controller
{
    public function index()
    {
        $messages = Message::with('user')->latest()->take(50)->get()->reverse()->values();
        return inertia('Chat/Index', ['messages' => $messages]);
    }

    // For group chat (if you want to keep it)
    public function sendGroup(Request $request)
    {
        $request->validate(['message' => 'required|string|max:1000']);
        $message = Message::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);
        broadcast(new MessageSent(auth()->user(), $message))->toOthers();
        return ['status' => 'Message sent!'];
    }

    public function list()
    {
        $users = auth()->user()->is_admin
            ? User::where('is_admin', 0)->get()
            : User::where('is_admin', 1)->get();

        return inertia('Chat/UserList', ['users' => $users]);
    }

    public function show(User $user)
    {
        $conversation = Conversation::firstOrCreate([
            'user_one' => min(auth()->id(), $user->id),
            'user_two' => max(auth()->id(), $user->id),
        ]);

        $messages = $conversation->messages()->with('user')->get();

        return inertia('Chat/Conversation', [
            'conversation' => $conversation,
            'messages' => $messages,
            'otherUser' => $user,
        ]);
    }

    // For private chat
    public function send(Request $request, User $user)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        // Find or create the conversation
        $conversation = Conversation::firstOrCreate([
            'user_one' => min(auth()->id(), $user->id),
            'user_two' => max(auth()->id(), $user->id),
        ]);

        // Insert the message (this is your code)
        $message = $conversation->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        // Optionally broadcast the message
        broadcast(new MessageSent(auth()->user(), $message, $conversation->id))->toOthers();

        return ['status' => 'Message sent!'];
    }
}
