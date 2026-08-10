<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">會員中心</h2>
        </div>
    </x-slot>

    @livewire(\App\Livewire\MemberCenter::class)
</x-app-layout>
