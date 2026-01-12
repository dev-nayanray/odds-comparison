<?php
/**
 * Betting Functions
 *
 * Handles user betting functionality including balance management,
 * bet placement, and transaction tracking.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get user's current balance
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID
 * @return float User balance
 */
function oc_get_user_balance( $user_id ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'oc_user_balance';

    $balance = $wpdb->get_var( $wpdb->prepare(
        "SELECT balance FROM {$table_name} WHERE user_id = %d",
        absint( $user_id )
    ) );

    return $balance ? floatval( $balance ) : 0.00;
}

/**
 * Update user's balance
 *
 * @since 1.0.0
 *
 * @param int   $user_id User ID
 * @param float $amount  Amount to add/subtract (negative for deduction)
 * @param string $reason Reason for balance change
 * @return bool Success status
 */
function oc_update_user_balance( $user_id, $amount, $reason = '' ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'oc_user_balance';
    $user_id = absint( $user_id );
    $amount = floatval( $amount );

    // Check if user has a balance record
    $existing_balance = $wpdb->get_var( $wpdb->prepare(
        "SELECT balance FROM {$table_name} WHERE user_id = %d",
        $user_id
    ) );

    if ( $existing_balance !== null ) {
        // Update existing balance
        $new_balance = floatval( $existing_balance ) + $amount;
        $result = $wpdb->update(
            $table_name,
            array( 'balance' => $new_balance, 'updated_at' => current_time( 'mysql' ) ),
            array( 'user_id' => $user_id ),
            array( '%f', '%s' ),
            array( '%d' )
        );
    } else {
        // Create new balance record
        $new_balance = $amount;
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'balance' => $new_balance,
                'created_at' => current_time( 'mysql' ),
                'updated_at' => current_time( 'mysql' )
            ),
            array( '%d', '%f', '%s', '%s' )
        );
    }

    if ( $result ) {
        // Log transaction
        oc_log_transaction( $user_id, $amount, $reason, $new_balance );
        return true;
    }

    return false;
}

/**
 * Place a bet
 *
 * @since 1.0.0
 *
 * @param int    $user_id    User ID
 * @param int    $match_id   Match post ID
 * @param string $bet_type   Type of bet (home, draw, away)
 * @param float  $stake      Bet amount
 * @param float  $odds       Odds for the bet
 * @return bool|int Bet ID on success, false on failure
 */
function oc_place_bet( $user_id, $match_id, $bet_type, $stake, $odds ) {
    global $wpdb;

    $user_id = absint( $user_id );
    $match_id = absint( $match_id );
    $stake = floatval( $stake );
    $odds = floatval( $odds );

    // Validate bet type
    $valid_types = array( 'home', 'draw', 'away' );
    if ( ! in_array( $bet_type, $valid_types ) ) {
        return false;
    }

    // Check if user has sufficient balance
    $current_balance = oc_get_user_balance( $user_id );
    if ( $current_balance < $stake ) {
        return false;
    }

    // Calculate potential win
    $potential_win = $stake * $odds;

    // Insert bet record
    $table_name = $wpdb->prefix . 'oc_user_bets';
    $result = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'match_id' => $match_id,
            'bet_type' => sanitize_text_field( $bet_type ),
            'stake' => $stake,
            'odds' => $odds,
            'potential_win' => $potential_win,
            'status' => 'pending',
            'placed_at' => current_time( 'mysql' )
        ),
        array( '%d', '%d', '%s', '%f', '%f', '%f', '%s', '%s' )
    );

    if ( $result ) {
        $bet_id = $wpdb->insert_id;

        // Deduct stake from balance
        oc_update_user_balance( $user_id, -$stake, 'Bet placed: ' . get_the_title( $match_id ) );

        return $bet_id;
    }

    return false;
}

/**
 * Log a transaction
 *
 * @since 1.0.0
 *
 * @param int    $user_id         User ID
 * @param float  $amount          Transaction amount
 * @param string $description     Transaction description
 * @param float  $balance_after   Balance after transaction
 * @param string $transaction_type Transaction type (deposit, withdrawal, bet, win, etc.)
 * @return bool Success status
 */
