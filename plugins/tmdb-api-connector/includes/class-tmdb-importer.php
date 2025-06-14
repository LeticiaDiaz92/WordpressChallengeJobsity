<?php
/**
 * TMDB Importer Class
 * Handles importing movies and actors from TMDB API
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMDB_Importer {
    
    private static $instance = null;
    private $api;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->api = TMDB_API::get_instance();
    }
    
    /**
     * Manual import
     */
    public function manual_import($import_type) {
        $results = array();
        
        switch ($import_type) {
            case 'popular_movies':
                $results = $this->import_popular_movies();
                break;
            case 'upcoming_movies':
                $results = $this->import_upcoming_movies();
                break;
            case 'popular_actors':
                $results = $this->import_popular_actors();
                break;
            case 'update_actor_credits':
                $results = $this->update_existing_actors_credits();
                break;
            case 'update_movie_details':
                $results = $this->update_existing_movies();
                break;
            case 'genres':
                $results = $this->import_genres();
                break;
            default:
                return array(
                    'success' => false,
                    'message' => __('Invalid import type', 'tmdb-api-connector')
                );
        }
        
        return $results;
    }
    
    /**
     * Import popular content (movies or actors)
     */
    public function import_popular($content_type) {
        switch ($content_type) {
            case 'movies':
                return $this->import_popular_movies();
            case 'actors':
                return $this->import_popular_actors();
            default:
                return array(
                    'success' => false,
                    'message' => __('Invalid content type', 'tmdb-api-connector')
                );
        }
    }
    
    /**
     * Import popular movies
     */
    private function import_popular_movies() {
        $limit = get_option('tmdb_import_limit', 20);
        $imported = 0;
        $errors = array();
        
        TMDB_Logger::log('Starting popular movies import', 'info', 'popular_movies');
        
        for ($page = 1; $page <= ceil($limit / 20); $page++) {
            $response = $this->api->get_popular_movies($page);
            
            if (!$response['success']) {
                $errors[] = $response['message'];
                continue;
            }
            
            $movies = $response['data']['results'];
            
            foreach ($movies as $movie_data) {
                if ($imported >= $limit) {
                    break 2;
                }
                
                $result = $this->import_movie($movie_data);
                if ($result['success']) {
                    $imported++;
                } else {
                    $errors[] = $result['message'];
                }
            }
        }
        
        $message = sprintf(__('Imported %d popular movies', 'tmdb-api-connector'), $imported);
        TMDB_Logger::log($message, 'success', 'popular_movies', $imported);
        
        return array(
            'success' => true,
            'message' => $message,
            'imported' => $imported,
            'errors' => $errors
        );
    }
    
    /**
     * Import upcoming movies
     */
    private function import_upcoming_movies() {
        $limit = get_option('tmdb_import_limit', 20);
        $update_existing = get_option('tmdb_update_existing_movies', '1') === '1';
        $imported = 0;
        $updated = 0;
        $errors = array();
        
        TMDB_Logger::log("Starting upcoming movies import (limit: {$limit}, update_existing: " . ($update_existing ? 'yes' : 'no') . ")", 'info', 'upcoming_movies');
        
        for ($page = 1; $page <= ceil($limit / 20); $page++) {
            TMDB_Logger::log("Fetching page {$page} from TMDB API...", 'info', 'upcoming_movies');
            
            $response = $this->api->get_upcoming_movies($page);
            
            if (!$response['success']) {
                $error_msg = "API request failed for page {$page}: " . $response['message'];
                TMDB_Logger::log($error_msg, 'error', 'upcoming_movies');
                $errors[] = $error_msg;
                continue;
            }
            
            $movies = $response['data']['results'] ?? array();
            $movie_count = count($movies);
            
            TMDB_Logger::log("Page {$page}: Found {$movie_count} movies", 'info', 'upcoming_movies');
            error_log("Page {$page} Movies: " . json_encode(array_column($movies, 'title')));
            
            if (empty($movies)) {
                TMDB_Logger::log("No movies found on page {$page}, stopping import", 'warning', 'upcoming_movies');
                break;
            }
            
            foreach ($movies as $movie_data) {
                if (($imported + $updated) >= $limit) {
                    TMDB_Logger::log("Reached import limit of {$limit}, stopping", 'info', 'upcoming_movies');
                    break 2;
                }
                
                $movie_title = $movie_data['title'] ?? 'Unknown';
                $movie_date = $movie_data['release_date'] ?? 'Unknown';
                
                TMDB_Logger::log("Processing movie: {$movie_title} (Release: {$movie_date})", 'info', 'upcoming_movies');
                
                $result = $this->import_or_update_upcoming_movie($movie_data);
                
                if ($result['success']) {
                    if ($result['action'] === 'imported') {
                        $imported++;
                        TMDB_Logger::log("✓ Imported: {$movie_title}", 'success', 'upcoming_movies');
                    } else {
                        $updated++;
                        TMDB_Logger::log("✓ Updated: {$movie_title}", 'success', 'upcoming_movies');
                    }
                } else {
                    TMDB_Logger::log("✗ Failed: {$movie_title} - " . $result['message'], 'error', 'upcoming_movies');
                    $errors[] = $result['message'];
                }
            }
        }
        
        $total_processed = $imported + $updated;
        $message = sprintf(__('Processed %d upcoming movies (%d imported, %d updated)', 'tmdb-api-connector'), 
                          $total_processed, $imported, $updated);
        
        if (!empty($errors)) {
            $message .= sprintf(__(' with %d errors', 'tmdb-api-connector'), count($errors));
        }
        
        TMDB_Logger::log($message, 'success', 'upcoming_movies', $total_processed);
        
        return array(
            'success' => true,
            'message' => $message,
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors
        );
    }
    
    /**
     * Import popular actors
     */
    private function import_popular_actors() {
        $limit = get_option('tmdb_import_limit', 20);
        $imported = 0;
        $errors = array();
        
        TMDB_Logger::log('Starting popular actors import', 'info', 'popular_actors');
        
        for ($page = 1; $page <= ceil($limit / 20); $page++) {
            $response = $this->api->get_popular_actors($page);
            
            if (!$response['success']) {
                $errors[] = $response['message'];
                continue;
            }
            
            $actors = $response['data']['results'];
            
            foreach ($actors as $actor_data) {
                if ($imported >= $limit) {
                    break 2;
                }
                
                $result = $this->import_actor($actor_data);
                if ($result['success']) {
                    $imported++;
                } else {
                    $errors[] = $result['message'];
                }
            }
        }
        
        $message = sprintf(__('Imported %d popular actors', 'tmdb-api-connector'), $imported);
        TMDB_Logger::log($message, 'success', 'popular_actors', $imported);
        
        return array(
            'success' => true,
            'message' => $message,
            'imported' => $imported,
            'errors' => $errors
        );
    }
    
    /**
     * Import genres
     */
    private function import_genres() {
        $response = $this->api->get_movie_genres();
        
        if (!$response['success']) {
            return $response;
        }
        
        $genres = $response['data']['genres'];
        $imported = 0;
        
        foreach ($genres as $genre_data) {
            $term = wp_insert_term($genre_data['name'], 'genre');
            
            if (!is_wp_error($term)) {
                update_term_meta($term['term_id'], 'tmdb_id', $genre_data['id']);
                $imported++;
            }
        }
        
        $message = sprintf(__('Imported %d genres', 'tmdb-api-connector'), $imported);
        TMDB_Logger::log($message, 'success', 'genres', $imported);
        
        return array(
            'success' => true,
            'message' => $message,
            'imported' => $imported
        );
    }
    
    /**
     * Import a single movie
     */
    private function import_movie($movie_data) {
        // Check if movie already exists
        $existing = get_posts(array(
            'post_type' => 'movie',
            'meta_key' => 'tmdb_id',
            'meta_value' => $movie_data['id'],
            'post_status' => 'any',
            'numberposts' => 1
        ));
        
        if (!empty($existing)) {
            return array(
                'success' => false,
                'message' => sprintf(__('Movie "%s" already exists', 'tmdb-api-connector'), $movie_data['title'])
            );
        }
        
        // Get detailed movie information
        $details_response = $this->api->get_movie_details($movie_data['id']);
        if (!$details_response['success']) {
            return $details_response;
        }
        
        $details = $details_response['data'];
        
        // Create movie post
        $post_data = array(
            'post_title' => sanitize_text_field($details['title']),
            'post_content' => wp_kses_post($details['overview']),
            'post_status' => 'publish',
            'post_type' => 'movie',
            'post_date' => $details['release_date'] ? date('Y-m-d H:i:s', strtotime($details['release_date'])) : current_time('mysql')
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return array(
                'success' => false,
                'message' => $post_id->get_error_message()
            );
        }
        
        // Add movie metadata
        $this->save_movie_metadata($post_id, $details);
        
        // Set genres
        $this->set_movie_genres($post_id, $details['genres']);
        
        // Download and set featured image
        if (!empty($details['poster_path'])) {
            $this->set_movie_poster($post_id, $details['poster_path'], $details['title']);
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('Movie "%s" imported successfully', 'tmdb-api-connector'), $details['title']),
            'post_id' => $post_id
        );
    }
    
    /**
     * Import a single actor
     */
    private function import_actor($actor_data) {
        // Check if actor already exists
        $existing = get_posts(array(
            'post_type' => 'actor',
            'meta_key' => 'tmdb_id',
            'meta_value' => $actor_data['id'],
            'post_status' => 'any',
            'numberposts' => 1
        ));
        
        if (!empty($existing)) {
            return array(
                'success' => false,
                'message' => sprintf(__('Actor "%s" already exists', 'tmdb-api-connector'), $actor_data['name'])
            );
        }
        
        // Get detailed actor information
        $details_response = $this->api->get_actor_details($actor_data['id']);
        if (!$details_response['success']) {
            return $details_response;
        }
        
        $details = $details_response['data'];
        
        // Get movie credits using dedicated endpoint for better data
        $credits_response = $this->api->get_actor_movie_credits($actor_data['id']);
        if ($credits_response['success']) {
            $details['movie_credits'] = $credits_response['data'];
        } else {
            // Fallback to empty array if credits request fails
            $details['movie_credits'] = array('cast' => array(), 'crew' => array());
        }
        
        // Create actor post
        $post_data = array(
            'post_title' => sanitize_text_field($details['name']),
            'post_content' => wp_kses_post($details['biography']),
            'post_status' => 'publish',
            'post_type' => 'actor'
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return array(
                'success' => false,
                'message' => $post_id->get_error_message()
            );
        }
        
        // Add actor metadata
        $this->save_actor_metadata($post_id, $details);
        
        // Download and set featured image
        if (!empty($details['profile_path'])) {
            $this->set_actor_photo($post_id, $details['profile_path'], $details['name']);
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('Actor "%s" imported successfully', 'tmdb-api-connector'), $details['name']),
            'post_id' => $post_id
        );
    }
    
    /**
     * Save movie metadata
     */
    private function save_movie_metadata($post_id, $movie_data) {
        $metadata = array(
            'tmdb_id' => $movie_data['id'],
            'release_date' => $movie_data['release_date'],
            'runtime' => $movie_data['runtime'],
            'budget' => $movie_data['budget'],
            'revenue' => $movie_data['revenue'],
            'popularity' => $movie_data['popularity'],
            'vote_average' => $movie_data['vote_average'],
            'vote_count' => $movie_data['vote_count'],
            'original_language' => $movie_data['original_language'],
            'original_title' => $movie_data['original_title'],
            'tagline' => $movie_data['tagline'],
            'homepage' => $movie_data['homepage'],
            'imdb_id' => $movie_data['imdb_id'],
            'poster_path' => $movie_data['poster_path'] ?? '',
            'backdrop_path' => $movie_data['backdrop_path'] ?? '',
            'status' => $movie_data['status'] ?? '',
            'production_companies' => wp_json_encode($movie_data['production_companies']),
            'production_countries' => wp_json_encode($movie_data['production_countries']),
            'spoken_languages' => wp_json_encode($movie_data['spoken_languages']),
            'alternative_titles' => wp_json_encode($movie_data['alternative_titles']['titles'] ?? array()),
            'credits' => wp_json_encode($movie_data['credits'] ?? array()),
            'videos' => wp_json_encode($movie_data['videos']['results'] ?? array()),
            'similar' => wp_json_encode($movie_data['similar']['results'] ?? array()),
            'reviews' => wp_json_encode($movie_data['reviews']['results'] ?? array()),
            'movie_data' => wp_json_encode($movie_data)
        );
        
        foreach ($metadata as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }
    
    /**
     * Save actor metadata
     */
    private function save_actor_metadata($post_id, $actor_data) {
        // Get IMDb ID from external_ids if available
        $imdb_id = '';
        if (!empty($actor_data['external_ids']['imdb_id'])) {
            $imdb_id = $actor_data['external_ids']['imdb_id'];
        }
        
        $metadata = array(
            'tmdb_id' => $actor_data['id'],
            'birthday' => $actor_data['birthday'],
            'deathday' => $actor_data['deathday'],
            'place_of_birth' => $actor_data['place_of_birth'],
            'homepage' => $actor_data['homepage'],
            'popularity' => $actor_data['popularity'],
            'gender' => $actor_data['gender'] ?? 0,
            'known_for_department' => $actor_data['known_for_department'] ?? '',
            'profile_path' => $actor_data['profile_path'] ?? '',
            'imdb_id' => $imdb_id,
            'also_known_as' => wp_json_encode($actor_data['also_known_as'] ?? array()),
            'images' => wp_json_encode($actor_data['images']['profiles'] ?? array())
        );

        // Handle movie credits with better structure
        $movie_credits = $actor_data['movie_credits'] ?? array();
        if (!empty($movie_credits)) {
            // Log credits for debugging
            $cast_count = count($movie_credits['cast'] ?? array());
            $crew_count = count($movie_credits['crew'] ?? array());
            TMDB_Logger::log("Actor {$actor_data['name']} has {$cast_count} cast credits and {$crew_count} crew credits", 'info', 'actor_import');
            
            $metadata['movie_credits'] = wp_json_encode($movie_credits);
        } else {
            $metadata['movie_credits'] = wp_json_encode(array('cast' => array(), 'crew' => array()));
        }
        
        foreach ($metadata as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }
    
    /**
     * Set movie genres
     */
    private function set_movie_genres($post_id, $genres) {
        $post_type = get_post_type($post_id);
        $taxonomy = ($post_type === 'upcoming') ? 'upcoming_genre' : 'genre';
        
        $genre_ids = array();
        
        foreach ($genres as $genre) {
            $term = get_term_by('name', $genre['name'], $taxonomy);
            
            if (!$term) {
                $term = wp_insert_term($genre['name'], $taxonomy);
                if (!is_wp_error($term)) {
                    update_term_meta($term['term_id'], 'tmdb_id', $genre['id']);
                    $genre_ids[] = $term['term_id'];
                }
            } else {
                $genre_ids[] = $term->term_id;
            }
        }
        
        if (!empty($genre_ids)) {
            wp_set_post_terms($post_id, $genre_ids, $taxonomy);
        }
    }
    
    /**
     * Set movie poster
     */
    private function set_movie_poster($post_id, $poster_path, $movie_title) {
        $image_url = $this->api->get_image_url($poster_path, 'w500');
        $filename = sanitize_file_name($movie_title . '-poster.jpg');
        
        $attachment_id = $this->api->download_image($image_url, $filename);
        
        if ($attachment_id) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }
    
    /**
     * Set actor photo
     */
    private function set_actor_photo($post_id, $profile_path, $actor_name) {
        $image_url = $this->api->get_image_url($profile_path, 'w500');
        $filename = sanitize_file_name($actor_name . '-photo.jpg');
        
        $attachment_id = $this->api->download_image($image_url, $filename);
        
        if ($attachment_id) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }
    
    /**
     * Import or update a single movie (DEBUG VERSION)
     */
    private function import_or_update_movie($movie_data) {
        $update_existing = get_option('tmdb_update_existing_movies', '1') === '1';
        
        // Check if movie already exists
        $existing = get_posts(array(
            'post_type' => 'movie',
            'meta_key' => 'tmdb_id',
            'meta_value' => $movie_data['id'],
            'post_status' => 'any',
            'numberposts' => 1
        ));
        
        $is_update = !empty($existing);
        $title = $movie_data['title'] ?? 'Unknown';
        
        // If movie exists and updates are disabled, skip it
        if ($is_update && !$update_existing) {
            return array(
                'success' => false,
                'message' => sprintf(__('Movie "%s" already exists (updates disabled)', 'tmdb-api-connector'), $title)
            );
        }
        
        $post_id = $is_update ? $existing[0]->ID : null;
        
        if ($is_update) {
            TMDB_Logger::log("Movie '{$title}' already exists (ID: {$post_id}) - will update", 'info', 'upcoming_movies');
        } else {
            TMDB_Logger::log("Movie '{$title}' is new - will import", 'info', 'upcoming_movies');
        }
        
        // Get detailed movie information
        $details_response = $this->api->get_movie_details($movie_data['id']);
        if (!$details_response['success']) {
            return $details_response;
        }
        
        $details = $details_response['data'];
        
        if ($is_update) {
            // Update existing movie
            $post_data = array(
                'ID' => $post_id,
                'post_title' => sanitize_text_field($details['title']),
                'post_content' => wp_kses_post($details['overview']),
                'post_status' => 'publish',
                'post_date' => $details['release_date'] ? date('Y-m-d H:i:s', strtotime($details['release_date'])) : get_post($post_id)->post_date
            );
            
            $result = wp_update_post($post_data);
            
            if (is_wp_error($result)) {
                return array(
                    'success' => false,
                    'message' => $result->get_error_message()
                );
            }
        } else {
            // Create new movie post
            $post_data = array(
                'post_title' => sanitize_text_field($details['title']),
                'post_content' => wp_kses_post($details['overview']),
                'post_status' => 'publish',
                'post_type' => 'movie',
                'post_date' => $details['release_date'] ? date('Y-m-d H:i:s', strtotime($details['release_date'])) : current_time('mysql')
            );
            
            $post_id = wp_insert_post($post_data);
            
            if (is_wp_error($post_id)) {
                return array(
                    'success' => false,
                    'message' => $post_id->get_error_message()
                );
            }
        }
        
        // Add/update movie metadata
        $this->save_movie_metadata($post_id, $details);
        
        // Set genres
        $this->set_movie_genres($post_id, $details['genres']);
        
        // Download and set featured image (only if not exists or updating)
        if (!empty($details['poster_path']) && (!$is_update || !has_post_thumbnail($post_id))) {
            $this->set_movie_poster($post_id, $details['poster_path'], $details['title']);
        }
        
        $action = $is_update ? 'updated' : 'imported';
        $message = sprintf(__('Movie "%s" %s successfully', 'tmdb-api-connector'), 
                          $details['title'], $action);
        
        return array(
            'success' => true,
            'message' => $message,
            'post_id' => $post_id,
            'action' => $action
        );
    }
    
    /**
     * Import or update a single upcoming movie
     */
    private function import_or_update_upcoming_movie($movie_data) {
        $update_existing = get_option('tmdb_update_existing_movies', '1') === '1';
        
        // Check if movie already exists
        $existing = get_posts(array(
            'post_type' => 'upcoming',
            'meta_key' => 'tmdb_id',
            'meta_value' => $movie_data['id'],
            'post_status' => 'any',
            'numberposts' => 1
        ));
        
        $is_update = !empty($existing);
        $title = $movie_data['title'] ?? 'Unknown';
        
        // If movie exists and updates are disabled, skip it
        if ($is_update && !$update_existing) {
            return array(
                'success' => false,
                'message' => sprintf(__('Movie "%s" already exists (updates disabled)', 'tmdb-api-connector'), $title)
            );
        }
        
        $post_id = $is_update ? $existing[0]->ID : null;
        
        if ($is_update) {
            TMDB_Logger::log("Movie '{$title}' already exists (ID: {$post_id}) - will update", 'info', 'upcoming_movies');
        } else {
            TMDB_Logger::log("Movie '{$title}' is new - will import", 'info', 'upcoming_movies');
        }
        
        // Get detailed movie information
        $details_response = $this->api->get_movie_details($movie_data['id']);
        if (!$details_response['success']) {
            return $details_response;
        }
        
        $details = $details_response['data'];
        
        if ($is_update) {
            // Update existing movie
            $post_data = array(
                'ID' => $post_id,
                'post_title' => sanitize_text_field($details['title']),
                'post_content' => wp_kses_post($details['overview']),
                'post_status' => 'publish',
                'post_date' => $details['release_date'] ? date('Y-m-d H:i:s', strtotime($details['release_date'])) : get_post($post_id)->post_date
            );
            
            $result = wp_update_post($post_data);
            
            if (is_wp_error($result)) {
                return array(
                    'success' => false,
                    'message' => $result->get_error_message()
                );
            }
        } else {
            // Create new movie post
            $post_data = array(
                'post_title' => sanitize_text_field($details['title']),
                'post_content' => wp_kses_post($details['overview']),
                'post_status' => 'publish',
                'post_type' => 'upcoming',
                'post_date' => $details['release_date'] ? date('Y-m-d H:i:s', strtotime($details['release_date'])) : current_time('mysql')
            );
            
            $post_id = wp_insert_post($post_data);
            
            if (is_wp_error($post_id)) {
                return array(
                    'success' => false,
                    'message' => $post_id->get_error_message()
                );
            }
        }
        
        // Add/update movie metadata
        $this->save_movie_metadata($post_id, $details);
        
        // Set genres
        $this->set_movie_genres($post_id, $details['genres']);
        
        // Download and set featured image (only if not exists or updating)
        if (!empty($details['poster_path']) && (!$is_update || !has_post_thumbnail($post_id))) {
            $this->set_movie_poster($post_id, $details['poster_path'], $details['title']);
        }
        
        $action = $is_update ? 'updated' : 'imported';
        $message = sprintf(__('Movie "%s" %s successfully', 'tmdb-api-connector'), 
                          $details['title'], $action);
        
        return array(
            'success' => true,
            'message' => $message,
            'post_id' => $post_id,
            'action' => $action
        );
    }

    /**
     * Update movie credits for existing actors
     */
    public function update_existing_actors_credits($limit = 10) {
        $updated = 0;
        $errors = array();
        
        TMDB_Logger::log('Starting update of existing actors movie credits', 'info', 'update_credits');
        
        // Get actors that don't have detailed movie credits yet
        $actors = get_posts(array(
            'post_type' => 'actor',
            'posts_per_page' => $limit,
            'meta_query' => array(
                array(
                    'key' => 'tmdb_id',
                    'value' => '',
                    'compare' => '!='
                )
            )
        ));
        
        foreach ($actors as $actor) {
            $tmdb_id = get_post_meta($actor->ID, 'tmdb_id', true);
            
            if (empty($tmdb_id)) {
                continue;
            }
            
            // Get current credits
            $current_credits = get_post_meta($actor->ID, 'movie_credits', true);
            
            // Skip if already has detailed credits structure
            if (!empty($current_credits)) {
                $credits_data = json_decode($current_credits, true);
                if (isset($credits_data['cast']) && isset($credits_data['crew'])) {
                    continue; // Already has the new structure
                }
            }
            
            // Get updated movie credits using dedicated endpoint
            $credits_response = $this->api->get_actor_movie_credits($tmdb_id);
            
            if ($credits_response['success']) {
                $movie_credits = $credits_response['data'];
                update_post_meta($actor->ID, 'movie_credits', wp_json_encode($movie_credits));
                
                $cast_count = count($movie_credits['cast'] ?? array());
                $crew_count = count($movie_credits['crew'] ?? array());
                
                TMDB_Logger::log("Updated {$actor->post_title}: {$cast_count} cast, {$crew_count} crew credits", 'success', 'update_credits');
                $updated++;
            } else {
                $error_msg = "Failed to update credits for {$actor->post_title}: " . $credits_response['message'];
                TMDB_Logger::log($error_msg, 'error', 'update_credits');
                $errors[] = $error_msg;
            }
        }
        
        $message = sprintf(__('Updated movie credits for %d actors', 'tmdb-api-connector'), $updated);
        TMDB_Logger::log($message, 'success', 'update_credits', $updated);
        
        return array(
            'success' => true,
            'message' => $message,
            'updated' => $updated,
            'errors' => $errors
        );
    }

    /**
     * Update existing movies with complete details
     */
    public function update_existing_movies($limit = 20) {
        $updated = 0;
        $errors = array();
        
        TMDB_Logger::log("Starting update of existing movies (limit: {$limit})", 'info', 'update_movies');
        
        // Get existing movies that need updating
        $movies = get_posts(array(
            'post_type' => 'movie',
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'tmdb_id',
                    'compare' => 'EXISTS'
                )
            )
        ));
        
        foreach ($movies as $movie) {
            $tmdb_id = get_post_meta($movie->ID, 'tmdb_id', true);
            
            if (empty($tmdb_id)) {
                continue;
            }
            
            // Get detailed movie information from TMDB
            $details_response = $this->api->get_movie_details($tmdb_id);
            
            if (!$details_response['success']) {
                $errors[] = "Failed to get details for movie ID {$tmdb_id}: " . $details_response['message'];
                TMDB_Logger::log("Failed to get details for movie ID {$tmdb_id}", 'error', 'update_movies');
                continue;
            }
            
            $details = $details_response['data'];
            
            // Update post content with overview
            $post_data = array(
                'ID' => $movie->ID,
                'post_content' => wp_kses_post($details['overview'])
            );
            
            $result = wp_update_post($post_data);
            
            if (is_wp_error($result)) {
                $errors[] = "Failed to update movie post ID {$movie->ID}: " . $result->get_error_message();
                continue;
            }
            
            // Save all movie metadata
            $this->save_movie_metadata($movie->ID, $details);
            
            // Update poster if available
            if (!empty($details['poster_path'])) {
                $this->set_movie_poster($movie->ID, $details['poster_path'], $details['title']);
            }
            
            // Update genres
            if (!empty($details['genres'])) {
                $this->set_movie_genres($movie->ID, $details['genres']);
            }
            
            $updated++;
            TMDB_Logger::log("✓ Updated movie: {$details['title']} (ID: {$movie->ID})", 'success', 'update_movies');
        }
        
        $message = sprintf(__('Updated %d existing movies', 'tmdb-api-connector'), $updated);
        
        if (!empty($errors)) {
            $message .= sprintf(__(' with %d errors', 'tmdb-api-connector'), count($errors));
        }
        
        TMDB_Logger::log($message, 'success', 'update_movies', $updated);
        
        return array(
            'success' => true,
            'message' => $message,
            'updated' => $updated,
            'errors' => $errors
        );
    }

    /**
     * Update existing actors with new details
     */
    public function update_existing_actors_details($limit = 10) {
        // Get actors that have TMDB ID but might be missing new fields
        $actors = get_posts(array(
            'post_type' => 'actor',
            'posts_per_page' => $limit,
            'meta_query' => array(
                array(
                    'key' => 'tmdb_id',
                    'value' => '',
                    'compare' => '!='
                )
            )
        ));
        
        if (empty($actors)) {
            return array(
                'success' => true,
                'message' => __('No actors found to update', 'tmdb-api-connector'),
                'processed' => 0
            );
        }
        
        $processed = 0;
        $errors = array();
        
        foreach ($actors as $actor) {
            $tmdb_id = get_post_meta($actor->ID, 'tmdb_id', true);
            
            if (empty($tmdb_id)) {
                continue;
            }
            
            // Get detailed actor information
            $details_response = $this->api->get_actor_details($tmdb_id);
            
            if (!$details_response['success']) {
                $errors[] = sprintf(__('Failed to get details for actor "%s": %s', 'tmdb-api-connector'), 
                    $actor->post_title, $details_response['message']);
                continue;
            }
            
            $details = $details_response['data'];
            
            // Get movie credits
            $credits_response = $this->api->get_actor_movie_credits($tmdb_id);
            if ($credits_response['success']) {
                $details['movie_credits'] = $credits_response['data'];
            }
            
            // Update metadata
            $this->save_actor_metadata($actor->ID, $details);
            
            // Update bio if not present
            if (empty($actor->post_content) && !empty($details['biography'])) {
                wp_update_post(array(
                    'ID' => $actor->ID,
                    'post_content' => wp_kses_post($details['biography'])
                ));
            }
            
            // Update featured image if not present
            if (!has_post_thumbnail($actor->ID) && !empty($details['profile_path'])) {
                $this->set_actor_photo($actor->ID, $details['profile_path'], $details['name']);
            }
            
            $processed++;
            
            TMDB_Logger::log("Updated actor '{$actor->post_title}' with new details", 'info', 'actor_update');
        }
        
        $message = sprintf(__('Updated %d actors', 'tmdb-api-connector'), $processed);
        
        if (!empty($errors)) {
            $message .= '. ' . __('Errors:', 'tmdb-api-connector') . ' ' . implode(', ', $errors);
        }
        
        return array(
            'success' => true,
            'message' => $message,
            'processed' => $processed,
            'errors' => $errors
        );
    }
} 