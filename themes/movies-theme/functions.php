<?php
/**
 * Movies Theme Functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('MOVIES_THEME_VERSION', '1.0');
define('MOVIES_THEME_PATH', get_template_directory());
define('MOVIES_THEME_URL', get_template_directory_uri());

// Theme setup
function movies_theme_setup() {
    // Add theme support
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'movies-theme'),
        'footer' => __('Footer Menu', 'movies-theme'),
    ));
}
add_action('after_setup_theme', 'movies_theme_setup');

// Enqueue scripts and styles
function movies_theme_scripts() {
    // Check if we're in development or production mode
    $is_development = defined('WP_DEBUG') && WP_DEBUG;
    
    // Enqueue main stylesheet (compiled from Sass)
    wp_enqueue_style(
        'movies-theme-style', 
        MOVIES_THEME_URL . '/assets/css/main.css', 
        array(), 
        MOVIES_THEME_VERSION
    );
    
    // Enqueue JavaScript files
    if ($is_development) {
        // Development mode - load individual files
        wp_enqueue_script('movies-theme-main', MOVIES_THEME_URL . '/assets/js/main.js', array('jquery'), MOVIES_THEME_VERSION, true);
        wp_enqueue_script('movies-theme-ajax-filters', MOVIES_THEME_URL . '/assets/js/ajax-filters.js', array('jquery'), MOVIES_THEME_VERSION, true);
        wp_enqueue_script('movies-theme-ajax-actor-filters', MOVIES_THEME_URL . '/assets/js/ajax-actor-filters.js', array('jquery'), MOVIES_THEME_VERSION, true);
        wp_enqueue_script('movies-theme-wishlist', MOVIES_THEME_URL . '/assets/js/wishlist.js', array('jquery'), MOVIES_THEME_VERSION, true);
        wp_enqueue_script('movies-theme-search', MOVIES_THEME_URL . '/assets/js/search.js', array('jquery'), MOVIES_THEME_VERSION, true);
    } else {
        // Production mode - load minified bundle
        if (file_exists(MOVIES_THEME_PATH . '/assets/js/main.min.js')) {
            wp_enqueue_script('movies-theme-bundle', MOVIES_THEME_URL . '/assets/js/main.min.js', array('jquery'), MOVIES_THEME_VERSION, true);
        } else {
            // Fallback to individual files if minified version doesn't exist
            wp_enqueue_script('movies-theme-main', MOVIES_THEME_URL . '/assets/js/main.js', array('jquery'), MOVIES_THEME_VERSION, true);
            wp_enqueue_script('movies-theme-ajax-filters', MOVIES_THEME_URL . '/assets/js/ajax-filters.js', array('jquery'), MOVIES_THEME_VERSION, true);
            wp_enqueue_script('movies-theme-ajax-actor-filters', MOVIES_THEME_URL . '/assets/js/ajax-actor-filters.js', array('jquery'), MOVIES_THEME_VERSION, true);
            wp_enqueue_script('movies-theme-wishlist', MOVIES_THEME_URL . '/assets/js/wishlist.js', array('jquery'), MOVIES_THEME_VERSION, true);
            wp_enqueue_script('movies-theme-search', MOVIES_THEME_URL . '/assets/js/search.js', array('jquery'), MOVIES_THEME_VERSION, true);
        }
    }
    
    // Localize script for AJAX - Movies
    if ($is_development) {
        wp_localize_script('movies-theme-ajax-filters', 'movies_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('movies_nonce')
        ));
    } else {
        wp_localize_script('movies-theme-bundle', 'movies_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('movies_nonce')
    ));
    }
    
    // Localize script for AJAX - Actors
    if ($is_development) {
        wp_localize_script('movies-theme-ajax-actor-filters', 'actorsAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('actors_nonce')
        ));
    } else {
        wp_localize_script('movies-theme-bundle', 'actorsAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('actors_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'movies_theme_scripts');

// Include theme files
require_once MOVIES_THEME_PATH . '/inc/custom-post-type-movies.php';
require_once MOVIES_THEME_PATH . '/inc/custom-post-type-actors.php';
require_once MOVIES_THEME_PATH . '/inc/custom-post-type-upcoming.php';
require_once MOVIES_THEME_PATH . '/inc/custom-fields.php';
require_once MOVIES_THEME_PATH . '/inc/api-functions.php';
require_once MOVIES_THEME_PATH . '/inc/widgets.php';
require_once MOVIES_THEME_PATH . '/inc/ajax-handlers.php';
require_once MOVIES_THEME_PATH . '/inc/user-functions.php';
require_once MOVIES_THEME_PATH . '/inc/helpers.php';


// Include blocks
require_once MOVIES_THEME_PATH . '/inc/blocks.php'; 

/**
 * Get all years from movies release dates
 */
