<?php

namespace App\Http\Controllers\Promoter;

use App\Http\Controllers\Controller;
use App\Models\PromoterAssignment;
use App\Models\PromoterCheckin;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PromoterCheckinController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $assignment = PromoterAssignment::where('user_id', $user->id)->first();
        if (!$assignment) {
            return back()->withErrors([
                'checkin' => 'No assignment found. Contact management for scheduling.',
            ]);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('checkins', 'public');
        }

        PromoterCheckin::create([
            'user_id' => $user->id,
            'location_id' => $assignment->location_id,
            'check_in_at' => Carbon::now(),
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'image_path' => $imagePath,
            'status' => 'checked_in',
        ]);

        return back()->with('status', 'Check-in recorded.');
    }
}
