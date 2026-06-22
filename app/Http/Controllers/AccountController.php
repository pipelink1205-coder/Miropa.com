<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user()->load('profile');

        return Inertia::render('Account/Index', [
            'trust' => $user->trustSummary(),
        ]);
    }
}
