<div
    x-data="{
        open: false,
        init() {
            this.open = ! window.localStorage.getItem('liuliu_guest_welcome_seen');
        },
        complete() {
            window.localStorage.setItem('liuliu_guest_welcome_seen', '1');
            this.open = false;
        }
    }"
    x-show="open"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-[60] flex items-end bg-slate-950/55 px-4 py-4 sm:items-center sm:justify-center"
    role="dialog"
    aria-modal="true"
    aria-labelledby="how-we-play-title"
>
    <section x-show="open" x-transition.scale.origin.bottom class="w-full max-w-[390px] overflow-hidden rounded-[2rem] bg-white shadow-2xl">
        <div class="bg-[linear-gradient(135deg,#064e3b_0%,#059669_100%)] px-6 pb-7 pt-6 text-white">
            <span class="text-2xl" aria-hidden="true">⛰️</span>
            <h2 id="how-we-play-title" class="mt-2 text-2xl font-semibold tracking-tight">我們怎麼玩？</h2>
            <p class="mt-2 text-sm leading-6 text-emerald-50">不是找主揪，是找到願意同行的人。</p>
        </div>

        <div class="space-y-5 px-6 py-6">
            <div class="flex gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-lg">👤</span>
                <div><h3 class="text-sm font-semibold text-slate-950">真人加入</h3><p class="mt-1 text-sm leading-6 text-slate-500">知道跟誰一起上山，安心一點。</p></div>
            </div>
            <div class="flex gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-lg">⚖️</span>
                <div><h3 class="text-sm font-semibold text-slate-950">一起成行，不預設主揪</h3><p class="mt-1 text-sm leading-6 text-slate-500">第一位許願的人不必帶隊。有人可帶就一起跟；自由成團則在成行時抽籤選協調人。</p></div>
            </div>
            <div class="flex gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-lg">📍</span>
                <div><h3 class="text-sm font-semibold text-slate-950">紀錄都在這裡</h3><p class="mt-1 text-sm leading-6 text-slate-500">參加過什麼、揪過幾次，平台幫你記著。不用翻群組、不靠印象。</p></div>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium leading-6 text-emerald-800">有來有往，才能一起玩得久。</div>
            <button type="button" class="ui-btn-primary w-full" x-on:click="complete()">我懂了，開始找山友 ⛰️</button>
        </div>
    </section>
</div>
