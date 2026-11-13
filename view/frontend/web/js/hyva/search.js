(function () {
    function initBlogSearch() {
        return {
            open: false,
            loading: false,
            suggestions: [],
            showImage: false,
            searchUrl: '',
            searchPageUrl: '',
            minChars: 1,
            limit: 10,
            requestId: 0,
            init() {
                const d = this.$el.dataset;
                this.searchUrl = d.searchUrl || '';
                this.searchPageUrl = d.searchPageUrl || '';
                this.minChars = parseInt(d.minChars, 10) || 1;
                this.limit = parseInt(d.searchLimit, 10) || 10;
                this.showImage = d.showImage === '1';
            },
            get hasSuggestions() {
                return this.suggestions.length > 0;
            },
            get showEmpty() {
                return !this.loading && this.suggestions.length === 0;
            },
            get showList() {
                return !this.loading && this.suggestions.length > 0;
            },
            fetchSuggestions() {
                const term = (this.$refs.input.value || '').trim();
                if (term.length < this.minChars) {
                    this.suggestions = [];
                    this.loading = false;
                    this.open = false;
                    return;
                }
                this.loading = true;
                this.open = true;
                const self = this;
                const id = ++this.requestId;
                fetch(this.searchUrl + '?query=' + encodeURIComponent(term), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (id !== self.requestId) {
                            return;
                        }
                        const raw = (data && data.suggestions) ? data.suggestions : [];
                        self.suggestions = raw
                            .slice(0, self.limit)
                            .map(function (s) {
                                return {
                                    value: s.value || '',
                                    url: s.url || '',
                                    desc: s.desc || '',
                                    image: s.image || '',
                                    hasImage: self.showImage && !!s.image
                                };
                            });
                        self.loading = false;
                    })
                    .catch(function () {
                        if (id !== self.requestId) {
                            return;
                        }
                        self.suggestions = [];
                        self.loading = false;
                    });
            },
            submitSearch() {
                const term = (this.$refs.input.value || '').trim();
                if (!term) {
                    this.$refs.input.focus();
                    return;
                }
                window.location.href = this.searchPageUrl + '?query=' + encodeURIComponent(term);
            },
            closeSuggestions() {
                this.open = false;
            }
        };
    }
    window.addEventListener('alpine:init', function () {
        window.Alpine.data('initBlogSearch', initBlogSearch);
    }, { once: true });
})();
