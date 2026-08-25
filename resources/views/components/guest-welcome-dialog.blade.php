<div
    x-data="{
        open: false,
        init() {
            this.open = ! window.sessionStorage.getItem('liuliu_welcome_seen');
        },
        complete() {
            window.sessionStorage.setItem('liuliu_welcome_seen', '1');
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
            <h2 id="how-we-play-title" class="mt-2 text-2xl font-semibold tracking-tight">我們為什麼成立？</h2>
            <p class="mt-2 text-sm leading-6 text-emerald-50">陌生揪團最大的問題，不是找不到人，而是找不到可以長期一起走的人。</p>
        </div>

        <div class="space-y-5 px-6 py-6">
            <div class="flex gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-lg">⛰️</span>
                <div><h3 class="text-sm font-semibold text-slate-950">讓關係能夠累積</h3><p class="mt-1 text-sm leading-6 text-slate-500">主揪付出、團員品質、信用、責任與安全，都無法在一般揪團裡累積。</p></div>
            </div>
            <div class="flex gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-lg">🧭</span>
                <div><h3 class="text-sm font-semibold text-slate-950">所以我們建立</h3><p class="mt-1 text-sm leading-6 text-slate-500"><strong class="font-semibold text-slate-700">實名制｜信用累積｜公平分工｜品質山友</strong></p></div>
            </div>
            <div class="flex gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-lg">📍</span>
                <div><h3 class="text-sm font-semibold text-slate-950">找到長期同行的山友</h3><p class="mt-1 text-sm leading-6 text-slate-500">不是找人共乘，而是找到值得長期一起登山的人。</p></div>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium leading-6 text-emerald-800"><strong>不是找人共乘，而是找到值得長期一起登山的人。</strong></div>
            <button type="button" class="ui-btn-primary w-full" x-on:click="complete()">我懂了，開始找山友 ⛰️</button>
        </div>
    </section>
</div>
