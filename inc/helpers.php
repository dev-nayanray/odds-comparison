<?php
/**
 * Helper Functions
 * 
 * Utility functions for the Odds Comparison theme.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get cached odds data
 * 
 * @since 1.0.0
 * 
 * @param string $key Cache key
 * @return mixed|false Cached data or false
 */
function oc_get_cached_odds( $key ) {
    $cache = wp_cache_get( $key, 'oc_odds' );
    
    if ( false !== $cache ) {
        return $cache;
    }
    
    return false;
}

/**
 * Cache odds data
 * 
 * @since 1.0.0
 * 
 * @param string $key   Cache key
 * @param mixed  $data  Data to cache
 * @param int    $ttl   Time to live in seconds
 * @return bool Success
 */
function oc_cache_odds( $key, $data, $ttl = 300 ) {
    return wp_cache_set( $key, $data, 'oc_odds', $ttl );
}

/* ================================================================
 * AFFILIATE TRACKING FUNCTIONS
 * ================================================================ */

/**
 * Generate or retrieve unique tracking ID for visitor
 * 
 * Creates a unique tracking ID stored in cookie for 30 days.
 * This ID is used to track affiliate clicks and conversions.
 * 
 * @since 1.0.0
 * 
 * @return string Unique tracking ID
 */
function oc_get_tracking_id() {
    // Check if cookie already exists
    if ( isset( $_COOKIE['oc_tid'] ) && ! empty( $_COOKIE['oc_tid'] ) ) {
        return sanitize_text_field( $_COOKIE['oc_tid'] );
    }
    
    // Generate new tracking ID
    $tracking_id = 'oc_' . bin2hex( random_bytes( 16 ) );
    
    // Set cookie for 30 days
    setcookie( 'oc_tid', $tracking_id, time() + ( 30 * DAY_IN_SECONDS ), '/' );
    
    // Also store in session for servers without cookie support
    if ( ! session_id() ) {
        session_start();
    }
    $_SESSION['oc_tid'] = $tracking_id;
    
    return $tracking_id;
}

/**
 * Get affiliate URL with tracking parameters
 * 
 * @since 1.0.0
 * 
 * @param int    $operator_id Operator post ID
 * @param string $outcome    Betting outcome (optional)
 * @param int    $match_id   Match post ID (optional)
 * @return string Tracking affiliate URL
 */
function oc_get_tracked_affiliate_url( $operator_id, $outcome = '', $match_id = 0 ) {
    $affiliate_url = get_post_meta( $operator_id, 'oc_operator_affiliate_url', true );
    
    if ( empty( $affiliate_url ) ) {
        return '#';
    }
    
    // Get tracking ID
    $tracking_id = oc_get_tracking_id();
    
    // Build tracking parameters
    $params = array(
        'oc_tid'       => $tracking_id,
        'oc_operator'  => $operator_id,
        'oc_source'    => 'odds-comparison',
    );
    
    // Add match ID if provided
    if ( $match_id ) {
        $params['oc_match'] = $match_id;
    }
    
    // Add outcome if provided
    if ( $outcome ) {
        $params['oc_outcome'] = sanitize_text_field( $outcome );
    }
    
    // Build URL with query parameters
    $separator = ( strpos( $affiliate_url, '?' ) !== false ) ? '&' : '?';
    $url = $affiliate_url . $separator . http_build_query( $params );
    
    // Record the click
    oc_record_affiliate_click_with_tracking( $operator_id, $url, $tracking_id, $match_id );
    
    return esc_url( $url );
}

/**
 * Record affiliate click with tracking information
 * 
 * @since 1.0.0
 * 
 * @param int    $operator_id  Operator post ID
 * @param string $click_url    Full click URL with tracking
 * @param string $tracking_id  Visitor tracking ID
 * @param int    $match_id     Match post ID (optional)
 * @return int|false Click record ID or false
 */
function oc_record_affiliate_click_with_tracking( $operator_id, $click_url, $tracking_id, $match_id = 0 ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_affiliate_clicks';
    
    $click_data = array(
        'operator_id'  => absint( $operator_id ),
        'page_url'     => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '',
        'click_url'    => esc_url_raw( $click_url ),
        'clicked_at'   => current_time( 'mysql' ),
        'tracking_id'  => sanitize_text_field( $tracking_id ),
        'ip_hash'      => oc_get_ip_hash(),
        'user_agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 500 ) : '',
        'referer'      => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '',
    );
    
    $format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' );
    
    return $wpdb->insert( $table_name, $click_data, $format );
}

/**
 * Get hashed IP for privacy compliance
 * 
 * @since 1.0.0
 * 
 * @return string Hashed IP address
 */
function oc_get_ip_hash() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $salt = defined( 'OC_TRACKING_SALT' ) ? OC_TRACKING_SALT : 'odds_comparison_salt_2024';
    return hash( 'sha256', $ip . $salt );
}

/**
 * Mark conversion by tracking ID
 * 
 * Called when a visitor completes registration or deposit.
 * 
 * @since 1.0.0
 * 
 * @param string $tracking_id Tracking ID from cookie/session
 * @param float  $value       Conversion value (optional)
 * @param string $type        Conversion type (optional)
 * @return bool Success status
 */
function oc_mark_conversion( $tracking_id, $value = 0, $type = 'registration' ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_affiliate_clicks';
    
    // Find clicks with this tracking ID
    $clicks = $wpdb->get_results( $wpdb->prepare(
        "SELECT id, operator_id FROM {$table_name} 
         WHERE tracking_id = %s AND converted = 0 
         ORDER BY clicked_at DESC LIMIT 5",
        sanitize_text_field( $tracking_id )
    ) );
    
    if ( empty( $clicks ) ) {
        return false;
    }
    
    // Mark all as converted (up to 5 recent clicks)
    $success = false;
    foreach ( $clicks as $click ) {
        $result = $wpdb->update(
            $table_name,
            array( 
                'converted'   => 1,
                'conv_type'   => sanitize_text_field( $type ),
                'conv_value'  => floatval( $value ),
                'converted_at'=> current_time( 'mysql' ),
            ),
            array( 'id' => $click->id ),
            array( '%d', '%s', '%f', '%s' ),
            array( '%d' )
        );
        
        if ( $result ) {
            $success = true;
            // Fire action for external integrations
            do_action( 'oc_affiliate_conversion', $click->operator_id, $tracking_id, $type, $value );
        }
    }
    
    return $success;
}

/**
 * Get conversion stats with tracking ID
 * 
 * @since 1.0.0
 * 
 * @param string $tracking_id Tracking ID
 * @param int    $operator_id Optional operator ID to filter
 * @return array Click and conversion data
 */
function oc_get_tracking_stats( $tracking_id, $operator_id = 0 ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_affiliate_clicks';
    
    $sql = "SELECT COUNT(*) as total_clicks, 
                   SUM(converted) as conversions,
                   SUM(CASE WHEN converted = 1 THEN conv_value ELSE 0 END) as total_value
            FROM {$table_name}
            WHERE tracking_id = %s";
    
    $params = array( sanitize_text_field( $tracking_id ) );
    
    if ( $operator_id ) {
        $sql .= " AND operator_id = %d";
        $params[] = absint( $operator_id );
    }
    
    $result = $wpdb->get_row( $wpdb->prepare( $sql, $params ), ARRAY_A );
    
    return array(
        'clicks'      => (int) ( $result['total_clicks'] ?? 0 ),
        'conversions' => (int) ( $result['conversions'] ?? 0 ),
        'value'       => (float) ( $result['total_value'] ?? 0 ),
        'conv_rate'   => $result['total_clicks'] > 0 
            ? round( ( $result['conversions'] / $result['total_clicks'] ) * 100, 2 ) 
            : 0,
    );
}

