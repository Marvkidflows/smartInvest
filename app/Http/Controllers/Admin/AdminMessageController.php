<?php
// LOCATION: app/Http/Controllers/Admin/AdminMessageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMessageController extends Controller
{
    
public function adminIndex(Request $request)
{
    // ── One grouped query for unread counts per investor, instead of N queries ──
    $unreadCounts = Message::where('initiated_by', 'investor')
        ->where('read_by_admin', false)
        ->selectRaw('investor_id, COUNT(*) as cnt')
        ->groupBy('investor_id')
        ->pluck('cnt', 'investor_id');

    // ── One query to get the latest message per investor, instead of N queries ──
    // MySQL-safe approach: get the max id per investor, then fetch those rows in one go.
    $latestIds = Message::selectRaw('MAX(id) as id')
        ->groupBy('investor_id')
        ->pluck('id');

    $latestMessages = Message::whereIn('id', $latestIds)
        ->get()
        ->keyBy('investor_id');

    $totalUnread = Message::where('initiated_by', 'investor')
        ->where('read_by_admin', false)
        ->count();

    $investors = User::where('role', 'investor')
        ->latest()
        ->get()
        ->map(function ($investor) use ($unreadCounts, $latestMessages) {
            $lastMessage = $latestMessages->get($investor->id);

            return [
                'investor' => [
                    'id'     => $investor->id,
                    'name'   => $investor->name ?? $investor->full_name,
                    'email'  => $investor->email,
                    'status' => $investor->status ?? 'active',
                ],
                'last_message' => $lastMessage ? [
                    'body'       => substr($lastMessage->body, 0, 80) . (strlen($lastMessage->body) > 80 ? '…' : ''),
                    'from'       => $lastMessage->initiated_by,
                    'created_at' => $lastMessage->created_at->diffForHumans(),
                ] : null,
                'unread_count' => $unreadCounts->get($investor->id, 0),
                'has_messages' => !is_null($lastMessage),
            ];
        });

    if ($request->expectsJson()) {
        return response()->json([
            'investors'    => $investors,
            'total_unread' => $totalUnread,
        ]);
    }

    return view('admin.messages.index', compact('investors', 'totalUnread'));
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
