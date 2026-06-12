<?php
// LOCATION: app/Http/Controllers/Investor/InvestorAnnouncementController.php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class InvestorAnnouncementController extends Controller
{
    // GET /investor-investment/announcements
    public function investorIndex(Request $request)
    {
        $announcements = Announcement::where('is_active', true)
            ->latest()
            ->get()
            ->map(fn($a) => [
                'id'         => $a->id,
                'title'      => $a->title,
                'content'    => $a->content ?? $a->message ?? '',
                'type'       => $a->type ?? 'info',
                'created_at' => $a->created_at->toDateString(),
                'time_ago'   => $a->created_at->diffForHumans(),
            ]);

        return response()->json([
            'announcements' => $announcements,
            'count'         => $announcements->count(),
        ]);
    }
}