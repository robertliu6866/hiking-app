<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900">建立活動</h2>

            <a href="{{ route('trips.index') }}" class="text-sm text-gray-500">返回活動</a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-container">
            @livewire(\App\Livewire\CreateTripForm::class, [
                'prefill' => request()->only(['mountain', 'wished_date', 'route_mode', 'note']),
            ])
        </div>
    </div>
</x-app-layout>
