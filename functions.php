<?php
/**
 * Odds Comparison Theme - Main Functions File
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =====================================================
 * CONSTANTS
 * ===================================================== */

define( 'OC_THEME_VERSION', '1.0.0' );
define( 'OC_THEME_DIR', get_template_directory() );
define( 'OC_THEME_URI', get_template_directory_uri() );
define( 'OC_ASSETS_URI', OC_THEME_URI . '/assets' );
define( 'OC_INC_DIR', OC_THEME_DIR . '/inc' );

define( 'OC_MIN_PHP_VERSION', '8.0' );
define( 'OC_MIN_WP_VERSION', '6.0' );

/* =====================================================
 * ENVIRONMENT CHECK
 * ===================================================== */

function oc_check_environment() {
    global $wp_version;

    if ( version_compare( PHP_VERSION, OC_MIN_PHP_VERSION, '<' ) ) {
        add_action( 'admin_notices', 'oc_php_version_notice' );
        switch_theme( WP_DEFAULT_THEME );
        return false;
    }

    if ( version_compare( $wp_version, OC_MIN_WP_VERSION, '<' ) ) {
        add_action( 'admin_notices', 'oc_wp_version_notice' );
        switch_theme( WP_DEFAULT_THEME );
        return false;
    }

    return true;
}
add_action( 'after_setup_theme', 'oc_check_environment', 1 );

function oc_php_version_notice() {
    echo '<div class="notice notice-error"><p>' .
        esc_html__( 'Odds Comparison theme requires PHP 8.0 or higher.', 'odds-comparison' ) .
    '</p></div>';
}

function oc_wp_version_notice() {
    echo '<div class="notice notice-error"><p>' .
        esc_html__( 'Odds Comparison theme requires WordPress 6.0 or higher.', 'odds-comparison' ) .
    '</p></div>';
}

/* =====================================================
 * LOAD THEME FILES
 * ===================================================== */

function oc_load_theme_files() {

    load_theme_textdomain( 'odds-comparison', OC_THEME_DIR . '/languages' );

    require_once OC_INC_DIR . '/helpers.php';
    require_once OC_INC_DIR . '/database.php';
    require_once OC_INC_DIR . '/template-functions.php';

    require_once OC_INC_DIR . '/post-types/operators.php';
    require_once OC_INC_DIR . '/post-types/matches.php';

    require_once OC_INC_DIR . '/taxonomies/sports.php';
    require_once OC_INC_DIR . '/taxonomies/leagues.php';
    require_once OC_INC_DIR . '/taxonomies/teams.php';

    require_once OC_INC_DIR . '/demo-data.php';

    if ( is_admin() ) {
        require_once OC_INC_DIR . '/admin/settings.php';
        require_once OC_INC_DIR . '/admin/meta-boxes.php';
    }

    require_once OC_INC_DIR . '/api/ajax.php';
    require_once OC_INC_DIR . '/api/rest.php';
}
add_action( 'after_setup_theme', 'oc_load_theme_files' );

/* =====================================================
 * THEME SETUP
 * ===================================================== */

function oc_theme_setup() {

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'customize-selective-refresh-widgets' );

    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-width'  => true,
        'flex-height' => true,
    ) );

    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'odds-comparison' ),
        'footer'  => __( 'Footer Menu', 'odds-comparison' ),
        'social'  => __( 'Social Links', 'odds-comparison' ),
    ) );

    add_image_size( 'operator-logo', 100, 100, false );
    add_image_size( 'team-logo', 64, 64, false );
    add_image_size( 'match-thumbnail', 400, 250, true );

    global $content_width;
    $content_width = 1280;
}
add_action( 'after_setup_theme', 'oc_theme_setup' );

/* =====================================================
 * ENQUEUE SCRIPTS & STYLES
 * ===================================================== */

