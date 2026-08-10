<?php

namespace App\Livewire;

use App\Models\Trip;
use App\Models\TripOrder;
use App\Models\TripRegistration;
use App\Notifications\PaymentLinkNotification;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class TripRegistrationForm extends Component
{
    public int $tripId;

    public string $dietary_restrictions = '';
    public string $health_notes = '';
    public string $special_requests = '';
    public string $notes = '';

    public bool $showModal = false;

    public function mount(int $tripId): void
    {
        $this->tripId = $tripId;

        $this->loadExistingRegistration();
    }

    public function register(): void
    {
        $this->loadExistingRegistration();
        $this->showModal = true;
    }

    private function loadExistingRegistration(): void
    {
        $registration = TripRegistration::where('trip_id', $this->tripId)
            ->where('user_id', auth()->id())
            ->first();

        if ($registration) {
            $this->dietary_restrictions = $registration->dietary_restrictions ?? '';
            $this->health_notes = $registration->health_notes ?? '';
            $this->special_requests = $registration->special_requests ?? '';
            $this->notes = $registration->notes ?? '';
        }
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    protected function rules(): array
    {
        return [
            'dietary_restrictions' => ['nullable', 'string', 'max:500'],
            'health_notes' => ['nullable', 'string', 'max:500'],
            'special_requests' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function submit(): void
    {
        $validated = $this->validate();

        $trip = Trip::findOrFail($this->tripId);
        $user = auth()->user();

        // 儲存報名資料
        TripRegistration::updateOrCreate(
            ['trip_id' => $this->tripId, 'user_id' => $user->id],
            [
                'dietary_restrictions' => $validated['dietary_restrictions'] ?: null,
                'health_notes' => $validated['health_notes'] ?: null,
                'special_requests' => $validated['special_requests'] ?: null,
                'notes' => $validated['notes'] ?: null,
            ]
        );

        // 檢查是否已報名
        if ($trip->participants()->whereKey($user->id)->exists()) {
            $this->showModal = false;
            $this->dispatch('trip-notice', message: '已更新報名資料');
            $this->dispatch('registration-saved');
            return;
        }

        // 檢查活動狀態
        if ($trip->status !== 'open') {
            $this->showModal = false;
            $this->dispatch('trip-notice', message: '這個活動目前沒有開放報名');
            return;
        }

        // 檢查名額
        if ($trip->reservedSeatsCount() >= $trip->quota) {
            $this->showModal = false;
            $this->dispatch('trip-notice', message: '活動名額已滿');
            return;
        }

        // 免費活動直接加入
        if ($trip->price <= 0) {
            $trip->participants()->syncWithoutDetaching($user->id);
            $freshTrip = $trip->fresh(['participants']);
            $this->showModal = false;
            $this->dispatch('trip-notice', message: '已完成報名');
            $this->dispatch('trip-join-updated', tripId: $this->tripId, status: 'joined', hasJoined: true, count: $freshTrip->participants_count, isFull: $freshTrip->participants_count >= $freshTrip->quota);
            return;
        }

        // 付費活動建立訂單
        $existingOrder = TripOrder::query()
            ->where('trip_id', $trip->id)
            ->where('user_id', $user->id)
            ->whereIn('status', TripOrder::RESERVED_STATUSES)
            ->latest()
            ->first();

        if (! $existingOrder) {
            $existingOrder = TripOrder::create([
                'trip_id' => $trip->id,
                'user_id' => $user->id,
                'merchant_order_id' => 'TRIP'.$trip->id.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6)),
                'amount' => $trip->price,
                'status' => TripOrder::STATUS_PENDING,
                'expires_at' => now()->addHours(2),
            ]);

            // 發送付款鏈接通知
            $user->notify(new PaymentLinkNotification($existingOrder));
        }

        $this->showModal = false;
        $this->dispatch('trip-notice', message: '報名資料已送出，請完成付款');
        $this->dispatch('registration-saved', orderId: $existingOrder->id);
        $this->redirect(route('payments.show', $existingOrder));
    }

    public function render(): View
    {
        $trip = Trip::findOrFail($this->tripId);
        $user = auth()->user();

        return view('livewire.trip-registration-form', [
            'trip' => $trip,
            'user' => $user,
        ]);
    }
}
