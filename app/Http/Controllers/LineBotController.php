<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\User;
use App\Services\LineFlexMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LineBotController extends Controller
{
    public function __construct(private readonly LineFlexMessageService $flexMessages)
    {
    }

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

        $user = $this->findOrCreateLineUser($lineUserId);

        if (in_array($text, ['活動', '活動列表', '找活動', 'trips', 'list'], true)) {
            $this->replyTripList($replyToken);

            return;
        }

        if (in_array($text, ['我的活動', '我的報名', '報名', 'my'], true)) {
            $this->replyText($replyToken, $this->myTripsText($user));

            return;
        }

        if (preg_match('/^(參加|我要參加|join)\s*#?(\d+)$/iu', $text, $matches)) {
            $this->replyText($replyToken, $this->joinTripText($user, (int) $matches[2]));

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

        parse_str((string) ($event['postback']['data'] ?? ''), $data);

        if (($data['action'] ?? null) === 'join_trip' && isset($data['trip_id'])) {
            $user = $this->findOrCreateLineUser($lineUserId);
            $this->replyText($replyToken, $this->joinTripText($user, (int) $data['trip_id']));

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

    private function replyTripList(string $replyToken): void
    {
        $trips = Trip::query()
            ->where('status', 'open')
            ->withCount('participants')
            ->upcoming()
            ->orderBy('departure_time')
            ->take(8)
            ->get();

        if ($trips->isEmpty()) {
            $this->replyText($replyToken, "目前沒有開放報名的活動。\n\n輸入「許願」可以先告訴我們想去哪座山。");

            return;
        }

        $this->replyMessages($replyToken, [
            $this->flexMessages->tripCarousel($trips),
        ]);
    }

    private function myTripsText(User $user): string
    {
        $trips = $user->joinedTrips()
            ->upcoming()
            ->orderBy('departure_time')
            ->take(8)
            ->get();

        if ($trips->isEmpty()) {
            return "你目前還沒有報名活動。\n\n輸入「活動」查看可參加的行程。";
        }

        $lines = ["你已報名："];

        foreach ($trips as $trip) {
            $date = $trip->departure_time?->format('m/d H:i') ?? '時間待定';
            $lines[] = "{$date}｜{$trip->title}";
            $lines[] = $trip->meeting_point ? "集合：{$trip->meeting_point}" : '集合地點待補';
            $lines[] = '';
        }

        return trim(implode("\n", $lines));
    }

    private function joinTripText(User $user, int $tripId): string
    {
        $trip = Trip::query()
            ->withCount('participants')
            ->upcoming()
            ->find($tripId);

        if (! $trip) {
            return "找不到這個活動。\n\n輸入「活動」查看目前可報名行程。";
        }

        if ($trip->status !== 'open') {
            return '這個活動目前沒有開放報名。';
        }

        if ($trip->participants()->whereKey($user->id)->exists()) {
            return "你已經報名「{$trip->title}」。\n\n輸入「我的活動」查看已報名行程。";
        }

        if ($trip->participants_count >= $trip->quota) {
            return "「{$trip->title}」目前名額已滿。";
        }

        $trip->participants()->attach($user->id);

        return "報名成功：{$trip->title}\n\n輸入「我的活動」可以查看已報名行程。";
    }

    private function helpText(): string
    {
        return implode("\n", [
            '劉里長登山 LINE Bot',
            '',
            '可輸入：',
            '活動：查看開放活動',
            '參加 1：報名指定活動 ID',
            '我的活動：查看已報名行程',
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
