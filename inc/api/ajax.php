<?php
/**
 * AJAX Handlers
 *
 * Handles all AJAX requests for odds loading, filtering, and affiliate tracking.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register AJAX actions
 *
 * @since 1.0.0
 */
function oc_register_ajax_actions() {
    // Public AJAX actions
    add_action( 'wp_ajax_oc_load_odds', 'oc_ajax_load_odds' );
    add_action( 'wp_ajax_nopriv_oc_load_odds', 'oc_ajax_load_odds' );
    
    add_action( 'wp_ajax_oc_search_matches', 'oc_ajax_search_matches' );
    add_action( 'wp_ajax_nopriv_oc_search_matches', 'oc_ajax_search_matches' );
    
    add_action( 'wp_ajax_oc_filter_operators', 'oc_ajax_filter_operators' );
    add_action( 'wp_ajax_nopriv_oc_filter_operators', 'oc_ajax_filter_operators' );
    
    add_action( 'wp_ajax_oc_track_click', 'oc_ajax_track_click' );
    add_action( 'wp_ajax_nopriv_oc_track_click', 'oc_ajax_track_click' );
    
    add_action( 'wp_ajax_oc_load_more_matches', 'oc_ajax_load_more_matches' );
    add_action( 'wp_ajax_nopriv_oc_load_more_matches', 'oc_ajax_load_more_matches' );
    
    add_action( 'wp_ajax_oc_get_odds_history', 'oc_ajax_get_odds_history' );
    add_action( 'wp_ajax_nopriv_oc_get_odds_history', 'oc_ajax_get_odds_history' );
    
    // Live matches AJAX actions
    add_action( 'wp_ajax_oc_load_live_matches', 'oc_ajax_load_live_matches' );
    add_action( 'wp_ajax_nopriv_oc_load_live_matches', 'oc_ajax_load_live_matches' );
    
    add_action( 'wp_ajax_oc_refresh_odds', 'oc_ajax_refresh_odds' );
    add_action( 'wp_ajax_nopriv_oc_refresh_odds', 'oc_ajax_refresh_odds' );
    
    add_action( 'wp_ajax_oc_filter_matches', 'oc_ajax_filter_matches' );
    add_action( 'wp_ajax_nopriv_oc_filter_matches', 'oc_ajax_filter_matches' );

add_action( 'wp_ajax_oc_save_quota_format', 'oc_ajax_save_quota_format' );
    add_action( 'wp_ajax_nopriv_oc_save_quota_format', 'oc_ajax_save_quota_format' );
    
    // Comparison tool AJAX actions
    add_action( 'wp_ajax_oc_load_comparison_operators', 'oc_ajax_load_comparison_operators' );
    add_action( 'wp_ajax_nopriv_oc_load_comparison_operators', 'oc_ajax_load_comparison_operators' );

    // Save odds AJAX action
    add_action( 'wp_ajax_oc_save_odds', 'oc_ajax_save_odds' );
}
add_action( 'init', 'oc_register_ajax_actions' );

/**
 * AJAX handler for loading odds
 *
 * @since 1.0.0
 */
