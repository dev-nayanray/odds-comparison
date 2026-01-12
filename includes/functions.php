<?php
/**
 * Odds Comparison Functions
 *
 * Core functions for the Odds Comparison plugin.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue scripts and styles
 */
function oc_enqueue_scripts() {
    // Enqueue main stylesheet
    wp_enqueue_style('odds-comparison-main', plugin_dir_url(__FILE__) . '../assets/css/main.css', array(), '1.0.0');

    // Enqueue operator styles on operator pages
    if (is_singular('operator')) {
        wp_enqueue_style('odds-comparison-operator', plugin_dir_url(__FILE__) . '../assets/css/operator-styles.css', array(), '1.0.0');
    }

    // Enqueue comparison styles on comparison page
    if (is_page_template('templates/page-comparison.php')) {
        wp_enqueue_style('odds-comparison-comparison', plugin_dir_url(__FILE__) . '../assets/css/comparison-styles.css', array(), '1.0.0');
    }

    // Enqueue contact styles on contact and about pages
    if (is_page_template('templates/page-contact.php') || is_page_template('templates/page-about.php')) {
        wp_enqueue_style('odds-comparison-contact', plugin_dir_url(__FILE__) . '../assets/css/contact-styles.css', array(), '1.0.0');
    }

    // Enqueue main script
    wp_enqueue_script('odds-comparison-main', plugin_dir_url(__FILE__) . '../assets/js/main.js', array('jquery'), '1.0.0', true);

    // Localize script for AJAX
    wp_localize_script('odds-comparison-main', 'oc_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('oc_ajax_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'oc_enqueue_scripts');

/**
 * Handle review submission
 */
function oc_submit_review() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['oc_review_nonce'], 'oc_submit_review')) {
        wp_die(__('Security check failed', 'odds-comparison'));
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_die(__('You must be logged in to submit a review', 'odds-comparison'));
    }

    $post_id = intval($_POST['oc_review_post_id']);
    $rating = intval($_POST['oc_review_rating']);
    $title = sanitize_text_field($_POST['oc_review_title']);
    $content = wp_kses_post($_POST['oc_review_content']);

    // Validate data
    if (!$post_id || !$rating || !$title || !$content) {
        wp_die(__('All fields are required', 'odds-comparison'));
    }

    if ($rating < 1 || $rating > 5) {
        wp_die(__('Invalid rating', 'odds-comparison'));
    }

    // Create the comment
    $comment_data = array(
        'comment_post_ID' => $post_id,
        'comment_author' => wp_get_current_user()->display_name,
        'comment_author_email' => wp_get_current_user()->user_email,
        'comment_content' => $content,
        'comment_type' => 'operator_review',
        'comment_approved' => 0, // Require moderation
    );

    $comment_id = wp_insert_comment($comment_data);

    if ($comment_id) {
        // Add rating meta
        add_comment_meta($comment_id, 'rating', $rating);
        add_comment_meta($comment_id, 'review_title', $title);

        // Update average rating
        oc_update_average_rating($post_id);

        wp_redirect(get_permalink($post_id) . '#reviews-tab');
        exit;
    } else {
        wp_die(__('Error submitting review', 'odds-comparison'));
    }
}
add_action('admin_post_oc_submit_review', 'oc_submit_review');
add_action('admin_post_nopriv_oc_submit_review', 'oc_submit_review');

/**
 * Update average rating for operator
 */
function oc_update_average_rating($post_id) {
    $reviews = get_comments(array(
        'post_id' => $post_id,
        'status' => 'approve',
        'type' => 'operator_review'
    ));

    if (empty($reviews)) {
        return;
    }

    $total_rating = 0;
    $count = 0;

    foreach ($reviews as $review) {
        $rating = get_comment_meta($review->comment_ID, 'rating', true);
        if ($rating) {
            $total_rating += intval($rating);
            $count++;
        }
    }

    if ($count > 0) {
        $average_rating = $total_rating / $count;
        update_post_meta($post_id, 'oc_operator_rating', number_format($average_rating, 1));
        update_post_meta($post_id, 'oc_review_count', $count);
    }
}

/**
 * Register operator sidebar
 */
