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

/**
 * Import demo data after theme files are loaded
 *
 * @since 1.0.0
 */
function oc_import_demo_data_after_load() {
    // Import demo data if not already imported
    if ( ! get_option( 'oc_demo_data_imported', false ) ) {
        oc_import_demo_data_on_activation();
    }
}
add_action( 'after_setup_theme', 'oc_import_demo_data_after_load', 20 );

/* =====================================================
 * THEME SETUP
 * ===================================================== */

function oc_theme_setup() {

add_theme_support( 'title-tag' );

// Fix for document title parts filter
add_filter( 'document_title_parts', 'oc_fix_document_title_parts' );
function oc_fix_document_title_parts( $parts ) {
    if ( ! is_array( $parts ) ) {
        $parts = array( $parts );
    }
    return $parts;
}
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

    // Coupon popup styles
    wp_enqueue_style(
        'oc-coupon-style',
        OC_ASSETS_URI . '/css/coupon.css',
        array( 'oc-odds-style' ),
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

/**
 * Handle AJAX request to save odds format preference
 *
 * @since 1.0.0
 */
function oc_save_odds_format() {
    // Check nonce for security
    if ( ! wp_verify_nonce( $_POST['nonce'], 'oc_ajax_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'odds-comparison' ) ) );
    }

    // Check if user is logged in
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => __( 'You must be logged in to save preferences.', 'odds-comparison' ) ) );
    }

    $format = sanitize_text_field( $_POST['format'] );

    // Validate format
    $valid_formats = array( 'decimal', 'fractional', 'american' );
    if ( ! in_array( $format, $valid_formats ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid odds format.', 'odds-comparison' ) ) );
    }

    // Save to user meta
    update_user_meta( get_current_user_id(), 'oc_odds_format', $format );

    wp_send_json_success( array( 'message' => __( 'Preference saved successfully.', 'odds-comparison' ) ) );
}
add_action( 'wp_ajax_oc_save_odds_format', 'oc_save_odds_format' );

/**
 * Get user's preferred odds format
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID
 * @return string Odds format (decimal, fractional, american)
 */
function oc_get_user_odds_format( $user_id = null ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    $format = get_user_meta( $user_id, 'oc_odds_format', true );

    // Default to decimal if not set
    if ( empty( $format ) ) {
        $format = 'decimal';
    }

    return $format;
}

/**
 * Enable user registration if not already enabled
 *
 * @since 1.0.0
 */
function oc_enable_user_registration() {
    // Enable user registration in WordPress settings
    update_option( 'users_can_register', 1 );
}
add_action( 'after_setup_theme', 'oc_enable_user_registration' );

/**
 * Handle user registration
 *
 * @since 1.0.0
 */
function oc_handle_registration() {
    if ( isset( $_POST['oc_register_submit'] ) && wp_verify_nonce( $_POST['oc_register_nonce'], 'oc_register_action' ) ) {
        $username = sanitize_user( $_POST['username'] );
        $email = sanitize_email( $_POST['email'] );
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        $errors = array();

        // Validation
        if ( empty( $username ) ) {
            $errors[] = __( 'Username is required.', 'odds-comparison' );
        } elseif ( username_exists( $username ) ) {
            $errors[] = __( 'Username already exists.', 'odds-comparison' );
        }

        if ( empty( $email ) ) {
            $errors[] = __( 'Email is required.', 'odds-comparison' );
        } elseif ( ! is_email( $email ) ) {
            $errors[] = __( 'Invalid email address.', 'odds-comparison' );
        } elseif ( email_exists( $email ) ) {
            $errors[] = __( 'Email already exists.', 'odds-comparison' );
        }

        if ( empty( $password ) ) {
            $errors[] = __( 'Password is required.', 'odds-comparison' );
        } elseif ( strlen( $password ) < 6 ) {
            $errors[] = __( 'Password must be at least 6 characters.', 'odds-comparison' );
        }

        if ( $password !== $confirm_password ) {
            $errors[] = __( 'Passwords do not match.', 'odds-comparison' );
        }

        if ( empty( $errors ) ) {
            $user_id = wp_create_user( $username, $password, $email );

            if ( ! is_wp_error( $user_id ) ) {
                // Log the user in
                wp_set_current_user( $user_id );
                wp_set_auth_cookie( $user_id );

                // Redirect to profile page
                wp_redirect( home_url( '/profile' ) );
                exit;
            } else {
                $errors[] = $user_id->get_error_message();
            }
        }

        // Store errors in session for display
        if ( ! empty( $errors ) ) {
            set_transient( 'oc_registration_errors', $errors, 30 );
            wp_redirect( get_permalink() );
            exit;
        }
    }
}
add_action( 'template_redirect', 'oc_handle_registration' );

/**
 * Handle password reset
 *
 * @since 1.0.0
 */
function oc_handle_password_reset() {
    if ( isset( $_POST['oc_reset_submit'] ) && wp_verify_nonce( $_POST['oc_reset_nonce'], 'oc_reset_action' ) ) {
        $user_login = sanitize_user( $_POST['user_login'] );

        $errors = array();

        if ( empty( $user_login ) ) {
            $errors[] = __( 'Please enter your username or email address.', 'odds-comparison' );
        }

        if ( empty( $errors ) ) {
            $user_data = get_user_by( 'login', $user_login );

            if ( ! $user_data ) {
                $user_data = get_user_by( 'email', $user_login );
            }

            if ( $user_data ) {
                $reset_result = retrieve_password( $user_data->user_login );

                if ( is_wp_error( $reset_result ) ) {
                    $errors[] = $reset_result->get_error_message();
                } else {
                    set_transient( 'oc_reset_success', __( 'Check your email for the password reset link.', 'odds-comparison' ), 30 );
                    wp_redirect( get_permalink() );
                    exit;
                }
            } else {
                $errors[] = __( 'Invalid username or email address.', 'odds-comparison' );
            }
        }

        // Store errors in session for display
        if ( ! empty( $errors ) ) {
            set_transient( 'oc_reset_errors', $errors, 30 );
            wp_redirect( get_permalink() );
            exit;
        }
    }
}
add_action( 'template_redirect', 'oc_handle_password_reset' );

/**
 * Get registration errors
 *
 * @since 1.0.0
 * @return array
 */
function oc_get_registration_errors() {
    $errors = get_transient( 'oc_registration_errors' );
    if ( $errors ) {
        delete_transient( 'oc_registration_errors' );
        return $errors;
    }
    return array();
}

/**
 * Get password reset errors
 *
 * @since 1.0.0
 * @return array
 */
function oc_get_reset_errors() {
    $errors = get_transient( 'oc_reset_errors' );
    if ( $errors ) {
        delete_transient( 'oc_reset_errors' );
        return $errors;
    }
    return array();
}

/**
 * Get password reset success message
 *
 * @since 1.0.0
 * @return string
 */
function oc_get_reset_success() {
    $message = get_transient( 'oc_reset_success' );
    if ( $message ) {
        delete_transient( 'oc_reset_success' );
        return $message;
    }
    return '';
}

/**
 * Handle user login
 *
 * @since 1.0.0
 */
function oc_handle_login() {
    if ( isset( $_POST['oc_login_submit'] ) && wp_verify_nonce( $_POST['oc_login_nonce'], 'oc_login_action' ) ) {
        $username = sanitize_user( $_POST['username'] );
        $password = $_POST['password'];
        $remember = isset( $_POST['remember'] ) ? true : false;

        $errors = array();

        // Validation
        if ( empty( $username ) ) {
            $errors[] = __( 'Username or email is required.', 'odds-comparison' );
        }

        if ( empty( $password ) ) {
            $errors[] = __( 'Password is required.', 'odds-comparison' );
        }

        if ( empty( $errors ) ) {
            $creds = array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => $remember,
            );

            $user = wp_signon( $creds, false );

            if ( ! is_wp_error( $user ) ) {
                wp_redirect( home_url( '/profile' ) );
                exit;
            } else {
                $errors[] = $user->get_error_message();
            }
        }

        // Store errors in session for display
        if ( ! empty( $errors ) ) {
            set_transient( 'oc_login_errors', $errors, 30 );
            wp_redirect( get_permalink() );
            exit;
        }
    }
}
add_action( 'template_redirect', 'oc_handle_login' );

