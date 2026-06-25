<?php
// LOCATION: app/Http/Controllers/Admin/AdminAnnouncementController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AdminAnnouncementController extends Controller
{
    // All valid announcement types
    const VALID_TYPES = [
        'general',
        'profit_update',
        'investment_opportunity',
        'maintenance',
        'balance_adjustment',
        'info',
        'warning',
        'success',
        'danger',
    ];

    public function index(Request $request)
    {
        $announcements = Announcement::latest()->get()->map(fn($a) => [
            'id'         => $a->id,
            'title'      => $a->title,
            'content'    => $a->content ?? '',
            'message'    => $a->content ?? '',
            'type'       => $a->type ?? 'general',
            'is_popup'   => (bool) $a->is_popup,
            'is_active'  => (bool) $a->is_active,
            'created_at' => $a->created_at->toDateString(),
        ]);

        return response()->json(['announcements' => $announcements]);
    }

    public function create()
    {
        return response()->json(['status' => 'ok']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'content'  => ['required', 'string'],
            'type'     => ['nullable', 'in:' . implode(',', self::VALID_TYPES)],
            'is_popup' => ['nullable', 'boolean'],
            'is_active'=> ['nullable', 'boolean'],
        ]);

        $announcement = Announcement::create([
            'title'      => $validated['title'],
            'content'    => $validated['content'],
            'type'       => $validated['type'] ?? 'general',
            'is_popup'   => $request->boolean('is_popup', false),
            'is_active'  => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message'      => 'Announcement published successfully.',
            'announcement' => [
                'id'         => $announcement->id,
                'title'      => $announcement->title,
                'content'    => $announcement->content,
                'message'    => $announcement->content,
                'type'       => $announcement->type,
                'is_popup'   => (bool) $announcement->is_popup,
                'is_active'  => (bool) $announcement->is_active,
                'created_at' => $announcement->created_at->toDateString(),
            ],
        ], 201);
    }

    public function show(Request $request, Announcement $announcement)
    {
        return response()->json(['announcement' => $announcement]);
    }

    public function edit(Announcement $announcement)
    {
        return response()->json(['announcement' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title'    => ['sometimes', 'string', 'max:255'],
            'content'  => ['sometimes', 'string'],
            'type'     => ['nullable', 'in:' . implode(',', self::VALID_TYPES)],
            'is_popup' => ['nullable', 'boolean'],
            'is_active'=> ['nullable', 'boolean'],
        ]);

        $announcement->update([
            'title'     => $validated['title']   ?? $announcement->title,
            'content'   => $validated['content'] ?? $announcement->content,
            'type'      => $validated['type']    ?? $announcement->type,
            'is_popup'  => $request->has('is_popup')
                            ? $request->boolean('is_popup')
                            : $announcement->is_popup,
            'is_active' => $request->has('is_active')
                            ? $request->boolean('is_active')
                            : $announcement->is_active,
        ]);

        return response()->json([
            'message'      => 'Announcement updated.',
            'announcement' => $announcement->fresh(),
        ]);
    }

    public function destroy(Request $request, Announcement $announcement)
    {
        $announcement->delete();
        return response()->json(['message' => 'Announcement deleted.']);
    }
}