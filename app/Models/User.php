<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Trip;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
protected $fillable = [
    'name',
    'email',
    'password',
    'is_admin',
    'avatar',
    'line_user_id',
    'line_display_name',
    'line_picture_url',
    'phone',
    'age',
    'gender',
    'hiking_experience',
    'preferred_regions',
    'available_days',
    'transport_modes',
    'preferred_route_modes',
    'hiking_styles',
    'preferred_difficulty_min',
    'preferred_difficulty_max',
    'address',
    'blood_type',
    'emergency_contact_name',
    'emergency_contact_phone',
    'bio',
    'free_trial_quota',
    'membership_status',
    'membership_paid_at',
    'membership_expires_at',
    'profile_completed_at',
    'onboarding_seen_at',
];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'preferred_regions' => 'array',
            'available_days' => 'array',
            'transport_modes' => 'array',
            'preferred_route_modes' => 'array',
            'hiking_styles' => 'array',
            'preferred_difficulty_min' => 'integer',
            'preferred_difficulty_max' => 'integer',
            'onboarding_seen_at' => 'datetime',
        ];
    }

public function tripMatchReasons(Trip $trip): array
{
    $reasons = [];

    if ($this->matchesRegion($trip)) {
        $reasons[] = ($trip->location ?: '地區').'符合你的偏好';
    }

    if ($this->matchesDifficulty($trip)) {
        $reasons[] = '難度在你的範圍內';
    }

    if ($this->matchesRouteMode($trip)) {
        $routeLabels = [
            'traverse' => '縱走',
            'single' => '單攻',
            'custom' => '自由規劃',
        ];
        $reasons[] = ($routeLabels[$trip->route_mode] ?? '路線型態').'符合你';
    }

    if ($this->matchesAvailableDay($trip)) {
        $reasons[] = $trip->departure_time?->isWeekend() ? '週末可出發' : '平日可出發';
    }

    if (in_array('public_transport', $this->transport_modes ?? [], true) && filled($trip->meeting_point)) {
        $reasons[] = '集合資訊清楚';
    }

    if (($trip->participants_count ?? $trip->participants()->count()) > 0) {
        $reasons[] = '已有山友同行';
    }

    return array_slice($reasons, 0, 3);
}

public function tripMatchScore(Trip $trip): int
{
    return count($this->tripMatchReasons($trip));
}

public function preferenceCompletionPercent(): int
{
    $filled = collect([
        $this->preferred_regions,
        $this->available_days,
        $this->transport_modes,
        $this->preferred_route_modes,
        $this->hiking_styles,
        $this->preferred_difficulty_min,
        $this->preferred_difficulty_max,
    ])->filter(fn ($value) => filled($value))->count();

    return (int) round(($filled / 7) * 100);
}

private function matchesRegion(Trip $trip): bool
{
    $regions = $this->preferred_regions ?? [];

    if ($regions === [] || blank($trip->location)) {
        return false;
    }

    foreach ($regions as $region) {
        if (str_contains((string) $trip->location, (string) $region)) {
            return true;
        }
    }

    return false;
}

private function matchesDifficulty(Trip $trip): bool
{
    if (! $trip->difficulty || ! $this->preferred_difficulty_min || ! $this->preferred_difficulty_max) {
        return false;
    }

    return $trip->difficulty >= $this->preferred_difficulty_min
        && $trip->difficulty <= $this->preferred_difficulty_max;
}

private function matchesRouteMode(Trip $trip): bool
{
    return filled($trip->route_mode)
        && in_array($trip->route_mode, $this->preferred_route_modes ?? [], true);
}

private function matchesAvailableDay(Trip $trip): bool
{
    if (! $trip->departure_time) {
        return false;
    }

    $days = $this->available_days ?? [];

    return ($trip->departure_time->isWeekend() && in_array('weekend', $days, true))
        || (! $trip->departure_time->isWeekend() && in_array('weekday', $days, true));
}
public function getAvatarUrlAttribute(): string
{
    if ($this->avatar && ! in_array(strtolower(pathinfo($this->avatar, PATHINFO_EXTENSION)), ['heic', 'heif'], true)) {
        return asset('storage/' . $this->avatar);
    }

    return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=EEF2FF&color=4338CA';
}
public function trips(): HasMany
{
    return $this->hasMany(Trip::class);
}

public function joinedTrips(): BelongsToMany
{
    return $this->belongsToMany(Trip::class)
        ->withTimestamps();
}

public function following(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'followed_id')
        ->withTimestamps();
}

public function followers(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'user_follows', 'followed_id', 'follower_id')
        ->withTimestamps();
}

public function tripOrders(): HasMany
{
    return $this->hasMany(TripOrder::class);
}

public function tripRegistrations(): HasMany
{
    return $this->hasMany(TripRegistration::class);
}
}
