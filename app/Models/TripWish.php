<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TripWish extends Model
{
    protected $fillable = [
        'user_id',
        'host_user_id',
        'mountain',
        'wished_date',
        'route_mode',
        'note',
        'homepage_group',
        'guided_days',
        'expected_participants',
    ];

    protected $casts = [
        'wished_date' => 'date',
        'guided_days' => 'integer',
        'expected_participants' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->wherePivot('status', 'joined')
            ->withPivot(['status', 'willing_to_host'])
            ->withTimestamps();
    }

    public function allUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['status', 'willing_to_host'])
            ->withTimestamps();
    }
}
