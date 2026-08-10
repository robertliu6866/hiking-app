<?php

namespace App\Services;

use App\Models\Trip;
use Illuminate\Database\Eloquent\Collection;

class LineFlexMessageService
{
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
