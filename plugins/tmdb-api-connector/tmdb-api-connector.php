<?php
/**
 * Plugin Name: TMDB API Connector
 * Plugin URI: https://github.com/your-repo/tmdb-api-connector
 * Description: Connects WordPress with The Movie Database (TMDB) API to import and sync movie and actor data with manual and automatic import capabilities.
 * Version: 1.0.0
 * Author: Movies Theme Developer
 * License: GPL v2 or later
 * Text Domain: tmdb-api-connector
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TMDB_API_PLUGIN_VERSION', '1.0.0');
define('TMDB_API_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TMDB_API_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TMDB_API_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Main plugin class
class TMDB_API_Connector {
    
    private static $instance = null;
    
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
        $this->init();
    }
    
    /**
     * Initialize the plugin
     */
    private function init() {
        // Include required files
        $this->include_files();
        
        // Initialize hooks
        add_action('init', array($this, 'init_plugin'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize cron jobs
        add_action('wp', array($this, 'schedule_cron_jobs'));
        add_action('tmdb_hourly_sync', array($this, 'run_hourly_sync'));
        add_action('tmdb_daily_sync', array($this, 'run_daily_sync'));
        
        // AJAX handlers
        add_action('wp_ajax_tmdb_test_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_tmdb_manual_import', array($this, 'ajax_manual_import'));
        add_action('wp_ajax_tmdb_import_popular', array($this, 'ajax_import_popular'));
        add_action('wp_ajax_tmdb_save_settings', array($this, 'ajax_save_settings'));
    }
    
    /**
     * Include required files
     */
    private function include_files() {
        require_once TMDB_API_PLUGIN_PATH . 'includes/class-tmdb-api.php';
        require_once TMDB_API_PLUGIN_PATH . 'includes/class-tmdb-importer.php';
        require_once TMDB_API_PLUGIN_PATH . 'includes/class-tmdb-scheduler.php';
        require_once TMDB_API_PLUGIN_PATH . 'includes/class-tmdb-admin.php';
        require_once TMDB_API_PLUGIN_PATH . 'includes/class-tmdb-logger.php';
    }
    
    /**
     * Initialize plugin components
     */
    public function init_plugin() {
        // Initialize classes
        TMDB_API::get_instance();
        TMDB_Importer::get_instance();
        TMDB_Scheduler::get_instance();
        TMDB_Logger::get_instance();
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('TMDB API', 'tmdb-api-connector'),
            __('TMDB API', 'tmdb-api-connector'),
            'manage_options',
            'tmdb-api-connector',
            array($this, 'admin_page'),
            'dashicons-video-alt2',
            30
        );
        
        // Add submenu pages
        add_submenu_page(
            'tmdb-api-connector',
            __('Settings', 'tmdb-api-connector'),
            __('Settings', 'tmdb-api-connector'),
            'manage_options',
            'tmdb-api-connector',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'tmdb-api-connector',
            __('Import', 'tmdb-api-connector'),
            __('Import', 'tmdb-api-connector'),
            'manage_options',
            'tmdb-api-import',
            array($this, 'import_page')
        );
        
        add_submenu_page(
            'tmdb-api-connector',
            __('Logs', 'tmdb-api-connector'),
            __('Logs', 'tmdb-api-connector'),
            'manage_options',
            'tmdb-api-logs',
            array($this, 'logs_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_scripts($hook) {
        if (strpos($hook, 'tmdb-api') === false) {
            return;
        }
        
        wp_enqueue_style(
            'tmdb-admin-style',
            TMDB_API_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            TMDB_API_PLUGIN_VERSION
        );
        
        wp_enqueue_script(
            'tmdb-admin-script',
            TMDB_API_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            TMDB_API_PLUGIN_VERSION,
            true
        );
        
        wp_localize_script('tmdb-admin-script', 'tmdb_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tmdb_admin_nonce'),
            'strings' => array(
                'testing' => __('Testing connection...', 'tmdb-api-connector'),
                'success' => __('Connection successful!', 'tmdb-api-connector'),
                'error' => __('Connection failed!', 'tmdb-api-connector'),
                'importing' => __('Importing...', 'tmdb-api-connector'),
                'import_complete' => __('Import completed!', 'tmdb-api-connector'),
                'confirm' => __('Are you sure?', 'tmdb-api-connector')
            )
        ));
    }
    
    /**
     * Admin page callback
     */
    public function admin_page() {
        $admin = new TMDB_Admin();
        $admin->render_settings_page();
    }
    
    /**
     * Import page callback
     */
    public function import_page() {
        $admin = new TMDB_Admin();
        $admin->render_import_page();
    }
    
    /**
     * Logs page callback
     */
    public function logs_page() {
        $admin = new TMDB_Admin();
        $admin->render_logs_page();
    }
    
    /**
     * Schedule cron jobs
     */
    public function schedule_cron_jobs() {
        if (!wp_next_scheduled('tmdb_hourly_sync')) {
            wp_schedule_event(time(), 'hourly', 'tmdb_hourly_sync');
        }
        
        if (!wp_next_scheduled('tmdb_daily_sync')) {
            wp_schedule_event(time(), 'daily', 'tmdb_daily_sync');
        }
    }
    
    /**
     * Run hourly sync
     */
    public function run_hourly_sync() {
        if (get_option('tmdb_auto_sync') == '1') {
            $scheduler = TMDB_Scheduler::get_instance();
            $scheduler->run_hourly_sync();
        }
    }
    
    /**
     * Run daily sync
     */
    public function run_daily_sync() {
        if (get_option('tmdb_auto_sync') == '1') {
            $scheduler = TMDB_Scheduler::get_instance();
            $scheduler->run_daily_sync();
        }
    }
    
    /**
     * AJAX: Test API connection
     */
    public function ajax_test_connection() {
        check_ajax_referer('tmdb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'tmdb-api-connector'));
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        $api = TMDB_API::get_instance();
        
        $result = $api->test_connection($api_key);
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX: Save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('tmdb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'tmdb-api-connector'));
        }
        
        $settings = array(
            'tmdb_api_key' => sanitize_text_field($_POST['api_key']),
            'tmdb_auto_sync' => sanitize_text_field($_POST['auto_sync']),
            'tmdb_sync_frequency' => sanitize_text_field($_POST['sync_frequency']),
            'tmdb_import_limit' => intval($_POST['import_limit']),
            'tmdb_upcoming_months_ahead' => intval($_POST['upcoming_months_ahead']),
            'tmdb_update_existing_movies' => isset($_POST['update_existing_movies']) ? '1' : '0',
            'tmdb_cache_duration' => intval($_POST['cache_duration']),
            'tmdb_enable_logging' => sanitize_text_field($_POST['enable_logging'])
        );
        
        foreach ($settings as $option => $value) {
            update_option($option, $value);
        }
        
        TMDB_Logger::log('Settings updated successfully');
        wp_send_json_success(__('Settings saved successfully!', 'tmdb-api-connector'));
    }
    
    /**
     * AJAX: Manual import
     */
    public function ajax_manual_import() {
        check_ajax_referer('tmdb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'tmdb-api-connector'));
        }
        
        $import_type = sanitize_text_field($_POST['import_type']);
        $importer = TMDB_Importer::get_instance();
        
        $result = $importer->manual_import($import_type);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * AJAX: Import popular content
     */
    public function ajax_import_popular() {
        check_ajax_referer('tmdb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'tmdb-api-connector'));
        }
        
        $content_type = sanitize_text_field($_POST['content_type']);
        $importer = TMDB_Importer::get_instance();
        
        $result = $importer->import_popular($content_type);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create plugin tables if needed
        $this->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        TMDB_Logger::log('Plugin activated successfully');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('tmdb_hourly_sync');
        wp_clear_scheduled_hook('tmdb_daily_sync');
        
        TMDB_Logger::log('Plugin deactivated');
    }
    
    /**
     * Create database tables for import logs
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table for import logs
        $table_name = $wpdb->prefix . 'tmdb_import_logs';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            import_type varchar(50) NOT NULL,
            status varchar(20) NOT NULL,
            message text,
            items_imported int DEFAULT 0,
            import_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $defaults = array(
            'tmdb_api_key' => '',
            'tmdb_auto_sync' => '1',
            'tmdb_sync_frequency' => 'daily',
            'tmdb_import_limit' => 20,
            'tmdb_upcoming_months_ahead' => 6,
            'tmdb_update_existing_movies' => '1',
            'tmdb_cache_duration' => 3600,
            'tmdb_enable_logging' => '1'
        );
        
        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
}

// Initialize the plugin
function tmdb_api_connector_init() {
    return TMDB_API_Connector::get_instance();
}

// Start the plugin
tmdb_api_connector_init(); 