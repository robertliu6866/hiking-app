<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripOrder;
use App\Notifications\BankTransferSubmitted;
use App\Services\LinePayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class TripPaymentController extends Controller
{
    public function show(Request $request, TripOrder $order)
    {
        $this->authorizeOrderViewer($request, $order);

        $order->load(['trip.user', 'user']);

        return view('payments.show', [
            'order' => $order,
        ]);
    }

    public function store(Request $request, Trip $trip)
    {
        if ($trip->participants()->whereKey($request->user()->id)->exists()) {
            $trip->participants()->detach($request->user()->id);

            return back()->with('status', 'trip-canceled');
        }

        if ($trip->status !== 'open') {
            return back()->withErrors(['trip' => '這個活動目前沒有開放報名']);
        }

        if ($trip->price <= 0) {
            $trip->participants()->syncWithoutDetaching($request->user()->id);

            return back()->with('status', 'trip-joined');
        }

        $order = TripOrder::query()
            ->where('trip_id', $trip->id)
            ->where('user_id', $request->user()->id)
            ->whereIn('status', TripOrder::RESERVED_STATUSES)
            ->latest()
            ->first();

        if ($order) {
            return redirect()->route('payments.show', $order);
        }

        if ($trip->reservedSeatsCount() >= $trip->quota) {
            return back()->withErrors(['trip' => '活動名額已滿']);
        }

        if (! $order) {
            $order = TripOrder::create([
                'trip_id' => $trip->id,
                'user_id' => $request->user()->id,
                'merchant_order_id' => 'TRIP'.$trip->id.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6)),
                'amount' => $trip->price,
                'status' => TripOrder::STATUS_PENDING,
                'expires_at' => now()->addHours(2),
            ]);
        }

        return redirect()->route('payments.show', $order);
    }

    public function linePay(Request $request, TripOrder $order, LinePayService $linePay)
    {
        $this->authorizeOrderOwner($request, $order);

        if ($order->isPaid()) {
            return redirect()->route('trips.show', $order->trip)->with('status', 'trip-joined');
        }

        try {
            $order->load('trip');
            $response = $linePay->requestPayment($order);
            $paymentUrl = $response['info']['paymentUrl']['web'] ?? null;

            if (! $paymentUrl) {
                return back()->withErrors(['payment' => 'LINE Pay 沒有回傳付款網址']);
            }

            $order->update([
                'payment_method' => 'line_pay',
                'status' => TripOrder::STATUS_LINE_PAY_PENDING,
                'raw_response' => $response,
            ]);

            return redirect()->away($paymentUrl);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['payment' => 'LINE Pay 付款建立失敗，請稍後再試或改用銀行匯款。']);
        }
    }

    public function bankTransfer(Request $request, TripOrder $order)
    {
        $this->authorizeOrderOwner($request, $order);

        $validated = $request->validate([
            'bank_transfer_name' => ['required', 'string', 'max:80'],
            'bank_transfer_last_five' => ['required', 'digits:5'],
        ]);

        DB::transaction(function () use ($order, $validated) {
            $order->update([
                'payment_method' => 'bank_transfer',
                'status' => TripOrder::STATUS_BANK_TRANSFER_PENDING,
                'bank_transfer_name' => $validated['bank_transfer_name'],
                'bank_transfer_last_five' => $validated['bank_transfer_last_five'],
            ]);

            $order->load(['trip.user', 'user']);
            $order->trip->user->notify(new BankTransferSubmitted($order));
        });

        return redirect()->route('payments.show', $order)->with('status', 'bank-transfer-submitted');
    }

    public function confirmBankTransfer(Request $request, TripOrder $order)
    {
        if ($order->trip->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->markPaidAndJoin($order, providerTransactionId: null);

        return redirect()->route('payments.show', $order)->with('status', 'payment-confirmed');
    }

    public function linePayConfirm(Request $request, TripOrder $order, LinePayService $linePay)
    {
        $this->authorizeOrderOwner($request, $order);

        $transactionId = (string) $request->query('transactionId');

        if ($transactionId === '') {
            return redirect()->route('payments.show', $order)->withErrors(['payment' => 'LINE Pay 沒有回傳交易編號']);
        }

        try {
            $response = $linePay->confirmPayment($order, $transactionId);

            $order->update([
                'raw_response' => $response,
            ]);

            $this->markPaidAndJoin($order, $transactionId);

            return redirect()->route('trips.show', $order->trip)->with('status', 'trip-joined');
        } catch (Throwable $exception) {
            report($exception);

            $order->update(['status' => TripOrder::STATUS_FAILED]);

            return redirect()->route('payments.show', $order)->withErrors(['payment' => 'LINE Pay 確認付款失敗，請重新付款或改用銀行匯款。']);
        }
    }

    public function linePayCancel(Request $request, TripOrder $order)
    {
        $this->authorizeOrderOwner($request, $order);

        $order->update(['status' => TripOrder::STATUS_CANCELED]);

        return redirect()->route('payments.show', $order)->with('status', 'line-pay-canceled');
    }

    private function markPaidAndJoin(TripOrder $order, ?string $providerTransactionId): void
    {
        DB::transaction(function () use ($order, $providerTransactionId) {
            $order->refresh();

            if ($order->isPaid()) {
                return;
            }

            if ($order->trip->participants()->count() >= $order->trip->quota
                && ! $order->trip->participants()->whereKey($order->user_id)->exists()) {
                throw new \RuntimeException('活動名額已滿');
            }

            $order->update([
                'status' => TripOrder::STATUS_PAID,
                'provider_transaction_id' => $providerTransactionId,
                'paid_at' => now(),
            ]);

            $order->trip->participants()->syncWithoutDetaching($order->user_id);
        });
    }

    private function authorizeOrderOwner(Request $request, TripOrder $order): void
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    private function authorizeOrderViewer(Request $request, TripOrder $order): void
    {
        if ($order->user_id !== $request->user()->id && $order->trip->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
