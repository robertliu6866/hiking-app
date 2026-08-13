<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LineBotWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_line_bot_replies_with_the_public_wish_board_for_wish_message(): void
    {
        config([
            'services.line.channel_secret' => 'test-channel-secret',
            'services.line.channel_access_token' => 'test-access-token',
        ]);
        Http::fake();

        $body = json_encode([
            'events' => [[
                'type' => 'message',
                'replyToken' => 'reply-token',
                'source' => ['userId' => 'U-test-user'],
                'message' => ['type' => 'text', 'text' => '許願'],
            ]],
        ], JSON_THROW_ON_ERROR);
        $signature = base64_encode(hash_hmac('sha256', $body, 'test-channel-secret', true));

        $this->call('POST', route('line.webhook'), [], [], [], [
            'HTTP_X_LINE_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertOk();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.line.me/v2/bot/message/reply'
                && str_contains($request['messages'][0]['text'], url('/'));
        });
    }
}
