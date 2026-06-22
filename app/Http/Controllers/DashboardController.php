<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $user = auth()->user()->load('profile.location');

        $listings = $user->listings()
            ->with(['primaryImage', 'category'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard/Index', [
            'user' => $user,
            'trust' => $user->trustSummary(),
            'listings' => $listings,
        ]);
    }
}
