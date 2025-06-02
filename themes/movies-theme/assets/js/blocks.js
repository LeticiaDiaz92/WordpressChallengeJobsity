(function(wp) {
  const { registerBlockType } = wp.blocks
  const { InspectorControls, useBlockProps } = wp.blockEditor
  const { PanelBody, RangeControl, ToggleControl } = wp.components
  const { __ } = wp.i18n
  const { Fragment } = wp.element

  // Register Upcoming Movies Block
  registerBlockType('movies-theme/upcoming-movies', {
    title: __('Upcoming Movies', 'movies-theme'),
    description: __('Display upcoming movies from TMDB', 'movies-theme'),
    category: 'movies-theme',
    icon: 'video-alt2',
    supports: {
      align: ['wide', 'full']
    },
    attributes: {
      limit: {
        type: 'number',
        default: 5
      },
      showDate: {
        type: 'boolean',
        default: true
      },
      showGenre: {
        type: 'boolean',
        default: true
      }
    },
    edit: function(props) {
      const { attributes, setAttributes } = props
      const { limit, showDate, showGenre } = attributes
      const blockProps = useBlockProps()

      return (
        wp.element.createElement(Fragment, null,
          wp.element.createElement(InspectorControls, null,
            wp.element.createElement(PanelBody, { title: __('Settings', 'movies-theme'), initialOpen: true },
              wp.element.createElement(RangeControl, {
                label: __('Number of movies', 'movies-theme'),
                value: limit,
                onChange: (value) => setAttributes({ limit: value }),
                min: 1,
                max: 20
              }),
              wp.element.createElement(ToggleControl, {
                label: __('Show release date', 'movies-theme'),
                checked: showDate,
                onChange: (value) => setAttributes({ showDate: value })
              }),
              wp.element.createElement(ToggleControl, {
                label: __('Show genres', 'movies-theme'),
                checked: showGenre,
                onChange: (value) => setAttributes({ showGenre: value })
              })
            )
          ),
          wp.element.createElement('div', blockProps,
            wp.element.createElement('div', { className: 'upcoming-movies-block-preview' },
              wp.element.createElement('h3', null, __('Upcoming Movies', 'movies-theme') + ' (' + limit + ')'),
              wp.element.createElement('p', null, __('This block will display upcoming movies on the frontend.', 'movies-theme')),
              wp.element.createElement('ul', null,
                wp.element.createElement('li', null, __('Show dates:', 'movies-theme') + ' ' + (showDate ? __('Yes', 'movies-theme') : __('No', 'movies-theme'))),
                wp.element.createElement('li', null, __('Show genres:', 'movies-theme') + ' ' + (showGenre ? __('Yes', 'movies-theme') : __('No', 'movies-theme')))
              )
            )
          )
        )
      )
    },
    save: function() {
      return null // Dynamic block rendered on server
    }
  })

  // Register Popular Actors Block
  registerBlockType('movies-theme/popular-actors', {
    title: __('Popular Actors', 'movies-theme'),
    description: __('Display popular actors from TMDB', 'movies-theme'),
    category: 'movies-theme',
    icon: 'groups',
    supports: {
      align: ['wide', 'full']
    },
    attributes: {
      limit: {
        type: 'number',
        default: 10
      },
      showPhoto: {
        type: 'boolean',
        default: true
      },
      showBio: {
        type: 'boolean',
        default: false
      }
    },
    edit: function(props) {
      const { attributes, setAttributes } = props
      const { limit, showPhoto, showBio } = attributes
      const blockProps = useBlockProps()

      return (
        wp.element.createElement(Fragment, null,
          wp.element.createElement(InspectorControls, null,
            wp.element.createElement(PanelBody, { title: __('Settings', 'movies-theme'), initialOpen: true },
              wp.element.createElement(RangeControl, {
                label: __('Number of actors', 'movies-theme'),
                value: limit,
                onChange: (value) => setAttributes({ limit: value }),
                min: 1,
                max: 20
              }),
              wp.element.createElement(ToggleControl, {
                label: __('Show photos', 'movies-theme'),
                checked: showPhoto,
                onChange: (value) => setAttributes({ showPhoto: value })
              }),
              wp.element.createElement(ToggleControl, {
                label: __('Show bio excerpt', 'movies-theme'),
                checked: showBio,
                onChange: (value) => setAttributes({ showBio: value })
              })
            )
          ),
          wp.element.createElement('div', blockProps,
            wp.element.createElement('div', { className: 'popular-actors-block-preview' },
              wp.element.createElement('h3', null, __('Popular Actors', 'movies-theme') + ' (' + limit + ')'),
              wp.element.createElement('p', null, __('This block will display popular actors on the frontend.', 'movies-theme')),
              wp.element.createElement('ul', null,
                wp.element.createElement('li', null, __('Show photos:', 'movies-theme') + ' ' + (showPhoto ? __('Yes', 'movies-theme') : __('No', 'movies-theme'))),
                wp.element.createElement('li', null, __('Show bio:', 'movies-theme') + ' ' + (showBio ? __('Yes', 'movies-theme') : __('No', 'movies-theme')))
              )
            )
          )
        )
      )
    },
    save: function() {
      return null // Dynamic block rendered on server
    }
  })
})(window.wp)
