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
// ADMIN COLUMNS FOR MOVIES
// ==========================================

/**
 * Add custom columns to Movies admin list
 */
function movies_add_admin_columns($columns) {
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

// ==========================================
// METABOXES FOR MOVIE DETAILS (SIMPLIFIED)
// ==========================================

/**
 * Add metaboxes for movie edit screen
 */
function movies_add_meta_boxes() {
    add_meta_box(
        'movie_details',
        __('Movie Details', 'movies-theme'),
        'movies_details_metabox_callback',
        'movie',
        'normal',
        'default'
    );
    
    add_meta_box(
        'movie_cast_crew',
        __('Cast & Crew', 'movies-theme'),
        'movies_cast_crew_metabox_callback',
        'movie',
        'normal',
        'default'
    );
    
    add_meta_box(
        'movie_videos',
        __('Trailers & Videos', 'movies-theme'),
        'movies_videos_metabox_callback',
        'movie',
        'normal',
        'default'
    );
    
    add_meta_box(
        'movie_tmdb_info',
        __('TMDB Information', 'movies-theme'),
        'movies_tmdb_info_metabox_callback',
        'movie',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'movies_add_meta_boxes');

/**
 * Display movie details metabox (simplified)
 */
function movies_details_metabox_callback($post) {
    $movie_data = get_post_meta($post->ID, 'movie_data', true);
    
    if (empty($movie_data)) {
        echo '<p>' . __('No movie data found. Import or update movie data from TMDB to see details.', 'movies-theme') . '</p>';
        return;
    }
    
    $movie_data = is_string($movie_data) ? json_decode($movie_data, true) : $movie_data;
    
    if (!$movie_data) {
        echo '<p>' . __('Invalid movie data.', 'movies-theme') . '</p>';
        return;
    }
    
    // Basic movie information
    $fields = [
        'original_title' => __('Original Title:', 'movies-theme'),
        'original_language' => __('Original Language:', 'movies-theme'),
        'release_date' => __('Release Date:', 'movies-theme'),
        'runtime' => __('Runtime:', 'movies-theme'),
        'budget' => __('Budget:', 'movies-theme'),
        'revenue' => __('Revenue:', 'movies-theme'),
        'vote_average' => __('Rating:', 'movies-theme'),
        'popularity' => __('Popularity:', 'movies-theme'),
    ];
    
    echo '<table class="form-table">';
    foreach ($fields as $key => $label) {
        if (!empty($movie_data[$key])) {
            echo '<tr>';
            echo '<th scope="row">' . esc_html($label) . '</th>';
            echo '<td>';
            
            switch ($key) {
                case 'original_language':
                    echo esc_html(strtoupper($movie_data[$key]));
                    break;
                case 'release_date':
                    echo date_i18n('F j, Y', strtotime($movie_data[$key]));
                    break;
                case 'runtime':
                    echo esc_html($movie_data[$key]) . ' minutes';
                    break;
                case 'budget':
                case 'revenue':
                    echo '$' . number_format($movie_data[$key]);
                    break;
                case 'vote_average':
                    echo '★ ' . number_format($movie_data[$key], 1) . '/10';
                    if (!empty($movie_data['vote_count'])) {
                        echo ' (' . number_format($movie_data['vote_count']) . ' votes)';
                    }
                    break;
                case 'popularity':
                    echo number_format($movie_data[$key], 1);
                    break;
                default:
                    echo esc_html($movie_data[$key]);
            }
            
            echo '</td>';
            echo '</tr>';
        }
    }
    echo '</table>';
    
    // Genres
    if (!empty($movie_data['genres'])) {
        echo '<h4>' . __('Genres', 'movies-theme') . '</h4>';
        echo '<p>';
        $genre_names = array_column($movie_data['genres'], 'name');
        echo esc_html(implode(', ', $genre_names));
        echo '</p>';
    }
    
    // Production Companies
    if (!empty($movie_data['production_companies'])) {
        echo '<h4>' . __('Production Companies', 'movies-theme') . '</h4>';
        echo '<ul>';
        foreach ($movie_data['production_companies'] as $company) {
            echo '<li>' . esc_html($company['name']);
            if (!empty($company['origin_country'])) {
                echo ' (' . esc_html($company['origin_country']) . ')';
            }
            echo '</li>';
        }
        echo '</ul>';
    }
}

/**
 * Display cast & crew metabox (simplified)
 */
function movies_cast_crew_metabox_callback($post) {
    $movie_data = get_post_meta($post->ID, 'movie_data', true);
    
    if (empty($movie_data)) {
        echo '<p>' . __('No cast and crew data found.', 'movies-theme') . '</p>';
        return;
    }
    
    $movie_data = is_string($movie_data) ? json_decode($movie_data, true) : $movie_data;
    $credits = isset($movie_data['credits']) ? $movie_data['credits'] : [];
    
    if (empty($credits)) {
        echo '<p>' . __('No cast and crew information available.', 'movies-theme') . '</p>';
        return;
    }
    
    // Cast
    if (!empty($credits['cast'])) {
        echo '<h4>' . sprintf(__('Cast (%d)', 'movies-theme'), count($credits['cast'])) . '</h4>';
        echo '<ul>';
        foreach (array_slice($credits['cast'], 0, 10) as $cast_member) {
            echo '<li><strong>' . esc_html($cast_member['name']) . '</strong>';
            if (!empty($cast_member['character'])) {
                echo ' as ' . esc_html($cast_member['character']);
            }
            echo '</li>';
        }
        echo '</ul>';
    }
    
    // Crew (key departments only)
    if (!empty($credits['crew'])) {
        $key_departments = ['Directing', 'Writing', 'Production'];
        foreach ($key_departments as $dept) {
            $dept_crew = array_filter($credits['crew'], function($member) use ($dept) {
                return ($member['department'] ?? '') === $dept;
            });
            
            if (!empty($dept_crew)) {
                echo '<h4>' . esc_html($dept) . '</h4>';
                echo '<ul>';
                foreach (array_slice($dept_crew, 0, 5) as $crew_member) {
                    echo '<li><strong>' . esc_html($crew_member['name']) . '</strong>';
                    if (!empty($crew_member['job'])) {
                        echo ' - ' . esc_html($crew_member['job']);
                    }
                    echo '</li>';
                }
                echo '</ul>';
            }
        }
    }
}

/**
 * Display videos metabox (simplified)
 */
function movies_videos_metabox_callback($post) {
    $movie_data = get_post_meta($post->ID, 'movie_data', true);
    
    if (empty($movie_data)) {
        echo '<p>' . __('No video data found.', 'movies-theme') . '</p>';
        return;
    }
    
    $movie_data = is_string($movie_data) ? json_decode($movie_data, true) : $movie_data;
    $videos = isset($movie_data['videos']['results']) ? $movie_data['videos']['results'] : [];
    
    if (empty($videos)) {
        echo '<p>' . __('No trailers or videos available.', 'movies-theme') . '</p>';
        return;
    }
    
    echo '<ul>';
    foreach ($videos as $video) {
        echo '<li>';
        echo '<strong>' . esc_html($video['name']) . '</strong> ';
        echo '<span>(' . esc_html($video['type']) . ' - ' . esc_html($video['site']) . ')</span>';
            
        if ($video['site'] === 'YouTube') {
            $video_url = 'https://www.youtube.com/watch?v=' . $video['key'];
            echo ' <a href="' . esc_url($video_url) . '" target="_blank">Watch</a>';
        }
        echo '</li>';
    }
    echo '</ul>';
}

/**
 * Display TMDB info metabox (simplified)
 */
function movies_tmdb_info_metabox_callback($post) {
    $tmdb_id = get_post_meta($post->ID, 'tmdb_id', true);
    $movie_data = get_post_meta($post->ID, 'movie_data', true);
    
    if ($movie_data) {
        $movie_data = is_string($movie_data) ? json_decode($movie_data, true) : $movie_data;
    }
    
    echo '<table class="form-table">';
    
    if ($tmdb_id) {
        echo '<tr>';
        echo '<th scope="row">' . __('TMDB ID:', 'movies-theme') . '</th>';
        echo '<td>' . esc_html($tmdb_id) . '<br>';
        echo '<a href="https://www.themoviedb.org/movie/' . esc_attr($tmdb_id) . '" target="_blank">View on TMDB</a>';
        echo '</td>';
        echo '</tr>';
    }
    
    if ($movie_data && !empty($movie_data['imdb_id'])) {
        echo '<tr>';
        echo '<th scope="row">' . __('IMDb ID:', 'movies-theme') . '</th>';
        echo '<td>' . esc_html($movie_data['imdb_id']) . '<br>';
        echo '<a href="https://www.imdb.com/title/' . esc_attr($movie_data['imdb_id']) . '/" target="_blank">View on IMDb</a>';
        echo '</td>';
        echo '</tr>';
    }
    
    if ($movie_data && !empty($movie_data['homepage'])) {
        echo '<tr>';
        echo '<th scope="row">' . __('Official Website:', 'movies-theme') . '</th>';
        echo '<td><a href="' . esc_url($movie_data['homepage']) . '" target="_blank">Visit Website</a></td>';
        echo '</tr>';
    }
    
    if ($movie_data && !empty($movie_data['status'])) {
        echo '<tr>';
        echo '<th scope="row">' . __('Status:', 'movies-theme') . '</th>';
        echo '<td>' . esc_html($movie_data['status']) . '</td>';
        echo '</tr>';
    }
    
    if ($movie_data && !empty($movie_data['tagline'])) {
        echo '<tr>';
        echo '<th scope="row">' . __('Tagline:', 'movies-theme') . '</th>';
        echo '<td><em>"' . esc_html($movie_data['tagline']) . '"</em></td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    if (empty($tmdb_id)) {
        echo '<p style="color: #d63638; font-style: italic;">';
        echo __('This movie has not been imported from TMDB. Use the TMDB Import tool to get complete movie data.', 'movies-theme');
        echo '</p>';
    }
}

// ==========================================
// HELPER FUNCTIONS
// ==========================================

/**
 * Find actor by TMDB ID
 */
function movies_find_actor_by_tmdb_id($tmdb_id) {
    $posts = get_posts(array(
        'post_type' => 'actor',
        'meta_query' => array(
            array(
                'key' => 'tmdb_id',
                'value' => $tmdb_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1
    ));
            
    return !empty($posts) ? $posts[0] : null;
        }

/**
 * Find movie by TMDB ID
 */
function movies_find_movie_by_tmdb_id($tmdb_id) {
    $posts = get_posts(array(
        'post_type' => 'movie',
        'meta_query' => array(
            array(
                'key' => 'tmdb_id',
                'value' => $tmdb_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1
    ));
    
    return !empty($posts) ? $posts[0] : null;
}

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