<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TripWish extends Model
{
    protected $fillable = [
        'user_id',
        'mountain',
        'wished_date',
        'route_mode',
        'note',
        'homepage_group',
    ];

    protected $casts = [
        'wished_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->wherePivot('status', 'joined')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function allUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('status')
            ->withTimestamps();
    }
}
