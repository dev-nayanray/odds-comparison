<?php
/**
 * The Front Page Template
 * 
 * This template is used when the front page is set to "Your latest posts"
 * or when a static page is selected as the front page.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Get the template from templates/home.php
// This ensures the homepage uses the custom template design
$template = locate_template( 'templates/home.php' );

if ( $template ) {
    load_template( $template );
} else {
    // Fallback if templates/home.php doesn't exist
    get_header(); ?>

    <main id="primary" class="site-main">
        <section class="oc-homepage-content">
            <div class="container">
                <h1><?php esc_html_e( 'Welcome to Odds Comparison', 'odds-comparison' ); ?></h1>
                <p><?php esc_html_e( 'Compare the best betting odds from trusted bookmakers.', 'odds-comparison' ); ?></p>
                
                <?php
                // Display recent matches if available
                $args = array(
                    'post_type'      => 'match',
                    'posts_per_page' => 5,
                    'post_status'    => 'publish',
                );
                
                $matches_query = new WP_Query( $args );
                
                if ( $matches_query->have_posts() ) :
                    ?>
                    <div class="oc-matches-section">
                        <h2><?php esc_html_e( 'Upcoming Matches', 'odds-comparison' ); ?></h2>
                        <div class="oc-matches-list">
                            <?php
                            while ( $matches_query->have_posts() ) :
                                $matches_query->the_post();
                                ?>
                                <article class="oc-match-item">
                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <?php the_excerpt(); ?>
                                </article>
                                <?php
                            endwhile;
                            wp_reset_postdata();
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main><!-- #primary -->

    <?php
    get_sidebar();
    get_footer();
}

