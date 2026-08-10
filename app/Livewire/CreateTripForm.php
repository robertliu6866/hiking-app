<?php

namespace App\Livewire;

use App\Actions\CreateTripFromInput;
use Illuminate\View\View;
use Livewire\Component;

class CreateTripForm extends Component
{
    public string $title = '';
    public string $mountain = '';
    public string $category = '';
    public string $route_mode = 'single';
    public int $difficulty = 2;
    public string $distance_km = '';
    public string $elevation_gain_m = '';
    public string $estimated_hours = '';
    public string $location = '';
    public string $departure_time = '';
    public string $meeting_point = '';
    public int $price = 0;
    public int $quota = 1;
    public string $description = '';
    public string $start_point = '';
    public string $end_point = '';
    public string $waypoints = '';
    public string $trailhead = '';
    public string $summit = '';
    public string $turnaround_time = '';
    public string $plan_note = '';
    public string $suitable_for = '';
    public string $transport_plan = 'carpool';
    public string $carpool_origin = '';
    public string $carpool_seats = '';
    public string $carpool_cost = '';
    public string $equipment = "雨衣\n頭燈\n保暖層\n2L 水\n行動糧\n登山鞋";
    public string $safety_note = '';
    public string $cancellation_policy = '';

    private const ROUTE_MODES = ['traverse', 'single', 'custom'];

    public function mount(array $prefill = []): void
    {
        $mountain = trim((string) ($prefill['mountain'] ?? ''));

        if ($mountain !== '') {
            $this->mountain = $mountain;
            $this->title = $mountain.'開團';
            $this->summit = $mountain;
        }

        $wishedDate = trim((string) ($prefill['wished_date'] ?? ''));

        if ($wishedDate !== '') {
            $this->departure_time = $wishedDate.'T06:00';
        }

        $routeMode = trim((string) ($prefill['route_mode'] ?? ''));

        if (in_array($routeMode, self::ROUTE_MODES, true)) {
            $this->route_mode = $routeMode;
        }

        $note = trim((string) ($prefill['note'] ?? ''));

        if ($note !== '') {
            $this->description = $note;
        }
    }

    public function save(CreateTripFromInput $createTrip)
    {
        abort_unless(auth()->user()->is_admin, 403);

        $validated = $this->validate(CreateTripFromInput::rules());
        $trip = $createTrip->handle(auth()->user(), $validated);

        session()->flash('status', 'trip-created');

        return redirect()->route('trips.show', $trip);
    }

    public function setRouteMode(string $mode): void
    {
        if (! in_array($mode, self::ROUTE_MODES, true)) {
            return;
        }

        $this->route_mode = $mode;
    }

    public function render(): View
    {
        return view('livewire.create-trip-form');
    }

    private function resetForm(): void
    {
        $this->reset([
            'title',
            'mountain',
            'category',
            'distance_km',
            'elevation_gain_m',
            'estimated_hours',
            'location',
            'departure_time',
            'meeting_point',
            'description',
            'start_point',
            'end_point',
            'waypoints',
            'trailhead',
            'summit',
            'turnaround_time',
            'plan_note',
            'suitable_for',
            'transport_plan',
            'carpool_origin',
            'carpool_seats',
            'carpool_cost',
            'safety_note',
            'cancellation_policy',
        ]);

        $this->route_mode = 'single';
        $this->difficulty = 2;
        $this->price = 0;
        $this->quota = 1;
        $this->transport_plan = 'carpool';
        $this->equipment = "雨衣\n頭燈\n保暖層\n2L 水\n行動糧\n登山鞋";
    }
}
