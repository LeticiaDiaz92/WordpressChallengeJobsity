# Movies Theme - Technical Documentation

## Overview

Movies Theme is a custom WordPress theme designed specifically for movie and actor content management. Built to work seamlessly with the TMDB API Connector plugin, it provides a complete frontend solution for displaying movies, actors, and upcoming releases with advanced filtering, search functionality, and user wishlists.

## Theme Information

- **Version**: 1.0.0
- **WordPress Compatibility**: 5.0+
- **License**: GPL-2.0-or-later
- **Author**: Custom Development
- **Description**: A responsive WordPress theme for movie and actor management with Sass compilation

## Architecture

### Core Files Structure

```
movies-theme/
├── assets/
│   ├── css/           # Compiled CSS files
│   ├── scss/          # Sass source files
│   ├── js/            # JavaScript files
│   └── images/        # Theme images
├── components/        # Reusable PHP components
├── inc/               # Theme functionality modules
├── template-parts/    # Template partials
├── functions.php      # Main theme functions
├── style.css         # Theme stylesheet header
├── index.php         # Main template fallback
├── header.php        # Site header
├── footer.php        # Site footer
```

## Custom Post Types

The theme registers and handles three custom post types:

### 1. Movies (`movie`)
```php
register_post_type('movie', array(
    'labels' => array(
        'name' => 'Movies',
        'singular_name' => 'Movie'
    ),
    'public' => true,
    'has_archive' => true,
    'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
    'rewrite' => array('slug' => 'movies'),
    'show_in_rest' => true
));
```

### 2. Actors (`actor`)
```php
register_post_type('actor', array(
    'labels' => array(
        'name' => 'Actors',
        'singular_name' => 'Actor'
    ),
    'public' => true,
    'has_archive' => true,
    'supports' => array('title', 'editor', 'thumbnail'),
    'rewrite' => array('slug' => 'actors'),
    'show_in_rest' => true
));
```

### 3. Upcoming Movies (`upcoming`)
```php
register_post_type('upcoming', array(
    'labels' => array(
        'name' => 'Upcoming Movies',
        'singular_name' => 'Upcoming Movie'
    ),
    'public' => true,
    'has_archive' => false,
    'supports' => array('title', 'editor', 'thumbnail'),
    'show_in_rest' => true
));
```

## Theme Features

### 1. Responsive Design
- Mobile-first approach
- Flexible grid system
- Touch-friendly interfaces
- Optimized for all screen sizes

### 2. Advanced Search & Filtering
- AJAX-powered filtering
- Real-time search results
- Multiple sort options
- Genre and year filtering

### 3. User Wishlist System
- User-specific movie wishlists
- AJAX wishlist management
- Persistent storage in user meta
- Guest session support

### 4. Image Optimization
- Multiple image sizes for different contexts
- Lazy loading implementation
- Responsive image handling
- TMDB API image integration

### 5. SEO Optimization
- Structured data for movies and actors
- Meta tag optimization
- Clean URL structure
- Search engine friendly markup

## Template Files

### Archive Templates
- `archive-movie.php` - Movies archive with filtering
- `archive-actor.php` - Actors archive with search
- `index.php` - Default archive fallback

### Single Templates
- `single-movie.php` - Individual movie display
- `single-actor.php` - Individual actor profile
- `single-upcoming.php` - Upcoming movie details

### Page Templates
- `page-homepage.php` - Custom homepage template
- `page-wishlist.php` - User wishlist page
- `search.php` - Search results template

### Utility Templates
- `header.php` - Site header with navigation
- `footer.php` - Site footer
- `sidebar.php` - Sidebar content
- `searchform.php` - Custom search form

## JavaScript Architecture

### Core Scripts
- `main.js` - Base functionality and utilities
- `ajax-filters.js` - Movie filtering functionality
- `ajax-actor-filters.js` - Actor filtering functionality
- `wishlist.js` - Wishlist core functionality
- `wishlist-ajax.js` - AJAX wishlist operations
- `auth-modals.js` - Authentication modals
- `search.js` - Enhanced search functionality

