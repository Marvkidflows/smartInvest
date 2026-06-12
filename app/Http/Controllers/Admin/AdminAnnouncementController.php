<?php
// LOCATION: app/Http/Controllers/Admin/AdminAnnouncementController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AdminAnnouncementController extends Controller
{
    // GET /admin/announcements
    public function index(Request $request)
    {
        $announcements = Announcement::latest()->get()->map(fn($a) => [
            'id'         => $a->id,
            'title'      => $a->title,
            'content'    => $a->content ?? $a->message ?? '',
            'type'       => $a->type ?? 'info',
            'is_active'  => (bool) ($a->is_active ?? true),
            'created_at' => $a->created_at->toDateString(),
        ]);

        return response()->json(['announcements' => $announcements]);
    }

    // GET /admin/announcements/create
    public function create()
    {
        return response()->json(['status' => 'ok']);
    }

    // POST /admin/announcements
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type'    => ['nullable', 'in:info,warning,success,danger'],
        ]);

        $announcement = Announcement::create([
            'title'      => $validated['title'],
            'content'    => $validated['content'],
            'message'    => $validated['content'],
            'type'       => $validated['type'] ?? 'info',
            'is_active'  => true,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message'      => 'Announcement published successfully.',
            'announcement' => [
                'id'        => $announcement->id,
                'title'     => $announcement->title,
                'content'   => $announcement->content,
                'type'      => $announcement->type,
                'is_active' => true,
                'created_at'=> $announcement->created_at->toDateString(),
            ],
        ], 201);
    }

    // GET /admin/announcements/{announcement}
    public function show(Request $request, Announcement $announcement)
    {
        return response()->json(['announcement' => $announcement]);
    }

    // GET /admin/announcements/{announcement}/edit
    public function edit(Announcement $announcement)
    {
        return response()->json(['announcement' => $announcement]);
    }

    // PUT /admin/announcements/{announcement}
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title'     => ['sometimes', 'string', 'max:255'],
            'content'   => ['sometimes', 'string'],
            'type'      => ['nullable', 'in:info,warning,success,danger'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $announcement->update([
            'title'     => $validated['title']     ?? $announcement->title,
            'content'   => $validated['content']   ?? $announcement->content,
            'message'   => $validated['content']   ?? $announcement->message,
            'type'      => $validated['type']       ?? $announcement->type,
            'is_active' => $request->has('is_active')
                           ? $request->boolean('is_active')
                           : $announcement->is_active,
        ]);

        return response()->json([
            'message'      => 'Announcement updated.',
            'announcement' => $announcement->fresh(),
        ]);
    }

    // DELETE /admin/announcements/{announcement}
    public function destroy(Request $request, Announcement $announcement)
    {
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted.']);
    }
}