/**
 * Generate operator comparison link with tracking
 * 
 * @since 1.0.0
 * 
 * @param int $operator_id Operator post ID
 * @param int $match_id    Match post ID (optional)
 * @return string Tracked affiliate URL
 */
function oc_get_comparison_affiliate_link( $operator_id, $match_id = 0 ) {
    return oc_get_tracked_affiliate_url( $operator_id, 'comparison', $match_id );
}

/**
 * Track outbound link click
 * 
 * @since 1.0.0
 * 
 * @param string $url  Target URL
 * @param string $type Link type (operator, bonus, etc.)
 * @param int    $id   Related post/term ID
 */
function oc_track_outbound_click( $url, $type = 'operator', $id = 0 ) {
    // Log to console in debug mode
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( sprintf(
            '[OC Tracking] Outbound click: %s - Type: %s - ID: %d',
            $url,
            $type,
            $id
        ) );
    }
    
    // Can be extended to send to analytics
    do_action( 'oc_outbound_click', $url, $type, $id );
}

/**
 * Get match data with all meta
 * 
 * @since 1.0.0
 * 
 * @param int $match_id Match post ID
 * @return array Match data
 */
function oc_get_match_data( $match_id ) {
    $match = get_post( $match_id );
    
    if ( ! $match ) {
        return array();
    }
    
    $data = array(
        'ID'            => $match_id,
        'title'         => $match->post_title,
        'slug'          => $match->post_name,
        'content'       => $match->post_content,
        'home_team'     => get_post_meta( $match_id, 'oc_match_home_team', true ),
        'away_team'     => get_post_meta( $match_id, 'oc_match_away_team', true ),
        'match_date'    => get_post_meta( $match_id, 'oc_match_date', true ),
        'match_time'    => get_post_meta( $match_id, 'oc_match_time', true ),
        'match_status'  => get_post_meta( $match_id, 'oc_match_status', true ),
        'stadium'       => get_post_meta( $match_id, 'oc_match_stadium', true ),
        'league'        => get_post_meta( $match_id, 'oc_match_league', true ),
        'is_live'       => get_post_meta( $match_id, 'oc_live_match', true ),
        'is_featured'   => get_post_meta( $match_id, 'oc_featured_match', true ),
        'permalink'     => get_permalink( $match_id ),
    );
    
    // Get teams from taxonomy
    $teams = get_the_terms( $match_id, 'team' );
    if ( $teams && ! is_wp_error( $teams ) ) {
        $data['teams'] = $teams;
    }
    
    // Get sport from taxonomy
    $sports = get_the_terms( $match_id, 'sport' );
    if ( $sports && ! is_wp_error( $sports ) ) {
        $data['sport'] = $sports[0];
    }
    
    // Get league from taxonomy
    $leagues = get_the_terms( $match_id, 'league' );
    if ( $leagues && ! is_wp_error( $leagues ) ) {
        $data['league_term'] = $leagues[0];
    }
    
    return $data;
}

/**
 * Get best odds for a match
 *
 * @since 1.0.0
 *
 * @param array|int $odds Array of odds data or match ID
 * @return array Best odds
 */
function oc_get_best_odds( $odds = array() ) {
    $best = array(
        'home'   => array( 'odds' => 0, 'bookmaker_id' => 0, 'bookmaker_name' => '' ),
        'draw'   => array( 'odds' => 0, 'bookmaker_id' => 0, 'bookmaker_name' => '' ),
        'away'   => array( 'odds' => 0, 'bookmaker_id' => 0, 'bookmaker_name' => '' ),
    );

    // If $odds is a match ID, fetch the odds data
    if ( is_numeric( $odds ) ) {
        $odds = oc_get_match_odds( absint( $odds ) );
    }

    if ( empty( $odds ) || ! is_array( $odds ) ) {
        return $best;
    }

    foreach ( $odds as $odd ) {
        if ( ! empty( $odd['odds_home'] ) && (float) $odd['odds_home'] > $best['home']['odds'] ) {
            $bookmaker_name = '';
            if ( ! empty( $odd['bookmaker_id'] ) ) {
                $bookmaker = get_post( $odd['bookmaker_id'] );
                $bookmaker_name = $bookmaker ? $bookmaker->post_title : '';
            }
            $best['home'] = array(
                'odds'           => (float) $odd['odds_home'],
                'bookmaker_id'   => $odd['bookmaker_id'],
                'bookmaker_name' => $bookmaker_name,
            );
        }
        if ( ! empty( $odd['odds_draw'] ) && (float) $odd['odds_draw'] > $best['draw']['odds'] ) {
            $bookmaker_name = '';
            if ( ! empty( $odd['bookmaker_id'] ) ) {
                $bookmaker = get_post( $odd['bookmaker_id'] );
                $bookmaker_name = $bookmaker ? $bookmaker->post_title : '';
            }
            $best['draw'] = array(
                'odds'           => (float) $odd['odds_draw'],
                'bookmaker_id'   => $odd['bookmaker_id'],
                'bookmaker_name' => $bookmaker_name,
            );
        }
        if ( ! empty( $odd['odds_away'] ) && (float) $odd['odds_away'] > $best['away']['odds'] ) {
            $bookmaker_name = '';
            if ( ! empty( $odd['bookmaker_id'] ) ) {
                $bookmaker = get_post( $odd['bookmaker_id'] );
                $bookmaker_name = $bookmaker ? $bookmaker->post_title : '';
            }
            $best['away'] = array(
                'odds'           => (float) $odd['odds_away'],
                'bookmaker_id'   => $odd['bookmaker_id'],
                'bookmaker_name' => $bookmaker_name,
            );
        }
    }

    return $best;
}

/**
 * Get match odds from database
 * 
 * @since 1.0.0
 * 
 * @param int   $match_id     Match post ID
 * @param array $operator_ids Optional operator IDs to filter
 * @return array Odds data
 */
function oc_get_match_odds( $match_id, $operator_ids = array() ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_match_odds';
    
    $sql = "SELECT * FROM {$table_name} WHERE match_id = %d";
    $params = array( absint( $match_id ) );
    
    if ( ! empty( $operator_ids ) ) {
        $placeholders = implode( ',', array_fill( 0, count( $operator_ids ), '%d' ) );
        $sql .= " AND bookmaker_id IN ({$placeholders})";
        $params = array_merge( $params, array_map( 'absint', $operator_ids ) );
    }
    
    $sql .= " ORDER BY last_updated DESC";
    
    $results = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
    
    return $results ? $results : array();
}

/**
 * Get live matches
 * 
 * @since 1.0.0
 * 
 * @param int $limit Number of matches to return
 * @return array Live matches
 */
