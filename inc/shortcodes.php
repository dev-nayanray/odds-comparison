<?php
/**
 * Shortcodes
 *
 * Custom shortcodes for odds comparison.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * [oc_live_matches] shortcode
 *
 * Display a list of live/upcoming matches with filtering and auto-refresh.
 *
 * @since 1.0.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function oc_live_matches_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'sport'         => '',
        'league'        => '',
        'limit'         => 20,
        'show_filters'  => 'yes',
        'show_date'     => 'yes',
        'show_sport'    => 'yes',
        'show_league'   => 'yes',
        'auto_refresh'  => '60', // seconds, set to 0 or 'false' to disable
        'date_filter'   => 'all', // today, tomorrow, upcoming, all
        'layout'        => 'list',
    ), $atts );
    
    // Generate unique ID for this instance
    $instance_id = 'oc-live-matches-' . uniqid();
    
    // Get sports for filter dropdown
    $sports = get_terms( array(
        'taxonomy'   => 'sport',
        'hide_empty' => true,
    ) );
    
    // Get leagues for filter dropdown (filtered by sport if selected)
    $league_args = array(
        'taxonomy'   => 'league',
        'hide_empty' => true,
    );
    
    if ( $atts['sport'] ) {
        $league_args['meta_query'] = array(
            array(
                'key'     => 'oc_sport_taxonomy',
                'value'   => $atts['sport'],
            ),
        );
    }
    
    $leagues = get_terms( $league_args );
    
    // Start output
    ob_start();
    ?>
    <div id="<?php echo esc_attr( $instance_id ); ?>" 
         class="oc-live-matches-list"
         data-limit="<?php echo esc_attr( $atts['limit'] ); ?>"
         data-auto-refresh="<?php echo esc_attr( $atts['auto_refresh'] ); ?>"
         data-nonce="<?php echo esc_attr( wp_create_nonce( 'oc_ajax_nonce' ) ); ?>">
        
        <?php if ( 'yes' === $atts['show_filters'] ) : ?>
            <div class="oc-filters">
                <?php if ( 'yes' === $atts['show_date'] ) : ?>
                    <div class="oc-filter-group">
                        <label for="<?php echo esc_attr( $instance_id ); ?>-date"><?php esc_html_e( 'When', 'odds-comparison' ); ?></label>
                        <select id="<?php echo esc_attr( $instance_id ); ?>-date" class="oc-filter-select oc-filter-date">
                            <option value="all" <?php selected( $atts['date_filter'], 'all' ); ?>><?php esc_html_e( 'All Matches', 'odds-comparison' ); ?></option>
                            <option value="today" <?php selected( $atts['date_filter'], 'today' ); ?>><?php esc_html_e( 'Today', 'odds-comparison' ); ?></option>
                            <option value="tomorrow" <?php selected( $atts['date_filter'], 'tomorrow' ); ?>><?php esc_html_e( 'Tomorrow', 'odds-comparison' ); ?></option>
                            <option value="upcoming" <?php selected( $atts['date_filter'], 'upcoming' ); ?>><?php esc_html_e( 'Upcoming', 'odds-comparison' ); ?></option>
                        </select>
                    </div>
                <?php endif; ?>
                
                <?php if ( 'yes' === $atts['show_sport'] && ! is_wp_error( $sports ) && ! empty( $sports ) ) : ?>
                    <div class="oc-filter-group">
                        <label for="<?php echo esc_attr( $instance_id ); ?>-sport"><?php esc_html_e( 'Sport', 'odds-comparison' ); ?></label>
                        <select id="<?php echo esc_attr( $instance_id ); ?>-sport" class="oc-filter-select oc-filter-sport" data-current="<?php echo esc_attr( $atts['sport'] ); ?>">
                            <option value=""><?php esc_html_e( 'All Sports', 'odds-comparison' ); ?></option>
                            <?php foreach ( $sports as $sport ) : ?>
                                <option value="<?php echo esc_attr( $sport->slug ); ?>" <?php selected( $atts['sport'], $sport->slug ); ?>>
                                    <?php echo esc_html( $sport->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <?php if ( 'yes' === $atts['show_league'] && ! is_wp_error( $leagues ) && ! empty( $leagues ) ) : ?>
                    <div class="oc-filter-group">
                        <label for="<?php echo esc_attr( $instance_id ); ?>-league"><?php esc_html_e( 'League', 'odds-comparison' ); ?></label>
                        <select id="<?php echo esc_attr( $instance_id ); ?>-league" class="oc-filter-select oc-filter-league" data-current="<?php echo esc_attr( $atts['league'] ); ?>">
                            <option value=""><?php esc_html_e( 'All Leagues', 'odds-comparison' ); ?></option>
                            <?php foreach ( $leagues as $league ) : ?>
                                <option value="<?php echo esc_attr( $league->slug ); ?>" <?php selected( $atts['league'], $league->slug ); ?>>
                                    <?php echo esc_html( $league->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <button type="button" class="oc-refresh-btn" aria-label="<?php esc_attr_e( 'Refresh odds', 'odds-comparison' ); ?>">
                    <span class="dashicons dashicons-update"></span>
                    <span class="oc-btn-text"><?php esc_html_e( 'Refresh', 'odds-comparison' ); ?></span>
                </button>
            </div>
        <?php endif; ?>
        
        <div class="oc-matches-container">
            <div class="oc-loading">
                <div class="spinner"></div>
                <p><?php esc_html_e( 'Loading matches...', 'odds-comparison' ); ?></p>
            </div>
        </div>
        
        <p class="oc-last-updated" style="display: none;"></p>
        
        <p class="oc-disclaimer">
            <?php esc_html_e( 'Odds subject to change. Please gamble responsibly.', 'odds-comparison' ); ?>
        </p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Initialize the live matches container
        var $container = $('#<?php echo esc_js( $instance_id ); ?>');
        
        // Trigger initial load
        if (typeof loadLiveMatches === 'function') {
            loadLiveMatches($container);
        }
        
        // Setup filter handlers
        if (typeof setupLiveMatchesFilters === 'function') {
            setupLiveMatchesFilters($container);
        }
        
        // Setup refresh button
        if (typeof setupRefreshButton === 'function') {
            setupRefreshButton($container);
        }
        
        // Start auto-refresh
        if (typeof startAutoRefresh === 'function') {
            startAutoRefresh($container);
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'oc_live_matches', 'oc_live_matches_shortcode' );

/**
 * [oc_odds] shortcode
 *
 * Display odds comparison for a specific match.
 *
 * @since 1.0.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function oc_odds_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'match_id'     => '',
        'show_header'  => 'yes',
        'show_filters' => 'yes',
        'layout'       => 'table',
        'operator_ids' => '',
    ), $atts );
    
    if ( ! $atts['match_id'] ) {
        return '<p class="oc-error">' . esc_html__( 'Please specify a match ID.', 'odds-comparison' ) . '</p>';
    }
    
    $match_id = absint( $atts['match_id'] );
    $match = get_post( $match_id );
    
    if ( ! $match || 'match' !== $match->post_type ) {
        return '<p class="oc-error">' . esc_html__( 'Match not found.', 'odds-comparison' ) . '</p>';
    }
    
    $operator_ids = $atts['operator_ids'] ? array_map( 'absint', explode( ',', $atts['operator_ids'] ) ) : array();
    $odds = oc_get_match_odds( $match_id, $operator_ids );
    $best_odds = oc_get_best_odds( $odds );
    
    $home_team = get_post_meta( $match_id, 'oc_home_team', true );
    $away_team = get_post_meta( $match_id, 'oc_away_team', true );
    
    ob_start();
    ?>
    <div class="oc-odds-shortcode" data-match-id="<?php echo esc_attr( $match_id ); ?>">
        <?php if ( 'yes' === $atts['show_header'] ) : ?>
            <div class="oc-odds-header">
                <h3 class="oc-match-title">
                    <?php echo esc_html( $home_team . ' vs ' . $away_team ); ?>
                </h3>
            </div>
        <?php endif; ?>
        
        <?php if ( 'yes' === $atts['show_filters'] && count( $odds ) > 1 ) : ?>
            <div class="oc-odds-filters">
                <select class="oc-sort-select">
                    <option value="rating"><?php esc_html_e( 'Sort by Rating', 'odds-comparison' ); ?></option>
                    <option value="odds_high"><?php esc_html_e( 'Highest Odds', 'odds-comparison' ); ?></option>
                    <option value="odds_low"><?php esc_html_e( 'Lowest Odds', 'odds-comparison' ); ?></option>
                </select>
            </div>
        <?php endif; ?>
        
        <div class="oc-odds-container <?php echo 'table' === $atts['layout'] ? 'oc-layout-table' : 'oc-layout-list'; ?>">
            <div class="oc-odds-row oc-header-row">
                <div class="oc-bookmaker-cell"><?php esc_html_e( 'Bookmaker', 'odds-comparison' ); ?></div>
                <div class="oc-odds-cell"><?php esc_html_e( '1', 'odds-comparison' ); ?></div>
                <div class="oc-odds-cell"><?php esc_html_e( 'X', 'odds-comparison' ); ?></div>
                <div class="oc-odds-cell"><?php esc_html_e( '2', 'odds-comparison' ); ?></div>
                <div class="oc-action-cell"></div>
            </div>
            
            <?php foreach ( $odds as $odd ) : ?>
                <?php $operator = get_post( $odd['bookmaker_id'] ); ?>
                <?php if ( ! $operator ) continue; ?>
                
                <?php
                $affiliate_url = get_post_meta( $operator->ID, 'oc_affiliate_url', true );
                $rating = get_post_meta( $operator->ID, 'oc_operator_rating', true );
                $bonus = get_post_meta( $operator->ID, 'oc_bonus_amount', true );
                $is_best_home = $best_odds['home'] && $best_odds['home']['id'] === $odd['id'];
                $is_best_draw = $best_odds['draw'] && $best_odds['draw']['id'] === $odd['id'];
                $is_best_away = $best_odds['away'] && $best_odds['away']['id'] === $odd['id'];
                ?>
                
                <div class="oc-odds-row" data-rating="<?php echo esc_attr( $rating ); ?>">
                    <div class="oc-bookmaker-cell">
                        <div class="oc-bookmaker-info">
                            <div class="oc-logo">
                                <?php if ( has_post_thumbnail( $operator->ID ) ) : ?>
                                    <?php echo get_the_post_thumbnail( $operator->ID, 'thumbnail' ); ?>
                                <?php else : ?>
                                    <span><?php echo esc_html( $operator->post_title ); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="oc-details">
                                <span class="oc-name"><?php echo esc_html( $operator->post_title ); ?></span>
                                <?php if ( $rating ) : ?>
                                    <span class="oc-rating"><?php echo esc_html( number_format( $rating, 1 ) ); ?> ★</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="oc-odds-cell <?php echo $is_best_home ? 'oc-best' : ''; ?>">
                        <?php if ( $odd['odds_home'] > 0 ) : ?>
                            <a href="<?php echo esc_url( add_query_arg( 'oc_match', $match_id, $affiliate_url ) ); ?>" target="_blank" rel="nofollow" class="oc-odds-btn">
                                <?php echo esc_html( number_format( $odd['odds_home'], 2 ) ); ?>
                            </a>
                            <?php if ( $is_best_home ) : ?>
                                <span class="oc-best-badge"><?php esc_html_e( 'Best', 'odds-comparison' ); ?></span>
                            <?php endif; ?>
                        <?php else : ?>
                            <span class="oc-na">-</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="oc-odds-cell <?php echo $is_best_draw ? 'oc-best' : ''; ?>">
                        <?php if ( $odd['odds_draw'] > 0 ) : ?>
                            <a href="<?php echo esc_url( add_query_arg( 'oc_match', $match_id, $affiliate_url ) ); ?>" target="_blank" rel="nofollow" class="oc-odds-btn">
                                <?php echo esc_html( number_format( $odd['odds_draw'], 2 ) ); ?>
                            </a>
                            <?php if ( $is_best_draw ) : ?>
                                <span class="oc-best-badge"><?php esc_html_e( 'Best', 'odds-comparison' ); ?></span>
                            <?php endif; ?>
                        <?php else : ?>
                            <span class="oc-na">-</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="oc-odds-cell <?php echo $is_best_away ? 'oc-best' : ''; ?>">
                        <?php if ( $odd['odds_away'] > 0 ) : ?>
                            <a href="<?php echo esc_url( add_query_arg( 'oc_match', $match_id, $affiliate_url ) ); ?>" target="_blank" rel="nofollow" class="oc-odds-btn">
                                <?php echo esc_html( number_format( $odd['odds_away'], 2 ) ); ?>
                            </a>
                            <?php if ( $is_best_away ) : ?>
                                <span class="oc-best-badge"><?php esc_html_e( 'Best', 'odds-comparison' ); ?></span>
                            <?php endif; ?>
                        <?php else : ?>
                            <span class="oc-na">-</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="oc-action-cell">
                        <a href="<?php echo esc_url( $affiliate_url ); ?>" class="button oc-visit" target="_blank" rel="nofollow">
                            <?php esc_html_e( 'Visit', 'odds-comparison' ); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if ( empty( $odds ) ) : ?>
                <div class="oc-no-odds">
                    <p><?php esc_html_e( 'No odds available for this match.', 'odds-comparison' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="oc-odds-footer">
            <p class="oc-disclaimer">
                <?php esc_html_e( 'Odds subject to change. Please gamble responsibly.', 'odds-comparison' ); ?>
            </p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'oc_odds', 'oc_odds_shortcode' );

/**
 * [oc_matches] shortcode
 *
 * Display list of upcoming matches.
 *
 * @since 1.0.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function oc_matches_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'sport'       => '',
        'league'      => '',
        'limit'       => 6,
        'show_past'   => 'no',
        'layout'      => 'grid',
        'featured'    => 'no',
        'live'        => 'no',
    ), $atts );
    
    $args = array(
        'post_type'      => 'match',
        'post_status'    => 'publish',
        'posts_per_page' => absint( $atts['limit'] ),
    );
    
    if ( $atts['sport'] ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'sport',
            'field'    => 'slug',
            'terms'    => $atts['sport'],
        );
    }
    
    if ( $atts['league'] ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'league',
            'field'    => 'slug',
            'terms'    => $atts['league'],
        );
    }
    
    if ( 'yes' === $atts['featured'] ) {
        $args['meta_query'][] = array(
            'key'   => 'oc_featured_match',
            'value' => '1',
        );
    }
    
    if ( 'yes' === $atts['live'] ) {
        $args['meta_query'][] = array(
            'key'   => 'oc_live_match',
            'value' => '1',
        );
    }
    
    $args['orderby'] = 'date';
    $args['order'] = 'DESC';
    
    $query = new WP_Query( $args );
    
    ob_start();
    ?>
    <div class="oc-matches-shortcode oc-layout-<?php echo esc_attr( $atts['layout'] ); ?>">
        <?php if ( $query->have_posts() ) : ?>
            <div class="oc-matches-grid">
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                    <?php
                    $match_id = get_the_ID();
                    $odds = oc_get_match_odds( $match_id );
                    $best_odds = oc_get_best_odds( $odds );
                    
                    $home_team = get_post_meta( $match_id, 'oc_home_team', true );
                    $away_team = get_post_meta( $match_id, 'oc_away_team', true );
                    $match_date = get_post_meta( $match_id, 'oc_match_date', true );
                    $match_time = get_post_meta( $match_id, 'oc_match_time', true );
                    $is_live = get_post_meta( $match_id, 'oc_live_match', true );
                    $is_featured = get_post_meta( $match_id, 'oc_featured_match', true );
                    ?>
                    
                    <article class="oc-match-item">
                        <?php if ( $is_featured ) : ?>
                            <div class="oc-badge oc-featured"><?php esc_html_e( 'Featured', 'odds-comparison' ); ?></div>
                        <?php endif; ?>
                        <?php if ( $is_live ) : ?>
                            <div class="oc-badge oc-live"><?php esc_html_e( 'LIVE', 'odds-comparison' ); ?></div>
                        <?php endif; ?>
                        
                        <div class="oc-match-info">
                            <div class="oc-team oc-home">
                                <span class="oc-name"><?php echo esc_html( $home_team ); ?></span>
                            </div>
                            <div class="oc-vs">
                                <span>vs</span>
                                <?php if ( $match_date ) : ?>
                                    <span class="oc-date"><?php echo esc_html( date_i18n( 'd.m', strtotime( $match_date ) ) ); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="oc-team oc-away">
                                <span class="oc-name"><?php echo esc_html( $away_team ); ?></span>
                            </div>
                        </div>
                        
                        <div class="oc-mini-odds">
                            <?php if ( $best_odds['home'] ) : ?>
                                <span class="oc-odd"><?php echo esc_html( number_format( $best_odds['home']['odds'], 2 ) ); ?></span>
                            <?php endif; ?>
                            <?php if ( $best_odds['draw'] ) : ?>
                                <span class="oc-odd"><?php echo esc_html( number_format( $best_odds['draw']['odds'], 2 ) ); ?></span>
                            <?php endif; ?>
                            <?php if ( $best_odds['away'] ) : ?>
                                <span class="oc-odd"><?php echo esc_html( number_format( $best_odds['away']['odds'], 2 ) ); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <a href="<?php the_permalink(); ?>" class="oc-link">
                            <?php esc_html_e( 'Compare', 'odds-comparison' ); ?>
                        </a>
                    </article>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        <?php else : ?>
            <p class="oc-no-matches"><?php esc_html_e( 'No matches found.', 'odds-comparison' ); ?></p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'oc_matches', 'oc_matches_shortcode' );

/**
 * [oc_operators] shortcode
 *
 * Display list of betting operators.
 *
 * @since 1.0.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function oc_operators_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'limit'     => 4,
        'license'   => '',
        'featured'  => 'no',
        'layout'    => 'list',
        'orderby'   => 'rating',
    ), $atts );
    
    $args = array(
        'post_type'      => 'operator',
        'post_status'    => 'publish',
        'posts_per_page' => absint( $atts['limit'] ),
        'meta_key'       => 'oc_operator_rating',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    );
    
    if ( $atts['license'] ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'license',
            'field'    => 'slug',
            'terms'    => $atts['license'],
        );
    }
    
    if ( 'yes' === $atts['featured'] ) {
        $args['meta_query'][] = array(
            'key'   => 'oc_featured_operator',
            'value' => '1',
        );
    }
    
    $query = new WP_Query( $args );
    
    ob_start();
    ?>
    <div class="oc-operators-shortcode oc-layout-<?php echo esc_attr( $atts['layout'] ); ?>">
        <?php if ( $query->have_posts() ) : ?>
            <div class="oc-operators-grid">
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                    <?php
                    $operator_id = get_the_ID();
                    $rating = get_post_meta( $operator_id, 'oc_operator_rating', true );
                    $bonus_amount = get_post_meta( $operator_id, 'oc_bonus_amount', true );
                    $bonus_type = get_post_meta( $operator_id, 'oc_bonus_type', true );
                    $affiliate_url = get_post_meta( $operator_id, 'oc_affiliate_url', true );
                    $is_featured = get_post_meta( $operator_id, 'oc_featured_operator', true );
                    ?>
                    
                    <article class="oc-operator-item <?php echo $is_featured ? 'featured' : ''; ?>">
                        <div class="oc-logo">
                            <?php if ( has_post_thumbnail( $operator_id ) ) : ?>
                                <?php the_post_thumbnail( 'thumbnail' ); ?>
                            <?php else : ?>
                                <span><?php echo esc_html( get_the_title()[0] ); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="oc-info">
                            <h3 class="oc-name">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            
                            <?php if ( $rating ) : ?>
                                <div class="oc-rating">
                                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                        <span class="star <?php echo $i <= round( $rating ) ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                    <span class="oc-rating-value"><?php echo esc_html( number_format( $rating, 1 ) ); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ( $bonus_amount ) : ?>
                                <div class="oc-bonus">
                                    <span class="oc-amount"><?php echo esc_html( $bonus_amount ); ?></span>
                                    <?php if ( $bonus_type ) : ?>
                                        <span class="oc-type"><?php echo esc_html( ucfirst( $bonus_type ) ); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="oc-actions">
                            <?php if ( $affiliate_url ) : ?>
                                <a href="<?php echo esc_url( $affiliate_url ); ?>" class="button oc-visit" target="_blank" rel="nofollow">
                                    <?php esc_html_e( 'Visit', 'odds-comparison' ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        <?php else : ?>
            <p class="oc-no-operators"><?php esc_html_e( 'No operators found.', 'odds-comparison' ); ?></p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'oc_operators', 'oc_operators_shortcode' );

/**
 * [oc_best_odds] shortcode
 *
 * Display only the best odds for a match.
 *
 * @since 1.0.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function oc_best_odds_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'match_id' => '',
        'show_all' => 'no',
    ), $atts );
    
    if ( ! $atts['match_id'] ) {
        return '';
    }
    
    $match_id = absint( $atts['match_id'] );
    $odds = oc_get_match_odds( $match_id );
    $best_odds = oc_get_best_odds( $odds );
    
    $home_team = get_post_meta( $match_id, 'oc_home_team', true );
    $away_team = get_post_meta( $match_id, 'oc_away_team', true );
    
    ob_start();
    ?>
    <div class="oc-best-odds-shortcode">
        <div class="oc-teams">
            <span class="oc-home-team"><?php echo esc_html( $home_team ); ?></span>
            <span class="oc-vs">vs</span>
            <span class="oc-away-team"><?php echo esc_html( $away_team ); ?></span>
        </div>
        
        <div class="oc-best-odds">
            <div class="oc-odd-item">
                <span class="oc-label"><?php esc_html_e( '1', 'odds-comparison' ); ?></span>
                <?php if ( $best_odds['home'] ) : ?>
                    <span class="oc-value"><?php echo esc_html( number_format( $best_odds['home']['odds'], 2 ) ); ?></span>
                    <span class="oc-bookmaker"><?php echo esc_html( get_the_title( $best_odds['home']['bookmaker_id'] ) ); ?></span>
                <?php else : ?>
                    <span class="oc-value na">-</span>
                <?php endif; ?>
            </div>
            
            <div class="oc-odd-item">
                <span class="oc-label"><?php esc_html_e( 'X', 'odds-comparison' ); ?></span>
                <?php if ( $best_odds['draw'] ) : ?>
                    <span class="oc-value"><?php echo esc_html( number_format( $best_odds['draw']['odds'], 2 ) ); ?></span>
                    <span class="oc-bookmaker"><?php echo esc_html( get_the_title( $best_odds['draw']['bookmaker_id'] ) ); ?></span>
                <?php else : ?>
                    <span class="oc-value na">-</span>
                <?php endif; ?>
            </div>
            
            <div class="oc-odd-item">
                <span class="oc-label"><?php esc_html_e( '2', 'odds-comparison' ); ?></span>
                <?php if ( $best_odds['away'] ) : ?>
                    <span class="oc-value"><?php echo esc_html( number_format( $best_odds['away']['odds'], 2 ) ); ?></span>
                    <span class="oc-bookmaker"><?php echo esc_html( get_the_title( $best_odds['away']['bookmaker_id'] ) ); ?></span>
                <?php else : ?>
                    <span class="oc-value na">-</span>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ( 'yes' === $atts['show_all'] ) : ?>
            <a href="<?php echo esc_url( get_permalink( $match_id ) ); ?>" class="oc-view-all">
                <?php esc_html_e( 'View All Odds', 'odds-comparison' ); ?>
            </a>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'oc_best_odds', 'oc_best_odds_shortcode' );

/**
 * [oc_comparison_tool] shortcode
 *
 * Display the operator comparison tool with filtering and sorting options.
 *
 * @since 1.0.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function oc_comparison_tool_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'limit'          => 12,
        'license'        => '',
        'sport'          => '',
        'featured'       => 'no',
        'show_filters'   => 'yes',
        'show_pros'      => 'yes',
        'per_page'       => 12,
    ), $atts );

    // Generate unique ID for this instance
    $instance_id = 'oc-comparison-' . uniqid();

    // Get licenses for filter dropdown
    $licenses = get_terms( array(
        'taxonomy'   => 'license',
        'hide_empty' => true,
    ) );

    // Get sports for filter dropdown
    $sports = get_terms( array(
        'taxonomy'   => 'sport',
        'hide_empty' => true,
    ) );

    // Get operators
    $operators = oc_get_comparison_operators( array(
        'limit'     => absint( $atts['limit'] ),
        'license'   => $atts['license'],
        'sport'     => $atts['sport'],
        'featured'  => $atts['featured'],
    ) );

    ob_start();
    ?>
    <div id="<?php echo esc_attr( $instance_id ); ?>" 
         class="oc-comparison-tool"
         data-limit="<?php echo esc_attr( $atts['limit'] ); ?>"
         data-per-page="<?php echo esc_attr( $atts['per_page'] ); ?>"
         data-show-pros="<?php echo esc_attr( $atts['show_pros'] ); ?>"
         data-nonce="<?php echo esc_attr( wp_create_nonce( 'oc_ajax_nonce' ) ); ?>">
        
        <div class="oc-comparison-header">
            <h2 class="oc-comparison-title"><?php esc_html_e( 'Compare Betting Operators', 'odds-comparison' ); ?></h2>
            <p class="oc-comparison-description">
                <?php esc_html_e( 'Find the best betting sites with competitive odds, generous bonuses, and reliable service. Compare features, ratings, and more.', 'odds-comparison' ); ?>
            </p>
        </div>
        
        <div class="oc-comparison-layout">
            <!-- Sidebar Filters -->
            <?php if ( 'yes' === $atts['show_filters'] ) : ?>
                <aside class="oc-comparison-sidebar">
                    <form class="oc-filter-form" data-instance="<?php echo esc_attr( $instance_id ); ?>">
                        <h3 class="oc-filter-heading"><?php esc_html_e( 'Filter Operators', 'odds-comparison' ); ?></h3>
                        
                        <?php if ( ! is_wp_error( $licenses ) && ! empty( $licenses ) ) : ?>
                            <div class="oc-filter-section">
                                <label for="<?php echo esc_attr( $instance_id ); ?>-license" class="oc-filter-label">
                                    <?php esc_html_e( 'License', 'odds-comparison' ); ?>
                                </label>
                                <select id="<?php echo esc_attr( $instance_id ); ?>-license" 
                                        name="license" 
                                        class="oc-filter-select"
                                        data-current="<?php echo esc_attr( $atts['license'] ); ?>">
                                    <option value=""><?php esc_html_e( 'All Licenses', 'odds-comparison' ); ?></option>
                                    <?php foreach ( $licenses as $license ) : ?>
                                        <option value="<?php echo esc_attr( $license->slug ); ?>" <?php selected( $atts['license'], $license->slug ); ?>>
                                            <?php echo esc_html( $license->name ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( ! is_wp_error( $sports ) && ! empty( $sports ) ) : ?>
                            <div class="oc-filter-section">
                                <label for="<?php echo esc_attr( $instance_id ); ?>-sport" class="oc-filter-label">
                                    <?php esc_html_e( 'Sport Focus', 'odds-comparison' ); ?>
                                </label>
                                <select id="<?php echo esc_attr( $instance_id ); ?>-sport" 
                                        name="sport" 
                                        class="oc-filter-select"
                                        data-current="<?php echo esc_attr( $atts['sport'] ); ?>">
                                    <option value=""><?php esc_html_e( 'All Sports', 'odds-comparison' ); ?></option>
                                    <?php foreach ( $sports as $sport ) : ?>
                                        <option value="<?php echo esc_attr( $sport->slug ); ?>" <?php selected( $atts['sport'], $sport->slug ); ?>>
                                            <?php echo esc_html( $sport->name ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        
                        <div class="oc-filter-section">
                            <label for="<?php echo esc_attr( $instance_id ); ?>-sort" class="oc-filter-label">
                                <?php esc_html_e( 'Sort By', 'odds-comparison' ); ?>
                            </label>
                            <select id="<?php echo esc_attr( $instance_id ); ?>-sort" 
                                    name="sort" 
                                    class="oc-filter-select">
                                <option value="rating"><?php esc_html_e( 'Highest Rated', 'odds-comparison' ); ?></option>
                                <option value="bonus"><?php esc_html_e( 'Bonus Amount', 'odds-comparison' ); ?></option>
                                <option value="newest"><?php esc_html_e( 'Newest First', 'odds-comparison' ); ?></option>
                                <option value="name"><?php esc_html_e( 'Name A-Z', 'odds-comparison' ); ?></option>
                            </select>
                        </div>
                        
                        <div class="oc-filter-actions">
                            <button type="submit" class="button oc-apply-filters-btn">
                                <?php esc_html_e( 'Apply Filters', 'odds-comparison' ); ?>
                            </button>
                            <a href="#" class="button oc-reset-filters-btn" onclick="return false;">
                                <?php esc_html_e( 'Reset Filters', 'odds-comparison' ); ?>
                            </a>
                        </div>
                    </form>
                    
                    <div class="oc-quick-stats">
                        <h4><?php esc_html_e( 'Showing', 'odds-comparison' ); ?></h4>
                        <p>
                            <strong id="<?php echo esc_attr( $instance_id ); ?>-count"><?php echo count( $operators ); ?></strong> 
                            <?php esc_html_e( 'operators', 'odds-comparison' ); ?>
                        </p>
                    </div>
                </aside>
            <?php endif; ?>
            
            <!-- Main Content -->
            <main class="oc-comparison-content">
                <!-- Active Filters Bar -->
                <div class="oc-active-filters-bar" style="display: none;">
                    <span class="oc-active-label"><?php esc_html_e( 'Active filters:', 'odds-comparison' ); ?></span>
                    <div class="oc-filter-tags"></div>
                </div>
                
                <!-- Operators Grid -->
                <div class="oc-operators-comparison-grid">
                    <?php if ( ! empty( $operators ) ) : ?>
                        <?php foreach ( $operators as $operator ) : ?>
                            <?php echo oc_render_comparison_card( $operator, $atts['show_pros'] ); ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="oc-no-results">
                            <p><?php esc_html_e( 'No operators found matching your criteria.', 'odds-comparison' ); ?></p>
                            <a href="#" class="button oc-reset-search">
                                <?php esc_html_e( 'Clear Filters', 'odds-comparison' ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <nav class="oc-pagination" style="display: none;">
                    <!-- Pagination will be populated by JavaScript -->
                </nav>
                
                <!-- Disclaimer -->
                <p class="oc-disclaimer" style="margin-top: 2rem; font-size: 0.75rem; color: var(--oc-text-light); text-align: center;">
                    <?php esc_html_e( 'Operator ratings and bonuses are subject to change. Please gamble responsibly. 18+.', 'odds-comparison' ); ?>
                </p>
            </main>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Initialize comparison tool
        var $container = $('#<?php echo esc_js( $instance_id ); ?>');
        
        // Setup filter handlers
        if (typeof ocComparisonToolInit === 'function') {
            ocComparisonToolInit($container);
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'oc_comparison_tool', 'oc_comparison_tool_shortcode' );

/**
 * [oc_premier_league] shortcode
 *
 * Display upcoming Premier League matches with date/day display and betting odds.
 *
 * @since 1.0.0
 *
 * @param array $atts Shortcode attributes
 * @return string
 */
function oc_premier_league_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'days'      => 7,      // Number of days to show matches for
        'limit'     => 10,     // Maximum number of matches to display
        'show_odds' => 'yes',  // Show betting odds
    ), $atts );

    return oc_render_premier_league_section( array(
        'days'      => absint( $atts['days'] ),
        'limit'     => absint( $atts['limit'] ),
        'show_odds' => 'yes' === $atts['show_odds'],
    ) );
}
add_shortcode( 'oc_premier_league', 'oc_premier_league_shortcode' );

