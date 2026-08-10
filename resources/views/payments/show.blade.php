<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">付款</h2>
            <a href="{{ route('trips.show', $order->trip) }}" class="text-sm text-slate-500">返回活動</a>
        </div>
    </x-slot>

    @php
        $statusLabels = [
            'pending' => '待付款',
            'line_pay_pending' => 'LINE Pay 付款中',
            'bank_transfer_pending' => '匯款待確認',
            'paid' => '已付款',
            'failed' => '付款失敗',
            'canceled' => '已取消',
        ];

        $bank = config('services.bank_transfer');
        $isHost = auth()->id() === $order->trip->user_id;
    @endphp

    <div class="page-shell">
        <div class="page-container space-y-4">
            @if (session('status') === 'bank-transfer-submitted')
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">已送出匯款資訊，等待主辦確認。</div>
            @elseif (session('status') === 'payment-confirmed')
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">已確認收款並完成報名。</div>
            @elseif (session('status') === 'line-pay-canceled')
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">已取消 LINE Pay 付款。</div>
            @endif

            @error('payment')
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
            @enderror

            <section class="ui-card">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm text-slate-500">{{ $order->trip->mountain ?: $order->trip->location }}</p>
                        <h1 class="mt-2 text-2xl font-semibold leading-tight text-slate-950">{{ $order->trip->title }}</h1>
                    </div>
                    <span class="ui-chip-hope shrink-0">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                </div>

                <div class="mt-5 divide-y divide-slate-100 text-sm">
                    <div class="flex items-center justify-between gap-4 py-3 first:pt-0">
                        <span class="text-slate-400">訂單編號</span>
                        <span class="text-right font-medium text-slate-900">{{ $order->merchant_order_id }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-3">
                        <span class="text-slate-400">付款金額</span>
                        <span class="text-right text-lg font-semibold text-emerald-700">NT$ {{ number_format($order->amount) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-3 last:pb-0">
                        <span class="text-slate-400">報名人</span>
                        <span class="text-right font-medium text-slate-900">{{ $order->user->name }}</span>
                    </div>
                </div>
            </section>

            @if ($order->status !== 'paid')
                <section class="ui-card">
                    <h3 class="ui-section-title">LINE Pay</h3>
                    <p class="ui-muted mt-2">使用 LINE Pay 完成付款後，系統會自動確認並加入活動名單。</p>

                    <form method="POST" action="{{ route('payments.line-pay', $order) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="ui-btn-primary w-full">使用 LINE Pay 付款</button>
                    </form>
                </section>

                <section class="ui-card">
                    <h3 class="ui-section-title">銀行匯款</h3>

                    <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-700">
                        <div>銀行：{{ $bank['bank_name'] ?: '請在 .env 設定 BANK_TRANSFER_BANK_NAME' }}</div>
                        <div>代碼：{{ $bank['bank_code'] ?: '請設定 BANK_TRANSFER_BANK_CODE' }}</div>
                        <div>戶名：{{ $bank['account_name'] ?: '請設定 BANK_TRANSFER_ACCOUNT_NAME' }}</div>
                        <div>帳號：{{ $bank['account_number'] ?: '請設定 BANK_TRANSFER_ACCOUNT_NUMBER' }}</div>
                    </div>

                    <form method="POST" action="{{ route('payments.bank-transfer', $order) }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label for="bank_transfer_name" class="ui-label">匯款戶名</label>
                            <input id="bank_transfer_name" type="text" name="bank_transfer_name" value="{{ old('bank_transfer_name', $order->bank_transfer_name) }}" class="w-full">
                            @error('bank_transfer_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="bank_transfer_last_five" class="ui-label">匯款帳號後五碼</label>
                            <input id="bank_transfer_last_five" type="text" name="bank_transfer_last_five" inputmode="numeric" maxlength="5" value="{{ old('bank_transfer_last_five', $order->bank_transfer_last_five) }}" class="w-full">
                            @error('bank_transfer_last_five') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="ui-btn-ghost w-full">送出匯款資訊</button>
                    </form>
                </section>
            @endif

            @if ($order->status === 'bank_transfer_pending' && $isHost)
                <section class="ui-card">
                    <h3 class="ui-section-title">主辦確認收款</h3>
                    <div class="mt-4 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800">
                        <div>匯款戶名：{{ $order->bank_transfer_name }}</div>
                        <div>後五碼：{{ $order->bank_transfer_last_five }}</div>
                    </div>

                    <form method="POST" action="{{ route('payments.bank-transfer.confirm', $order) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="ui-btn-primary w-full">確認已收款並完成報名</button>
                    </form>
                </section>
            @endif

            @if ($order->status === 'paid')
                <a href="{{ route('trips.show', $order->trip) }}" class="ui-btn-primary w-full">查看活動</a>
            @endif
        </div>
    </div>
</x-app-layout>
