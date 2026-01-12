<?php
/**
 * The main template file
 * 
 * This is the most generic template file in a WordPress theme.
 * It's used when no more specific template is found.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header(); ?>

<main id="primary" class="site-main">
    
    <?php if ( have_posts() ) : ?>
        
        <?php if ( is_home() && ! is_front_page() ) : ?>
            <header class="page-header">
                <h1 class="page-title"><?php single_post_title(); ?></h1>
            </header>
        <?php endif; ?>
        
        <div class="posts-grid">
            <?php
            // Start the Loop
            while ( have_posts() ) :
                the_post();
                
                // Load appropriate template part
                get_template_part( 'template-parts/content', get_post_type() );
                
            endwhile;
            ?>
        </div>
        
        <?php
        // Pagination
        the_posts_pagination( array(
            'prev_text' => __( 'Previous', 'odds-comparison' ),
            'next_text' => __( 'Next', 'odds-comparison' ),
            'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'odds-comparison' ) . ' </span>',
        ) );
        ?>
        
    <?php else : ?>
        
        <?php
        // If no content, include the "No posts found" template
        get_template_part( 'template-parts/content', 'none' );
        ?>
        
    <?php endif; ?>
    
</main><!-- #primary -->

<?php
// Check if sidebar should be displayed
if ( is_active_sidebar( 'sidebar-1' ) || is_home() ) {
    get_sidebar();
}

get_footer();

