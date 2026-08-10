<?php

namespace App\Notifications;

use App\Models\Trip;
use App\Models\TripWish;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FollowedUserActivityCreated extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $actor,
        private readonly Trip|TripWish $activity,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        if ($this->activity instanceof Trip) {
            return [
                'type' => 'followed_user_trip_created',
                'actor_id' => $this->actor->id,
                'actor_name' => $this->actor->name,
                'trip_id' => $this->activity->id,
                'title' => $this->activity->title,
                'mountain' => $this->activity->mountain,
                'url' => route('trips.show', $this->activity),
                'message' => $this->actor->name.' 發佈了新行程，邀請你一起參加。',
            ];
        }

        return [
            'type' => 'followed_user_wish_created',
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'wish_id' => $this->activity->id,
            'mountain' => $this->activity->mountain,
            'url' => route('trips.index'),
            'message' => $this->actor->name.' 發佈了新許願，邀請你一起響應。',
        ];
    }
}
