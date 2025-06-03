<?php
/**
 * User Functions for Movies Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get user wishlist
 */
function movies_get_user_wishlist($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return array();
    }
    
    $wishlist = get_user_meta($user_id, 'movie_wishlist', true);
    
    // Debug temporal
    // var_dump($wishlist); // Quita esta línea
    
    // Manejar diferentes casos
    if (empty($wishlist) || $wishlist === '' || $wishlist === false) {
        return array();
    }
    
    return is_array($wishlist) ? $wishlist : array();
}

/**
 * Add movie to wishlist
 */
function movies_add_to_wishlist($movie_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $movie_id = intval($movie_id);
    $wishlist = movies_get_user_wishlist($user_id);
    
    if (!in_array($movie_id, $wishlist)) {
        $wishlist[] = $movie_id;
        
        // Actualizar el wishlist
        update_user_meta($user_id, 'movie_wishlist', $wishlist);
        
        // Verificar que se guardó correctamente
        $saved_wishlist = movies_get_user_wishlist($user_id);
        
        // El éxito se verifica si la película está ahora en el wishlist guardado
        return in_array($movie_id, $saved_wishlist);
    }
    
    // La película ya estaba en el wishlist
    return false;
}

/**
 * Remove movie from wishlist
 */
function movies_remove_from_wishlist($movie_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $wishlist = movies_get_user_wishlist($user_id);
    $key = array_search($movie_id, $wishlist);
    
    if ($key !== false) {
        unset($wishlist[$key]);
        $wishlist = array_values($wishlist); // Reindex array
        update_user_meta($user_id, 'movie_wishlist', $wishlist);
        return true;
    }
    
    return false;
}

/**
 * Check if movie is in wishlist
 */
function movies_is_in_wishlist($movie_id, $user_id = null) {
    $wishlist = movies_get_user_wishlist($user_id);
    return in_array($movie_id, $wishlist);
}

/**
 * Get wishlist count
 */
function movies_get_wishlist_count($user_id = null) {
    $wishlist = movies_get_user_wishlist($user_id);
    return count($wishlist);
}

/**
 * Handle user login form
 */
function movies_handle_login() {
    if (!isset($_POST['movies_login_nonce']) || !wp_verify_nonce($_POST['movies_login_nonce'], 'movies_login')) {
        return;
    }
    
    $username = sanitize_text_field($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    $user = wp_signon(array(
        'user_login' => $username,
        'user_password' => $password,
        'remember' => $remember
    ));
    
    if (is_wp_error($user)) {
        wp_redirect(add_query_arg('login_error', urlencode($user->get_error_message()), wp_get_referer()));
    } else {
        wp_redirect(remove_query_arg('login_error', wp_get_referer()));
    }
    exit;
}
add_action('init', 'movies_handle_login');

/**
 * Handle user registration
 */
function movies_handle_registration() {
    if (!isset($_POST['movies_register_nonce']) || !wp_verify_nonce($_POST['movies_register_nonce'], 'movies_register')) {
        return;
    }
    
    $username = sanitize_text_field($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        wp_redirect(add_query_arg('register_error', urlencode($user_id->get_error_message()), wp_get_referer()));
    } else {
        // Auto login after registration
        wp_signon(array(
            'user_login' => $username,
            'user_password' => $password,
            'remember' => true
        ));
        wp_redirect(remove_query_arg('register_error', wp_get_referer()));
    }
    exit;
}
add_action('init', 'movies_handle_registration');

/**
 * Show wishlist in user profile (admin)
 */
function movies_show_user_wishlist_admin($user) {
    if (!current_user_can('edit_user', $user->ID)) {
        return false;
    }
    
    $wishlist = movies_get_user_wishlist($user->ID);
    ?>
    <h3><?php _e('Movie Wishlist Debug', 'movies-theme'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label><?php _e('Wishlist Data', 'movies-theme'); ?></label></th>
            <td>
                <strong>Count:</strong> <?php echo count($wishlist); ?> movies<br>
                <strong>Movie IDs:</strong> <?php echo !empty($wishlist) ? implode(', ', $wishlist) : 'Empty'; ?><br>
                <strong>Raw Data:</strong> <pre><?php var_dump($wishlist); ?></pre>
                
                <?php if (!empty($wishlist)) : ?>
                    <h4>Movies in Wishlist:</h4>
                    <ul>
                        <?php foreach ($wishlist as $movie_id) : ?>
                            <li>
                                ID: <?php echo $movie_id; ?> - 
                                <?php 
                                $movie = get_post($movie_id);
                                if ($movie && $movie->post_type === 'movie') {
                                    echo '<strong>' . $movie->post_title . '</strong>';
                                } else {
                                    echo '<em>Movie not found</em>';
                                }
                                ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <!-- Manual test -->
                <p>
                    <button type="button" onclick="manualAddToWishlist()" class="button button-primary">
                        🧪 Add Test Movie to Wishlist
                    </button>
                    <button type="button" onclick="clearWishlist()" class="button">
                        🗑️ Clear Wishlist
                    </button>
                </p>
                
                <script>
                function manualAddToWishlist() {
                    // Agregar película ID 1 manualmente
                    jQuery.post(ajaxurl, {
                        action: 'add_to_wishlist',
                        movie_id: 1,
                        nonce: '<?php echo wp_create_nonce('movies_nonce'); ?>'
                    }, function(response) {
                        console.log('Response:', response);
                        alert('Response: ' + JSON.stringify(response));
                        location.reload();
                    }).fail(function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('Error: ' + error);
                    });
                }
                
                function clearWishlist() {
                    if (confirm('Clear wishlist?')) {
                        // Limpiar manualmente
                        jQuery.post(ajaxurl, {
                            action: 'wp_ajax_nopriv_test_clear_wishlist',
                            user_id: <?php echo $user->ID; ?>
                        }, function() {
                            alert('Cleared!');
                            location.reload();
                        });
                    }
                }
                </script>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'movies_show_user_wishlist_admin');
add_action('edit_user_profile', 'movies_show_user_wishlist_admin'); 