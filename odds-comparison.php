<?php
/**
 * Plugin Name: Odds Comparison
 * Plugin URI: https://example.com/odds-comparison
 * Description: A comprehensive WordPress plugin for comparing betting odds across multiple bookmakers. Features include match management, operator listings, odds comparison tables, affiliate tracking, and REST API support.
 * Version: 1.0.0
 * Author: Developer
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: odds-comparison
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'OC_VERSION', '1.0.0' );
define( 'OC_FILE', __FILE__ );
define( 'OC_PLUGIN_BASENAME', plugin_basename( OC_FILE ) );
define( 'OC_PLUGIN_DIR', plugin_dir_path( OC_FILE ) );
define( 'OC_PLUGIN_URL', plugin_dir_url( OC_FILE ) );

/**
 * Main Plugin Class
 *
 * Initializes all plugin components.
 *
 * @since 1.0.0
 */
final class Odds_Comparison {

    /**
     * Single instance of the plugin
     *
     * @since 1.0.0
     * @var Odds_Comparison
     */
    private static $instance = null;

    /**
     * Get plugin instance
     *
     * @since 1.0.0
     * @return Odds_Comparison
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->load_dependencies();
        $this->define_constants();
        $this->init_hooks();
    }

    /**
     * Prevent cloning
     *
     * @since 1.0.0
     */
    private function __clone() {}

    /**
     * Prevent unserializing
     *
     * @since 1.0.0
     */
    public function __wakeup() {}

    /**
     * Load required files
     *
     * @since 1.0.0
     */
    private function load_dependencies() {
        // Core files
        require_once OC_PLUGIN_DIR . 'inc/helpers.php';
        require_once OC_PLUGIN_DIR . 'inc/database.php';
        
        // Post types
        require_once OC_PLUGIN_DIR . 'inc/post-types/matches.php';
        require_once OC_PLUGIN_DIR . 'inc/post-types/operators.php';
        
        // Taxonomies
        require_once OC_PLUGIN_DIR . 'inc/taxonomies/sports.php';
        require_once OC_PLUGIN_DIR . 'inc/taxonomies/leagues.php';
        require_once OC_PLUGIN_DIR . 'inc/taxonomies/licenses.php';
        
        // Admin
        if ( is_admin() ) {
            require_once OC_PLUGIN_DIR . 'inc/admin/settings.php';
            require_once OC_PLUGIN_DIR . 'inc/admin/meta-boxes.php';
            require_once OC_PLUGIN_DIR . 'inc/admin/admin-menu.php';
        }
        
        // API
        require_once OC_PLUGIN_DIR . 'inc/api/ajax.php';
        require_once OC_PLUGIN_DIR . 'inc/api/rest.php';
        
        // Templates
        require_once OC_PLUGIN_DIR . 'inc/template-functions.php';
        require_once OC_PLUGIN_DIR . 'inc/shortcodes.php';
        
        // Widgets
        require_once OC_PLUGIN_DIR . 'inc/widgets.php';
    }

    /**
     * Define plugin constants
     *
     * @since 1.0.0
     */
    private function define_constants() {
        // Already defined at top of file, but can add more here
        do_action( 'oc_define_constants' );
    }

    /**
     * Initialize hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Load textdomain
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        
        // Initialize on init
        add_action( 'init', array( $this, 'init' ) );
        
        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        
        // Activation and deactivation hooks
        register_activation_hook( OC_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( OC_FILE, array( $this, 'deactivate' ) );
        
        // Register widgets
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );
    }

    /**
     * Load plugin textdomain
     *
     * @since 1.0.0
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'odds-comparison',
            false,
            dirname( OC_PLUGIN_BASENAME ) . '/languages'
        );
    }

    /**
     * Initialize plugin
     *
     * @since 1.0.0
     */
    public function init() {
        // Initialize post types
        do_action( 'oc_init_post_types' );
        
        // Initialize taxonomies
        do_action( 'oc_init_taxonomies' );
        
        // Initialize template functions
        do_action( 'oc_init_templates' );
        
        // Flush rewrite rules if needed
        if ( get_option( 'oc_flush_rewrite' ) ) {
            flush_rewrite_rules();
            delete_option( 'oc_flush_rewrite' );
        }
    }

    /**
     * Enqueue frontend assets
     *
     * @since 1.0.0
     */
    public function enqueue_frontend_assets() {
        // Main stylesheet
        wp_enqueue_style(
            'oc-styles',
            OC_PLUGIN_URL . 'assets/css/odds-comparison.css',
            array(),
            OC_VERSION
        );
        
        // Main JavaScript
        wp_enqueue_script(
            'oc-main',
            OC_PLUGIN_URL . 'assets/js/odds-comparison.js',
            array( 'jquery' ),
            OC_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script( 'oc-main', 'ocAjax', array(
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'oc_ajax_nonce' ),
            'loading'   => __( 'Loading...', 'odds-comparison' ),
            'error'     => __( 'An error occurred. Please try again.', 'odds-comparison' ),
            'no_results'=> __( 'No results found.', 'odds-comparison' ),
        ) );
        
        do_action( 'oc_enqueue_frontend_assets' );
    }

    /**
     * Register widgets
     *
     * @since 1.0.0
     */
    public function register_widgets() {
        register_widget( 'OC_Best_Odds_Widget' );
        register_widget( 'OC_Matches_Widget' );
        register_widget( 'OC_Operators_Widget' );
    }

    /**
     * Plugin activation
     *
     * @since 1.0.0
     */
    public function activate() {
        // Create database tables
        oc_create_database_tables();
        
        // Flush rewrite rules
        update_option( 'oc_flush_rewrite', true );
        
        // Set activation flag
        update_option( 'oc_activated', true );
        
        do_action( 'oc_activate' );
    }

    /**
     * Plugin deactivation
     *
     * @since 1.0.0
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        do_action( 'oc_deactivate' );
    }
}

/**
 * Get plugin instance
 *
 * @since 1.0.0
 * @return Odds_Comparison
 */
function odds_comparison() {
    return Odds_Comparison::get_instance();
}

// Initialize plugin
odds_comparison();

// Helper function to get plugin URL
function oc_plugin_url( $path = '' ) {
    return OC_PLUGIN_URL . ltrim( $path, '/' );
}

// Helper function to get plugin directory path
function oc_plugin_dir( $path = '' ) {
    return OC_PLUGIN_DIR . ltrim( $path, '/' );
}

// Helper function to get version
function oc_get_version() {
    return OC_VERSION;
}

