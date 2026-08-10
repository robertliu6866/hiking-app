<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'mountain',
        'category',
        'route_mode',
        'difficulty',
        'distance_km',
        'elevation_gain_m',
        'estimated_hours',
        'route_details',
        'location',
        'departure_time',
        'meeting_point',
        'price',
        'quota',
        'description',
        'status',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'distance_km' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'route_details' => 'array',
    ];

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereNull('departure_time')
                ->orWhere('departure_time', '>=', now());
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(TripOrder::class);
    }

    public function pendingOrders(): HasMany
    {
        return $this->hasMany(TripOrder::class)
            ->whereIn('status', [
                TripOrder::STATUS_LINE_PAY_PENDING,
                TripOrder::STATUS_BANK_TRANSFER_PENDING,
            ]);
    }

    public function reservedOrders(): HasMany
    {
        return $this->hasMany(TripOrder::class)
            ->whereIn('status', TripOrder::RESERVED_STATUSES);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(TripRegistration::class);
    }

    public function reservedSeatsCount(): int
    {
        return $this->participants()->count() + $this->reservedOrders()->count();
    }
}
