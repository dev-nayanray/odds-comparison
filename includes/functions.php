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
