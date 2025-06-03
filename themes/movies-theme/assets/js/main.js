/**
 * Main JavaScript file for Movies Theme
 */

jQuery(document).ready(function($) {
  // Movie filters functionality
  if ($('.movies-filters').length) {
    initMovieFilters()
  }

  // Initialize other components
  initScrollToTop()
  initMobileMenu()
  initHeaderAccount()
})

/**
 * Initialize movie filters functionality
 */
function initMovieFilters() {
  const $ = jQuery
  const $form = $('.filters-form')
  const $submitButton = $('.filter-submit')

  // Auto-submit on select change (optional UX improvement)
  $form.on('change', 'select', function() {
    // Optional: uncomment to auto-submit on select change
    // $form.submit();
  })

  // Handle form submission
  $form.on('submit', function(e) {
    $submitButton.prop('disabled', true).text('Filtering...')

    // Clean up empty values before submitting
    $form.find('input, select').each(function() {
      if ($(this).val() === '') {
        $(this).prop('disabled', true)
      }
    })

    // Form will submit normally after this
  })

  // Clear filters functionality
  $('.filter-reset').on('click', function(e) {
    e.preventDefault()

    // Reset form
    $form[0].reset()

    // Redirect to clean URL
    window.location.href = $(this).attr('href')
  })

  // Enable/disable submit button based on form state
  function updateSubmitButton() {
    const hasValues = $form.find('input, select').filter(function() {
      return $(this).val() !== ''
    }).length > 0

    if (hasValues) {
      $submitButton.removeClass('disabled')
    } else {
      $submitButton.addClass('disabled')
    }
  }

  // Check initial state
  updateSubmitButton()

  // Update on input changes
  $form.on('input change', 'input, select', updateSubmitButton)
}

/**
 * Initialize scroll to top functionality
 */
function initScrollToTop() {
  const $ = jQuery

  // Create scroll to top button if it doesn't exist
  if (!$('.scroll-to-top').length) {
    $('body').append('<button class="scroll-to-top" title="Back to top">↑</button>')
  }

  const $scrollBtn = $('.scroll-to-top')

  // Show/hide scroll button
  $(window).on('scroll', function() {
    if ($(window).scrollTop() > 300) {
      $scrollBtn.addClass('visible')
    } else {
      $scrollBtn.removeClass('visible')
    }
  })

  // Scroll to top on click
  $scrollBtn.on('click', function() {
    $('html, body').animate({
      scrollTop: 0
    }, 300)
  })
}

/**
 * Initialize mobile menu functionality
 */
function initMobileMenu() {
  const $ = jQuery

  $('.mobile-menu-toggle').on('click', function() {
    $(this).toggleClass('active')
    $('.main-navigation').toggleClass('mobile-open')
  })

  // Close mobile menu when clicking outside
  $(document).on('click', function(e) {
    if (!$(e.target).closest('.main-navigation, .mobile-menu-toggle').length) {
      $('.mobile-menu-toggle').removeClass('active')
      $('.main-navigation').removeClass('mobile-open')
    }
  })
}

/**
 * Initialize header account functionality
 */
function initHeaderAccount() {
  const $ = jQuery

  // Login dropdown toggle
  $('.login-toggle').on('click', function(e) {
    e.stopPropagation()
    $('.register-dropdown').removeClass('show')
    $('.user-dropdown').removeClass('show')
    $('.login-dropdown').toggleClass('show')
  })

  // Register dropdown toggle
  $('.register-toggle').on('click', function(e) {
    e.stopPropagation()
    $('.login-dropdown').removeClass('show')
    $('.user-dropdown').removeClass('show')
    $('.register-dropdown').toggleClass('show')
  })

  // User menu dropdown toggle
  $('.user-toggle').on('click', function(e) {
    e.stopPropagation()
    $('.login-dropdown').removeClass('show')
    $('.register-dropdown').removeClass('show')
    $('.user-dropdown').toggleClass('show')
  })

  // Close dropdowns when clicking outside
  $(document).on('click', function() {
    $('.login-dropdown, .register-dropdown, .user-dropdown').removeClass('show')
  })

  // Prevent dropdown close when clicking inside
  $('.login-dropdown, .register-dropdown, .user-dropdown').on('click', function(e) {
    e.stopPropagation()
  })
}

/**
 * Initialize smooth scrolling for anchor links
 */
function initSmoothScrolling() {
  $('a[href^="#"]').on('click', function(e) {
    const target = $(this.getAttribute('href'))
    if (target.length) {
      e.preventDefault()
      $('html, body').animate({
        scrollTop: target.offset().top - 100
      }, 800)
    }
  })
}

/**
 * Initialize lazy loading for images
 */
function initImageLazyLoading() {
  if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target
          img.src = img.dataset.src
          img.classList.remove('lazy')
          imageObserver.unobserve(img)
        }
      })
    })

    document.querySelectorAll('img[data-src]').forEach(img => {
      imageObserver.observe(img)
    })
  }
}

/**
 * Initialize tooltips
 */
function initTooltips() {
  $('[data-tooltip]').hover(
    function() {
      const tooltip = $('<div class="tooltip"></div>').text($(this).data('tooltip'))
      $('body').append(tooltip)

      const pos = $(this).offset()
      tooltip.css({
        top: pos.top - tooltip.outerHeight() - 10,
        left: pos.left + ($(this).outerWidth() / 2) - (tooltip.outerWidth() / 2)
      }).fadeIn()
    },
    function() {
      $('.tooltip').remove()
    }
  )
}

/**
 * Utility function to debounce events
 */
function debounce(func, wait, immediate) {
  let timeout
  return function() {
    const context = this; const args = arguments
    const later = function() {
      timeout = null
      if (!immediate) func.apply(context, args)
    }
    const callNow = immediate && !timeout
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
    if (callNow) func.apply(context, args)
  }
}

// Expose utility functions globally
window.MoviesTheme = {
  debounce
}
