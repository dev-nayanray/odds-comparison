<?php
/**
 * Admin Menu
 *
 * Admin menu and submenu setup.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Add admin menu items
 *
 * @since 1.0.0
 */
function oc_add_admin_menu() {
    // Main menu item
    add_menu_page(
        __( 'Odds Comparison', 'odds-comparison' ),
        __( 'Odds Comparison', 'odds-comparison' ),
        'manage_options',
        'odds-comparison',
        'oc_settings_page',
        'dashicons-chart-line',
        30
    );
    
    // Dashboard submenu
    add_submenu_page(
        'odds-comparison',
        __( 'Dashboard', 'odds-comparison' ),
        __( 'Dashboard', 'odds-comparison' ),
        'manage_options',
        'odds-comparison',
        'oc_settings_page'
    );
    
    // Matches submenu
    add_submenu_page(
        'odds-comparison',
        __( 'Matches', 'odds-comparison' ),
        __( 'Matches', 'odds-comparison' ),
        'edit_posts',
        'edit.php?post_type=match'
    );
    
    // Operators submenu
    add_submenu_page(
        'odds-comparison',
        __( 'Operators', 'odds-comparison' ),
        __( 'Operators', 'odds-comparison' ),
        'edit_posts',
        'edit.php?post_type=operator'
    );
    
    // Odds submenu
    add_submenu_page(
        'odds-comparison',
        __( 'Manage Odds', 'odds-comparison' ),
        __( 'Manage Odds', 'odds-comparison' ),
        'manage_options',
        'oc-manage-odds',
        'oc_manage_odds_page'
    );
    
    // Settings submenu
    add_submenu_page(
        'odds-comparison',
        __( 'Settings', 'odds-comparison' ),
        __( 'Settings', 'odds-comparison' ),
        'manage_options',
        'oc-settings',
        'oc_settings_page'
    );
    
    // Import/Export submenu
    add_submenu_page(
        'odds-comparison',
        __( 'Import/Export', 'odds-comparison' ),
        __( 'Import/Export', 'odds-comparison' ),
        'manage_options',
        'oc-import-export',
        'oc_import_export_page'
    );
}
add_action( 'admin_menu', 'oc_add_admin_menu' );

/**
 * Settings page callback
 *
 * @since 1.0.0
 */
