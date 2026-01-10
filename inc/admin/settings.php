<?php
/**
 * Theme Settings Page
 *
 * Handles the theme settings administration interface.
 * Registers settings, sections, and fields using the WordPress Settings API.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Add theme settings menu item
 *
 * @since 1.0.0
 */
function oc_add_settings_menu() {
    add_theme_page(
        __( 'Theme Settings', 'odds-comparison' ),
        __( 'Theme Settings', 'odds-comparison' ),
        'manage_options',
        'oc-settings',
        'oc_render_settings_page'
    );
    
    add_menu_page(
        __( 'Odds Management', 'odds-comparison' ),
        __( 'Odds', 'odds-comparison' ),
        'manage_options',
        'oc-odds',
        'oc_render_odds_page',
        'dashicons-chart-line',
        30
    );
    
    add_submenu_page(
        'oc-odds',
        __( 'Import Odds', 'odds-comparison' ),
        __( 'Import Odds', 'odds-comparison' ),
        'manage_options',
        'oc-odds-import',
        'oc_render_odds_import_page'
    );
    
    add_submenu_page(
        'oc-odds',
        __( 'Analytics', 'odds-comparison' ),
        __( 'Analytics', 'odds-comparison' ),
        'manage_options',
        'oc-analytics',
        'oc_render_analytics_page'
    );

    add_submenu_page(
        'oc-odds',
        __( 'Demo Import', 'odds-comparison' ),
        __( 'Demo Import', 'odds-comparison' ),
        'manage_options',
        'oc-demo-import',
        'oc_render_demo_import_page'
    );
}
add_action( 'admin_menu', 'oc_add_settings_menu' );

/**
 * Register theme settings
 *
 * @since 1.0.0
 */
function oc_register_settings() {
    register_setting( 'oc_theme_options', 'oc_theme_options', 'oc_sanitize_settings' );
    
    // General Section
    add_settings_section(
        'oc_general_section',
        __( 'General Settings', 'odds-comparison' ),
        'oc_general_section_callback',
        'oc_theme_options'
    );
    
    add_settings_field(
        'site_logo',
        __( 'Site Logo', 'odds-comparison' ),
        'oc_logo_field_callback',
        'oc_theme_options',
        'oc_general_section'
    );
    
    add_settings_field(
        'site_favicon',
        __( 'Site Favicon', 'odds-comparison' ),
        'oc_favicon_field_callback',
        'oc_theme_options',
        'oc_general_section'
    );
    
    // Display Section
    add_settings_section(
        'oc_display_section',
        __( 'Display Settings', 'odds-comparison' ),
        'oc_display_section_callback',
        'oc_theme_options'
    );
    
    add_settings_field(
        'matches_per_page',
        __( 'Matches Per Page', 'odds-comparison' ),
        'oc_matches_per_page_field_callback',
        'oc_theme_options',
        'oc_display_section'
    );
    
    add_settings_field(
        'odds_decimal_places',
        __( 'Odds Decimal Places', 'odds-comparison' ),
        'oc_odds_decimals_field_callback',
        'oc_theme_options',
        'oc_display_section'
    );
    
    add_settings_field(
        'show_live_matches',
        __( 'Show Live Matches First', 'odds-comparison' ),
        'oc_show_live_field_callback',
        'oc_theme_options',
        'oc_display_section'
    );
    
    // Ranking Section
    add_settings_section(
        'oc_ranking_section',
        __( 'Ranking Weights', 'odds-comparison' ),
        'oc_ranking_section_callback',
        'oc_theme_options'
    );
    
    add_settings_field(
        'ranking_rating_weight',
        __( 'Rating Weight', 'odds-comparison' ),
        'oc_rating_weight_callback',
        'oc_theme_options',
        'oc_ranking_section'
    );
    
    add_settings_field(
        'ranking_bonus_weight',
        __( 'Bonus Weight', 'odds-comparison' ),
        'oc_bonus_weight_callback',
        'oc_theme_options',
        'oc_ranking_section'
    );
    
    add_settings_field(
        'ranking_license_weight',
        __( 'License Weight', 'odds-comparison' ),
        'oc_license_weight_callback',
        'oc_theme_options',
        'oc_ranking_section'
    );
    
    add_settings_field(
        'ranking_odds_weight',
        __( 'Odds Weight', 'odds-comparison' ),
        'oc_odds_weight_callback',
        'oc_theme_options',
        'oc_ranking_section'
    );
    
    // Affiliate Section
    add_settings_section(
        'oc_affiliate_section',
        __( 'Affiliate Settings', 'odds-comparison' ),
        'oc_affiliate_section_callback',
        'oc_theme_options'
    );
    
    add_settings_field(
        'affiliate_tracking_enabled',
        __( 'Enable Affiliate Tracking', 'odds-comparison' ),
        'oc_affiliate_tracking_callback',
        'oc_theme_options',
        'oc_affiliate_section'
    );
    
    add_settings_field(
        'affiliate_cookie_days',
        __( 'Cookie Duration (Days)', 'odds-comparison' ),
        'oc_cookie_days_callback',
        'oc_theme_options',
        'oc_affiliate_section'
    );
    
    // SEO Section
    add_settings_section(
        'oc_seo_section',
        __( 'SEO Settings', 'odds-comparison' ),
        'oc_seo_section_callback',
        'oc_theme_options'
    );
    
    add_settings_field(
        'home_title_prefix',
        __( 'Home Title Prefix', 'odds-comparison' ),
        'oc_home_title_callback',
        'oc_theme_options',
        'oc_seo_section'
    );
    
    add_settings_field(
        'home_meta_description',
        __( 'Home Meta Description', 'odds-comparison' ),
        'oc_home_meta_callback',
        'oc_theme_options',
        'oc_seo_section'
    );
    
    // Social Section
    add_settings_section(
        'oc_social_section',
        __( 'Social Links', 'odds-comparison' ),
        'oc_social_section_callback',
        'oc_theme_options'
    );
    
    add_settings_field(
        'social_facebook',
        __( 'Facebook URL', 'odds-comparison' ),
        'oc_social_facebook_callback',
        'oc_theme_options',
        'oc_social_section'
    );
    
    add_settings_field(
        'social_twitter',
        __( 'Twitter URL', 'odds-comparison' ),
        'oc_social_twitter_callback',
        'oc_theme_options',
        'oc_social_section'
    );
    
    add_settings_field(
        'social_youtube',
        __( 'YouTube URL', 'odds-comparison' ),
        'oc_social_youtube_callback',
        'oc_theme_options',
        'oc_social_section'
    );
}
add_action( 'admin_init', 'oc_register_settings' );

