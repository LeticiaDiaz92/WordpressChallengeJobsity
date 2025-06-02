<?php
/**
 * TMDB API Class
 * Handles all communication with The Movie Database API
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMDB_API {
    
    private static $instance = null;
    private $api_key;
    private $base_url = 'https://api.themoviedb.org/3';
    private $image_base_url = 'https://image.tmdb.org/t/p/';
    
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
        $this->api_key = get_option('tmdb_api_key', '');
    }
    
    /**
     * Test API connection
     */
    public function test_connection($api_key = null) {
        $test_key = $api_key ? $api_key : $this->api_key;
        
        if (empty($test_key)) {
            return array(
                'success' => false,
                'message' => __('API key is required', 'tmdb-api-connector')
            );
        }
        
        $url = $this->base_url . '/configuration?api_key=' . $test_key;
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => __('Connection failed: ', 'tmdb-api-connector') . $response->get_error_message()
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['status_code']) && $data['status_code'] !== 200) {
            return array(
                'success' => false,
                'message' => __('API Error: ', 'tmdb-api-connector') . $data['status_message']
            );
        }
        
        if (isset($data['images'])) {
            return array(
                'success' => true,
                'message' => __('Connection successful!', 'tmdb-api-connector'),
                'data' => $data
            );
        }
        
        return array(
            'success' => false,
            'message' => __('Invalid API response', 'tmdb-api-connector')
        );
    }
    
    /**
     * Get popular movies
     */
    public function get_popular_movies($page = 1) {
        return $this->make_request('/movie/popular', array('page' => $page));
    }
    
    /**
     * Get upcoming movies using discover endpoint with date filtering
     */
    public function get_upcoming_movies($page = 1) {
        $today = date('Y-m-d');
        
        // Get the upcoming movies range setting (in months) with fallback
        $months_ahead = get_option('tmdb_upcoming_months_ahead', 6);
        if (empty($months_ahead) || $months_ahead <= 0) {
            $months_ahead = 6; // Default fallback
        }

        $date = new DateTime();
        $calculate_months = $date->add(new DateInterval('P' . $months_ahead . 'M'));
        $max_date = $calculate_months->format('Y-m-d');
        
        $params = array(
            'page' => $page,
            'primary_release_date.gte' => $today,
            'primary_release_date.lte' => $max_date,
        );
        
      
        $result = $this->make_request('/discover/movie', $params);
        
        return $result;
    }
    
    /**
     * Get popular actors
     */
    public function get_popular_actors($page = 1) {
        return $this->make_request('/person/popular', array('page' => $page));
    }
    
    /**
     * Get movie details
     */
    public function get_movie_details($movie_id) {
        return $this->make_request('/movie/' . $movie_id, array(
            'append_to_response' => 'credits,videos,similar,reviews,alternative_titles'
        ));
    }
    
    /**
     * Get actor details
     */
    public function get_actor_details($actor_id) {
        return $this->make_request('/person/' . $actor_id, array(
            'append_to_response' => 'movie_credits,images'
        ));
    }
    
    /**
     * Search movies
     */
    public function search_movies($query, $page = 1) {
        return $this->make_request('/search/movie', array(
            'query' => $query,
            'page' => $page
        ));
    }
    
    /**
     * Search actors
     */
    public function search_actors($query, $page = 1) {
        return $this->make_request('/search/person', array(
            'query' => $query,
            'page' => $page
        ));
    }
    
    /**
     * Get movie genres
     */
    public function get_movie_genres() {
        return $this->make_request('/genre/movie/list');
    }
    
    /**
     * Make API request
     */
    private function make_request($endpoint, $params = array()) {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => __('API key not configured', 'tmdb-api-connector')
            );
        }
        
        $params['api_key'] = $this->api_key;
        $url = $this->base_url . $endpoint . '?' . http_build_query($params);
        
        // Check cache first
        $cache_key = 'tmdb_' . md5($url);
        $cached_data = get_transient($cache_key);
        
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        ));
        
        if (is_wp_error($response)) {
            if (class_exists('TMDB_Logger')) {
                TMDB_Logger::log('API request failed: ' . $response->get_error_message(), 'error');
            }
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($http_code !== 200) {
            $error_message = isset($data['status_message']) ? $data['status_message'] : 'HTTP Error ' . $http_code;
            if (class_exists('TMDB_Logger')) {
                TMDB_Logger::log('API request failed: ' . $error_message, 'error');
            }
            return array(
                'success' => false,
                'message' => $error_message
            );
        }
        
        $result = array(
            'success' => true,
            'data' => $data
        );
        
        // Cache the result
        $cache_duration = get_option('tmdb_cache_duration', 3600);
        set_transient($cache_key, $result, $cache_duration);
        
        return $result;
    }
    
    /**
     * Get image URL
     */
    public function get_image_url($path, $size = 'w500') {
        if (empty($path)) {
            return '';
        }
        
        return $this->image_base_url . $size . $path;
    }
    
    /**
     * Download and save image
     */
    public function download_image($image_url, $filename) {
        if (empty($image_url)) {
            return false;
        }
        
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        
        if ($image_data === false) {
            return false;
        }
        
        $file_path = $upload_dir['path'] . '/' . $filename;
        $file_saved = file_put_contents($file_path, $image_data);
        
        if ($file_saved === false) {
            return false;
        }
        
        // Create attachment
        $file_type = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $file_type['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment($attachment, $file_path);
        
        if (!is_wp_error($attachment_id)) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            return $attachment_id;
        }
        
        return false;
    }
} 