/**
 * Get login errors
 *
 * @since 1.0.0
 * @return array
 */
function oc_get_login_errors() {
    $errors = get_transient( 'oc_login_errors' );
    if ( $errors ) {
        delete_transient( 'oc_login_errors' );
        return $errors;
    }
    return array();
}

/**
 * Customize password reset email
 *
 * @since 1.0.0
 *
 * @param string $message Password reset message
 * @param string $key Password reset key
 * @param string $user_login User login
 * @param object $user_data User data
 * @return string Modified message
 */
function oc_password_reset_message( $message, $key, $user_login, $user_data ) {
    $site_name = get_bloginfo( 'name' );
    $reset_url = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' );

    $message = __( 'Someone has requested a password reset for the following account:', 'odds-comparison' ) . "\r\n\r\n";
    $message .= sprintf( __( 'Site Name: %s', 'odds-comparison' ), $site_name ) . "\r\n\r\n";
    $message .= sprintf( __( 'Username: %s', 'odds-comparison' ), $user_login ) . "\r\n\r\n";
    $message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'odds-comparison' ) . "\r\n\r\n";
    $message .= sprintf( __( 'To reset your password, visit the following address: %s', 'odds-comparison' ), $reset_url ) . "\r\n\r\n";

    return $message;
}
add_filter( 'retrieve_password_message', 'oc_password_reset_message', 10, 4 );