function oc_ajax_load_odds() {
    check_ajax_referer( 'oc_ajax_nonce', 'nonce' );
    
    $match_id = isset( $_POST['match_id'] ) ? absint( $_POST['match_id'] ) : 0;
    $operator_ids = isset( $_POST['operator_ids'] ) ? array_map( 'absint', $_POST['operator_ids'] ) : array();
    $sort_by = isset( $_POST['sort_by'] ) ? sanitize_text_field( $_POST['sort_by'] ) : 'rating';
    $market_type = isset( $_POST['market_type'] ) ? sanitize_text_field( $_POST['market_type'] ) : 'all';
    
    if ( ! $match_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid match ID.', 'odds-comparison' ) ) );
    }
    
    $odds = oc_get_match_odds( $match_id, $operator_ids );
    
    // Filter by market type if specified
    if ( $market_type !== 'all' ) {
        $odds = array_filter( $odds, function( $odd ) use ( $market_type ) {
            switch ( $market_type ) {
                case 'home':
                    return $odd['odds_home'] > 0;
                case 'draw':
                    return $odd['odds_draw'] > 0;
                case 'away':
                    return $odd['odds_away'] > 0;
                default:
                    return true;
            }
        } );
    }
    
    // Sort odds
    usort( $odds, function( $a, $b ) use ( $sort_by ) {
        switch ( $sort_by ) {
            case 'rating':
                $rating_a = get_post_meta( $a['bookmaker_id'], 'oc_operator_rating', true );
                $rating_b = get_post_meta( $b['bookmaker_id'], 'oc_operator_rating', true );
                return floatval( $rating_b ) - floatval( $rating_a );
            case 'bonus':
                $bonus_a = get_post_meta( $a['bookmaker_id'], 'oc_bonus_amount', true );
                $bonus_b = get_post_meta( $b['bookmaker_id'], 'oc_bonus_amount', true );
                return strlen( $bonus_b ) - strlen( $bonus_a );
            case 'name':
                $name_a = get_the_title( $a['bookmaker_id'] );
                $name_b = get_the_title( $b['bookmaker_id'] );
                return strcmp( $name_a, $name_b );
            case 'odds_high':
                $max_a = max( floatval( $a['odds_home'] ), floatval( $a['odds_draw'] ), floatval( $a['odds_away'] ) );
                $max_b = max( floatval( $b['odds_home'] ), floatval( $b['odds_draw'] ), floatval( $b['odds_away'] ) );
                return $max_b - $max_a;
            case 'odds_low':
            default:
                $min_a = min( floatval( $a['odds_home'] ), floatval( $a['odds_draw'] ), floatval( $a['odds_away'] ) );
                $min_b = min( floatval( $b['odds_home'] ), floatval( $b['odds_draw'] ), floatval( $b['odds_away'] ) );
                return $min_a - $min_b;
        }
    } );
    
    // Enhance odds data with operator info
    $enhanced_odds = array();
    foreach ( $odds as $odd ) {
        $operator = get_post( $odd['bookmaker_id'] );
        if ( ! $operator ) {
            continue;
        }
        
        $enhanced_odd = array(
            'id'           => $odd['id'],
            'bookmaker_id' => $odd['bookmaker_id'],
            'bookmaker_name' => $operator->post_title,
            'bookmaker_slug' => $operator->post_name,
            'odds_home'    => $odd['odds_home'],
            'odds_draw'    => $odd['odds_draw'],
            'odds_away'    => $odd['odds_away'],
            'last_updated' => $odd['last_updated'],
            'operator'     => array(
                'rating'       => get_post_meta( $operator->ID, 'oc_operator_rating', true ),
                'bonus'        => get_post_meta( $operator->ID, 'oc_bonus_amount', true ),
                'affiliate_url' => get_post_meta( $operator->ID, 'oc_affiliate_url', true ),
                'logo'         => get_the_post_thumbnail_url( $operator->ID, 'thumbnail' ),
            ),
        );
        
        $enhanced_odds[] = $enhanced_odd;
    }
    
    wp_send_json_success( array(
        'odds'      => $enhanced_odds,
        'total'     => count( $enhanced_odds ),
        'sorted_by' => $sort_by,
    ) );
}

/**
 * AJAX handler for searching matches
 *
 * @since 1.0.0
 */
