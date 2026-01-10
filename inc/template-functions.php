<?php
/**
 * Template Functions
 * 
 * Custom template tags and functions for the theme.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Output the theme credit
 * 
 * @since 1.0.0
 */
function oc_the_credit() {
    ?>
    <div class="oc-credit">
        <?php
        printf(
            /* translators: %1$s: Theme name, %2$s: WordPress */
            esc_html__( 'Powered by %1$s and %2$s', 'odds-comparison' ),
            '<a href="' . esc_url( 'https://wordpress.org/' ) . '">WordPress</a>',
            '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>'
        );
        ?>
    </div>
    <?php
}

/**
 * Output the site logo
 * 
 * @since 1.0.0
 */
function oc_the_logo() {
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $logo = wp_get_attachment_image_src( $custom_logo_id, 'full' );
    
    if ( has_custom_logo() ) {
        the_custom_logo();
    } else {
        echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="site-logo-text">';
        echo esc_html( get_bloginfo( 'name' ) );
        echo '</a>';
    }
}

/**
 * Get the primary navigation menu
 * 
 * @since 1.0.0
 * 
 * @param string $location Menu location
 * @return string Menu HTML
 */
function oc_get_nav_menu( $location = 'primary' ) {
    $args = array(
        'theme_location' => $location,
        'container'      => 'nav',
        'container_class'=> 'main-navigation',
        'menu_class'     => 'nav-menu',
        'fallback_cb'    => 'oc_nav_fallback',
        'echo'           => false,
    );
    
    return wp_nav_menu( $args );
}

/**
 * Fallback for primary navigation
 * 
 * @since 1.0.0
 */
function oc_nav_fallback() {
    echo '<nav class="main-navigation" role="navigation" aria-label="' . esc_attr__( 'Primary Menu', 'odds-comparison' ) . '">';
    echo '<ul class="nav-menu">';
    
    wp_list_pages( array(
        'title_li' => '',
        'depth'    => 1,
        'number'   => 5,
    ) );
    
    echo '</ul>';
    echo '</nav>';
}

/**
 * Output odds comparison table
 * 
 * @since 1.0.0
 * 
 * @param int $match_id Match post ID
 */
