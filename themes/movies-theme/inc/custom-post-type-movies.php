<?php
/**
 * Custom Post Type: Movies
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register Movie post type
function movies_register_movie_post_type() {
    $labels = array(
        'name'               => _x('Movies', 'post type general name', 'movies-theme'),
        'singular_name'      => _x('Movie', 'post type singular name', 'movies-theme'),
        'menu_name'          => _x('Movies', 'admin menu', 'movies-theme'),
        'add_new'            => _x('Add New', 'movie', 'movies-theme'),
        'add_new_item'       => __('Add New Movie', 'movies-theme'),
        'new_item'           => __('New Movie', 'movies-theme'),
        'edit_item'          => __('Edit Movie', 'movies-theme'),
        'view_item'          => __('View Movie', 'movies-theme'),
        'all_items'          => __('All Movies', 'movies-theme'),
        'search_items'       => __('Search Movies', 'movies-theme'),
        'not_found'          => __('No movies found.', 'movies-theme'),
        'not_found_in_trash' => __('No movies found in Trash.', 'movies-theme')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'movies'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-video-alt3',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest'       => true,
    );

    register_post_type('movie', $args);
}
add_action('init', 'movies_register_movie_post_type');

// Register taxonomies for movies
function movies_register_movie_taxonomies() {
    // Genre taxonomy for movies
    register_taxonomy('genre', 'movie', array(
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => _x('Genres', 'taxonomy general name', 'movies-theme'),
            'singular_name'     => _x('Genre', 'taxonomy singular name', 'movies-theme'),
        ),
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'genre'),
        'show_in_rest'      => true,
    ));

    // Rating taxonomy for movies
    register_taxonomy('rating', 'movie', array(
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => _x('Ratings', 'taxonomy general name', 'movies-theme'),
            'singular_name'     => _x('Rating', 'taxonomy singular name', 'movies-theme'),
        ),
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'rating'),
        'show_in_rest'      => true,
    ));
}
add_action('init', 'movies_register_movie_taxonomies');

// ==========================================
// ADMIN FILTERS AND COLUMNS FOR MOVIES
// ==========================================

/**
 * Add custom columns to Movies admin list
 */
function movies_add_admin_columns($columns) {
    // Insert new columns after title
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['release_date'] = __('Release Date', 'movies-theme');
            $new_columns['movie_status'] = __('Status', 'movies-theme');
            $new_columns['popularity'] = __('Popularity', 'movies-theme');
            $new_columns['rating'] = __('Rating', 'movies-theme');
        }
    }
    return $new_columns;
}
add_filter('manage_movie_posts_columns', 'movies_add_admin_columns');

/**
 * Populate custom columns content
 */
function movies_populate_admin_columns($column, $post_id) {
    switch ($column) {
        case 'release_date':
            $release_date = get_post_meta($post_id, 'release_date', true);
            if ($release_date) {
                $formatted_date = date('M j, Y', strtotime($release_date));
                $today = date('Y-m-d');
                
                if ($release_date > $today) {
                    echo '<span style="color: #2271b1; font-weight: bold;">' . esc_html($formatted_date) . '</span>';
                    echo '<br><small style="color: #2271b1;">Upcoming</small>';
                } else {
                    echo esc_html($formatted_date);
                }
            } else {
                echo '<span style="color: #999;">—</span>';
            }
            break;
            
        case 'movie_status':
            $release_date = get_post_meta($post_id, 'release_date', true);
            $today = date('Y-m-d');
            
            if ($release_date) {
                if ($release_date > $today) {
                    echo '<span class="upcoming-badge" style="background: #2271b1; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px;">UPCOMING</span>';
                } else {
                    echo '<span class="released-badge" style="background: #00a32a; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px;">RELEASED</span>';
                }
            } else {
                echo '<span style="color: #999;">Unknown</span>';
            }
            break;
            
        case 'popularity':
            $popularity = get_post_meta($post_id, 'popularity', true);
            if ($popularity) {
                echo '<strong>' . number_format((float)$popularity, 1) . '</strong>';
            } else {
                echo '<span style="color: #999;">—</span>';
            }
            break;
            
        case 'rating':
            $vote_average = get_post_meta($post_id, 'vote_average', true);
            $vote_count = get_post_meta($post_id, 'vote_count', true);
            
            if ($vote_average) {
                echo '<strong style="color: #f39c12;">★ ' . number_format((float)$vote_average, 1) . '</strong>';
                if ($vote_count) {
                    echo '<br><small>(' . number_format($vote_count) . ' votes)</small>';
                }
            } else {
                echo '<span style="color: #999;">—</span>';
            }
            break;
    }
}
add_action('manage_movie_posts_custom_column', 'movies_populate_admin_columns', 10, 2);

/**
 * Make custom columns sortable
 */
function movies_sortable_admin_columns($columns) {
    $columns['release_date'] = 'release_date';
    $columns['popularity'] = 'popularity';
    $columns['rating'] = 'vote_average';
    return $columns;
}
add_filter('manage_edit-movie_sortable_columns', 'movies_sortable_admin_columns');