function oc_get_live_matches( $limit = 5 ) {
    $args = array(
        'post_type'      => 'match',
        'post_status'    => 'publish',
        'posts_per_page' => absint( $limit ),
        'meta_key'       => 'oc_live_match',
        'meta_value'     => '1',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    
    $matches = get_posts( $args );
    
    $result = array();
    
    foreach ( $matches as $match ) {
        $result[] = oc_get_match_data( $match->ID );
    }
    
    return $result;
}

/**
 * Get featured operators
 * 
 * @since 1.0.0
 * 
 * @param int $limit Number of operators to return
 * @return array Featured operators
 */
function oc_get_featured_operators( $limit = 4 ) {
    $args = array(
        'post_type'      => 'operator',
        'post_status'    => 'publish',
        'posts_per_page' => absint( $limit ),
        'meta_key'       => 'oc_featured_operator',
        'meta_value'     => '1',
        'meta_key'       => 'oc_operator_rating',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    );
    
    return get_posts( $args );
}

/**
 * Render match card
 * 
 * @since 1.0.0
 * 
 * @param array $match Match data
 */
function oc_render_match_card( $match ) {
    $match_id = is_array( $match ) ? $match['ID'] : $match->ID;
    
    if ( is_array( $match ) ) {
        $home_team = ! empty( $match['home_team'] ) ? $match['home_team'] : '';
        $away_team = ! empty( $match['away_team'] ) ? $match['away_team'] : '';
        $match_date = ! empty( $match['match_date'] ) ? $match['match_date'] : '';
        $match_time = ! empty( $match['match_time'] ) ? $match['match_time'] : '';
        $is_live = ! empty( $match['is_live'] );
        $is_featured = ! empty( $match['is_featured'] );
        $permalink = ! empty( $match['permalink'] ) ? $match['permalink'] : get_permalink( $match_id );
    } else {
        $home_team = get_post_meta( $match_id, 'oc_match_home_team', true );
        $away_team = get_post_meta( $match_id, 'oc_match_away_team', true );
        $match_date = get_post_meta( $match_id, 'oc_match_date', true );
        $match_time = get_post_meta( $match_id, 'oc_match_time', true );
        $is_live = get_post_meta( $match_id, 'oc_live_match', true );
        $is_featured = get_post_meta( $match_id, 'oc_featured_match', true );
        $permalink = get_permalink( $match_id );
    }
    
    // Get best odds
    $odds = oc_get_match_odds( $match_id );
    $best_odds = oc_get_best_odds( $odds );
    ?>
    <article class="oc-match-card <?php echo $is_live ? 'is-live' : ''; ?> <?php echo $is_featured ? 'is-featured' : ''; ?>">
        <?php if ( $is_featured ) : ?>
            <div class="oc-badge oc-featured"><?php esc_html_e( 'Featured', 'odds-comparison' ); ?></div>
        <?php endif; ?>
        
        <?php if ( $is_live ) : ?>
            <div class="oc-badge oc-live"><span class="live-indicator"></span><?php esc_html_e( 'LIVE', 'odds-comparison' ); ?></div>
        <?php endif; ?>
        
        <div class="oc-match-teams">
            <div class="oc-team oc-home">
                <span class="oc-team-name"><?php echo esc_html( $home_team ); ?></span>
            </div>
            
            <div class="oc-match-vs">
                <span class="vs-text"><?php esc_html_e( 'vs', 'odds-comparison' ); ?></span>
                <?php if ( $match_date ) : ?>
                    <span class="match-date">
                        <?php echo esc_html( date_i18n( 'd M', strtotime( $match_date ) ) ); ?>
                    </span>
                <?php endif; ?>
                <?php if ( $match_time ) : ?>
                    <span class="match-time">
                        <?php echo esc_html( date_i18n( 'H:i', strtotime( $match_time ) ) ); ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="oc-team oc-away">
                <span class="oc-team-name"><?php echo esc_html( $away_team ); ?></span>
            </div>
        </div>
        
        <div class="oc-match-odds">
            <?php if ( $best_odds['home']['odds'] > 0 ) : ?>
                <div class="oc-odd oc-odd-home">
                    <span class="odd-value"><?php echo esc_html( number_format( $best_odds['home']['odds'], 2 ) ); ?></span>
                    <span class="odd-label"><?php esc_html_e( '1', 'odds-comparison' ); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ( $best_odds['draw']['odds'] > 0 ) : ?>
                <div class="oc-odd oc-odd-draw">
                    <span class="odd-value"><?php echo esc_html( number_format( $best_odds['draw']['odds'], 2 ) ); ?></span>
                    <span class="odd-label"><?php esc_html_e( 'X', 'odds-comparison' ); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ( $best_odds['away']['odds'] > 0 ) : ?>
                <div class="oc-odd oc-odd-away">
                    <span class="odd-value"><?php echo esc_html( number_format( $best_odds['away']['odds'], 2 ) ); ?></span>
                    <span class="odd-label"><?php esc_html_e( '2', 'odds-comparison' ); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <a href="<?php echo esc_url( $permalink ); ?>" class="oc-match-link">
            <?php esc_html_e( 'Compare Odds', 'odds-comparison' ); ?>
        </a>
    </article>
    <?php
}

/**
 * Render operator card
 * 
 * @since 1.0.0
 * 
 * @param WP_Post $operator Operator post object
 */
function oc_render_operator_card( $operator ) {
    $operator_id = $operator->ID;
    
    $rating = get_post_meta( $operator_id, 'oc_operator_rating', true );
    $bonus_text = get_post_meta( $operator_id, 'oc_operator_bonus_text', true );
    $bonus_value = get_post_meta( $operator_id, 'oc_operator_bonus_value', true );
    $affiliate_url = get_post_meta( $operator_id, 'oc_operator_affiliate_url', true );
    $min_deposit = get_post_meta( $operator_id, 'oc_operator_min_deposit', true );
    $payment_methods = get_post_meta( $operator_id, 'oc_operator_payment_methods', true );
    $is_featured = get_post_meta( $operator_id, 'oc_featured_operator', true );
    $pros = get_post_meta( $operator_id, 'oc_operator_pros', true );
    $cons = get_post_meta( $operator_id, 'oc_operator_cons', true );
    
    // Get logo
    $logo_html = '';
    if ( has_post_thumbnail( $operator_id ) ) {
        $logo_html = get_the_post_thumbnail( $operator_id, 'operator-logo', array( 'alt' => $operator->post_title ) );
    } else {
        $logo_html = '<span class="oc-logo-placeholder">' . esc_html( substr( $operator->post_title, 0, 2 ) ) . '</span>';
    }
    ?>
    <article class="oc-operator-card <?php echo $is_featured ? 'is-featured' : ''; ?>">
        <?php if ( $is_featured ) : ?>
            <div class="oc-badge oc-featured"><?php esc_html_e( 'Featured', 'odds-comparison' ); ?></div>
        <?php endif; ?>
        
        <div class="oc-operator-header">
            <div class="oc-operator-logo">
                <?php echo $logo_html; ?>
            </div>
            
            <div class="oc-operator-info">
                <h3 class="oc-operator-name">
                    <a href="<?php echo esc_url( get_permalink( $operator_id ) ); ?>">
                        <?php echo esc_html( $operator->post_title ); ?>
                    </a>
                </h3>
                
                <?php if ( $rating ) : ?>
                    <div class="oc-operator-rating">
                        <div class="stars" aria-label="<?php echo sprintf( esc_attr__( 'Rating: %s out of 5 stars', 'odds-comparison' ), number_format( $rating, 1 ) ); ?>">
                            <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                <span class="star <?php echo $i <= round( $rating ) ? 'filled' : 'empty'; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-value"><?php echo esc_html( number_format( $rating, 1 ) ); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ( $bonus_text ) : ?>
            <div class="oc-operator-bonus">
                <span class="bonus-label"><?php esc_html_e( 'Welcome Bonus', 'odds-comparison' ); ?></span>
                <span class="bonus-value"><?php echo esc_html( $bonus_text ); ?></span>
            </div>
        <?php endif; ?>
        
        <div class="oc-operator-details">
            <?php if ( $min_deposit ) : ?>
                <div class="detail">
                    <span class="detail-label"><?php esc_html_e( 'Min. Deposit', 'odds-comparison' ); ?></span>
                    <span class="detail-value"><?php echo esc_html( $min_deposit ); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ( $payment_methods ) : ?>
                <div class="detail">
                    <span class="detail-label"><?php esc_html_e( 'Payment', 'odds-comparison' ); ?></span>
                    <span class="detail-value"><?php echo esc_html( $payment_methods ); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ( ! empty( $pros ) && is_array( $pros ) ) : ?>
            <ul class="oc-operator-pros">
                <?php foreach ( array_slice( $pros, 0, 3 ) as $pro ) : ?>
                    <li><?php echo esc_html( $pro ); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <div class="oc-operator-footer">
            <?php if ( $affiliate_url ) : ?>
                <a href="<?php echo esc_url( $affiliate_url ); ?>" class="oc-btn oc-btn-visit" target="_blank" rel="nofollow sponsored">
                    <?php esc_html_e( 'Visit Now', 'odds-comparison' ); ?>
                </a>
            <?php endif; ?>
            <a href="<?php echo esc_url( get_permalink( $operator_id ) ); ?>" class="oc-btn oc-btn-review">
                <?php esc_html_e( 'Read Review', 'odds-comparison' ); ?>
            </a>
        </div>
    </article>
    <?php
}

/**
 * Get operator data
 * 
 * @since 1.0.0
 * 
 * @param int $operator_id Operator post ID
 * @return array Operator data
 */
function oc_get_operator_data( $operator_id ) {
    $operator = get_post( $operator_id );
    
    if ( ! $operator ) {
        return array();
    }
    
    $data = array(
        'ID'            => $operator_id,
        'title'         => $operator->post_title,
        'slug'          => $operator->post_name,
        'content'       => $operator->post_content,
        'license'       => get_post_meta( $operator_id, 'oc_operator_license', true ),
        'rating'        => get_post_meta( $operator_id, 'oc_operator_rating', true ),
        'affiliate_url' => get_post_meta( $operator_id, 'oc_operator_affiliate_url', true ),
        'bonus_text'    => get_post_meta( $operator_id, 'oc_operator_bonus_text', true ),
        'bonus_value'   => get_post_meta( $operator_id, 'oc_operator_bonus_value', true ),
        'bonus_type'    => get_post_meta( $operator_id, 'oc_operator_bonus_type', true ),
        'min_deposit'   => get_post_meta( $operator_id, 'oc_operator_min_deposit', true ),
        'min_bet'       => get_post_meta( $operator_id, 'oc_operator_min_bet', true ),
        'payment_methods' => get_post_meta( $operator_id, 'oc_operator_payment_methods', true ),
        'pros'          => get_post_meta( $operator_id, 'oc_operator_pros', true ),
        'cons'          => get_post_meta( $operator_id, 'oc_operator_cons', true ),
        'is_featured'   => get_post_meta( $operator_id, 'oc_featured_operator', true ),
        'permalink'     => get_permalink( $operator_id ),
    );
    
    // Get license from taxonomy
    $licenses = get_the_terms( $operator_id, 'license' );
    if ( $licenses && ! is_wp_error( $licenses ) ) {
        $data['license_term'] = $licenses[0];
    }
    
    return $data;
}

/**
 * Format odds for display
 * 
 * @since 1.0.0
 * 
 * @param float $odds Odds value
 * @return string Formatted odds
 */
function oc_format_odds( $odds ) {
    return number_format( (float) $odds, 2 );
}

/**
 * Calculate implied probability from odds
 * 
 * @since 1.0.0
 * 
 * @param float $odds Odds value
 * @return float Implied probability (0-1)
 */
function oc_implied_probability( $odds ) {
    if ( empty( $odds ) || (float) $odds <= 0 ) {
        return 0;
    }
    
    return 1 / (float) $odds;
}

/**
 * Calculate value bet
 * 
 * @since 1.0.0
 * 
 * @param float $odds          Odds value
 * @param float $probablity    Your estimated probability (0-1)
 * @return float Value score (positive = good value)
 */
function oc_calculate_value( $odds, $probability ) {
    $implied = oc_implied_probability( $odds );
    
    return $probability - $implied;
}

/**
 * Sanitize odds input
 * 
 * @since 1.0.0
 * 
 * @param string $input Odds input
 * @return float Sanitized odds
 */
function oc_sanitize_odds( $input ) {
    return round( (float) preg_replace( '/[^0-9.]/', '', $input ), 2 );
}

/**
 * Get team data
 * 
 * @since 1.0.0
 * 
 * @param int|WP_Term $team Team ID or term object
 * @return array Team data
 */
function oc_get_team_data( $team ) {
    if ( is_numeric( $team ) ) {
        $team = get_term( $team, 'team' );
    }
    
    if ( ! $team || is_wp_error( $team ) ) {
        return array();
    }
    
    return array(
        'id'         => $team->term_id,
        'name'       => $team->name,
        'slug'       => $team->slug,
        'short_name' => get_term_meta( $team->term_id, 'oc_team_short_name', true ),
        'country'    => get_term_meta( $team->term_id, 'oc_team_country', true ),
        'stadium'    => get_term_meta( $team->term_id, 'oc_team_stadium', true ),
    );
}

/**
 * Get league data
 * 
 * @since 1.0.0
 * 
 * @param int|WP_Term $league League ID or term object
 * @return array League data
 */
function oc_get_league_data( $league ) {
    if ( is_numeric( $league ) ) {
        $league = get_term( $league, 'league' );
    }
    
    if ( ! $league || is_wp_error( $league ) ) {
        return array();
    }
    
    // Get associated sport
    $sports = get_the_terms( $league, 'sport' );
    $sport = ! empty( $sports ) && ! is_wp_error( $sports ) ? $sports[0] : null;
    
    return array(
        'id'      => $league->term_id,
        'name'    => $league->name,
        'slug'    => $league->slug,
        'sport'   => $sport,
        'matches' => 0, // Will be populated if needed
    );
}

/**
 * Get sport data
 * 
 * @since 1.0.0
 * 
 * @param int|WP_Term $sport Sport ID or term object
 * @return array Sport data
 */
function oc_get_sport_data( $sport ) {
    if ( is_numeric( $sport ) ) {
        $sport = get_term( $sport, 'sport' );
    }
    
    if ( ! $sport || is_wp_error( $sport ) ) {
        return array();
    }
    
    return array(
        'id'          => $sport->term_id,
        'name'        => $sport->name,
        'slug'        => $sport->slug,
        'description' => $sport->description,
    );
}

/**
 * Get grouped matches for live matches list
 * 
 * Groups matches by date, then by league within each date.
 * Includes Today grouping with special handling for live matches.
 * 
 * @since 1.0.0
 * 
 * @param array $args Query arguments
 * @return array Grouped matches
 */
function oc_get_grouped_matches( $args = array() ) {
    $defaults = array(
        'posts_per_page' => -1,
        'post_type'      => 'match',
        'post_status'    => 'publish',
        'meta_key'       => 'oc_match_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array(
            array(
                'key'     => 'oc_match_date',
                'value'   => date( 'Y-m-d', strtotime( '-1 day' ) ),
                'compare' => '>=',
                'type'    => 'DATE',
            ),
        ),
        'tax_query'      => array(),
    );
    
    $args = wp_parse_args( $args, $defaults );
    
    // Get today's date for special grouping
    $today = date( 'Y-m-d' );
    $tomorrow = date( 'Y-m-d', strtotime( '+1 day' ) );
    
    $matches = get_posts( $args );
    
    if ( empty( $matches ) ) {
        return array();
    }
    
    $grouped = array(
        'today'    => array( 'label' => __( 'Today', 'odds-comparison' ), 'matches' => array() ),
        'tomorrow' => array( 'label' => __( 'Tomorrow', 'odds-comparison' ), 'matches' => array() ),
        'upcoming' => array(), // Will be keyed by formatted date
    );
    
    // Group matches by date
    foreach ( $matches as $match ) {
        $match_id = $match->ID;
        $match_date = get_post_meta( $match_id, 'oc_match_date', true );
        $league = get_post_meta( $match_id, 'oc_match_league', true );
        $is_live = get_post_meta( $match_id, 'oc_live_match', true );
        
        if ( empty( $match_date ) ) {
            continue;
        }
        
        // Get league taxonomy if not set
        if ( empty( $league ) ) {
            $leagues = get_the_terms( $match_id, 'league' );
            if ( $leagues && ! is_wp_error( $leagues ) ) {
                $league = $leagues[0]->name;
            }
        }
        
        // Get teams from taxonomy
        $home_team_id = get_post_meta( $match_id, 'oc_match_home_team', true );
        $away_team_id = get_post_meta( $match_id, 'oc_match_away_team', true );
        
        $home_team = $home_team_id ? get_term( $home_team_id, 'team' ) : null;
        $away_team = $away_team_id ? get_term( $away_team_id, 'team' ) : null;
        
        $match_data = array(
            'id'         => $match_id,
            'title'      => $match->post_title,
            'date'       => $match_date,
            'time'       => get_post_meta( $match_id, 'oc_match_time', true ),
            'league'     => $league ?: __( 'Other', 'odds-comparison' ),
            'status'     => get_post_meta( $match_id, 'oc_match_status', true ),
            'is_live'    => $is_live,
            'home_team'  => $home_team ? $home_team->name : '',
            'away_team'  => $away_team ? $away_team->name : '',
            'home_logo'  => $home_team ? get_term_meta( $home_team->term_id, 'oc_team_logo', true ) : '',
            'away_logo'  => $away_team ? get_term_meta( $away_team->term_id, 'oc_team_logo', true ) : '',
            'permalink'  => get_permalink( $match_id ),
        );
        
        // Get odds data
        $odds = oc_get_match_odds( $match_id );
        $best_odds = oc_get_best_odds( $odds );
        $match_data['odds'] = $best_odds;
        $match_data['has_odds'] = ! empty( $odds );
        
        // Sort into date groups
        if ( $match_date === $today ) {
            // Put live matches first in Today
            if ( $is_live ) {
                array_unshift( $grouped['today']['matches'], $match_data );
            } else {
                $grouped['today']['matches'][] = $match_data;
            }
        } elseif ( $match_date === $tomorrow ) {
            $grouped['tomorrow']['matches'][] = $match_data;
        } else {
            // Format date like "Wednesday, January 7"
            $formatted_date = date_i18n( 'l, F j', strtotime( $match_date ) );
            
            if ( ! isset( $grouped['upcoming'][ $formatted_date ] ) ) {
                $grouped['upcoming'][ $formatted_date ] = array(
                    'label'   => $formatted_date,
                    'date'    => $match_date,
                    'matches' => array(),
                );
            }
            $grouped['upcoming'][ $formatted_date ]['matches'][] = $match_data;
        }
    }
    
    // Remove empty date groups
    foreach ( $grouped as $key => $group ) {
        if ( empty( $group['matches'] ) ) {
            unset( $grouped[ $key ] );
        }
    }
    
    return $grouped;
}

