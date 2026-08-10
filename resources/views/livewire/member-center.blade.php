<div class="page-shell">
    <div class="page-container space-y-5">
        @if (session()->has('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Avatar Section --}}
        <section class="ui-card">
            <div class="flex flex-col items-center text-center">
                <div class="relative group">
                    <img
                        src="{{ $avatar ? $avatar->temporaryUrl() : $user->avatar_url . '?v=' . $user->updated_at?->timestamp }}"
                        alt="會員頭像"
                        class="h-24 w-24 rounded-full border-4 border-white object-cover shadow-sm"
                    >
                    <label class="absolute inset-0 flex items-center justify-center rounded-full bg-black/40 opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                        </svg>
                        <input type="file" wire:model="avatar" accept="image/jpeg,image/png,image/webp" class="absolute inset-0 opacity-0 cursor-pointer">
                    </label>
                </div>
                <div wire:loading wire:target="avatar" class="mt-2 text-xs text-slate-500">上傳中...</div>
                @error('avatar')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror

                <h2 class="mt-3 text-lg font-semibold text-slate-950">{{ $user->name }}</h2>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>

                @if($user->profile_completed_at)
                    <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        資料已完成
                    </span>
                @else
                    <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                        尚未完成資料
                    </span>
                @endif
            </div>
        </section>

        @if(! $editing)
            {{-- View Mode --}}
            <section class="ui-card space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-slate-950">基本資料</h3>
                    <button wire:click="toggleEdit" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                        編輯
                    </button>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-500">電話</span>
                        <span class="text-sm font-medium text-slate-900">{{ $user->phone ?: '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-500">年齡</span>
                        <span class="text-sm font-medium text-slate-900">{{ $user->age ?: '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-500">性別</span>
                        <span class="text-sm font-medium text-slate-900">{{ $user->gender ?: '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-500">登山年資</span>
                        <span class="text-sm font-medium text-slate-900">{{ $user->hiking_experience ?: '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-500">血型</span>
                        <span class="text-sm font-medium text-slate-900">{{ $user->blood_type ?: '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-500">地址</span>
                        <span class="text-sm font-medium text-slate-900">{{ $user->address ?: '-' }}</span>
                    </div>
                </div>
            </section>

            <section class="ui-card space-y-4">
                <h3 class="text-sm font-medium text-slate-950">緊急聯絡人</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-500">聯絡人</span>
                        <span class="text-sm font-medium text-slate-900">{{ $user->emergency_contact_name ?: '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-500">電話</span>
                        <span class="text-sm font-medium text-slate-900">{{ $user->emergency_contact_phone ?: '-' }}</span>
                    </div>
                </div>
            </section>

            @if($user->bio)
                <section class="ui-card">
                    <h3 class="text-sm font-medium text-slate-950 mb-3">自我介紹</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">{{ $user->bio }}</p>
                </section>
            @endif

        @else
            {{-- Edit Mode --}}
            <form wire:submit="save" class="space-y-5">
                <section class="ui-card space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-slate-950">編輯資料</h3>
                        <button type="button" wire:click="toggleEdit" class="text-sm font-medium text-slate-500 hover:text-slate-700">
                            取消
                        </button>
                    </div>

                    <div>
                        <label class="ui-label">姓名</label>
                        <input type="text" wire:model="name" class="w-full">
                        @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ui-label">Email</label>
                        <input type="email" wire:model="email" class="w-full">
                        @error('email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ui-label">電話</label>
                        <input type="text" wire:model="phone" class="w-full">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="ui-label">年齡</label>
                            <input type="number" wire:model="age" class="w-full">
                        </div>
                        <div>
                            <label class="ui-label">性別</label>
                            <select wire:model="gender" class="w-full">
                                <option value="">請選擇</option>
                                <option value="男">男</option>
                                <option value="女">女</option>
                                <option value="其他">其他</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="ui-label">登山年資</label>
                        <select wire:model="hiking_experience" class="w-full">
                            <option value="">請選擇</option>
                            <option value="1年以下">1年以下</option>
                            <option value="1-3年">1-3年</option>
                            <option value="3-5年">3-5年</option>
                            <option value="5-10年">5-10年</option>
                            <option value="10年以上">10年以上</option>
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">血型</label>
                        <select wire:model="blood_type" class="w-full">
                            <option value="">請選擇</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="O">O</option>
                            <option value="AB">AB</option>
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">地址</label>
                        <input type="text" wire:model="address" class="w-full">
                    </div>
                </section>

                <section class="ui-card space-y-4">
                    <h3 class="text-sm font-medium text-slate-950">緊急聯絡人</h3>
                    <div>
                        <label class="ui-label">聯絡人姓名</label>
                        <input type="text" wire:model="emergency_contact_name" class="w-full">
                    </div>
                    <div>
                        <label class="ui-label">聯絡電話</label>
                        <input type="text" wire:model="emergency_contact_phone" class="w-full">
                    </div>
                </section>

                <section class="ui-card space-y-4">
                    <h3 class="text-sm font-medium text-slate-950">自我介紹</h3>
                    <textarea wire:model="bio" rows="4" placeholder="讓其他山友更了解你..." class="w-full"></textarea>
                </section>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="avatar,save"
                    class="ui-btn-primary w-full disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="save">儲存資料</span>
                    <span wire:loading wire:target="save">儲存中...</span>
                </button>
            </form>
        @endif
    </div>
</div>