/**
 * Sanitize settings input
 *
 * @since 1.0.0
 *
 * @param array $input Settings input
 * @return array Sanitized settings
 */
function oc_sanitize_settings( $input ) {
    $sanitized = array();
    
    // General settings
    if ( isset( $input['site_logo'] ) ) {
        $sanitized['site_logo'] = esc_url_raw( $input['site_logo'] );
    }
    
    if ( isset( $input['site_favicon'] ) ) {
        $sanitized['site_favicon'] = esc_url_raw( $input['site_favicon'] );
    }
    
    // Display settings
    if ( isset( $input['matches_per_page'] ) ) {
        $sanitized['matches_per_page'] = absint( $input['matches_per_page'] );
    }
    
    if ( isset( $input['odds_decimal_places'] ) ) {
        $sanitized['odds_decimal_places'] = absint( $input['odds_decimal_places'] );
    }
    
    if ( isset( $input['show_live_matches'] ) ) {
        $sanitized['show_live_matches'] = 1;
    } else {
        $sanitized['show_live_matches'] = 0;
    }
    
    // Ranking weights
    if ( isset( $input['ranking_rating_weight'] ) ) {
        $sanitized['ranking_rating_weight'] = absint( $input['ranking_rating_weight'] );
    }
    
    if ( isset( $input['ranking_bonus_weight'] ) ) {
        $sanitized['ranking_bonus_weight'] = absint( $input['ranking_bonus_weight'] );
    }
    
    if ( isset( $input['ranking_license_weight'] ) ) {
        $sanitized['ranking_license_weight'] = absint( $input['ranking_license_weight'] );
    }
    
    if ( isset( $input['ranking_odds_weight'] ) ) {
        $sanitized['ranking_odds_weight'] = absint( $input['ranking_odds_weight'] );
    }
    
    // Affiliate settings
    if ( isset( $input['affiliate_tracking_enabled'] ) ) {
        $sanitized['affiliate_tracking_enabled'] = 1;
    } else {
        $sanitized['affiliate_tracking_enabled'] = 0;
    }
    
    if ( isset( $input['affiliate_cookie_days'] ) ) {
        $sanitized['affiliate_cookie_days'] = absint( $input['affiliate_cookie_days'] );
    }
    
    // SEO settings
    if ( isset( $input['home_title_prefix'] ) ) {
        $sanitized['home_title_prefix'] = sanitize_text_field( $input['home_title_prefix'] );
    }
    
    if ( isset( $input['home_meta_description'] ) ) {
        $sanitized['home_meta_description'] = sanitize_textarea_field( $input['home_meta_description'] );
    }
    
    // Social settings
    if ( isset( $input['social_facebook'] ) ) {
        $sanitized['social_facebook'] = esc_url_raw( $input['social_facebook'] );
    }
    
    if ( isset( $input['social_twitter'] ) ) {
        $sanitized['social_twitter'] = esc_url_raw( $input['social_twitter'] );
    }
    
    if ( isset( $input['social_youtube'] ) ) {
        $sanitized['social_youtube'] = esc_url_raw( $input['social_youtube'] );
    }
    
    return $sanitized;
}

/**
 * Render settings page
 *
 * @since 1.0.0
 */
