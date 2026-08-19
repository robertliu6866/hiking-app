<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function complete(Request $request): Response
    {
        $request->user()->forceFill([
            'onboarding_seen_at' => now(),
        ])->save();

        return response()->noContent();
    }
}