function oc_odds_table( $match_id ) {
    $odds = oc_get_match_odds( $match_id );
    $best_odds = oc_get_best_odds( $odds );
    $match_data = oc_get_match_data( $match_id );
    
    if ( empty( $odds ) ) {
        echo '<p class="oc-no-odds">' . esc_html__( 'No odds available yet.', 'odds-comparison' ) . '</p>';
        return;
    }
    ?>
    <div class="oc-odds-table-wrapper" role="region" aria-label="<?php esc_attr_e( 'Odds comparison table', 'odds-comparison' ); ?>">
        <div class="odds-table-header">
            <h3><?php esc_html_e( 'Odds Comparison', 'odds-comparison' ); ?></h3>
            <span class="odds-last-updated">
                <?php
                printf(
                    /* translators: %s: Time */
                    esc_html__( 'Updated: %s', 'odds-comparison' ),
                    date_i18n( 'H:i', current_time( 'timestamp' ) )
                );
                ?>
            </span>
        </div>
        
        <div class="odds-table">
            <div class="odds-header-row">
                <div class="odds-bookmaker-col"><?php esc_html_e( 'Bookmaker', 'odds-comparison' ); ?></div>
                <div class="odds-1-col"><?php esc_html_e( '1', 'odds-comparison' ); ?></div>
                <div class="odds-x-col"><?php esc_html_e( 'X', 'odds-comparison' ); ?></div>
                <div class="odds-2-col"><?php esc_html_e( '2', 'odds-comparison' ); ?></div>
                <div class="odds-action-col"></div>
            </div>
            
            <?php foreach ( $odds as $odd ) : ?>
                <?php
                $operator = get_post( $odd['bookmaker_id'] );
                if ( ! $operator ) continue;
                
                $affiliate_url = get_post_meta( $odd['bookmaker_id'], 'oc_operator_affiliate_url', true );
                $is_best_home = $best_odds['home']['odds'] === (float) $odd['odds_home'] && $odd['odds_home'] > 0;
                $is_best_draw = $best_odds['draw']['odds'] === (float) $odd['odds_draw'] && $odd['odds_draw'] > 0;
                $is_best_away = $best_odds['away']['odds'] === (float) $odd['odds_away'] && $odd['odds_away'] > 0;
                ?>
                <div class="odds-data-row">
                    <div class="odds-bookmaker-col">
                        <div class="bookmaker-info">
                            <?php if ( has_post_thumbnail( $odd['bookmaker_id'] ) ) : ?>
                                <?php echo get_the_post_thumbnail( $odd['bookmaker_id'], 'thumbnail', array( 'class' => 'bookmaker-logo' ) ); ?>
                            <?php else : ?>
                                <span class="bookmaker-initials"><?php echo esc_html( substr( $operator->post_title, 0, 2 ) ); ?></span>
                            <?php endif; ?>
                            <span class="bookmaker-name"><?php echo esc_html( $operator->post_title ); ?></span>
                        </div>
                    </div>
                    
                    <div class="odds-1-col <?php echo $is_best_home ? 'best' : ''; ?>">
                        <?php if ( $odd['odds_home'] > 0 ) : ?>
                            <a href="<?php echo esc_url( oc_get_affiliate_url( $odd['bookmaker_id'], $match_id, 'home' ) ); ?>" 
                               class="odds-btn" target="_blank" rel="nofollow sponsored" 
                               aria-label="<?php echo sprintf( esc_attr__( 'Bet on %s at odds of %s', 'odds-comparison' ), $match_data['home_team'], number_format( $odd['odds_home'], 2 ) ); ?>">
                                <?php echo esc_html( number_format( $odd['odds_home'], 2 ) ); ?>
                            </a>
                            <?php if ( $is_best_home ) : ?>
                                <span class="best-badge" aria-label="<?php esc_attr_e( 'Best odds', 'odds-comparison' ); ?>">★</span>
                            <?php endif; ?>
                        <?php else : ?>
                            <span class="odds-na">-</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="odds-x-col <?php echo $is_best_draw ? 'best' : ''; ?>">
                        <?php if ( $odd['odds_draw'] > 0 ) : ?>
                            <a href="<?php echo esc_url( oc_get_affiliate_url( $odd['bookmaker_id'], $match_id, 'draw' ) ); ?>" 
                               class="odds-btn" target="_blank" rel="nofollow sponsored"
                               aria-label="<?php echo sprintf( esc_attr__( 'Bet on draw at odds of %s', 'odds-comparison' ), number_format( $odd['odds_draw'], 2 ) ); ?>">
                                <?php echo esc_html( number_format( $odd['odds_draw'], 2 ) ); ?>
                            </a>
                            <?php if ( $is_best_draw ) : ?>
                                <span class="best-badge" aria-label="<?php esc_attr_e( 'Best odds', 'odds-comparison' ); ?>">★</span>
                            <?php endif; ?>
                        <?php else : ?>
                            <span class="odds-na">-</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="odds-2-col <?php echo $is_best_away ? 'best' : ''; ?>">
                        <?php if ( $odd['odds_away'] > 0 ) : ?>
                            <a href="<?php echo esc_url( oc_get_affiliate_url( $odd['bookmaker_id'], $match_id, 'away' ) ); ?>" 
                               class="odds-btn" target="_blank" rel="nofollow sponsored"
                               aria-label="<?php echo sprintf( esc_attr__( 'Bet on %s at odds of %s', 'odds-comparison' ), $match_data['away_team'], number_format( $odd['odds_away'], 2 ) ); ?>">
                                <?php echo esc_html( number_format( $odd['odds_away'], 2 ) ); ?>
                            </a>
                            <?php if ( $is_best_away ) : ?>
                                <span class="best-badge" aria-label="<?php esc_attr_e( 'Best odds', 'odds-comparison' ); ?>">★</span>
                            <?php endif; ?>
                        <?php else : ?>
                            <span class="odds-na">-</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="odds-action-col">
                        <a href="<?php echo esc_url( $affiliate_url ? $affiliate_url : get_permalink( $odd['bookmaker_id'] ) ); ?>" 
                           class="visit-btn" target="_blank" rel="nofollow sponsored">
                            <?php esc_html_e( 'Visit', 'odds-comparison' ); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="odds-footer">
            <p class="odds-disclaimer">
                <?php esc_html_e( 'Odds subject to change. Please gamble responsibly. 18+', 'odds-comparison' ); ?>
            </p>
        </div>
    </div>
    <?php
}

/**
 * Output match info
 * 
 * @since 1.0.0
 * 
 * @param int $match_id Match post ID
 */
