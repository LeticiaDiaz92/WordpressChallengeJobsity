<?php
/**
 * TMDB Admin Class
 * Handles the admin interface for the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMDB_Admin {
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        $api_key = get_option('tmdb_api_key', '');
        $auto_sync = get_option('tmdb_auto_sync', '1');
        $sync_frequency = get_option('tmdb_sync_frequency', 'daily');
        $import_limit = get_option('tmdb_import_limit', 20);
        $cache_duration = get_option('tmdb_cache_duration', 3600);
        $enable_logging = get_option('tmdb_enable_logging', '1');
        
        // Test connection status
        $connection_status = '';
        if (!empty($api_key)) {
            $api = TMDB_API::get_instance();
            $test_result = $api->test_connection();
            $connection_status = $test_result['success'] ? 'connected' : 'error';
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('TMDB API Settings', 'tmdb-api-connector'); ?></h1>
            
            <div class="tmdb-admin-container">
                <div class="tmdb-settings-form">
                    <form id="tmdb-settings-form">
                        <?php wp_nonce_field('tmdb_admin_nonce', 'nonce'); ?>
                        
                        <div class="tmdb-section">
                            <h2><?php _e('API Configuration', 'tmdb-api-connector'); ?></h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="api_key"><?php _e('TMDB API Key', 'tmdb-api-connector'); ?></label>
                                    </th>
                                    <td>
                                        <input type="password" id="api_key" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" placeholder="<?php _e('Enter your TMDB API key', 'tmdb-api-connector'); ?>" />
                                        <button type="button" id="test-connection" class="button"><?php _e('Test Connection', 'tmdb-api-connector'); ?></button>
                                        <div id="connection-status" class="tmdb-status <?php echo $connection_status; ?>">
                                            <?php if ($connection_status === 'connected'): ?>
                                                <span class="dashicons dashicons-yes-alt"></span> <?php _e('Connected', 'tmdb-api-connector'); ?>
                                            <?php elseif ($connection_status === 'error'): ?>
                                                <span class="dashicons dashicons-warning"></span> <?php _e('Connection Failed', 'tmdb-api-connector'); ?>
                                            <?php endif; ?>
                                        </div>
                                        <p class="description">
                                            <?php printf(__('Get your API key from <a href="%s" target="_blank">TMDB API Settings</a>', 'tmdb-api-connector'), 'https://www.themoviedb.org/settings/api'); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="tmdb-section">
                            <h2><?php _e('Sync Settings', 'tmdb-api-connector'); ?></h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="auto_sync"><?php _e('Enable Auto Sync', 'tmdb-api-connector'); ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" id="auto_sync" name="auto_sync" value="1" <?php checked($auto_sync, '1'); ?> />
                                        <label for="auto_sync"><?php _e('Automatically sync data from TMDB', 'tmdb-api-connector'); ?></label>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="sync_frequency"><?php _e('Sync Frequency', 'tmdb-api-connector'); ?></label>
                                    </th>
                                    <td>
                                        <select id="sync_frequency" name="sync_frequency">
                                            <option value="hourly" <?php selected($sync_frequency, 'hourly'); ?>><?php _e('Hourly', 'tmdb-api-connector'); ?></option>
                                            <option value="daily" <?php selected($sync_frequency, 'daily'); ?>><?php _e('Daily', 'tmdb-api-connector'); ?></option>
                                            <option value="weekly" <?php selected($sync_frequency, 'weekly'); ?>><?php _e('Weekly', 'tmdb-api-connector'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="import_limit"><?php _e('Import Limit', 'tmdb-api-connector'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="import_limit" name="import_limit" value="<?php echo esc_attr($import_limit); ?>" min="1" max="100" />
                                        <p class="description"><?php _e('Maximum number of items to import per sync', 'tmdb-api-connector'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="upcoming_months_ahead"><?php _e('Upcoming Movies Range (months)', 'tmdb-api-connector'); ?></label>
                                    </th>
                                    <td>
                                        <?php $upcoming_months_ahead = get_option('tmdb_upcoming_months_ahead', 6); ?>
                                        <input type="number" id="upcoming_months_ahead" name="upcoming_months_ahead" value="<?php echo esc_attr($upcoming_months_ahead); ?>" min="1" max="24" />
                                        <p class="description"><?php _e('How many months ahead to look for upcoming movies', 'tmdb-api-connector'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="update_existing_movies"><?php _e('Update Existing Movies', 'tmdb-api-connector'); ?></label>
                                    </th>
                                    <td>
                                        <?php $update_existing = get_option('tmdb_update_existing_movies', '1'); ?>
                                        <input type="checkbox" id="update_existing_movies" name="update_existing_movies" value="1" <?php checked($update_existing, '1'); ?> />
                                        <label for="update_existing_movies"><?php _e('Update movie data if it already exists', 'tmdb-api-connector'); ?></label>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="update_existing_actors"><?php _e('Update Existing Actors', 'tmdb-api-connector'); ?></label>
                                    </th>
                                    <td>
                                        <?php $update_existing_actors = get_option('tmdb_update_existing_actors', '1'); ?>
                                        <input type="checkbox" id="update_existing_actors" name="update_existing_actors" value="1" <?php checked($update_existing_actors, '1'); ?> />
                                        <label for="update_existing_actors"><?php _e('Update actor data and credits if actor already exists', 'tmdb-api-connector'); ?></label>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="tmdb-section">
                            <h2><?php _e('Performance Settings', 'tmdb-api-connector'); ?></h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="cache_duration"><?php _e('Cache Duration (seconds)', 'tmdb-api-connector'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="cache_duration" name="cache_duration" value="<?php echo esc_attr($cache_duration); ?>" min="300" max="86400" />
                                        <p class="description"><?php _e('How long to cache API responses', 'tmdb-api-connector'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="enable_logging"><?php _e('Enable Logging', 'tmdb-api-connector'); ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" id="enable_logging" name="enable_logging" value="1" <?php checked($enable_logging, '1'); ?> />
                                        <label for="enable_logging"><?php _e('Log plugin activities and errors', 'tmdb-api-connector'); ?></label>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <p class="submit">
                            <input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save Settings', 'tmdb-api-connector'); ?>" />
                        </p>
                    </form>
                </div>
                
                <div class="tmdb-sidebar">
                    <div class="tmdb-info-box">
                        <h3><?php _e('Sync Status', 'tmdb-api-connector'); ?></h3>
                        <?php
                        $last_sync = get_option('tmdb_last_sync');
                        $scheduler = TMDB_Scheduler::get_instance();
                        $next_syncs = $scheduler->get_next_sync_times();
                        ?>
                        <p><strong><?php _e('Last Sync:', 'tmdb-api-connector'); ?></strong><br>
                        <?php echo $last_sync ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_sync)) : __('Never', 'tmdb-api-connector'); ?></p>
                        
                        <p><strong><?php _e('Next Sync:', 'tmdb-api-connector'); ?></strong><br>
                        <?php 
                        $next_sync = $next_syncs['daily'] ?: $next_syncs['hourly'];
                        echo $next_sync ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), $next_sync) : __('Not scheduled', 'tmdb-api-connector'); 
                        ?></p>
                        
                        <button type="button" id="force-sync" class="button"><?php _e('Force Sync Now', 'tmdb-api-connector'); ?></button>
                    </div>
                    
                    <div class="tmdb-info-box">
                        <h3><?php _e('Quick Stats', 'tmdb-api-connector'); ?></h3>
                        <?php
                        $movie_count = wp_count_posts('movie')->publish;
                        $actor_count = wp_count_posts('actor')->publish;
                        $genre_count = wp_count_terms('genre');
                        ?>
                        <ul>
                            <li><?php printf(__('Movies: %d', 'tmdb-api-connector'), $movie_count); ?></li>
                            <li><?php printf(__('Actors: %d', 'tmdb-api-connector'), $actor_count); ?></li>
                            <li><?php printf(__('Genres: %d', 'tmdb-api-connector'), $genre_count); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render import page
     */
    public function render_import_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('TMDB Import', 'tmdb-api-connector'); ?></h1>
            
            <div class="tmdb-import-container">
                <div class="tmdb-import-section">
                    <h2><?php _e('Manual Import', 'tmdb-api-connector'); ?></h2>
                    <p><?php _e('Import specific content types manually from TMDB.', 'tmdb-api-connector'); ?></p>
                    
                    <div class="tmdb-import-buttons">
                        <button type="button" class="button button-primary manual-import" data-type="popular_movies">
                            <?php _e('Import Popular Movies', 'tmdb-api-connector'); ?>
                        </button>
                        
                        <button type="button" class="button button-primary manual-import" data-type="upcoming_movies">
                            <?php _e('Import Upcoming Movies', 'tmdb-api-connector'); ?>
                        </button>
                        
                        <button type="button" class="button button-primary manual-import" data-type="popular_actors">
                            <?php _e('Import Popular Actors', 'tmdb-api-connector'); ?>
                        </button>
                        
                        <button type="button" class="button button-secondary manual-import" data-type="update_actor_credits">
                            <?php _e('Update Actor Credits', 'tmdb-api-connector'); ?>
                        </button>
                        
                        <button type="button" class="button button-secondary manual-import" data-type="genres">
                            <?php _e('Import Genres', 'tmdb-api-connector'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="tmdb-import-section">
                    <h2><?php _e('Popular Content Import', 'tmdb-api-connector'); ?></h2>
                    <p><?php _e('Import the most popular movies and actors from TMDB.', 'tmdb-api-connector'); ?></p>
                    
                    <div class="tmdb-import-buttons">
                        <button type="button" class="button button-primary import-popular" data-content="movies">
                            <?php _e('Import Popular Movies', 'tmdb-api-connector'); ?>
                        </button>
                        
                        <button type="button" class="button button-primary import-popular" data-content="actors">
                            <?php _e('Import Popular Actors', 'tmdb-api-connector'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="tmdb-import-results">
                    <h2><?php _e('Import Results', 'tmdb-api-connector'); ?></h2>
                    <div id="import-log"></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render logs page
     */
    public function render_logs_page() {
        $logger = TMDB_Logger::get_instance();
        $logs = $logger->get_logs(100);
        $stats = $logger->get_log_stats();
        
        ?>
        <div class="wrap">
            <h1><?php _e('TMDB Logs', 'tmdb-api-connector'); ?></h1>
            
            <div class="tmdb-logs-container">
                <div class="tmdb-logs-stats">
                    <div class="tmdb-stat-box">
                        <h3><?php _e('Total Logs', 'tmdb-api-connector'); ?></h3>
                        <span class="stat-number"><?php echo $stats['total']; ?></span>
                    </div>
                    
                    <div class="tmdb-stat-box">
                        <h3><?php _e('Recent Activity (24h)', 'tmdb-api-connector'); ?></h3>
                        <span class="stat-number"><?php echo $stats['recent']; ?></span>
                    </div>
                    
                    <div class="tmdb-stat-box">
                        <h3><?php _e('Last Import', 'tmdb-api-connector'); ?></h3>
                        <span class="stat-text">
                            <?php 
                            if ($stats['last_import']) {
                                echo wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($stats['last_import']->import_date));
                            } else {
                                _e('Never', 'tmdb-api-connector');
                            }
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="tmdb-logs-actions">
                    <button type="button" id="clear-logs" class="button"><?php _e('Clear All Logs', 'tmdb-api-connector'); ?></button>
                    <button type="button" id="refresh-logs" class="button"><?php _e('Refresh', 'tmdb-api-connector'); ?></button>
                </div>
                
                <div class="tmdb-logs-table">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Date', 'tmdb-api-connector'); ?></th>
                                <th><?php _e('Type', 'tmdb-api-connector'); ?></th>
                                <th><?php _e('Status', 'tmdb-api-connector'); ?></th>
                                <th><?php _e('Message', 'tmdb-api-connector'); ?></th>
                                <th><?php _e('Items', 'tmdb-api-connector'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="5"><?php _e('No logs found.', 'tmdb-api-connector'); ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->import_date)); ?></td>
                                        <td><?php echo esc_html($log->import_type); ?></td>
                                        <td>
                                            <span class="tmdb-status-badge status-<?php echo esc_attr($log->status); ?>">
                                                <?php echo esc_html($log->status); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html($log->message); ?></td>
                                        <td><?php echo $log->items_imported ? intval($log->items_imported) : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
} 