function oc_ajax_search_matches() {
    check_ajax_referer( 'oc_ajax_nonce', 'nonce' );
    
    $search_term = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
    $sport = isset( $_POST['sport'] ) ? sanitize_text_field( $_POST['sport'] ) : '';
    $league = isset( $_POST['league'] ) ? sanitize_text_field( $_POST['league'] ) : '';
    $limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 10;
    
    $args = array(
        'post_type'      => 'match',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        's'              => $search_term,
    );
    
    if ( $sport ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'sport',
            'field'    => 'slug',
            'terms'    => $sport,
        );
    }
    
    if ( $league ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'league',
            'field'    => 'slug',
            'terms'    => $league,
        );
    }
    
    $matches = get_posts( $args );
    $results = array();
    
    foreach ( $matches as $match ) {
        $results[] = array(
            'id'       => $match->ID,
            'title'    => $match->post_title,
            'slug'     => $match->post_name,
            'date'     => get_post_meta( $match->ID, 'oc_match_date', true ),
            'time'     => get_post_meta( $match->ID, 'oc_match_time', true ),
            'home_team' => get_post_meta( $match->ID, 'oc_home_team', true ),
            'away_team' => get_post_meta( $match->ID, 'oc_away_team', true ),
            'url'      => get_permalink( $match->ID ),
        );
    }
    
    wp_send_json_success( array(
        'matches' => $results,
        'total'   => count( $results ),
    ) );
}

/**
 * AJAX handler for filtering operators
 *
 * @since 1.0.0
 */
function oc_ajax_filter_operators() {
    check_ajax_referer( 'oc_ajax_nonce', 'nonce' );
    
    $rating_min = isset( $_POST['rating_min'] ) ? floatval( $_POST['rating_min'] ) : 0;
    $bonus_type = isset( $_POST['bonus_type'] ) ? sanitize_text_field( $_POST['bonus_type'] ) : '';
    $license = isset( $_POST['license'] ) ? sanitize_text_field( $_POST['license'] ) : '';
    $has_live_betting = isset( $_POST['live_betting'] ) ? (bool) $_POST['live_betting'] : false;
    $featured_only = isset( $_POST['featured'] ) ? (bool) $_POST['featured'] : false;
    $limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 10;
    
    $args = array(
        'post_type'      => 'operator',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'meta_query'     => array(),
        'tax_query'      => array(),
    );
    
    if ( $rating_min > 0 ) {
        $args['meta_query'][] = array(
            'key'     => 'oc_operator_rating',
            'value'   => $rating_min,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        );
    }
    
    if ( $bonus_type ) {
        $args['meta_query'][] = array(
            'key'   => 'oc_bonus_type',
            'value' => $bonus_type,
        );
    }
    
    if ( $license ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'license',
            'field'    => 'slug',
            'terms'    => $license,
        );
    }
    
    if ( $has_live_betting ) {
        $args['meta_query'][] = array(
            'key'   => 'oc_live_betting',
            'value' => '1',
        );
    }
    
    if ( $featured_only ) {
        $args['meta_query'][] = array(
            'key'   => 'oc_featured_operator',
            'value' => '1',
        );
    }
    
    $operators = get_posts( $args );
    $results = array();
    
    foreach ( $operators as $op ) {
        $results[] = array(
            'id'           => $op->ID,
            'name'         => $op->post_title,
            'slug'         => $op->post_name,
            'rating'       => get_post_meta( $op->ID, 'oc_operator_rating', true ),
            'bonus'        => get_post_meta( $op->ID, 'oc_bonus_amount', true ),
            'bonus_type'   => get_post_meta( $op->ID, 'oc_bonus_type', true ),
            'affiliate_url' => get_post_meta( $op->ID, 'oc_affiliate_url', true ),
            'logo'         => get_the_post_thumbnail_url( $op->ID, 'thumbnail' ),
            'pros'         => get_post_meta( $op->ID, 'oc_operator_pros', true ),
            'cons'         => get_post_meta( $op->ID, 'oc_operator_cons', true ),
            'url'          => get_permalink( $op->ID ),
        );
    }
    
    wp_send_json_success( array(
        'operators' => $results,
        'total'     => count( $results ),
    ) );
}

/**
 * AJAX handler for tracking affiliate clicks
 *
 * @since 1.0.0
 */
