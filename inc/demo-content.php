<?php
/**
 * Demo Content Data File
 * 
 * Contains all demo content data arrays for the Theme Demo Import System.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OC_Demo_Content {

    public static function get_theme_options() {
        return array(
            'matches_per_page'     => 10,
            'odds_decimal_places'  => 2,
            'show_live_matches'    => 1,
            'ranking_rating_weight' => 40,
            'ranking_bonus_weight' => 30,
            'ranking_license_weight' => 20,
            'ranking_odds_weight'  => 10,
            'affiliate_tracking_enabled' => 1,
            'affiliate_cookie_days' => 30,
        );
    }

    public static function get_customizer_settings() {
        return array(
            'colors' => array(
                'primary_color'     => '#1a5f7a',
                'secondary_color'   => '#57837b',
                'accent_color'      => '#c38154',
                'background_color'  => '#f5f5f5',
            ),
            'typography' => array(
                'font_family'       => 'Inter',
                'font_size'         => 16,
            ),
            'layout' => array(
                'container_width'   => 1280,
                'sidebar_position'  => 'right',
            ),
        );
    }

    public static function get_widgets() {
        return array(
            'sidebar-1' => array(
                array(
                    'id'   => 'search',
                    'type' => 'widget',
                    'settings' => array(
                        'title' => __( 'Search', 'odds-comparison' ),
                    ),
                ),
            ),
            'footer-1' => array(
                array(
                    'id'   => 'custom-html',
                    'type' => 'widget',
                    'settings' => array(
                        'content' => '<h3>' . esc_html__( 'About Us', 'odds-comparison' ) . '</h3><p>' . esc_html__( 'Best odds comparison service.', 'odds-comparison' ) . '</p>',
                    ),
                ),
            ),
            'footer-2' => array(
                array(
                    'id'   => 'custom-html',
                    'type' => 'widget',
                    'settings' => array(
                        'content' => '<h3>' . esc_html__( 'Quick Links', 'odds-comparison' ) . '</h3><ul><li><a href="' . esc_url( home_url( '/matches/' ) ) . '">' . esc_html__( 'Matches', 'odds-comparison' ) . '</a></li></ul>',
                    ),
                ),
            ),
        );
    }

    public static function get_menus() {
        return array(
            'primary' => array(
                'home' => array(
                    'title'      => __( 'Home', 'odds-comparison' ),
                    'url'        => home_url( '/' ),
                    'target'     => '',
                    'classes'    => array(),
                ),
                'matches' => array(
                    'title'      => __( 'Matches', 'odds-comparison' ),
                    'url'        => home_url( '/matches/' ),
                    'target'     => '',
                    'classes'    => array(),
                ),
                'operators' => array(
                    'title'      => __( 'Operators', 'odds-comparison' ),
                    'url'        => home_url( '/operators/' ),
                    'target'     => '',
                    'classes'    => array(),
                ),
            ),
            'footer' => array(
                'home' => array(
                    'title'      => __( 'Home', 'odds-comparison' ),
                    'url'        => home_url( '/' ),
                    'target'     => '',
                    'classes'    => array(),
                ),
            ),
        );
    }

    public static function get_pages() {
        return array(
            'home' => array(
                'title'       => __( 'Home', 'odds-comparison' ),
                'content'     => '',
                'template'    => 'templates/home.php',
            ),
            'matches' => array(
                'title'       => __( 'All Matches', 'odds-comparison' ),
                'content'     => '',
                'template'    => 'templates/archive-match.php',
            ),
            'operators' => array(
                'title'       => __( 'Betting Operators', 'odds-comparison' ),
                'content'     => '',
                'template'    => 'templates/archive-operator.php',
            ),
        );
    }

    public static function get_sports() {
        return array(
            'football' => array(
                'name'        => __( 'Football', 'odds-comparison' ),
                'slug'        => 'football',
                'description' => __( 'Soccer/Association Football', 'odds-comparison' ),
            ),
            'basketball' => array(
                'name'        => __( 'Basketball', 'odds-comparison' ),
                'slug'        => 'basketball',
                'description' => __( 'Basketball matches', 'odds-comparison' ),
            ),
            'tennis' => array(
                'name'        => __( 'Tennis', 'odds-comparison' ),
                'slug'        => 'tennis',
                'description' => __( 'Tennis tournaments', 'odds-comparison' ),
            ),
        );
    }

    public static function get_leagues() {
        return array(
            'premier-league' => array(
                'name'        => __( 'Premier League', 'odds-comparison' ),
                'slug'        => 'premier-league',
                'sport'       => 'football',
                'description' => __( 'English Premier League', 'odds-comparison' ),
            ),
            'la-liga' => array(
                'name'        => __( 'La Liga', 'odds-comparison' ),
                'slug'        => 'la-liga',
                'sport'       => 'football',
                'description' => __( 'Spanish Primera División', 'odds-comparison' ),
            ),
            'bundesliga' => array(
                'name'        => __( 'Bundesliga', 'odds-comparison' ),
                'slug'        => 'bundesliga',
                'sport'       => 'football',
                'description' => __( 'German Bundesliga', 'odds-comparison' ),
            ),
        );
    }

    public static function get_teams() {
        return array(
            'man-city' => array(
                'name'        => __( 'Manchester City', 'odds-comparison' ),
                'slug'        => 'man-city',
                'short_name'  => 'MCI',
                'country'     => __( 'England', 'odds-comparison' ),
            ),
            'liverpool' => array(
                'name'        => __( 'Liverpool', 'odds-comparison' ),
                'slug'        => 'liverpool',
                'short_name'  => 'LIV',
                'country'     => __( 'England', 'odds-comparison' ),
            ),
            'arsenal' => array(
                'name'        => __( 'Arsenal', 'odds-comparison' ),
                'slug'        => 'arsenal',
                'short_name'  => 'ARS',
                'country'     => __( 'England', 'odds-comparison' ),
            ),
            'real-madrid' => array(
                'name'        => __( 'Real Madrid', 'odds-comparison' ),
                'slug'        => 'real-madrid',
                'short_name'  => 'RMA',
                'country'     => __( 'Spain', 'odds-comparison' ),
            ),
            'barcelona' => array(
                'name'        => __( 'Barcelona', 'odds-comparison' ),
                'slug'        => 'barcelona',
                'short_name'  => 'FCB',
                'country'     => __( 'Spain', 'odds-comparison' ),
            ),
            'bayern-munich' => array(
                'name'        => __( 'Bayern Munich', 'odds-comparison' ),
                'slug'        => 'bayern-munich',
                'short_name'  => 'BAY',
                'country'     => __( 'Germany', 'odds-comparison' ),
            ),
        );
    }

    public static function get_operators() {
        return array(
            'bet365' => array(
                'title'         => 'Bet365',
                'content'       => __( 'Bet365 is one of the world\'s leading online gambling companies.', 'odds-comparison' ),
                'license'       => 'Gibraltar',
                'rating'        => 4.8,
                'affiliate_url' => 'https://www.bet365.com/',
                'bonus_text'    => '100% up to €100',
                'bonus_value'   => 100,
            ),
            'betway' => array(
                'title'         => 'Betway',
                'content'       => __( 'Betway is a global online betting platform.', 'odds-comparison' ),
                'license'       => 'MGA',
                'rating'        => 4.5,
                'affiliate_url' => 'https://www.betway.com/',
                'bonus_text'    => '100% up to €200',
                'bonus_value'   => 200,
            ),
            'unibet' => array(
                'title'         => 'Unibet',
                'content'       => __( 'Unibet is a trusted name in online betting.', 'odds-comparison' ),
                'license'       => 'MGA',
                'rating'        => 4.4,
                'affiliate_url' => 'https://www.unibet.com/',
                'bonus_text'    => '€30 Free Bet',
                'bonus_value'   => 30,
            ),
        );
    }

    public static function get_matches() {
        return array(
            array(
                'title'       => 'Manchester City vs Liverpool',
                'home_team'   => 'man-city',
                'away_team'   => 'liverpool',
                'league'      => 'premier-league',
                'sport'       => 'football',
                'date'        => date( 'Y-m-d', strtotime( '+3 days' ) ),
                'time'        => '20:00',
                'status'      => 'upcoming',
            ),
            array(
                'title'       => 'Arsenal vs Manchester United',
                'home_team'   => 'arsenal',
                'away_team'   => 'man-utd',
                'league'      => 'premier-league',
                'sport'       => 'football',
                'date'        => date( 'Y-m-d', strtotime( '+5 days' ) ),
                'time'        => '17:30',
                'status'      => 'upcoming',
            ),
            array(
                'title'       => 'Real Madrid vs Barcelona',
                'home_team'   => 'real-madrid',
                'away_team'   => 'barcelona',
                'league'      => 'champions-league',
                'sport'       => 'football',
                'date'        => date( 'Y-m-d', strtotime( '+7 days' ) ),
                'time'        => '20:00',
                'status'      => 'upcoming',
            ),
        );
    }

    public static function get_odds_ranges() {
        return array(
            'bet365'    => array( 'min' => 1.75, 'max' => 2.35 ),
            'betway'    => array( 'min' => 1.70, 'max' => 2.30 ),
            'unibet'    => array( 'min' => 1.73, 'max' => 2.33 ),
        );
    }
}