/**
 * Get team logo URL
 * 
 * @since 1.0.0
 * 
 * @param int|string $team_id Team term ID or name
 * @return string Logo URL or empty string
 */
function oc_get_team_logo( $team_id ) {
    // If team_id is a name, try to find the term
    if ( ! is_numeric( $team_id ) ) {
        $team = get_term_by( 'name', $team_id, 'team' );
        if ( $team && ! is_wp_error( $team ) ) {
            $team_id = $team->term_id;
        } else {
            return '';
        }
    }
    
    $logo = get_term_meta( absint( $team_id ), 'oc_team_logo', true );
    
    return ! empty( $logo ) ? esc_url( $logo ) : '';
}

/**
 * Get match best odds for live display
 * 
 * Returns formatted odds for 1/X/2 display with best odds highlighting.
 * 
 * @since 1.0.0
 * 
 * @param int $match_id Match post ID
 * @return array Odds data
 */
function oc_get_match_live_odds( $match_id ) {
    $odds = oc_get_match_odds( $match_id );
    
    if ( empty( $odds ) ) {
        return array(
            'home'      => 0,
            'draw'      => 0,
            'away'      => 0,
            'updated_at' => null,
            'has_odds'   => false,
        );
    }
    
    $best = oc_get_best_odds( $odds );
    
    return array(
        'home'        => $best['home']['odds'] > 0 ? $best['home']['odds'] : 0,
        'draw'        => $best['draw']['odds'] > 0 ? $best['draw']['odds'] : 0,
        'away'        => $best['away']['odds'] > 0 ? $best['away']['odds'] : 0,
        'updated_at'  => ! empty( $odds[0]['last_updated'] ) ? $odds[0]['last_updated'] : null,
        'has_odds'    => true,
        'bookmakers'  => count( $odds ),
    );
}

