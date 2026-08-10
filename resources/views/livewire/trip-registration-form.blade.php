<div wire:ignore.self x-data x-on:open-registration-form.window="if ($event.detail.tripId === {{ $tripId }}) $wire.register()">
    {{-- Registration Form Modal --}}
    @if ($showModal)
        <div
            class="fixed inset-0 z-50 flex items-end justify-center sm:items-center"
            x-data
            x-on:keydown.escape.window="$wire.close()"
        >
            {{-- Backdrop --}}
            <div
                class="fixed inset-0 bg-black/50"
                x-on:click="$wire.close()"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            ></div>

            {{-- Modal --}}
            <div
                class="relative w-full max-w-lg rounded-t-3xl bg-white p-6 shadow-xl sm:rounded-3xl"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
            >
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">報名資料</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $trip->title }}</p>
                    </div>
                    <button
                        type="button"
                        wire:click="close"
                        class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- User Profile Reference --}}
                <div class="mb-6 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-xs font-medium text-slate-500">個人資料（自動帶入）</div>
                    <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <span class="text-slate-400">姓名</span>
                            <span class="ml-2 font-medium text-slate-700">{{ $user->name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400">電話</span>
                            <span class="ml-2 font-medium text-slate-700">{{ $user->phone ?: '未填' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400">緊急聯絡人</span>
                            <span class="ml-2 font-medium text-slate-700">{{ $user->emergency_contact_name ?: '未填' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400">聯絡電話</span>
                            <span class="ml-2 font-medium text-slate-700">{{ $user->emergency_contact_phone ?: '未填' }}</span>
                        </div>
                    </div>
                    <p class="mt-3 text-xs leading-5 text-slate-500">
                        這些資料會提供給主辦者作為聯繫與緊急應變參考，送出前可以先確認是否完整。
                    </p>
                </div>

                {{-- Registration Form --}}
                <form wire:submit="submit" class="space-y-4">
                    <div>
                        <label class="ui-label">飲食需求</label>
                        <input
                            type="text"
                            wire:model="dietary_restrictions"
                            placeholder="如：素食、不吃牛、過敏食物等"
                            class="w-full"
                        >
                        @error('dietary_restrictions')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="ui-label">健康備註</label>
                        <textarea
                            wire:model="health_notes"
                            rows="2"
                            placeholder="如：心臟病、高血壓、氣喘等病史，或近期傷勢"
                            class="w-full"
                        ></textarea>
                        @error('health_notes')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="ui-label">特殊需求</label>
                        <textarea
                            wire:model="special_requests"
                            rows="2"
                            placeholder="如：裝備借用、共乘需求、無經驗需協助等"
                            class="w-full"
                        ></textarea>
                        @error('special_requests')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="ui-label">其他備註</label>
                        <textarea
                            wire:model="notes"
                            rows="2"
                            placeholder="其他想讓主辦人知道的事"
                            class="w-full"
                        ></textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            wire:click="close"
                            class="ui-btn-soft flex-1"
                        >
                            取消
                        </button>
                        <button
                            type="submit"
                            class="ui-btn-primary flex-1"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="submit">{{ $trip->price > 0 ? '送出並前往付款' : '送出並完成報名' }}</span>
                            <span wire:loading wire:target="submit">處理中...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
