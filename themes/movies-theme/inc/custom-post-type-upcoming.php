<?php
/**
 * Custom Post Type: Upcoming Movies
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register Upcoming post type
function movies_register_upcoming_post_type() {
    $labels = array(
        'name'               => _x('Upcoming Movies', 'post type general name', 'movies-theme'),
        'singular_name'      => _x('Upcoming Movie', 'post type singular name', 'movies-theme'),
        'menu_name'          => _x('Upcoming', 'admin menu', 'movies-theme'),
        'add_new'            => _x('Add New', 'upcoming movie', 'movies-theme'),
        'add_new_item'       => __('Add New Upcoming Movie', 'movies-theme'),
        'new_item'           => __('New Upcoming Movie', 'movies-theme'),
        'edit_item'          => __('Edit Upcoming Movie', 'movies-theme'),
        'view_item'          => __('View Upcoming Movie', 'movies-theme'),
        'all_items'          => __('All Upcoming Movies', 'movies-theme'),
        'search_items'       => __('Search Upcoming Movies', 'movies-theme'),
        'not_found'          => __('No upcoming movies found.', 'movies-theme'),
        'not_found_in_trash' => __('No upcoming movies found in Trash.', 'movies-theme')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'upcoming'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 7,
        'menu_icon'          => 'dashicons-calendar-alt',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest'       => true,
    );

    register_post_type('upcoming', $args);
}
add_action('init', 'movies_register_upcoming_post_type');

// Register taxonomies for upcoming movies
function movies_register_upcoming_taxonomies() {
    // Genre taxonomy for upcoming movies
    register_taxonomy('upcoming_genre', 'upcoming', array(
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => _x('Genres', 'taxonomy general name', 'movies-theme'),
            'singular_name'     => _x('Genre', 'taxonomy singular name', 'movies-theme'),
        ),
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'upcoming-genre'),
        'show_in_rest'      => true,
    ));
}
add_action('init', 'movies_register_upcoming_taxonomies');

// ==========================================
// ADMIN FILTERS AND COLUMNS FOR UPCOMING MOVIES
// ==========================================

/**
 * Add custom columns to Upcoming Movies admin list
 */
function upcoming_add_admin_columns($columns) {
    // Insert new columns after title
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['release_date'] = __('Release Date', 'movies-theme');
            $new_columns['days_until_release'] = __('Days Until Release', 'movies-theme');
            $new_columns['popularity'] = __('Popularity', 'movies-theme');
        }
    }
    return $new_columns;
}
add_filter('manage_upcoming_posts_columns', 'upcoming_add_admin_columns');

/**
 * Populate custom columns content for upcoming movies
 */
function upcoming_populate_admin_columns($column, $post_id) {
    switch ($column) {
        case 'release_date':
            $release_date = get_post_meta($post_id, 'release_date', true);
            if ($release_date) {
                $formatted_date = date('M j, Y', strtotime($release_date));
                echo '<span style="color: #2271b1; font-weight: bold;">' . esc_html($formatted_date) . '</span>';
            } else {
                echo '<span style="color: #999;">—</span>';
            }
            break;
            
        case 'days_until_release':
            $release_date = get_post_meta($post_id, 'release_date', true);
            if ($release_date) {
                $today = date('Y-m-d');
                $days_diff = floor((strtotime($release_date) - strtotime($today)) / (60 * 60 * 24));
                
                if ($days_diff > 0) {
                    echo '<strong style="color: #2271b1;">' . $days_diff . ' days</strong>';
                } elseif ($days_diff === 0) {
                    echo '<strong style="color: #d63638;">Today!</strong>';
                } else {
                    echo '<strong style="color: #d63638;">Released</strong>';
                }
            } else {
                echo '<span style="color: #999;">—</span>';
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
    }
}
add_action('manage_upcoming_posts_custom_column', 'upcoming_populate_admin_columns', 10, 2);



/**
 * Make custom columns sortable for upcoming movies
 */
function upcoming_sortable_admin_columns($columns) {
    $columns['release_date'] = 'release_date';
    $columns['popularity'] = 'popularity';
    return $columns;
}
add_filter('manage_edit-upcoming_sortable_columns', 'upcoming_sortable_admin_columns');

/**
 * Handle custom column sorting for upcoming movies
 */
function upcoming_admin_column_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    global $typenow;
    if ($typenow !== 'upcoming') {
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
    }
}
add_action('pre_get_posts', 'upcoming_admin_column_orderby');

