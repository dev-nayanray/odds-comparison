<?php
/**
 * AJAX Handlers
 *
 * Handles AJAX requests for betting functionality including
 * placing bets, getting user balance, and more.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Place a bet via AJAX
 *
 * @since 1.0.0
 */
function oc_ajax_place_bet() {
	// Check nonce
	check_ajax_referer( 'oc_ajax_nonce', 'nonce' );

	// Check if user is logged in
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array(
			'message' => __( 'Please log in to place a bet.', 'odds-comparison' )
		) );
	}

	$user_id = get_current_user_id();

	// Get and validate input data
	$match_id = isset( $_POST['match_id'] ) ? absint( $_POST['match_id'] ) : 0;
	$bet_type = isset( $_POST['bet_type'] ) ? sanitize_text_field( $_POST['bet_type'] ) : '';
	$stake = isset( $_POST['stake'] ) ? floatval( $_POST['stake'] ) : 0;
	$odds = isset( $_POST['odds'] ) ? floatval( $_POST['odds'] ) : 0;
	$bookmaker_id = isset( $_POST['bookmaker_id'] ) ? absint( $_POST['bookmaker_id'] ) : 0;

	// Validate required fields
	if ( ! $match_id || ! $bet_type || ! $stake || ! $odds ) {
		wp_send_json_error( array(
			'message' => __( 'Invalid bet data. Please try again.', 'odds-comparison' )
		) );
	}

	// Validate bet type
	$valid_types = array( 'home', 'draw', 'away', '1', 'X', '2' );
	if ( ! in_array( $bet_type, $valid_types ) ) {
		wp_send_json_error( array(
			'message' => __( 'Invalid bet type.', 'odds-comparison' )
		) );
	}

	// Validate stake amount
	if ( $stake < 0.10 ) {
		wp_send_json_error( array(
			'message' => __( 'Minimum stake is €0.10.', 'odds-comparison' )
		) );
	}

	// Get user balance
	$current_balance = oc_get_user_balance( $user_id );

	if ( $current_balance < $stake ) {
	 wp_send_json_error( array(
		'message' => __( 'Insufficient balance. Please deposit funds.', 'odds-comparison' ),
		'balance' => $current_balance,
		'required' => $stake
	 ) );
	}

	// Convert bet type if needed
	$normalized_bet_type = $bet_type;
	if ( $bet_type === '1' ) {
		$normalized_bet_type = 'home';
	} elseif ( $bet_type === 'X' || $bet_type === 'x' ) {
		$normalized_bet_type = 'draw';
	} elseif ( $bet_type === '2' ) {
		$normalized_bet_type = 'away';
	}

	// Place the bet
	$bet_id = oc_place_bet( $user_id, $match_id, $normalized_bet_type, $stake, $odds );

	if ( $bet_id ) {
		// Get updated balance
		$new_balance = oc_get_user_balance( $user_id );

		// Get match details
		$match_title = get_the_title( $match_id );

		// Calculate potential win
		$potential_win = $stake * $odds;

	 wp_send_json_success( array(
		'message' => __( 'Bet placed successfully!', 'odds-comparison' ),
		'bet_id' => $bet_id,
		'match' => $match_title,
		'bet_type' => $normalized_bet_type,
		'stake' => number_format( $stake, 2 ),
		'odds' => $odds,
		'potential_win' => number_format( $potential_win, 2 ),
		'new_balance' => number_format( $new_balance, 2 ),
		'balance_raw' => $new_balance
	 ) );
	} else {
	 wp_send_json_error( array(
		'message' => __( 'Failed to place bet. Please try again.', 'odds-comparison' )
	 ) );
	}
}
add_action( 'wp_ajax_oc_place_bet', 'oc_ajax_place_bet' );

/**
 * Get user balance via AJAX
 *
 * @since 1.0.0
 */
function oc_ajax_get_balance() {
	// Check nonce
	check_ajax_referer( 'oc_ajax_nonce', 'nonce' );

	// Check if user is logged in
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array(
			'message' => __( 'Please log in to view balance.', 'odds-comparison' )
		) );
	}

	$user_id = get_current_user_id();
	$balance = oc_get_user_balance( $user_id );

 wp_send_json_success( array(
	'balance' => number_format( $balance, 2 ),
	'balance_raw' => $balance
 ) );
}
add_action( 'wp_ajax_oc_get_balance', 'oc_ajax_get_balance' );

/**
 * Get coupon calculations via AJAX
 *
 * @since 1.0.0
 */
function oc_ajax_get_coupon_calculations() {
	// Check nonce
	check_ajax_referer( 'oc_ajax_nonce', 'nonce' );

	// Get posted data
	$bets = isset( $_POST['bets'] ) ? $_POST['bets'] : array();

	if ( empty( $bets ) || ! is_array( $bets ) ) {
	 wp_send_json_error( array(
		'message' => __( 'No bets in coupon.', 'odds-comparison' )
	 ) );
	}

	$total_odds = 1;
	$total_stake = 0;

	foreach ( $bets as $bet ) {
		$stake = floatval( $bet['stake'] );
		$odds = floatval( $bet['odds'] );

		$total_stake += $stake;
		$total_odds *= $odds;
	}

	$potential_win = $total_stake * $total_odds;

 wp_send_json_success( array(
	'total_stake' => number_format( $total_stake, 2 ),
	'total_odds' => number_format( $total_odds, 2 ),
	'potential_win' => number_format( $potential_win, 2 )
 ) );
}
add_action( 'wp_ajax_oc_get_coupon_calculations', 'oc_ajax_get_coupon_calculations' );