/**
 * Get live matches list data
 * 
 * Enhanced version of oc_get_grouped_matches for the live matches list UI.
 * Groups by date, then by league within each date.
 * 
 * @since 1.0.0
 * 
 * @param array $args Query arguments
 * @return array Structured match data
 */
function oc_get_live_matches_list( $args = array() ) {
    $defaults = array(
        'posts_per_page' => 50,
        'post_type'      => 'match',
        'post_status'    => 'publish',
        'meta_key'       => 'oc_match_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array(
            array(
                'key'     => 'oc_match_date',
                'value'   => date( 'Y-m-d', strtotime( '-1 day' ) ),
                'compare' => '>=',
                'type'    => 'DATE',
            ),
        ),
    );
    
    $args = wp_parse_args( $args, $defaults );
    
    // Filter by sport if specified
    if ( ! empty( $args['sport'] ) ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'sport',
            'field'    => 'slug',
            'terms'    => $args['sport'],
        );
        unset( $args['sport'] );
    }
    
    // Filter by league if specified
    if ( ! empty( $args['league'] ) ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'league',
            'field'    => 'slug',
            'terms'    => $args['league'],
        );
        unset( $args['league'] );
    }
    
    $matches = get_posts( $args );
    
    if ( empty( $matches ) ) {
        return array( 'date_groups' => array(), 'total_matches' => 0 );
    }
    
    $today = date( 'Y-m-d' );
    $tomorrow = date( 'Y-m-d', strtotime( '+1 day' ) );
    
    $date_groups = array();
    $total_matches = 0;
    
    foreach ( $matches as $match ) {
        $match_id = $match->ID;
        $match_date = get_post_meta( $match_id, 'oc_match_date', true );
        $match_time = get_post_meta( $match_id, 'oc_match_time', true );
        
        if ( empty( $match_date ) ) {
            continue;
        }
        
        // Get league taxonomy
        $leagues = get_the_terms( $match_id, 'league' );
        $league = $leagues && ! is_wp_error( $leagues ) ? $leagues[0] : null;
        
        // Get teams from taxonomy
        $teams = get_the_terms( $match_id, 'team' );
        $home_team = null;
        $away_team = null;
        
        if ( $teams && ! is_wp_error( $teams ) ) {
            $team_count = count( $teams );
            if ( $team_count >= 2 ) {
                $home_team = $teams[0];
                $away_team = $teams[1];
            } elseif ( $team_count === 1 ) {
                $home_team = $teams[0];
            }
        }
        
        // Fallback to meta values
        if ( ! $home_team ) {
            $home_team_name = get_post_meta( $match_id, 'oc_match_home_team', true );
            if ( $home_team_name ) {
                $home_team = (object) array( 'name' => $home_team_name );
            }
        }
        
        if ( ! $away_team ) {
            $away_team_name = get_post_meta( $match_id, 'oc_match_away_team', true );
            if ( $away_team_name ) {
                $away_team = (object) array( 'name' => $away_team_name );
            }
        }
        
        // Get odds
        $odds_data = oc_get_match_live_odds( $match_id );
        
        // Build match data
        $match_data = array(
            'id'           => $match_id,
            'time'         => $match_time ? date_i18n( 'H:i', strtotime( $match_time ) ) : '',
            'league_id'    => $league ? $league->term_id : 0,
            'league_name'  => $league ? $league->name : __( 'Other', 'odds-comparison' ),
            'league_slug'  => $league ? $league->slug : 'other',
            'home_team'    => array(
                'name'  => $home_team ? $home_team->name : '',
                'logo'  => $home_team && isset( $home_team->term_id ) ? oc_get_team_logo( $home_team->term_id ) : '',
            ),
            'away_team'    => array(
                'name'  => $away_team ? $away_team->name : '',
                'logo'  => $away_team && isset( $away_team->term_id ) ? oc_get_team_logo( $away_team->term_id ) : '',
            ),
            'odds'         => $odds_data,
            'permalink'    => get_permalink( $match_id ),
            'is_live'      => get_post_meta( $match_id, 'oc_live_match', true ),
        );
        
        // Determine date group key
        if ( $match_date === $today ) {
            $group_key = 'today';
            $label = __( 'Today', 'odds-comparison' );
        } elseif ( $match_date === $tomorrow ) {
            $group_key = 'tomorrow';
            $label = __( 'Tomorrow', 'odds-comparison' );
        } else {
            $group_key = $match_date;
            $label = date_i18n( 'l, F j', strtotime( $match_date ) );
        }
        
        // Create date group if needed
        if ( ! isset( $date_groups[ $group_key ] ) ) {
            $date_groups[ $group_key ] = array(
                'key'    => $group_key,
                'label'  => $label,
                'leagues' => array(),
            );
        }
        
        // Add match to league subgroup
        $league_key = $match_data['league_slug'];
        if ( ! isset( $date_groups[ $group_key ]['leagues'][ $league_key ] ) ) {
            $date_groups[ $group_key ]['leagues'][ $league_key ] = array(
                'id'     => $match_data['league_id'],
                'name'   => $match_data['league_name'],
                'matches' => array(),
            );
        }
        
        $date_groups[ $group_key ]['leagues'][ $league_key ]['matches'][] = $match_data;
        $total_matches++;
    }
    
    // Remove empty groups and reindex
    $date_groups = array_filter( $date_groups, function( $group ) {
        return ! empty( $group['leagues'] );
    });
    
    return array(
        'date_groups'   => array_values( $date_groups ),
        'total_matches' => $total_matches,
    );
}

