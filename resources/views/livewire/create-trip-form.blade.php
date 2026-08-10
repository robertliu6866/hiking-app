<div
    class="mx-auto w-full space-y-5 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/60"
    x-data="{
        step: 'basic',
        routeMode: @js($route_mode),
        chooseRouteMode(mode) {
            this.routeMode = mode;
            this.$wire.set('route_mode', mode);
        }
    }"
>
    <div>
        <h2 class="text-lg font-semibold text-slate-950">建立活動</h2>
        <p class="ui-muted mt-1">先讓山友快速判斷能不能參加，再補路線與安全資訊。</p>
    </div>

    <div class="grid grid-cols-3 rounded-full border border-slate-200 bg-slate-50 p-1">
        <button type="button" @click="step = 'basic'" :class="step === 'basic' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600'" class="ui-tab">
            基本
        </button>
        <button type="button" @click="step = 'route'" :class="step === 'route' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600'" class="ui-tab">
            路線
        </button>
        <button type="button" @click="step = 'safety'" :class="step === 'safety' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600'" class="ui-tab">
            安全
        </button>
    </div>

    @if (session()->has('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="font-medium">活動尚未建立，請檢查必填或格式。</div>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('trips.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="route_mode" x-model="routeMode" value="{{ $route_mode }}">

        <section x-show="step === 'basic'" x-transition.opacity class="space-y-5">
            <div class="rounded-3xl bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-800">
                先填使用者做決定最需要的資訊：去哪裡、難度、適合誰、費用與名額。
            </div>

            <div class="grid grid-cols-3 gap-2">
                <button
                    type="button"
                    @click="chooseRouteMode('traverse')"
                    :class="routeMode === 'traverse' ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-700 hover:border-emerald-200 hover:bg-emerald-50'"
                    class="rounded-2xl border px-3 py-3 text-left transition"
                >
                    <div class="text-sm font-semibold">縱走</div>
                    <div class="mt-1 text-xs opacity-75">多點進出</div>
                </button>
                <button
                    type="button"
                    @click="chooseRouteMode('single')"
                    :class="routeMode === 'single' ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-700 hover:border-emerald-200 hover:bg-emerald-50'"
                    class="rounded-2xl border px-3 py-3 text-left transition"
                >
                    <div class="text-sm font-semibold">單攻</div>
                    <div class="mt-1 text-xs opacity-75">同日完成</div>
                </button>
                <button
                    type="button"
                    @click="chooseRouteMode('custom')"
                    :class="routeMode === 'custom' ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-700 hover:border-emerald-200 hover:bg-emerald-50'"
                    class="rounded-2xl border px-3 py-3 text-left transition"
                >
                    <div class="text-sm font-semibold">自由</div>
                    <div class="mt-1 text-xs opacity-75">彈性規劃</div>
                </button>
            </div>

            <div>
                <label class="ui-label">活動標題</label>
                <input type="text" name="title" wire:model="title" placeholder="例如 合歡北峰日出單攻" class="w-full">
                @error('title') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-3">
                <div>
                    <label class="ui-label">山名</label>
                    <input type="text" name="mountain" wire:model="mountain" placeholder="合歡山北峰" class="w-full">
                </div>

                <div>
                    <label class="ui-label">活動類別</label>
                    <input type="text" name="category" wire:model="category" placeholder="百岳、郊山、訓練" class="w-full">
                </div>
            </div>

            <div class="grid gap-3">
                <div>
                    <label class="ui-label">難度</label>
                    <select name="difficulty" wire:model="difficulty" class="w-full">
                        <option value="1">1 / 5 輕鬆</option>
                        <option value="2">2 / 5 入門</option>
                        <option value="3">3 / 5 中等</option>
                        <option value="4">4 / 5 進階</option>
                        <option value="5">5 / 5 硬派</option>
                    </select>
                </div>

                <div>
                    <label class="ui-label">適合對象</label>
                    <input type="text" name="suitable_for" wire:model="suitable_for" placeholder="例：有郊山經驗、可行走 6 小時" class="w-full">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ui-label">費用</label>
                    <input type="number" name="price" wire:model="price" class="w-full">
                    @error('price') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="ui-label">名額</label>
                    <input type="number" name="quota" wire:model="quota" class="w-full">
                    @error('quota') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <button type="button" @click="step = 'route'" class="ui-btn-primary w-full">
                下一步：路線與集合
            </button>
        </section>

        <section x-show="step === 'route'" x-transition.opacity class="space-y-5">
            <div class="rounded-3xl bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-800">
                路線資訊不用寫很長，讓山友知道集合、距離與折返方式即可。
            </div>

            <div class="grid gap-3">
                <div>
                    <label class="ui-label">里程</label>
                    <input type="number" step="0.1" name="distance_km" wire:model="distance_km" placeholder="km" class="w-full">
                </div>

                <div>
                    <label class="ui-label">爬升</label>
                    <input type="number" name="elevation_gain_m" wire:model="elevation_gain_m" placeholder="m" class="w-full">
                </div>

                <div>
                    <label class="ui-label">時間</label>
                    <input type="number" step="0.5" name="estimated_hours" wire:model="estimated_hours" placeholder="hr" class="w-full">
                </div>
            </div>

            <div x-cloak x-show="routeMode === 'traverse'" x-transition.opacity class="rounded-3xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                <div class="text-sm font-semibold text-slate-800">縱走資訊</div>
                <div class="grid gap-3">
                    <div>
                        <label class="ui-label">起點</label>
                        <input type="text" name="start_point" wire:model="start_point" class="w-full">
                    </div>
                    <div>
                        <label class="ui-label">終點</label>
                        <input type="text" name="end_point" wire:model="end_point" class="w-full">
                    </div>
                </div>
                <div>
                    <label class="ui-label">中途節點</label>
                    <textarea name="waypoints" wire:model="waypoints" rows="3" placeholder="每行一個節點" class="w-full"></textarea>
                </div>
            </div>

            <div x-show="routeMode === 'single'" x-transition.opacity class="rounded-3xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                <div class="text-sm font-semibold text-slate-800">單攻資訊</div>
                <div class="grid gap-3">
                    <div>
                        <label class="ui-label">登山口</label>
                        <input type="text" name="trailhead" wire:model="trailhead" class="w-full">
                    </div>
                    <div>
                        <label class="ui-label">目標</label>
                        <input type="text" name="summit" wire:model="summit" placeholder="主峰、折返點" class="w-full">
                    </div>
                </div>
                <div>
                    <label class="ui-label">最晚折返時間</label>
                    <input type="text" name="turnaround_time" wire:model="turnaround_time" placeholder="例如 11:00" class="w-full">
                </div>
            </div>

            <div x-cloak x-show="routeMode === 'custom'" x-transition.opacity class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                <label class="ui-label">自由規劃備註</label>
                <textarea name="plan_note" wire:model="plan_note" rows="3" placeholder="例如依天候調整路線、訓練目的、臨時備案" class="w-full"></textarea>
            </div>

            <div class="grid gap-3">
                <div>
                    <label class="ui-label">地點</label>
                    <input type="text" name="location" wire:model="location" placeholder="南投、花蓮..." class="w-full">
                </div>

                <div>
                    <label class="ui-label">出發時間</label>
                    <input type="datetime-local" name="departure_time" wire:model="departure_time" class="w-full">
                    @error('departure_time') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="ui-label">集合地點</label>
                <input type="text" name="meeting_point" wire:model="meeting_point" placeholder="例：小風口停車場、台北車站東三門" class="w-full">
            </div>

            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-slate-800">交通與共乘</div>
                        <p class="mt-1 text-xs leading-5 text-slate-500">先讓山友知道怎麼到集合點、費用如何分攤。</p>
                    </div>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-emerald-700">核心資訊</span>
                </div>

                <div>
                    <label class="ui-label">交通方式</label>
                    <select name="transport_plan" wire:model="transport_plan" class="w-full">
                        <option value="carpool">可共乘</option>
                        <option value="mixed">共乘 / 自行前往皆可</option>
                        <option value="self">自行前往</option>
                        <option value="public_transport">大眾運輸</option>
                    </select>
                </div>

                <div class="grid gap-3">
                    <div>
                        <label class="ui-label">共乘出發地</label>
                        <input type="text" name="carpool_origin" wire:model="carpool_origin" placeholder="台北車站" class="w-full">
                    </div>

                    <div>
                        <label class="ui-label">車位</label>
                        <input type="number" name="carpool_seats" wire:model="carpool_seats" placeholder="3" class="w-full">
                    </div>

                    <div>
                        <label class="ui-label">預估分攤</label>
                        <input type="number" name="carpool_cost" wire:model="carpool_cost" placeholder="600" class="w-full">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="step = 'basic'" class="ui-btn-ghost">
                    上一步
                </button>
                <button type="button" @click="step = 'safety'" class="ui-btn-primary">
                    下一步：安全資訊
                </button>
            </div>
        </section>

        <section x-show="step === 'safety'" x-transition.opacity class="space-y-5">
            <div class="rounded-3xl bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-800">
                安全資訊會建立信任感。寫清楚裝備、撤退條件與天候規則。
            </div>

            <div>
                <label class="ui-label">建議裝備</label>
                <textarea name="equipment" wire:model="equipment" rows="6" placeholder="每行一項裝備" class="w-full"></textarea>
            </div>

            <div>
                <label class="ui-label">風險與撤退提醒</label>
                <textarea name="safety_note" wire:model="safety_note" rows="3" placeholder="例：午後雷陣雨機率高，若 11:00 未抵達稜線即折返。" class="w-full"></textarea>
            </div>

            <div>
                <label class="ui-label">取消 / 天候規則</label>
                <textarea name="cancellation_policy" wire:model="cancellation_policy" rows="3" placeholder="例：中央氣象署發布豪雨特報或颱風警報即取消。" class="w-full"></textarea>
            </div>

            <div>
                <label class="ui-label">活動描述</label>
                <textarea name="description" wire:model="description" rows="5" placeholder="補充交通、節奏、經驗要求或其他注意事項。" class="w-full"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="step = 'route'" class="ui-btn-ghost">
                    上一步
                </button>
                <button type="submit" class="ui-btn-primary">
                    建立活動
                </button>
            </div>
        </section>
    </form>
</div>