function oc_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        
        <form action="options.php" method="post">
            <?php
            // Output security fields
            settings_fields( 'oc_theme_options' );
            
            // Output setting sections and their fields
            do_settings_sections( 'oc_theme_options' );
            
            // Output save button
            submit_button( __( 'Save Settings', 'odds-comparison' ) );
            ?>
        </form>
        
        <div class="oc-settings-sidebar">
            <div class="oc-sidebar-box">
                <h3><?php esc_html_e( 'Theme Information', 'odds-comparison' ); ?></h3>
                <p><strong><?php esc_html_e( 'Version:', 'odds-comparison' ); ?></strong> <?php echo esc_html( OC_THEME_VERSION ); ?></p>
                <p><strong><?php esc_html_e( 'Database Version:', 'odds-comparison' ); ?></strong> <?php echo esc_html( get_option( 'oc_db_version', '1.0.0' ) ); ?></p>
            </div>
            
            <div class="oc-sidebar-box">
                <h3><?php esc_html_e( 'Quick Links', 'odds-comparison' ); ?></h3>
                <ul>
                    <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=oc-odds' ) ); ?>"><?php esc_html_e( 'Manage Odds', 'odds-comparison' ); ?></a></li>
                    <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=match' ) ); ?>"><?php esc_html_e( 'Manage Matches', 'odds-comparison' ); ?></a></li>
                    <li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=operator' ) ); ?>"><?php esc_html_e( 'Manage Operators', 'odds-comparison' ); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <style>
        .oc-settings-sidebar {
            float: right;
            width: 250px;
            margin-left: 20px;
        }
        .oc-sidebar-box {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .oc-sidebar-box h3 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .oc-sidebar-box ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .oc-sidebar-box li {
            margin-bottom: 8px;
        }
    </style>
    <?php
}

/**
 * Render odds management page
 *
 * @since 1.0.0
 */
function oc_render_odds_page() {
    global $wpdb;
    
    $matches_table = $wpdb->posts;
    $odds_table    = $wpdb->prefix . 'oc_match_odds';
    
    // Get recent matches with odds
    $matches = $wpdb->get_results( "
        SELECT DISTINCT p.ID, p.post_title, p.post_date, 
               MAX(o.last_updated) as last_odds_update
        FROM {$matches_table} p
        LEFT JOIN {$odds_table} o ON p.ID = o.match_id
        WHERE p.post_type = 'match' AND p.post_status = 'publish'
        GROUP BY p.ID
        ORDER BY p.post_date DESC
        LIMIT 50
    ", ARRAY_A );
    
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Odds Management', 'odds-comparison' ); ?></h1>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=oc-odds-import' ) ); ?>" class="button">
                    <?php esc_html_e( 'Import Odds', 'odds-comparison' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=oc-analytics' ) ); ?>" class="button">
                    <?php esc_html_e( 'View Analytics', 'odds-comparison' ); ?>
                </a>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Match', 'odds-comparison' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'odds-comparison' ); ?></th>
                    <th><?php esc_html_e( 'Bookmakers', 'odds-comparison' ); ?></th>
                    <th><?php esc_html_e( 'Last Updated', 'odds-comparison' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'odds-comparison' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $matches ) ) : ?>
                    <?php foreach ( $matches as $match ) : ?>
                        <?php
                        $bookmakers_count = $wpdb->get_var( $wpdb->prepare(
                            "SELECT COUNT(DISTINCT bookmaker_id) FROM {$odds_table} WHERE match_id = %d",
                            $match['ID']
                        ) );
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html( $match['post_title'] ); ?></strong>
                                <br>
                                <a href="<?php echo esc_url( get_permalink( $match['ID'] ) ); ?>" target="_blank">
                                    <?php esc_html_e( 'View Match', 'odds-comparison' ); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $match['post_date'] ) ) ); ?></td>
                            <td><?php echo esc_html( $bookmakers_count ); ?></td>
                            <td>
                                <?php 
                                if ( $match['last_odds_update'] ) {
                                    echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $match['last_odds_update'] ) ) );
                                } else {
                                    esc_html_e( 'No odds', 'odds-comparison' );
                                }
                                ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $match['ID'] . '&action=edit' ) ); ?>" class="button button-small">
                                    <?php esc_html_e( 'Edit', 'odds-comparison' ); ?>
                                </a>
                                <button class="button button-small oc-update-odds" data-match-id="<?php echo esc_attr( $match['ID'] ); ?>">
                                    <?php esc_html_e( 'Update Odds', 'odds-comparison' ); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5"><?php esc_html_e( 'No matches found.', 'odds-comparison' ); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Render odds import page
 *
 * @since 1.0.0
 */