function oc_match_info( $match_id ) {
    $match_data = oc_get_match_data( $match_id );
    
    $league_term = ! empty( $match_data['league_term'] ) ? $match_data['league_term'] : null;
    $sport = ! empty( $match_data['sport'] ) ? $match_data['sport'] : null;
    ?>
    <div class="oc-match-info">
        <div class="match-meta">
            <?php if ( $league_term ) : ?>
                <span class="meta-item meta-league">
                    <a href="<?php echo esc_url( get_term_link( $league_term ) ); ?>">
                        <?php echo esc_html( $league_term->name ); ?>
                    </a>
                </span>
            <?php endif; ?>
            
            <?php if ( $sport ) : ?>
                <span class="meta-item meta-sport">
                    <a href="<?php echo esc_url( get_term_link( $sport ) ); ?>">
                        <?php echo esc_html( $sport->name ); ?>
                    </a>
                </span>
            <?php endif; ?>
            
            <?php if ( ! empty( $match_data['stadium'] ) ) : ?>
                <span class="meta-item meta-stadium">
                    <?php echo esc_html( $match_data['stadium'] ); ?>
                </span>
            <?php endif; ?>
        </div>
        
        <div class="match-teams-display">
            <div class="team team-home">
                <span class="team-name"><?php echo esc_html( $match_data['home_team'] ); ?></span>
            </div>
            
            <div class="match-vs">
                <span class="vs"><?php esc_html_e( 'vs', 'odds-comparison' ); ?></span>
            </div>
            
            <div class="team team-away">
                <span class="team-name"><?php echo esc_html( $match_data['away_team'] ); ?></span>
            </div>
        </div>
        
        <div class="match-datetime">
            <?php if ( ! empty( $match_data['match_date'] ) ) : ?>
                <span class="match-date">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <?php echo esc_html( date_i18n( 'l, d F Y', strtotime( $match_data['match_date'] ) ) ); ?>
                </span>
            <?php endif; ?>
            
            <?php if ( ! empty( $match_data['match_time'] ) ) : ?>
                <span class="match-time">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <?php echo esc_html( date_i18n( 'H:i', strtotime( $match_data['match_time'] ) ) ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Output operator details
 * 
 * @since 1.0.0
 * 
 * @param int $operator_id Operator post ID
 */
function oc_operator_details( $operator_id ) {
    $data = oc_get_operator_data( $operator_id );
    $pros = $data['pros'] ? (array) $data['pros'] : array();
    $cons = $data['cons'] ? (array) $data['cons'] : array();
    ?>
    <div class="oc-operator-details-full">
        <?php if ( $data['license'] ) : ?>
            <div class="detail-row">
                <span class="detail-label"><?php esc_html_e( 'License', 'odds-comparison' ); ?></span>
                <span class="detail-value"><?php echo esc_html( $data['license'] ); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ( $data['min_deposit'] ) : ?>
            <div class="detail-row">
                <span class="detail-label"><?php esc_html_e( 'Min. Deposit', 'odds-comparison' ); ?></span>
                <span class="detail-value"><?php echo esc_html( $data['min_deposit'] ); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ( $data['min_bet'] ) : ?>
            <div class="detail-row">
                <span class="detail-label"><?php esc_html_e( 'Min. Bet', 'odds-comparison' ); ?></span>
                <span class="detail-value"><?php echo esc_html( $data['min_bet'] ); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ( $data['payment_methods'] ) : ?>
            <div class="detail-row">
                <span class="detail-label"><?php esc_html_e( 'Payment Methods', 'odds-comparison' ); ?></span>
                <span class="detail-value"><?php echo esc_html( $data['payment_methods'] ); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ( ! empty( $pros ) || ! empty( $cons ) ) : ?>
            <div class="pros-cons-section">
                <?php if ( ! empty( $pros ) ) : ?>
                    <div class="pros">
                        <h4><?php esc_html_e( 'Pros', 'odds-comparison' ); ?></h4>
                        <ul>
                            <?php foreach ( $pros as $pro ) : ?>
                                <li><?php echo esc_html( $pro ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $cons ) ) : ?>
                    <div class="cons">
                        <h4><?php esc_html_e( 'Cons', 'odds-comparison' ); ?></h4>
                        <ul>
                            <?php foreach ( $cons as $con ) : ?>
                                <li><?php echo esc_html( $con ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Output social share buttons
 * 
 * @since 1.0.0
 * 
 * @param string $title Share title
 * @param string $url   Share URL
 */
function oc_social_share( $title = '', $url = '' ) {
    if ( ! $title ) {
        $title = urlencode( get_the_title() );
    }
    if ( ! $url ) {
        $url = urlencode( get_permalink() );
    }
    
    $twitter_url = 'https://twitter.com/intent/tweet?text=' . $title . '&url=' . $url;
    $facebook_url = 'https://www.facebook.com/sharer/sharer.php?u=' . $url;
    $linkedin_url = 'https://www.linkedin.com/shareArticle?mini=true&url=' . $url . '&title=' . $title;
    ?>
    <div class="oc-social-share" role="region" aria-label="<?php esc_attr_e( 'Share this content', 'odds-comparison' ); ?>">
        <span class="share-label"><?php esc_html_e( 'Share:', 'odds-comparison' ); ?></span>
        <a href="<?php echo esc_url( $twitter_url ); ?>" class="share-btn share-twitter" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on Twitter', 'odds-comparison' ); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
            </svg>
        </a>
        <a href="<?php echo esc_url( $facebook_url ); ?>" class="share-btn share-facebook" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on Facebook', 'odds-comparison' ); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
        </a>
        <a href="<?php echo esc_url( $linkedin_url ); ?>" class="share-btn share-linkedin" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on LinkedIn', 'odds-comparison' ); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
            </svg>
        </a>
    </div>
    <?php
}

/**
 * Output the disclaimer
 * 
 * @since 1.0.0
 */
function oc_disclaimer() {
    ?>
    <div class="oc-site-disclaimer" role="contentinfo">
        <div class="container">
            <div class="disclaimer-content">
                <p class="disclaimer-warning">
                    <strong><?php esc_html_e( 'Important:', 'odds-comparison' ); ?></strong>
                    <?php esc_html_e( 'Gambling involves risk. Only gamble with money you can afford to lose.', 'odds-comparison' ); ?>
                </p>
                <p>
                    <?php esc_html_e( 'This website is for informational purposes only and does not offer gambling services. We promote responsible gambling. You must be 18+ to gamble.', 'odds-comparison' ); ?>
                </p>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Output the 404 page content
 * 
 * @since 1.0.0
 */
function oc_404_content() {
    ?>
    <div class="oc-404-content">
        <div class="error-code">404</div>
        <h1><?php esc_html_e( 'Page Not Found', 'odds-comparison' ); ?></h1>
        <p><?php esc_html_e( 'Sorry, the page you are looking for does not exist or has been moved.', 'odds-comparison' ); ?></p>
        
        <div class="oc-404-search">
            <h2><?php esc_html_e( 'Search the site', 'odds-comparison' ); ?></h2>
            <?php get_search_form(); ?>
        </div>
        
        <div class="oc-404-suggestions">
            <h2><?php esc_html_e( 'Popular Sections', 'odds-comparison' ); ?></h2>
            <nav aria-label="<?php esc_attr_e( 'Popular sections', 'odds-comparison' ); ?>">
                <ul>
                    <li><a href="<?php echo esc_url( get_post_type_archive_link( 'match' ) ); ?>"><?php esc_html_e( 'All Matches', 'odds-comparison' ); ?></a></li>
                    <li><a href="<?php echo esc_url( get_post_type_archive_link( 'operator' ) ); ?>"><?php esc_html_e( 'Betting Operators', 'odds-comparison' ); ?></a></li>
                    <li><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'Blog', 'odds-comparison' ); ?></a></li>
                </ul>
            </nav>
        </div>
        
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="oc-btn oc-btn-primary">
            <?php esc_html_e( 'Return to Homepage', 'odds-comparison' ); ?>
        </a>
    </div>
    <?php
}

/**
 * Get the matches archive URL
 * 
 * @since 1.0.0
 * 
 * @return string
 */
function oc_get_matches_archive_url() {
    $page = get_page_by_path( 'matches' );
    if ( $page ) {
        return get_permalink( $page->ID );
    }
    return get_post_type_archive_link( 'match' );
}

/**
 * Get the operators archive URL
 * 
 * @since 1.0.0
 * 
 * @return string
 */
function oc_get_operators_archive_url() {
    $page = get_page_by_path( 'operators' );
    if ( $page ) {
        return get_permalink( $page->ID );
    }
    return get_post_type_archive_link( 'operator' );
}

