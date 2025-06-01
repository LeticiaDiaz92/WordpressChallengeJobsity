<?php
/**
 * TMDB Scheduler Class
 * Handles cron jobs for automatic synchronization
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMDB_Scheduler {
    
    private static $instance = null;
    private $importer;
    
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
        $this->importer = TMDB_Importer::get_instance();
    }
    
    /**
     * Run hourly sync
     */
    public function run_hourly_sync() {
        if (!$this->should_run_sync()) {
            return;
        }
        
        TMDB_Logger::log('Starting hourly sync', 'info', 'hourly_sync');
        
        // Import upcoming movies (smaller batch for hourly)
        $result = $this->importer->import_popular('movies');
        
        if ($result['success']) {
            TMDB_Logger::log('Hourly sync completed successfully', 'success', 'hourly_sync', $result['imported']);
        } else {
            TMDB_Logger::log('Hourly sync failed: ' . $result['message'], 'error', 'hourly_sync');
        }
    }
    
    /**
     * Run daily sync
     */
    public function run_daily_sync() {
        if (!$this->should_run_sync()) {
            return;
        }
        
        TMDB_Logger::log('Starting daily sync', 'info', 'daily_sync');
        
        $total_imported = 0;
        $errors = array();
        
        // Import popular movies
        $movies_result = $this->importer->import_popular('movies');
        if ($movies_result['success']) {
            $total_imported += $movies_result['imported'];
        } else {
            $errors[] = 'Movies: ' . $movies_result['message'];
        }
        
        // Import popular actors
        $actors_result = $this->importer->import_popular('actors');
        if ($actors_result['success']) {
            $total_imported += $actors_result['imported'];
        } else {
            $errors[] = 'Actors: ' . $actors_result['message'];
        }
        
        // Import upcoming movies
        $upcoming_result = $this->importer->manual_import('upcoming_movies');
        if ($upcoming_result['success']) {
            $total_imported += $upcoming_result['imported'];
        } else {
            $errors[] = 'Upcoming: ' . $upcoming_result['message'];
        }
        
        // Import genres
        $genres_result = $this->importer->manual_import('genres');
        if ($genres_result['success']) {
            $total_imported += $genres_result['imported'];
        } else {
            $errors[] = 'Genres: ' . $genres_result['message'];
        }
        
        // Clean old logs
        $logger = TMDB_Logger::get_instance();
        $logger->clear_old_logs(30);
        
        // Log results
        if (empty($errors)) {
            TMDB_Logger::log("Daily sync completed successfully. Imported {$total_imported} items.", 'success', 'daily_sync', $total_imported);
        } else {
            $error_message = 'Daily sync completed with errors: ' . implode('; ', $errors);
            TMDB_Logger::log($error_message, 'warning', 'daily_sync', $total_imported);
        }
        
        // Update last sync time
        update_option('tmdb_last_sync', current_time('mysql'));
    }
    
    /**
     * Check if sync should run
     */
    private function should_run_sync() {
        // Check if auto sync is enabled
        if (get_option('tmdb_auto_sync') !== '1') {
            return false;
        }
        
        // Check if API key is configured
        $api_key = get_option('tmdb_api_key');
        if (empty($api_key)) {
            TMDB_Logger::log('Sync skipped: API key not configured', 'warning', 'sync_check');
            return false;
        }
        
        // Test API connection
        $api = TMDB_API::get_instance();
        $connection_test = $api->test_connection();
        
        if (!$connection_test['success']) {
            TMDB_Logger::log('Sync skipped: API connection failed - ' . $connection_test['message'], 'error', 'sync_check');
            return false;
        }
        
        return true;
    }
    
    /**
     * Force run sync now
     */
    public function force_sync($type = 'daily') {
        if ($type === 'hourly') {
            $this->run_hourly_sync();
        } else {
            $this->run_daily_sync();
        }
    }
    
    /**
     * Get next scheduled sync times
     */
    public function get_next_sync_times() {
        return array(
            'hourly' => wp_next_scheduled('tmdb_hourly_sync'),
            'daily' => wp_next_scheduled('tmdb_daily_sync')
        );
    }
    
    /**
     * Reschedule cron jobs
     */
    public function reschedule_jobs() {
        // Clear existing schedules
        wp_clear_scheduled_hook('tmdb_hourly_sync');
        wp_clear_scheduled_hook('tmdb_daily_sync');
        
        // Reschedule based on settings
        $frequency = get_option('tmdb_sync_frequency', 'daily');
        
        switch ($frequency) {
            case 'hourly':
                wp_schedule_event(time(), 'hourly', 'tmdb_hourly_sync');
                break;
            case 'daily':
                wp_schedule_event(time(), 'daily', 'tmdb_daily_sync');
                break;
            case 'weekly':
                wp_schedule_event(time(), 'weekly', 'tmdb_daily_sync');
                break;
        }
        
        TMDB_Logger::log("Cron jobs rescheduled for {$frequency} frequency", 'info', 'scheduler');
    }
} 