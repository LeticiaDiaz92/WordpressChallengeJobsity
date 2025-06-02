<?php
/**
 * Custom Post Type: Actors
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register Actor post type
function movies_register_actor_post_type() {
    $labels = array(
        'name'               => _x('Actors', 'post type general name', 'movies-theme'),
        'singular_name'      => _x('Actor', 'post type singular name', 'movies-theme'),
        'menu_name'          => _x('Actors', 'admin menu', 'movies-theme'),
        'add_new'            => _x('Add New', 'actor', 'movies-theme'),
        'add_new_item'       => __('Add New Actor', 'movies-theme'),
        'new_item'           => __('New Actor', 'movies-theme'),
        'edit_item'          => __('Edit Actor', 'movies-theme'),
        'view_item'          => __('View Actor', 'movies-theme'),
        'all_items'          => __('All Actors', 'movies-theme'),
        'search_items'       => __('Search Actors', 'movies-theme'),
        'not_found'          => __('No actors found.', 'movies-theme'),
        'not_found_in_trash' => __('No actors found in Trash.', 'movies-theme')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'actors'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-groups',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest'       => true,
    );

    register_post_type('actor', $args);
}
add_action('init', 'movies_register_actor_post_type');

// ==========================================
// ADMIN COLUMNS FOR ACTORS
// ==========================================

/**
 * Add custom columns to Actors admin list
 */
function actors_add_admin_columns($columns) {
    // Insert new columns after title
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['birth_date'] = __('Birth Date', 'movies-theme');
            $new_columns['birth_place'] = __('Birth Place', 'movies-theme');
            $new_columns['popularity'] = __('Popularity', 'movies-theme');
            $new_columns['known_for'] = __('Known For', 'movies-theme');
        }
    }
    return $new_columns;
}
add_filter('manage_actor_posts_columns', 'actors_add_admin_columns');

/**
 * Populate custom columns content for actors
 */
function actors_populate_admin_columns($column, $post_id) {
    switch ($column) {
        case 'birth_date':
            $birth_date = get_post_meta($post_id, 'birthday', true);
            if ($birth_date) {
                $formatted_date = date('M j, Y', strtotime($birth_date));
                $age = floor((time() - strtotime($birth_date)) / 31556926); // Calculate age
                echo esc_html($formatted_date);
                echo '<br><small>(' . $age . ' years old)</small>';
            } else {
                echo '<span style="color: #999;">—</span>';
            }
            break;
            
        case 'birth_place':
            $place_of_birth = get_post_meta($post_id, 'place_of_birth', true);
            if ($place_of_birth) {
                echo esc_html($place_of_birth);
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
            
        case 'known_for':
            $known_for_department = get_post_meta($post_id, 'known_for_department', true);
            if ($known_for_department) {
                echo '<span style="background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-size: 11px;">' . 
                     esc_html(strtoupper($known_for_department)) . '</span>';
            } else {
                echo '<span style="color: #999;">—</span>';
            }
            break;
    }
}
add_action('manage_actor_posts_custom_column', 'actors_populate_admin_columns', 10, 2);

/**
 * Make custom columns sortable for actors
 */
function actors_sortable_admin_columns($columns) {
    $columns['birth_date'] = 'birthday';
    $columns['popularity'] = 'popularity';
    return $columns;
}
add_filter('manage_edit-actor_sortable_columns', 'actors_sortable_admin_columns');

/**
 * Handle custom column sorting for actors
 */
function actors_admin_column_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    global $typenow;
    if ($typenow !== 'actor') {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    switch ($orderby) {
        case 'birthday':
            $query->set('meta_key', 'birthday');
            $query->set('orderby', 'meta_value');
            break;
            
        case 'popularity':
            $query->set('meta_key', 'popularity');
            $query->set('orderby', 'meta_value_num');
            break;
    }
}
add_action('pre_get_posts', 'actors_admin_column_orderby');

/**
 * Add filter dropdown for actors
 */
function actors_add_admin_filters() {
    global $typenow;
    
    if ($typenow === 'actor') {
        // Department filter
        $current_department = isset($_GET['actor_department_filter']) ? $_GET['actor_department_filter'] : '';
        $departments = actors_get_available_departments();
        
        if (!empty($departments)) {
            ?>
            <select name="actor_department_filter">
                <option value=""><?php _e('All Departments', 'movies-theme'); ?></option>
                <?php foreach ($departments as $department) : ?>
                    <option value="<?php echo esc_attr($department); ?>" <?php selected($current_department, $department); ?>>
                        <?php echo esc_html(ucfirst($department)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php
        }
    }
}
add_action('restrict_manage_posts', 'actors_add_admin_filters');

/**
 * Handle admin filter queries for actors
 */
function actors_handle_admin_filters($query) {
    global $pagenow, $typenow;
    
    if ($pagenow === 'edit.php' && $typenow === 'actor' && $query->is_main_query()) {
        
        // Handle department filter
        if (!empty($_GET['actor_department_filter'])) {
            $query->set('meta_query', array(
                array(
                    'key' => 'known_for_department',
                    'value' => $_GET['actor_department_filter'],
                    'compare' => '='
                )
            ));
        }
    }
}
add_action('pre_get_posts', 'actors_handle_admin_filters');

/**
 * Get available departments from actors
 */
function actors_get_available_departments() {
    global $wpdb;
    
    $departments = $wpdb->get_col("
        SELECT DISTINCT meta_value as department
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = 'known_for_department' 
        AND p.post_type = 'actor'
        AND p.post_status = 'publish'
        AND pm.meta_value != ''
        ORDER BY department ASC
    ");
    
    return array_filter($departments);
} 