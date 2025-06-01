<?php
/**
 * API Functions for Movies Theme
 * Functions to get data from imported TMDB content
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get upcoming movies (next 10, grouped by month/year)
 */
function movies_get_upcoming_movies($limit = 10) {
    $upcoming_movies = get_posts(array(
        'post_type' => 'movie',
        'posts_per_page' => $limit,
        'meta_key' => 'release_date',
        'meta_query' => array(
            array(
                'key' => 'release_date',
                'value' => date('Y-m-d'),
                'compare' => '>='
            )
        ),
        'orderby' => 'meta_value',
        'order' => 'ASC'
    ));
    
    // Group by month/year
    $grouped_movies = array();
    foreach ($upcoming_movies as $movie) {
        $release_date = get_post_meta($movie->ID, 'release_date', true);
        if ($release_date) {
            $month_year = date('F Y', strtotime($release_date));
            if (!isset($grouped_movies[$month_year])) {
                $grouped_movies[$month_year] = array();
            }
            $grouped_movies[$month_year][] = $movie;
        }
    }
    
    return $grouped_movies;
}

/**
 * Get popular movies
 */
function movies_get_popular_movies($limit = 20) {
    return get_posts(array(
        'post_type' => 'movie',
        'posts_per_page' => $limit,
        'meta_key' => 'popularity',
        'orderby' => 'meta_value_num',
        'order' => 'DESC'
    ));
}

/**
 * Get top 10 popular actors
 */
function movies_get_popular_actors($limit = 10) {
    return get_posts(array(
        'post_type' => 'actor',
        'posts_per_page' => $limit,
        'meta_key' => 'popularity',
        'orderby' => 'meta_value_num',
        'order' => 'DESC'
    ));
}

/**
 * Get movies filtered by criteria
 */
