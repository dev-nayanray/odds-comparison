<?php
/**
 * Author Archive Template
 * 
 * The template for displaying author archive pages.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();
?>

<main class="site-main oc-author-archive">
    <div class="container">
        
        <?php
        // Get the author data
        $curauth = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );
        ?>
        
        <header class="oc-author-header">
            <div class="oc-author-avatar">
                <?php echo get_avatar( $curauth->ID, 150, '', '', array( 'class' => 'avatar rounded' ) ); ?>
            </div>
            
            <div class="oc-author-info">
                <h1 class="oc-author-name">
                    <?php
                    /* translators: %s: Author name */
                    printf( esc_html__( 'Author: %s', 'odds-comparison' ), $curauth->display_name );
                    ?>
                </h1>
                
                <?php if ( $curauth->user_description ) : ?>
                    <p class="oc-author-bio">
                        <?php echo esc_html( $curauth->user_description ); ?>
                    </p>
                <?php endif; ?>
                
                <div class="oc-author-stats">
                    <span class="stat posts-count">
                        <?php
                        $post_count = count_user_posts( $curauth->ID );
                        printf(
                            /* translators: %d: Number of posts */
                            _n( '%d article', '%d articles', $post_count, 'odds-comparison' ),
                            $post_count
                        );
                        ?>
                    </span>
                    
                    <?php if ( $curauth->user_url ) : ?>
                        <span class="stat website">
                            <a href="<?php echo esc_url( $curauth->user_url ); ?>" target="_blank" rel="nofollow">
                                <?php esc_html_e( 'Website', 'odds-comparison' ); ?>
                            </a>
                        </span>
                    <?php endif; ?>
                </div>
                
                <nav class="oc-author-social">
                    <?php
                    // You can add social links here if you have a custom field for them
                    ?>
                </nav>
            </div>
        </header>
        
        <div class="oc-archive-layout">
            <div class="oc-archive-content">
                <h2 class="oc-section-title">
                    <?php echo esc_html( $curauth->display_name ); ?>'s <?php esc_html_e( 'Articles', 'odds-comparison' ); ?>
                </h2>
                
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
                        <p><?php esc_html_e( 'No articles published yet.', 'odds-comparison' ); ?></p>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="oc-back-home">
                            <?php esc_html_e( 'Return to Homepage', 'odds-comparison' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
                <aside class="oc-archive-sidebar" role="complementary">
                    <?php dynamic_sidebar( 'sidebar-1' ); ?>
                </aside>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
get_footer();