function oc_ajax_track_click() {
    check_ajax_referer( 'oc_ajax_nonce', 'nonce' );
    
    $operator_id = isset( $_POST['operator_id'] ) ? absint( $_POST['operator_id'] ) : 0;
    $match_id = isset( $_POST['match_id'] ) ? absint( $_POST['match_id'] ) : 0;
    $bet_type = isset( $_POST['bet_type'] ) ? sanitize_text_field( $_POST['bet_type'] ) : '';
    
    if ( ! $operator_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid operator.', 'odds-comparison' ) ) );
    }
    
    // Track the click
    $click_id = oc_track_affiliate_click( $operator_id, $match_id, $bet_type );
    
    // Get affiliate URL
    $affiliate_url = get_post_meta( $operator_id, 'oc_affiliate_url', true );
    
    if ( ! $affiliate_url ) {
        wp_send_json_error( array( 'message' => __( 'No affiliate URL configured.', 'odds-comparison' ) ) );
    }
    
    // Add click ID to URL for tracking
    $tracked_url = add_query_arg( 'oc_click_id', $click_id, $affiliate_url );
    
    wp_send_json_success( array(
        'click_id'   => $click_id,
        'redirect_url' => $tracked_url,
    ) );
}

/**
 * AJAX handler for loading more matches
 *
 * @since 1.0.0
 */
function oc_ajax_load_more_matches() {
    check_ajax_referer( 'oc_ajax_nonce', 'nonce' );
    
    $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
    $posts_per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 10;
    $sport = isset( $_POST['sport'] ) ? sanitize_text_field( $_POST['sport'] ) : '';
    $league = isset( $_POST['league'] ) ? sanitize_text_field( $_POST['league'] ) : '';
    $sort = isset( $_POST['sort'] ) ? sanitize_text_field( $_POST['sort'] ) : 'date';
    
    $args = array(
        'post_type'      => 'match',
        'post_status'    => 'publish',
        'paged'          => $page,
        'posts_per_page' => $posts_per_page,
    );
    
    if ( $sport ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'sport',
            'field'    => 'slug',
            'terms'    => $sport,
        );
    }
    
    if ( $league ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'league',
            'field'    => 'slug',
            'terms'    => $league,
        );
    }
    
    // Sort by date or custom field
    if ( 'live' === $sort ) {
        $args['meta_key'] = 'oc_live_match';
        $args['orderby'] = 'meta_value';
        $args['order'] = 'DESC';
    } elseif ( 'featured' === $sort ) {
        $args['meta_key'] = 'oc_featured_match';
        $args['orderby'] = 'meta_value';
        $args['order'] = 'DESC';
    } else {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    }
    
    $query = new WP_Query( $args );
    $matches = array();
    
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            
            $match_id = get_the_ID();
            $odds = oc_get_match_odds( $match_id );
            $best_odds = oc_get_best_odds( $odds );
            
            $matches[] = array(
                'id'         => $match_id,
                'title'      => get_the_title(),
                'slug'       => get_post_field( 'post_name' ),
                'excerpt'    => get_the_excerpt(),
                'date'       => get_the_date(),
                'url'        => get_permalink(),
                'home_team'  => get_post_meta( $match_id, 'oc_home_team', true ),
                'away_team'  => get_post_meta( $match_id, 'oc_away_team', true ),
                'match_date' => get_post_meta( $match_id, 'oc_match_date', true ),
                'match_time' => get_post_meta( $match_id, 'oc_match_time', true ),
                'is_live'    => get_post_meta( $match_id, 'oc_live_match', true ),
                'is_featured' => get_post_meta( $match_id, 'oc_featured_match', true ),
                'best_odds'  => $best_odds,
                'odds_count' => count( $odds ),
            );
        }
        wp_reset_postdata();
    }
    
    wp_send_json_success( array(
        'matches'     => $matches,
        'total'       => $query->found_posts,
        'current_page' => $page,
        'total_pages' => $query->max_num_pages,
        'has_more'    => $page < $query->max_num_pages,
    ) );
}

/**
 * AJAX handler for getting odds history
 *
 * @since 1.0.0
 */
