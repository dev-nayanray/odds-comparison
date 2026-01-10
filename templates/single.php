<?php
/**
 * Single Post Template
 * 
 * The template for displaying single blog posts.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();
?>

<main class="site-main oc-single-post">
    <div class="container">
        
        <?php
        // Breadcrumb navigation
        if ( function_exists( 'bcn_display' ) ) {
            echo '<nav class="oc-breadcrumb" typeof="BreadcrumbList" vocab="https://schema.org/">';
            bcn_display();
            echo '</nav>';
        }
        ?>
        
        <?php
        while ( have_posts() ) :
            the_post();
            ?>
            
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'oc-article' ); ?>>
                
                <header class="oc-post-header">
                    <?php
                    // Post category
                    $categories = get_the_category();
                    if ( ! empty( $categories ) ) :
                        ?>
                        <div class="oc-post-category">
                            <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>">
                                <?php echo esc_html( $categories[0]->name ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php the_title( '<h1 class="oc-post-title">', '</h1>' ); ?>
                    
                    <div class="oc-post-meta">
                        <span class="oc-post-author">
                            <?php
                            printf(
                                /* translators: %s: Author name */
                                esc_html__( 'By %s', 'odds-comparison' ),
                                '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a>'
                            );
                            ?>
                        </span>
                        
                        <span class="oc-post-date">
                            <?php
                            printf(
                                /* translators: %s: Post date */
                                esc_html__( 'Published on %s', 'odds-comparison' ),
                                '<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>'
                            );
                            ?>
                        </span>
                        
                        <?php if ( get_the_modified_date() !== get_the_date() ) : ?>
                            <span class="oc-post-updated">
                                <?php
                                printf(
                                    /* translators: %s: Modified date */
                                    esc_html__( 'Updated on %s', 'odds-comparison' ),
                                    '<time datetime="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . esc_html( get_the_modified_date() ) . '</time>'
                                );
                                ?>
                            </span>
                        <?php endif; ?>
                        
                        <span class="oc-post-comments">
                            <?php
                            $comments_number = get_comments_number();
                            if ( '1' === $comments_number ) {
                                printf( esc_html__( '%d Comment', 'odds-comparison' ), $comments_number );
                            } else {
                                printf( esc_html__( '%d Comments', 'odds-comparison' ), $comments_number );
                            }
                            ?>
                        </span>
                    </div>
                    
                    <?php if ( has_post_thumbnail() ) : ?>
                        <figure class="oc-post-thumbnail">
                            <?php the_post_thumbnail( 'large', array( 'alt' => get_the_title() ) ); ?>
                            <?php if ( $caption = get_post( get_post_thumbnail_id() )->post_excerpt ) : ?>
                                <figcaption><?php echo esc_html( $caption ); ?></figcaption>
                            <?php endif; ?>
                        </figure>
                    <?php endif; ?>
                </header>
                
                <div class="oc-post-content">
                    <?php the_content(); ?>
                    
                    <?php
                    wp_link_pages( array(
                        'before' => '<div class="oc-page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'odds-comparison' ) . '</span>',
                        'after'  => '</div>',
                        'link_before' => '<span>',
                        'link_after'  => '</span>',
                    ) );
                    ?>
                </div>
                
                <footer class="oc-post-footer">
                    <?php
                    // Tags
                    $tags = get_the_tags();
                    if ( ! empty( $tags ) ) :
                        ?>
                        <div class="oc-post-tags">
                            <span class="tag-label"><?php esc_html_e( 'Tags:', 'odds-comparison' ); ?></span>
                            <?php foreach ( $tags as $tag ) : ?>
                                <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" rel="tag">
                                    <?php echo esc_html( $tag->name ); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php
                    // Author bio
                    $author_bio = get_the_author_meta( 'description' );
                    if ( $author_bio ) :
                        ?>
                        <div class="oc-author-bio">
                            <div class="author-avatar">
                                <?php echo get_avatar( get_the_author_meta( 'user_email' ), 80, '', '', array( 'class' => 'rounded' ) ); ?>
                            </div>
                            <div class="author-info">
                                <h3 class="author-name">
                                    <?php
                                    printf(
                                        /* translators: %s: Author name */
                                        esc_html__( 'About %s', 'odds-comparison' ),
                                        get_the_author()
                                    );
                                    ?>
                                </h3>
                                <p class="author-description"><?php echo esc_html( $author_bio ); ?></p>
                                <a class="author-link" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
                                    <?php esc_html_e( 'View all posts', 'odds-comparison' ); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </footer>
                
            </article>
            
            <?php
            // Related posts
            $related_args = array(
                'post_type'           => 'post',
                'posts_per_page'      => 3,
                'post__not_in'        => array( get_the_ID() ),
                'ignore_sticky_posts' => true,
            );
            
            $related_query = new WP_Query( $related_args );
            
            if ( $related_query->have_posts() ) :
                ?>
                <section class="oc-related-posts">
                    <h2 class="oc-section-title"><?php esc_html_e( 'Related Articles', 'odds-comparison' ); ?></h2>
                    
                    <div class="oc-related-grid">
                        <?php
                        while ( $related_query->have_posts() ) :
                            $related_query->the_post();
                            ?>
                            <article class="oc-related-post">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <a href="<?php the_permalink(); ?>" class="oc-related-thumbnail">
                                        <?php the_post_thumbnail( 'medium', array( 'alt' => get_the_title() ) ); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <h3 class="oc-related-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                
                                <div class="oc-related-meta">
                                    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                        <?php echo esc_html( get_the_date() ); ?>
                                    </time>
                                </div>
                            </article>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </section>
            <?php endif; ?>
            
            <?php
            // Comments
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
            
        endwhile;
        ?>
        
    </div>
</main>

<?php
get_footer();

