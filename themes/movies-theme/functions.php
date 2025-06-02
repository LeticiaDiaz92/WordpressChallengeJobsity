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
    // Add theme support for various features
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption'
    ));
    add_theme_support('custom-logo');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'movies-theme'),
        'footer'  => __('Footer Menu', 'movies-theme'),
    ));
    
    // Add image sizes
    add_image_size('movie-thumbnail', 300, 450, true);
    add_image_size('actor-thumbnail', 200, 200, true);
    add_image_size('hero-banner', 1920, 800, true);
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

/**
 * Include custom post types in search results
 */
function movies_include_custom_post_types_in_search($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        $query->set('post_type', array('post', 'movie', 'actor'));
        
        // Increase search results per page
        $query->set('posts_per_page', 12);
    }
}
add_action('pre_get_posts', 'movies_include_custom_post_types_in_search');

/**
 * Track post views (with bot detection)
 */
function movies_track_post_views($post_id) {
    // Don't track if user is admin or if it's a bot
    if (is_admin() || movies_is_bot()) {
        return;
    }
    
    // Get current view count
    $views = (int) get_post_meta($post_id, 'views', true);
    
    // Increment view count
    update_post_meta($post_id, 'views', $views + 1);
}

/**
 * Simple bot detection
 */
function movies_is_bot() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $bots = array(
        'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider',
        'yandexbot', 'facebookexternalhit', 'twitterbot', 'rogerbot',
        'linkedinbot', 'embedly', 'quora link preview', 'showyoubot',
        'outbrain', 'pinterest', 'developers.google.com', 'applebot',
        'crawler', 'spider', 'scraper'
    );
    
    $user_agent_lower = strtolower($user_agent);
    
    foreach ($bots as $bot) {
        if (strpos($user_agent_lower, $bot) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Track views on single posts
 */
function movies_track_single_post_views() {
    if (is_single() && (is_singular('movie') || is_singular('actor'))) {
        movies_track_post_views(get_the_ID());
    }
}
add_action('wp_head', 'movies_track_single_post_views');

/**
 * Improve search functionality to include custom fields
 */
function movies_extend_search_functionality($search, $wp_query) {
    global $wpdb;
    
    if (!is_search() || is_admin()) {
        return $search;
    }
    
    $search_term = $wp_query->get('s');
    if (empty($search_term)) {
        return $search;
    }
    
    // Escape the search term
    $search_term_like = '%' . $wpdb->esc_like($search_term) . '%';
    
    // Create additional search conditions for meta fields
    $meta_search = " OR EXISTS (
        SELECT * FROM {$wpdb->postmeta} 
        WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID 
        AND (
            ({$wpdb->postmeta}.meta_key = 'overview' AND {$wpdb->postmeta}.meta_value LIKE %s)
            OR ({$wpdb->postmeta}.meta_key = 'tagline' AND {$wpdb->postmeta}.meta_value LIKE %s)
            OR ({$wpdb->postmeta}.meta_key = 'known_for_department' AND {$wpdb->postmeta}.meta_value LIKE %s)
            OR ({$wpdb->postmeta}.meta_key = 'place_of_birth' AND {$wpdb->postmeta}.meta_value LIKE %s)
        )
    )";
    
    // Add the meta search to the existing search
    if (!empty($search)) {
        $search = preg_replace(
            "/({$wpdb->posts}.post_title LIKE [^)]+\))/",
            "($1 {$meta_search})",
            $search
        );
        
        // Prepare the query with multiple instances of the search term
        $search = $wpdb->prepare($search, $search_term_like, $search_term_like, $search_term_like, $search_term_like);
    }
    
    return $search;
}
add_filter('posts_search', 'movies_extend_search_functionality', 20, 2);

/**
 * Enhanced search results ordering using custom ranking formula (V × P) / D
 */
function movies_improve_search_ordering($orderby, $wp_query) {
    global $wpdb;
    
    if (!is_search() || is_admin()) {
        return $orderby;
    }
    
    $search_term = $wp_query->get('s');
    if (empty($search_term)) {
        return $orderby;
    }
    
    // Create custom ordering that uses the (V × P) / D formula with fallbacks
    $custom_orderby = "
        CASE 
            WHEN {$wpdb->posts}.post_type = 'movie' THEN 
                (
                    COALESCE(
                        (SELECT CAST(meta_value AS UNSIGNED) FROM {$wpdb->postmeta} WHERE post_id = {$wpdb->posts}.ID AND meta_key = 'views'), 
                        1
                    ) * 
                    COALESCE(
                        (SELECT CAST(meta_value AS DECIMAL(10,2)) FROM {$wpdb->postmeta} WHERE post_id = {$wpdb->posts}.ID AND meta_key = 'popularity'), 
                        1.0
                    )
                ) / GREATEST(
                    COALESCE(
                        DATEDIFF(NOW(), (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = {$wpdb->posts}.ID AND meta_key = 'release_date')),
                        DATEDIFF(NOW(), {$wpdb->posts}.post_date)
                    ),
                    1
                )
            WHEN {$wpdb->posts}.post_type = 'actor' THEN 
                (
                    COALESCE(
                        (SELECT CAST(meta_value AS UNSIGNED) FROM {$wpdb->postmeta} WHERE post_id = {$wpdb->posts}.ID AND meta_key = 'views'), 
                        1
                    ) * 
                    COALESCE(
                        (SELECT CAST(meta_value AS DECIMAL(10,2)) FROM {$wpdb->postmeta} WHERE post_id = {$wpdb->posts}.ID AND meta_key = 'popularity'), 
                        1.0
                    )
                ) / GREATEST(
                    DATEDIFF(NOW(), {$wpdb->posts}.post_date),
                    1
                )
            ELSE 1
        END DESC,
        CASE 
            WHEN {$wpdb->posts}.post_title = '" . esc_sql($search_term) . "' THEN 1
            WHEN {$wpdb->posts}.post_title LIKE '%" . esc_sql($search_term) . "%' THEN 2
            ELSE 3
        END ASC,
        {$wpdb->posts}.post_type ASC,
        {$wpdb->posts}.post_title ASC
    ";
    
    return $custom_orderby;
}
add_filter('posts_orderby', 'movies_improve_search_ordering', 20, 2);

/**
 * Debug function to test search scoring
 */
function movies_debug_search_scores($search_term = '') {
    if (!current_user_can('manage_options')) {
        return 'Access denied';
    }
    
    if (empty($search_term)) {
        return 'Please provide a search term';
    }
    
    $results = get_posts(array(
        'post_type' => array('movie', 'actor'),
        'posts_per_page' => 10,
        's' => $search_term,
        'suppress_filters' => false
    ));
    
    $debug_output = array();
    
    foreach ($results as $post) {
        $views = (int) get_post_meta($post->ID, 'views', true) ?: 1;
        $popularity = (float) get_post_meta($post->ID, 'popularity', true) ?: 1.0;
        
        if ($post->post_type === 'movie') {
            $release_date = get_post_meta($post->ID, 'release_date', true);
            $days_since = $release_date ? (time() - strtotime($release_date)) / (24 * 60 * 60) : 1;
        } else {
            $days_since = (time() - strtotime($post->post_date)) / (24 * 60 * 60);
        }
        
        $score = ($views * $popularity) / max($days_since, 1);
        
        $debug_output[] = array(
            'title' => $post->post_title,
            'type' => $post->post_type,
            'views' => $views,
            'popularity' => $popularity,
            'days_since' => round($days_since, 2),
            'score' => round($score, 4)
        );
    }
    
    // Sort by score descending
    usort($debug_output, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });
    
    return $debug_output;
}