function oc_ajax_get_odds_history() {
    check_ajax_referer( 'oc_ajax_nonce', 'nonce' );
    
    $match_id = isset( $_POST['match_id'] ) ? absint( $_POST['match_id'] ) : 0;
    $bookmaker_id = isset( $_POST['bookmaker_id'] ) ? absint( $_POST['bookmaker_id'] ) : 0;
    $days = isset( $_POST['days'] ) ? absint( $_POST['days'] ) : 7;
    
    if ( ! $match_id || ! $bookmaker_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid parameters.', 'odds-comparison' ) ) );
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'oc_odds_history';
    
    $history = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$table_name}
         WHERE match_id = %d AND bookmaker_id = %d
         AND recorded_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
         ORDER BY recorded_at ASC",
        $match_id,
        $bookmaker_id,
        $days
    ), ARRAY_A );
    
    // Format history data for chart
    $chart_data = array(
        'labels'   => array(),
        'home'     => array(),
        'draw'     => array(),
        'away'     => array(),
    );
    
    foreach ( $history as $entry ) {
        $chart_data['labels'][] = date_i18n( 'm/d H:i', strtotime( $entry['recorded_at'] ) );
        $chart_data['home'][] = floatval( $entry['odds_home'] );
        $chart_data['draw'][] = floatval( $entry['odds_draw'] );
        $chart_data['away'][] = floatval( $entry['odds_away'] );
    }
    
    wp_send_json_success( array(
        'history'   => $history,
        'chart_data' => $chart_data,
    ) );
}

/**
 * AJAX handler for loading live matches list
 *
 * Returns matches grouped by date and league for the live matches UI.
 *
 * @since 1.0.0
 */
function oc_ajax_load_live_matches() {
    check_ajax_referer( 'oc_ajax_nonce', 'nonce' );
    
    $sport = isset( $_POST['sport'] ) ? sanitize_text_field( $_POST['sport'] ) : '';
    $league = isset( $_POST['league'] ) ? sanitize_text_field( $_POST['league'] ) : '';
    $limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 50;
    
    $args = array(
        'posts_per_page' => $limit,
    );
    
    if ( $sport ) {
        $args['sport'] = $sport;
    }
    
    if ( $league ) {
        $args['league'] = $league;
    }
    
    $matches_data = oc_get_live_matches_list( $args );
    
    // Generate HTML for the matches
    $html = oc_generate_matches_html( $matches_data['date_groups'] );
    
    wp_send_json_success( array(
        'html'           => $html,
        'date_groups'    => $matches_data['date_groups'],
        'total_matches'  => $matches_data['total_matches'],
        'last_updated'   => current_time( 'mysql' ),
    ) );
}

/**
 * AJAX handler for refreshing odds for specific matches
 *
 * Returns updated odds data for the specified match IDs.
 *
 * @since 1.0.0
 */
function oc_ajax_refresh_odds() {
    check_ajax_referer( 'oc_ajax_nonce', 'nonce' );
    
    $match_ids = isset( $_POST['match_ids'] ) ? array_map( 'absint', $_POST['match_ids'] ) : array();
    
    if ( empty( $match_ids ) ) {
        wp_send_json_error( array( 'message' => __( 'No match IDs provided.', 'odds-comparison' ) ) );
    }
    
    $odds_data = array();
    
    foreach ( $match_ids as $match_id ) {
        $odds_data[ $match_id ] = oc_get_match_live_odds( $match_id );
    }
    
    wp_send_json_success( array(
        'odds'         => $odds_data,
        'last_updated' => current_time( 'mysql' ),
    ) );
}

/**
 * AJAX handler for filtering matches
 *
 * Returns filtered matches based on date and league criteria.
 *
 * @since 1.0.0
 */
