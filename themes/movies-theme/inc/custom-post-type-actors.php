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
            $new_columns['movie_credits_count'] = __('Movies', 'movies-theme');
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
            
        case 'movie_credits_count':
            $movie_credits = get_post_meta($post_id, 'movie_credits', true);
            if ($movie_credits) {
                $credits_data = json_decode($movie_credits, true);
                $cast_count = isset($credits_data['cast']) ? count($credits_data['cast']) : 0;
                $crew_count = isset($credits_data['crew']) ? count($credits_data['crew']) : 0;
                $total_count = $cast_count + $crew_count;
                
                if ($total_count > 0) {
                    echo '<strong>' . $total_count . '</strong>';
                    echo '<br><small style="color: #666;">';
                    if ($cast_count > 0) {
                        echo $cast_count . ' ' . _n('acting', 'acting', $cast_count, 'movies-theme');
                    }
                    if ($cast_count > 0 && $crew_count > 0) {
                        echo ', ';
                    }
                    if ($crew_count > 0) {
                        echo $crew_count . ' ' . _n('crew', 'crew', $crew_count, 'movies-theme');
                    }
                    echo '</small>';
                } else {
                    echo '<span style="color: #999;">0</span>';
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
    $columns['movie_credits_count'] = 'movie_credits_count';
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
            
        case 'movie_credits_count':
            // Para ordenar por número de créditos, necesitamos usar un meta_query complejo
            // Por simplicidad, ordenaremos por popularidad que generalmente correlaciona
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

// ==========================================
// METABOX FOR ACTOR MOVIE CREDITS
// ==========================================

/**
 * Add metaboxes for actor edit screen
 */
function actors_add_meta_boxes() {
    add_meta_box(
        'actor_movie_credits',
        __('Movie Credits', 'movies-theme'),
        'actors_movie_credits_metabox_callback',
        'actor',
        'normal',
        'default'
    );
    
    add_meta_box(
        'actor_details',
        __('Actor Details', 'movies-theme'),
        'actors_details_metabox_callback',
        'actor',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'actors_add_meta_boxes');

/**
 * Display movie credits metabox
 */
function actors_movie_credits_metabox_callback($post) {
    $movie_credits = get_post_meta($post->ID, 'movie_credits', true);
    
    if (empty($movie_credits)) {
        echo '<p>' . __('No movie credits found for this actor.', 'movies-theme') . '</p>';
        echo '<p><em>' . __('Import or update actor data from TMDB to see movie credits.', 'movies-theme') . '</em></p>';
        return;
    }
    
    $credits_data = json_decode($movie_credits, true);
    
    if (!$credits_data || (!isset($credits_data['cast']) && !isset($credits_data['crew']))) {
        echo '<p>' . __('Invalid movie credits data.', 'movies-theme') . '</p>';
        return;
    }
    
    ?>
    <div class="actor-movie-credits">
        <style>
        .actor-movie-credits {
            max-height: 500px;
            overflow-y: auto;
        }
        .credits-section {
            margin-bottom: 25px;
        }
        .credits-section h4 {
            margin: 0 0 10px 0;
            padding: 8px 12px;
            background: #f0f0f1;
            border-left: 4px solid #2271b1;
            font-size: 14px;
        }
        .credits-list {
            border: 1px solid #ddd;
            background: #fff;
        }
        .credit-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .credit-item:last-child {
            border-bottom: none;
        }
        .credit-poster {
            width: 50px;
            height: 75px;
            background: #f0f0f1;
            margin-right: 15px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #666;
            border-radius: 3px;
        }
        .credit-poster img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 3px;
        }
        .credit-info {
            flex: 1;
        }
        .credit-title {
            font-weight: 600;
            margin: 0 0 5px 0;
            font-size: 14px;
        }
        .credit-character {
            color: #0073aa;
            font-style: italic;
            margin: 0 0 3px 0;
            font-size: 13px;
        }
        .credit-year {
            color: #666;
            font-size: 12px;
        }
        .no-credits {
            padding: 20px;
            text-align: center;
            color: #666;
            font-style: italic;
        }
        </style>
        
        <?php if (!empty($credits_data['cast'])) : ?>
            <div class="credits-section">
                <h4><?php printf(__('Acting Credits (%d)', 'movies-theme'), count($credits_data['cast'])); ?></h4>
                <div class="credits-list">
                    <?php 
                    // Ordenar por fecha de lanzamiento (más reciente primero)
                    $cast_credits = $credits_data['cast'];
                    usort($cast_credits, function($a, $b) {
                        $date_a = $a['release_date'] ?? '1900-01-01';
                        $date_b = $b['release_date'] ?? '1900-01-01';
                        return strcmp($date_b, $date_a);
                    });
                    
                    foreach ($cast_credits as $credit) : 
                        $poster_url = !empty($credit['poster_path']) 
                            ? 'https://image.tmdb.org/t/p/w92' . $credit['poster_path'] 
                            : false;
                        $year = !empty($credit['release_date']) ? date('Y', strtotime($credit['release_date'])) : 'TBA';
                    ?>
                        <div class="credit-item">
                            <div class="credit-poster">
                                <?php if ($poster_url) : ?>
                                    <img src="<?php echo esc_url($poster_url); ?>" alt="<?php echo esc_attr($credit['title']); ?>">
                                <?php else : ?>
                                    No Image
                                <?php endif; ?>
                            </div>
                            <div class="credit-info">
                                <div class="credit-title"><?php echo esc_html($credit['title']); ?></div>
                                <?php if (!empty($credit['character'])) : ?>
                                    <div class="credit-character"><?php _e('as', 'movies-theme'); ?> <?php echo esc_html($credit['character']); ?></div>
                                <?php endif; ?>
                                <div class="credit-year"><?php echo esc_html($year); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($credits_data['crew'])) : ?>
            <div class="credits-section">
                <h4><?php printf(__('Crew Credits (%d)', 'movies-theme'), count($credits_data['crew'])); ?></h4>
                <div class="credits-list">
                    <?php 
                    // Ordenar por fecha de lanzamiento (más reciente primero)
                    $crew_credits = $credits_data['crew'];
                    usort($crew_credits, function($a, $b) {
                        $date_a = $a['release_date'] ?? '1900-01-01';
                        $date_b = $b['release_date'] ?? '1900-01-01';
                        return strcmp($date_b, $date_a);
                    });
                    
                    foreach ($crew_credits as $credit) : 
                        $poster_url = !empty($credit['poster_path']) 
                            ? 'https://image.tmdb.org/t/p/w92' . $credit['poster_path'] 
                            : false;
                        $year = !empty($credit['release_date']) ? date('Y', strtotime($credit['release_date'])) : 'TBA';
                    ?>
                        <div class="credit-item">
                            <div class="credit-poster">
                                <?php if ($poster_url) : ?>
                                    <img src="<?php echo esc_url($poster_url); ?>" alt="<?php echo esc_attr($credit['title']); ?>">
                                <?php else : ?>
                                    No Image
                                <?php endif; ?>
                            </div>
                            <div class="credit-info">
                                <div class="credit-title"><?php echo esc_html($credit['title']); ?></div>
                                <?php if (!empty($credit['job'])) : ?>
                                    <div class="credit-character"><?php echo esc_html($credit['job']); ?></div>
                                <?php endif; ?>
                                <div class="credit-year"><?php echo esc_html($year); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (empty($credits_data['cast']) && empty($credits_data['crew'])) : ?>
            <div class="no-credits">
                <?php _e('No movie credits available for this actor.', 'movies-theme'); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Display actor details metabox
 */
function actors_details_metabox_callback($post) {
    $tmdb_id = get_post_meta($post->ID, 'tmdb_id', true);
    $birthday = get_post_meta($post->ID, 'birthday', true);
    $deathday = get_post_meta($post->ID, 'deathday', true);
    $place_of_birth = get_post_meta($post->ID, 'place_of_birth', true);
    $popularity = get_post_meta($post->ID, 'popularity', true);
    $homepage = get_post_meta($post->ID, 'homepage', true);
    $also_known_as = get_post_meta($post->ID, 'also_known_as', true);
    $gender = get_post_meta($post->ID, 'gender', true);
    $known_for_department = get_post_meta($post->ID, 'known_for_department', true);
    $profile_path = get_post_meta($post->ID, 'profile_path', true);
    $imdb_id = get_post_meta($post->ID, 'imdb_id', true);
    
    // Gender mapping
    $gender_labels = array(
        0 => __('Not specified', 'movies-theme'),
        1 => __('Female', 'movies-theme'),
        2 => __('Male', 'movies-theme'),
        3 => __('Non-binary', 'movies-theme')
    );
    
    ?>
    <style>
    .actor-details-metabox {
        font-size: 13px;
    }
    .actor-detail-row {
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f1;
    }
    .actor-detail-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    .actor-detail-label {
        font-weight: 600;
        display: block;
        margin-bottom: 3px;
        color: #1d2327;
    }
    .actor-detail-value {
        color: #50575e;
    }
    .actor-detail-value a {
        color: #2271b1;
        text-decoration: none;
    }
    .actor-detail-value a:hover {
        text-decoration: underline;
    }
    .tmdb-link {
        background: #ff6600;
        color: white !important;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 11px;
        text-decoration: none !important;
        font-weight: 500;
    }
    .tmdb-link:hover {
        background: #e55a00;
    }
    .also-known-as {
        font-style: italic;
        font-size: 12px;
    }
    </style>
    
    <div class="actor-details-metabox">
        <?php if ($tmdb_id) : ?>
            <div class="actor-detail-row">
                <span class="actor-detail-label"><?php _e('TMDB ID:', 'movies-theme'); ?></span>
                <div class="actor-detail-value">
                    <?php echo esc_html($tmdb_id); ?>
                    <a href="https://www.themoviedb.org/person/<?php echo esc_attr($tmdb_id); ?>" target="_blank" class="tmdb-link">
                        <?php _e('View on TMDB', 'movies-theme'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($birthday) : ?>
            <div class="actor-detail-row">
                <span class="actor-detail-label"><?php _e('Birth Date:', 'movies-theme'); ?></span>
                <div class="actor-detail-value">
                    <?php 
                    echo date_i18n('F j, Y', strtotime($birthday));
                    $age = floor((time() - strtotime($birthday)) / 31556926);
                    echo ' <em>(' . sprintf(__('%d years old', 'movies-theme'), $age) . ')</em>';
                    ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($deathday) : ?>
            <div class="actor-detail-row">
                <span class="actor-detail-label"><?php _e('Death Date:', 'movies-theme'); ?></span>
                <div class="actor-detail-value">
                    <?php echo date_i18n('F j, Y', strtotime($deathday)); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($place_of_birth) : ?>
            <div class="actor-detail-row">
                <span class="actor-detail-label"><?php _e('Place of Birth:', 'movies-theme'); ?></span>
                <div class="actor-detail-value"><?php echo esc_html($place_of_birth); ?></div>
            </div>
        <?php endif; ?>
        
        <?php if ($gender !== '' && isset($gender_labels[$gender])) : ?>
            <div class="actor-detail-row">
                <span class="actor-detail-label"><?php _e('Gender:', 'movies-theme'); ?></span>
                <div class="actor-detail-value"><?php echo esc_html($gender_labels[$gender]); ?></div>
            </div>
        <?php endif; ?>
        
        <?php if ($known_for_department) : ?>
            <div class="actor-detail-row">
                <span class="actor-detail-label"><?php _e('Known For:', 'movies-theme'); ?></span>
                <div class="actor-detail-value">
                    <span style="background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-size: 11px;">
                        <?php echo esc_html(strtoupper($known_for_department)); ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($popularity) : ?>
            <div class="actor-detail-row">
                <span class="actor-detail-label"><?php _e('Popularity Score:', 'movies-theme'); ?></span>
                <div class="actor-detail-value"><?php echo number_format((float)$popularity, 2); ?></div>
            </div>
        <?php endif; ?>
        
        <?php if ($imdb_id) : ?>
            <div class="actor-detail-row">
                <span class="actor-detail-label"><?php _e('IMDb ID:', 'movies-theme'); ?></span>
                <div class="actor-detail-value">
                    <?php echo esc_html($imdb_id); ?>
                    <a href="https://www.imdb.com/name/<?php echo esc_attr($imdb_id); ?>/" target="_blank" class="tmdb-link" style="background: #f5c518; color: #000;">
                        <?php _e('View on IMDb', 'movies-theme'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($homepage) : ?>
            <div class="actor-detail-row">
                <span class="actor-detail-label"><?php _e('Homepage:', 'movies-theme'); ?></span>
                <div class="actor-detail-value">
                    <a href="<?php echo esc_url($homepage); ?>" target="_blank"><?php echo esc_html($homepage); ?></a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($profile_path) : ?>
            <div class="actor-detail-row">
                <span class="actor-detail-label"><?php _e('Profile Image Path:', 'movies-theme'); ?></span>
                <div class="actor-detail-value">
                    <small style="color: #666; font-family: monospace;"><?php echo esc_html($profile_path); ?></small>
                    <br>
                    <img src="https://image.tmdb.org/t/p/w92<?php echo esc_attr($profile_path); ?>" 
                         alt="Profile preview" style="max-width: 50px; margin-top: 5px; border-radius: 3px;">
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($also_known_as) : 
            $aliases = json_decode($also_known_as, true);
            if (!empty($aliases)) :
        ?>
            <div class="actor-detail-row">
                <span class="actor-detail-label"><?php _e('Also Known As:', 'movies-theme'); ?></span>
                <div class="actor-detail-value also-known-as">
                    <?php echo esc_html(implode(', ', array_slice($aliases, 0, 3))); ?>
                    <?php if (count($aliases) > 3) : ?>
                        <em><?php printf(__('and %d more', 'movies-theme'), count($aliases) - 3); ?></em>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; endif; ?>
        
        <?php if (empty($tmdb_id)) : ?>
            <div class="actor-detail-row">
                <div class="actor-detail-value" style="color: #d63638; font-style: italic;">
                    <?php _e('This actor has not been imported from TMDB. Use the TMDB Import tool to get complete actor data.', 'movies-theme'); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// ==========================================
// HELPER FUNCTIONS
// ==========================================

/**
 * Get actor statistics for dashboard
 */
function actors_get_statistics() {
    global $wpdb;
    
    $stats = array();
    
    // Total actors
    $stats['total'] = wp_count_posts('actor')->publish;
    
    // Actors with TMDB data
    $stats['with_tmdb_data'] = $wpdb->get_var("
        SELECT COUNT(DISTINCT p.ID) 
        FROM {$wpdb->posts} p 
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
        WHERE p.post_type = 'actor' 
        AND p.post_status = 'publish' 
        AND pm.meta_key = 'tmdb_id' 
        AND pm.meta_value != ''
    ");
    
    // Actors with movie credits
    $stats['with_credits'] = $wpdb->get_var("
        SELECT COUNT(DISTINCT p.ID) 
        FROM {$wpdb->posts} p 
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
        WHERE p.post_type = 'actor' 
        AND p.post_status = 'publish' 
        AND pm.meta_key = 'movie_credits' 
        AND pm.meta_value != '' 
        AND pm.meta_value != '{\"cast\":[],\"crew\":[]}'
    ");
    
    // Top actor by popularity
    $top_actor = $wpdb->get_row("
        SELECT p.ID, p.post_title, pm.meta_value as popularity
        FROM {$wpdb->posts} p 
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
        WHERE p.post_type = 'actor' 
        AND p.post_status = 'publish' 
        AND pm.meta_key = 'popularity' 
        ORDER BY CAST(pm.meta_value AS DECIMAL(10,2)) DESC 
        LIMIT 1
    ");
    
    $stats['top_actor'] = $top_actor;
    
    return $stats;
}

/**
 * Get actors that need credits update
 */
function actors_get_actors_needing_credits_update() {
    global $wpdb;
    
    return $wpdb->get_results("
        SELECT p.ID, p.post_title, pm_tmdb.meta_value as tmdb_id
        FROM {$wpdb->posts} p 
        INNER JOIN {$wpdb->postmeta} pm_tmdb ON p.ID = pm_tmdb.post_id 
        LEFT JOIN {$wpdb->postmeta} pm_credits ON p.ID = pm_credits.post_id AND pm_credits.meta_key = 'movie_credits'
        WHERE p.post_type = 'actor' 
        AND p.post_status = 'publish' 
        AND pm_tmdb.meta_key = 'tmdb_id' 
        AND pm_tmdb.meta_value != ''
        AND (
            pm_credits.meta_value IS NULL 
            OR pm_credits.meta_value = '' 
            OR pm_credits.meta_value = '{\"cast\":[],\"crew\":[]}'
        )
        ORDER BY p.post_title
        LIMIT 50
    ");
} 