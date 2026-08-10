<div class="page-shell">
    <div class="page-container space-y-5">
        <div>
            <h1 class="text-xl font-semibold text-slate-950">編輯資料</h1>
            <p class="ui-muted mt-1">讓同行山友快速了解你的經驗與聯絡資訊。</p>
        </div>

        @if (session()->has('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <section class="ui-card">
            <div class="flex items-center gap-4">
                <img
                    src="{{ $avatar ? $avatar->temporaryUrl() : $user->avatar_url . '?v=' . $user->updated_at?->timestamp }}"
                    alt="會員頭像"
                    class="h-20 w-20 rounded-full border-4 border-white object-cover shadow-sm"
                >

                <div class="min-w-0 flex-1">
                    <label class="ui-label">頭像</label>
                    <input
                        type="file"
                        wire:model="avatar"
                        accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-full file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-emerald-700"
                    >
                    <div wire:loading wire:target="avatar" class="ui-help">
                        圖片上傳中...
                    </div>
                    @error('avatar')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="ui-card space-y-4">
            <div>
                <label class="ui-label">姓名</label>
                <input type="text" wire:model.defer="name" class="w-full">
            </div>

            <div>
                <label class="ui-label">Email</label>
                <input type="email" wire:model.defer="email" class="w-full">
            </div>

            <div>
                <label class="ui-label">電話</label>
                <input type="text" wire:model.defer="phone" class="w-full">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ui-label">年齡</label>
                    <input type="number" wire:model.defer="age" class="w-full">
                </div>

                <div>
                    <label class="ui-label">性別</label>
                    <select wire:model.defer="gender" class="w-full">
                        <option value="">請選擇</option>
                        <option value="男">男</option>
                        <option value="女">女</option>
                        <option value="其他">其他</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="ui-card space-y-4">
            <div>
                <label class="ui-label">登山年資</label>
                <select wire:model.defer="hiking_experience" class="w-full">
                    <option value="">請選擇</option>
                    <option value="1年以下">1年以下</option>
                    <option value="1-3年">1-3年</option>
                    <option value="3-5年">3-5年</option>
                    <option value="5-10年">5-10年</option>
                    <option value="10年以上">10年以上</option>
                </select>
            </div>

            <div class="rounded-3xl bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-800">
                這些偏好會用來排序與標記行程，讓活動卡片顯示「為什麼適合你」。
            </div>

            <div>
                <label class="ui-label">偏好地區</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach (['北部', '中部', '南部', '東部', '離島'] as $region)
                        <button
                            type="button"
                            wire:click="togglePreference('preferred_regions', @js($region))"
                            @class([
                                'flex min-h-11 items-center justify-center rounded-2xl border px-3 py-2 text-sm font-medium transition',
                                'border-emerald-300 bg-emerald-50 text-emerald-700' => in_array($region, $preferred_regions, true),
                                'border-slate-200 bg-white text-slate-700' => ! in_array($region, $preferred_regions, true),
                            ])
                        >
                            {{ $region }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="ui-label">可出發時間</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach (['weekday' => '平日', 'weekend' => '週末', 'holiday' => '連假'] as $value => $label)
                        <button
                            type="button"
                            wire:click="togglePreference('available_days', @js($value))"
                            @class([
                                'flex min-h-11 items-center justify-center rounded-2xl border px-3 py-2 text-sm font-medium transition',
                                'border-emerald-300 bg-emerald-50 text-emerald-700' => in_array($value, $available_days, true),
                                'border-slate-200 bg-white text-slate-700' => ! in_array($value, $available_days, true),
                            ])
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="ui-label">交通方式</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach (['drive' => '自駕', 'carpool' => '可共乘', 'public_transport' => '大眾運輸'] as $value => $label)
                        <button
                            type="button"
                            wire:click="togglePreference('transport_modes', @js($value))"
                            @class([
                                'flex min-h-11 items-center justify-center rounded-2xl border px-3 py-2 text-sm font-medium transition',
                                'border-emerald-300 bg-emerald-50 text-emerald-700' => in_array($value, $transport_modes, true),
                                'border-slate-200 bg-white text-slate-700' => ! in_array($value, $transport_modes, true),
                            ])
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="ui-label">偏好路線</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach (['single' => '單攻', 'traverse' => '縱走', 'custom' => '自由'] as $value => $label)
                        <button
                            type="button"
                            wire:click="togglePreference('preferred_route_modes', @js($value))"
                            @class([
                                'flex min-h-11 items-center justify-center rounded-2xl border px-3 py-2 text-sm font-medium transition',
                                'border-emerald-300 bg-emerald-50 text-emerald-700' => in_array($value, $preferred_route_modes, true),
                                'border-slate-200 bg-white text-slate-700' => ! in_array($value, $preferred_route_modes, true),
                            ])
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="ui-label">登山風格</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach (['slow' => '慢慢走', 'photo' => '拍照', 'training' => '訓練配速', 'newbie_friendly' => '新手友善', 'challenge' => '挑戰型'] as $value => $label)
                        <button
                            type="button"
                            wire:click="togglePreference('hiking_styles', @js($value))"
                            @class([
                                'flex min-h-11 items-center justify-center rounded-2xl border px-3 py-2 text-sm font-medium transition',
                                'border-emerald-300 bg-emerald-50 text-emerald-700' => in_array($value, $hiking_styles, true),
                                'border-slate-200 bg-white text-slate-700' => ! in_array($value, $hiking_styles, true),
                            ])
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ui-label">最低難度</label>
                    <select wire:model.defer="preferred_difficulty_min" class="w-full">
                        <option value="">不限</option>
                        @for ($level = 1; $level <= 5; $level++)
                            <option value="{{ $level }}">{{ $level }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="ui-label">最高難度</label>
                    <select wire:model.defer="preferred_difficulty_max" class="w-full">
                        <option value="">不限</option>
                        @for ($level = 1; $level <= 5; $level++)
                            <option value="{{ $level }}">{{ $level }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div>
                <label class="ui-label">地址</label>
                <input type="text" wire:model.defer="address" class="w-full">
            </div>

            <div>
                <label class="ui-label">血型</label>
                <select wire:model.defer="blood_type" class="w-full">
                    <option value="">請選擇</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="O">O</option>
                    <option value="AB">AB</option>
                </select>
            </div>
        </section>

        <section class="ui-card space-y-4">
            <div>
                <label class="ui-label">緊急聯絡人</label>
                <input type="text" wire:model.defer="emergency_contact_name" class="w-full">
            </div>

            <div>
                <label class="ui-label">緊急電話</label>
                <input type="text" wire:model.defer="emergency_contact_phone" class="w-full">
            </div>

            <div>
                <label class="ui-label">自我介紹</label>
                <textarea wire:model.defer="bio" rows="4" class="w-full"></textarea>
            </div>
        </section>

        <button
            type="button"
            wire:click="save"
            wire:loading.attr="disabled"
            wire:target="avatar,save"
            class="ui-btn-primary w-full disabled:cursor-not-allowed disabled:opacity-50"
        >
            <span wire:loading.remove wire:target="save">儲存資料</span>
            <span wire:loading wire:target="save">儲存中...</span>
        </button>
    </div>
</div>