function oc_ajax_filter_matches() {
    check_ajax_referer( 'oc_ajax_nonce', 'nonce' );
    
    $date_filter = isset( $_POST['date_filter'] ) ? sanitize_text_field( $_POST['date_filter'] ) : 'all';
    $league = isset( $_POST['league'] ) ? sanitize_text_field( $_POST['league'] ) : '';
    $sport = isset( $_POST['sport'] ) ? sanitize_text_field( $_POST['sport'] ) : '';
    $limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 50;
    
    $args = array(
        'posts_per_page' => $limit,
    );
    
    if ( $sport ) {
        $args['sport'] = $sport;
    }
    
    if ( $league ) {
        $args['league'] = $league;
    }
    
    // Get all matches first
    $all_matches = oc_get_live_matches_list( $args );
    $date_groups = $all_matches['date_groups'];
    
    // Apply date filter
    if ( 'all' !== $date_filter ) {
        $filtered_groups = array();
        
        foreach ( $date_groups as $group ) {
            $filtered_leagues = array();
            
            foreach ( $group['leagues'] as $league_key => $league_data ) {
                $filtered_matches = array();
                
                foreach ( $league_data['matches'] as $match ) {
                    $match_date = get_post_meta( $match['id'], 'oc_match_date', true );
                    $today = date( 'Y-m-d' );
                    $tomorrow = date( 'Y-m-d', strtotime( '+1 day' ) );
                    
                    $include_match = false;
                    
                    switch ( $date_filter ) {
                        case 'today':
                            $include_match = ( $match_date === $today );
                            break;
                        case 'tomorrow':
                            $include_match = ( $match_date === $tomorrow );
                            break;
                        case 'upcoming':
                            $include_match = ( $match_date > $tomorrow );
                            break;
                    }
                    
                    if ( $include_match ) {
                        $filtered_matches[] = $match;
                    }
                }
                
                if ( ! empty( $filtered_matches ) ) {
                    $filtered_leagues[ $league_key ] = array(
                        'id'      => $league_data['id'],
                        'name'    => $league_data['name'],
                        'matches' => $filtered_matches,
                    );
                }
            }
            
            if ( ! empty( $filtered_leagues ) ) {
                $filtered_groups[] = array(
                    'key'     => $group['key'],
                    'label'   => $group['label'],
                    'leagues' => $filtered_leagues,
                );
            }
        }
        
        $date_groups = $filtered_groups;
    }
    
    // Generate HTML
    $html = oc_generate_matches_html( $date_groups );
    
    $total_matches = 0;
    foreach ( $date_groups as $group ) {
        foreach ( $group['leagues'] as $league_data ) {
            $total_matches += count( $league_data['matches'] );
        }
    }
    
    wp_send_json_success( array(
        'html'          => $html,
        'date_groups'   => $date_groups,
        'total_matches' => $total_matches,
        'last_updated'  => current_time( 'mysql' ),
    ) );
}

/**
 * Generate HTML for matches list
 *
 * @since 1.0.0
 *
 * @param array $date_groups Date groups with leagues and matches
 * @return string HTML output
 */
function oc_generate_matches_html( $date_groups ) {
    if ( empty( $date_groups ) ) {
        return '<div class="oc-matches-empty"><p>' . esc_html__( 'No matches found.', 'odds-comparison' ) . '</p></div>';
    }
    
    ob_start();
    
    foreach ( $date_groups as $group ) :
        ?>
        <div class="oc-matches-date-group" data-date="<?php echo esc_attr( $group['key'] ); ?>">
            <h3 class="oc-date-label"><?php echo esc_html( $group['label'] ); ?></h3>
            
            <?php foreach ( $group['leagues'] as $league ) : ?>
                <div class="oc-matches-league-group" data-league="<?php echo esc_attr( $league['id'] ); ?>">
                    <h4 class="oc-league-label"><?php echo esc_html( $league['name'] ); ?></h4>
                    
                    <div class="oc-matches-list">
                        <?php foreach ( $league['matches'] as $match ) : ?>
                            <?php oc_render_match_row( $match ); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    endforeach;
    
    return ob_get_clean();
}

/**
 * Render a single match row for the live matches list
 *
 * @since 1.0.0
 *
 * @param array $match Match data
 */
