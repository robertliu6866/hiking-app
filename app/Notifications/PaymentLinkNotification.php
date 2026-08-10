<?php

namespace App\Notifications;

use App\Models\TripOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentLinkNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly TripOrder $order)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->line_user_id) {
            $channels[] = 'line';
        }

        return $channels;
    }

    public function toLine(object $notifiable): array
    {
        $paymentUrl = route('payments.show', $this->order);
        $trip = $this->order->trip;

        return [
            'type' => 'flex',
            'altText' => "付款通知：{$trip->title}",
            'contents' => [
                'type' => 'bubble',
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'spacing' => 'md',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => '報名成功！請完成付款',
                            'weight' => 'bold',
                            'size' => 'lg',
                            'color' => '#111827',
                        ],
                        [
                            'type' => 'separator',
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'baseline',
                            'spacing' => 'sm',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '活動',
                                    'size' => 'sm',
                                    'color' => '#9CA3AF',
                                    'flex' => 2,
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $trip->title,
                                    'size' => 'sm',
                                    'color' => '#374151',
                                    'wrap' => true,
                                    'flex' => 5,
                                ],
                            ],
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'baseline',
                            'spacing' => 'sm',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '金額',
                                    'size' => 'sm',
                                    'color' => '#9CA3AF',
                                    'flex' => 2,
                                ],
                                [
                                    'type' => 'text',
                                    'text' => 'NT$ ' . number_format($this->order->amount),
                                    'size' => 'sm',
                                    'color' => '#374151',
                                    'wrap' => true,
                                    'flex' => 5,
                                ],
                            ],
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'baseline',
                            'spacing' => 'sm',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '訂單',
                                    'size' => 'sm',
                                    'color' => '#9CA3AF',
                                    'flex' => 2,
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $this->order->merchant_order_id,
                                    'size' => 'sm',
                                    'color' => '#374151',
                                    'wrap' => true,
                                    'flex' => 5,
                                ],
                            ],
                        ],
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
                            'color' => '#059669',
                            'action' => [
                                'type' => 'uri',
                                'label' => '前往付款',
                                'uri' => $paymentUrl,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_link',
            'order_id' => $this->order->id,
            'trip_id' => $this->order->trip_id,
            'trip_title' => $this->order->trip->title,
            'amount' => $this->order->amount,
            'payment_url' => route('payments.show', $this->order),
            'message' => '請完成付款以確認報名。',
        ];
    }
}