/**
 * Get available leagues for filtering
 * 
 * @since 1.0.0
 * 
 * @param array $args Query arguments
 * @return array League data
 */
function oc_get_leagues_for_filter( $args = array() ) {
    $defaults = array(
        'taxonomy'   => 'league',
        'hide_empty' => true,
        'number'     => 20,
    );
    
    $args = wp_parse_args( $args, $defaults );
    
    $leagues = get_terms( $args );
    
    if ( is_wp_error( $leagues ) || empty( $leagues ) ) {
        return array();
    }
    
    $result = array();
    
    foreach ( $leagues as $league ) {
        $result[] = array(
            'id'   => $league->term_id,
            'name' => $league->name,
            'slug' => $league->slug,
            'count' => $league->count,
        );
    }
    
    return $result;
}

/**
 * Render live matches list HTML
 * 
 * @since 1.0.0
 * 
 * @param array $args Shortcode/function arguments
 */
function oc_render_live_matches_list( $args = array() ) {
    $defaults = array(
        'sport'         => '',
        'league'        => '',
        'limit'         => 50,
        'show_filters'  => true,
        'auto_refresh'  => 30,
        'container_id'  => 'oc-live-matches-' . uniqid(),
    );
    
    $args = wp_parse_args( $args, $defaults );
    
    $container_id = $args['container_id'];
    $leagues = oc_get_leagues_for_filter();
    
    // Build filter options
    $filter_html = '';
    if ( $args['show_filters'] ) {
        ob_start();
        ?>
        <div class="oc-matches-filters">
            <div class="oc-filter-group">
                <label for="<?php echo esc_attr( $container_id ); ?>-date"><?php esc_html_e( 'Date:', 'odds-comparison' ); ?></label>
                <select id="<?php echo esc_attr( $container_id ); ?>-date" class="oc-matches-filter" data-filter="date">
                    <option value="all"><?php esc_html_e( 'All Dates', 'odds-comparison' ); ?></option>
                    <option value="today" selected><?php esc_html_e( 'Today', 'odds-comparison' ); ?></option>
                    <option value="tomorrow"><?php esc_html_e( 'Tomorrow', 'odds-comparison' ); ?></option>
                    <option value="upcoming"><?php esc_html_e( 'Upcoming', 'odds-comparison' ); ?></option>
                </select>
            </div>
            
            <div class="oc-filter-group">
                <label for="<?php echo esc_attr( $container_id ); ?>-league"><?php esc_html_e( 'League:', 'odds-comparison' ); ?></label>
                <select id="<?php echo esc_attr( $container_id ); ?>-league" class="oc-matches-filter" data-filter="league">
                    <option value=""><?php esc_html_e( 'All Leagues', 'odds-comparison' ); ?></option>
                    <?php foreach ( $leagues as $league ) : ?>
                        <option value="<?php echo esc_attr( $league['slug'] ); ?>" <?php selected( $args['league'], $league['slug'] ); ?>>
                            <?php echo esc_html( $league['name'] ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="oc-filter-status">
                <span class="oc-last-updated"></span>
            </div>
        </div>
        <?php
        $filter_html = ob_get_clean();
    }
    
    // Main container
    $html = sprintf(
        '<div id="%s" class="oc-live-matches-list" data-auto-refresh="%d" data-nonce="%s">',
        esc_attr( $container_id ),
        absint( $args['auto_refresh'] ),
        esc_attr( wp_create_nonce( 'oc_ajax_nonce' ) )
    );
    
    $html .= $filter_html;
    
    $html .= '<div class="oc-matches-container">';
    $html .= '<div class="oc-matches-loading">';
    $html .= '<span class="oc-loading-spinner"></span>';
    $html .= '<span class="oc-loading-text">' . esc_html__( 'Loading matches...', 'odds-comparison' ) . '</span>';
    $html .= '</div>';
    $html .= '<div class="oc-matches-content"></div>';
    $html .= '<div class="oc-matches-empty" style="display:none;">';
    $html .= '<p>' . esc_html__( 'No matches found for the selected criteria.', 'odds-comparison' ) . '</p>';
    $html .= '</div>';
    $html .= '</div>'; // .oc-matches-container
    
    $html .= '</div>'; // .oc-live-matches-list
    
    echo $html;
}

/**
 * Get matches by date range
 * 
 * @since 1.0.0
 * 
 * @param string $start_date Start date (Y-m-d)
 * @param string $end_date   End date (Y-m-d)
 * @param array  $args       Additional query arguments
 * @return array Matches
 */
function oc_get_matches_by_date_range( $start_date, $end_date, $args = array() ) {
    $defaults = array(
        'post_type'      => 'match',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_key'       => 'oc_match_date',
        'meta_type'      => 'DATE',
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => 'oc_match_date',
                'value'   => $start_date,
                'compare' => '>=',
                'type'    => 'DATE',
            ),
            array(
                'key'     => 'oc_match_date',
                'value'   => $end_date,
                'compare' => '<=',
                'type'    => 'DATE',
            ),
        ),
        'orderby' => 'meta_value',
        'order'   => 'ASC',
    );
    
    $args = wp_parse_args( $args, $defaults );
    
    return get_posts( $args );
}

/**
 * Get match count by status
 * 
 * @since 1.0.0
 * 
 * @param string $status Match status
 * @return int Count
 */
function oc_get_match_count_by_status( $status ) {
    $args = array(
        'post_type'      => 'match',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_key'       => 'oc_match_status',
        'meta_value'     => $status,
    );
    
    $matches = get_posts( $args );
    
    return count( $matches );
}

/**
 * Generate stars HTML
 * 
 * @since 1.0.0
 * 
 * @param float $rating Rating value (0-5)
 * @return string Stars HTML
 */
function oc_generate_stars( $rating ) {
    $full_stars = floor( $rating );
    $half_star = ( $rating - $full_stars ) >= 0.5 ? 1 : 0;
    $empty_stars = 5 - $full_stars - $half_star;
    
    $html = '';
    
    for ( $i = 0; $i < $full_stars; $i++ ) {
        $html .= '<span class="star filled">★</span>';
    }
    
    if ( $half_star ) {
        $html .= '<span class="star half">★</span>';
    }
    
    for ( $i = 0; $i < $empty_stars; $i++ ) {
        $html .= '<span class="star empty">★</span>';
    }
    
    return $html;
}

