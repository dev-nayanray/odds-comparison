<?php
/**
 * 404 Error Template
 * 
 * The template displayed when a page is not found.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();
?>

<div class="oc-404-container">
    <div class="oc-404-content">
        <div class="oc-404-icon">🔍</div>
        <h1 class="oc-404-title"><?php esc_html_e( '404 - Page Not Found', 'odds-comparison' ); ?></h1>
        <p class="oc-404-message">
            <?php esc_html_e( 'Sorry, the page you are looking for does not exist or has been moved.', 'odds-comparison' ); ?>
        </p>
        
        <div class="oc-404-search">
            <h2><?php esc_html_e( 'Search for what you need:', 'odds-comparison' ); ?></h2>
            <?php get_search_form(); ?>
        </div>
        
        <div class="oc-404-links">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="oc-404-link">
                <?php esc_html_e( 'Go to Homepage', 'odds-comparison' ); ?>
            </a>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'match' ) ); ?>" class="oc-404-link">
                <?php esc_html_e( 'View Matches', 'odds-comparison' ); ?>
            </a>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'operator' ) ); ?>" class="oc-404-link">
                <?php esc_html_e( 'View Bookmakers', 'odds-comparison' ); ?>
            </a>
        </div>
        
        <div class="oc-404-suggestions">
            <h2><?php esc_html_e( 'Popular Searches', 'odds-comparison' ); ?></h2>
            <div class="oc-404-tags">
                <?php
                $popular_leagues = get_terms( array(
                    'taxonomy' => 'league',
                    'hide_empty' => true,
                    'number' => 6,
                ) );
                
                foreach ( $popular_leagues as $league ) :
                    printf(
                        '<a href="%s" class="oc-tag">%s</a>',
                        esc_url( get_term_link( $league ) ),
                        esc_html( $league->name )
                    );
                endforeach;
                ?>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();