/**
 * Clear user coupon (localStorage sync)
 *
 * @since 1.0.0
 */
function oc_ajax_clear_coupon() {
	// Check nonce
	check_ajax_referer( 'oc_ajax_nonce', 'nonce' );

	// Check if user is logged in
	if ( ! is_user_logged_in() ) {
	 wp_send_json_error( array(
		'message' => __( 'Please log in.', 'odds-comparison' )
	 ) );
	}

	$user_id = get_current_user_id();

	// Get user's pending bets
	$user_bets = oc_get_user_bets( $user_id, array( 'status' => 'pending' ) );

	$pending_count = count( $user_bets );

 wp_send_json_success( array(
	'message' => __( 'Coupon cleared.', 'odds-comparison' ),
	'pending_bets_count' => $pending_count
 ) );
}
add_action( 'wp_ajax_oc_clear_coupon', 'oc_ajax_clear_coupon' );

/**
 * Get available bookmakers for betting
 *
 * @since 1.0.0
 */
function oc_ajax_get_bookmakers() {
	// Check nonce
	check_ajax_referer( 'oc_ajax_nonce', 'nonce' );

	$args = array(
		'post_type' => 'operator',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'meta_query' => array(
			array(
				'key' => 'oc_affiliate_url',
				'value' => '',
				'compare' => '!='
			)
		)
	);

	$operators = get_posts( $args );

	$bookmakers = array();

	foreach ( $operators as $operator ) {
		$rating = get_post_meta( $operator->ID, 'oc_operator_rating', true );
		$affiliate_url = get_post_meta( $operator->ID, 'oc_affiliate_url', true );
		$bonus_amount = get_post_meta( $operator->ID, 'oc_bonus_amount', true );

		$bookmakers[] = array(
			'id' => $operator->ID,
			'name' => $operator->post_title,
			'rating' => floatval( $rating ),
			'affiliate_url' => $affiliate_url,
			'bonus' => $bonus_amount,
			'logo' => get_the_post_thumbnail_url( $operator->ID, 'thumbnail' )
		);
	}

	// Sort by rating
	usort( $bookmakers, function( $a, $b ) {
		return $b['rating'] - $a['rating'];
	} );

 wp_send_json_success( array(
	'bookmakers' => $bookmakers
 ) );
}
add_action( 'wp_ajax_oc_get_bookmakers', 'oc_ajax_get_bookmakers' );
add_action( 'wp_ajax_nopriv_oc_get_bookmakers', 'oc_ajax_get_bookmakers' );

/**
 * Save odds for a match via AJAX
 *
 * @since 1.0.0
 */
function oc_ajax_save_odds() {
    // Check nonce
    check_ajax_referer( 'oc_ajax_nonce', 'nonce' );

    // Check user capabilities
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array(
            'message' => __( 'You do not have permission to save odds.', 'odds-comparison' )
        ) );
    }

    // Get and validate input data
    $match_id = isset( $_POST['match_id'] ) ? absint( $_POST['match_id'] ) : 0;

    // Reconstruct odds data from individual POST parameters
    $odds_data = array();
    if ( isset( $_POST['bookmaker_id'] ) ) {
        $odds_data[] = array(
            'bookmaker_id' => absint( $_POST['bookmaker_id'] ),
            'odds_home' => isset( $_POST['odds_home'] ) ? ( $_POST['odds_home'] ? floatval( $_POST['odds_home'] ) : null ) : null,
            'odds_draw' => isset( $_POST['odds_draw'] ) ? ( $_POST['odds_draw'] ? floatval( $_POST['odds_draw'] ) : null ) : null,
            'odds_away' => isset( $_POST['odds_away'] ) ? ( $_POST['odds_away'] ? floatval( $_POST['odds_away'] ) : null ) : null,
        );
    }

    // Validate required fields
    if ( ! $match_id ) {
        wp_send_json_error( array(
            'message' => __( 'Invalid match ID.', 'odds-comparison' )
        ) );
    }

    if ( empty( $odds_data ) || ! is_array( $odds_data ) ) {
        wp_send_json_error( array(
            'message' => __( 'No odds data provided.', 'odds-comparison' )
        ) );
    }

    // Validate odds data
    foreach ( $odds_data as $odds ) {
        if ( ! isset( $odds['bookmaker_id'] ) || ! absint( $odds['bookmaker_id'] ) ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid bookmaker ID.', 'odds-comparison' )
            ) );
        }

        // Check if at least one odds value is provided
        $has_odds = false;
        if ( isset( $odds['odds_home'] ) && floatval( $odds['odds_home'] ) > 0 ) $has_odds = true;
        if ( isset( $odds['odds_draw'] ) && floatval( $odds['odds_draw'] ) > 0 ) $has_odds = true;
        if ( isset( $odds['odds_away'] ) && floatval( $odds['odds_away'] ) > 0 ) $has_odds = true;

        if ( ! $has_odds ) {
            wp_send_json_error( array(
                'message' => __( 'At least one odds value must be provided.', 'odds-comparison' )
            ) );
        }
    }

    // Save odds data
    $saved = oc_save_match_odds( $match_id, $odds_data );

    if ( $saved ) {
        wp_send_json_success( array(
            'message' => __( 'Odds saved successfully!', 'odds-comparison' ),
            'match_id' => $match_id
        ) );
    } else {
        wp_send_json_error( array(
            'message' => __( 'Failed to save odds. Please try again.', 'odds-comparison' )
        ) );
    }
}
add_action( 'wp_ajax_oc_save_odds', 'oc_ajax_save_odds' );

