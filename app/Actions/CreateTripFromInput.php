<?php

namespace App\Actions;

use App\Models\Trip;
use App\Models\User;

class CreateTripFromInput
{
    public static function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'mountain' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'route_mode' => 'required|in:traverse,single,custom',
            'difficulty' => 'required|integer|min:1|max:5',
            'distance_km' => 'nullable|numeric|min:0|max:999.99',
            'elevation_gain_m' => 'nullable|integer|min:0|max:99999',
            'estimated_hours' => 'nullable|numeric|min:0|max:999.99',
            'location' => 'nullable|string|max:255',
            'departure_time' => 'nullable|date',
            'meeting_point' => 'nullable|string|max:255',
            'price' => 'required|integer|min:0',
            'quota' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'start_point' => 'nullable|string|max:255',
            'end_point' => 'nullable|string|max:255',
            'waypoints' => 'nullable|string|max:1000',
            'trailhead' => 'nullable|string|max:255',
            'summit' => 'nullable|string|max:255',
            'turnaround_time' => 'nullable|string|max:50',
            'plan_note' => 'nullable|string|max:1000',
            'suitable_for' => 'nullable|string|max:500',
            'transport_plan' => 'nullable|in:carpool,self,public_transport,mixed',
            'carpool_origin' => 'nullable|string|max:255',
            'carpool_seats' => 'nullable|integer|min:0|max:99',
            'carpool_cost' => 'nullable|integer|min:0|max:999999',
            'equipment' => 'nullable|string|max:1000',
            'safety_note' => 'nullable|string|max:1000',
            'cancellation_policy' => 'nullable|string|max:1000',
        ];
    }

    public function handle(User $user, array $input): Trip
    {
        $trip = Trip::create([
            'user_id' => $user->id,
            'title' => $input['title'],
            'mountain' => $input['mountain'] ?? '',
            'category' => $input['category'] ?? '',
            'route_mode' => $input['route_mode'],
            'difficulty' => $input['difficulty'],
            'distance_km' => $this->filled($input, 'distance_km') ? $input['distance_km'] : null,
            'elevation_gain_m' => $this->filled($input, 'elevation_gain_m') ? $input['elevation_gain_m'] : null,
            'estimated_hours' => $this->filled($input, 'estimated_hours') ? $input['estimated_hours'] : null,
            'route_details' => $this->routeDetails($input),
            'location' => $input['location'] ?? '',
            'departure_time' => $input['departure_time'] ?? null,
            'meeting_point' => $input['meeting_point'] ?? '',
            'price' => $input['price'],
            'quota' => $input['quota'],
            'description' => $input['description'] ?? '',
            'status' => 'open',
        ]);

        app(NotifyFollowersAboutActivity::class)->handle($user, $trip);

        return $trip;
    }

    private function routeDetails(array $input): array
    {
        $modeDetails = match ($input['route_mode']) {
            'traverse' => [
                'start_point' => $input['start_point'] ?? '',
                'end_point' => $input['end_point'] ?? '',
                'waypoints' => $this->lines($input['waypoints'] ?? ''),
            ],
            'single' => [
                'trailhead' => $input['trailhead'] ?? '',
                'summit' => $input['summit'] ?? '',
                'turnaround_time' => $input['turnaround_time'] ?? '',
            ],
            default => [
                'plan_note' => $input['plan_note'] ?? '',
            ],
        };

        return array_merge($modeDetails, [
            'suitable_for' => $input['suitable_for'] ?? '',
            'transport_plan' => $input['transport_plan'] ?? 'carpool',
            'carpool_origin' => $input['carpool_origin'] ?? '',
            'carpool_seats' => $this->filled($input, 'carpool_seats') ? (int) $input['carpool_seats'] : null,
            'carpool_cost' => $this->filled($input, 'carpool_cost') ? (int) $input['carpool_cost'] : null,
            'equipment' => $this->lines($input['equipment'] ?? ''),
            'safety_note' => $input['safety_note'] ?? '',
            'cancellation_policy' => $input['cancellation_policy'] ?? '',
        ]);
    }

    private function lines(string $value): array
    {
        return collect(explode("\n", $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function filled(array $input, string $key): bool
    {
        return array_key_exists($key, $input) && $input[$key] !== '' && $input[$key] !== null;
    }
}
