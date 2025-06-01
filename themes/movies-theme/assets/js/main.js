/**
 * Main JavaScript file for Movies Theme
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initMobileMenu();
        initSmoothScrolling();
        initImageLazyLoading();
        initTooltips();
    });

    /**
     * Initialize mobile menu toggle
     */
    function initMobileMenu() {
        $('.menu-toggle').on('click', function() {
            $('.nav-menu').toggleClass('toggled');
            $(this).attr('aria-expanded', function(i, attr) {
                return attr === 'true' ? 'false' : 'true';
            });
        });

        // Close menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.main-navigation').length) {
                $('.nav-menu').removeClass('toggled');
                $('.menu-toggle').attr('aria-expanded', 'false');
            }
        });
    }

    /**
     * Initialize smooth scrolling for anchor links
     */
    function initSmoothScrolling() {
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 800);
            }
        });
    }

    /**
     * Initialize lazy loading for images
     */
    function initImageLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        $('[data-tooltip]').hover(
            function() {
                var tooltip = $('<div class="tooltip"></div>').text($(this).data('tooltip'));
                $('body').append(tooltip);
                
                var pos = $(this).offset();
                tooltip.css({
                    top: pos.top - tooltip.outerHeight() - 10,
                    left: pos.left + ($(this).outerWidth() / 2) - (tooltip.outerWidth() / 2)
                }).fadeIn();
            },
            function() {
                $('.tooltip').remove();
            }
        );
    }

    /**
     * Utility function to debounce events
     */
    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }

    // Expose utility functions globally
    window.MoviesTheme = {
        debounce: debounce
    };

})(jQuery); 