function oc_render_match_row( $match ) {
    $odds = $match['odds'];
    $home_odds = $odds['home'] > 0 ? number_format( $odds['home'], 2 ) : '—';
    $draw_odds = $odds['draw'] > 0 ? number_format( $odds['draw'], 2 ) : '—';
    $away_odds = $odds['away'] > 0 ? number_format( $odds['away'], 2 ) : '—';
    ?>
    <div class="oc-match-row" data-match-id="<?php echo esc_attr( $match['id'] ); ?>">
        <div class="oc-match-time">
            <?php if ( ! empty( $match['is_live'] ) ) : ?>
                <span class="oc-live-indicator">LIVE</span>
            <?php endif; ?>
            <span class="oc-time-value"><?php echo esc_html( $match['time'] ); ?></span>
        </div>
        
        <div class="oc-team-cell oc-home-team">
            <?php if ( ! empty( $match['home_team']['logo'] ) ) : ?>
                <img src="<?php echo esc_url( $match['home_team']['logo'] ); ?>" 
                     alt="<?php echo esc_attr( $match['home_team']['name'] ); ?>"
                     class="oc-team-logo">
            <?php endif; ?>
            <span class="oc-team-name"><?php echo esc_html( $match['home_team']['name'] ); ?></span>
        </div>
        
        <div class="oc-odds-cell">
            <a href="#" class="oc-odd-btn oc-odd-home" data-match="<?php echo esc_attr( $match['id'] ); ?>" data-bet="home">
                <?php echo esc_html( $home_odds ); ?>
            </a>
            <a href="#" class="oc-odd-btn oc-odd-draw" data-match="<?php echo esc_attr( $match['id'] ); ?>" data-bet="draw">
                <?php echo esc_html( $draw_odds ); ?>
            </a>
            <a href="#" class="oc-odd-btn oc-odd-away" data-match="<?php echo esc_attr( $match['id'] ); ?>" data-bet="away">
                <?php echo esc_html( $away_odds ); ?>
            </a>
        </div>
        
        <div class="oc-team-cell oc-away-team">
            <?php if ( ! empty( $match['away_team']['logo'] ) ) : ?>
                <img src="<?php echo esc_url( $match['away_team']['logo'] ); ?>"
                     alt="<?php echo esc_attr( $match['away_team']['name'] ); ?>"
                     class="oc-team-logo">
            <?php endif; ?>
            <span class="oc-team-name"><?php echo esc_html( $match['away_team']['name'] ); ?></span>
        </div>
        
        <div class="oc-more-markets">
            <a href="<?php echo esc_url( $match['permalink'] ); ?>" class="oc-more-markets-btn">
                <?php esc_html_e( 'More Markets', 'odds-comparison' ); ?>
            </a>
        </div>
    </div>
    <?php
}

/**
 * AJAX handler for saving quota format preference
 *
 * Saves the user's preferred odds format (decimal, fractional, american).
 *
 * @since 1.0.0
 */
function oc_ajax_save_quota_format() {
    check_ajax_referer( 'oc_ajax_nonce', 'nonce' );

    $format = isset( $_POST['format'] ) ? sanitize_text_field( $_POST['format'] ) : 'decimal';

    // Validate format
    $valid_formats = array( 'decimal', 'fractional', 'american' );
    if ( ! in_array( $format, $valid_formats, true ) ) {
        $format = 'decimal';
    }

    // Save to user meta if logged in, otherwise use cookie
    if ( is_user_logged_in() ) {
        update_user_meta( get_current_user_id(), 'oc_quota_format', $format );
        wp_send_json_success( array(
            'format' => $format,
            'message' => __( 'Quota format saved.', 'odds-comparison' ),
        ) );
    } else {
        // Set cookie for 30 days
        setcookie( 'oc_quota_format', $format, time() + ( 30 * DAY_IN_SECONDS ), '/' );
        wp_send_json_success( array(
            'format' => $format,
            'message' => __( 'Quota format saved (browser).', 'odds-comparison' ),
        ) );
    }
}

/**
 * AJAX handler for loading comparison tool operators
 *
 * Returns filtered operators for the comparison tool with AJAX support.
 *
 * @since 1.0.0
 */
