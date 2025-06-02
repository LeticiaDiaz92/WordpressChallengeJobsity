/**
 * AJAX Filters for Actors
 */

/* global actorsAjax, jQuery */

jQuery(document).ready(function($) {
  'use strict'

  // Only initialize on actor archive pages
  if (!$('.actors-filters').length) {
    return
  }

  const ActorFilters = {
    $form: $('.filters-form'),
    $resultsContainer: $('#actors-archive-container'),
    $loadingOverlay: null,
    currentXHR: null,
    searchTimeout: null,
    currentPage: 1,

    init: function() {
      this.createLoadingOverlay()
      this.bindEvents()
      this.loadFromURL()
    },

    createLoadingOverlay: function() {
      this.$loadingOverlay = $(`
        <div class="loading-overlay">
          <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Loading actors...</p>
          </div>
        </div>
      `)
      this.$resultsContainer.append(this.$loadingOverlay)
    },

    bindEvents: function() {
      const self = this

      // Real-time search with debouncing
      this.$form.find('input[name="actor_search"]').on('input', function() {
        clearTimeout(self.searchTimeout)
        self.searchTimeout = setTimeout(function() {
          self.currentPage = 1
          self.performSearch()
        }, 500) // 500ms delay
      })

      // Select changes
      this.$form.find('select').on('change', function() {
        self.currentPage = 1
        self.performSearch()
      })

      // Handle pagination clicks (delegated event)
      $(document).on('click', '.pagination-wrapper a', function(e) {
        e.preventDefault()

        // Extract page number from href or data attributes
        let pageNum = 1
        const href = $(this).attr('href')

        if (href) {
          const url = new URL(href, window.location.origin)
          const extractedPage = url.searchParams.get('paged')
          pageNum = parseInt(extractedPage) || 1
        }

        // Check if this is prev/next or numbered link
        if ($(this).hasClass('prev')) {
          pageNum = Math.max(1, self.currentPage - 1)
        } else if ($(this).hasClass('next')) {
          pageNum = self.currentPage + 1
        } else {
          // For numbered links, try to extract from text if URL parsing failed
          if (pageNum === 1 && href) {
            const linkText = $(this).text().trim()
            const textPageNum = parseInt(linkText)
            if (!isNaN(textPageNum) && textPageNum > 0) {
              pageNum = textPageNum
            }
          }
        }

        self.currentPage = pageNum
        self.performSearch()

        // Scroll to top of results - with safety check
        if (self.$resultsContainer.length && self.$resultsContainer.offset()) {
          $('html, body').animate({
            scrollTop: self.$resultsContainer.offset().top - 100
          }, 300)
        }
      })
    },

    performSearch: function() {
      const self = this

      // Check if we should reload the page instead of performing AJAX
      const searchValue = this.$form.find('input[name="actor_search"]').val().trim()
      const movieValue = this.$form.find('select[name="actor_movie"]').val()
      const orderValue = this.$form.find('select[name="orderby"]').val()

      // If all filters are back to default values, reload the page to show original content
      if (!searchValue && !movieValue && orderValue === 'title' && this.currentPage === 1) {
        // Clear URL parameters and reload
        window.location.href = window.location.pathname
        return
      }

      // Cancel previous request if still running
      if (this.currentXHR && this.currentXHR.readyState !== 4) {
        this.currentXHR.abort()
      }

      // Show loading state
      this.showLoading()

      // Disable form elements during search
      this.$form.find('input, select').prop('disabled', true)

      // Prepare data
      const formData = {
        action: 'filter_actors',
        nonce: actorsAjax.nonce,
        actor_search: searchValue,
        actor_movie: movieValue,
        orderby: orderValue,
        paged: this.currentPage
      }

      // Perform AJAX request
      this.currentXHR = $.ajax({
        url: actorsAjax.ajaxUrl,
        type: 'POST',
        data: formData,
        timeout: 10000, // 10 second timeout

        success: function(response) {
          if (response.success) {
            self.updateResults(response)
            self.updateURL(formData)
            self.updateResultsCount(response.found_posts, formData)
          } else {
            self.showError('Failed to load actors. Please try again.')
          }
        },

        error: function(xhr, status, error) {
          if (status !== 'abort') {
            console.error('AJAX Error:', error, xhr.responseText)
            self.showError('Connection error. Please check your internet connection and try again.')
          }
        },

        complete: function() {
          self.hideLoading()
          self.enableForm()
        }
      })
    },

    updateResults: function(response) {
      // Update actors grid - reemplazar todo el contenido del contenedor de resultados
      const $resultsContainer = $('#actors-results-container')
      $resultsContainer.html(response.html)

      // Update pagination - si hay paginación, agregarla después del grid
      if (response.pagination) {
        $resultsContainer.append(response.pagination)
      }

      // Add fade-in animation
      $('#actors-grid').hide().fadeIn(400)
    },

    updateResultsCount: function(count, filters) {
      const activeFilters = []
      if (filters.actor_search) activeFilters.push('Name')
      if (filters.actor_movie) activeFilters.push('Movie')

      let message
      if (activeFilters.length > 0) {
        message = count === 1
          ? `Found ${count} actor matching filters: ${activeFilters.join(', ')}`
          : `Found ${count} actors matching filters: ${activeFilters.join(', ')}`
      } else {
        message = count === 1
          ? `Showing ${count} actor`
          : `Showing ${count} actors`
      }

      $('.results-count').text(message)
    },

    updateURL: function(data) {
      const url = new URL(window.location)
      const params = url.searchParams

      // Clear existing params
      params.delete('actor_search')
      params.delete('actor_movie')
      params.delete('orderby')
      params.delete('paged')

      // Add new params
      if (data.actor_search) params.set('actor_search', data.actor_search)
      if (data.actor_movie) params.set('actor_movie', data.actor_movie)
      if (data.orderby && data.orderby !== 'title') params.set('orderby', data.orderby)
      if (data.paged > 1) params.set('paged', data.paged)

      // Update URL without page reload
      window.history.replaceState({}, '', url.toString())
    },

    loadFromURL: function() {
      const url = new URL(window.location)
      const params = url.searchParams

      // Set form values from URL
      if (params.get('actor_search')) {
        this.$form.find('input[name="actor_search"]').val(params.get('actor_search'))
      }
      if (params.get('actor_movie')) {
        this.$form.find('select[name="actor_movie"]').val(params.get('actor_movie'))
      }
      if (params.get('orderby')) {
        this.$form.find('select[name="orderby"]').val(params.get('orderby'))
      }
      if (params.get('paged')) {
        this.currentPage = parseInt(params.get('paged'))
      }

      // Only perform search if there are active filters (not default values)
      const hasActiveFilters = (
        (params.get('actor_search') && params.get('actor_search').trim() !== '') ||
        (params.get('actor_movie') && params.get('actor_movie') !== '') ||
        (params.get('orderby') && params.get('orderby') !== 'title') ||
        (params.get('paged') && parseInt(params.get('paged')) > 1)
      )

      if (hasActiveFilters) {
        this.performSearch()
      }
    },

    showLoading: function() {
      this.$loadingOverlay.addClass('visible')
      $('#actors-grid').addClass('loading')
    },

    hideLoading: function() {
      this.$loadingOverlay.removeClass('visible')
      $('#actors-grid').removeClass('loading')
    },

    enableForm: function() {
      this.$form.find('input, select').prop('disabled', false)
    },

    showError: function(message) {
      const $error = $(`
        <div class="error-message">
          <p>${message}</p>
          <button class="retry-btn">Try Again</button>
        </div>
      `)

      $('#actors-grid').parent().html($error)

      // Bind retry button
      $error.find('.retry-btn').on('click', () => {
        this.performSearch()
      })
    }
  }

  // Initialize filters
  ActorFilters.init()
})
