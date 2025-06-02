/**
 * Wishlist functionality for Movies Theme
 */

(function($) {
  'use strict'

  $(document).ready(function() {
    initWishlist()
  })

  function initWishlist() {
    $(document).on('click', '.wishlist-btn', function(e) {
      e.preventDefault()

      const $btn = $(this)
      const movieId = $btn.data('movie-id')
      const isInWishlist = $btn.hasClass('in-wishlist')

      // Prevent double clicks
      if ($btn.hasClass('loading')) {
        return
      }

      $btn.addClass('loading')

      $.ajax({
        url: movies_ajax.ajax_url,
        type: 'POST',
        data: {
          action: isInWishlist ? 'remove_from_wishlist' : 'add_to_wishlist',
          nonce: movies_ajax.nonce,
          movie_id: movieId
        },
        success: function(response) {
          if (response.success) {
            $btn.toggleClass('in-wishlist')
            $btn.text(isInWishlist ? 'Add to Wishlist' : 'Remove from Wishlist')

            // Update wishlist count if element exists
            if (response.data.count !== undefined) {
              $('.wishlist-count').text(response.data.count)
            }

            // Show notification
            showNotification(response.data.message, 'success')
          } else {
            showNotification(response.data.message || 'An error occurred', 'error')
          }
        },
        error: function() {
          showNotification('Network error occurred', 'error')
        },
        complete: function() {
          $btn.removeClass('loading')
        }
      })
    })
  }

  function showNotification(message, type) {
    const $notification = $('<div class="notification notification-' + type + '">' + message + '</div>')
    $('body').append($notification)

    setTimeout(function() {
      $notification.addClass('show')
    }, 100)

    setTimeout(function() {
      $notification.removeClass('show')
      setTimeout(function() {
        $notification.remove()
      }, 300)
    }, 3000)
  }
})(jQuery)