/**
 * Get betting site ranking based on multiple factors
 * 
 * @since 1.0.0
 * 
 * @param int $operator_id Operator post ID
 * @return float Rank score
 */
function oc_calculate_operator_rank( $operator_id ) {
    $rating = (float) get_post_meta( $operator_id, 'oc_operator_rating', true );
    $bonus_value = (float) get_post_meta( $operator_id, 'oc_operator_bonus_value', true );
    
    // Weighted scoring: 70% rating, 30% bonus
    $score = ( $rating * 0.7 ) + ( min( $bonus_value, 200 ) / 200 * 5 * 0.3 );
    
    return round( $score, 2 );
}

/**
 * Format date for display
 * 
 * @since 1.0.0
 * 
 * @param string $date   Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function oc_format_match_date( $date, $format = 'd M Y' ) {
    if ( empty( $date ) ) {
        return '';
    }
    
    $timestamp = strtotime( $date );
    
    if ( false === $timestamp ) {
        return $date;
    }
    
    return date_i18n( $format, $timestamp );
}

/**
 * Check if match is upcoming
 * 
 * @since 1.0.0
 * 
 * @param int $match_id Match post ID
 * @return bool
 */
function oc_is_upcoming_match( $match_id ) {
    $match_date = get_post_meta( $match_id, 'oc_match_date', true );
    
    if ( empty( $match_date ) ) {
        return false;
    }
    
    $today = date( 'Y-m-d' );
    
    return $match_date >= $today;
}

/**
 * Check if match is live
 * 
 * @since 1.0.0
 * 
 * @param int $match_id Match post ID
 * @return bool
 */
function oc_is_live_match( $match_id ) {
    return '1' === get_post_meta( $match_id, 'oc_live_match', true );
}

/**
 * Get time until match
 * 
 * @since 1.0.0
 * 
 * @param int $match_id Match post ID
 * @return string Time until match
 */
function oc_get_time_until_match( $match_id ) {
    $match_date = get_post_meta( $match_id, 'oc_match_date', true );
    $match_time = get_post_meta( $match_id, 'oc_match_time', true );
    
    if ( empty( $match_date ) || empty( $match_time ) ) {
        return '';
    }
    
    $match_datetime = strtotime( $match_date . ' ' . $match_time );
    $now = current_time( 'timestamp' );
    
    $diff = $match_datetime - $now;
    
    if ( $diff <= 0 ) {
        return esc_html__( 'Started', 'odds-comparison' );
    }
    
    $days = floor( $diff / ( 24 * 60 * 60 ) );
    $hours = floor( ( $diff % ( 24 * 60 * 60 ) ) / ( 60 * 60 ) );
    $minutes = floor( ( $diff % ( 60 * 60 ) ) / 60 );
    
    if ( $days > 0 ) {
        return sprintf( 
            _n( '%d day', '%d days', $days, 'odds-comparison' ),
            $days
        );
    }
    
    if ( $hours > 0 ) {
        return sprintf( 
            _n( '%d hour', '%d hours', $hours, 'odds-comparison' ),
            $hours
        );
    }
    
    return sprintf( 
        _n( '%d minute', '%d minutes', $minutes, 'odds-comparison' ),
        $minutes
    );
}

/**
 * Truncate text with ellipsis
 * 
 * @since 1.0.0
 * 
 * @param string $text   Text to truncate
 * @param int    $length Max length
 * @return string Truncated text
 */
function oc_truncate_text( $text, $length = 100 ) {
    if ( strlen( $text ) <= $length ) {
        return $text;
    }
    
    return substr( $text, 0, $length ) . '&hellip;';
}

/**
 * Get odds comparison URL for affiliate tracking
 * 
 * @since 1.0.0
 * 
 * @param int    $operator_id Operator post ID
 * @param int    $match_id    Match post ID
 * @param string $outcome    Betting outcome (home/draw/away)
 * @return string Affiliate URL with tracking
 */
function oc_get_affiliate_url( $operator_id, $match_id = 0, $outcome = '' ) {
    $affiliate_url = get_post_meta( $operator_id, 'oc_operator_affiliate_url', true );
    
    if ( empty( $affiliate_url ) ) {
        return '#';
    }
    
    // Add UTM parameters for tracking
    $utm_params = array(
        'utm_source'   => 'odds-comparison',
        'utm_medium'   => 'referral',
        'utm_campaign' => 'odds-widget',
    );
    
    if ( $match_id ) {
        $utm_params['utm_content'] = 'match-' . $match_id;
    }
    
    if ( $outcome ) {
        $utm_params['bet_type'] = $outcome;
    }
    
    // Build URL with query parameters
    $separator = ( strpos( $affiliate_url, '?' ) !== false ) ? '&' : '?';
    $url = $affiliate_url . $separator . http_build_query( $utm_params );
    
    // Record click
    if ( $match_id ) {
        oc_record_affiliate_click( $operator_id, array(
            'page_url'  => get_permalink( $match_id ),
            'click_url' => $url,
        ) );
    }
    
    return esc_url( $url );
}

/**
 * Validate odds format
 * 
 * @since 1.0.0
 * 
 * @param mixed $odds Odds value
 * @return bool
 */
function oc_validate_odds( $odds ) {
    if ( ! is_numeric( $odds ) ) {
        return false;
    }
    
    $odds = (float) $odds;
    
    // Odds should be between 1.01 and 1000
    return $odds >= 1.01 && $odds <= 1000;
}

/**
 * Get all operators ordered by rank
 *
 * @since 1.0.0
 *
 * @param array $args Query arguments
 * @return array Operators
 */
function oc_get_ranked_operators( $args = array() ) {
    $defaults = array(
        'post_type'      => 'operator',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );
    
    $args = wp_parse_args( $args, $defaults );
    
    $operators = get_posts( $args );
    
    // Sort by rank
    usort( $operators, function( $a, $b ) {
        $rank_a = oc_calculate_operator_rank( $a->ID );
        $rank_b = oc_calculate_operator_rank( $b->ID );
        
        return $rank_b - $rank_a;
    } );
    
    return $operators;
}

/**
 * Get operators for comparison tool with filtering and sorting
 *
 * @since 1.0.0
 *
 * @param array $args Query arguments
 * @return array Operators data
 */
function oc_get_comparison_operators( $args = array() ) {
    $defaults = array(
        'limit'    => 12,
        'license'  => '',
        'sport'    => '',
        'featured' => 'no',
        'sort'     => 'rating',
        'offset'   => 0,
    );
    
    $args = wp_parse_args( $args, $defaults );
    
    $query_args = array(
        'post_type'      => 'operator',
        'post_status'    => 'publish',
        'posts_per_page' => absint( $args['limit'] ),
        'offset'         => absint( $args['offset'] ),
    );
    
    // Filter by license
    if ( ! empty( $args['license'] ) ) {
        $query_args['tax_query'][] = array(
            'taxonomy' => 'license',
            'field'    => 'slug',
            'terms'    => $args['license'],
        );
    }
    
    // Filter by sport
    if ( ! empty( $args['sport'] ) ) {
        $query_args['tax_query'][] = array(
            'taxonomy' => 'sport',
            'field'    => 'slug',
            'terms'    => $args['sport'],
        );
    }
    
    // Filter by featured
    if ( 'yes' === $args['featured'] ) {
        $query_args['meta_query'][] = array(
            'key'   => 'oc_featured_operator',
            'value' => '1',
        );
    }
    
    // Sorting
    switch ( $args['sort'] ) {
        case 'bonus':
            $query_args['meta_key'] = 'oc_operator_bonus_value';
            $query_args['orderby'] = 'meta_value_num';
            $query_args['order'] = 'DESC';
            break;
        case 'newest':
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
            break;
        case 'name':
            $query_args['orderby'] = 'title';
            $query_args['order'] = 'ASC';
            break;
        case 'rating':
        default:
            $query_args['meta_key'] = 'oc_operator_rating';
            $query_args['orderby'] = 'meta_value_num';
            $query_args['order'] = 'DESC';
            break;
    }
    
    $operators = get_posts( $query_args );
    
    $result = array();
    
    foreach ( $operators as $operator ) {
        $result[] = oc_get_operator_data( $operator->ID );
    }
    
    return $result;
}

