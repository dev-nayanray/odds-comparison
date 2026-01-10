<?php
/**
 * Database Setup and Management
 * 
 * Handles creation and management of custom database tables
 * for odds data and affiliate tracking.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Create custom database tables on theme activation
 * 
 * @since 1.0.0
 */
function oc_create_database_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Match Odds Table
    $table_name_odds = $wpdb->prefix . 'oc_match_odds';
    
    $sql_odds = "CREATE TABLE {$table_name_odds} (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        match_id bigint(20) UNSIGNED NOT NULL,
        bookmaker_id bigint(20) UNSIGNED NOT NULL,
        odds_home decimal(5,2) DEFAULT NULL,
        odds_draw decimal(5,2) DEFAULT NULL,
        odds_away decimal(5,2) DEFAULT NULL,
        last_updated datetime DEFAULT NULL,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY match_id (match_id),
        KEY bookmaker_id (bookmaker_id),
        KEY match_bookmaker (match_id, bookmaker_id),
        KEY updated (last_updated)
    ) {$charset_collate};";
    
    // Affiliate Clicks Table
    $table_name_clicks = $wpdb->prefix . 'oc_affiliate_clicks';
    
    $sql_clicks = "CREATE TABLE {$table_name_clicks} (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        operator_id bigint(20) UNSIGNED NOT NULL,
        page_url varchar(500) DEFAULT NULL,
        click_url varchar(500) DEFAULT NULL,
        clicked_at datetime DEFAULT NULL,
        ip_hash varchar(64) DEFAULT NULL,
        user_agent varchar(500) DEFAULT NULL,
        referer varchar(500) DEFAULT NULL,
        converted tinyint(1) DEFAULT 0,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY operator_id (operator_id),
        KEY clicked_at (clicked_at),
        KEY ip_hash (ip_hash),
        KEY converted (converted)
    ) {$charset_collate};";
    
    // Operators Ratings Table (for aggregated ratings)
    $table_name_ratings = $wpdb->prefix . 'oc_operator_ratings';
    
    $sql_ratings = "CREATE TABLE {$table_name_ratings} (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        operator_id bigint(20) UNSIGNED NOT NULL,
        rating_source varchar(100) DEFAULT NULL,
        rating_value decimal(3,2) DEFAULT NULL,
        rating_count int(11) DEFAULT 0,
        last_updated datetime DEFAULT NULL,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY operator_source (operator_id, rating_source),
        KEY operator_id (operator_id)
    ) {$charset_collate};";
    
    // Include WordPress dbDelta function
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
    // Create tables
    dbDelta( $sql_odds );
    dbDelta( $sql_clicks );
    dbDelta( $sql_ratings );
    
    // Store version for future updates
    update_option( 'oc_db_version', OC_THEME_VERSION );
}

/**
 * Drop custom database tables
 * 
 * @since 1.0.0
 */
function oc_drop_database_tables() {
    global $wpdb;
    
    $tables = array(
        $wpdb->prefix . 'oc_match_odds',
        $wpdb->prefix . 'oc_affiliate_clicks',
        $wpdb->prefix . 'oc_operator_ratings',
    );
    
    foreach ( $tables as $table ) {
        $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
    }
    
    delete_option( 'oc_db_version' );
}

/**
 * Insert odds for a match from a bookmaker
 * 
 * @since 1.0.0
 * 
 * @param array $odds_data Odds data array
 * @return int|false Inserted row ID or false on failure
 */
