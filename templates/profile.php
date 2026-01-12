<?php
/**
 * Template Name: User Profile
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

// Check if user is logged in
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url( get_permalink() ) );
    exit;
}

$current_user = wp_get_current_user();
$user_balance = oc_get_user_balance( $current_user->ID );
$user_bets = oc_get_user_bets( $current_user->ID );
$user_transactions = oc_get_user_transactions( $current_user->ID );
?>

<div class="oc-profile-container">
    <div class="oc-profile-header">
        <h1><?php printf( esc_html__( 'Welcome, %s', 'odds-comparison' ), esc_html( $current_user->display_name ) ); ?></h1>
        <div class="oc-balance-display">
            <span class="oc-balance-label"><?php esc_html_e( 'Current Balance:', 'odds-comparison' ); ?></span>
            <span class="oc-balance-amount">€<?php echo number_format( $user_balance, 2 ); ?></span>
        </div>
    </div>

    <div class="oc-profile-tabs">
        <div class="oc-tab-buttons">
            <button class="oc-tab-button active" data-tab="bets"><?php esc_html_e( 'My Bets', 'odds-comparison' ); ?></button>
            <button class="oc-tab-button" data-tab="transactions"><?php esc_html_e( 'Transactions', 'odds-comparison' ); ?></button>
            <button class="oc-tab-button" data-tab="settings"><?php esc_html_e( 'Settings', 'odds-comparison' ); ?></button>
        </div>

        <div class="oc-tab-content">
            <!-- Bets Tab -->
            <div class="oc-tab-pane active" id="bets">
                <h2><?php esc_html_e( 'Betting History', 'odds-comparison' ); ?></h2>
                <?php if ( ! empty( $user_bets ) ) : ?>
                    <div class="oc-bets-table-container">
                        <table class="oc-bets-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Match', 'odds-comparison' ); ?></th>
                                    <th><?php esc_html_e( 'Bet Type', 'odds-comparison' ); ?></th>
                                    <th><?php esc_html_e( 'Stake', 'odds-comparison' ); ?></th>
                                    <th><?php esc_html_e( 'Odds', 'odds-comparison' ); ?></th>
                                    <th><?php esc_html_e( 'Potential Win', 'odds-comparison' ); ?></th>
                                    <th><?php esc_html_e( 'Status', 'odds-comparison' ); ?></th>
                                    <th><?php esc_html_e( 'Date', 'odds-comparison' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $user_bets as $bet ) : ?>
                                    <tr>
                                        <td><?php echo esc_html( get_the_title( $bet['match_id'] ) ); ?></td>
                                        <td><?php echo esc_html( $bet['bet_type'] ); ?></td>
                                        <td>€<?php echo number_format( $bet['stake'], 2 ); ?></td>
                                        <td><?php echo number_format( $bet['odds'], 2 ); ?></td>
                                        <td>€<?php echo number_format( $bet['potential_win'], 2 ); ?></td>
                                        <td>
                                            <span class="oc-bet-status status-<?php echo esc_attr( $bet['status'] ); ?>">
                                                <?php echo esc_html( ucfirst( $bet['status'] ) ); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $bet['placed_at'] ) ) ); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <p><?php esc_html_e( 'You haven\'t placed any bets yet.', 'odds-comparison' ); ?></p>
                <?php endif; ?>
            </div>

            <!-- Transactions Tab -->
            <div class="oc-tab-pane" id="transactions">
                <h2><?php esc_html_e( 'Transaction History', 'odds-comparison' ); ?></h2>
                <?php if ( ! empty( $user_transactions ) ) : ?>
                    <div class="oc-transactions-table-container">
                        <table class="oc-transactions-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Type', 'odds-comparison' ); ?></th>
                                    <th><?php esc_html_e( 'Amount', 'odds-comparison' ); ?></th>
                                    <th><?php esc_html_e( 'Balance After', 'odds-comparison' ); ?></th>
                                    <th><?php esc_html_e( 'Description', 'odds-comparison' ); ?></th>
                                    <th><?php esc_html_e( 'Date', 'odds-comparison' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $user_transactions as $transaction ) : ?>
                                    <tr>
                                        <td><?php echo esc_html( ucfirst( $transaction['transaction_type'] ) ); ?></td>
                                        <td class="<?php echo $transaction['amount'] >= 0 ? 'positive' : 'negative'; ?>">
                                            <?php echo $transaction['amount'] >= 0 ? '+' : ''; ?>€<?php echo number_format( $transaction['amount'], 2 ); ?>
                                        </td>
                                        <td>€<?php echo number_format( $transaction['balance_after'], 2 ); ?></td>
                                        <td><?php echo esc_html( $transaction['description'] ); ?></td>
                                        <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $transaction['created_at'] ) ) ); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <p><?php esc_html_e( 'No transactions found.', 'odds-comparison' ); ?></p>
                <?php endif; ?>
            </div>

            <!-- Settings Tab -->
            <div class="oc-tab-pane" id="settings">
                <h2><?php esc_html_e( 'Profile Settings', 'odds-comparison' ); ?></h2>
                <form method="post" class="oc-profile-form">
                    <?php wp_nonce_field( 'oc_profile_update', 'oc_profile_nonce' ); ?>
                    <div class="oc-form-group">
                        <label for="odds_format"><?php esc_html_e( 'Preferred Odds Format', 'odds-comparison' ); ?></label>
                        <select name="odds_format" id="odds_format">
                            <option value="decimal" <?php selected( get_user_meta( $current_user->ID, 'oc_odds_format', true ), 'decimal' ); ?>>
                                <?php esc_html_e( 'Decimal (2.10)', 'odds-comparison' ); ?>
                            </option>
                            <option value="fractional" <?php selected( get_user_meta( $current_user->ID, 'oc_odds_format', true ), 'fractional' ); ?>>
                                <?php esc_html_e( 'Fractional (11/10)', 'odds-comparison' ); ?>
                            </option>
                            <option value="american" <?php selected( get_user_meta( $current_user->ID, 'oc_odds_format', true ), 'american' ); ?>>
                                <?php esc_html_e( 'American (+110)', 'odds-comparison' ); ?>
                            </option>
                        </select>
                    </div>
                    <div class="oc-form-group">
                        <button type="submit" name="oc_update_profile" class="oc-submit-btn">
                            <?php esc_html_e( 'Update Settings', 'odds-comparison' ); ?>
                        </button>
                    </div>
                </form>

                <?php
                $profile_updated = get_transient( 'oc_profile_updated' );
                $profile_error = get_transient( 'oc_profile_error' );
                if ( $profile_updated ) {
                    echo '<div class="oc-notice success">' . esc_html( $profile_updated ) . '</div>';
                    delete_transient( 'oc_profile_updated' );
                }
                if ( $profile_error ) {
                    echo '<div class="oc-notice error">' . esc_html( $profile_error ) . '</div>';
                    delete_transient( 'oc_profile_error' );
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.oc-tab-button').on('click', function() {
        var tab = $(this).data('tab');

        $('.oc-tab-button').removeClass('active');
        $(this).addClass('active');

        $('.oc-tab-pane').removeClass('active');
        $('#' + tab).addClass('active');
    });
});
</script>

<?php get_footer(); ?>
