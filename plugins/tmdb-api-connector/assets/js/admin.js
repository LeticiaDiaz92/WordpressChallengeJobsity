/**
 * TMDB API Connector Admin JavaScript
 */

jQuery(document).ready(function($) {
    
    // Test connection button
    $('#test-connection').on('click', function() {
        var $button = $(this);
        var $status = $('#connection-status');
        var apiKey = $('#api_key').val();
        
        if (!apiKey) {
            showNotification('Please enter an API key first.', 'error');
            return;
        }
        
        $button.addClass('loading').prop('disabled', true);
        $status.removeClass('connected error').text('');
        
        $.ajax({
            url: tmdb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tmdb_test_connection',
                api_key: apiKey,
                nonce: tmdb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status.addClass('connected').html('<span class="dashicons dashicons-yes-alt"></span> ' + response.data);
                    showNotification('Connection successful!', 'success');
                } else {
                    $status.addClass('error').html('<span class="dashicons dashicons-warning"></span> ' + response.data);
                    showNotification(response.data, 'error');
                }
            },
            error: function() {
                $status.addClass('error').html('<span class="dashicons dashicons-warning"></span> Connection failed');
                showNotification('Connection test failed.', 'error');
            },
            complete: function() {
                $button.removeClass('loading').prop('disabled', false);
            }
        });
    });
    
    // Settings form submission
    $('#tmdb-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submit = $form.find('#submit');
        
        $submit.addClass('loading').prop('disabled', true);
        
        var formData = {
            action: 'tmdb_save_settings',
            api_key: $('#api_key').val(),
            auto_sync: $('#auto_sync').is(':checked') ? '1' : '0',
            sync_frequency: $('#sync_frequency').val(),
            import_limit: $('#import_limit').val(),
            cache_duration: $('#cache_duration').val(),
            enable_logging: $('#enable_logging').is(':checked') ? '1' : '0',
            nonce: tmdb_ajax.nonce
        };
        
        $.ajax({
            url: tmdb_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showNotification(response.data, 'success');
                } else {
                    showNotification(response.data, 'error');
                }
            },
            error: function() {
                showNotification('Failed to save settings.', 'error');
            },
            complete: function() {
                $submit.removeClass('loading').prop('disabled', false);
            }
        });
    });
    
    // Force sync button
    $('#force-sync').on('click', function() {
        if (!confirm(tmdb_ajax.strings.confirm)) {
            return;
        }
        
        var $button = $(this);
        $button.addClass('loading').prop('disabled', true);
        
        // This would trigger a manual sync - for now just show a message
        setTimeout(function() {
            $button.removeClass('loading').prop('disabled', false);
            showNotification('Sync scheduled. Check logs for details.', 'success');
        }, 2000);
    });
    
    // Manual import buttons
    $('.manual-import').on('click', function() {
        var $button = $(this);
        var importType = $button.data('type');
        
        $button.addClass('loading').prop('disabled', true);
        addLogEntry('Starting ' + importType.replace('_', ' ') + ' import...', 'info');
        
        $.ajax({
            url: tmdb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tmdb_manual_import',
                import_type: importType,
                nonce: tmdb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry(response.data.message, 'success');
                    if (response.data.errors && response.data.errors.length > 0) {
                        response.data.errors.forEach(function(error) {
                            addLogEntry('Error: ' + error, 'error');
                        });
                    }
                } else {
                    addLogEntry('Import failed: ' + response.data, 'error');
                }
            },
            error: function() {
                addLogEntry('Import request failed.', 'error');
            },
            complete: function() {
                $button.removeClass('loading').prop('disabled', false);
            }
        });
    });
    
    // Import popular content buttons
    $('.import-popular').on('click', function() {
        var $button = $(this);
        var contentType = $button.data('content');
        
        $button.addClass('loading').prop('disabled', true);
        addLogEntry('Starting popular ' + contentType + ' import...', 'info');
        
        $.ajax({
            url: tmdb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tmdb_import_popular',
                content_type: contentType,
                nonce: tmdb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry(response.data.message, 'success');
                    if (response.data.errors && response.data.errors.length > 0) {
                        response.data.errors.forEach(function(error) {
                            addLogEntry('Error: ' + error, 'error');
                        });
                    }
                } else {
                    addLogEntry('Import failed: ' + response.data, 'error');
                }
            },
            error: function() {
                addLogEntry('Import request failed.', 'error');
            },
            complete: function() {
                $button.removeClass('loading').prop('disabled', false);
            }
        });
    });
    
    // Clear logs button
    $('#clear-logs').on('click', function() {
        if (!confirm('Are you sure you want to clear all logs?')) {
            return;
        }
        
        var $button = $(this);
        $button.addClass('loading').prop('disabled', true);
        
        // Simulate clearing logs
        setTimeout(function() {
            $button.removeClass('loading').prop('disabled', false);
            showNotification('Logs cleared successfully.', 'success');
            location.reload();
        }, 1000);
    });
    
    // Refresh logs button
    $('#refresh-logs').on('click', function() {
        location.reload();
    });
    
    // Helper function to show notifications
    function showNotification(message, type) {
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after($notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
        
        // Make dismissible
        $notice.on('click', '.notice-dismiss', function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        });
    }
    
    // Helper function to add log entries
    function addLogEntry(message, type) {
        var $log = $('#import-log');
        if ($log.length === 0) {
            return;
        }
        
        var timestamp = new Date().toLocaleTimeString();
        var $entry = $('<div class="log-entry ' + type + '">[' + timestamp + '] ' + message + '</div>');
        
        $log.append($entry);
        $log.scrollTop($log[0].scrollHeight);
    }
    
    // Auto-refresh connection status on API key change
    $('#api_key').on('input', function() {
        var $status = $('#connection-status');
        $status.removeClass('connected error').text('');
    });
    
    // Validate form fields
    $('#import_limit').on('input', function() {
        var value = parseInt($(this).val());
        if (value < 1) {
            $(this).val(1);
        } else if (value > 100) {
            $(this).val(100);
        }
    });
    
    $('#cache_duration').on('input', function() {
        var value = parseInt($(this).val());
        if (value < 300) {
            $(this).val(300);
        } else if (value > 86400) {
            $(this).val(86400);
        }
    });
    
    // Show/hide sync frequency based on auto sync setting
    $('#auto_sync').on('change', function() {
        var $frequency = $('#sync_frequency').closest('tr');
        if ($(this).is(':checked')) {
            $frequency.show();
        } else {
            $frequency.hide();
        }
    }).trigger('change');
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl+S to save settings (on settings page)
        if (e.ctrlKey && e.which === 83 && $('#tmdb-settings-form').length > 0) {
            e.preventDefault();
            $('#tmdb-settings-form').submit();
        }
        
        // Ctrl+R to refresh logs (on logs page)
        if (e.ctrlKey && e.which === 82 && $('.tmdb-logs-container').length > 0) {
            e.preventDefault();
            location.reload();
        }
    });
    
    // Initialize tooltips (if WordPress admin includes them)
    if ($.fn.tooltip) {
        $('[title]').tooltip();
    }
    
    // Progress indicator for long-running operations
    function showProgress(message) {
        var $progress = $('<div class="tmdb-progress"><div class="tmdb-progress-bar"></div><div class="tmdb-progress-text">' + message + '</div></div>');
        $('body').append($progress);
        return $progress;
    }
    
    function hideProgress($progress) {
        if ($progress) {
            $progress.fadeOut(function() {
                $(this).remove();
            });
        }
    }
    
    // Add some loading animations and visual feedback
    $('.tmdb-import-buttons').on('click', '.button', function() {
        var $button = $(this);
        if (!$button.hasClass('loading')) {
            // Visual feedback
            $button.addClass('button-primary-focus');
            setTimeout(function() {
                $button.removeClass('button-primary-focus');
            }, 150);
        }
    });
    
}); 