function oc_settings_page() {
    // Check capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'odds-comparison' ) );
    }
    
    // Save settings if form submitted
    if ( isset( $_POST['oc_save_settings'] ) && wp_verify_nonce( $_POST['oc_nonce'], 'oc_save_settings' ) ) {
        update_option( 'oc_default_currency', sanitize_text_field( $_POST['oc_default_currency'] ?? 'EUR' ) );
        update_option( 'oc_odds_decimal_places', absint( $_POST['oc_odds_decimal_places'] ?? 2 ) );
        update_option( 'oc_age_verification_required', sanitize_text_field( $_POST['oc_age_verification_required'] ?? 'no' ) );
        update_option( 'oc_show_live_matches_only', sanitize_text_field( $_POST['oc_show_live_matches_only'] ?? 'no' ) );
        update_option( 'oc_default_sort_order', sanitize_text_field( $_POST['oc_default_sort_order'] ?? 'rating' ) );
        update_option( 'oc_affiliate_disclaimer', wp_kses_post( $_POST['oc_affiliate_disclaimer'] ?? '' ) );
        
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved successfully.', 'odds-comparison' ) . '</p></div>';
    }
    
    // Get current settings
    $currency = get_option( 'oc_default_currency', 'EUR' );
    $decimal_places = get_option( 'oc_odds_decimal_places', 2 );
    $age_verification = get_option( 'oc_age_verification_required', 'no' );
    $live_only = get_option( 'oc_show_live_matches_only', 'no' );
    $sort_order = get_option( 'oc_default_sort_order', 'rating' );
    $disclaimer = get_option( 'oc_affiliate_disclaimer', '' );
    
    // Get stats
    $matches_count = wp_count_posts( 'match' )->publish;
    $operators_count = wp_count_posts( 'operator' )->publish;
    
    // Get odds count from database
    global $wpdb;
    $odds_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}oc_match_odds" );
    
    ?>
    <div class="wrap oc-admin-wrap">
        <h1><?php esc_html_e( 'Odds Comparison Settings', 'odds-comparison' ); ?></h1>
        
        <div class="oc-admin-dashboard">
            <div class="oc-stats-grid">
                <div class="oc-stat-card">
                    <span class="stat-number"><?php echo esc_html( $matches_count ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Matches', 'odds-comparison' ); ?></span>
                </div>
                <div class="oc-stat-card">
                    <span class="stat-number"><?php echo esc_html( $operators_count ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Operators', 'odds-comparison' ); ?></span>
                </div>
                <div class="oc-stat-card">
                    <span class="stat-number"><?php echo esc_html( $odds_count ?? 0 ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Odds Entries', 'odds-comparison' ); ?></span>
                </div>
            </div>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field( 'oc_save_settings', 'oc_nonce' ); ?>
            
            <div class="oc-settings-sections">
                <section class="oc-settings-section">
                    <h2><?php esc_html_e( 'General Settings', 'odds-comparison' ); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="oc_default_currency"><?php esc_html_e( 'Default Currency', 'odds-comparison' ); ?></label>
                            </th>
                            <td>
                                <select name="oc_default_currency" id="oc_default_currency">
                                    <option value="EUR" <?php selected( $currency, 'EUR' ); ?>>EUR (€)</option>
                                    <option value="USD" <?php selected( $currency, 'USD' ); ?>>USD ($)</option>
                                    <option value="GBP" <?php selected( $currency, 'GBP' ); ?>>GBP (£)</option>
                                    <option value="AUD" <?php selected( $currency, 'AUD' ); ?>>AUD ($)</option>
                                    <option value="CAD" <?php selected( $currency, 'CAD' ); ?>>CAD ($)</option>
                                </select>
                                <p class="description"><?php esc_html_e( 'Default currency for displaying odds and bonuses.', 'odds-comparison' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="oc_odds_decimal_places"><?php esc_html_e( 'Decimal Places', 'odds-comparison' ); ?></label>
                            </th>
                            <td>
                                <input type="number" name="oc_odds_decimal_places" id="oc_odds_decimal_places" 
                                       value="<?php echo esc_attr( $decimal_places ); ?>" min="0" max="4" step="1">
                                <p class="description"><?php esc_html_e( 'Number of decimal places for odds display.', 'odds-comparison' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </section>
                
                <section class="oc-settings-section">
                    <h2><?php esc_html_e( 'Display Options', 'odds-comparison' ); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="oc_default_sort_order"><?php esc_html_e( 'Default Sort Order', 'odds-comparison' ); ?></label>
                            </th>
                            <td>
                                <select name="oc_default_sort_order" id="oc_default_sort_order">
                                    <option value="rating" <?php selected( $sort_order, 'rating' ); ?>><?php esc_html_e( 'Rating', 'odds-comparison' ); ?></option>
                                    <option value="odds_high" <?php selected( $sort_order, 'odds_high' ); ?>><?php esc_html_e( 'Highest Odds', 'odds-comparison' ); ?></option>
                                    <option value="bonus" <?php selected( $sort_order, 'bonus' ); ?>><?php esc_html_e( 'Bonus Amount', 'odds-comparison' ); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e( 'Default sorting method for odds comparison.', 'odds-comparison' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="oc_age_verification_required"><?php esc_html_e( 'Age Verification', 'odds-comparison' ); ?></label>
                            </th>
                            <td>
                                <select name="oc_age_verification_required" id="oc_age_verification_required">
                                    <option value="no" <?php selected( $age_verification, 'no' ); ?>><?php esc_html_e( 'Disabled', 'odds-comparison' ); ?></option>
                                    <option value="yes" <?php selected( $age_verification, 'yes' ); ?>><?php esc_html_e( 'Enabled', 'odds-comparison' ); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e( 'Require age verification before showing odds.', 'odds-comparison' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </section>
                
                <section class="oc-settings-section">
                    <h2><?php esc_html_e( 'Affiliate Disclosure', 'odds-comparison' ); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="oc_affiliate_disclaimer"><?php esc_html_e( 'Disclaimer Text', 'odds-comparison' ); ?></label>
                            </th>
                            <td>
                                <?php
                                wp_editor(
                                    $disclaimer,
                                    'oc_affiliate_disclaimer',
                                    array(
                                        'textarea_rows' => 5,
                                        'media_buttons' => false,
                                    )
                                );
                                ?>
                                <p class="description"><?php esc_html_e( 'Text shown to users about affiliate links. Leave empty for default.', 'odds-comparison' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </section>
            </div>
            
            <?php submit_button( __( 'Save Settings', 'odds-comparison' ), 'primary large', 'oc_save_settings' ); ?>
        </form>
    </div>
    <?php
}

/**
 * Manage odds page callback
 *
 * @since 1.0.0
 */
function oc_manage_odds_page() {
    global $wpdb;
    
    // Handle form submissions
    if ( isset( $_POST['oc_add_odds'] ) && wp_verify_nonce( $_POST['oc_nonce'], 'oc_add_odds' ) ) {
        $match_id = absint( $_POST['match_id'] );
        $bookmaker_id = absint( $_POST['bookmaker_id'] );
        $odds_home = oc_sanitize_odds( $_POST['odds_home'] );
        $odds_draw = oc_sanitize_odds( $_POST['odds_draw'] );
        $odds_away = oc_sanitize_odds( $_POST['odds_away'] );
        
        $wpdb->insert(
            $wpdb->prefix . 'oc_match_odds',
            array(
                'match_id'     => $match_id,
                'bookmaker_id' => $bookmaker_id,
                'odds_home'    => $odds_home,
                'odds_draw'    => $odds_draw,
                'odds_away'    => $odds_away,
                'last_updated' => current_time( 'mysql' ),
            ),
            array( '%d', '%d', '%f', '%f', '%f', '%s' )
        );
        
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Odds added successfully.', 'odds-comparison' ) . '</p></div>';
    }
    
    // Handle deletion
    if ( isset( $_GET['delete_odds'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'delete_odds' ) ) {
        $odds_id = absint( $_GET['delete_odds'] );
        $wpdb->delete( $wpdb->prefix . 'oc_match_odds', array( 'id' => $odds_id ), array( '%d' ) );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Odds deleted.', 'odds-comparison' ) . '</p></div>';
    }
    
    // Get all odds with match and bookmaker info
    $odds = $wpdb->get_results(
        "SELECT o.*, m.post_title as match_name, b.post_title as bookmaker_name 
         FROM {$wpdb->prefix}oc_match_odds o 
         INNER JOIN {$wpdb->posts} m ON o.match_id = m.ID 
         INNER JOIN {$wpdb->posts} b ON o.bookmaker_id = b.ID 
         ORDER BY o.last_updated DESC 
         LIMIT 100",
        ARRAY_A
    );
    
    // Get matches for dropdown
    $matches = get_posts( array( 'post_type' => 'match', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
    
    // Get operators for dropdown
    $operators = get_posts( array( 'post_type' => 'operator', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
    
    ?>
    <div class="wrap oc-admin-wrap">
        <h1><?php esc_html_e( 'Manage Odds', 'odds-comparison' ); ?></h1>
        
        <div class="oc-manage-odds-grid">
            <div class="oc-add-odds-form">
                <h2><?php esc_html_e( 'Add New Odds', 'odds-comparison' ); ?></h2>
                
                <form method="post">
                    <?php wp_nonce_field( 'oc_add_odds', 'oc_nonce' ); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="match_id"><?php esc_html_e( 'Match', 'odds-comparison' ); ?></label>
                            </th>
                            <td>
                                <select name="match_id" id="match_id" required>
                                    <option value=""><?php esc_html_e( 'Select Match', 'odds-comparison' ); ?></option>
                                    <?php foreach ( $matches as $match ) : ?>
                                        <option value="<?php echo esc_attr( $match->ID ); ?>">
                                            <?php echo esc_html( get_post_meta( $match->ID, 'oc_home_team', true ) ); ?> vs 
                                            <?php echo esc_html( get_post_meta( $match->ID, 'oc_away_team', true ) ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="bookmaker_id"><?php esc_html_e( 'Bookmaker', 'odds-comparison' ); ?></label>
                            </th>
                            <td>
                                <select name="bookmaker_id" id="bookmaker_id" required>
                                    <option value=""><?php esc_html_e( 'Select Bookmaker', 'odds-comparison' ); ?></option>
                                    <?php foreach ( $operators as $operator ) : ?>
                                        <option value="<?php echo esc_attr( $operator->ID ); ?>">
                                            <?php echo esc_html( $operator->post_title ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="odds_home"><?php esc_html_e( 'Home Odds', 'odds-comparison' ); ?></label>
                            </th>
                            <td>
                                <input type="number" name="odds_home" id="odds_home" step="0.01" min="1.01" required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="odds_draw"><?php esc_html_e( 'Draw Odds', 'odds-comparison' ); ?></label>
                            </th>
                            <td>
                                <input type="number" name="odds_draw" id="odds_draw" step="0.01" min="1.01" required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="odds_away"><?php esc_html_e( 'Away Odds', 'odds-comparison' ); ?></label>
                            </th>
                            <td>
                                <input type="number" name="odds_away" id="odds_away" step="0.01" min="1.01" required>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button( __( 'Add Odds', 'odds-comparison' ), 'primary', 'oc_add_odds' ); ?>
                </form>
            </div>
            
            <div class="oc-odds-list">
                <h2><?php esc_html_e( 'Recent Odds', 'odds-comparison' ); ?></h2>
                
                <?php if ( empty( $odds ) ) : ?>
                    <p><?php esc_html_e( 'No odds entries found.', 'odds-comparison' ); ?></p>
                <?php else : ?>
                    <table class="widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Match', 'odds-comparison' ); ?></th>
                                <th><?php esc_html_e( 'Bookmaker', 'odds-comparison' ); ?></th>
                                <th><?php esc_html_e( '1', 'odds-comparison' ); ?></th>
                                <th><?php esc_html_e( 'X', 'odds-comparison' ); ?></th>
                                <th><?php esc_html_e( '2', 'odds-comparison' ); ?></th>
                                <th><?php esc_html_e( 'Updated', 'odds-comparison' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'odds-comparison' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $odds as $odd ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $odd['match_name'] ); ?></td>
                                    <td><?php echo esc_html( $odd['bookmaker_name'] ); ?></td>
                                    <td><?php echo esc_html( number_format( $odd['odds_home'], 2 ) ); ?></td>
                                    <td><?php echo esc_html( number_format( $odd['odds_draw'], 2 ) ); ?></td>
                                    <td><?php echo esc_html( number_format( $odd['odds_away'], 2 ) ); ?></td>
                                    <td><?php echo esc_html( date_i18n( 'd.m.Y H:i', strtotime( $odd['last_updated'] ) ) ); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=oc-manage-odds&delete_odds=' . $odd['id'] ), 'delete_odds' ) ); ?>" 
                                           class="button button-small"
                                           onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete these odds?', 'odds-comparison' ); ?>')">
                                            <?php esc_html_e( 'Delete', 'odds-comparison' ); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Import/Export page callback
 *
 * @since 1.0.0
 */
function oc_import_export_page() {
    // Handle export
    if ( isset( $_POST['oc_export_data'] ) && wp_verify_nonce( $_POST['oc_nonce'], 'oc_export_data' ) ) {
        $export_type = sanitize_text_field( $_POST['export_type'] );
        
        if ( 'odds' === $export_type ) {
            global $wpdb;
            $odds = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}oc_odds", ARRAY_A );
            
            header( 'Content-Type: application/json' );
            header( 'Content-Disposition: attachment; filename="odds-export.json"' );
            echo json_encode( $odds, JSON_PRETTY_PRINT );
            exit;
        } elseif ( 'matches' === $export_type ) {
            $matches = get_posts( array(
                'post_type'      => 'match',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    'relation' => 'OR',
                    array( 'key' => 'oc_home_team' ),
                    array( 'key' => 'oc_away_team' ),
                ),
            ) );
            
            $export_data = array();
            foreach ( $matches as $match ) {
                $export_data[] = array(
                    'ID'           => $match->ID,
                    'post_title'   => $match->post_title,
                    'home_team'    => get_post_meta( $match->ID, 'oc_home_team', true ),
                    'away_team'    => get_post_meta( $match->ID, 'oc_away_team', true ),
                    'match_date'   => get_post_meta( $match->ID, 'oc_match_date', true ),
                    'match_time'   => get_post_meta( $match->ID, 'oc_match_time', true ),
                    'venue'        => get_post_meta( $match->ID, 'oc_venue', true ),
                );
            }
            
            header( 'Content-Type: application/json' );
            header( 'Content-Disposition: attachment; filename="matches-export.json"' );
            echo json_encode( $export_data, JSON_PRETTY_PRINT );
            exit;
        }
    }
    
    ?>
    <div class="wrap oc-admin-wrap">
        <h1><?php esc_html_e( 'Import/Export', 'odds-comparison' ); ?></h1>
        
        <div class="oc-import-export-grid">
            <div class="oc-export-section">
                <h2><?php esc_html_e( 'Export Data', 'odds-comparison' ); ?></h2>
                <p><?php esc_html_e( 'Export your odds comparison data to JSON format.', 'odds-comparison' ); ?></p>
                
                <form method="post">
                    <?php wp_nonce_field( 'oc_export_data', 'oc_nonce' ); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="export_type"><?php esc_html_e( 'Export Type', 'odds-comparison' ); ?></label>
                            </th>
                            <td>
                                <select name="export_type" id="export_type">
                                    <option value="odds"><?php esc_html_e( 'Odds Data', 'odds-comparison' ); ?></option>
                                    <option value="matches"><?php esc_html_e( 'Matches Data', 'odds-comparison' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button( __( 'Export', 'odds-comparison' ), 'primary', 'oc_export_data' ); ?>
                </form>
            </div>
            
            <div class="oc-import-section">
                <h2><?php esc_html_e( 'Import Data', 'odds-comparison' ); ?></h2>
                <p><?php esc_html_e( 'Import odds data from JSON file. The file should contain an array of odds records with match_id, bookmaker_id, odds_home, odds_draw, and odds_away fields.', 'odds-comparison' ); ?></p>
                
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field( 'oc_import_data', 'oc_nonce' ); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="import_file"><?php esc_html_e( 'JSON File', 'odds-comparison' ); ?></label>
                            </th>
                            <td>
                                <input type="file" name="import_file" id="import_file" accept=".json" required>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button( __( 'Import', 'odds-comparison' ), 'primary', 'oc_import_data' ); ?>
                </form>
            </div>
        </div>
    </div>
    <?php
}

