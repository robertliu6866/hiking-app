<?php

namespace App\Http\Controllers;

use App\Models\TripWish;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $joinedWishes = TripWish::query()
            ->select('trip_wishes.*')
            ->with([
                'user',
                'users' => fn ($query) => $query->orderBy('trip_wish_user.created_at'),
            ])
            ->withCount('users')
            ->join('trip_wish_user as my_wish_status', 'my_wish_status.trip_wish_id', '=', 'trip_wishes.id')
            ->where('my_wish_status.user_id', $user->id)
            ->where('my_wish_status.status', 'joined')
            ->where(function ($query) {
                $query->whereNull('trip_wishes.wished_date')
                    ->orWhereDate('trip_wishes.wished_date', '>=', now()->toDateString());
            })
            ->orderBy('trip_wishes.wished_date')
            ->orderByDesc('my_wish_status.updated_at')
            ->get();

        return view('dashboard', [
            'joinedWishes' => $joinedWishes,
        ]);
    }
}