/**
 * Handle custom column sorting
 */
function movies_admin_column_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    switch ($orderby) {
        case 'release_date':
            $query->set('meta_key', 'release_date');
            $query->set('orderby', 'meta_value');
            break;
            
        case 'popularity':
            $query->set('meta_key', 'popularity');
            $query->set('orderby', 'meta_value_num');
            break;
            
        case 'vote_average':
            $query->set('meta_key', 'vote_average');
            $query->set('orderby', 'meta_value_num');
            break;
    }
}
add_action('pre_get_posts', 'movies_admin_column_orderby');

/**
 * Add filter dropdown for movie status (Upcoming vs Released)
 */
function movies_add_admin_filters() {
    global $typenow;
    
    if ($typenow === 'movie') {
        // Status filter (Upcoming/Released)
        $current_filter = isset($_GET['movie_status_filter']) ? $_GET['movie_status_filter'] : '';
        ?>
        <select name="movie_status_filter">
            <option value=""><?php _e('All Movies', 'movies-theme'); ?></option>
            <option value="upcoming" <?php selected($current_filter, 'upcoming'); ?>><?php _e('Upcoming Movies', 'movies-theme'); ?></option>
            <option value="released" <?php selected($current_filter, 'released'); ?>><?php _e('Released Movies', 'movies-theme'); ?></option>
        </select>
        
        <?php
        // Year filter
        $current_year = isset($_GET['movie_year_filter']) ? $_GET['movie_year_filter'] : '';
        $years = movies_get_available_years();
        
        if (!empty($years)) {
            ?>
            <select name="movie_year_filter">
                <option value=""><?php _e('All Years', 'movies-theme'); ?></option>
                <?php foreach ($years as $year) : ?>
                    <option value="<?php echo esc_attr($year); ?>" <?php selected($current_year, $year); ?>>
                        <?php echo esc_html($year); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php
        }
    }
}
add_action('restrict_manage_posts', 'movies_add_admin_filters');

/**
 * Handle admin filter queries
 */
function movies_handle_admin_filters($query) {
    global $pagenow, $typenow;
    
    if ($pagenow === 'edit.php' && $typenow === 'movie' && $query->is_main_query()) {
        
        // Handle status filter
        if (!empty($_GET['movie_status_filter'])) {
            $today = date('Y-m-d');
            
            if ($_GET['movie_status_filter'] === 'upcoming') {
                $query->set('meta_query', array(
                    array(
                        'key' => 'release_date',
                        'value' => $today,
                        'compare' => '>',
                        'type' => 'DATE'
                    )
                ));
            } elseif ($_GET['movie_status_filter'] === 'released') {
                $query->set('meta_query', array(
                    array(
                        'key' => 'release_date',
                        'value' => $today,
                        'compare' => '<=',
                        'type' => 'DATE'
                    )
                ));
            }
        }
        
        // Handle year filter
        if (!empty($_GET['movie_year_filter'])) {
            $year = intval($_GET['movie_year_filter']);
            $start_date = $year . '-01-01';
            $end_date = $year . '-12-31';
            
            $existing_meta_query = $query->get('meta_query') ?: array();
            $existing_meta_query[] = array(
                'key' => 'release_date',
                'value' => array($start_date, $end_date),
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            );
            
            $query->set('meta_query', $existing_meta_query);
        }
    }
}
add_action('pre_get_posts', 'movies_handle_admin_filters');

/**
 * Get available years from movies
 */
function movies_get_available_years() {
    global $wpdb;
    
    $years = $wpdb->get_col("
        SELECT DISTINCT YEAR(meta_value) as year 
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = 'release_date' 
        AND p.post_type = 'movie'
        AND p.post_status = 'publish'
        AND pm.meta_value != ''
        ORDER BY year DESC
    ");
    
    return array_filter($years);
}

/**
 * Add admin notice showing movie counts
 */
function movies_admin_notices() {
    $screen = get_current_screen();
    
    if ($screen->id === 'edit-movie') {
        $today = date('Y-m-d');
        
        // Count upcoming movies
        $upcoming_count = get_posts(array(
            'post_type' => 'movie',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'release_date',
                    'value' => $today,
                    'compare' => '>',
                    'type' => 'DATE'
                )
            )
        ));
        
        // Count released movies
        $released_count = get_posts(array(
            'post_type' => 'movie',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'release_date',
                    'value' => $today,
                    'compare' => '<=',
                    'type' => 'DATE'
                )
            )
        ));
        
        if (!empty($upcoming_count) || !empty($released_count)) {
            ?>
            <div class="notice notice-info">
                <p>
                    <strong><?php _e('Movie Statistics:', 'movies-theme'); ?></strong>
                    <?php printf(__('%d upcoming movies', 'movies-theme'), count($upcoming_count)); ?> | 
                    <?php printf(__('%d released movies', 'movies-theme'), count($released_count)); ?>
                </p>
            </div>
            <?php
        }
    }
}
add_action('admin_notices', 'movies_admin_notices'); 