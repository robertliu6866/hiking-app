<?php

namespace App\Http\Controllers;

use App\Actions\CreateTripFromInput;
use App\Actions\ToggleTripJoin;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $trips = Trip::with(['user', 'participants'])
            ->withCount(['participants', 'pendingOrders'])
            ->upcoming()
            ->orderBy('departure_time')
            ->latest()
            ->get();

        $joinedTrips = $request->user()
            ->joinedTrips()
            ->with('user')
            ->withCount(['participants', 'pendingOrders'])
            ->upcoming()
            ->orderBy('departure_time')
            ->get();

        return view('trips.index', [
            'trips' => $trips,
            'joinedTrips' => $joinedTrips,
        ]);
    }

    public function create(Request $request)
    {
        return view('trips.create');
    }

    public function store(Request $request, CreateTripFromInput $createTrip)
    {
        $validated = $request->validate(CreateTripFromInput::rules());
        $trip = $createTrip->handle($request->user(), $validated);

        return redirect()
            ->route('trips.show', $trip)
            ->with('status', 'trip-created');
    }

    public function show(Trip $trip)
    {
        $trip->load(['user', 'participants'])
            ->loadCount(['participants', 'pendingOrders']);

        return view('trips.show', [
            'trip' => $trip,
        ]);
    }

    public function join(Request $request, Trip $trip, ToggleTripJoin $toggleTripJoin)
    {
        $status = $toggleTripJoin->handle($trip, $request->user());

        if ($status === 'full') {
            return back()->withErrors([
                'trip' => '活動名額已滿',
            ]);
        }

        if ($status === 'closed') {
            return back()->withErrors([
                'trip' => '這個活動目前沒有開放報名',
            ]);
        }

        return back()->with('status', $status === 'canceled' ? 'trip-canceled' : 'trip-joined');
    }
}
