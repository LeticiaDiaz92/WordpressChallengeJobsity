<?php if (!is_user_logged_in()) : ?>

<!-- Login Modal -->
<div id="login-modal" class="auth-modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2><?php _e('Welcome Back!', 'movies-theme'); ?></h2>
            <button class="modal-close" aria-label="<?php _e('Close', 'movies-theme'); ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <?php if (isset($_GET['login_error'])) : ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo esc_html($_GET['login_error']); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" class="auth-form">
                <?php wp_nonce_field('movies_login', 'movies_login_nonce'); ?>
                
                <div class="form-group">
                    <label for="login_username"><?php _e('Username or Email', 'movies-theme'); ?></label>
                    <input type="text" id="login_username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="login_password"><?php _e('Password', 'movies-theme'); ?></label>
                    <input type="password" id="login_password" name="password" required>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="remember" value="1">
                        <span><?php _e('Remember Me', 'movies-theme'); ?></span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <?php _e('Login', 'movies-theme'); ?>
                </button>
            </form>
            
            <div class="modal-footer">
                <p><?php _e("Don't have an account?", 'movies-theme'); ?> 
                   <a href="#" class="switch-to-register"><?php _e('Sign up here', 'movies-theme'); ?></a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div id="register-modal" class="auth-modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2><?php _e('Join Our Community!', 'movies-theme'); ?></h2>
            <button class="modal-close" aria-label="<?php _e('Close', 'movies-theme'); ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <?php if (isset($_GET['register_error'])) : ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo esc_html($_GET['register_error']); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" class="auth-form">
                <?php wp_nonce_field('movies_register', 'movies_register_nonce'); ?>
                
                <div class="form-group">
                    <label for="register_username"><?php _e('Username', 'movies-theme'); ?></label>
                    <input type="text" id="register_username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="register_email"><?php _e('Email', 'movies-theme'); ?></label>
                    <input type="email" id="register_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="register_password"><?php _e('Password', 'movies-theme'); ?></label>
                    <input type="password" id="register_password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <?php _e('Create Account', 'movies-theme'); ?>
                </button>
            </form>
            
            <div class="modal-footer">
                <p><?php _e('Already have an account?', 'movies-theme'); ?> 
                   <a href="#" class="switch-to-login"><?php _e('Login here', 'movies-theme'); ?></a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php endif; ?> 