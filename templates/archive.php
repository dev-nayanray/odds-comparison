<?php
/**
 * Archive Template (Blog)
 * 
 * The template for displaying blog posts archive.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();
?>

<main class="site-main oc-blog-archive">
    <div class="container">
        
        <header class="oc-archive-header">
            <?php if ( is_search() ) : ?>
                <h1 class="oc-archive-title">
                    <?php
                    /* translators: %s: Search query */
                    printf( esc_html__( 'Search Results for: "%s"', 'odds-comparison' ), get_search_query() );
                    ?>
                </h1>
                
                <div class="oc-search-result-count">
                    <?php
                    global $wp_query;
                    printf(
                        /* translators: %d: Number of results */
                        esc_html__( 'Found %d articles', 'odds-comparison' ),
                        $wp_query->found_posts
                    );
                    ?>
                </div>
                
            <?php elseif ( is_category() ) : ?>
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
                
            <?php elseif ( is_tag() ) : ?>
                <h1 class="oc-archive-title">
                    <?php
                    /* translators: %s: Tag name */
                    printf( esc_html__( 'Posts tagged: %s', 'odds-comparison' ), single_tag_title( '', false ) );
                    ?>
                </h1>
                
                <?php
                $tag_description = tag_description();
                if ( ! empty( $tag_description ) ) :
                    ?>
                    <div class="oc-archive-description">
                        <?php echo wp_kses_post( $tag_description ); ?>
                    </div>
                <?php endif; ?>
                
            <?php elseif ( is_author() ) : ?>
                <?php the_post(); ?>
                <h1 class="oc-archive-title">
                    <?php
                    /* translators: %s: Author name */
                    printf( esc_html__( 'Author: %s', 'odds-comparison' ), get_the_author() );
                    ?>
                </h1>
                
                <div class="oc-author-info-archive">
                    <?php echo get_avatar( get_the_author_meta( 'user_email' ), 100, '', '', array( 'class' => 'author-avatar' ) ); ?>
                    <div class="author-bio">
                        <?php the_author_meta( 'description' ); ?>
                    </div>
                </div>
                <?php rewind_posts(); ?>
                
            <?php elseif ( is_archive() ) : ?>
                <h1 class="oc-archive-title">
                    <?php the_archive_title(); ?>
                </h1>
                
                <?php
                $archive_description = get_the_archive_description();
                if ( ! empty( $archive_description ) ) :
                    ?>
                    <div class="oc-archive-description">
                        <?php echo wp_kses_post( $archive_description ); ?>
                    </div>
                <?php endif; ?>
                
            <?php elseif ( is_home() ) : ?>
                <h1 class="oc-archive-title">
                    <?php
                    $blog_page = get_option( 'page_for_posts' );
                    if ( $blog_page ) {
                        echo get_the_title( $blog_page );
                    } else {
                        esc_html_e( 'Blog', 'odds-comparison' );
                    }
                    ?>
                </h1>
                
                <?php
                $blog_description = get_option( 'blogdescription' );
                if ( $blog_description ) :
                    ?>
                    <div class="oc-archive-description">
                        <?php echo esc_html( $blog_description ); ?>
                    </div>
                <?php endif; ?>
                
            <?php endif; ?>
        </header>
        
        <div class="oc-archive-layout">
            <?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
                <aside class="oc-archive-sidebar" role="complementary">
                    <?php dynamic_sidebar( 'sidebar-1' ); ?>
                </aside>
            <?php endif; ?>
            
            <div class="oc-archive-content">
                <?php if ( have_posts() ) : ?>
                    <div class="oc-posts-grid">
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
                                        <?php
                                        // Category
                                        $categories = get_the_category();
                                        if ( ! empty( $categories ) ) :
                                            ?>
                                            <div class="oc-post-category">
                                                <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>">
                                                    <?php echo esc_html( $categories[0]->name ); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        
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
                                        <?php
                                        the_excerpt( array(
                                            'length' => 25,
                                            'more' => '&hellip;',
                                        ) );
                                        ?>
                                    </div>
                                    
                                    <footer class="oc-post-card-footer">
                                        <a href="<?php the_permalink(); ?>" class="oc-read-more">
                                            <?php esc_html_e( 'Read More', 'odds-comparison' ); ?>
                                            <span class="screen-reader-text"><?php the_title(); ?></span>
                                        </a>
                                        
                                        <?php if ( function_exists( 'oc_share_post' ) ) : ?>
                                            <div class="oc-share-buttons">
                                                <?php oc_share_post(); ?>
                                            </div>
                                        <?php endif; ?>
                                    </footer>
                                </div>
                            </article>
                            <?php
                        endwhile;
                        ?>
                    </div>
                    
                    <?php
                    // Pagination
                    the_posts_pagination( array(
                        'mid_size'  => 2,
                        'prev_text' => esc_html__( 'Previous', 'odds-comparison' ),
                        'next_text' => esc_html__( 'Next', 'odds-comparison' ),
                        'screen_reader_text' => esc_html__( 'Posts navigation', 'odds-comparison' ),
                    ) );
                    ?>
                    
                else :
                    ?>
                    <div class="oc-no-posts">
                        <p class="oc-no-results">
                            <?php
                            if ( is_search() ) {
                                esc_html_e( 'No articles found matching your search. Please try different keywords.', 'odds-comparison' );
                            } else {
                                esc_html_e( 'No articles found.', 'odds-comparison' );
                            }
                            ?>
                        </p>
                        
                        <?php if ( is_search() ) : ?>
                            <div class="oc-search-form-404">
                                <?php get_search_form(); ?>
                            </div>
                        <?php endif; ?>
                        
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