function movies_get_filtered_movies($args = array()) {
    $defaults = array(
        'post_type' => 'movie',
        'posts_per_page' => 20,
        'paged' => 1
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Handle search
    if (!empty($args['search'])) {
        $args['s'] = $args['search'];
        unset($args['search']);
    }
    
    // Handle year filter
    if (!empty($args['year'])) {
        $args['meta_query'][] = array(
            'key' => 'release_date',
            'value' => array($args['year'] . '-01-01', $args['year'] . '-12-31'),
            'compare' => 'BETWEEN',
            'type' => 'DATE'
        );
        unset($args['year']);
    }
    
    // Handle genre filter
    if (!empty($args['genre'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'genre',
            'field' => 'slug',
            'terms' => $args['genre']
        );
        unset($args['genre']);
    }
    
    return new WP_Query($args);
}

/**
 * Get actors filtered by criteria
 */
function movies_get_filtered_actors($args = array()) {
    $defaults = array(
        'post_type' => 'actor',
        'posts_per_page' => 20,
        'paged' => 1,
        'orderby' => 'title',
        'order' => 'ASC'
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Handle search
    if (!empty($args['search'])) {
        $args['s'] = $args['search'];
        unset($args['search']);
    }
    
    // Handle movie filter (actors who appeared in specific movie)
    if (!empty($args['movie'])) {
        $args['meta_query'][] = array(
            'key' => 'movie_credits',
            'value' => $args['movie'],
            'compare' => 'LIKE'
        );
        unset($args['movie']);
    }
    
    return new WP_Query($args);
}

/**
 * Get movie details with all metadata
 */
function movies_get_movie_details($post_id) {
    $movie = get_post($post_id);
    if (!$movie || $movie->post_type !== 'movie') {
        return false;
    }
    
    // Get all metadata
    $metadata = get_post_meta($post_id);
    $movie_data = array(
        'post' => $movie,
        'tmdb_id' => $metadata['tmdb_id'][0] ?? '',
        'release_date' => $metadata['release_date'][0] ?? '',
        'runtime' => $metadata['runtime'][0] ?? '',
        'popularity' => $metadata['popularity'][0] ?? '',
        'vote_average' => $metadata['vote_average'][0] ?? '',
        'vote_count' => $metadata['vote_count'][0] ?? '',
        'original_language' => $metadata['original_language'][0] ?? '',
        'original_title' => $metadata['original_title'][0] ?? '',
        'tagline' => $metadata['tagline'][0] ?? '',
        'homepage' => $metadata['homepage'][0] ?? '',
        'imdb_id' => $metadata['imdb_id'][0] ?? '',
        'production_companies' => json_decode($metadata['production_companies'][0] ?? '[]', true),
        'production_countries' => json_decode($metadata['production_countries'][0] ?? '[]', true),
        'spoken_languages' => json_decode($metadata['spoken_languages'][0] ?? '[]', true),
        'alternative_titles' => json_decode($metadata['alternative_titles'][0] ?? '[]', true),
        'credits' => json_decode($metadata['credits'][0] ?? '[]', true),
        'videos' => json_decode($metadata['videos'][0] ?? '[]', true),
        'similar' => json_decode($metadata['similar'][0] ?? '[]', true),
        'reviews' => json_decode($metadata['reviews'][0] ?? '[]', true),
        'genres' => wp_get_post_terms($post_id, 'genre'),
        'poster_url' => get_the_post_thumbnail_url($post_id, 'large')
    );
    
    return $movie_data;
}

/**
 * Get actor details with all metadata
 */
function movies_get_actor_details($post_id) {
    $actor = get_post($post_id);
    if (!$actor || $actor->post_type !== 'actor') {
        return false;
    }
    
    // Get all metadata
    $metadata = get_post_meta($post_id);
    $actor_data = array(
        'post' => $actor,
        'tmdb_id' => $metadata['tmdb_id'][0] ?? '',
        'birthday' => $metadata['birthday'][0] ?? '',
        'deathday' => $metadata['deathday'][0] ?? '',
        'place_of_birth' => $metadata['place_of_birth'][0] ?? '',
        'homepage' => $metadata['homepage'][0] ?? '',
        'popularity' => $metadata['popularity'][0] ?? '',
        'also_known_as' => json_decode($metadata['also_known_as'][0] ?? '[]', true),
        'movie_credits' => json_decode($metadata['movie_credits'][0] ?? '[]', true),
        'images' => json_decode($metadata['images'][0] ?? '[]', true),
        'photo_url' => get_the_post_thumbnail_url($post_id, 'large')
    );
    
    return $actor_data;
}

/**
 * Get similar movies
 */
function movies_get_similar_movies($post_id, $limit = 6) {
    // Get genres of current movie
    $genres = wp_get_post_terms($post_id, 'genre', array('fields' => 'ids'));
    
    if (empty($genres)) {
        return array();
    }
    
    return get_posts(array(
        'post_type' => 'movie',
        'posts_per_page' => $limit,
        'post__not_in' => array($post_id),
        'tax_query' => array(
            array(
                'taxonomy' => 'genre',
                'field' => 'term_id',
                'terms' => $genres
            )
        ),
        'meta_key' => 'popularity',
        'orderby' => 'meta_value_num',
        'order' => 'DESC'
    ));
}

/**
 * Get movie trailer URL
 */
function movies_get_movie_trailer($post_id) {
    $videos = get_post_meta($post_id, 'videos', true);
    if (empty($videos)) {
        return false;
    }
    
    $videos = json_decode($videos, true);
    
    // Look for trailer
    foreach ($videos as $video) {
        if ($video['type'] === 'Trailer' && $video['site'] === 'YouTube') {
            return 'https://www.youtube.com/watch?v=' . $video['key'];
        }
    }
    
    return false;
}

/**
 * Search movies and actors
 */
function movies_global_search($query, $limit = 10) {
    $movies = get_posts(array(
        'post_type' => 'movie',
        'posts_per_page' => $limit,
        's' => $query
    ));
    
    $actors = get_posts(array(
        'post_type' => 'actor',
        'posts_per_page' => $limit,
        's' => $query
    ));
    
    return array(
        'movies' => $movies,
        'actors' => $actors
    );
}

/**
 * Calculate custom search ranking formula: (V * P) / D
 * V: views, P: popularity, D: days since release
 */
function movies_calculate_search_score($post_id, $post_type = 'movie') {
    $popularity = (float) get_post_meta($post_id, 'popularity', true);
    $views = (int) get_post_meta($post_id, 'views', true) ?: 1; // Default to 1 if no views
    
    if ($post_type === 'movie') {
        $release_date = get_post_meta($post_id, 'release_date', true);
        $days_since_release = $release_date ? (time() - strtotime($release_date)) / (24 * 60 * 60) : 1;
    } else {
        $days_since_release = (time() - strtotime(get_post($post_id)->post_date)) / (24 * 60 * 60);
    }
    
    return ($views * $popularity) / max($days_since_release, 1);
}

/**
 * Get all available genres
 */
function movies_get_all_genres() {
    return get_terms(array(
        'taxonomy' => 'genre',
        'hide_empty' => false
    ));
}

/**
 * Get all available years from movies
 */
function movies_get_all_years() {
    global $wpdb;
    
    $years = $wpdb->get_col("
        SELECT DISTINCT YEAR(STR_TO_DATE(meta_value, '%Y-%m-%d')) as year
        FROM {$wpdb->postmeta} pm
        JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = 'release_date'
        AND p.post_type = 'movie'
        AND p.post_status = 'publish'
        AND meta_value != ''
        ORDER BY year DESC
    ");
    
    return array_filter($years);
} 