function oc_ajax_load_comparison_operators() {
    check_ajax_referer( 'oc_ajax_nonce', 'nonce' );
    
    $license = isset( $_POST['license'] ) ? sanitize_text_field( $_POST['license'] ) : '';
    $sport = isset( $_POST['sport'] ) ? sanitize_text_field( $_POST['sport'] ) : '';
    $sort_by = isset( $_POST['sort_by'] ) ? sanitize_text_field( $_POST['sort_by'] ) : 'rating';
    $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
    $per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 10;
    $search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
    
    // Build query args
    $args = array(
        'post_type'      => 'operator',
        'post_status'    => 'publish',
        'paged'          => $page,
        'posts_per_page' => $per_page,
        'meta_query'     => array(),
        'tax_query'      => array(),
    );
    
    // Add search
    if ( $search ) {
        $args['s'] = $search;
    }
    
    // Filter by license
    if ( $license && 'all' !== $license ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'license',
            'field'    => 'slug',
            'terms'    => $license,
        );
    }
    
    // Filter by sport (operators offering odds for this sport)
    if ( $sport && 'all' !== $sport ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'sport',
            'field'    => 'slug',
            'terms'    => $sport,
        );
    }
    
    // Sort by
    switch ( $sort_by ) {
        case 'rating':
            $args['meta_key'] = 'oc_operator_rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'name':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'bonus':
            $args['meta_key'] = 'oc_bonus_amount';
            $args['orderby'] = 'meta_value';
            $args['order'] = 'DESC';
            break;
        case 'newest':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        default:
            $args['meta_key'] = 'oc_operator_rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
    }
    
    $query = new WP_Query( $args );
    $operators = array();
    
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            
            $operator_id = get_the_ID();
            
            // Get operator data using oc_get_operator_data helper
            $operator_data = oc_get_operator_data( $operator_id );
            
            // Get licenses for display
            $licenses = get_the_terms( $operator_id, 'license' );
            if ( $licenses && ! is_wp_error( $licenses ) ) {
                $operator_data['license_term'] = $licenses[0];
            }
            
            // Get logo URL
            $operator_data['logo'] = get_the_post_thumbnail_url( $operator_id, 'thumbnail' );
            
            $operators[] = $operator_data;
        }
        wp_reset_postdata();
    }
    
    // Generate HTML cards using the helper function
    $html = '';
    foreach ( $operators as $operator ) {
        $html .= oc_render_comparison_card( $operator );
    }
    
    wp_send_json_success( array(
        'operators'     => $operators,
        'html'          => $html,
        'total'         => $query->found_posts,
        'current_page'  => $page,
        'total_pages'   => $query->max_num_pages,
        'has_more'      => $page < $query->max_num_pages,
        'per_page'      => $per_page,
    ) );
}

/**
 * AJAX handler for saving odds
 *
 * Saves odds data for a match via AJAX.
 *
 * @since 1.0.0
 */
function oc_ajax_save_odds() {
    check_ajax_referer( 'oc_ajax_nonce', 'nonce' );

    $match_id = isset( $_POST['match_id'] ) ? absint( $_POST['match_id'] ) : 0;
    $odds_data = isset( $_POST['odds_data'] ) ? $_POST['odds_data'] : array();

    if ( ! $match_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid match ID.', 'odds-comparison' ) ) );
    }

    if ( empty( $odds_data ) || ! is_array( $odds_data ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid odds data.', 'odds-comparison' ) ) );
    }

    // Save odds data
    $saved = oc_save_match_odds( $match_id, $odds_data );

    if ( $saved ) {
        wp_send_json_success( array(
            'message' => __( 'Odds saved successfully.', 'odds-comparison' ),
        ) );
    } else {
        wp_send_json_error( array( 'message' => __( 'Failed to save odds.', 'odds-comparison' ) ) );
    }
}

/**
 * Enqueue AJAX scripts
 *
 * @since 1.0.0
 */
function oc_enqueue_ajax_scripts() {
    wp_localize_script( 'oc-main', 'ocAjax', array(
        'ajaxurl'   => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'oc_ajax_nonce' ),
        'loading'   => __( 'Loading...', 'odds-comparison' ),
        'error'     => __( 'An error occurred. Please try again.', 'odds-comparison' ),
        'no_results' => __( 'No results found.', 'odds-comparison' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'oc_enqueue_ajax_scripts' );
