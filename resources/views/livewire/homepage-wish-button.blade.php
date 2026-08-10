<div
    wire:key="homepage-wish-button-{{ $this->getId() }}"
    @class([
        'mt-3' => $variant !== 'dark',
    ])
>
    <button
        type="button"
        wire:click="toggle"
        wire:loading.attr="disabled"
        wire:target="toggle"
        @class([
            'flex h-9 w-full items-center justify-center rounded-full px-4 py-2 text-sm font-semibold disabled:cursor-wait disabled:opacity-70',
            'bg-emerald-600 text-white' => ! $joined,
            'bg-emerald-700 text-white' => $joined,
        ])
    >
        <span wire:loading.remove wire:target="toggle">
            {{ $joined ? '已完成報名' : '+1' }}
        </span>
        <span wire:loading wire:target="toggle">
            處理中
        </span>
    </button>

    @if ($showAvatars)
        <div class="mt-3 flex h-7 items-center">
            @foreach ($joinedUsers as $joinedUser)
                <div
                    class="h-7 w-7 overflow-hidden rounded-full border-2 border-white bg-slate-100 shadow-sm -ml-1 first:ml-0"
                    title="{{ $joinedUser->name }}"
                >
                    @if ($joinedUser->avatar && ! in_array(strtolower(pathinfo($joinedUser->avatar, PATHINFO_EXTENSION)), ['heic', 'heif'], true))
                        <img
                            src="{{ $joinedUser->avatar_url }}?v={{ $joinedUser->updated_at?->timestamp }}"
                            alt="{{ $joinedUser->name }}"
                            class="h-full w-full object-cover"
                        >
                    @else
                        <span class="flex h-full w-full items-center justify-center text-xs font-semibold text-slate-700">
                            {{ mb_substr($joinedUser->name, 0, 1) }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