function oc_insert_odds( $odds_data ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_match_odds';
    
    // Validate required fields
    if ( empty( $odds_data['match_id'] ) || empty( $odds_data['bookmaker_id'] ) ) {
        return false;
    }
    
    $data = array(
        'match_id'      => absint( $odds_data['match_id'] ),
        'bookmaker_id'  => absint( $odds_data['bookmaker_id'] ),
        'odds_home'     => isset( $odds_data['odds_home'] ) ? floatval( $odds_data['odds_home'] ) : null,
        'odds_draw'     => isset( $odds_data['odds_draw'] ) ? floatval( $odds_data['odds_draw'] ) : null,
        'odds_away'     => isset( $odds_data['odds_away'] ) ? floatval( $odds_data['odds_away'] ) : null,
        'last_updated'  => current_time( 'mysql' ),
    );
    
    $format = array( '%d', '%d', '%f', '%f', '%f', '%s' );
    
    // Check if record exists
    $existing = $wpdb->get_row( $wpdb->prepare(
        "SELECT id FROM {$table_name} WHERE match_id = %d AND bookmaker_id = %d",
        $data['match_id'],
        $data['bookmaker_id']
    ) );
    
    if ( $existing ) {
        // Update existing record
        $wpdb->update(
            $table_name,
            $data,
            array( 'id' => $existing->id ),
            $format,
            array( '%d' )
        );
        return $existing->id;
    }
    
    // Insert new record
    return $wpdb->insert( $table_name, $data, $format );
}

/**
 * Update odds for a match from a bookmaker
 * 
 * @since 1.0.0
 * 
 * @param int   $row_id     Row ID to update
 * @param array $odds_data  New odds data
 * @return bool Success status
 */
function oc_update_odds( $row_id, $odds_data ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_match_odds';
    
    $data = array(
        'odds_home'    => isset( $odds_data['odds_home'] ) ? floatval( $odds_data['odds_home'] ) : null,
        'odds_draw'    => isset( $odds_data['odds_draw'] ) ? floatval( $odds_data['odds_draw'] ) : null,
        'odds_away'    => isset( $odds_data['odds_away'] ) ? floatval( $odds_data['odds_away'] ) : null,
        'last_updated' => current_time( 'mysql' ),
    );
    
    $format = array( '%f', '%f', '%f', '%s' );
    
    return $wpdb->update(
        $table_name,
        $data,
        array( 'id' => absint( $row_id ) ),
        $format,
        array( '%d' )
    );
}

/**
 * Delete odds for a match
 * 
 * @since 1.0.0
 * 
 * @param int $match_id Match post ID
 * @return bool Success status
 */
function oc_delete_odds_by_match( $match_id ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_match_odds';
    
    return $wpdb->delete(
        $table_name,
        array( 'match_id' => absint( $match_id ) ),
        array( '%d' )
    );
}

/**
 * Delete odds by bookmaker
 * 
 * @since 1.0.0
 * 
 * @param int $bookmaker_id Operator post ID
 * @return bool Success status
 */
function oc_delete_odds_by_bookmaker( $bookmaker_id ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_match_odds';
    
    return $wpdb->delete(
        $table_name,
        array( 'bookmaker_id' => absint( $bookmaker_id ) ),
        array( '%d' )
    );
}

/**
 * Get all odds for a match
 *
 * This function is now defined in inc/helpers.php with additional operator filtering.
 * This definition is kept for backwards compatibility with any direct database queries.
 *
 * @since 1.0.0
 *
 * @param int $match_id Match post ID
 * @return array Array of odds data
 */
if ( ! function_exists( 'oc_get_match_odds' ) ) {
    function oc_get_match_odds( $match_id ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'oc_match_odds';
        
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE match_id = %d ORDER BY last_updated DESC",
            absint( $match_id )
        ), ARRAY_A );
        
        return $results ? $results : array();
    }
}

/**
 * Clear odds cache for a specific match or all matches
 * 
 * @since 1.1.0
 * 
 * @param int|null $match_id Match post ID to clear cache for, or null for all
 * @return bool Success status
 */