/**
 * Add filter dropdown for upcoming movies
 */
function upcoming_add_admin_filters() {
    global $typenow;
    
    if ($typenow === 'upcoming') {
        // Time period filter
        $current_period = isset($_GET['upcoming_period_filter']) ? $_GET['upcoming_period_filter'] : '';
        ?>
        <select name="upcoming_period_filter">
            <option value=""><?php _e('All Periods', 'movies-theme'); ?></option>
            <option value="this_month" <?php selected($current_period, 'this_month'); ?>><?php _e('This Month', 'movies-theme'); ?></option>
            <option value="next_month" <?php selected($current_period, 'next_month'); ?>><?php _e('Next Month', 'movies-theme'); ?></option>
            <option value="this_quarter" <?php selected($current_period, 'this_quarter'); ?>><?php _e('This Quarter', 'movies-theme'); ?></option>
            <option value="this_year" <?php selected($current_period, 'this_year'); ?>><?php _e('This Year', 'movies-theme'); ?></option>
            <option value="next_year" <?php selected($current_period, 'next_year'); ?>><?php _e('Next Year', 'movies-theme'); ?></option>
        </select>
        <?php
    }
}
add_action('restrict_manage_posts', 'upcoming_add_admin_filters');

/**
 * Handle admin filter queries for upcoming movies
 */
function upcoming_handle_admin_filters($query) {
    global $pagenow, $typenow;
    
    if ($pagenow === 'edit.php' && $typenow === 'upcoming' && $query->is_main_query()) {
        
        // Handle period filter
        if (!empty($_GET['upcoming_period_filter'])) {
            $today = date('Y-m-d');
            $start_date = '';
            $end_date = '';
            
            switch ($_GET['upcoming_period_filter']) {
                case 'this_month':
                    $start_date = date('Y-m-01');
                    $end_date = date('Y-m-t');
                    break;
                    
                case 'next_month':
                    $start_date = date('Y-m-01', strtotime('+1 month'));
                    $end_date = date('Y-m-t', strtotime('+1 month'));
                    break;
                    
                case 'this_quarter':
                    $quarter = ceil(date('n') / 3);
                    $start_date = date('Y-m-01', mktime(0, 0, 0, ($quarter - 1) * 3 + 1, 1, date('Y')));
                    $end_date = date('Y-m-t', mktime(0, 0, 0, $quarter * 3, 1, date('Y')));
                    break;
                    
                case 'this_year':
                    $start_date = date('Y-01-01');
                    $end_date = date('Y-12-31');
                    break;
                    
                case 'next_year':
                    $start_date = date('Y-01-01', strtotime('+1 year'));
                    $end_date = date('Y-12-31', strtotime('+1 year'));
                    break;
            }
            
            if ($start_date && $end_date) {
                $query->set('meta_query', array(
                    array(
                        'key' => 'release_date',
                        'value' => array($start_date, $end_date),
                        'compare' => 'BETWEEN',
                        'type' => 'DATE'
                    )
                ));
            }
        }
    }
}
add_action('pre_get_posts', 'upcoming_handle_admin_filters');

/**
 * Add admin notice showing upcoming movie counts
 */
function upcoming_admin_notices() {
    $screen = get_current_screen();
    
    if ($screen->id === 'edit-upcoming') {
        $today = date('Y-m-d');
        $next_month = date('Y-m-d', strtotime('+1 month'));
        
        // Count movies releasing this month
        $this_month_count = get_posts(array(
            'post_type' => 'upcoming',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'release_date',
                    'value' => array(date('Y-m-01'), date('Y-m-t')),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        ));
        
        // Count movies releasing next month
        $next_month_count = get_posts(array(
            'post_type' => 'upcoming',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'release_date',
                    'value' => array(date('Y-m-01', strtotime('+1 month')), date('Y-m-t', strtotime('+1 month'))),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        ));
        
        if (!empty($this_month_count) || !empty($next_month_count)) {
            ?>
            <div class="notice notice-info">
                <p>
                    <strong><?php _e('Upcoming Movies:', 'movies-theme'); ?></strong>
                    <?php printf(__('%d this month', 'movies-theme'), count($this_month_count)); ?> | 
                    <?php printf(__('%d next month', 'movies-theme'), count($next_month_count)); ?>
                </p>
            </div>
            <?php
        }
    }
}
add_action('admin_notices', 'upcoming_admin_notices'); 