<?php
/**
 * The main template file
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        
        <?php if (have_posts()) : ?>
            
            <div class="posts-container">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <header class="entry-header">
                            <?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '">', '</a></h2>'); ?>
                        </header>
                        
                        <div class="entry-content">
                            <?php the_excerpt(); ?>
                        </div>
                        
                        <footer class="entry-footer">
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="read-more">
                                <?php _e('Read More', 'movies-theme'); ?>
                            </a>
                        </footer>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <?php the_posts_navigation(); ?>
            
        <?php else : ?>
            
            <section class="no-results not-found">
                <header class="page-header">
                    <h1 class="page-title"><?php _e('Nothing here', 'movies-theme'); ?></h1>
                </header>
                
                <div class="page-content">
                    <p><?php _e('It looks like nothing was found at this location.', 'movies-theme'); ?></p>
                </div>
            </section>
            
        <?php endif; ?>
        
    </main>
</div>

<?php
get_sidebar();
get_footer(); 