function oc_log_transaction( $user_id, $amount, $description, $balance_after, $transaction_type = 'adjustment' ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'oc_betting_transactions';

    return $wpdb->insert(
        $table_name,
        array(
            'user_id' => absint( $user_id ),
            'transaction_type' => sanitize_text_field( $transaction_type ),
            'amount' => floatval( $amount ),
            'balance_after' => floatval( $balance_after ),
            'description' => sanitize_text_field( $description ),
            'created_at' => current_time( 'mysql' )
        ),
        array( '%d', '%s', '%f', '%f', '%s', '%s' )
    );
}

/**
 * Get user's betting history
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID
 * @param array $args Query arguments
 * @return array Array of bet data
 */
function oc_get_user_bets( $user_id, $args = array() ) {
    global $wpdb;

    $defaults = array(
        'limit' => 50,
        'offset' => 0,
        'status' => '',
        'orderby' => 'placed_at',
        'order' => 'DESC',
    );

    $args = wp_parse_args( $args, $defaults );

    $table_name = $wpdb->prefix . 'oc_user_bets';

    $where = $wpdb->prepare( "WHERE user_id = %d", absint( $user_id ) );

    if ( ! empty( $args['status'] ) ) {
        $where .= $wpdb->prepare( " AND status = %s", $args['status'] );
    }

    $orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
    $limit = $wpdb->prepare( "LIMIT %d OFFSET %d", $args['limit'], $args['offset'] );

    $query = "SELECT * FROM {$table_name} {$where} ORDER BY {$orderby} {$limit}";

    $results = $wpdb->get_results( $query, ARRAY_A );

    return $results ? $results : array();
}

/**
 * Get user's transaction history
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID
 * @param array $args Query arguments
 * @return array Array of transaction data
 */
function oc_get_user_transactions( $user_id, $args = array() ) {
    global $wpdb;

    $defaults = array(
        'limit' => 50,
        'offset' => 0,
        'type' => '',
        'orderby' => 'created_at',
        'order' => 'DESC',
    );

    $args = wp_parse_args( $args, $defaults );

    $table_name = $wpdb->prefix . 'oc_betting_transactions';

    $where = $wpdb->prepare( "WHERE user_id = %d", absint( $user_id ) );

    if ( ! empty( $args['type'] ) ) {
        $where .= $wpdb->prepare( " AND transaction_type = %s", $args['type'] );
    }

    $orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
    $limit = $wpdb->prepare( "LIMIT %d OFFSET %d", $args['limit'], $args['offset'] );

    $query = "SELECT * FROM {$table_name} {$where} ORDER BY {$orderby} {$limit}";

    $results = $wpdb->get_results( $query, ARRAY_A );

    return $results ? $results : array();
}

/**
 * Update user profile settings
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID
 * @param array $data Profile data
 * @return bool Success status
 */
function oc_update_user_profile( $user_id, $data ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }

    if ( isset( $data['odds_format'] ) ) {
        update_user_meta( $user_id, 'oc_odds_format', sanitize_text_field( $data['odds_format'] ) );
    }

    return true;
}

/**
 * Handle profile update form submission
 *
 * @since 1.0.0
 */
function oc_handle_profile_update() {
    if ( isset( $_POST['oc_update_profile'] ) && wp_verify_nonce( $_POST['oc_profile_nonce'], 'oc_profile_update' ) ) {
        $user_id = get_current_user_id();

        $data = array(
            'odds_format' => sanitize_text_field( $_POST['odds_format'] ),
        );

        if ( oc_update_user_profile( $user_id, $data ) ) {
            set_transient( 'oc_profile_updated', __( 'Profile updated successfully.', 'odds-comparison' ), 30 );
        } else {
            set_transient( 'oc_profile_error', __( 'Failed to update profile.', 'odds-comparison' ), 30 );
        }

        wp_redirect( get_permalink() );
        exit;
    }
}
add_action( 'template_redirect', 'oc_handle_profile_update' );