function oc_clear_odds_cache( $match_id = null ) {
    // Clear object cache first
    if ( function_exists( 'wp_cache_flush_group' ) ) {
        wp_cache_flush_group( 'oc_odds' );
    }
    
    // Delete any transients related to odds
    global $wpdb;
    
    if ( $match_id ) {
        // Clear cache for specific match
        wp_cache_delete( 'odds_comparison_' . $match_id, 'oc_odds' );
        wp_cache_delete( 'match_odds_' . $match_id, 'oc_odds' );
        
        // Delete related transients
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_name LIKE %s",
            '%_transient_oc_odds_%',
            '%' . $match_id . '%'
        ) );
    } else {
        // Clear all odds caches
        wp_cache_flush_group( 'oc_odds' );
        
        // Delete all odds-related transients
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_oc_odds_%' 
             OR option_name LIKE '_transient_timeout_oc_odds_%'"
        );
    }
    
    // Trigger action for any external cache systems
    do_action( 'oc_odds_cache_cleared', $match_id );
    
    return true;
}

/**
 * Get odds by bookmaker for a match
 * 
 * @since 1.0.0
 * 
 * @param int $match_id     Match post ID
 * @param int $bookmaker_id Operator post ID
 * @return array|null Odds data or null
 */
function oc_get_odds_by_bookmaker( $match_id, $bookmaker_id ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_match_odds';
    
    $result = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE match_id = %d AND bookmaker_id = %d",
        absint( $match_id ),
        absint( $bookmaker_id )
    ), ARRAY_A );
    
    return $result;
}

/**
 * Get all bookmakers offering odds for a match
 * 
 * @since 1.0.0
 * 
 * @param int $match_id Match post ID
 * @return array Array of operator data with odds
 */
function oc_get_bookmakers_for_match( $match_id ) {
    global $wpdb;
    
    $odds_table   = $wpdb->prefix . 'oc_match_odds';
    $posts_table  = $wpdb->posts;
    
    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT o.*, p.post_title as operator_name 
         FROM {$odds_table} o
         INNER JOIN {$posts_table} p ON o.bookmaker_id = p.ID
         WHERE o.match_id = %d AND p.post_type = 'operator' AND p.post_status = 'publish'
         ORDER BY o.last_updated DESC",
        absint( $match_id )
    ), ARRAY_A );
    
    return $results ? $results : array();
}

/**
 * Get odds comparison for display
 * 
 * @since 1.0.0
 * 
 * @param int $match_id Match post ID
 * @return array Comparison data with best odds highlighted
 */
function oc_get_odds_comparison( $match_id ) {
    // Try to get from cache first
    $cache_key = 'odds_comparison_' . $match_id;
    $cached    = oc_get_cached_odds( $cache_key );
    
    if ( false !== $cached ) {
        return $cached;
    }
    
    $bookmakers = oc_get_bookmakers_for_match( $match_id );
    
    if ( empty( $bookmakers ) ) {
        return array();
    }
    
    // Find best odds
    $best = array(
        'home' => array( 'odds' => 0, 'bookmaker_id' => 0 ),
        'draw' => array( 'odds' => 0, 'bookmaker_id' => 0 ),
        'away' => array( 'odds' => 0, 'bookmaker_id' => 0 ),
    );
    
    foreach ( $bookmakers as $b ) {
        if ( ! empty( $b['odds_home'] ) && (float) $b['odds_home'] > $best['home']['odds'] ) {
            $best['home']['odds'] = (float) $b['odds_home'];
            $best['home']['bookmaker_id'] = $b['bookmaker_id'];
        }
        if ( ! empty( $b['odds_draw'] ) && (float) $b['odds_draw'] > $best['draw']['odds'] ) {
            $best['draw']['odds'] = (float) $b['odds_draw'];
            $best['draw']['bookmaker_id'] = $b['bookmaker_id'];
        }
        if ( ! empty( $b['odds_away'] ) && (float) $b['odds_away'] > $best['away']['odds'] ) {
            $best['away']['odds'] = (float) $b['odds_away'];
            $best['away']['bookmaker_id'] = $b['bookmaker_id'];
        }
    }
    
    // Build comparison data
    $comparison = array(
        'match_id'    => $match_id,
        'best_odds'   => $best,
        'bookmakers'  => array(),
    );
    
    foreach ( $bookmakers as $b ) {
        $bookmaker_data = array(
            'id'           => $b['bookmaker_id'],
            'name'         => $b['operator_name'],
            'odds_home'    => $b['odds_home'],
            'odds_draw'    => $b['odds_draw'],
            'odds_away'    => $b['odds_away'],
            'last_updated' => $b['last_updated'],
            'is_best'      => array(
                'home' => (int) $b['bookmaker_id'] === (int) $best['home']['bookmaker_id'],
                'draw' => (int) $b['bookmaker_id'] === (int) $best['draw']['bookmaker_id'],
                'away' => (int) $b['bookmaker_id'] === (int) $best['away']['bookmaker_id'],
            ),
        );
        
        $comparison['bookmakers'][] = $bookmaker_data;
    }
    
    // Cache for 5 minutes
    oc_cache_odds( $cache_key, $comparison, 300 );
    
    return $comparison;
}

