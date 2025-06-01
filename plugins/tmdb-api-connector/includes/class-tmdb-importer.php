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
        $imported = 0;
        $errors = array();
        
        TMDB_Logger::log('Starting upcoming movies import', 'info', 'upcoming_movies');
        
        for ($page = 1; $page <= ceil($limit / 20); $page++) {
            $response = $this->api->get_upcoming_movies($page);
            
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
        
        $message = sprintf(__('Imported %d upcoming movies', 'tmdb-api-connector'), $imported);
        TMDB_Logger::log($message, 'success', 'upcoming_movies', $imported);
        
        return array(
            'success' => true,
            'message' => $message,
            'imported' => $imported,
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
            'production_companies' => wp_json_encode($movie_data['production_companies']),
            'production_countries' => wp_json_encode($movie_data['production_countries']),
            'spoken_languages' => wp_json_encode($movie_data['spoken_languages']),
            'alternative_titles' => wp_json_encode($movie_data['alternative_titles']['titles'] ?? array()),
            'credits' => wp_json_encode($movie_data['credits'] ?? array()),
            'videos' => wp_json_encode($movie_data['videos']['results'] ?? array()),
            'similar' => wp_json_encode($movie_data['similar']['results'] ?? array()),
            'reviews' => wp_json_encode($movie_data['reviews']['results'] ?? array())
        );
        
        foreach ($metadata as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }
    
    /**
     * Save actor metadata
     */
    private function save_actor_metadata($post_id, $actor_data) {
        $metadata = array(
            'tmdb_id' => $actor_data['id'],
            'birthday' => $actor_data['birthday'],
            'deathday' => $actor_data['deathday'],
            'place_of_birth' => $actor_data['place_of_birth'],
            'homepage' => $actor_data['homepage'],
            'popularity' => $actor_data['popularity'],
            'also_known_as' => wp_json_encode($actor_data['also_known_as'] ?? array()),
            'movie_credits' => wp_json_encode($actor_data['movie_credits'] ?? array()),
            'images' => wp_json_encode($actor_data['images']['profiles'] ?? array())
        );
        
        foreach ($metadata as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }
    
    /**
     * Set movie genres
     */
    private function set_movie_genres($post_id, $genres) {
        $genre_ids = array();
        
        foreach ($genres as $genre) {
            $term = get_term_by('name', $genre['name'], 'genre');
            
            if (!$term) {
                $term = wp_insert_term($genre['name'], 'genre');
                if (!is_wp_error($term)) {
                    update_term_meta($term['term_id'], 'tmdb_id', $genre['id']);
                    $genre_ids[] = $term['term_id'];
                }
            } else {
                $genre_ids[] = $term->term_id;
            }
        }
        
        if (!empty($genre_ids)) {
            wp_set_post_terms($post_id, $genre_ids, 'genre');
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
} 