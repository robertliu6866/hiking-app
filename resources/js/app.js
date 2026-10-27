import './bootstrap';

document.addEventListener('alpine:init', () => {
    Alpine.data('tripSearch', () => ({
        searching: false,
        searchError: false,
        timer: null,
        controller: null,
        version: 0,

        queueSearch(event) {
            // Wait until Chinese input composition has finished.
            if (event.isComposing) return;
            clearTimeout(this.timer);
            this.controller?.abort();
            this.version++;
            this.timer = setTimeout(() => this.search(), 300);
        },

        paginate(event) {
            const link = event.target.closest('nav[aria-label="行程換頁"] a');
            if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            this.search(link.href);
        },

        async search(pageUrl = null) {
            clearTimeout(this.timer);
            this.controller?.abort();
            const version = ++this.version;
            const controller = new AbortController();
            this.controller = controller;
            const url = new URL(pageUrl || this.$refs.searchForm.action);
            if (!pageUrl) {
                url.search = new URLSearchParams(new FormData(this.$refs.searchForm)).toString();
            }
            this.searching = true;
            this.searchError = false;
            try {
                const response = await fetch(url, {
                    signal: controller.signal,
                    headers: { 'X-Trip-Search': '1' },
                });
                if (response.redirected) {
                    window.location.assign(response.url);
                    return;
                }
                if (!response.ok) throw new Error('Search failed');
                const html = await response.text();
                if (version !== this.version) return;
                // Alpine and Livewire observe inserted cards and initialize their controls.
                this.$refs.results.innerHTML = html;
                history.replaceState(history.state, '', url);
                // Keep the duration/month links in sync with the current search.
                this.$el.querySelectorAll('a').forEach(link => {
                    const target = new URL(link.href);
                    if (target.pathname !== url.pathname || !target.search || target.searchParams.has('page')) return;
                    for (const name of ['q', 'group', 'sort']) {
                        target.searchParams.set(name, url.searchParams.get(name) || '');
                    }
                    link.href = target.toString();
                });
            } catch (error) {
                if (error.name !== 'AbortError' && version === this.version) this.searchError = true;
            } finally {
                if (version === this.version) this.searching = false;
            }
        },

        destroy() {
            clearTimeout(this.timer);
            this.controller?.abort();
        },
    }));
});