function movies_get_all_years() {
    global $wpdb;
    
    $years = $wpdb->get_col("
        SELECT DISTINCT YEAR(meta_value) as year
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = 'release_date'
        AND p.post_type = 'movie'
        AND p.post_status = 'publish'
        AND pm.meta_value IS NOT NULL
        AND pm.meta_value != ''
        ORDER BY year DESC
    ");
    
    return array_filter($years);
}

/**
 * Modify the main query for movie archive filtering
 */
function movies_filter_archive_query($query) {
    // Don't modify queries for AJAX requests - they are handled separately
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }
    
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('movie')) {
        
        // Set default ordering by title
        if (!isset($_GET['orderby']) || empty($_GET['orderby'])) {
            $query->set('orderby', 'title');
            $query->set('order', 'ASC');
        } else {
            $orderby = sanitize_text_field($_GET['orderby']);
            
            switch ($orderby) {
                case 'title':
                    $query->set('orderby', 'title');
                    $query->set('order', 'ASC');
                    break;
                case 'title_desc':
                    $query->set('orderby', 'title');
                    $query->set('order', 'DESC');
                    break;
                case 'date':
                    $query->set('orderby', 'date');
                    $query->set('order', 'DESC');
                    break;
                case 'date_asc':
                    $query->set('orderby', 'date');
                    $query->set('order', 'ASC');
                    break;
                case 'popularity':
                    $query->set('orderby', 'meta_value_num');
                    $query->set('meta_key', 'popularity');
                    $query->set('order', 'DESC');
                    break;
                case 'rating':
                    $query->set('orderby', 'meta_value_num');
                    $query->set('meta_key', 'vote_average');
                    $query->set('order', 'DESC');
                    break;
            }
        }
        
        // Handle search by title
        if (!empty($_GET['movie_search'])) {
            $search_term = sanitize_text_field($_GET['movie_search']);
            $query->set('s', $search_term);
        }
        
        // Handle genre filter
        if (!empty($_GET['movie_genre'])) {
            $genre_slug = sanitize_text_field($_GET['movie_genre']);
            $query->set('tax_query', array(
                array(
                    'taxonomy' => 'genre',
                    'field'    => 'slug',
                    'terms'    => $genre_slug,
                ),
            ));
        }
        
        // Handle year filter
        if (!empty($_GET['movie_year'])) {
            $year = intval($_GET['movie_year']);
            
            $meta_query = $query->get('meta_query') ?: array();
            $meta_query[] = array(
                'key'     => 'release_date',
                'value'   => array($year . '-01-01', $year . '-12-31'),
                'compare' => 'BETWEEN',
                'type'    => 'DATE'
            );
            
            $query->set('meta_query', $meta_query);
        }
        
        // Set posts per page
        $query->set('posts_per_page', 12);
    }
}
add_action('pre_get_posts', 'movies_filter_archive_query');

/**
 * Modify the main query for actor archive filtering
 */
