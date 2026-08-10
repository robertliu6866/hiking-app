<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripOrder;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $upcomingTrips = Trip::query()
            ->upcoming()
            ->withCount('participants')
            ->orderBy('departure_time')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'memberCount' => User::count(),
            'upcomingTripCount' => Trip::query()->upcoming()->count(),
            'registrationCount' => Trip::query()->withCount('participants')->get()->sum('participants_count'),
            'pendingPaymentCount' => TripOrder::query()
                ->whereIn('status', [
                    TripOrder::STATUS_LINE_PAY_PENDING,
                    TripOrder::STATUS_BANK_TRANSFER_PENDING,
                ])
                ->count(),
            'upcomingTrips' => $upcomingTrips,
        ]);
    }
}
