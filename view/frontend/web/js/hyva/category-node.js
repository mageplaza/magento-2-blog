(function () {
    function blogCategoryNode() {
        return {
            open: false,
            init() { this.open = this.$el.dataset.open === 'true'; },
            toggle() { this.open = !this.open; }
        };
    }
    window.addEventListener('alpine:init', function () {
        window.Alpine.data('blogCategoryNode', blogCategoryNode);
    }, { once: true });
})();
