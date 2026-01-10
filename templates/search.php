<?php
/**
 * Search Results Template
 * 
 * The template for displaying search results.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();
?>

<main class="site-main oc-search-results">
    <div class="container">
        
        <header class="oc-search-header">
            <h1 class="oc-search-title">
                <?php
                /* translators: %s: Search query */
                printf( esc_html__( 'Search Results for: "%s"', 'odds-comparison' ), get_search_query() );
                ?>
            </h1>
            
            <div class="oc-search-form-large">
                <?php get_search_form( array( 'aria_label' => esc_attr__( 'Search site', 'odds-comparison' ) ) ); ?>
            </div>
            
            <div class="oc-search-stats">
                <span class="results-count">
                    <?php
                    global $wp_query;
                    $total = $wp_query->found_posts;
                    printf(
                        /* translators: %d: Number of results */
                        _n( '%d result found', '%d results found', $total, 'odds-comparison' ),
                        $total
                    );
                    ?>
                </span>
                
                <span class="search-time">
                    <?php
                    timer_stop( 1 );
                    ?>
                </span>
            </div>
        </header>
        
        <div class="oc-search-layout">
            <div class="oc-search-content">
                <?php if ( have_posts() ) : ?>
                    
                    <?php
                    // Search filter tabs
                    $search_types = array(
                        'all'   => esc_html__( 'All', 'odds-comparison' ),
                        'post'  => esc_html__( 'Articles', 'odds-comparison' ),
                        'match' => esc_html__( 'Matches', 'odds-comparison' ),
                        'operator' => esc_html__( 'Bookmakers', 'odds-comparison' ),
                    );
                    ?>
                    <nav class="oc-search-tabs" role="navigation" aria-label="<?php esc_attr_e( 'Search results filter', 'odds-comparison' ); ?>">
                        <ul>
                            <?php foreach ( $search_types as $type => $label ) : ?>
                                <li class="<?php echo ( $type === 'all' && ! isset( $_GET['post_type'] ) ) || ( isset( $_GET['post_type'] ) && $_GET['post_type'] === $type ) ? 'active' : ''; ?>">
                                    <?php
                                    $current_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : 'all';
                                    $url = add_query_arg( 'post_type', $type );
                                    if ( $type === 'all' ) {
                                        $url = remove_query_arg( 'post_type' );
                                    }
                                    ?>
                                    <a href="<?php echo esc_url( $url ); ?>">
                                        <?php echo esc_html( $label ); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                    
                    <div class="oc-search-results-list">
                        <?php
                        while ( have_posts() ) :
                            the_post();
                            
                            $post_type = get_post_type();
                            $post_type_obj = get_post_type_object( $post_type );
                            
                            switch ( $post_type ) {
                                case 'match':
                                    oc_render_match_search_item();
                                    break;
                                case 'operator':
                                    oc_render_operator_search_item();
                                    break;
                                default:
                                    oc_render_post_search_item();
                            }
                        endwhile;
                        ?>
                    </div>
                    
                    <?php the_posts_pagination( array(
                        'mid_size'  => 2,
                        'prev_text' => esc_html__( 'Previous', 'odds-comparison' ),
                        'next_text' => esc_html__( 'Next', 'odds-comparison' ),
                        'screen_reader_text' => esc_html__( 'Search results navigation', 'odds-comparison' ),
                    ) ); ?>
                    
                else :
                    ?>
                    <div class="oc-no-results">
                        <div class="no-results-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="M21 21l-4.35-4.35"></path>
                            </svg>
                        </div>
                        
                        <h2><?php esc_html_e( 'No results found', 'odds-comparison' ); ?></h2>
                        
                        <p>
                            <?php
                            esc_html_e( 'We couldn\'t find what you\'re looking for. Try different keywords or browse our popular sections.', 'odds-comparison' );
                            ?>
                        </p>
                        
                        <div class="oc-suggestions">
                            <h3><?php esc_html_e( 'Popular Searches', 'odds-comparison' ); ?></h3>
                            <div class="oc-popular-tags">
                                <?php
                                $popular_tags = get_tags( array( 'number' => 5, 'orderby' => 'count', 'order' => 'DESC' ) );
                                foreach ( $popular_tags as $tag ) {
                                    echo '<a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '" class="popular-tag">' . esc_html( $tag->name ) . '</a>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="oc-back-home">
                            <?php esc_html_e( 'Go to Homepage', 'odds-comparison' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
                <aside class="oc-search-sidebar" role="complementary">
                    <?php dynamic_sidebar( 'sidebar-1' ); ?>
                </aside>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
get_footer();

/**
 * Render match search item
 * 
 * @since 1.0.0
 */
function oc_render_match_search_item() {
    $match_id = get_the_ID();
    $home_team = get_post_meta( $match_id, 'oc_match_home_team', true );
    $away_team = get_post_meta( $match_id, 'oc_match_away_team', true );
    $match_date = get_post_meta( $match_id, 'oc_match_date', true );
    $league = get_post_meta( $match_id, 'oc_match_league', true );
    ?>
    <article class="oc-search-item oc-search-match">
        <div class="search-item-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 6v6l4 2"></path>
            </svg>
        </div>
        
        <div class="search-item-content">
            <header>
                <span class="item-type"><?php esc_html_e( 'Match', 'odds-comparison' ); ?></span>
                <?php if ( $league ) : ?>
                    <span class="item-league"><?php echo esc_html( $league ); ?></span>
                <?php endif; ?>
            </header>
            
            <h2 class="item-title">
                <a href="<?php the_permalink(); ?>">
                    <?php echo esc_html( $home_team . ' vs ' . $away_team ); ?>
                </a>
            </h2>
            
            <div class="item-meta">
                <?php if ( $match_date ) : ?>
                    <time datetime="<?php echo esc_attr( $match_date ); ?>">
                        <?php echo esc_html( date_i18n( 'd M Y', strtotime( $match_date ) ) ); ?>
                    </time>
                <?php endif; ?>
            </div>
        </div>
        
        <a href="<?php the_permalink(); ?>" class="item-link" aria-label="<?php esc_attr_e( 'View match odds', 'odds-comparison' ); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 12h14M12 5l7 7-7 7"></path>
            </svg>
        </a>
    </article>
    <?php
}

/**
 * Render operator search item
 * 
 * @since 1.0.0
 */
function oc_render_operator_search_item() {
    $operator_id = get_the_ID();
    $rating = get_post_meta( $operator_id, 'oc_operator_rating', true );
    $bonus_text = get_post_meta( $operator_id, 'oc_operator_bonus_text', true );
    $affiliate_url = get_post_meta( $operator_id, 'oc_operator_affiliate_url', true );
    ?>
    <article class="oc-search-item oc-search-operator">
        <div class="search-item-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                <path d="M3 9h18M9 21V9"></path>
            </svg>
        </div>
        
        <div class="search-item-content">
            <header>
                <span class="item-type"><?php esc_html_e( 'Bookmaker', 'odds-comparison' ); ?></span>
                <?php if ( $rating ) : ?>
                    <span class="item-rating">
                        <?php echo esc_html( number_format( $rating, 1 ) ); ?> ★
                    </span>
                <?php endif; ?>
            </header>
            
            <h2 class="item-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h2>
            
            <?php if ( $bonus_text ) : ?>
                <div class="item-bonus">
                    <span class="bonus-label"><?php esc_html_e( 'Bonus:', 'odds-comparison' ); ?></span>
                    <span class="bonus-value"><?php echo esc_html( $bonus_text ); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="search-item-actions">
            <?php if ( $affiliate_url ) : ?>
                <a href="<?php echo esc_url( $affiliate_url ); ?>" class="btn btn-secondary" target="_blank" rel="nofollow sponsored">
                    <?php esc_html_e( 'Visit', 'odds-comparison' ); ?>
                </a>
            <?php endif; ?>
            <a href="<?php the_permalink(); ?>" class="btn btn-outline">
                <?php esc_html_e( 'Review', 'odds-comparison' ); ?>
            </a>
        </div>
    </article>
    <?php
}

/**
 * Render post search item
 * 
 * @since 1.0.0
 */
function oc_render_post_search_item() {
    ?>
    <article class="oc-search-item oc-search-post">
        <?php if ( has_post_thumbnail() ) : ?>
            <a href="<?php the_permalink(); ?>" class="search-item-thumbnail">
                <?php the_post_thumbnail( 'thumbnail', array( 'alt' => get_the_title() ) ); ?>
            </a>
        <?php endif; ?>
        
        <div class="search-item-content">
            <header>
                <span class="item-type"><?php esc_html_e( 'Article', 'odds-comparison' ); ?></span>
                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                    <?php echo esc_html( get_the_date() ); ?>
                </time>
            </header>
            
            <h2 class="item-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h2>
            
            <div class="item-excerpt">
                <?php the_excerpt(); ?>
            </div>
        </div>
        
        <a href="<?php the_permalink(); ?>" class="item-link" aria-label="<?php esc_attr_e( 'Read article', 'odds-comparison' ); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 12h14M12 5l7 7-7 7"></path>
            </svg>
        </a>
    </article>
    <?php
}

