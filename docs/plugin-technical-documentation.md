# TMDB API Connector Plugin - Technical Documentation

## Overview

The TMDB API Connector is a custom WordPress plugin that handles the integration between WordPress and The Movie Database (TMDB) API. It provides automated data import, synchronization, and management of movie and actor content.

## Architecture

### Core Classes

#### 1. TMDB_API_Connector (Main Plugin Class)
- **File**: `tmdb-api-connector.php`
- **Purpose**: Main plugin initialization and coordination
- **Key Methods**:
  - `get_instance()`: Singleton pattern implementation
  - `init()`: Plugin initialization
  - `schedule_cron_jobs()`: Automated sync scheduling
  - `add_admin_menu()`: Admin interface setup

#### 2. TMDB_API (API Handler)
- **File**: `includes/class-tmdb-api.php`
- **Purpose**: Direct communication with TMDB API
- **Key Features**:
  - API authentication handling
  - Rate limiting compliance
  - Error handling and retries
  - Response caching

#### 3. TMDB_Importer (Data Processing)
- **File**: `includes/class-tmdb-importer.php`
- **Purpose**: Transform TMDB data into WordPress content
- **Functions**:
  - Movie data processing and custom field mapping
  - Actor data processing and relationship building
  - Image handling and media library integration
  - Taxonomy term management

#### 4. TMDB_Scheduler (Automation)
- **File**: `includes/class-tmdb-scheduler.php`
- **Purpose**: Automated content synchronization
- **Features**:
  - Cron job management
  - Batch processing
  - Background updates
  - Conflict resolution

#### 5. TMDB_Admin (Administration Interface)
- **File**: `includes/class-tmdb-admin.php`
- **Purpose**: WordPress admin interface
- **Components**:
  - Settings page
  - Import controls
  - Logging interface
  - Statistics dashboard

#### 6. TMDB_Logger (Logging System)
- **File**: `includes/class-tmdb-logger.php`
- **Purpose**: Activity logging and debugging
- **Features**:
  - Import process logging
  - Error tracking
  - Performance monitoring
  - Debug information

## Data Flow

### Import Process

1. **API Request**: Plugin requests data from TMDB API
2. **Data Validation**: Incoming JSON is validated and sanitized
3. **WordPress Integration**: Data is converted to WordPress post format
4. **Media Processing**: Images are imported to WordPress media library
5. **Relationship Building**: Cast/crew relationships are established
6. **Caching**: Processed data is cached for performance

### Data Mapping

#### Movies (TMDB → WordPress)
```php
$movie_data = array(
    'post_title' => $tmdb_data['title'],
    'post_content' => $tmdb_data['overview'],
    'post_type' => 'movie',
    'meta_input' => array(
        'tmdb_id' => $tmdb_data['id'],
        'release_date' => $tmdb_data['release_date'],
        'runtime' => $tmdb_data['runtime'],
        'popularity' => $tmdb_data['popularity'],
        'vote_average' => $tmdb_data['vote_average'],
        'production_companies' => json_encode($tmdb_data['production_companies']),
        'credits' => json_encode($tmdb_data['credits']),
        'videos' => json_encode($tmdb_data['videos']),
        'reviews' => json_encode($tmdb_data['reviews'])
    )
);
```

#### Actors (TMDB → WordPress)
```php
$actor_data = array(
    'post_title' => $tmdb_data['name'],
    'post_content' => $tmdb_data['biography'],
    'post_type' => 'actor',
    'meta_input' => array(
        'tmdb_id' => $tmdb_data['id'],
        'birthday' => $tmdb_data['birthday'],
        'place_of_birth' => $tmdb_data['place_of_birth'],
        'popularity' => $tmdb_data['popularity'],
        'movie_credits' => json_encode($tmdb_data['movie_credits']),
        'images' => json_encode($tmdb_data['images'])
    )
);
```

## API Integration

### Authentication
- Uses API key authentication
- Keys stored in WordPress options table
- Automatic key validation

### Rate Limiting
- Respects TMDB API limits (40 requests per 10 seconds)
- Implements exponential backoff
- Queue system for batch operations

