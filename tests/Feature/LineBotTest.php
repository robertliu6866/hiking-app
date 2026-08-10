<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LineBotTest extends TestCase
{
    use RefreshDatabase;

    public function test_line_webhook_rejects_invalid_signature(): void
    {
        config()->set('services.line.channel_secret', 'secret');

        $this->call(
            'POST',
            '/api/line/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_LINE_SIGNATURE' => 'bad-signature',
            ],
            json_encode(['events' => []]),
        )->assertForbidden();
    }

    public function test_line_user_can_list_and_join_trips(): void
    {
        config()->set('services.line.channel_secret', 'secret');
        config()->set('services.line.channel_access_token', 'token');
        Http::fake();

        $host = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $host->id,
            'title' => '羊頭山單攻',
            'mountain' => '羊頭山',
            'category' => '單攻',
            'location' => '花蓮',
            'departure_time' => now()->addWeek(),
            'meeting_point' => '台北車站',
            'quota' => 6,
            'status' => 'open',
        ]);

        $this->postLineEvent('活動');

        Http::assertSent(fn ($request) => $request->url() === 'https://api.line.me/v2/bot/message/reply'
            && $request['messages'][0]['type'] === 'flex'
            && $request['messages'][0]['altText'] === '目前開放活動'
            && $request['messages'][0]['contents']['contents'][0]['body']['contents'][0]['text'] === '羊頭山單攻'
            && $request['messages'][0]['contents']['contents'][0]['footer']['contents'][0]['action']['data'] === 'action=join_trip&trip_id='.$trip->id);

        $this->postLinePostback('action=join_trip&trip_id='.$trip->id);

        $lineUser = User::where('line_user_id', 'U123')->firstOrFail();
        $this->assertTrue($trip->participants()->whereKey($lineUser->id)->exists());

        Http::assertSent(fn ($request) => ($request['messages'][0]['type'] ?? null) === 'text'
            && str_contains($request['messages'][0]['text'], '報名成功'));
    }

    private function postLineEvent(string $text): void
    {
        $body = json_encode([
            'events' => [
                [
                    'type' => 'message',
                    'replyToken' => 'reply-token',
                    'source' => [
                        'type' => 'user',
                        'userId' => 'U123',
                    ],
                    'message' => [
                        'type' => 'text',
                        'text' => $text,
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $this->postLinePayload($body);
    }

    private function postLinePostback(string $data): void
    {
        $body = json_encode([
            'events' => [
                [
                    'type' => 'postback',
                    'replyToken' => 'reply-token',
                    'source' => [
                        'type' => 'user',
                        'userId' => 'U123',
                    ],
                    'postback' => [
                        'data' => $data,
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $this->postLinePayload($body);
    }

    private function postLinePayload(string $body): void
    {
        $signature = base64_encode(hash_hmac('sha256', $body, 'secret', true));

        $this->call(
            'POST',
            '/api/line/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_LINE_SIGNATURE' => $signature,
            ],
            $body,
        )->assertOk();
    }
}