function oc_register_sidebars() {
    register_sidebar(array(
        'name' => __('Operator Sidebar', 'odds-comparison'),
        'id' => 'operator-sidebar',
        'description' => __('Widgets for operator pages', 'odds-comparison'),
        'before_widget' => '<div class="oc-sidebar-widget">',
        'after_widget' => '</div>',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
}
add_action('widgets_init', 'oc_register_sidebars');

/**
 * Load custom templates
 */
function oc_load_templates($template) {
    if (is_singular('operator')) {
        $custom_template = plugin_dir_path(__FILE__) . '../templates/single-operator.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }

    if (is_page_template('templates/page-comparison.php')) {
        $custom_template = plugin_dir_path(__FILE__) . '../templates/page-comparison.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }

    return $template;
}
add_filter('template_include', 'oc_load_templates');

/**
 * Add custom comment type for operator reviews
 */
function oc_add_comment_type($comment_types) {
    $comment_types['operator_review'] = __('Operator Review', 'odds-comparison');
    return $comment_types;
}
add_filter('admin_comment_types_dropdown', 'oc_add_comment_type');

/**
 * Display rating in comments
 */
function oc_display_comment_rating($comment_content, $comment) {
    if ($comment->comment_type === 'operator_review') {
        $rating = get_comment_meta($comment->comment_ID, 'rating', true);
        $title = get_comment_meta($comment->comment_ID, 'review_title', true);

        if ($rating) {
            $stars = '';
            for ($i = 1; $i <= 5; $i++) {
                $stars .= $i <= $rating ? '★' : '☆';
            }

            $rating_html = '<div class="oc-comment-rating">';
            $rating_html .= '<div class="oc-comment-stars">' . $stars . '</div>';
            if ($title) {
                $rating_html .= '<div class="oc-comment-title">' . esc_html($title) . '</div>';
            }
            $rating_html .= '</div>';

            $comment_content = $rating_html . $comment_content;
        }
    }

    return $comment_content;
}
add_filter('comment_text', 'oc_display_comment_rating', 10, 2);

/**
 * AJAX handler for loading comparison data
 */
function oc_load_comparison_data() {
    check_ajax_referer('oc_ajax_nonce', 'nonce');

    $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
    $sort_by = isset($_POST['sort_by']) ? sanitize_text_field($_POST['sort_by']) : 'rating';

    // Build query args based on filters
    $args = array(
        'post_type' => 'operator',
        'posts_per_page' => -1,
        'meta_query' => array('relation' => 'AND'),
    );

    // Apply filters
    if (!empty($filters['bonus_type'])) {
        $args['meta_query'][] = array(
            'key' => 'oc_bonus_type',
            'value' => sanitize_text_field($filters['bonus_type']),
            'compare' => '='
        );
    }

    if (!empty($filters['payment_method'])) {
        $args['meta_query'][] = array(
            'key' => 'oc_payment_methods',
            'value' => sanitize_text_field($filters['payment_method']),
            'compare' => 'LIKE'
        );
    }

    if (!empty($filters['min_rating'])) {
        $args['meta_query'][] = array(
            'key' => 'oc_operator_rating',
            'value' => floatval($filters['min_rating']),
            'compare' => '>=',
            'type' => 'DECIMAL'
        );
    }

    if (!empty($filters['features'])) {
        foreach ($filters['features'] as $feature) {
            $args['meta_query'][] = array(
                'key' => 'oc_' . sanitize_text_field($feature),
                'value' => '1',
                'compare' => '='
            );
        }
    }

    // Apply sorting
    switch ($sort_by) {
        case 'rating':
            $args['meta_key'] = 'oc_operator_rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'bonus':
            $args['meta_key'] = 'oc_bonus_amount';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'name':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
    }

    $query = new WP_Query($args);
    $operators = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $operator_id = get_the_ID();

            $operators[] = array(
                'id' => $operator_id,
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'thumbnail' => get_the_post_thumbnail_url($operator_id, 'thumbnail'),
                'rating' => get_post_meta($operator_id, 'oc_operator_rating', true),
                'bonus_amount' => get_post_meta($operator_id, 'oc_bonus_amount', true),
                'bonus_type' => get_post_meta($operator_id, 'oc_bonus_type', true),
                'bonus_code' => get_post_meta($operator_id, 'oc_bonus_code', true),
                'affiliate_url' => get_post_meta($operator_id, 'oc_affiliate_url', true),
                'min_deposit' => get_post_meta($operator_id, 'oc_min_deposit', true),
                'live_betting' => get_post_meta($operator_id, 'oc_live_betting', true),
                'cash_out' => get_post_meta($operator_id, 'oc_cash_out', true),
                'mobile_app' => get_post_meta($operator_id, 'oc_mobile_app', true),
                'live_streaming' => get_post_meta($operator_id, 'oc_live_streaming', true),
                'payment_methods' => get_post_meta($operator_id, 'oc_payment_methods', true),
            );
        }
    }

    wp_reset_postdata();

    wp_send_json_success($operators);
}
add_action('wp_ajax_oc_load_comparison_data', 'oc_load_comparison_data');
add_action('wp_ajax_nopriv_oc_load_comparison_data', 'oc_load_comparison_data');

/**
 * Shortcode for comparison table
 */
function oc_comparison_shortcode($atts) {
    ob_start();

    // Extract attributes
    $atts = shortcode_atts(array(
        'limit' => -1,
        'show_filters' => 'true',
        'sort_by' => 'rating'
    ), $atts);

    // Include comparison template
    include plugin_dir_path(__FILE__) . '../templates/page-comparison.php';

    return ob_get_clean();
}
add_shortcode('odds_comparison', 'oc_comparison_shortcode');

/**
 * Activation hook
 */
function oc_activate() {
    // Create database tables
    oc_create_database_tables();

    // Create necessary pages
    $comparison_page = array(
        'post_title' => __('Sportsbook Comparison', 'odds-comparison'),
        'post_content' => '[odds_comparison]',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => 'sportsbook-comparison'
    );

    $page_id = wp_insert_post($comparison_page);

    if ($page_id) {
        update_post_meta($page_id, '_wp_page_template', 'templates/page-comparison.php');
    }

    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'oc_activate');

/**
 * Deactivation hook
 */
function oc_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'oc_deactivate');

/**
 * ============================================
 * BETTING & COUPON FUNCTIONS
 * ============================================
 */

/**
 * Get user balance
 *
 * @param int $user_id User ID
 * @return float User balance
 */
function oc_get_user_balance($user_id) {
    $balance = get_user_meta($user_id, 'oc_user_balance', true);
    return floatval($balance);
}

/**
 * Update user balance
 *
 * @param int $user_id User ID
 * @param float $amount New balance
 */
function oc_update_user_balance($user_id, $amount) {
    update_user_meta($user_id, 'oc_user_balance', floatval($amount));
}

/**
 * Add amount to user balance (deposit)
 *
 * @param int $user_id User ID
 * @param float $amount Amount to add
 */
function oc_deposit_to_balance($user_id, $amount) {
    $current_balance = oc_get_user_balance($user_id);
    $new_balance = $current_balance + floatval($amount);
    oc_update_user_balance($user_id, $new_balance);
    return $new_balance;
}

/**
 * Place a bet for a user
 *
 * @param int $user_id User ID
 * @param int $match_id Match ID
 * @param string $bet_type Bet type (home, draw, away)
 * @param float $stake Stake amount
 * @param float $odds Odds value
 * @return int|false Bet ID or false on failure
 */
function oc_place_bet($user_id, $match_id, $bet_type, $stake, $odds) {
    // Get current balance
    $current_balance = oc_get_user_balance($user_id);

    // Check if user has enough balance
    if ($current_balance < $stake) {
        return false;
    }

    // Deduct stake from balance
    $new_balance = $current_balance - $stake;
    oc_update_user_balance($user_id, $new_balance);

    // Calculate potential win
    $potential_win = $stake * $odds;

    // Create bet record
    $bet_data = array(
        'user_id' => $user_id,
        'match_id' => $match_id,
        'bet_type' => sanitize_text_field($bet_type),
        'stake' => floatval($stake),
        'odds' => floatval($odds),
        'potential_win' => floatval($potential_win),
        'status' => 'pending',
        'created_at' => current_time('mysql'),
    );

    global $wpdb;
    $table_name = $wpdb->prefix . 'oc_bets';

    $wpdb->insert($table_name, $bet_data);

    if ($wpdb->insert_id) {
        return $wpdb->insert_id;
    }

    // If database insert failed, revert balance
    oc_update_user_balance($user_id, $current_balance);
    return false;
}

/**
 * Get user bets
 *
 * @param int $user_id User ID
 * @param array $args Optional arguments (status, limit, offset)
 * @return array User bets
 */
function oc_get_user_bets($user_id, $args = array()) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'oc_bets';

    $default_args = array(
        'status' => '',
        'limit' => 10,
        'offset' => 0,
        'orderby' => 'created_at',
        'order' => 'DESC',
    );

    $args = wp_parse_args($args, $default_args);

    $sql = "SELECT * FROM {$table_name} WHERE user_id = %d";
    $params = array($user_id);

    if (!empty($args['status'])) {
        $sql .= " AND status = %s";
        $params[] = $args['status'];
    }

    $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
    $sql .= " LIMIT %d OFFSET %d";
    $params[] = intval($args['limit']);
    $params[] = intval($args['offset']);

    $bets = $wpdb->get_results($wpdb->prepare($sql, $params));

    return $bets ? $bets : array();
}