/**
 * Get upcoming matches with odds
 * 
 * @since 1.0.0
 * 
 * @param array $args Query arguments
 * @return array Array of match data with odds
 */
if ( ! function_exists( 'oc_get_upcoming_matches' ) ) {
    function oc_get_upcoming_matches( $args = array() ) {
    $defaults = array(
        'posts_per_page' => 10,
        'post_type'      => 'match',
        'post_status'    => 'publish',
        'meta_key'       => 'oc_match_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array(
            array(
                'key'     => 'oc_match_date',
                'value'   => current_time( 'mysql' ),
                'compare' => '>=',
                'type'    => 'DATETIME',
            ),
        ),
    );
    
    $args = wp_parse_args( $args, $defaults );
    
    $matches = get_posts( $args );
    
    $result = array();
    
    foreach ( $matches as $match ) {
        $match_data = oc_get_match_data( $match->ID );
        $odds       = oc_get_odds_comparison( $match->ID );
        
        $match_data['odds'] = $odds;
        
        $result[] = $match_data;
    }
    
    return $result;
}
}

/**
 * Record affiliate click
 * 
 * @since 1.0.0
 * 
 * @param int   $operator_id Operator post ID
 * @param array $data        Click data
 * @return int|false Inserted click ID or false
 */
function oc_record_affiliate_click( $operator_id, $data = array() ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_affiliate_clicks';
    
    // Hash IP for privacy
    $ip_hash = defined( 'SALTPERSONAL' ) 
        ? hash( 'sha256', $_SERVER['REMOTE_ADDR'] . SALTPERSONAL )
        : hash( 'sha256', $_SERVER['REMOTE_ADDR'] . 'personal_salt' );
    
    $click_data = array(
        'operator_id'  => absint( $operator_id ),
        'page_url'     => isset( $data['page_url'] ) ? esc_url_raw( $data['page_url'] ) : '',
        'click_url'    => isset( $data['click_url'] ) ? esc_url_raw( $data['click_url'] ) : '',
        'clicked_at'   => current_time( 'mysql' ),
        'ip_hash'      => $ip_hash,
        'user_agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 500 ) : '',
        'referer'      => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '',
    );
    
    $format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' );
    
    return $wpdb->insert( $table_name, $click_data, $format );
}

/**
 * Mark click as converted
 * 
 * @since 1.0.0
 * 
 * @param int $click_id Click ID
 * @return bool Success status
 */
function oc_mark_click_converted( $click_id ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_affiliate_clicks';
    
    return $wpdb->update(
        $table_name,
        array( 'converted' => 1 ),
        array( 'id' => absint( $click_id ) ),
        array( '%d' ),
        array( '%d' )
    );
}

/**
 * Get conversion stats for an operator
 * 
 * @since 1.0.0
 * 
 * @param int    $operator_id Operator post ID
 * @param string $period      Time period (day, week, month, year)
 * @return array Stats data
 */
