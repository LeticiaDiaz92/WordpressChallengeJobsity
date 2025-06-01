<?php
/**
 * TMDB Logger Class
 * Handles logging for the TMDB API plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMDB_Logger {
    
    private static $instance = null;
    private $log_table;
    
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
        global $wpdb;
        $this->log_table = $wpdb->prefix . 'tmdb_import_logs';
    }
    
    /**
     * Log a message
     */
    public static function log($message, $type = 'info', $import_type = 'general', $items_imported = 0) {
        if (get_option('tmdb_enable_logging') !== '1') {
            return;
        }
        
        global $wpdb;
        $log_table = $wpdb->prefix . 'tmdb_import_logs';
        
        $wpdb->insert(
            $log_table,
            array(
                'import_type' => sanitize_text_field($import_type),
                'status' => sanitize_text_field($type),
                'message' => sanitize_text_field($message),
                'items_imported' => intval($items_imported),
                'import_date' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s')
        );
        
        // Also log to WordPress debug.log if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[TMDB API] ' . $type . ': ' . $message);
        }
    }
    
    /**
     * Get recent logs
     */
    public function get_logs($limit = 50, $type = null) {
        global $wpdb;
        
        $where_clause = '';
        $prepare_args = array();
        
        if ($type) {
            $where_clause = ' WHERE status = %s';
            $prepare_args[] = $type;
        }
        
        $prepare_args[] = $limit;
        
        $query = "SELECT * FROM {$this->log_table}{$where_clause} ORDER BY import_date DESC LIMIT %d";
        
        if (!empty($prepare_args)) {
            $query = $wpdb->prepare($query, $prepare_args);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Clear old logs
     */
    public function clear_old_logs($days = 30) {
        global $wpdb;
        
        $date_threshold = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->log_table} WHERE import_date < %s",
                $date_threshold
            )
        );
        
        self::log("Cleared {$deleted} old log entries", 'info', 'maintenance');
        
        return $deleted;
    }
    
    /**
     * Clear all logs
     */
    public function clear_all_logs() {
        global $wpdb;
        
        $deleted = $wpdb->query("DELETE FROM {$this->log_table}");
        
        return $deleted;
    }
    
    /**
     * Get log statistics
     */
    public function get_log_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Total logs
        $stats['total'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->log_table}");
        
        // Logs by type
        $stats['by_type'] = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM {$this->log_table} GROUP BY status",
            OBJECT_K
        );
        
        // Recent activity (last 24 hours)
        $stats['recent'] = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->log_table} WHERE import_date > %s",
                date('Y-m-d H:i:s', strtotime('-24 hours'))
            )
        );
        
        // Last import
        $stats['last_import'] = $wpdb->get_row(
            "SELECT * FROM {$this->log_table} WHERE import_type != 'general' ORDER BY import_date DESC LIMIT 1"
        );
        
        return $stats;
    }
} 