<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LineBotController extends Controller
{
    public function webhook(Request $request): JsonResponse
    {
        $body = $request->getContent();

        if (! $this->isValidSignature($body, (string) $request->header('X-Line-Signature'))) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        foreach ($request->input('events', []) as $event) {
            $replyToken = $event['replyToken'] ?? null;

            if (! $replyToken) {
                continue;
            }

            if (($event['type'] ?? null) === 'message' && ($event['message']['type'] ?? null) === 'text') {
                $this->handleTextMessage($replyToken, $event);

                continue;
            }

            if (($event['type'] ?? null) === 'postback') {
                $this->handlePostback($replyToken, $event);
            }
        }

        return response()->json(['ok' => true]);
    }

    private function handleTextMessage(string $replyToken, array $event): void
    {
        $text = trim((string) ($event['message']['text'] ?? ''));
        $lineUserId = $event['source']['userId'] ?? null;

        if (! $lineUserId) {
            $this->replyText($replyToken, '目前只支援一對一聊天室操作。');

            return;
        }

        $this->findOrCreateLineUser($lineUserId);

        if (in_array($text, ['許願', '願望', 'wish', 'list'], true)) {
            $this->replyText($replyToken, '每個許願就是一趟想去的行程。先看看大家想去哪座山；登入後可發布自己的願望或 +1 響應：'.url('/'));

            return;
        }

        $this->replyText($replyToken, $this->helpText());
    }

    private function handlePostback(string $replyToken, array $event): void
    {
        $lineUserId = $event['source']['userId'] ?? null;

        if (! $lineUserId) {
            $this->replyText($replyToken, '目前只支援一對一聊天室操作。');

            return;
        }

        $this->replyText($replyToken, $this->helpText());
    }

    private function findOrCreateLineUser(string $lineUserId): User
    {
        return User::firstOrCreate(
            ['line_user_id' => $lineUserId],
            [
                'name' => 'LINE 山友',
                'email' => 'line_'.$lineUserId.'@line.local',
                'password' => Hash::make(Str::random(40)),
            ],
        );
    }

    private function helpText(): string
    {
        return implode("\n", [
            '劉里長登山 LINE Bot',
            '',
            '可輸入：',
            '許願：前往登山許願板',
            '在網站發起願望，或為想同行的願望 +1。',
        ]);
    }

    private function isValidSignature(string $body, string $signature): bool
    {
        $secret = config('services.line.channel_secret');

        if (! $secret || ! $signature) {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $body, $secret, true));

        return hash_equals($expected, $signature);
    }

    private function replyText(string $replyToken, string $text): void
    {
        $this->replyMessages($replyToken, [
            [
                'type' => 'text',
                'text' => Str::limit($text, 4900, ''),
            ],
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     */
    private function replyMessages(string $replyToken, array $messages): void
    {
        $accessToken = config('services.line.channel_access_token');

        if (! $accessToken) {
            return;
        }

        Http::withToken($accessToken)
            ->acceptJson()
            ->post('https://api.line.me/v2/bot/message/reply', [
                'replyToken' => $replyToken,
                'messages' => $messages,
            ]);
    }
}