/**
 * Admin function to test search scoring (accessible via URL parameter for testing)
 */
function movies_test_search_scoring() {
    if (isset($_GET['debug_search']) && current_user_can('manage_options')) {
        $search_term = sanitize_text_field($_GET['debug_search']);
        $results = movies_debug_search_scores($search_term);
        
        echo '<div style="background: white; padding: 20px; margin: 20px; border: 1px solid #ccc;">';
        echo '<h3>Search Debug Results for: "' . esc_html($search_term) . '"</h3>';
        echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
        echo '<tr><th>Title</th><th>Type</th><th>Views</th><th>Popularity</th><th>Days Since</th><th>Score</th></tr>';
        
        foreach ($results as $result) {
            echo '<tr>';
            echo '<td>' . esc_html($result['title']) . '</td>';
            echo '<td>' . esc_html($result['type']) . '</td>';
            echo '<td>' . esc_html($result['views']) . '</td>';
            echo '<td>' . esc_html($result['popularity']) . '</td>';
            echo '<td>' . esc_html($result['days_since']) . '</td>';
            echo '<td><strong>' . esc_html($result['score']) . '</strong></td>';
            echo '</tr>';
        }
        
        echo '</table>';
        echo '<p><small>Formula: (Views × Popularity) / Days Since Release/Publication</small></p>';
        echo '</div>';
    }
}
add_action('wp_head', 'movies_test_search_scoring');

/**
 * Add mobile menu toggle functionality
 */
function movies_mobile_menu_script() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.nav-menu');
        
        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', function() {
                navMenu.classList.toggle('toggled');
                const expanded = navMenu.classList.contains('toggled');
                menuToggle.setAttribute('aria-expanded', expanded);
            });
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'movies_mobile_menu_script'); 