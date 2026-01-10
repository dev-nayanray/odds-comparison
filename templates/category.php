<?php
/**
 * Category Archive Template
 * 
 * The template for displaying category archive pages.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();
?>

<main class="site-main oc-category-archive">
    <div class="container">
        
        <header class="oc-archive-header">
            <div class="oc-archive-breadcrumb">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php esc_html_e( 'Home', 'odds-comparison' ); ?>
                </a>
                <span class="separator">/</span>
                <span class="current"><?php single_cat_title( '', true ); ?></span>
            </div>
            
            <h1 class="oc-archive-title">
                <?php single_cat_title( '', true ); ?>
            </h1>
            
            <?php
            $category_description = category_description();
            if ( ! empty( $category_description ) ) :
                ?>
                <div class="oc-archive-description">
                    <?php echo wp_kses_post( $category_description ); ?>
                </div>
            <?php endif; ?>
            
            <div class="oc-archive-stats">
                <span class="stat">
                    <?php
                    $count = get_category( get_query_var( 'cat' ) )->count;
                    printf(
                        /* translators: %d: Number of posts */
                        esc_html__( '%d articles', 'odds-comparison' ),
                        $count
                    );
                    ?>
                </span>
            </div>
        </header>
        
        <div class="oc-archive-layout">
            <?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
                <aside class="oc-archive-sidebar" role="complementary">
                    <?php dynamic_sidebar( 'sidebar-1' ); ?>
                </aside>
            <?php endif; ?>
            
            <div class="oc-archive-content">
                <?php if ( have_posts() ) : ?>
                    <div class="oc-posts-grid oc-category-grid">
                        <?php
                        while ( have_posts() ) :
                            the_post();
                            ?>
                            <article id="post-<?php the_ID(); ?>" <?php post_class( 'oc-post-card' ); ?>>
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <a href="<?php the_permalink(); ?>" class="oc-post-thumbnail-link">
                                        <?php the_post_thumbnail( 'medium_large', array( 'alt' => get_the_title() ) ); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <div class="oc-post-card-content">
                                    <header class="oc-post-card-header">
                                        <?php the_title( '<h2 class="oc-post-card-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
                                        
                                        <div class="oc-post-card-meta">
                                            <span class="oc-post-author">
                                                <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
                                                    <?php echo esc_html( get_the_author() ); ?>
                                                </a>
                                            </span>
                                            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                                <?php echo esc_html( get_the_date() ); ?>
                                            </time>
                                        </div>
                                    </header>
                                    
                                    <div class="oc-post-card-excerpt">
                                        <?php the_excerpt( array( 'length' => 30, 'more' => '&hellip;' ) ); ?>
                                    </div>
                                    
                                    <footer class="oc-post-card-footer">
                                        <a href="<?php the_permalink(); ?>" class="oc-read-more">
                                            <?php esc_html_e( 'Read More', 'odds-comparison' ); ?>
                                        </a>
                                    </footer>
                                </div>
                            </article>
                            <?php
                        endwhile;
                        ?>
                    </div>
                    
                    <?php the_posts_pagination( array(
                        'mid_size'  => 2,
                        'prev_text' => esc_html__( 'Previous', 'odds-comparison' ),
                        'next_text' => esc_html__( 'Next', 'odds-comparison' ),
                    ) ); ?>
                    
                else :
                    ?>
                    <div class="oc-no-posts">
                        <p><?php esc_html_e( 'No posts found in this category.', 'odds-comparison' ); ?></p>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="oc-back-home">
                            <?php esc_html_e( 'Return to Homepage', 'odds-comparison' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();

