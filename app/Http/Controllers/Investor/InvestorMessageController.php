<?php
// LOCATION: app/Http/Controllers/Investor/InvestorMessageController.php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestorMessageController extends Controller
{
    // GET /investor-investment/investor/messages
    public function index(Request $request)
    {
        $user     = Auth::user();
        $messages = Message::where('user_id', $user->id)
            ->orWhere('sender_id', $user->id)
            ->latest()
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'subject'    => $m->subject ?? 'No Subject',
                'message'    => $m->message ?? $m->body ?? '',
                'is_read'    => (bool) ($m->read ?? $m->is_read ?? false),
                'created_at' => $m->created_at->toDateString(),
            ]);

        if ($request->expectsJson()) {
            return response()->json(['messages' => $messages]);
        }
        return view('investor.messages.index', compact('messages'));
    }

    // GET /investor-investment/investor/messages/create
    public function create(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }
        return view('investor.messages.create');
    }

    // POST /investor-investment/investor/messages
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $msg = Message::create([
            'user_id'   => $user->id,
            'sender_id' => $user->id,
            'subject'   => $validated['subject'] ?? 'Support Request',
            'message'   => $validated['message'],
            'body'      => $validated['message'],
            'is_read'   => false,
            'read'      => false,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Message sent to support!',
                'data'    => [
                    'id'         => $msg->id,
                    'subject'    => $msg->subject,
                    'message'    => $msg->message,
                    'created_at' => $msg->created_at->toDateString(),
                ],
            ], 201);
        }

        return redirect()->route('investor-investment.messages.index')
            ->with('success', 'Message sent!');
    }

    // GET /investor-investment/investor/messages/{message}
    public function show(Request $request, Message $message)
    {
        $user = Auth::user();

        // Ensure investor owns this message
        if ($message->user_id !== $user->id && $message->sender_id !== $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403);
        }

        // Get replies
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
                'is_read'    => (bool) ($message->read ?? $message->is_read ?? false),
                'created_at' => $message->created_at->toDateString(),
                'replies'    => $replies,
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json($data);
        }
        return view('investor.messages.show', $data);
    }
}
