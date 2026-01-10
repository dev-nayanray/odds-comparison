<?php
/**
 * Default Page Template
 * 
 * The default template for individual pages.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();
?>

<div class="oc-page-container">
    <div class="oc-content-area">
        <?php
        while ( have_posts() ) :
            the_post();
        ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'oc-page' ); ?>>
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="oc-page-thumbnail">
                        <?php the_post_thumbnail( 'large', array( 'alt' => get_the_title() ) ); ?>
                    </div>
                <?php endif; ?>
                
                <header class="oc-page-header">
                    <h1 class="oc-page-title"><?php the_title(); ?></h1>
                </header>
                
                <div class="oc-page-content">
                    <?php the_content(); ?>
                </div>
                
                <?php
                // Display page links for paginated content
                wp_link_pages( array(
                    'before' => '<div class="oc-page-links">' . esc_html__( 'Pages:', 'odds-comparison' ),
                    'after'  => '</div>',
                ) );
                ?>
            </article>
            
            <?php
            // If comments are open or we have at least one comment, load the comment template
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
            ?>
            
        <?php endwhile; ?>
    </div>
    
    <?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
        <aside class="oc-sidebar">
            <?php dynamic_sidebar( 'sidebar-1' ); ?>
        </aside>
    <?php endif; ?>
</div>

<?php
get_footer();

