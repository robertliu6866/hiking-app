<?php

namespace App\Notifications\Channels;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Notifications\Notification;

class LineChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $lineUserId = $notifiable->line_user_id;

        if (! $lineUserId) {
            return;
        }

        $message = $notification->toLine($notifiable);

        $this->pushMessage($lineUserId, [$message]);
    }

    private function pushMessage(string $lineUserId, array $messages): void
    {
        $accessToken = config('services.line.channel_access_token');

        if (! $accessToken) {
            return;
        }

        $this->http()
            ->withToken($accessToken)
            ->acceptJson()
            ->post('https://api.line.me/v2/bot/message/push', [
                'to' => $lineUserId,
                'messages' => $messages,
            ]);
    }

    private function http(): PendingRequest
    {
        return new PendingRequest();
    }
}
