<?php

namespace App\Notifications;

use App\Models\TripOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BankTransferSubmitted extends Notification
{
    use Queueable;

    public function __construct(private readonly TripOrder $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'bank_transfer_submitted',
            'order_id' => $this->order->id,
            'trip_id' => $this->order->trip_id,
            'trip_title' => $this->order->trip->title,
            'user_id' => $this->order->user_id,
            'user_name' => $this->order->user->name,
            'amount' => $this->order->amount,
            'bank_transfer_name' => $this->order->bank_transfer_name,
            'bank_transfer_last_five' => $this->order->bank_transfer_last_five,
            'message' => $this->order->user->name.' 已送出匯款資訊，等待確認。',
        ];
    }
}