/**
 * Redirect users after login
 *
 * @since 1.0.0
 *
 * @param string $redirect_to URL to redirect to
 * @param string $request URL the user is coming from
 * @param object $user Logged in user object
 * @return string Redirect URL
 */
function oc_login_redirect( $redirect_to, $request, $user ) {
    // Redirect to profile page after login
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        return home_url( '/profile' );
    }
    return $redirect_to;
}
add_filter( 'login_redirect', 'oc_login_redirect', 10, 3 );

/**
 * Add custom user fields to profile
 *
 * @since 1.0.0
 *
 * @param object $user User object
 */
function oc_user_profile_fields( $user ) {
    ?>
    <h3><?php esc_html_e( 'Betting Preferences', 'odds-comparison' ); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="oc_odds_format"><?php esc_html_e( 'Preferred Odds Format', 'odds-comparison' ); ?></label></th>
            <td>
                <select name="oc_odds_format" id="oc_odds_format">
                    <option value="decimal" <?php selected( get_user_meta( $user->ID, 'oc_odds_format', true ), 'decimal' ); ?>><?php esc_html_e( 'Decimal (2.10)', 'odds-comparison' ); ?></option>
                    <option value="fractional" <?php selected( get_user_meta( $user->ID, 'oc_odds_format', true ), 'fractional' ); ?>><?php esc_html_e( 'Fractional (11/10)', 'odds-comparison' ); ?></option>
                    <option value="american" <?php selected( get_user_meta( $user->ID, 'oc_odds_format', true ), 'american' ); ?>><?php esc_html_e( 'American (+110)', 'odds-comparison' ); ?></option>
                </select>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'oc_user_profile_fields' );
add_action( 'edit_user_profile', 'oc_user_profile_fields' );

/**
 * Save custom user fields
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID
 */
function oc_save_user_profile_fields( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }

    if ( isset( $_POST['oc_odds_format'] ) ) {
        update_user_meta( $user_id, 'oc_odds_format', sanitize_text_field( $_POST['oc_odds_format'] ) );
    }
}
add_action( 'personal_options_update', 'oc_save_user_profile_fields' );
add_action( 'edit_user_profile_update', 'oc_save_user_profile_fields' );

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
