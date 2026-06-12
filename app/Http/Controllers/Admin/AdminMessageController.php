<?php
// LOCATION: app/Http/Controllers/Admin/AdminMessageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMessageController extends Controller
{
    // GET /admin/messages
    public function index(Request $request)
    {
        $messages = Message::with('user:id,name,email')
            ->latest()
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'subject'    => $m->subject ?? 'No Subject',
                'message'    => $m->message ?? $m->body ?? '',
                'is_read'    => (bool) ($m->read ?? $m->is_read ?? false),
                'created_at' => $m->created_at->toDateString(),
                'user' => [
                    'id'    => $m->user->id ?? $m->sender_id ?? null,
                    'name'  => $m->user->name ?? 'Investor',
                    'email' => $m->user->email ?? '',
                ],
            ]);

        if ($request->expectsJson()) {
            return response()->json(['messages' => $messages]);
        }
        return view('admin.messages.index', compact('messages'));
    }

    // GET /admin/messages/{message}
    public function show(Request $request, Message $message)
    {
        $message->load('user');

        // Mark as read
        $message->update(['read' => true, 'is_read' => true]);

        // Get replies if they exist
        $replies = [];
        if (method_exists($message, 'replies')) {
            $replies = $message->replies()->latest()->get()->map(fn($r) => [
                'id'       => $r->id,
                'message'  => $r->message ?? $r->body,
                'is_admin' => (bool) ($r->is_admin ?? ($r->sender_role === 'admin')),
                'created_at' => $r->created_at->toDateString(),
            ])->toArray();
        }

        $data = [
            'message' => [
                'id'         => $message->id,
                'subject'    => $message->subject ?? 'No Subject',
                'message'    => $message->message ?? $message->body ?? '',
                'is_read'    => true,
                'created_at' => $message->created_at->toDateString(),
                'user' => [
                    'id'    => $message->user->id ?? null,
                    'name'  => $message->user->name ?? 'Investor',
                    'email' => $message->user->email ?? '',
                ],
                'replies' => $replies,
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json($data);
        }
        return view('admin.messages.show', compact('message', 'replies'));
    }

    // POST /admin/messages/{message}/reply
    public function reply(Request $request, Message $message)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // Try to store as a reply — depends on your Message model
        if (method_exists($message, 'replies')) {
            $reply = $message->replies()->create([
                'message'     => $request->message,
                'sender_id'   => Auth::id(),
                'is_admin'    => true,
                'sender_role' => 'admin',
            ]);
        } else {
            // Fallback: update the message with reply field
            $message->update([
                'reply'       => $request->message,
                'replied_at'  => now(),
                'replied_by'  => Auth::id(),
            ]);
            $reply = ['message' => $request->message, 'is_admin' => true];
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Reply sent.', 'reply' => $reply]);
        }
        return back()->with('success', 'Reply sent.');
    }

    // DELETE /admin/messages/{message}
    public function destroy(Request $request, Message $message)
    {
        $message->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Message deleted.']);
        }
        return redirect()->route('admin.messages.index')->with('success', 'Message deleted.');
    }
}
