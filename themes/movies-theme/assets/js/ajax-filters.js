/**
 * AJAX Filters for Movies
 */

jQuery(document).ready(function($) {
  'use strict'

  // Only initialize on archive pages
  if (!$('.filters-container').length) {
    return
  }

  const MovieFilters = {
    $form: $('.filters-form'),
    $resultsContainer: $('#movies-archive-container'),
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
            <p>Loading movies...</p>
          </div>
        </div>
      `)
      this.$resultsContainer.append(this.$loadingOverlay)
    },

    bindEvents: function() {
      const self = this

      // Real-time search with debouncing
      this.$form.find('input[name="movie_search"]').on('input', function() {
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
      $(document).on('click', '.pagination-wrapper a.page-numbers', function(e) {
        e.preventDefault()
        const url = new URL(this.href)
        const page = url.searchParams.get('paged') || 1
        self.currentPage = parseInt(page)
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
        action: 'filter_movies',
        nonce: movies_ajax.nonce,
        movie_search: this.$form.find('input[name="movie_search"]').val(),
        movie_year: this.$form.find('select[name="movie_year"]').val(),
        movie_genre: this.$form.find('select[name="movie_genre"]').val(),
        orderby: this.$form.find('select[name="orderby"]').val(),
        paged: this.currentPage
      }

      // Perform AJAX request
      this.currentXHR = $.ajax({
        url: movies_ajax.ajax_url,
        type: 'POST',
        data: formData,
        timeout: 10000, // 10 second timeout

        success: function(response) {
          if (response.success) {
            self.updateResults(response)
            self.updateURL(formData)
            self.updateResultsCount(response.found_posts, formData)
          } else {
            self.showError('Failed to load movies. Please try again.')
          }
        },

        error: function(xhr, status, error) {
          if (status !== 'abort') {
            console.error('AJAX Error:', error)
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
      // Update movies grid
      const $moviesContainer = $('#movies-grid').parent()
      $moviesContainer.html(response.html)

      // Update pagination
      const $paginationContainer = $('.pagination-wrapper')
      if (response.pagination) {
        if ($paginationContainer.length) {
          $paginationContainer.replaceWith(response.pagination)
        } else {
          $('.movies-archive-container').append(response.pagination)
        }
      } else {
        $paginationContainer.remove()
      }

      // Add fade-in animation
      $('#movies-grid').hide().fadeIn(400)
    },

    updateResultsCount: function(count, filters) {
      const activeFilters = []
      if (filters.movie_search) activeFilters.push('Title')
      if (filters.movie_year) activeFilters.push('Year')
      if (filters.movie_genre) activeFilters.push('Genre')

      let message
      if (activeFilters.length > 0) {
        message = count === 1
          ? `Found ${count} movie matching filters: ${activeFilters.join(', ')}`
          : `Found ${count} movies matching filters: ${activeFilters.join(', ')}`
      } else {
        message = count === 1
          ? `Showing ${count} movie`
          : `Showing ${count} movies`
      }

      $('.results-count').text(message)
    },

    updateURL: function(data) {
      const url = new URL(window.location)
      const params = url.searchParams

      // Clear existing params
      params.delete('movie_search')
      params.delete('movie_year')
      params.delete('movie_genre')
      params.delete('orderby')
      params.delete('paged')

      // Add new params
      if (data.movie_search) params.set('movie_search', data.movie_search)
      if (data.movie_year) params.set('movie_year', data.movie_year)
      if (data.movie_genre) params.set('movie_genre', data.movie_genre)
      if (data.orderby && data.orderby !== 'title') params.set('orderby', data.orderby)
      if (data.paged > 1) params.set('paged', data.paged)

      // Update URL without page reload
      window.history.replaceState({}, '', url.toString())
    },

    loadFromURL: function() {
      const url = new URL(window.location)
      const params = url.searchParams

      // Set form values from URL
      if (params.get('movie_search')) {
        this.$form.find('input[name="movie_search"]').val(params.get('movie_search'))
      }
      if (params.get('movie_year')) {
        this.$form.find('select[name="movie_year"]').val(params.get('movie_year'))
      }
      if (params.get('movie_genre')) {
        this.$form.find('select[name="movie_genre"]').val(params.get('movie_genre'))
      }
      if (params.get('orderby')) {
        this.$form.find('select[name="orderby"]').val(params.get('orderby'))
      }
      if (params.get('paged')) {
        this.currentPage = parseInt(params.get('paged'))
      }

      // Only perform search if there are active filters
      if (params.get('movie_search') || params.get('movie_year') ||
          params.get('movie_genre') || params.get('orderby') || params.get('paged')) {
        this.performSearch()
      }
    },

    showLoading: function() {
      this.$loadingOverlay.addClass('visible')
      $('#movies-grid').addClass('loading')
    },

    hideLoading: function() {
      this.$loadingOverlay.removeClass('visible')
      $('#movies-grid').removeClass('loading')
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

      $('#movies-grid').parent().html($error)

      // Bind retry button
      $error.find('.retry-btn').on('click', () => {
        this.performSearch()
      })
    }
  }

  // Initialize filters
  MovieFilters.init()
})
