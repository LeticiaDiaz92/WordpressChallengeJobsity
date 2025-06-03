<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#main"><?php _e('Skip to content', 'movies-theme'); ?></a>
    
    <header id="masthead" class="site-header">
        <div class="container">
            <div class="site-branding">
                <?php
                if (has_custom_logo()) {
                    the_custom_logo();
                } else {
                    ?>
                    <h1 class="site-title">
                        <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                            <?php bloginfo('name'); ?>
                        </a>
                    </h1>
                    <?php
                    $description = get_bloginfo('description', 'display');
                    if ($description || is_customize_preview()) {
                        ?>
                        <p class="site-description"><?php echo $description; ?></p>
                        <?php
                    }
                }
                ?>
            </div>
            
            <nav id="site-navigation" class="main-navigation">
                <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                    <?php _e('Primary Menu', 'movies-theme'); ?>
                </button>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_id'        => 'primary-menu',
                    'menu_class'     => 'nav-menu',
                ));
                ?>
            </nav>
            
            <!-- Header Search Form -->
            <div class="header-search">
                <?php get_search_form(); ?>
            </div>
            
            <!-- User Account Section -->
            <div class="header-account">
                <?php if (is_user_logged_in()) : ?>
                    <div class="user-menu">
                        <button class="user-toggle">
                            <span class="user-name"><?php echo wp_get_current_user()->display_name; ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown">
                            <a href="<?php echo esc_url(home_url('/wishlist')); ?>" class="wishlist-link">
                                <i class="fas fa-heart"></i>
                                <span><?php _e('My Wishlist', 'movies-theme'); ?></span>
                                <?php $wishlist_count = movies_get_wishlist_count(); ?>
                                <?php if ($wishlist_count > 0) : ?>
                                    <span class="wishlist-count"><?php echo $wishlist_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="<?php echo wp_logout_url(home_url()); ?>">
                                <i class="fas fa-sign-out-alt"></i>
                                <span><?php _e('Logout', 'movies-theme'); ?></span>
                            </a>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="auth-buttons">
                        <button class="btn btn-outline login-modal-trigger" data-modal="login">
                            <i class="fas fa-sign-in-alt"></i>
                            <span><?php _e('Login', 'movies-theme'); ?></span>
                        </button>
                        <button class="btn btn-primary register-modal-trigger" data-modal="register">
                            <i class="fas fa-user-plus"></i>
                            <span><?php _e('Register', 'movies-theme'); ?></span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <div id="content" class="site-content">
        <div class="container"> 