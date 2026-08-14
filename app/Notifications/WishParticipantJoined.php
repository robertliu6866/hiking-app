<?php

namespace App\Notifications;

use App\Models\TripWish;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WishParticipantJoined extends Notification
{
    use Queueable;

    public function __construct(
        private readonly TripWish $wish,
        private readonly User $joiningUser,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'line'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'wish_participant_joined',
            'wish_id' => $this->wish->id,
            'joining_user_id' => $this->joiningUser->id,
            'mountain' => $this->wish->mountain,
            'message' => $this->messageFor($notifiable),
            'url' => route('lotteries.yushan'),
        ];
    }

    public function toLine(object $notifiable): array
    {
        return [
            'type' => 'text',
            'text' => $this->messageFor($notifiable),
        ];
    }

    private function messageFor(object $notifiable): string
    {
        if ($notifiable->id === $this->wish->user_id) {
            return "{$this->joiningUser->name} 參加了你的許願團「{$this->wish->mountain}」。";
        }

        return "{$this->joiningUser->name} 參加了你也想去的「{$this->wish->mountain}」許願團。";
    }
}
