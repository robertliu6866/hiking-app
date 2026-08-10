<?php

namespace App\Services;

use App\Models\TripOrder;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class LinePayService
{
    public function requestPayment(TripOrder $order): array
    {
        $uri = '/v3/payments/request';
        $body = [
            'amount' => $order->amount,
            'currency' => $order->currency,
            'orderId' => $order->merchant_order_id,
            'packages' => [
                [
                    'id' => 'trip-'.$order->trip_id,
                    'amount' => $order->amount,
                    'products' => [
                        [
                            'name' => Str::limit($order->trip->title, 80, ''),
                            'quantity' => 1,
                            'price' => $order->amount,
                        ],
                    ],
                ],
            ],
            'redirectUrls' => [
                'confirmUrl' => route('payments.line-pay.confirm', ['order' => $order]),
                'cancelUrl' => route('payments.line-pay.cancel', ['order' => $order]),
            ],
        ];

        return $this->post($uri, $body);
    }

    public function confirmPayment(TripOrder $order, string $transactionId): array
    {
        $uri = '/v3/payments/'.$transactionId.'/confirm';
        $body = [
            'amount' => $order->amount,
            'currency' => $order->currency,
        ];

        return $this->post($uri, $body);
    }

    private function post(string $uri, array $body): array
    {
        $payload = json_encode($body, JSON_UNESCAPED_UNICODE);
        $nonce = (string) Str::uuid();
        $response = $this->client($nonce, $this->signature($uri, $payload, $nonce))
            ->withBody($payload, 'application/json')
            ->post($this->baseUrl().$uri);

        if (! $response->successful()) {
            throw new RuntimeException('LINE Pay request failed with HTTP '.$response->status());
        }

        $data = $response->json();

        if (($data['returnCode'] ?? null) !== '0000') {
            throw new RuntimeException($data['returnMessage'] ?? 'LINE Pay rejected the request.');
        }

        return $data;
    }

    private function client(string $nonce, string $signature): PendingRequest
    {
        $channelId = config('services.line_pay.channel_id');

        if (! $channelId || ! config('services.line_pay.channel_secret')) {
            throw new RuntimeException('LINE Pay credentials are not configured.');
        }

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-LINE-ChannelId' => $channelId,
            'X-LINE-Authorization-Nonce' => $nonce,
            'X-LINE-Authorization' => $signature,
        ]);
    }

    private function signature(string $uri, string $payload, string $nonce): string
    {
        $secret = config('services.line_pay.channel_secret');

        return base64_encode(hash_hmac('sha256', $secret.$uri.$payload.$nonce, $secret, true));
    }

    private function baseUrl(): string
    {
        return rtrim(config('services.line_pay.base_url', 'https://sandbox-api-pay.line.me'), '/');
    }
}
