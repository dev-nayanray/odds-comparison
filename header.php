<?php
/**
 * The header template file
 *
 * Contains the <head> section and opening tags up to <div class="site-wrapper">
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}



// Get quota format from settings
$oc_options = get_option( 'oc_theme_options', array() );
$current_format = isset( $oc_options['odds_format'] ) ? $oc_options['odds_format'] : 'decimal';
$quota_notifications = 4; // Dynamic value - could be from transient/option

?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php wp_head(); ?>

    <!-- DNS Prefetch -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">

    <!-- Favicons -->
    <link rel="icon" href="<?php echo esc_url( OC_ASSETS_URI . '/images/favicon.ico' ); ?>" sizes="32x32">
    <link rel="icon" href="<?php echo esc_url( OC_ASSETS_URI . '/images/favicon.svg' ); ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?php echo esc_url( OC_ASSETS_URI . '/images/apple-touch-icon.png' ); ?>">
</head>

<body <?php body_class(); ?>>

<?php
// Hook for content before site wrapper
do_action( 'oc_before_site_wrapper' );
?>

<div class="site-wrapper">

    <?php
    // Hook for content before header
    do_action( 'oc_before_header' );
    ?>

    <!-- Top Bar with Navigation Slider -->
    <div class="oc-top-bar">
        <div class="container">
            <div class="oc-top-bar-inner">
                <!-- Slider Navigation -->
                <div class="oc-slider-nav oc-slider-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'odds-comparison' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </div>

                <!-- Slider Container -->
                <div class="oc-slider-container">
                    <div class="oc-slider-track">
                        <a href="#" class="oc-slider-item"><?php esc_html_e( 'DEPOSIT BONUSES', 'odds-comparison' ); ?></a>
                        <a href="#" class="oc-slider-item"><?php esc_html_e( 'BETTING HOUSES IN SPAIN', 'odds-comparison' ); ?></a>
                        <a href="#" class="oc-slider-item"><?php esc_html_e( 'CASINOS SPAIN', 'odds-comparison' ); ?></a>
                        <a href="#" class="oc-slider-item"><?php esc_html_e( 'CASINO MEXICO', 'odds-comparison' ); ?></a>
                        <a href="#" class="oc-slider-item"><?php esc_html_e( 'BETTING HOUSES IN PERU', 'odds-comparison' ); ?></a>
                        <a href="#" class="oc-slider-item"><?php esc_html_e( 'FORECASTS', 'odds-comparison' ); ?></a>
                    </div>
                </div>

                <!-- Slider Navigation -->
                <div class="oc-slider-nav oc-slider-next" aria-label="<?php esc_attr_e( 'Next slide', 'odds-comparison' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header id="masthead" class="site-header">
        <div class="container">
            <div class="header-inner">

                <!-- Site Logo - Modern Design -->
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" rel="home" aria-label="<?php bloginfo( 'name' ); ?>">
                    <?php
                    // Custom logo
                    $custom_logo_id = get_theme_mod( 'custom_logo' );
                    if ( $custom_logo_id ) {
                        $logo = wp_get_attachment_image_src( $custom_logo_id, 'full' );
                        echo '<img src="' . esc_url( $logo[0] ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" width="' . esc_attr( $logo[1] ) . '" height="' . esc_attr( $logo[2] ) . '">';
                    } else {
                        // Modern text logo with icon
                        echo '<div class="logo-icon">';
                        echo '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
                        echo '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>';
                        echo '</svg>';
                        echo '</div>';
                        echo '<span class="logo-text">';
                        echo '<span class="logo-main">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
                        echo '<span class="logo-tagline">' . esc_html__( 'Odds Comparison', 'odds-comparison' ) . '</span>';
                        echo '</span>';
                    }
                    ?>
                </a>

                <!-- Quota Format Dropdown -->
                <div class="oc-quota-selector">
                    <div class="oc-quota-dropdown">
                        <button class="oc-quota-btn" aria-expanded="false" aria-haspopup="true">
                            <span class="oc-quota-label"><?php esc_html_e( 'Quota format', 'odds-comparison' ); ?></span>
                            <span class="oc-quota-value"><?php echo esc_html( strtoupper( $current_format ) ); ?></span>
                            <svg class="oc-quota-arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="oc-quota-menu">
                            <a href="#" class="oc-quota-option <?php echo $current_format === 'decimal' ? 'active' : ''; ?>" data-format="decimal">
                                <span class="oc-quota-symbol">1.85</span>
                                <span class="oc-quota-name"><?php esc_html_e( 'Decimal', 'odds-comparison' ); ?></span>
                            </a>
                            <a href="#" class="oc-quota-option <?php echo $current_format === 'fractional' ? 'active' : ''; ?>" data-format="fractional">
                                <span class="oc-quota-symbol">5/4</span>
                                <span class="oc-quota-name"><?php esc_html_e( 'Fractional', 'odds-comparison' ); ?></span>
                            </a>
                            <a href="#" class="oc-quota-option <?php echo $current_format === 'american' ? 'active' : ''; ?>" data-format="american">
                                <span class="oc-quota-symbol">+125</span>
                                <span class="oc-quota-name"><?php esc_html_e( 'American', 'odds-comparison' ); ?></span>
                            </a>
                            <a href="#" class="oc-quota-option <?php echo $current_format === 'hongkong' ? 'active' : ''; ?>" data-format="hongkong">
                                <span class="oc-quota-symbol">0.85</span>
                                <span class="oc-quota-name"><?php esc_html_e( 'Hong Kong', 'odds-comparison' ); ?></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Actions -->
                <div class="oc-user-actions">
                    <!-- Login Button -->
                    <a href="<?php echo esc_url( wp_login_url() ); ?>" class="oc-login-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        <span><?php esc_html_e( 'Login', 'odds-comparison' ); ?></span>
                    </a>

                    <!-- Coupon/Betslip -->
                    <a href="#" class="oc-coupon-btn" id="oc-coupon-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 2v20l3-2 3 2 3-2 3 2 3-2 3 2V2l-3 2-3-2-3 2-3-2-3 2-3-2z"></path>
                            <line x1="4" y1="10" x2="20" y2="10"></line>
                        </svg>
                        <span><?php esc_html_e( 'Coupon', 'odds-comparison' ); ?></span>
                        <?php if ( $quota_notifications > 0 ) : ?>
                        <span class="oc-coupon-count"><?php echo esc_html( $quota_notifications ); ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Mobile Menu Toggle -->
                <button class="oc-mobile-toggle" aria-label="<?php esc_attr_e( 'Toggle menu', 'odds-comparison' ); ?>">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>

            </div>
        </div>
    </header>

    <!-- Main Navigation -->
    <nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'odds-comparison' ); ?>">
        <div class="container">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'primary',
                'menu_id'        => 'primary-menu',
                'menu_class'     => 'primary-menu',
                'container'      => false,
                'fallback_cb'    => 'oc_primary_menu_fallback',
                'depth'          => 3,
            ) );
            ?>
        </div>
    </nav>

    <?php
    // Hook for content after header
    do_action( 'oc_after_header' );
    ?>

    <!-- Coupon Popup Modal -->
    <div id="oc-coupon-popup" class="oc-coupon-popup-overlay" style="display: none;">
        <div class="oc-coupon-popup">
            <div class="oc-coupon-header">
                <div class="oc-coupon-title">
                    <span class="oc-coupon-count">4</span>
                    <span class="oc-coupon-label"><?php esc_html_e( 'Combined accumulator', 'odds-comparison' ); ?></span>
                </div>
                <button class="oc-coupon-close" aria-label="<?php esc_attr_e( 'Close coupon', 'odds-comparison' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="oc-coupon-content">
                <!-- Current Bets -->
                <div class="oc-coupon-bets">
                    <div class="oc-coupon-bet-item">
                        <div class="oc-bet-info">
                            <span class="oc-bet-type"><?php esc_html_e( 'Bet', 'odds-comparison' ); ?></span>
                            <span class="oc-bet-match"><?php esc_html_e( 'Draw', 'odds-comparison' ); ?> - <?php esc_html_e( 'Atlético Madrid v Real Madrid', 'odds-comparison' ); ?></span>
                        </div>
                        <div class="oc-bet-odds">1.85</div>
                    </div>

                    <div class="oc-coupon-bet-item">
                        <div class="oc-bet-info">
                            <span class="oc-bet-type"><?php esc_html_e( 'Winner', 'odds-comparison' ); ?></span>
                            <span class="oc-bet-match"><?php esc_html_e( 'Olympique Marseille', 'odds-comparison' ); ?> - <?php esc_html_e( 'PSG v Olympique Marseille', 'odds-comparison' ); ?></span>
                        </div>
                        <div class="oc-bet-odds">2.10</div>
                    </div>

                    <div class="oc-coupon-bet-item">
                        <div class="oc-bet-info">
                            <span class="oc-bet-type"><?php esc_html_e( 'Winner', 'odds-comparison' ); ?></span>
                            <span class="oc-bet-match"><?php esc_html_e( 'Real Sociedad', 'odds-comparison' ); ?> - <?php esc_html_e( 'Getafe v Real Sociedad', 'odds-comparison' ); ?></span>
                        </div>
                        <div class="oc-bet-odds">1.95</div>
                    </div>

                    <div class="oc-coupon-bet-item">
                        <div class="oc-bet-info">
                            <span class="oc-bet-type"><?php esc_html_e( 'Handicap', 'odds-comparison' ); ?></span>
                            <span class="oc-bet-match"><?php esc_html_e( 'Atlético Madrid', 'odds-comparison' ); ?> - <?php esc_html_e( 'Atlético Madrid v Real Madrid', 'odds-comparison' ); ?></span>
                        </div>
                        <div class="oc-bet-odds">1.75</div>
                    </div>
                </div>

                <!-- Bookmaker Options -->
                <div class="oc-coupon-bookmakers">
                    <div class="oc-bookmaker-option oc-current-bookmaker">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/luckia-logo.png' ); ?>" alt="Luckia" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Luckia', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>56.95</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€570</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-bet-with-bookmaker"><?php esc_html_e( 'Bet with this bookmaker', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <span class="oc-bet-alternative"><?php esc_html_e( 'Bet with another Bookmaker', 'odds-comparison' ); ?>:</span>
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/casumo-logo.png' ); ?>" alt="Casumo Sports Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Casumo Sports Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>55.78</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€558</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/yosports-logo.png' ); ?>" alt="YoSports Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'YoSports Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>55.78</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€558</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/gran-madrid-logo.png' ); ?>" alt="Casino Gran Madrid Sportsbook Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Casino Gran Madrid Sportsbook Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>52.19</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€522</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/tonybet-logo.png' ); ?>" alt="TonyBet Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'TonyBet Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>51.03</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€510</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/1xbet-logo.png' ); ?>" alt="1xBet Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( '1xBet Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>46.85</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€468</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/jokerbet-logo.png' ); ?>" alt="Jokerbet Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Jokerbet Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>46.56</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€466</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/paston-logo.png' ); ?>" alt="Paston" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Paston', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>46.37</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€464</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/sportium-logo.png' ); ?>" alt="Sportium" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Sportium', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>45.94</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€459</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/brand-logo.png' ); ?>" alt="Brand" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Brand', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>45.21</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€452</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/retabet-logo.png' ); ?>" alt="Retabet Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Retabet Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>43.22</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€432</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/interwetten-logo.png' ); ?>" alt="Interwetten Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Interwetten Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>42.88</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€429</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/betway-logo.png' ); ?>" alt="Betway" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Betway', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>42.75</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€428</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/bet365-logo.png' ); ?>" alt="Bet365 Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Bet365 Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>42.39</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€424</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/bwin-logo.png' ); ?>" alt="Bwin Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Bwin Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>42.34</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€423</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/888-logo.png' ); ?>" alt="888 Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( '888 Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>40.43</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€404</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/william-hill-logo.png' ); ?>" alt="William Hill" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'William Hill', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>40.43</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€404</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/betfair-sportsbook-logo.png' ); ?>" alt="Betfair Sportsbook Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Betfair Sportsbook Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>40.25</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€403</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/codere-logo.png' ); ?>" alt="Codere Sportsbook Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Codere Sportsbook Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>39.60</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€396</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/olybet-logo.png' ); ?>" alt="Olybet Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Olybet Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>12.74</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€127</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>

                    <div class="oc-bookmaker-option">
                        <div class="oc-bookmaker-header">
                            <div class="oc-bookmaker-info">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/betfair-logo.png' ); ?>" alt="Betfair Spain" class="oc-bookmaker-logo">
                                <div class="oc-bookmaker-details">
                                    <span class="oc-bookmaker-name"><?php esc_html_e( 'Betfair Spain', 'odds-comparison' ); ?></span>
                                    <div class="oc-bet-calculation">
                                        <span class="oc-fees"><?php esc_html_e( 'Fees', 'odds-comparison' ); ?>: <strong>46.72</strong></span>
                                        <span class="oc-amount"><?php esc_html_e( 'Amount', 'odds-comparison' ); ?>: <strong>€10</strong></span>
                                        <span class="oc-earnings"><?php esc_html_e( 'Earnings', 'odds-comparison' ); ?>: <strong>€467</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="oc-register-bookmaker"><?php esc_html_e( 'Register', 'odds-comparison' ); ?></button>
                    </div>
                </div>

                <!-- Delete All Button -->
                <div class="oc-coupon-actions">
                    <button class="oc-delete-all-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="m19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                        <?php esc_html_e( 'Delete all', 'odds-comparison' ); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="site-content">

