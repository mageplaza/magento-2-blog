
require([
    'jquery'
], function ($) {

    // change id, placeholder of search box
    var blogContainer = $(document).find('#mpblog-list-container');
    if (blogContainer) {
        var searchBox = blogContainer.find('input#search');
        if (searchBox) {
            searchBox.prop('id', 'mpblog-search-box').prop('placeholder', 'Search blogs...')
        }
    }
});