### Error Handling
```php
try {
    $response = $this->make_api_request($endpoint);
    $this->process_response($response);
} catch (TMDB_API_Exception $e) {
    $this->logger->log_error($e->getMessage());
    $this->handle_api_error($e);
}
```

## Database Schema

### Custom Tables
The plugin uses WordPress's existing structure but adds:

#### Post Meta Fields (Movies)
- `tmdb_id`: TMDB movie ID
- `release_date`: Release date (Y-m-d format)
- `runtime`: Duration in minutes
- `popularity`: TMDB popularity score
- `vote_average`: Average rating
- `production_companies`: JSON array
- `credits`: Cast and crew JSON
- `videos`: Trailers and clips JSON
- `reviews`: User reviews JSON

#### Post Meta Fields (Actors)
- `tmdb_id`: TMDB person ID
- `birthday`: Birth date (Y-m-d format)
- `deathday`: Death date if applicable
- `place_of_birth`: Birth location
- `popularity`: TMDB popularity score
- `movie_credits`: Filmography JSON
- `images`: Photo gallery JSON

### Taxonomies
- `genre`: Movie genres (hierarchical)
- `production_company`: Production companies (non-hierarchical)

## Cron Jobs

### Scheduled Tasks
```php
// Hourly sync for popular content
wp_schedule_event(time(), 'hourly', 'tmdb_hourly_sync');

// Daily sync for complete updates
wp_schedule_event(time(), 'daily', 'tmdb_daily_sync');
```

### Background Processing
- Uses WordPress cron system
- Implements batch processing to avoid timeouts
- Maintains processing queue in options table

## Security Considerations

### Data Sanitization
```php
$title = sanitize_text_field($tmdb_data['title']);
$overview = wp_kses_post($tmdb_data['overview']);
$tmdb_id = absint($tmdb_data['id']);
```

### Capability Checks
```php
if (!current_user_can('manage_options')) {
    wp_die(__('Insufficient permissions'));
}
```

### Nonce Verification
```php
if (!wp_verify_nonce($_POST['nonce'], 'tmdb_admin_action')) {
    wp_die(__('Security check failed'));
}
```

## Performance Optimization

### Caching Strategy
- Transient API for short-term caching
- Object caching for database queries
- Image optimization and CDN integration

### Database Optimization
- Indexed meta queries
- Batch database operations
- Query optimization for large datasets

## Configuration Options

### Settings (wp_options table)
```php
$options = array(
    'tmdb_api_key' => '',
    'tmdb_auto_sync' => true,
    'tmdb_sync_interval' => 'daily',
    'tmdb_import_images' => true,
    'tmdb_cache_duration' => 3600,
    'tmdb_batch_size' => 50
);
```

## Troubleshooting

### Common Issues

1. **API Key Invalid**
   - Check key format and permissions
   - Verify account status with TMDB

2. **Import Timeouts**
   - Reduce batch size
   - Check server memory limits
   - Review cron job frequency

3. **Missing Images**
   - Verify media upload permissions
   - Check available disk space
   - Review image processing settings

### Debug Mode
```php
define('TMDB_DEBUG', true);
```

## Hooks and Filters

### Action Hooks
- `tmdb_before_import`: Before starting import process
- `tmdb_after_import`: After completing import
- `tmdb_movie_imported`: When individual movie is imported
- `tmdb_actor_imported`: When individual actor is imported

### Filter Hooks
- `tmdb_movie_data`: Modify movie data before saving
- `tmdb_actor_data`: Modify actor data before saving
- `tmdb_api_request_args`: Modify API request parameters
- `tmdb_import_batch_size`: Customize batch processing size

## Extensions and Customization

### Adding Custom Fields
```php
add_filter('tmdb_movie_data', function($data, $tmdb_data) {
    $data['meta_input']['custom_field'] = $tmdb_data['custom_value'];
    return $data;
}, 10, 2);
```

### Custom Import Processing
```php
add_action('tmdb_after_import', function($imported_data) {
    // Custom processing after import
    do_custom_processing($imported_data);
});
```