function actors_filter_archive_query($query) {
    // Don't modify queries for AJAX requests - they are handled separately
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }
    
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('actor')) {
        
        // Set default ordering by title
        if (!isset($_GET['orderby']) || empty($_GET['orderby'])) {
            $query->set('orderby', 'title');
            $query->set('order', 'ASC');
        } else {
            $orderby = sanitize_text_field($_GET['orderby']);
            
            switch ($orderby) {
                case 'title':
                    $query->set('orderby', 'title');
                    $query->set('order', 'ASC');
                    break;
                case 'title_desc':
                    $query->set('orderby', 'title');
                    $query->set('order', 'DESC');
                    break;
                case 'date':
                    $query->set('orderby', 'date');
                    $query->set('order', 'DESC');
                    break;
                case 'popularity':
                    $query->set('orderby', 'meta_value_num');
                    $query->set('meta_key', 'popularity');
                    $query->set('order', 'DESC');
                    break;
            }
        }
        
        // Handle search by name
        if (!empty($_GET['actor_search'])) {
            $search_term = sanitize_text_field($_GET['actor_search']);
            $query->set('s', $search_term);
        }
        
        // Handle movie filter
        if (!empty($_GET['actor_movie'])) {
            $movie_id = intval($_GET['actor_movie']);
            
            // Get actors associated with the movie (search in both cast and crew)
            // Note: $movie_id is now the TMDB movie ID, not WordPress post ID
            global $wpdb;
            $actor_ids = $wpdb->get_col($wpdb->prepare("
                SELECT DISTINCT pm.post_id 
                FROM {$wpdb->postmeta} pm 
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = 'movie_credits' 
                AND p.post_type = 'actor'
                AND p.post_status = 'publish'
                AND (
                    (pm.meta_value LIKE %s AND pm.meta_value LIKE %s) OR
                    (pm.meta_value LIKE %s AND pm.meta_value LIKE %s)
                )
            ", 
            '%"cast":%', '%"id":' . $movie_id . '%',
            '%"crew":%', '%"id":' . $movie_id . '%'
            ));
            
            if (!empty($actor_ids)) {
                $query->set('post__in', $actor_ids);
            } else {
                // No actors found for this movie
                $query->set('post__in', array(0));
            }
        }
        
        // Set posts per page
        $query->set('posts_per_page', 12);
    }
}
add_action('pre_get_posts', 'actors_filter_archive_query');

/**
 * Add custom query vars for filtering
 */
function movies_add_query_vars($vars) {
    // Movie filters
    $vars[] = 'movie_search';
    $vars[] = 'movie_year';
    $vars[] = 'movie_genre';
    
    // Actor filters
    $vars[] = 'actor_search';
    $vars[] = 'actor_movie';
    
    return $vars;
}
add_filter('query_vars', 'movies_add_query_vars');

/**
 * Get movies that have actors associated with them (from actor credits JSON)
 */
