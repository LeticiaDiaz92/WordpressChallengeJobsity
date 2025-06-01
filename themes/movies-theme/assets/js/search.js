/**
 * Search functionality for Movies Theme
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initLiveSearch();
    });

    function initLiveSearch() {
        var $searchField = $('.search-field');
        var $searchResults = $('.search-results');
        var searchTimeout;

        $searchField.on('input', function() {
            var query = $(this).val().trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 3) {
                $searchResults.hide();
                return;
            }
            
            searchTimeout = setTimeout(function() {
                performSearch(query);
            }, 300);
        });

        // Hide results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-form').length) {
                $searchResults.hide();
            }
        });
    }

    function performSearch(query) {
        var $searchResults = $('.search-results');
        
        $.ajax({
            url: movies_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'live_search',
                nonce: movies_ajax.nonce,
                query: query
            },
            beforeSend: function() {
                $searchResults.html('<div class="search-loading">Searching...</div>').show();
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.results.length > 0) {
                        var html = '<div class="search-results-list">';
                        response.data.results.forEach(function(item) {
                            html += '<div class="search-result-item">';
                            html += '<a href="' + item.url + '">';
                            if (item.thumbnail) {
                                html += '<img src="' + item.thumbnail + '" alt="' + item.title + '">';
                            }
                            html += '<div class="search-result-content">';
                            html += '<h4>' + item.title + '</h4>';
                            html += '<p>' + item.excerpt + '</p>';
                            html += '<span class="search-result-type">' + item.type + '</span>';
                            html += '</div>';
                            html += '</a>';
                            html += '</div>';
                        });
                        html += '</div>';
                        $searchResults.html(html);
                    } else {
                        $searchResults.html('<div class="search-no-results">No results found</div>');
                    }
                } else {
                    $searchResults.html('<div class="search-error">Search error occurred</div>');
                }
            },
            error: function() {
                $searchResults.html('<div class="search-error">Network error occurred</div>');
            }
        });
    }

})(jQuery); 