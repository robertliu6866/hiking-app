<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripOrder extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_LINE_PAY_PENDING = 'line_pay_pending';

    public const STATUS_BANK_TRANSFER_PENDING = 'bank_transfer_pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELED = 'canceled';

    public const RESERVED_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_LINE_PAY_PENDING,
        self::STATUS_BANK_TRANSFER_PENDING,
    ];

    protected $fillable = [
        'trip_id',
        'user_id',
        'merchant_order_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'provider_transaction_id',
        'bank_transfer_name',
        'bank_transfer_last_five',
        'paid_at',
        'expires_at',
        'raw_response',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'raw_response' => 'array',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }
}
