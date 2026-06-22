<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(string $username): Response
    {
        $user = User::where('username', $username)
            ->where('status', 'active')
            ->with(['profile.location'])
            ->firstOrFail();

        $listings = $user->listings()
            ->where('status', 'active')
            ->with(['primaryImage', 'category', 'condition', 'location'])
            ->latest('published_at')
            ->paginate(12);

        $reviews = $user->reviewsReceived()
            ->with('reviewer')
            ->latest()
            ->paginate(5);

        return Inertia::render('Profile/Show', [
            'profileUser' => $user,
            'trust' => $user->trustSummary(),
            'listings' => $listings,
            'reviews' => $reviews,
        ]);
    }
}
