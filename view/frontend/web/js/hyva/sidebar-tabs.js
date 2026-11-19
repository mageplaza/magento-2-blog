(function () {
    function blogSidebarTabs() {
        return {
            active: 'popular',
            get popularActive() { return this.active === 'popular'; },
            get latestActive() { return this.active === 'latest'; },
            showPopular() { this.active = 'popular'; },
            showLatest() { this.active = 'latest'; }
        };
    }
    window.addEventListener('alpine:init', function () {
        window.Alpine.data('blogSidebarTabs', blogSidebarTabs);
    }, { once: true });
})();
