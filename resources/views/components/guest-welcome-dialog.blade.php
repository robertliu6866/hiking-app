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
    aria-labelledby="welcome-dialog-title"
>
    <section x-show="open" x-transition.scale.origin.bottom class="max-h-[calc(100dvh-2rem)] w-full max-w-[430px] overflow-y-auto rounded-[2rem] bg-white shadow-2xl">
        <div class="bg-[linear-gradient(135deg,#064e3b_0%,#059669_100%)] px-6 pb-7 pt-6 text-white">
            <span class="text-2xl" aria-hidden="true">⛰️</span>
            <h2 id="welcome-dialog-title" class="mt-2 text-2xl font-semibold tracking-tight">喜歡爬山，所以一直回來</h2>
            <p class="mt-2 text-sm leading-6 text-emerald-50">只因為爬山帶來的平靜，以及下山後，那種重新充滿活力的感覺。</p>
        </div>

        <div class="space-y-4 px-6 py-6 text-sm leading-6 text-slate-600">
            <p>只是隨著時間過去，曾經一起爬山的夥伴，總會因為工作、家庭、生活而慢慢離開。</p>
            <p>但你還是想爬山。</p>
            <p>所以這裡想做的事情很簡單：</p>
            <p class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 font-medium leading-6 text-emerald-800"><strong>當你這週想上山，就能找到適合一起出發的夥伴。</strong></p>
            <p>不管你是想完成百岳目標、找固定山友，還是剛開始爬山，希望有人陪你安全入門，都可以從這裡開始。</p>
            <p>我們希望建立一個規則明確、資訊透明的登山社群。大家知道自己的分工，也知道彼此的能力與經驗，不用每次都從頭找人、重新磨合。</p>
            <p class="text-center font-medium leading-6 text-slate-800"><strong>找到對的夥伴，方便出門。<br>快樂上山，安全下山。</strong></p>
            <button type="button" class="ui-btn-primary w-full" x-on:click="complete()">我懂了，開始找山友 ⛰️</button>
        </div>
    </section>
</div>