### Build System
```json
{
  "scripts": {
    "build": "npm run build:css && npm run build:js",
    "dev": "npm run watch",
    "watch": "npm run watch:css & npm run watch:js",
    "lint:scss": "stylelint 'assets/scss/**/*.scss' --fix",
    "lint:js": "eslint assets/js/*.js --ignore-pattern '*.min.js' --fix"
  }
}
```

## Sass/CSS Architecture

### SCSS Structure
```
assets/scss/
├── main.scss          # Main entry point
├── _variables.scss    # Global variables
├── _mixins.scss       # Sass mixins
├── _base.scss         # Base styles
├── _layout.scss       # Layout components
├── _components.scss   # UI components
└── _responsive.scss   # Media queries
```

### CSS Compilation
- Source maps for development
- Compressed output for production
- Autoprefixer for browser compatibility
- PostCSS processing pipeline

## PHP Module System

### Core Modules (`/inc/`)

#### 1. Custom Post Types
- `custom-post-type-movies.php` - Movie post type and meta fields
- `custom-post-type-actors.php` - Actor post type and relationships  
- `custom-post-type-upcoming.php` - Upcoming movies management

#### 2. AJAX Handlers
- `ajax-handlers.php` - Server-side AJAX processing
- Movie filtering endpoints
- Actor search endpoints
- Wishlist operations

#### 3. API Integration
- `api-functions.php` - TMDB API integration helpers
- Data fetching and caching
- Image processing utilities
- Error handling

#### 4. User Functions
- `user-functions.php` - User management and authentication
- Wishlist user meta handling
- Session management
- Capability checks

#### 5. Utility Modules
- `helpers.php` - General utility functions
- `widgets.php` - Custom widgets
- `blocks.php` - Gutenberg block support
- `custom-fields.php` - Meta field definitions

## AJAX Functionality

### Movie Filtering
```php
// Filter movies by genre, year, rating
add_action('wp_ajax_filter_movies', 'handle_movie_filter');
add_action('wp_ajax_nopriv_filter_movies', 'handle_movie_filter');
```

### Actor Search
```php
// Search actors with AJAX
add_action('wp_ajax_filter_actors', 'handle_actor_filter');
add_action('wp_ajax_nopriv_filter_actors', 'handle_actor_filter');
```

### Wishlist Management
```php
// Add/remove movies from wishlist
add_action('wp_ajax_toggle_wishlist', 'handle_wishlist_toggle');
add_action('wp_ajax_get_wishlist', 'get_user_wishlist');
```

## Custom Fields & Meta Data

### Movie Meta Fields
- `tmdb_id` - TMDB API identifier
- `release_date` - Movie release date
- `runtime` - Duration in minutes
- `popularity` - TMDB popularity score
- `vote_average` - Average rating
- `production_companies` - Production company data
- `credits` - Cast and crew information
- `videos` - Trailers and clips
- `reviews` - User reviews

### Actor Meta Fields
- `tmdb_id` - TMDB person identifier
- `birthday` - Birth date
- `place_of_birth` - Birth location
- `popularity` - TMDB popularity score
- `movie_credits` - Filmography data
- `images` - Actor photo gallery

## Search Enhancement

### Custom Search Query Modification
```php
function movies_extend_search_functionality($search, $wp_query) {
    // Enhanced search across multiple post types
    // Custom field searching
    // Relevance scoring
}
```

### Search Features
- Multi-post-type search (movies, actors, upcoming)
- Custom field content searching
- Weighted relevance scoring
- Real-time search suggestions

## Performance Optimization

### Caching Strategy
- Transient API for expensive queries
- Object caching for frequently accessed data
- Image lazy loading
- Minified and concatenated assets

### Database Optimization
- Efficient meta queries
- Proper indexing usage
- Batch processing for large datasets
- Query result caching

## Security Measures