function oc_get_conversion_stats( $operator_id, $period = 'month' ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_affiliate_clicks';
    
    // Calculate date range
    $now = current_time( 'mysql' );
    
    switch ( $period ) {
        case 'day':
            $start_date = date( 'Y-m-d 00:00:00', strtotime( '-1 day' ) );
            break;
        case 'week':
            $start_date = date( 'Y-m-d 00:00:00', strtotime( '-1 week' ) );
            break;
        case 'month':
            $start_date = date( 'Y-m-d 00:00:00', strtotime( '-1 month' ) );
            break;
        case 'year':
            $start_date = date( 'Y-m-d 00:00:00', strtotime( '-1 year' ) );
            break;
        default:
            $start_date = date( 'Y-m-d 00:00:00', strtotime( '-1 month' ) );
    }
    
    // Get stats
    $total_clicks = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_name} 
         WHERE operator_id = %d AND clicked_at >= %s",
        absint( $operator_id ),
        $start_date
    ) );
    
    $converted = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_name} 
         WHERE operator_id = %d AND clicked_at >= %s AND converted = 1",
        absint( $operator_id ),
        $start_date
    ) );
    
    return array(
        'clicks'     => (int) $total_clicks,
        'converted'  => (int) $converted,
        'rate'       => $total_clicks > 0 ? round( ( $converted / $total_clicks ) * 100, 2 ) : 0,
        'period'     => $period,
        'start_date' => $start_date,
        'end_date'   => $now,
    );
}

/**
 * Bulk insert odds data
 * 
 * @since 1.0.0
 * 
 * @param array $odds_array Array of odds data arrays
 * @return int Number of records inserted/updated
 */
function oc_bulk_insert_odds( $odds_array ) {
    global $wpdb;
    
    if ( empty( $odds_array ) ) {
        return 0;
    }
    
    $count = 0;
    
    foreach ( $odds_array as $odds_data ) {
        if ( oc_insert_odds( $odds_data ) ) {
            $count++;
        }
    }
    
    return $count;
}

/**
 * Check if database tables exist
 * 
 * @since 1.0.0
 * 
 * @return bool True if tables exist
 */
function oc_check_database_tables() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_match_odds';
    
    $result = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
    
    return $table_name === $result;
}

/**
 * Get database table status
 * 
 * @since 1.0.0
 * 
 * @return array Table status information
 */
function oc_get_database_status() {
    global $wpdb;
    
    $tables = array(
        $wpdb->prefix . 'oc_match_odds',
        $wpdb->prefix . 'oc_affiliate_clicks',
        $wpdb->prefix . 'oc_operator_ratings',
    );
    
    $status = array();
    
    foreach ( $tables as $table ) {
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
        
        $status[ $table ] = array(
            'rows'      => (int) $count,
            'exists'    => true,
        );
    }
    
    return $status;
}

/**
 * Clean old odds data
 * 
 * @since 1.0.0
 * 
 * @param int $days_old Delete odds older than this many days
 * @return int Number of records deleted
 */
function oc_clean_old_odds( $days_old = 30 ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'oc_match_odds';
    
    $deleted = $wpdb->query( $wpdb->prepare(
        "DELETE FROM {$table_name} WHERE last_updated < DATE_SUB(NOW(), INTERVAL %d DAY)",
        absint( $days_old )
    ) );
    
    return $deleted;
}

/**
 * Optimize database tables
 * 
 * @since 1.0.0
 * 
 * @return array Optimization results
 */
function oc_optimize_tables() {
    global $wpdb;
    
    $tables = array(
        $wpdb->prefix . 'oc_match_odds',
        $wpdb->prefix . 'oc_affiliate_clicks',
        $wpdb->prefix . 'oc_operator_ratings',
    );
    
    $results = array();
    
    foreach ( $tables as $table ) {
        $results[ $table ] = $wpdb->query( "OPTIMIZE TABLE {$table}" );
    }
    
    return $results;
}

// Run on theme activation
add_action( 'after_switch_theme', 'oc_create_database_tables' );

// Run on theme deactivation to clean up
add_action( 'switch_theme', 'oc_drop_database_tables' );

