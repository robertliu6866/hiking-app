<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $trips = Trip::with(['user', 'participants'])
            ->withCount('participants')
            ->orderByDesc('departure_time')
            ->latest()
            ->get();

        return view('admin.trips.index', [
            'trips' => $trips,
        ]);
    }
}
