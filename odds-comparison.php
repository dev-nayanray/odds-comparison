<?php
/**
 * Plugin Name: Odds Comparison
 * Plugin URI: https://example.com/odds-comparison
 * Description: A comprehensive sportsbook comparison plugin for WordPress with advanced filtering, reviews, and operator management.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: odds-comparison
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('OC_VERSION', '1.0.0');
define('OC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OC_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include core files
require_once OC_PLUGIN_DIR . 'includes/functions.php';
require_once OC_PLUGIN_DIR . 'inc/ajax.php';
require_once OC_PLUGIN_DIR . 'inc/post-types/matches.php';
require_once OC_PLUGIN_DIR . 'inc/admin/meta-boxes.php';

/**
 * Main Plugin Class
 */
class Odds_Comparison {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('template_include', array($this, 'load_templates'));
        add_shortcode('odds_comparison', array($this, 'comparison_shortcode'));
    }

    /**
     * Register custom post type for operators
     */
    public function register_post_types() {
        $labels = array(
            'name' => __('Operators', 'odds-comparison'),
            'singular_name' => __('Operator', 'odds-comparison'),
            'add_new' => __('Add New Operator', 'odds-comparison'),
            'add_new_item' => __('Add New Operator', 'odds-comparison'),
            'edit_item' => __('Edit Operator', 'odds-comparison'),
            'new_item' => __('New Operator', 'odds-comparison'),
            'view_item' => __('View Operator', 'odds-comparison'),
            'search_items' => __('Search Operators', 'odds-comparison'),
            'not_found' => __('No operators found', 'odds-comparison'),
            'not_found_in_trash' => __('No operators found in trash', 'odds-comparison'),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'operator'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 20,
            'menu_icon' => 'dashicons-chart-line',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest' => true,
        );

        register_post_type('operator', $args);
    }

    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        // License taxonomy
        $license_labels = array(
            'name' => __('Licenses', 'odds-comparison'),
            'singular_name' => __('License', 'odds-comparison'),
            'search_items' => __('Search Licenses', 'odds-comparison'),
            'all_items' => __('All Licenses', 'odds-comparison'),
            'edit_item' => __('Edit License', 'odds-comparison'),
            'update_item' => __('Update License', 'odds-comparison'),
            'add_new_item' => __('Add New License', 'odds-comparison'),
            'new_item_name' => __('New License Name', 'odds-comparison'),
            'menu_name' => __('Licenses', 'odds-comparison'),
        );

        $license_args = array(
            'hierarchical' => false,
            'labels' => $license_labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'license'),
            'show_in_rest' => true,
        );

        register_taxonomy('license', array('operator'), $license_args);

        // Sports taxonomy
        $sports_labels = array(
            'name' => __('Sports', 'odds-comparison'),
            'singular_name' => __('Sport', 'odds-comparison'),
            'search_items' => __('Search Sports', 'odds-comparison'),
            'all_items' => __('All Sports', 'odds-comparison'),
            'edit_item' => __('Edit Sport', 'odds-comparison'),
            'update_item' => __('Update Sport', 'odds-comparison'),
            'add_new_item' => __('Add New Sport', 'odds-comparison'),
            'new_item_name' => __('New Sport Name', 'odds-comparison'),
            'menu_name' => __('Sports', 'odds-comparison'),
        );

        $sports_args = array(
            'hierarchical' => false,
            'labels' => $sports_labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'sport'),
            'show_in_rest' => true,
        );

        register_taxonomy('sport', array('operator'), $sports_args);
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'operator_details',
            __('Operator Details', 'odds-comparison'),
            array($this, 'operator_details_meta_box'),
            'operator',
            'normal',
            'high'
        );

        add_meta_box(
            'operator_bonuses',
            __('Bonus Information', 'odds-comparison'),
            array($this, 'operator_bonuses_meta_box'),
            'operator',
            'normal',
            'high'
        );

        add_meta_box(
            'operator_features',
            __('Features & Settings', 'odds-comparison'),
            array($this, 'operator_features_meta_box'),
            'operator',
            'normal',
            'high'
        );
    }

    /**
     * Operator details meta box
     */
    public function operator_details_meta_box($post) {
        wp_nonce_field('operator_details_nonce', 'operator_details_nonce');

        $rating = get_post_meta($post->ID, 'oc_operator_rating', true);
        $review_count = get_post_meta($post->ID, 'oc_review_count', true);
        $affiliate_url = get_post_meta($post->ID, 'oc_affiliate_url', true);
        $min_deposit = get_post_meta($post->ID, 'oc_min_deposit', true);
        $pros = get_post_meta($post->ID, 'oc_operator_pros', true);
        $cons = get_post_meta($post->ID, 'oc_operator_cons', true);
        $payment_methods = get_post_meta($post->ID, 'oc_payment_methods', true);
        $sports_supported = get_post_meta($post->ID, 'oc_sports_supported', true);

        ?>
        <table class="form-table">
            <tr>
                <th><label for="oc_operator_rating"><?php _e('Rating', 'odds-comparison'); ?></label></th>
                <td>
                    <input type="number" id="oc_operator_rating" name="oc_operator_rating" value="<?php echo esc_attr($rating); ?>" step="0.1" min="0" max="5" />
                    <p class="description"><?php _e('Operator rating out of 5', 'odds-comparison'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="oc_review_count"><?php _e('Review Count', 'odds-comparison'); ?></label></th>
                <td>
                    <input type="number" id="oc_review_count" name="oc_review_count" value="<?php echo esc_attr($review_count); ?>" min="0" />
                    <p class="description"><?php _e('Number of reviews', 'odds-comparison'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="oc_affiliate_url"><?php _e('Affiliate URL', 'odds-comparison'); ?></label></th>
                <td>
                    <input type="url" id="oc_affiliate_url" name="oc_affiliate_url" value="<?php echo esc_attr($affiliate_url); ?>" class="regular-text" />
                    <p class="description"><?php _e('Affiliate link for the operator', 'odds-comparison'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="oc_min_deposit"><?php _e('Minimum Deposit', 'odds-comparison'); ?></label></th>
                <td>
                    <input type="text" id="oc_min_deposit" name="oc_min_deposit" value="<?php echo esc_attr($min_deposit); ?>" />
                    <p class="description"><?php _e('Minimum deposit amount (e.g., $10)', 'odds-comparison'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="oc_operator_pros"><?php _e('Pros', 'odds-comparison'); ?></label></th>
                <td>
                    <textarea id="oc_operator_pros" name="oc_operator_pros" rows="4" class="large-text"><?php echo esc_textarea(is_array($pros) ? implode("\n", $pros) : $pros); ?></textarea>
                    <p class="description"><?php _e('One pro per line', 'odds-comparison'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="oc_operator_cons"><?php _e('Cons', 'odds-comparison'); ?></label></th>
                <td>
                    <textarea id="oc_operator_cons" name="oc_operator_cons" rows="4" class="large-text"><?php echo esc_textarea(is_array($cons) ? implode("\n", $cons) : $cons); ?></textarea>
                    <p class="description"><?php _e('One con per line', 'odds-comparison'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="oc_payment_methods"><?php _e('Payment Methods', 'odds-comparison'); ?></label></th>
                <td>
                    <textarea id="oc_payment_methods" name="oc_payment_methods" rows="3" class="large-text"><?php echo esc_textarea(is_array($payment_methods) ? implode("\n", $payment_methods) : $payment_methods); ?></textarea>
                    <p class="description"><?php _e('One payment method per line', 'odds-comparison'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="oc_sports_supported"><?php _e('Sports Supported', 'odds-comparison'); ?></label></th>
                <td>
                    <textarea id="oc_sports_supported" name="oc_sports_supported" rows="3" class="large-text"><?php echo esc_textarea(is_array($sports_supported) ? implode("\n", $sports_supported) : $sports_supported); ?></textarea>
                    <p class="description"><?php _e('One sport per line', 'odds-comparison'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Operator bonuses meta box
     */
    public function operator_bonuses_meta_box($post) {
        $bonus_amount = get_post_meta($post->ID, 'oc_bonus_amount', true);
        $bonus_type = get_post_meta($post->ID, 'oc_bonus_type', true);
        $bonus_description = get_post_meta($post->ID, 'oc_bonus_description', true);
        $bonus_code = get_post_meta($post->ID, 'oc_bonus_code', true);
        $wagering_requirement = get_post_meta($post->ID, 'oc_wagering_requirement', true);
        $bonus_expiry = get_post_meta($post->ID, 'oc_bonus_expiry', true);
        $bonus_last_updated = get_post_meta($post->ID, 'oc_bonus_last_updated', true);

        ?>
        <table class="form-table">
            <tr>
                <th><label for="oc_bonus_amount"><?php _e('Bonus Amount', 'odds-comparison'); ?></label></th>
                <td>
                    <input type="text" id="oc_bonus_amount" name="oc_bonus_amount" value="<?php echo esc_attr($bonus_amount); ?>" />
                    <p class="description"><?php _e('e.g., 100% up to $200', 'odds-comparison'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="oc_bonus_type"><?php _e('Bonus Type', 'odds-comparison'); ?></label></th>
                <td>
                    <select id="oc_bonus_type" name="oc_bonus_type">
                        <option value=""><?php _e('Select Type', 'odds-comparison'); ?></option>
                        <option value="welcome" <?php selected($bonus_type, 'welcome'); ?>><?php _e('Welcome Bonus', 'odds-comparison'); ?></option>
                        <option value="deposit" <?php selected($bonus_type, 'deposit'); ?>><?php _e('Deposit Bonus', 'odds-comparison'); ?></option>
                        <option value="free-bet" <?php selected($bonus_type, 'free-bet'); ?>><?php _e('Free Bet', 'odds-comparison'); ?></option>
                        <option value="cashback" <?php selected($bonus_type, 'cashback'); ?>><?php _e('Cashback', 'odds-comparison'); ?></option>
                        <option value="no-deposit" <?php selected($bonus_type, 'no-deposit'); ?>><?php _e('No Deposit Bonus', 'odds-comparison'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="oc_bonus_description"><?php _e('Bonus Description', 'odds-comparison'); ?></label></th>
                <td>
                    <textarea id="oc_bonus_description" name="oc_bonus_description" rows="3" class="large-text"><?php echo esc_textarea($bonus_description); ?></textarea>
                    <p class="description"><?php _e('Detailed description of the bonus', 'odds-comparison'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="oc_bonus_code"><?php _e('Bonus Code', 'odds-comparison'); ?></label></th>
                <td>
                    <input type="text" id="oc_bonus_code" name="oc_bonus_code" value="<?php echo esc_attr($bonus_code); ?>" />
                    <p class="description"><?php _e('Bonus code if required', 'odds-comparison'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="oc_wagering_requirement"><?php _e('Wagering Requirement', 'odds-comparison'); ?></label></th>
                <td>
                    <input type="text" id="oc_wagering_requirement" name="oc_wagering_requirement" value="<?php echo esc_attr($wagering_requirement); ?>" />
                    <p class="description"><?php _e('e.g., 30x bonus amount', 'odds-comparison'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="oc_bonus_expiry"><?php _e('Bonus Expiry', 'odds-comparison'); ?></label></th>
                <td>
                    <input type="date" id="oc_bonus_expiry" name="oc_bonus_expiry" value="<?php echo esc_attr($bonus_expiry); ?>" />
                    <p class="description"><?php _e('When the bonus expires', 'odds-comparison'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="oc_bonus_last_updated"><?php _e('Last Updated', 'odds-comparison'); ?></label></th>
                <td>
                    <input type="date" id="oc_bonus_last_updated" name="oc_bonus_last_updated" value="<?php echo esc_attr($bonus_last_updated); ?>" />
                    <p class="description"><?php _e('When bonus information was last updated', 'odds-comparison'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Operator features meta box
     */
    public function operator_features_meta_box($post) {
        $live_betting = get_post_meta($post->ID, 'oc_live_betting', true);
        $cash_out = get_post_meta($post->ID, 'oc_cash_out', true);
        $mobile_app = get_post_meta($post->ID, 'oc_mobile_app', true);
        $live_streaming = get_post_meta($post->ID, 'oc_live_streaming', true);

        ?>
        <table class="form-table">
            <tr>
                <th><?php _e('Features', 'odds-comparison'); ?></th>
                <td>
                    <label for="oc_live_betting">
                        <input type="checkbox" id="oc_live_betting" name="oc_live_betting" value="1" <?php checked($live_betting, '1'); ?> />
                        <?php _e('Live Betting', 'odds-comparison'); ?>
                    </label>
                    <br>
                    <label for="oc_cash_out">
                        <input type="checkbox" id="oc_cash_out" name="oc_cash_out" value="1" <?php checked($cash_out, '1'); ?> />
                        <?php _e('Cash Out', 'odds-comparison'); ?>
                    </label>
                    <br>
                    <label for="oc_mobile_app">
                        <input type="checkbox" id="oc_mobile_app" name="oc_mobile_app" value="1" <?php checked($mobile_app, '1'); ?> />
                        <?php _e('Mobile App', 'odds-comparison'); ?>
                    </label>
                    <br>
                    <label for="oc_live_streaming">
                        <input type="checkbox" id="oc_live_streaming" name="oc_live_streaming" value="1" <?php checked($live_streaming, '1'); ?> />
                        <?php _e('Live Streaming', 'odds-comparison'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save meta boxes
     */
    public function save_meta_boxes($post_id) {
        if (!isset($_POST['operator_details_nonce']) || !wp_verify_nonce($_POST['operator_details_nonce'], 'operator_details_nonce')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        // Save operator details
        $fields = array(
            'oc_operator_rating',
            'oc_review_count',
            'oc_affiliate_url',
            'oc_min_deposit',
            'oc_bonus_amount',
            'oc_bonus_type',
            'oc_bonus_description',
            'oc_bonus_code',
            'oc_wagering_requirement',
            'oc_bonus_expiry',
            'oc_bonus_last_updated',
        );

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }

        // Save array fields
        $array_fields = array(
            'oc_operator_pros',
            'oc_operator_cons',
            'oc_payment_methods',
            'oc_sports_supported',
        );

        foreach ($array_fields as $field) {
            if (isset($_POST[$field])) {
                $values = explode("\n", sanitize_textarea_field($_POST[$field]));
                $values = array_map('trim', $values);
                $values = array_filter($values);
                update_post_meta($post_id, $field, $values);
            }
        }

        // Save checkbox fields
        $checkbox_fields = array(
            'oc_live_betting',
            'oc_cash_out',
            'oc_mobile_app',
            'oc_live_streaming',
        );

        foreach ($checkbox_fields as $field) {
            $value = isset($_POST[$field]) ? '1' : '';
            update_post_meta($post_id, $field, $value);
        }
    }

    /**
     * Admin enqueue scripts
     */
    public function admin_enqueue_scripts($hook) {
        if ($hook === 'post.php' || $hook === 'post-new.php') {
            wp_enqueue_script('odds-comparison-admin', OC_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), OC_VERSION, true);

            wp_localize_script('odds-comparison-admin', 'ocAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('oc_ajax_nonce')
            ));
        }
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_style('odds-comparison-main', OC_PLUGIN_URL . 'assets/css/main.css', array(), OC_VERSION);

        if (is_singular('operator')) {
            wp_enqueue_style('odds-comparison-operator', OC_PLUGIN_URL . 'assets/css/operator-styles.css', array(), OC_VERSION);
        }

        if (is_page_template('templates/page-comparison.php')) {
            wp_enqueue_style('odds-comparison-comparison', OC_PLUGIN_URL . 'assets/css/comparison-styles.css', array(), OC_VERSION);
        }

        wp_enqueue_script('odds-comparison-main', OC_PLUGIN_URL . 'assets/js/main.js', array('jquery'), OC_VERSION, true);
        wp_enqueue_script('odds-comparison-coupon', OC_PLUGIN_URL . 'assets/js/odds-comparison.js', array('jquery'), OC_VERSION, true);

        wp_localize_script('odds-comparison-main', 'oc_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('oc_ajax_nonce')
        ));

        wp_localize_script('odds-comparison-coupon', 'ocAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('oc_ajax_nonce'),
            'loading' => __('Loading...', 'odds-comparison'),
            'error' => __('An error occurred', 'odds-comparison'),
            'no_results' => __('No results found', 'odds-comparison')
        ));
    }

    /**
     * Load custom templates
     */
    public function load_templates($template) {
        if (is_singular('operator')) {
            $custom_template = OC_PLUGIN_DIR . 'templates/single-operator.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        if (is_page_template('templates/page-comparison.php')) {
            $custom_template = OC_PLUGIN_DIR . 'templates/page-comparison.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        return $template;
    }

    /**
     * Comparison shortcode
     */
    public function comparison_shortcode($atts) {
        ob_start();
        include OC_PLUGIN_DIR . 'templates/page-comparison.php';
        return ob_get_clean();
    }
}

// Initialize the plugin
new Odds_Comparison();

// Activation hook
register_activation_hook(__FILE__, 'oc_activate');
function oc_activate() {
    // Register post types and taxonomies
    $plugin = new Odds_Comparison();
    $plugin->register_post_types();
    $plugin->register_taxonomies();

    // Create database tables
    require_once OC_PLUGIN_DIR . 'inc/database.php';
    oc_create_database_tables();

    // Create comparison page
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

    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'oc_deactivate');
function oc_deactivate() {
    flush_rewrite_rules();
}
