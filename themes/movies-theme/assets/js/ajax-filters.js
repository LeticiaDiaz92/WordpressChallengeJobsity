/**
 * AJAX Filters for Movies Theme
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initAjaxFilters();
    });

    function initAjaxFilters() {
        $('.filters-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $container = $('.movies-grid, .actors-grid');
            var formData = $form.serialize();
            
            // Add loading state
            $container.addClass('loading');
            
            $.ajax({
                url: movies_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'filter_movies',
                    nonce: movies_ajax.nonce,
                    filters: formData
                },
                success: function(response) {
                    if (response.success) {
                        $container.html(response.data.html);
                        updateActiveFilters(formData);
                    }
                },
                error: function() {
                    console.error('Filter request failed');
                },
                complete: function() {
                    $container.removeClass('loading');
                }
            });
        });

        // Remove filter functionality
        $(document).on('click', '.remove-filter', function(e) {
            e.preventDefault();
            var filterName = $(this).data('filter');
            $('input[name="' + filterName + '"], select[name="' + filterName + '"]').val('');
            $('.filters-form').trigger('submit');
        });
    }

    function updateActiveFilters(formData) {
        // Update active filters display
        var $activeFilters = $('.active-filters');
        $activeFilters.empty();
        
        // Parse form data and create filter tags
        var filters = new URLSearchParams(formData);
        filters.forEach(function(value, key) {
            if (value && value !== '') {
                var $tag = $('<span class="filter-tag">' + 
                    key + ': ' + value + 
                    '<button class="remove-filter" data-filter="' + key + '">×</button>' +
                    '</span>');
                $activeFilters.append($tag);
            }
        });
    }

})(jQuery); 