function oc_render_odds_import_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Import Odds', 'odds-comparison' ); ?></h1>
        
        <div class="card">
            <h2><?php esc_html_e( 'Import Odds from CSV', 'odds-comparison' ); ?></h2>
            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'oc_import_odds', 'oc_import_nonce' ); ?>
                <input type="hidden" name="action" value="oc_import_odds">
                
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="odds_csv_file"><?php esc_html_e( 'CSV File', 'odds-comparison' ); ?></label></th>
                        <td><input type="file" name="odds_csv_file" id="odds_csv_file" accept=".csv"></td>
                    </tr>
                </table>
                
                <?php submit_button( __( 'Import Odds', 'odds-comparison' ) ); ?>
            </form>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2><?php esc_html_e( 'CSV Format', 'odds-comparison' ); ?></h2>
            <p><?php esc_html_e( 'The CSV file should have the following columns:', 'odds-comparison' ); ?></p>
            <code>match_id,bookmaker_id,odds_home,odds_draw,odds_away</code>
            <p><?php esc_html_e( 'Example:', 'odds-comparison' ); ?></p>
            <code>123,456,1.85,3.40,4.20<br>124,456,2.10,3.50,3.30</code>
        </div>
    </div>
    <?php
}

/**
 * Render analytics page
 *
 * @since 1.0.0
 */