/**
 * Update bet status (for processing results)
 *
 * @param int $bet_id Bet ID
 * @param string $status New status (won, lost, cancelled)
 * @return bool
 */
function oc_update_bet_status($bet_id, $status) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'oc_bets';

    $valid_statuses = array('won', 'lost', 'cancelled', 'pending');
    if (!in_array($status, $valid_statuses)) {
        return false;
    }

    $bet = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $bet_id));

    if (!$bet) {
        return false;
    }

    // If bet is won, add winnings to user balance
    if ($status === 'won' && $bet->status === 'pending') {
        $user_id = $bet->user_id;
        $current_balance = oc_get_user_balance($user_id);
        $new_balance = $current_balance + $bet->potential_win;
        oc_update_user_balance($user_id, $new_balance);
    }

    return $wpdb->update(
        $table_name,
        array('status' => $status, 'updated_at' => current_time('mysql')),
        array('id' => $bet_id),
        array('%s', '%s'),
        array('%d')
    );
}

/**
 * Create bets table on plugin activation
 */
function oc_create_bets_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'oc_bets';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table_name} (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) NOT NULL,
        match_id mediumint(9) NOT NULL,
        bet_type varchar(20) NOT NULL,
        stake decimal(10,2) NOT NULL,
        odds decimal(10,2) NOT NULL,
        potential_win decimal(10,2) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY match_id (match_id),
        KEY status (status)
    ) {$charset_collate};";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Get all operators for bookmaker selection
 *
 * @return array Array of operator data
 */
