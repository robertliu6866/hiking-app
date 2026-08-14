<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\TripWish;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class LineFlexMessageService
{
    /**
     * @return array<string, mixed>
     */
    public function wishParticipantJoined(TripWish $wish, User $joiningUser, string $message): array
    {
        $date = $wish->wished_date?->format('Y/m/d') ?? '日期待定';
        $avatar = $joiningUser->line_picture_url ?: $joiningUser->avatar_url;

        return [
            'type' => 'flex',
            'altText' => "{$joiningUser->name} 已加入 {$wish->mountain} 許願",
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'hero' => [
                    'type' => 'image',
                    'url' => $avatar,
                    'size' => 'full',
                    'aspectRatio' => '20:13',
                    'aspectMode' => 'cover',
                    'action' => [
                        'type' => 'uri',
                        'uri' => route('lotteries.yushan'),
                    ],
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'spacing' => 'md',
                    'backgroundColor' => '#F0FDF4',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => '許願成功 · 新山友加入',
                            'size' => 'xs',
                            'weight' => 'bold',
                            'color' => '#15803D',
                        ],
                        [
                            'type' => 'text',
                            'text' => $joiningUser->name,
                            'size' => 'xl',
                            'weight' => 'bold',
                            'color' => '#0F172A',
                            'wrap' => true,
                        ],
                        [
                            'type' => 'text',
                            'text' => $message,
                            'size' => 'sm',
                            'color' => '#475569',
                            'wrap' => true,
                        ],
                        [
                            'type' => 'separator',
                            'margin' => 'md',
                        ],
                        $this->infoRow('想去的山', $wish->mountain),
                        $this->infoRow('預計日期', $date),
                    ],
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '12px',
                    'contents' => [[
                        'type' => 'button',
                        'style' => 'primary',
                        'height' => 'sm',
                        'color' => '#16A34A',
                        'action' => [
                            'type' => 'uri',
                            'label' => '查看許願看板',
                            'uri' => route('lotteries.yushan'),
                        ],
                    ]],
                ],
            ],
        ];
    }

    /**
     * @param  Collection<int, Trip>  $trips
     * @return array<string, mixed>
     */
    public function tripCarousel(Collection $trips): array
    {
        return [
            'type' => 'flex',
            'altText' => '目前開放活動',
            'contents' => [
                'type' => 'carousel',
                'contents' => $trips->map(fn (Trip $trip) => $this->tripBubble($trip))->values()->all(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function tripBubble(Trip $trip): array
    {
        $date = $trip->departure_time?->format('m/d H:i') ?? '時間待定';
        $quota = "{$trip->participants_count}/{$trip->quota}";

        return [
            'type' => 'bubble',
            'size' => 'mega',
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'spacing' => 'md',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => $trip->title,
                        'weight' => 'bold',
                        'size' => 'lg',
                        'wrap' => true,
                        'color' => '#111827',
                    ],
                    [
                        'type' => 'text',
                        'text' => $trip->mountain ?: '登山活動',
                        'size' => 'sm',
                        'color' => '#6B7280',
                        'wrap' => true,
                    ],
                    [
                        'type' => 'separator',
                    ],
                    $this->infoRow('日期', $date),
                    $this->infoRow('地點', $trip->location ?: '地點待補'),
                    $this->infoRow('名額', $quota),
                    $this->infoRow('費用', $trip->price > 0 ? '$'.number_format($trip->price) : '免費'),
                ],
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'spacing' => 'sm',
                'contents' => [
                    [
                        'type' => 'button',
                        'style' => 'primary',
                        'height' => 'sm',
                        'color' => '#111827',
                        'action' => [
                            'type' => 'postback',
                            'label' => '我要參加',
                            'data' => "action=join_trip&trip_id={$trip->id}",
                            'displayText' => "我要參加 {$trip->title}",
                        ],
                    ],
                    [
                        'type' => 'button',
                        'style' => 'link',
                        'height' => 'sm',
                        'action' => [
                            'type' => 'uri',
                            'label' => '查看網站詳情',
                            'uri' => route('trips.show', $trip),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function infoRow(string $label, string $value): array
    {
        return [
            'type' => 'box',
            'layout' => 'baseline',
            'spacing' => 'sm',
            'contents' => [
                [
                    'type' => 'text',
                    'text' => $label,
                    'size' => 'sm',
                    'color' => '#9CA3AF',
                    'flex' => 2,
                ],
                [
                    'type' => 'text',
                    'text' => $value,
                    'size' => 'sm',
                    'color' => '#374151',
                    'wrap' => true,
                    'flex' => 5,
                ],
            ],
        ];
    }
}