function oc_render_analytics_page() {
    global $wpdb;
    
    $clicks_table = $wpdb->prefix . 'oc_affiliate_clicks';
    $operators_table = $wpdb->posts;
    
    // Get stats
    $total_clicks = $wpdb->get_var( "SELECT COUNT(*) FROM {$clicks_table}" );
    $converted = $wpdb->get_var( "SELECT COUNT(*) FROM {$clicks_table} WHERE converted = 1" );
    $clicks_today = $wpdb->get_var( "SELECT COUNT(*) FROM {$clicks_table} WHERE DATE(clicked_at) = CURDATE()" );
    
    // Top operators by clicks
    $top_operators = $wpdb->get_results( "
        SELECT o.ID, o.post_title, COUNT(c.id) as click_count
        FROM {$operators_table} o
        LEFT JOIN {$clicks_table} c ON o.ID = c.operator_id
        WHERE o.post_type = 'operator' AND o.post_status = 'publish'
        GROUP BY o.ID
        ORDER BY click_count DESC
        LIMIT 10
    ", ARRAY_A );
    
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Analytics', 'odds-comparison' ); ?></h1>
        
        <div class="oc-stats-grid">
            <div class="oc-stat-box">
                <h3><?php esc_html_e( 'Total Clicks', 'odds-comparison' ); ?></h3>
                <div class="stat-value"><?php echo esc_html( number_format( $total_clicks ) ); ?></div>
            </div>
            <div class="oc-stat-box">
                <h3><?php esc_html_e( 'Clicks Today', 'odds-comparison' ); ?></h3>
                <div class="stat-value"><?php echo esc_html( number_format( $clicks_today ) ); ?></div>
            </div>
            <div class="oc-stat-box">
                <h3><?php esc_html_e( 'Conversions', 'odds-comparison' ); ?></h3>
                <div class="stat-value"><?php echo esc_html( number_format( $converted ) ); ?></div>
            </div>
            <div class="oc-stat-box">
                <h3><?php esc_html_e( 'Conversion Rate', 'odds-comparison' ); ?></h3>
                <div class="stat-value">
                    <?php 
                    $rate = $total_clicks > 0 ? round( ( $converted / $total_clicks ) * 100, 2 ) : 0;
                    echo esc_html( $rate . '%' );
                    ?>
                </div>
            </div>
        </div>
        
        <h2><?php esc_html_e( 'Top Operators by Clicks', 'odds-comparison' ); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Operator', 'odds-comparison' ); ?></th>
                    <th><?php esc_html_e( 'Clicks', 'odds-comparison' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $top_operators as $op ) : ?>
                    <tr>
                        <td><?php echo esc_html( $op['post_title'] ); ?></td>
                        <td><?php echo esc_html( number_format( $op['click_count'] ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <style>
        .oc-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .oc-stat-box {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            text-align: center;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .oc-stat-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
        }
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1a5f7a;
        }
    </style>
    <?php
}

/**
 * Render demo import page
 *
 * @since 1.0.0
 */
function oc_render_demo_import_page() {
    if ( ! class_exists( 'OC_Demo_Import' ) ) {
        require_once get_template_directory() . '/inc/demo-import.php';
    }

    $importer = OC_Demo_Import::get_instance();
    $status = $importer->get_status_summary();
    $logs = $importer->get_logs();

    // Handle form submissions
    if ( isset( $_POST['oc_demo_action'] ) && check_admin_referer( 'oc_demo_import', 'oc_demo_nonce' ) ) {
        $action = sanitize_text_field( $_POST['oc_demo_action'] );

        if ( $action === 'import' ) {
            $options = array(
                'theme_options' => isset( $_POST['import_theme_options'] ),
                'customizer'    => isset( $_POST['import_customizer'] ),
                'widgets'       => isset( $_POST['import_widgets'] ),
                'menus'         => isset( $_POST['import_menus'] ),
                'pages'         => isset( $_POST['import_pages'] ),
                'taxonomies'    => isset( $_POST['import_taxonomies'] ),
                'operators'     => isset( $_POST['import_operators'] ),
                'matches'       => isset( $_POST['import_matches'] ),
                'odds'          => isset( $_POST['import_odds'] ),
            );

            // Run import via AJAX-like redirect to avoid timeout
            $importer->import_all( $options );
        } elseif ( $action === 'reset' ) {
            $importer->reset_demo();
        }
    }

    // Refresh status after actions
    $status = $importer->get_status_summary();
    $logs = $importer->get_logs();

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Demo Import', 'odds-comparison' ); ?></h1>

        <?php if ( ! empty( $_GET['imported'] ) ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php printf( esc_html__( 'Demo content imported successfully!', 'odds-comparison' ) ); ?></p>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $_GET['reset'] ) ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php printf( esc_html__( 'Demo data has been reset.', 'odds-comparison' ) ); ?></p>
            </div>
        <?php endif; ?>

        <!-- Status Dashboard -->
        <div class="oc-demo-status-card">
            <h2><?php esc_html_e( 'Import Status', 'odds-comparison' ); ?></h2>
            <div class="oc-status-grid">
                <div class="oc-status-item">
                    <span class="status-label"><?php esc_html_e( 'Status:', 'odds-comparison' ); ?></span>
                    <span class="status-value status-<?php echo esc_attr( $status['status'] ); ?>">
                        <?php
                        switch ( $status['status'] ) {
                            case 'running':
                                esc_html_e( 'Running...', 'odds-comparison' );
                                break;
                            case 'completed':
                                esc_html_e( 'Completed', 'odds-comparison' );
                                break;
                            default:
                                esc_html_e( 'Not Imported', 'odds-comparison' );
                        }
                        ?>
                    </span>
                </div>
                <div class="oc-status-item">
                    <span class="status-label"><?php esc_html_e( 'Progress:', 'odds-comparison' ); ?></span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo esc_attr( $status['progress'] ); ?>%;"></div>
                    </div>
                    <span class="progress-text"><?php echo esc_html( $status['progress'] . '%' ); ?></span>
                </div>
                <div class="oc-status-item">
                    <span class="status-label"><?php esc_html_e( 'Imported:', 'odds-comparison' ); ?></span>
                    <span class="status-value">
                        <?php echo $status['imported'] ? esc_html__( 'Yes', 'odds-comparison' ) : esc_html__( 'No', 'odds-comparison' ); ?>
                    </span>
                </div>
                <?php if ( $status['import_date'] ) : ?>
                <div class="oc-status-item">
                    <span class="status-label"><?php esc_html_e( 'Import Date:', 'odds-comparison' ); ?></span>
                    <span class="status-value">
                        <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $status['import_date'] ) ) ); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Import Options -->
        <form method="post" action="">
            <?php wp_nonce_field( 'oc_demo_import', 'oc_demo_nonce' ); ?>
            <input type="hidden" name="oc_demo_action" value="import">

            <div class="oc-import-options">
                <h2><?php esc_html_e( 'Import Options', 'odds-comparison' ); ?></h2>
                <p><?php esc_html_e( 'Select which demo content to import:', 'odds-comparison' ); ?></p>

                <div class="import-options-grid">
                    <label class="import-option">
                        <input type="checkbox" name="import_theme_options" checked>
                        <span class="option-name"><?php esc_html_e( 'Theme Options', 'odds-comparison' ); ?></span>
                        <span class="option-desc"><?php esc_html_e( 'Settings for matches per page, odds display, ranking weights', 'odds-comparison' ); ?></span>
                    </label>
                    <label class="import-option">
                        <input type="checkbox" name="import_customizer" checked>
                        <span class="option-name"><?php esc_html_e( 'Customizer Settings', 'odds-comparison' ); ?></span>
                        <span class="option-desc"><?php esc_html_e( 'Colors, typography, layout options', 'odds-comparison' ); ?></span>
                    </label>
                    <label class="import-option">
                        <input type="checkbox" name="import_widgets" checked>
                        <span class="option-name"><?php esc_html_e( 'Widgets', 'odds-comparison' ); ?></span>
                        <span class="option-desc"><?php esc_html_e( 'Sidebar and footer widget configurations', 'odds-comparison' ); ?></span>
                    </label>
                    <label class="import-option">
                        <input type="checkbox" name="import_menus" checked>
                        <span class="option-name"><?php esc_html_e( 'Menus', 'odds-comparison' ); ?></span>
                        <span class="option-desc"><?php esc_html_e( 'Primary and footer navigation menus', 'odds-comparison' ); ?></span>
                    </label>
                    <label class="import-option">
                        <input type="checkbox" name="import_pages" checked>
                        <span class="option-name"><?php esc_html_e( 'Pages', 'odds-comparison' ); ?></span>
                        <span class="option-desc"><?php esc_html_e( 'Home, Matches, Operators pages', 'odds-comparison' ); ?></span>
                    </label>
                    <label class="import-option">
                        <input type="checkbox" name="import_taxonomies" checked>
                        <span class="option-name"><?php esc_html_e( 'Taxonomies', 'odds-comparison' ); ?></span>
                        <span class="option-desc"><?php esc_html_e( 'Sports, Leagues, Teams', 'odds-comparison' ); ?></span>
                    </label>
                    <label class="import-option">
                        <input type="checkbox" name="import_operators" checked>
                        <span class="option-name"><?php esc_html_e( 'Operators', 'odds-comparison' ); ?></span>
                        <span class="option-desc"><?php esc_html_e( 'Betting operators with ratings and bonuses', 'odds-comparison' ); ?></span>
                    </label>
                    <label class="import-option">
                        <input type="checkbox" name="import_matches" checked>
                        <span class="option-name"><?php esc_html_e( 'Matches', 'odds-comparison' ); ?></span>
                        <span class="option-desc"><?php esc_html_e( 'Upcoming matches with team information', 'odds-comparison' ); ?></span>
                    </label>
                    <label class="import-option">
                        <input type="checkbox" name="import_odds" checked>
                        <span class="option-name"><?php esc_html_e( 'Odds', 'odds-comparison' ); ?></span>
                        <span class="option-desc"><?php esc_html_e( 'Sample odds for matches from operators', 'odds-comparison' ); ?></span>
                    </label>
                </div>
            </div>

            <div class="oc-import-actions">
                <button type="submit" class="button button-primary button-hero">
                    <?php esc_html_e( 'Import Demo Content', 'odds-comparison' ); ?>
                </button>
            </div>
        </form>

        <!-- Reset Section -->
        <div class="oc-reset-section">
            <h2><?php esc_html_e( 'Reset Demo Data', 'odds-comparison' ); ?></h2>
            <p><?php esc_html_e( 'Remove all imported demo content. This action cannot be undone.', 'odds-comparison' ); ?></p>
            <form method="post" action="">
                <?php wp_nonce_field( 'oc_demo_import', 'oc_demo_nonce' ); ?>
                <input type="hidden" name="oc_demo_action" value="reset">
                <button type="submit" class="button button-secondary" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to remove all demo content?', 'odds-comparison' ); ?>' );">
                    <?php esc_html_e( 'Reset Demo Data', 'odds-comparison' ); ?>
                </button>
            </form>
        </div>

        <!-- Import Logs -->
        <?php if ( ! empty( $logs ) ) : ?>
        <div class="oc-logs-section">
            <h2><?php esc_html_e( 'Import Logs', 'odds-comparison' ); ?></h2>
            <div class="oc-logs-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Time', 'odds-comparison' ); ?></th>
                            <th><?php esc_html_e( 'Type', 'odds-comparison' ); ?></th>
                            <th><?php esc_html_e( 'Component', 'odds-comparison' ); ?></th>
                            <th><?php esc_html_e( 'Message', 'odds-comparison' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( array_slice( $logs, -20 ) as $log ) : ?>
                        <tr>
                            <td><?php echo esc_html( date_i18n( 'H:i:s', strtotime( $log['timestamp'] ) ) ); ?></td>
                            <td>
                                <span class="log-type log-<?php echo esc_attr( $log['type'] ); ?>">
                                    <?php echo esc_html( ucfirst( $log['type'] ) ); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html( $log['component'] ); ?></td>
                            <td><?php echo esc_html( $log['message'] ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <style>
        .oc-demo-status-card {
            background: #fff;
            border: 1px solid #c3c4c7;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .oc-demo-status-card h2 {
            margin-top: 0;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .oc-status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .oc-status-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .status-label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .status-value {
            font-weight: 600;
        }
        .status-value.status-completed {
            color: #00a32a;
        }
        .status-value.status-running {
            color: #007cba;
        }
        .status-value.status-idle {
            color: #666;
        }
        .progress-bar {
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin: 5px 0;
        }
        .progress-fill {
            height: 100%;
            background: #007cba;
            transition: width 0.3s ease;
        }
        .progress-text {
            font-size: 12px;
            color: #666;
        }
        .oc-import-options {
            background: #fff;
            border: 1px solid #c3c4c7;
            padding: 20px;
            margin-bottom: 20px;
        }
        .oc-import-options h2 {
            margin-top: 0;
        }
        .import-options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .import-option {
            display: block;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .import-option:hover {
            background: #f0f0f1;
        }
        .import-option input {
            margin-right: 10px;
        }
        .option-name {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .option-desc {
            display: block;
            font-size: 12px;
            color: #666;
        }
        .oc-import-actions {
            margin: 20px 0;
            padding: 20px;
            background: #f0f0f1;
            text-align: center;
            border-radius: 4px;
        }
        .oc-reset-section {
            background: #fff;
            border: 1px solid #c3c4c7;
            padding: 20px;
            margin-bottom: 20px;
        }
        .oc-reset-section h2 {
            margin-top: 0;
            color: #dc3232;
        }
        .oc-logs-section {
            background: #fff;
            border: 1px solid #c3c4c7;
            padding: 20px;
        }
        .oc-logs-section h2 {
            margin-top: 0;
        }
        .log-type {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
        }
        .log-success {
            background: #d4edda;
            color: #155724;
        }
        .log-info {
            background: #cce5ff;
            color: #004085;
        }
        .log-warning {
            background: #fff3cd;
            color: #856404;
        }
        .log-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
    <?php
}

// Section callbacks
function oc_general_section_callback() {
    echo '<p>' . esc_html__( 'General theme settings.', 'odds-comparison' ) . '</p>';
}

function oc_display_section_callback() {
    echo '<p>' . esc_html__( 'Configure how content is displayed.', 'odds-comparison' ) . '</p>';
}

function oc_ranking_section_callback() {
    echo '<p>' . esc_html__( 'Configure operator ranking weights (total should equal 100).', 'odds-comparison' ) . '</p>';
}

function oc_affiliate_section_callback() {
    echo '<p>' . esc_html__( 'Configure affiliate tracking settings.', 'odds-comparison' ) . '</p>';
}

function oc_seo_section_callback() {
    echo '<p>' . esc_html__( 'Configure SEO settings.', 'odds-comparison' ) . '</p>';
}

function oc_social_section_callback() {
    echo '<p>' . esc_html__( 'Add your social media links.', 'odds-comparison' ) . '</p>';
}

// Field callbacks
function oc_logo_field_callback() {
    $options = get_option( 'oc_theme_options' );
    $logo = isset( $options['site_logo'] ) ? $options['site_logo'] : '';
    ?>
    <input type="url" name="oc_theme_options[site_logo]" value="<?php echo esc_url( $logo ); ?>" class="regular-text" placeholder="https://example.com/logo.png">
    <p class="description"><?php esc_html_e( 'Enter the URL of your logo or use the WordPress customizer.', 'odds-comparison' ); ?></p>
    <?php
}

function oc_favicon_field_callback() {
    $options = get_option( 'oc_theme_options' );
    $favicon = isset( $options['site_favicon'] ) ? $options['site_favicon'] : '';
    ?>
    <input type="url" name="oc_theme_options[site_favicon]" value="<?php echo esc_url( $favicon ); ?>" class="regular-text" placeholder="https://example.com/favicon.ico">
    <p class="description"><?php esc_html_e( 'Enter the URL of your favicon.', 'odds-comparison' ); ?></p>
    <?php
}

function oc_matches_per_page_field_callback() {
    $options = get_option( 'oc_theme_options' );
    $value = isset( $options['matches_per_page'] ) ? $options['matches_per_page'] : 10;
    ?>
    <input type="number" name="oc_theme_options[matches_per_page]" value="<?php echo esc_attr( $value ); ?>" min="1" max="100">
    <?php
}

function oc_odds_decimals_field_callback() {
    $options = get_option( 'oc_theme_options' );
    $value = isset( $options['odds_decimal_places'] ) ? $options['odds_decimal_places'] : 2;
    ?>
    <input type="number" name="oc_theme_options[odds_decimal_places]" value="<?php echo esc_attr( $value ); ?>" min="0" max="4">
    <?php
}

function oc_show_live_field_callback() {
    $options = get_option( 'oc_theme_options' );
    $checked = isset( $options['show_live_matches'] ) && $options['show_live_matches'] ? 'checked' : '';
    ?>
    <label>
        <input type="checkbox" name="oc_theme_options[show_live_matches]" value="1" <?php echo $checked; ?>>
        <?php esc_html_e( 'Show live matches at the top of listings', 'odds-comparison' ); ?>
    </label>
    <?php
}

function oc_rating_weight_callback() {
    $options = get_option( 'oc_theme_options' );
    $value = isset( $options['ranking_rating_weight'] ) ? $options['ranking_rating_weight'] : 40;
    ?>
    <input type="number" name="oc_theme_options[ranking_rating_weight]" value="<?php echo esc_attr( $value ); ?>" min="0" max="100">
    <?php
}

function oc_bonus_weight_callback() {
    $options = get_option( 'oc_theme_options' );
    $value = isset( $options['ranking_bonus_weight'] ) ? $options['ranking_bonus_weight'] : 30;
    ?>
    <input type="number" name="oc_theme_options[ranking_bonus_weight]" value="<?php echo esc_attr( $value ); ?>" min="0" max="100">
    <?php
}

function oc_license_weight_callback() {
    $options = get_option( 'oc_theme_options' );
    $value = isset( $options['ranking_license_weight'] ) ? $options['ranking_license_weight'] : 20;
    ?>
    <input type="number" name="oc_theme_options[ranking_license_weight]" value="<?php echo esc_attr( $value ); ?>" min="0" max="100">
    <?php
}

function oc_odds_weight_callback() {
    $options = get_option( 'oc_theme_options' );
    $value = isset( $options['ranking_odds_weight'] ) ? $options['ranking_odds_weight'] : 10;
    ?>
    <input type="number" name="oc_theme_options[ranking_odds_weight]" value="<?php echo esc_attr( $value ); ?>" min="0" max="100">
    <?php
}

function oc_affiliate_tracking_callback() {
    $options = get_option( 'oc_theme_options' );
    $checked = isset( $options['affiliate_tracking_enabled'] ) && $options['affiliate_tracking_enabled'] ? 'checked' : '';
    ?>
    <label>
        <input type="checkbox" name="oc_theme_options[affiliate_tracking_enabled]" value="1" <?php echo $checked; ?>>
        <?php esc_html_e( 'Enable affiliate link tracking', 'odds-comparison' ); ?>
    </label>
    <?php
}

function oc_cookie_days_callback() {
    $options = get_option( 'oc_theme_options' );
    $value = isset( $options['affiliate_cookie_days'] ) ? $options['affiliate_cookie_days'] : 30;
    ?>
    <input type="number" name="oc_theme_options[affiliate_cookie_days]" value="<?php echo esc_attr( $value ); ?>" min="1" max="365">
    <?php
}

function oc_home_title_callback() {
    $options = get_option( 'oc_theme_options' );
    $value = isset( $options['home_title_prefix'] ) ? $options['home_title_prefix'] : '';
    ?>
    <input type="text" name="oc_theme_options[home_title_prefix]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g., Compare Odds', 'odds-comparison' ); ?>">
    <?php
}

function oc_home_meta_callback() {
    $options = get_option( 'oc_theme_options' );
    $value = isset( $options['home_meta_description'] ) ? $options['home_meta_description'] : '';
    ?>
    <textarea name="oc_theme_options[home_meta_description]" rows="4" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
    <?php
}

function oc_social_facebook_callback() {
    $options = get_option( 'oc_theme_options' );
    $value = isset( $options['social_facebook'] ) ? $options['social_facebook'] : '';
    ?>
    <input type="url" name="oc_theme_options[social_facebook]" value="<?php echo esc_url( $value ); ?>" class="regular-text" placeholder="https://facebook.com/...">
    <?php
}

function oc_social_twitter_callback() {
    $options = get_option( 'oc_theme_options' );
    $value = isset( $options['social_twitter'] ) ? $options['social_twitter'] : '';
    ?>
    <input type="url" name="oc_theme_options[social_twitter]" value="<?php echo esc_url( $value ); ?>" class="regular-text" placeholder="https://twitter.com/...">
    <?php
}

function oc_social_youtube_callback() {
    $options = get_option( 'oc_theme_options' );
    $value = isset( $options['social_youtube'] ) ? $options['social_youtube'] : '';
    ?>
    <input type="url" name="oc_theme_options[social_youtube]" value="<?php echo esc_url( $value ); ?>" class="regular-text" placeholder="https://youtube.com/...">
    <?php
}

/**
 * Handle odds import via admin-post
 *
 * @since 1.0.0
 */
function oc_handle_odds_import() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Insufficient permissions.', 'odds-comparison' ) );
    }
    
    check_admin_referer( 'oc_import_odds', 'oc_import_nonce' );
    
    if ( empty( $_FILES['odds_csv_file']['tmp_name'] ) ) {
        wp_redirect( admin_url( 'admin.php?page=oc-odds-import&error=no_file' ) );
        exit;
    }
    
    $file = $_FILES['odds_csv_file'];
    
    if ( $file['type'] !== 'text/csv' && pathinfo( $file['name'], PATHINFO_EXTENSION ) !== 'csv' ) {
        wp_redirect( admin_url( 'admin.php?page=oc-odds-import&error=invalid_type' ) );
        exit;
    }
    
    $handle = fopen( $file['tmp_name'], 'r' );
    if ( ! $handle ) {
        wp_redirect( admin_url( 'admin.php?page=oc-odds-import&error=read_failed' ) );
        exit;
    }
    
    $imported = 0;
    $header = fgetcsv( $handle );
    
    while ( ( $row = fgetcsv( $handle ) ) !== false ) {
        if ( count( $row ) >= 5 ) {
            $odds_data = array(
                'match_id'     => $row[0],
                'bookmaker_id' => $row[1],
                'odds_home'    => $row[2],
                'odds_draw'    => $row[3],
                'odds_away'    => $row[4],
            );
            
            if ( oc_insert_odds( $odds_data ) ) {
                $imported++;
            }
        }
    }
    
    fclose( $handle );
    
    wp_redirect( admin_url( 'admin.php?page=oc-odds-import&imported=' . $imported ) );
    exit;
}
add_action( 'admin_post_oc_import_odds', 'oc_handle_odds_import' );

