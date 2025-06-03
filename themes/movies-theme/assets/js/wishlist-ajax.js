/**
 * Wishlist AJAX functionality with notifications
 */
(function($) {
    'use strict';

    class WishlistManager {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            $(document).on('click', '.wishlist-btn', this.handleWishlistClick.bind(this));
            $(document).on('click', '.wishlist-btn--login-required', this.handleLoginRequired.bind(this));
        }

        handleWishlistClick(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const movieId = $btn.data('movie-id');
            const isInWishlist = $btn.hasClass('wishlist-btn--active');

            if ($btn.hasClass('wishlist-btn--loading') || !movieId) {
                return;
            }

            this.toggleWishlist($btn, movieId, isInWishlist);
        }

        handleLoginRequired(e) {
            e.preventDefault();
            // Show login modal instead of dropdown
            $('.login-modal-trigger').trigger('click');
            this.showNotification('Please login to add movies to your wishlist', 'info');
        }

        toggleWishlist($btn, movieId, isInWishlist) {
            const action = isInWishlist ? 'remove_from_wishlist' : 'add_to_wishlist';
            
            this.setButtonLoading($btn, true);

            $.ajax({
                url: movies_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: action,
                    nonce: movies_ajax.nonce,
                    movie_id: movieId
                },
                success: (response) => {
                    this.handleSuccess($btn, response, isInWishlist);
                },
                error: () => {
                    this.handleError($btn);
                },
                complete: () => {
                    this.setButtonLoading($btn, false);
                }
            });
        }

        handleSuccess($btn, response, wasInWishlist) {
            if (response.success) {
                this.updateButton($btn, !wasInWishlist);
                this.updateWishlistCount(response.data.count);
                this.showNotification(response.data.message, 'success');
            } else {
                this.showNotification(response.data.message || 'An error occurred', 'error');
            }
        }

        handleError($btn) {
            this.showNotification('Network error occurred. Please try again.', 'error');
        }

        updateButton($btn, isNowInWishlist) {
            const $text = $btn.find('.wishlist-text');
            
            if (isNowInWishlist) {
                $btn.addClass('wishlist-btn--active');
                $btn.attr('aria-label', 'Remove from wishlist');
                if ($text.length) {
                    $text.text('In Wishlist');
                }
            } else {
                $btn.removeClass('wishlist-btn--active');
                $btn.attr('aria-label', 'Add to wishlist');
                if ($text.length) {
                    $text.text('Add to Wishlist');
                }
            }
        }

        updateWishlistCount(count) {
            $('.wishlist-count').text(count);
            
            // Hide count if zero
            if (count === 0) {
                $('.wishlist-count').hide();
            } else {
                $('.wishlist-count').show();
            }
        }

        setButtonLoading($btn, isLoading) {
            if (isLoading) {
                $btn.addClass('wishlist-btn--loading');
                $btn.prop('disabled', true);
                
                // Add loading animation to icon
                const $icon = $btn.find('.wishlist-icon');
                $icon.removeClass('fa-heart').addClass('fa-spinner fa-spin');
            } else {
                $btn.removeClass('wishlist-btn--loading');
                $btn.prop('disabled', false);
                
                // Restore heart icon
                const $icon = $btn.find('.wishlist-icon');
                $icon.removeClass('fa-spinner fa-spin').addClass('fa-heart');
            }
        }

        showNotification(message, type = 'info') {
            // Remove existing notifications
            $('.movies-notification').remove();
            
            const iconMap = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'info': 'fa-info-circle',
                'warning': 'fa-exclamation-triangle'
            };
            
            const icon = iconMap[type] || iconMap.info;
            
            const $notification = $(`
                <div class="movies-notification movies-notification--${type}">
                    <div class="notification-content">
                        <i class="fas ${icon} notification-icon"></i>
                        <span class="notification-message">${message}</span>
                        <button class="notification-close" type="button">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `);
            
            // Add to page
            $('body').append($notification);
            
            // Show with animation
            setTimeout(() => {
                $notification.addClass('movies-notification--show');
            }, 100);
            
            // Auto-hide after 4 seconds
            const hideTimeout = setTimeout(() => {
                this.hideNotification($notification);
            }, 4000);
            
            // Manual close
            $notification.find('.notification-close').on('click', () => {
                clearTimeout(hideTimeout);
                this.hideNotification($notification);
            });
        }

        hideNotification($notification) {
            $notification.removeClass('movies-notification--show');
            
            setTimeout(() => {
                $notification.remove();
            }, 300);
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        new WishlistManager();
    });

})(jQuery); 