function movies_get_movies_with_actors() {
    // Check cache first
    $cache_key = 'movies_with_actors_list';
    $cached_movies = wp_cache_get($cache_key);
    
    if ($cached_movies !== false) {
        return $cached_movies;
    }
    
    global $wpdb;
    
    // Get all movie data from actor credits (both cast and crew)
    $movies_data = $wpdb->get_results("
        SELECT DISTINCT 
            JSON_UNQUOTE(JSON_EXTRACT(pm.meta_value, CONCAT('$.cast[', numbers.n, '].id'))) as movie_id,
            JSON_UNQUOTE(JSON_EXTRACT(pm.meta_value, CONCAT('$.cast[', numbers.n, '].title'))) as movie_title,
            JSON_UNQUOTE(JSON_EXTRACT(pm.meta_value, CONCAT('$.cast[', numbers.n, '].release_date'))) as release_date
        FROM {$wpdb->postmeta} pm
        CROSS JOIN (
            SELECT 0 as n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION 
            SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
            SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION
            SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
        ) as numbers
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = 'movie_credits'
        AND p.post_type = 'actor'
        AND p.post_status = 'publish'
        AND pm.meta_value != ''
        AND JSON_UNQUOTE(JSON_EXTRACT(pm.meta_value, CONCAT('$.cast[', numbers.n, '].id'))) IS NOT NULL
        AND JSON_UNQUOTE(JSON_EXTRACT(pm.meta_value, CONCAT('$.cast[', numbers.n, '].title'))) IS NOT NULL
        
        UNION
        
        SELECT DISTINCT 
            JSON_UNQUOTE(JSON_EXTRACT(pm.meta_value, CONCAT('$.crew[', numbers.n, '].id'))) as movie_id,
            JSON_UNQUOTE(JSON_EXTRACT(pm.meta_value, CONCAT('$.crew[', numbers.n, '].title'))) as movie_title,
            JSON_UNQUOTE(JSON_EXTRACT(pm.meta_value, CONCAT('$.crew[', numbers.n, '].release_date'))) as release_date
        FROM {$wpdb->postmeta} pm
        CROSS JOIN (
            SELECT 0 as n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION 
            SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
            SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION
            SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
        ) as numbers
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = 'movie_credits'
        AND p.post_type = 'actor'
        AND p.post_status = 'publish'
        AND pm.meta_value != ''
        AND JSON_UNQUOTE(JSON_EXTRACT(pm.meta_value, CONCAT('$.crew[', numbers.n, '].id'))) IS NOT NULL
        AND JSON_UNQUOTE(JSON_EXTRACT(pm.meta_value, CONCAT('$.crew[', numbers.n, '].title'))) IS NOT NULL
    ");
    
    // Fallback method if JSON functions are not available (older MySQL versions)
    if (empty($movies_data)) {
        $result = movies_get_movies_with_actors_fallback();
        wp_cache_set($cache_key, $result, '', 3600); // Cache for 1 hour
        return $result;
    }
    
    // Remove duplicates and invalid entries
    $unique_movies = array();
    foreach ($movies_data as $movie) {
        if (!empty($movie->movie_id) && !empty($movie->movie_title) && $movie->movie_id != 'null') {
            $unique_movies[$movie->movie_id] = (object) array(
                'ID' => $movie->movie_id,
                'post_title' => $movie->movie_title,
                'release_date' => $movie->release_date
            );
        }
    }
    
    // Sort by title
    uasort($unique_movies, function($a, $b) {
        return strcmp($a->post_title, $b->post_title);
    });
    
    $result = array_values($unique_movies);
    
    // Cache the result for 1 hour
    wp_cache_set($cache_key, $result, '', 3600);
    
    return $result;
}

/**
 * Fallback method for older MySQL versions that don't support JSON functions
 */
function movies_get_movies_with_actors_fallback() {
    global $wpdb;
    
    $actors_with_credits = $wpdb->get_results("
        SELECT pm.meta_value as movie_credits
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = 'movie_credits'
        AND p.post_type = 'actor'
        AND p.post_status = 'publish'
        AND pm.meta_value != ''
        AND pm.meta_value != '{\"cast\":[],\"crew\":[]}'
    ");
    
    $unique_movies = array();
    
    foreach ($actors_with_credits as $row) {
        $credits = json_decode($row->movie_credits, true);
        
        if (!$credits) continue;
        
        // Process cast credits
        if (!empty($credits['cast'])) {
            foreach ($credits['cast'] as $credit) {
                if (!empty($credit['id']) && !empty($credit['title'])) {
                    $unique_movies[$credit['id']] = (object) array(
                        'ID' => $credit['id'],
                        'post_title' => $credit['title'],
                        'release_date' => $credit['release_date'] ?? ''
                    );
                }
            }
        }
        
        // Process crew credits
        if (!empty($credits['crew'])) {
            foreach ($credits['crew'] as $credit) {
                if (!empty($credit['id']) && !empty($credit['title'])) {
                    $unique_movies[$credit['id']] = (object) array(
                        'ID' => $credit['id'],
                        'post_title' => $credit['title'],
                        'release_date' => $credit['release_date'] ?? ''
                    );
                }
            }
        }
    }
    
    // Sort by title
    uasort($unique_movies, function($a, $b) {
        return strcmp($a->post_title, $b->post_title);
    });
    
    return array_values($unique_movies);
}

/**
 * Clear movies with actors cache when actor credits are updated
 */
function movies_clear_movies_with_actors_cache($post_id) {
    if (get_post_type($post_id) === 'actor') {
        wp_cache_delete('movies_with_actors_list');
    }
}
add_action('save_post', 'movies_clear_movies_with_actors_cache');
add_action('updated_post_meta', function($meta_id, $object_id, $meta_key) {
    if ($meta_key === 'movie_credits') {
        wp_cache_delete('movies_with_actors_list');
    }
}, 10, 3); 