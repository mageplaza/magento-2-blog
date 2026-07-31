/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('mpblog-sidebar-search');
    if (!container) {
        return;
    }

    var visibleImage = container.dataset.showImage === '1';
    var searchBox = document.getElementById('mpblog-search-box');
    var minChars = parseInt(container.dataset.minChars, 10) || 1;
    var searchUrl = container.dataset.searchUrl || '';
    var limitPost = parseInt(container.dataset.searchLimit, 10) || 10;
    var searchTimer = null;

    if (!searchBox) {
        return;
    }

    searchBox.addEventListener('input', function () {
        var searchTerm = searchBox.value;
        clearTimeout(searchTimer);
        if (!searchTerm || searchTerm.length < minChars) {
            updateAutocompleteResults([], searchTerm);
            return;
        }
        searchTimer = setTimeout(function () {
            fetchSuggestions(searchTerm);
        }, 250);
    });

    function fetchSuggestions(searchTerm) {
        fetch(searchUrl + '?query=' + encodeURIComponent(searchTerm), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                updateAutocompleteResults((data && data.suggestions) ? data.suggestions : [], searchTerm);
            })
            .catch(function () {});
    }

    function updateAutocompleteResults(suggestions, searchTerm) {
        var autocompleteResults = document.getElementById('autocomplete-results');
        autocompleteResults.innerHTML = '';
        if (searchTerm && searchTerm.length >= minChars) {
            if (suggestions.length > 0) {
                autocompleteResults.style.display = 'block';
                var existedPost = false;
                var checkNumberPostRender = 0;
                suggestions.forEach(function (suggestion) {
                    if (suggestion.value.toLocaleLowerCase().includes(searchTerm.toLowerCase())) {
                        checkNumberPostRender++;
                        if (checkNumberPostRender > limitPost) {
                            return;
                        }
                        existedPost = true;
                        var suggestionDiv = document.createElement('div');
                        suggestionDiv.className = 'mpblog-suggestion';

                        var suggestionDivParrent = document.createElement('div');
                        suggestionDivParrent.className = 'autocomplete-suggestion list-post-tabs cursor-pointer';

                        if (visibleImage) {
                            var suggestionLeftDiv = document.createElement('div');
                            suggestionLeftDiv.className = 'mpblog-suggestion-left';
                            var img = document.createElement('img');
                            img.className = 'img-responsive';
                            img.src = suggestion.image;
                            suggestionLeftDiv.appendChild(img);
                            suggestionDiv.appendChild(suggestionLeftDiv);
                        }

                        var suggestionRightDiv = document.createElement('div');
                        suggestionRightDiv.className = 'mpblog-suggestion-right ' + (visibleImage ? 'image-visible' : '');

                        var productLineDiv = document.createElement('div');
                        productLineDiv.className = 'mpblog-product-line mpblog-product-name';
                        productLineDiv.textContent = suggestion.value;
                        suggestionRightDiv.appendChild(productLineDiv);

                        var productDesDiv = document.createElement('div');
                        productDesDiv.className = 'mpblog-product-des';
                        var shortDesP = document.createElement('p');
                        shortDesP.className = 'mpblog-short-des';
                        shortDesP.textContent = suggestion.desc;
                        productDesDiv.appendChild(shortDesP);

                        suggestionRightDiv.appendChild(productDesDiv);
                        suggestionDiv.appendChild(suggestionRightDiv);
                        suggestionDivParrent.appendChild(suggestionDiv);
                        suggestionDivParrent.addEventListener('click', function () {
                            window.location.href = suggestion.url;
                        });
                        autocompleteResults.appendChild(suggestionDivParrent);
                    }
                });
                if (!existedPost) {
                    var noResultDiv = document.createElement('div');
                    noResultDiv.className = 'autocomplete-suggestion';
                    noResultDiv.innerText = 'No results';
                    autocompleteResults.appendChild(noResultDiv);
                }
            } else {
                autocompleteResults.style.display = 'none';
            }
        } else {
            autocompleteResults.style.display = 'none';
        }
    }

    searchBox.addEventListener('blur', function () {
        setTimeout(function () {
            var autocompleteResults = document.getElementById('autocomplete-results');
            autocompleteResults.style.display = 'none';
        }, 150);
    });
});