function oc_get_operators_for_betting() {
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
        ),
    );

    $operators = get_posts($args);
    $result = array();

    foreach ($operators as $operator) {
        $rating = get_post_meta($operator->ID, 'oc_operator_rating', true);
        $bonus_amount = get_post_meta($operator->ID, 'oc_bonus_amount', true);

        $result[] = array(
            'id' => $operator->ID,
            'name' => $operator->post_title,
            'rating' => floatval($rating),
            'bonus' => $bonus_amount,
            'logo' => get_the_post_thumbnail_url($operator->ID, 'thumbnail') ?: '',
            'affiliate_url' => get_post_meta($operator->ID, 'oc_affiliate_url', true),
        );
    }

    // Sort by rating
    usort($result, function($a, $b) {
        return $b['rating'] - $a['rating'];
    });

    return $result;
}

/**
 * Output coupon popup modal HTML
 */
function oc_coupon_popup_markup() {
    // Check if user is logged in
    $is_logged_in = is_user_logged_in();
    $user_balance = $is_logged_in ? oc_get_user_balance(get_current_user_id()) : 0;

    ?>
    <!-- Coupon Popup Modal -->
    <div class="oc-coupon-popup-overlay" id="oc-coupon-popup" style="display: none;">
        <div class="oc-coupon-popup">
            <div class="oc-coupon-header">
                <h3><?php _e('Betting Coupon', 'odds-comparison'); ?></h3>
                <button class="oc-coupon-close" id="oc-coupon-close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <div class="oc-coupon-content">
                <!-- Empty State -->
                <div class="oc-coupon-empty" id="oc-coupon-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
                    <p><?php _e('Your coupon is empty', 'odds-comparison'); ?></p>
                    <p class="oc-empty-hint"><?php _e('Select odds from the match cards to add to your coupon', 'odds-comparison'); ?></p>
                </div>

                <!-- Bet Items -->
                <div class="oc-coupon-bets" id="oc-coupon-bets" style="display: none;">
                    <!-- Dynamic content will be inserted here -->
                </div>
            </div>

            <!-- Bookmaker Options -->
            <div class="oc-coupon-bookmakers" id="oc-coupon-bookmakers" style="display: none;">
                <h4><?php _e('Select Bookmaker', 'odds-comparison'); ?></h4>
                <div class="oc-bookmaker-options">
                    <?php
                    $operators = oc_get_operators_for_betting();
                    foreach ($operators as $index => $operator) :
                        $logo = $operator['logo'] ? '<img src="' . esc_url($operator['logo']) . '" alt="' . esc_attr($operator['name']) . '">' : '<span class="oc-logo-fallback">' . esc_html(substr($operator['name'], 0, 2)) . '</span>';
                    ?>
                    <div class="oc-bookmaker-option-card <?php echo $index === 0 ? 'selected' : ''; ?>" data-bookmaker-id="<?php echo esc_attr($operator['id']); ?>" data-bookmaker-name="<?php echo esc_attr($operator['name']); ?>" data-affiliate-url="<?php echo esc_url($operator['affiliate_url']); ?>">
                        <div class="oc-bookmaker-logo">
                            <?php echo $logo; ?>
                        </div>
                        <div class="oc-bookmaker-info">
                            <span class="oc-bookmaker-name"><?php echo esc_html($operator['name']); ?></span>
                            <?php if ($operator['rating']) : ?>
                            <span class="oc-bookmaker-rating"><?php echo esc_html(number_format($operator['rating'], 1)); ?> ★</span>
                            <?php endif; ?>
                            <?php if ($operator['bonus']) : ?>
                            <span class="oc-bookmaker-bonus"><?php echo esc_html($operator['bonus']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Stake Input Section -->
            <div class="oc-coupon-stake-section" id="oc-coupon-stake-section" style="display: none;">
                <div class="oc-stake-input-group">
                    <label for="oc-stake-input"><?php _e('Stake (€)', 'odds-comparison'); ?></label>
                    <input type="number" id="oc-stake-input" class="oc-stake-input" placeholder="0.00" min="0.10" step="0.10" value="10.00">
                </div>

                <div class="oc-stake-presets">
                    <button type="button" class="oc-stake-preset" data-stake="5">€5</button>
                    <button type="button" class="oc-stake-preset" data-stake="10">€10</button>
                    <button type="button" class="oc-stake-preset" data-stake="25">€25</button>
                    <button type="button" class="oc-stake-preset" data-stake="50">€50</button>
                    <button type="button" class="oc-stake-preset" data-stake="100">€100</button>
                </div>

                <div class="oc-coupon-summary">
                    <div class="oc-summary-row">
                        <span class="oc-summary-label"><?php _e('Total Odds', 'odds-comparison'); ?></span>
                        <span class="oc-summary-value" id="oc-total-odds">—</span>
                    </div>
                    <div class="oc-summary-row">
                        <span class="oc-summary-label"><?php _e('Total Stake', 'odds-comparison'); ?></span>
                        <span class="oc-summary-value" id="oc-total-stake">€0.00</span>
                    </div>
                    <div class="oc-summary-row oc-summary-win">
                        <span class="oc-summary-label"><?php _e('Potential Win', 'odds-comparison'); ?></span>
                        <span class="oc-summary-value" id="oc-potential-win">€0.00</span>
                    </div>
                </div>
            </div>

            <!-- User Balance -->
            <div class="oc-coupon-balance" id="oc-coupon-balance">
                <?php if ($is_logged_in) : ?>
                <span class="oc-balance-label"><?php _e('Balance:', 'odds-comparison'); ?></span>
                <span class="oc-balance-value">€<?php echo number_format($user_balance, 2); ?></span>
                <?php else : ?>
                <span class="oc-balance-notice"><?php _e('Please log in to place bets', 'odds-comparison'); ?></span>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="oc-coupon-actions">
                <button type="button" class="oc-clear-all-btn" id="oc-clear-all-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    <?php _e('Clear All', 'odds-comparison'); ?>
                </button>
                <button type="button" class="oc-place-bet-btn" id="oc-place-bet-btn" <?php echo !$is_logged_in ? 'disabled' : ''; ?>>
                    <?php _e('Place Bet', 'odds-comparison'); ?>
                </button>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'oc_coupon_popup_markup');

/**
 * Create database tables on activation
 */
function oc_create_tables() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'oc_bets';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table_name} (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) NOT NULL,
        match_id mediumint(9) NOT NULL,
        bet_type varchar(20) NOT NULL,
        stake decimal(10,2) NOT NULL,
        odds decimal(10,2) NOT NULL,
        potential_win decimal(10,2) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY match_id (match_id),
        KEY status (status)
    ) {$charset_collate};";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'oc_create_tables');