### Data Sanitization
```php
$search_term = sanitize_text_field($_POST['search']);
$movie_id = absint($_POST['movie_id']);
$user_input = wp_kses_post($_POST['content']);
```

### AJAX Security
```php
// Nonce verification for all AJAX requests
if (!wp_verify_nonce($_POST['nonce'], 'movies_nonce')) {
    wp_die('Security check failed');
}
```

### Capability Checks
```php
if (!current_user_can('read')) {
    wp_die(__('Insufficient permissions'));
}
```

## Theme Configuration

### Theme Support Features
```php
add_theme_support('post-thumbnails');
add_theme_support('title-tag');
add_theme_support('html5');
add_theme_support('custom-logo');
```

### Image Sizes
```php
add_image_size('movie-thumbnail', 300, 450, true);
add_image_size('actor-thumbnail', 200, 200, true);
add_image_size('hero-banner', 1920, 800, true);
```

### Navigation Menus
```php
register_nav_menus(array(
    'primary' => __('Primary Menu', 'movies-theme'),
    'footer'  => __('Footer Menu', 'movies-theme'),
));
```

## Development Workflow

### Prerequisites
- Node.js (v14+)
- npm or yarn
- WordPress development environment
- TMDB API Connector plugin

### Setup Instructions
1. Clone/download theme to WordPress themes directory
2. Run `npm install` to install dependencies
3. Run `npm run dev` for development mode
4. Run `npm run build` for production build

### Development Commands
```bash
npm run dev        # Start development with file watching
npm run build      # Build production assets
npm run lint:scss  # Lint Sass files
npm run lint:js    # Lint JavaScript files
npm run clean      # Remove compiled assets
```

## Browser Compatibility

### Supported Browsers
- Chrome (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Edge (last 2 versions)
- Internet Explorer 11+

### Progressive Enhancement
- Core functionality works without JavaScript
- Enhanced features added progressively
- Graceful degradation for older browsers

## API Integration

### TMDB API Connector Compatibility
- Seamless integration with plugin data
- Automatic image handling
- Meta field synchronization
- Cache invalidation coordination

### External Dependencies
- Font Awesome 6.5.1 (CDN)
- Google Fonts (optional)
- TMDB API imagery

## Troubleshooting

### Common Issues

#### AJAX Not Working
- Check nonce verification
- Verify AJAX URL configuration
- Confirm user capabilities

#### Images Not Loading
- Verify TMDB API connector is active
- Check image size registration
- Confirm media permissions

#### Search Results Empty
- Verify custom post types are public
- Check search query modifications
- Confirm database content exists

### Debug Mode
Enable WordPress debug mode for development:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Extensibility

### Custom Hooks
The theme provides custom hooks for extensions:

```php
// Movie display customization
do_action('movies_before_movie_content', $movie_id);
do_action('movies_after_movie_content', $movie_id);

// Filter customization  
apply_filters('movies_filter_options', $options);
apply_filters('movies_search_results', $results, $query);
```

### Child Theme Support
Create a child theme for customizations:
```php
// In child theme functions.php
function child_theme_setup() {
    // Custom functionality
}
add_action('after_setup_theme', 'child_theme_setup', 11);
```

## Changelog

### Version 1.0.0
- Initial release
- Complete movie and actor management system
- AJAX filtering and search
- User wishlist functionality
- Responsive design implementation
- TMDB API integration
- Performance optimizations

## Support & Maintenance

### Regular Updates
- Security patches
- WordPress compatibility
- Bug fixes
- Performance improvements

### Monitoring
- Error logging
- Performance tracking
- User experience analytics
- Search functionality metrics

## Contributing

### Development Standards
- Follow WordPress coding standards
- Use semantic versioning
- Write comprehensive documentation
- Include unit tests where applicable
- Maintain backward compatibility

### Code Review Process
1. Create feature branch
2. Implement changes
3. Test thoroughly
4. Submit pull request
5. Code review and approval
6. Merge to main branch 