/**
 * Count operators matching criteria
 *
 * @since 1.0.0
 *
 * @param array $args Query arguments
 * @return int Count
 */
function oc_count_comparison_operators( $args = array() ) {
    $defaults = array(
        'license'  => '',
        'sport'    => '',
        'featured' => 'no',
    );
    
    $args = wp_parse_args( $args, $defaults );
    
    $query_args = array(
        'post_type'      => 'operator',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );
    
    // Filter by license
    if ( ! empty( $args['license'] ) ) {
        $query_args['tax_query'][] = array(
            'taxonomy' => 'license',
            'field'    => 'slug',
            'terms'    => $args['license'],
        );
    }
    
    // Filter by sport
    if ( ! empty( $args['sport'] ) ) {
        $query_args['tax_query'][] = array(
            'taxonomy' => 'sport',
            'field'    => 'slug',
            'terms'    => $args['sport'],
        );
    }
    
    // Filter by featured
    if ( 'yes' === $args['featured'] ) {
        $query_args['meta_query'][] = array(
            'key'   => 'oc_featured_operator',
            'value' => '1',
        );
    }
    
    $query = new WP_Query( $query_args );
    
    return $query->found_posts;
}

/**
 * Render comparison card for an operator
 *
 * @since 1.0.0
 *
 * @param array $operator   Operator data array
 * @param string $show_pros Show pros ('yes' or 'no')
 * @return string HTML output
 */
function oc_render_comparison_card( $operator, $show_pros = 'yes' ) {
    $operator_id = $operator['ID'];
    
    $rating = ! empty( $operator['rating'] ) ? $operator['rating'] : 0;
    $bonus_text = ! empty( $operator['bonus_text'] ) ? $operator['bonus_text'] : '';
    $bonus_value = ! empty( $operator['bonus_value'] ) ? $operator['bonus_value'] : '';
    $bonus_type = ! empty( $operator['bonus_type'] ) ? $operator['bonus_type'] : '';
    $affiliate_url = ! empty( $operator['affiliate_url'] ) ? $operator['affiliate_url'] : '';
    $min_deposit = ! empty( $operator['min_deposit'] ) ? $operator['min_deposit'] : '';
    $payment_methods = ! empty( $operator['payment_methods'] ) ? $operator['payment_methods'] : '';
    $is_featured = ! empty( $operator['is_featured'] ) ? $operator['is_featured'] : false;
    $pros = ! empty( $operator['pros'] ) && is_array( $operator['pros'] ) ? $operator['pros'] : array();
    $cons = ! empty( $operator['cons'] ) && is_array( $operator['cons'] ) ? $operator['cons'] : array();
    
    // Get license term
    $license_name = '';
    if ( ! empty( $operator['license_term'] ) ) {
        $license_name = $operator['license_term']->name;
    }
    
    // Get logo
    $logo_html = '';
    if ( has_post_thumbnail( $operator_id ) ) {
        $logo_html = get_the_post_thumbnail( $operator_id, 'operator-logo', array( 'alt' => $operator['title'] ) );
    } else {
        $logo_html = '<span class="oc-logo-placeholder">' . esc_html( substr( $operator['title'], 0, 2 ) ) . '</span>';
    }
    
    ob_start();
    ?>
    <article class="oc-comparison-card <?php echo $is_featured ? 'is-featured' : ''; ?>" data-operator-id="<?php echo absint( $operator_id ); ?>">
        <?php if ( $is_featured ) : ?>
            <div class="oc-badge oc-featured"><?php esc_html_e( 'Featured', 'odds-comparison' ); ?></div>
        <?php endif; ?>
        
        <div class="oc-comparison-header">
            <div class="oc-operator-logo">
                <?php echo $logo_html; ?>
            </div>
            
            <div class="oc-operator-info">
                <h3 class="oc-operator-name">
                    <a href="<?php echo esc_url( $operator['permalink'] ); ?>">
                        <?php echo esc_html( $operator['title'] ); ?>
                    </a>
                </h3>
                
                <?php if ( $license_name ) : ?>
                    <span class="oc-license"><?php echo esc_html( $license_name ); ?></span>
                <?php endif; ?>
                
                <?php if ( $rating ) : ?>
                    <div class="oc-rating" aria-label="<?php echo sprintf( esc_attr__( 'Rating: %s out of 5 stars', 'odds-comparison' ), number_format( $rating, 1 ) ); ?>">
                        <div class="oc-stars">
                            <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                <span class="star <?php echo $i <= round( $rating ) ? 'filled' : 'empty'; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <span class="oc-rating-value"><?php echo esc_html( number_format( $rating, 1 ) ); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ( $bonus_text ) : ?>
            <div class="oc-comparison-bonus">
                <span class="bonus-label"><?php esc_html_e( 'Welcome Bonus', 'odds-comparison' ); ?></span>
                <span class="bonus-value"><?php echo esc_html( $bonus_text ); ?></span>
                <?php if ( $bonus_value ) : ?>
                    <span class="bonus-amount"><?php echo esc_html( $bonus_value ); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="oc-comparison-details">
            <?php if ( $min_deposit ) : ?>
                <div class="detail-item">
                    <span class="detail-label"><?php esc_html_e( 'Min. Deposit', 'odds-comparison' ); ?></span>
                    <span class="detail-value"><?php echo esc_html( $min_deposit ); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ( $payment_methods ) : ?>
                <div class="detail-item">
                    <span class="detail-label"><?php esc_html_e( 'Payment Methods', 'odds-comparison' ); ?></span>
                    <span class="detail-value"><?php echo esc_html( $payment_methods ); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ( 'yes' === $show_pros && ( ! empty( $pros ) || ! empty( $cons ) ) ) : ?>
            <div class="oc-comparison-pros-cons">
                <?php if ( ! empty( $pros ) ) : ?>
                    <ul class="oc-pros">
                        <?php foreach ( array_slice( $pros, 0, 3 ) as $pro ) : ?>
                            <li><span class="oc-check">✓</span> <?php echo esc_html( $pro ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <?php if ( ! empty( $cons ) ) : ?>
                    <ul class="oc-cons">
                        <?php foreach ( array_slice( $cons, 0, 2 ) as $con ) : ?>
                            <li><span class="oc-cross">✕</span> <?php echo esc_html( $con ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="oc-comparison-actions">
            <?php if ( $affiliate_url ) : ?>
                <a href="<?php echo esc_url( $affiliate_url ); ?>" 
                   class="button oc-btn-primary oc-visit-btn" 
                   target="_blank" 
                   rel="nofollow sponsored"
                   data-operator-id="<?php echo absint( $operator_id ); ?>">
                    <?php esc_html_e( 'Visit Site', 'odds-comparison' ); ?>
                </a>
            <?php endif; ?>
            <a href="<?php echo esc_url( $operator['permalink'] ); ?>" class="button oc-btn-secondary oc-review-btn">
                <?php esc_html_e( 'Read Review', 'odds-comparison' ); ?>
            </a>
        </div>
        
        <div class="oc-comparison-footer">
            <span class="oc-tnc-note"><?php esc_html_e( '18+ • T&Cs apply', 'odds-comparison' ); ?></span>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