function oc_enqueue_scripts() {

    /* Styles */
    wp_enqueue_style(
        'oc-main-style',
        OC_THEME_URI . '/style.css',
        array(),
        OC_THEME_VERSION
    );

    wp_enqueue_style(
        'oc-odds-style',
        OC_ASSETS_URI . '/css/odds-comparison.css',
        array( 'oc-main-style' ),
        OC_THEME_VERSION
    );

    if ( is_rtl() ) {
        wp_enqueue_style(
            'oc-rtl-style',
            OC_THEME_URI . '/rtl.css',
            array( 'oc-odds-style' ),
            OC_THEME_VERSION
        );
    }

    /* Scripts */
    wp_enqueue_script(
        'oc-main-js',
        OC_ASSETS_URI . '/js/odds-comparison.js',
        array( 'jquery' ),
        OC_THEME_VERSION,
        true
    );

    wp_localize_script(
        'oc-main-js',
        'oc_ajax',
        array(
            'ajax_url'    => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'oc_ajax_nonce' ),
            'loadingText' => __( 'Loading...', 'odds-comparison' ),
            'errorText'   => __( 'An error occurred. Please try again.', 'odds-comparison' ),
        )
    );

    /* Conditional Odds Loader */
    if ( is_page_template( 'templates/home.php' ) || is_singular( 'match' ) ) {
        wp_enqueue_script(
            'oc-odds-loader',
            OC_ASSETS_URI . '/js/odds-loader.js',
            array( 'jquery', 'oc-main-js' ),
            OC_THEME_VERSION,
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'oc_enqueue_scripts' );

/**
 * Add defer/async attributes to scripts
 * 
 * @since 1.0.0
 * 
 * @param string $tag    The script tag
 * @param string $handle The script handle
 * @return string Modified script tag
 */
function oc_script_attributes( $tag, $handle ) {
    // Add defer to main.js
    if ( 'oc-main-js' === $handle ) {
        return str_replace( ' src=', ' defer src=', $tag );
    }
    return $tag;
}
add_filter( 'script_loader_tag', 'oc_script_attributes', 10, 2 );

/**
 * Add preconnect for external resources
 * 
 * @since 1.0.0
 */
function oc_resource_hints( $urls, $relation_type ) {
    if ( 'preconnect' === $relation_type ) {
        $urls[] = array(
            'href' => 'https://fonts.googleapis.com',
            'crossorigin',
        );
        $urls[] = array(
            'href' => 'https://fonts.gstatic.com',
            'crossorigin',
        );
    }
    return $urls;
}
add_filter( 'wp_resource_hints', 'oc_resource_hints', 10, 2 );

/**
 * Register widget areas
 * 
 * @since 1.0.0
 */
function oc_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Sidebar', 'odds-comparison' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Add widgets here to appear in the sidebar.', 'odds-comparison' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
    
    register_sidebar( array(
        'name'          => __( 'Footer Column 1', 'odds-comparison' ),
        'id'            => 'footer-1',
        'description'   => __( 'First footer column widgets.', 'odds-comparison' ),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-widget-title">',
        'after_title'   => '</h3>',
    ) );
    
    register_sidebar( array(
        'name'          => __( 'Footer Column 2', 'odds-comparison' ),
        'id'            => 'footer-2',
        'description'   => __( 'Second footer column widgets.', 'odds-comparison' ),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-widget-title">',
        'after_title'   => '</h3>',
    ) );
    
    register_sidebar( array(
        'name'          => __( 'Footer Column 3', 'odds-comparison' ),
        'id'            => 'footer-3',
        'description'   => __( 'Third footer column widgets.', 'odds-comparison' ),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-widget-title">',
        'after_title'   => '</h3>',
    ) );
    
    register_sidebar( array(
        'name'          => __( 'Footer Column 4', 'odds-comparison' ),
        'id'            => 'footer-4',
        'description'   => __( 'Fourth footer column widgets.', 'odds-comparison' ),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'oc_widgets_init' );

/**
 * Disable emoji scripts (performance optimization)
 * 
 * @since 1.0.0
 */
function oc_disable_emojis() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'oc_disable_emojis' );

/**
 * Add body classes
 * 
 * @since 1.0.0
 * 
 * @param array $classes Body classes
 * @return array Modified body classes
 */
function oc_body_classes( $classes ) {
    // Add class for mobile detection
    if ( wp_is_mobile() ) {
        $classes[] = 'is-mobile';
    }
    
    // Add layout class
    if ( is_active_sidebar( 'sidebar-1' ) && ! is_page() ) {
        $classes[] = 'has-sidebar';
    } else {
        $classes[] = 'no-sidebar';
    }
    
    // Add custom class for odds comparison pages
    if ( is_singular( 'operator' ) || is_singular( 'match' ) || is_post_type_archive( 'operator' ) || is_post_type_archive( 'match' ) ) {
        $classes[] = 'odds-comparison-page';
    }
    
    return $classes;
}
add_filter( 'body_class', 'oc_body_classes' );

/**
 * Custom excerpt length
 * 
 * @since 1.0.0
 * 
 * @param int $length Excerpt length
 * @return int Modified excerpt length
 */
function oc_excerpt_length( $length ) {
    return 20;
}
add_filter( 'excerpt_length', 'oc_excerpt_length' );

/**
 * Custom excerpt more
 * 
 * @since 1.0.0
 * 
 * @param string $more Excerpt more string
 * @return string Modified excerpt more
 */
function oc_excerpt_more( $more ) {
    return '&hellip;';
}
add_filter( 'excerpt_more', 'oc_excerpt_more' );

/**
 * Disable XML RPC (security)
 * 
 * @since 1.0.0
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Remove version from scripts and styles (cleaner HTML)
 * 
 * @since 1.0.0
 * 
 * @param string $src Script/stylesheet URL
 * @return string Modified URL
 */
function oc_remove_script_version( $src ) {
    $parts = explode( '?', $src );
    return $parts[0];
}
add_filter( 'script_loader_src', 'oc_remove_script_version', 15, 1 );
add_filter( 'style_loader_src', 'oc_remove_script_version', 15, 1 );

/**
 * Set up theme defaults and support for various WordPress features
 * 
 * Note: This function is hooked to 'after_setup_theme' only if the theme
 * is being activated for the first time.
 * 
 * @since 1.0.0
 */
function oc_theme_activation() {
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Import demo data if not already imported
    if ( ! get_option( 'oc_demo_data_imported', false ) ) {
        oc_import_demo_data_on_activation();
    }
}
add_action( 'after_switch_theme', 'oc_theme_activation' );

/**
 * Import demo data on theme activation
 * 
 * This runs on theme activation to ensure all demo data is imported
 * and required pages are created.
 * 
 * @since 1.0.0
 */
function oc_import_demo_data_on_activation() {
    // Create database tables
    oc_create_database_tables();
    
    // Import demo data
    oc_import_demo_data();
    
    // Mark as imported
    update_option( 'oc_demo_data_imported', true );
    update_option( 'oc_demo_data_imported_date', current_time( 'mysql' ) );
}

/**
 * Theme deactivation cleanup
 * 
 * @since 1.0.0
 */
function oc_theme_deactivation() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
add_action( 'switch_theme', 'oc_theme_deactivation' );

/**
 * Display custom copyright in admin footer
 * 
 * @since 1.0.0
 * 
 * @param string $footer_text Footer text
 * @return string Modified footer text
 */
function oc_admin_footer_text( $footer_text ) {
    $screen = get_current_screen();
    
    // Only modify on theme-specific pages
    if ( in_array( $screen->id, array( 'themes', 'theme-install', 'appearance_page_oc-settings' ) ) ) {
        $footer_text = sprintf(
            /* translators: %1$s: Theme name, %2$s: Theme author */
            __( 'Thank you for using %1$s theme by %2$s', 'odds-comparison' ),
            'Odds Comparison',
            '<a href="https://example.com" target="_blank">Theme Developer</a>'
        );
    }
    
    return $footer_text;
}
add_filter( 'admin_footer_text', 'oc_admin_footer_text' );

/**
 * Add custom admin notice for theme settings
 * 
 * @since 1.0.0
 */
function oc_admin_notices() {
    // Check if user can manage options
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    // Check if settings have been saved
    if ( ! get_option( 'oc_theme_activated' ) ) {
        add_action( 'admin_notices', 'oc_theme_activation_notice' );
    }
}

/**
 * Display theme activation notice
 * 
 * @since 1.0.0
 */
function oc_theme_activation_notice() {
    echo '<div class="notice notice-success is-dismissible">';
    echo '<p><strong>' . esc_html__( 'Odds Comparison Theme Activated!', 'odds-comparison' ) . '</strong></p>';
    echo '<p>' . esc_html__( 'Configure your theme settings:', 'odds-comparison' ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=oc-settings' ) ) . '">' . esc_html__( 'Theme Settings', 'odds-comparison' ) . '</a></p>';
    echo '</div>';
    
    // Mark as activated
    update_option( 'oc_theme_activated', true );
}
add_action( 'admin_notices', 'oc_admin_notices' );

function oc_output_seo_meta_tags() {

    if ( is_front_page() ) {
        $title       = get_bloginfo( 'name' );
        $description = get_bloginfo( 'description' );
    } else {
        $title       = single_post_title( '', false );
        $description = get_the_excerpt() ?: get_bloginfo( 'description' );
    }

    echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $description ) ) . '">' . "\n";

    if ( is_singular( 'match' ) ) {
        $home = get_post_meta( get_the_ID(), 'oc_home_team', true );
        $away = get_post_meta( get_the_ID(), 'oc_away_team', true );
        if ( $home && $away ) {
            echo '<meta name="keywords" content="' .
                esc_attr( "$home vs $away odds, betting, comparison" ) .
            '">' . "\n";
        }
    }

    echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( wp_strip_all_tags( $description ) ) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:url" content="' . esc_url( get_permalink() ) . '">' . "\n";

    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}
add_action( 'wp_head', 'oc_output_seo_meta_tags', 5 );
