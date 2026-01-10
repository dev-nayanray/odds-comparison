<?php
/**
 * Demo Import Class
 * 
 * Main import system for the Theme Demo Import feature.
 * Handles importing theme options, widgets, menus, pages, taxonomies, operators, matches, and odds.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once get_template_directory() . '/inc/demo-content.php';

/**
 * Demo Import Class
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */
class OC_Demo_Import {

    /**
     * Singleton instance
     * 
     * @since 1.0.0
     * @var OC_Demo_Import
     */
    private static $instance = null;

    /**
     * Import logs
     * 
     * @since 1.0.0
     * @var array
     */
    private $logs = array();

    /**
     * Current progress percentage
     * 
     * @since 1.0.0
     * @var int
     */
    private $progress = 0;

    /**
     * Import status
     * 
     * @since 1.0.0
     * @var string
     */
    private $status = 'idle';

    /**
     * Error messages
     * 
     * @since 1.0.0
     * @var array
     */
    private $errors = array();

    /**
     * Get singleton instance
     * 
     * @since 1.0.0
     * 
     * @return OC_Demo_Import
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
        $this->init_logs();
    }

    /**
     * Initialize logs from database
     * 
     * @since 1.0.0
     */
    private function init_logs() {
        $saved_logs = get_option( 'oc_demo_import_logs', array() );
        if ( is_array( $saved_logs ) ) {
            $this->logs = $saved_logs;
        }
    }

    /**
     * Save logs to database
     * 
     * @since 1.0.0
     */
    private function save_logs() {
        update_option( 'oc_demo_import_logs', $this->logs );
    }

    /**
     * Add log entry
     * 
     * @since 1.0.0
     * 
     * @param string $type Log type: info, success, warning, error
     * @param string $message Log message
     * @param string $component Component that generated the log
     */
    public function log( $type, $message, $component = 'general' ) {
        $this->logs[] = array(
            'timestamp'  => current_time( 'mysql' ),
            'type'       => $type,
            'message'    => $message,
            'component'  => $component,
        );
        $this->save_logs();
    }

    /**
     * Clear all logs
     * 
     * @since 1.0.0
     */
    public function clear_logs() {
        $this->logs = array();
        delete_option( 'oc_demo_import_logs' );
    }

    /**
     * Get all logs
     * 
     * @since 1.0.0
     * 
     * @return array
     */
    public function get_logs() {
        return $this->logs;
    }

    /**
     * Set import status
     * 
     * @since 1.0.0
     * 
     * @param string $status Status: idle, running, completed, error
     */
    public function set_status( $status ) {
        $this->status = $status;
        update_option( 'oc_demo_import_status', $status );
    }

    /**
     * Get import status
     * 
     * @since 1.0.0
     * 
     * @return string
     */
    public function get_status() {
        if ( empty( $this->status ) ) {
            $this->status = get_option( 'oc_demo_import_status', 'idle' );
        }
        return $this->status;
    }

    /**
     * Set progress
     * 
     * @since 1.0.0
     * 
     * @param int $progress Progress percentage (0-100)
     */
    public function set_progress( $progress ) {
        $this->progress = min( 100, max( 0, $progress ) );
        update_option( 'oc_demo_import_progress', $this->progress );
    }

    /**
     * Get progress
     * 
     * @since 1.0.0
     * 
     * @return int
     */
    public function get_progress() {
        if ( empty( $this->progress ) ) {
            $this->progress = get_option( 'oc_demo_import_progress', 0 );
        }
        return $this->progress;
    }

    /**
     * Add error
     * 
     * @since 1.0.0
     * 
     * @param string $error Error message
     * @param string $component Component that generated the error
     */
    public function add_error( $error, $component = 'general' ) {
        $this->errors[] = array(
            'message'   => $error,
            'component' => $component,
        );
        $this->log( 'error', $error, $component );
    }

    /**
     * Get errors
     * 
     * @since 1.0.0
     * 
     * @return array
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Clear errors
     * 
     * @since 1.0.0
     */
    public function clear_errors() {
        $this->errors = array();
    }

    /**
     * Check if import is running
     * 
     * @since 1.0.0
     * 
     * @return bool
     */
    public function is_running() {
        return $this->get_status() === 'running';
    }

    /**
     * Run full import
     * 
     * @since 1.0.0
     * 
     * @param array $options Import options
     * @return bool Success status
     */
    public function import_all( $options = array() ) {
        if ( $this->is_running() ) {
            $this->add_error( 'Import is already running', 'system' );
            return false;
        }

        $defaults = array(
            'theme_options' => true,
            'customizer'    => true,
            'widgets'       => true,
            'menus'         => true,
            'pages'         => true,
            'taxonomies'    => true,
            'operators'     => true,
            'matches'       => true,
            'odds'          => true,
        );
        $options = wp_parse_args( $options, $defaults );

        $this->clear_errors();
        $this->clear_logs();
        $this->set_status( 'running' );
        $this->set_progress( 0 );

        $this->log( 'info', 'Starting demo import', 'system' );

        $total_steps = count( array_filter( $options ) );
        $current_step = 0;

        // Import theme options
        if ( $options['theme_options'] ) {
            $current_step++;
            $this->set_progress( floor( ( $current_step / $total_steps ) * 100 ) );
            $this->import_theme_options();
        }

        // Import customizer settings
        if ( $options['customizer'] ) {
            $current_step++;
            $this->set_progress( floor( ( $current_step / $total_steps ) * 100 ) );
            $this->import_customizer_settings();
        }

        // Import widgets
        if ( $options['widgets'] ) {
            $current_step++;
            $this->set_progress( floor( ( $current_step / $total_steps ) * 100 ) );
            $this->import_widgets();
        }

        // Import menus
        if ( $options['menus'] ) {
            $current_step++;
            $this->set_progress( floor( ( $current_step / $total_steps ) * 100 ) );
            $this->import_menus();
        }

        // Import pages
        if ( $options['pages'] ) {
            $current_step++;
            $this->set_progress( floor( ( $current_step / $total_steps ) * 100 ) );
            $this->import_pages();
        }

        // Import taxonomies
        if ( $options['taxonomies'] ) {
            $current_step++;
            $this->set_progress( floor( ( $current_step / $total_steps ) * 100 ) );
            $this->import_taxonomies();
        }

        // Import operators
        if ( $options['operators'] ) {
            $current_step++;
            $this->set_progress( floor( ( $current_step / $total_steps ) * 100 ) );
            $this->import_operators();
        }

        // Import matches
        if ( $options['matches'] ) {
            $current_step++;
            $this->set_progress( floor( ( $current_step / $total_steps ) * 100 ) );
            $this->import_matches();
        }

        // Import odds
        if ( $options['odds'] ) {
            $current_step++;
            $this->set_progress( floor( ( $current_step / $total_steps ) * 100 ) );
            $this->import_odds();
        }

        $this->set_progress( 100 );
        $this->set_status( 'completed' );
        $this->log( 'info', 'Demo import completed', 'system' );

        // Mark demo as imported
        update_option( 'oc_demo_imported', true );
        update_option( 'oc_demo_import_date', current_time( 'mysql' ) );

        return empty( $this->errors );
    }

    /**
     * Reset demo data
     * 
     * @since 1.0.0
     * 
     * @return bool Success status
     */
    public function reset_demo() {
        if ( $this->is_running() ) {
            $this->add_error( 'Cannot reset while import is running', 'system' );
            return false;
        }

        $this->set_status( 'running' );
        $this->log( 'info', 'Starting demo data reset', 'system' );

        // Delete demo content
        $this->delete_demo_posts();
        $this->delete_demo_terms();
        $this->reset_theme_options();
        $this->reset_widgets();
        $this->reset_menus();
        $this->reset_customizer();

        // Clear import flags
        delete_option( 'oc_demo_imported' );
        delete_option( 'oc_demo_import_date' );

        $this->set_status( 'idle' );
        $this->set_progress( 0 );
        $this->log( 'info', 'Demo data reset completed', 'system' );

        return true;
    }

    /**
     * Import theme options
     * 
     * @since 1.0.0
     * 
     * @return bool Success status
     */
    public function import_theme_options() {
        $this->log( 'info', 'Importing theme options', 'theme-options' );

        $options = OC_Demo_Content::get_theme_options();

        foreach ( $options as $key => $value ) {
            update_option( "oc_{$key}", $value );
        }

        $count = count( $options );
        $this->log( 'success', "Imported {$count} theme options", 'theme-options' );

        return true;
    }

    /**
     * Import customizer settings
     * 
     * @since 1.0.0
     * 
     * @return bool Success status
     */
    public function import_customizer_settings() {
        $this->log( 'info', 'Importing customizer settings', 'customizer' );

        $settings = OC_Demo_Content::get_customizer_settings();

        foreach ( $settings as $section => $values ) {
            if ( is_array( $values ) ) {
                foreach ( $values as $key => $value ) {
                    set_theme_mod( "oc_{$section}_{$key}", $value );
                }
            }
        }

        $this->log( 'success', 'Customizer settings imported', 'customizer' );

        return true;
    }

    /**
     * Import widgets
     * 
     * @since 1.0.0
     * 
     * @return bool Success status
     */
    public function import_widgets() {
        $this->log( 'info', 'Importing widgets', 'widgets' );

        $widgets = OC_Demo_Content::get_widgets();
        $sidebars = get_option( 'sidebars_widgets', array() );

        foreach ( $widgets as $sidebar_id => $widget_configs ) {
            if ( ! isset( $sidebars[ $sidebar_id ] ) ) {
                $sidebars[ $sidebar_id ] = array();
            }

            foreach ( $widget_configs as $config ) {
                $widget_id = $this->add_widget( $sidebar_id, $config );
                if ( $widget_id ) {
                    $sidebars[ $sidebar_id ][] = $widget_id;
                }
            }
        }

        update_option( 'sidebars_widgets', $sidebars );
        $this->log( 'success', 'Widgets imported', 'widgets' );

        return true;
    }

    /**
     * Add a single widget
     * 
     * @since 1.0.0
     * 
     * @param string $sidebar_id Sidebar ID
     * @param array $config Widget configuration
     * @return string|false Widget ID or false on failure
     */
    private function add_widget( $sidebar_id, $config ) {
        $widget_id = $config['id'];
        $settings = isset( $config['settings'] ) ? $config['settings'] : array();

        // Get widget type from ID
        $widget_type = str_replace( '-', '_', $widget_id );

        // Check if widget class exists
        if ( ! class_exists( "WP_Widget_{$widget_type}" ) && ! class_exists( $widget_type ) ) {
            // Use custom HTML widget as fallback
            $widget_type = 'WP_Widget_Custom_HTML';
        }

        // Get all instances of this widget
        $widget_instances = get_option( "widget_{$widget_id}", array() );

        // Find next available instance number
        $instance_number = 1;
        while ( isset( $widget_instances[ $instance_number ] ) ) {
            $instance_number++;
        }

        // Add new instance
        $widget_instances[ $instance_number ] = $settings;
        update_option( "widget_{$widget_id}", $widget_instances );

        $widget_id_with_instance = "{$widget_id}-{$instance_number}";

        return $widget_id_with_instance;
    }

    /**
     * Import menus
     * 
     * @since 1.0.0
     * 
     * @return bool Success status
     */
    public function import_menus() {
        $this->log( 'info', 'Importing menus', 'menus' );

        $menus = OC_Demo_Content::get_menus();
        $menu_locations = array();

        foreach ( $menus as $location => $items ) {
            $menu_id = $this->create_menu( $location, $items );
            if ( $menu_id ) {
                $menu_locations[ $location ] = $menu_id;
            }
        }

        // Update menu locations
        $existing_locations = get_theme_mod( 'nav_menu_locations', array() );
        $updated_locations = array_merge( $existing_locations, $menu_locations );
        set_theme_mod( 'nav_menu_locations', $updated_locations );

        $this->log( 'success', 'Menus imported', 'menus' );

        return true;
    }

    /**
     * Create a menu
     * 
     * @since 1.0.0
     * 
     * @param string $name Menu name
     * @param array $items Menu items
     * @return int|WP_Error Menu ID or error
     */
    private function create_menu( $name, $items ) {
        $menu_exists = wp_get_nav_menu_object( $name );

        if ( ! $menu_exists ) {
            $menu_id = wp_create_nav_menu( $name );
            if ( is_wp_error( $menu_id ) ) {
                $this->add_error( $menu_exists->get_error_message(), 'menus' );
                return false;
            }
        } else {
            $menu_id = $menu_exists->term_id;
        }

        foreach ( $items as $item ) {
            wp_update_nav_menu_item( $menu_id, 0, array(
                'menu-item-title'     => $item['title'],
                'menu-item-url'       => $item['url'],
                'menu-item-target'    => $item['target'],
                'menu-item-classes'   => implode( ' ', $item['classes'] ),
                'menu-item-status'    => 'publish',
            ) );
        }

        return $menu_id;
    }

    /**
     * Import pages
     * 
     * @since 1.0.0
     * 
     * @return bool Success status
     */
    public function import_pages() {
        $this->log( 'info', 'Importing pages', 'pages' );

        $pages = OC_Demo_Content::get_pages();
        $imported = 0;

        foreach ( $pages as $key => $page_data ) {
            $existing = get_page_by_path( sanitize_title( $page_data['title'] ) );

            if ( ! $existing ) {
                $page_id = wp_insert_post( array(
                    'post_title'     => $page_data['title'],
                    'post_content'   => $page_data['content'],
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'post_name'      => sanitize_title( $page_data['title'] ),
                ) );

                if ( ! is_wp_error( $page_id ) && ! empty( $page_data['template'] ) ) {
                    update_post_meta( $page_id, '_wp_page_template', $page_data['template'] );
                }

                $imported++;
            }
        }

        // Set homepage
        $homepage = get_page_by_path( 'home' );
        if ( $homepage ) {
            update_option( 'show_on_front', 'page' );
            update_option( 'page_on_front', $homepage->ID );
        }

        $this->log( 'success', "Imported {$imported} pages", 'pages' );

        return true;
    }

    /**
     * Import taxonomies (sports, leagues, teams)
     * 
     * @since 1.0.0
     * 
     * @return bool Success status
     */
    public function import_taxonomies() {
        $this->log( 'info', 'Importing taxonomies', 'taxonomies' );

        // Import sports
        $this->import_sports();

        // Import leagues
        $this->import_leagues();

        // Import teams
        $this->import_teams();

        $this->log( 'success', 'Taxonomies imported', 'taxonomies' );

        return true;
    }

    /**
     * Import sports
     * 
     * @since 1.0.0
     * 
     * @return int Number of imported sports
     */
    private function import_sports() {
        $this->log( 'info', 'Importing sports', 'taxonomies' );

        $sports = OC_Demo_Content::get_sports();
        $imported = 0;

        foreach ( $sports as $key => $sport ) {
            $exists = term_exists( $sport['slug'], 'sport' );

            if ( ! $exists ) {
                $result = wp_insert_term(
                    $sport['name'],
                    'sport',
                    array(
                        'slug'        => $sport['slug'],
                        'description' => $sport['description'],
                    )
                );

                if ( ! is_wp_error( $result ) ) {
                    $imported++;
                }
            }
        }

        $this->log( 'success', "Imported {$imported} sports", 'taxonomies' );

        return $imported;
    }

    /**
     * Import leagues
     * 
     * @since 1.0.0
     * 
     * @return int Number of imported leagues
     */
    private function import_leagues() {
        $this->log( 'info', 'Importing leagues', 'taxonomies' );

        $leagues = OC_Demo_Content::get_leagues();
        $imported = 0;

        foreach ( $leagues as $key => $league ) {
            $exists = term_exists( $league['slug'], 'league' );

            if ( ! $exists ) {
                $result = wp_insert_term(
                    $league['name'],
                    'league',
                    array(
                        'slug'        => $league['slug'],
                        'description' => $league['description'],
                    )
                );

                if ( ! is_wp_error( $result ) ) {
                    // Set sport taxonomy relationship
                    if ( ! empty( $league['sport'] ) ) {
                        $sport_term = get_term_by( 'slug', $league['sport'], 'sport' );
                        if ( $sport_term ) {
                            wp_set_object_terms( $result['term_id'], $sport_term->term_id, 'sport' );
                        }
                    }
                    $imported++;
                }
            }
        }

        $this->log( 'success', "Imported {$imported} leagues", 'taxonomies' );

        return $imported;
    }

    /**
     * Import teams
     * 
     * @since 1.0.0
     * 
     * @return int Number of imported teams
     */
    private function import_teams() {
        $this->log( 'info', 'Importing teams', 'taxonomies' );

        $teams = OC_Demo_Content::get_teams();
        $imported = 0;

        foreach ( $teams as $key => $team ) {
            $exists = term_exists( $team['slug'], 'team' );

            if ( ! $exists ) {
                $result = wp_insert_term(
                    $team['name'],
                    'team',
                    array(
                        'slug' => $team['slug'],
                    )
                );

                if ( ! is_wp_error( $result ) ) {
                    // Add team meta
                    update_term_meta( $result['term_id'], 'oc_team_short_name', $team['short_name'] );
                    update_term_meta( $result['term_id'], 'oc_team_country', $team['country'] );
                    if ( ! empty( $team['stadium'] ) ) {
                        update_term_meta( $result['term_id'], 'oc_team_stadium', $team['stadium'] );
                    }
                    $imported++;
                }
            }
        }

        $this->log( 'success', "Imported {$imported} teams", 'taxonomies' );

        return $imported;
    }

    /**
     * Import operators
     * 
     * @since 1.0.0
     * 
     * @return bool Success status
     */
    public function import_operators() {
        $this->log( 'info', 'Importing operators', 'operators' );

        $operators = OC_Demo_Content::get_operators();
        $imported = 0;

        foreach ( $operators as $key => $operator ) {
            $existing = get_page_by_path( $key, OBJECT, 'operator' );

            if ( ! $existing ) {
                $post_id = wp_insert_post( array(
                    'post_title'   => $operator['title'],
                    'post_content' => $operator['content'],
                    'post_status'  => 'publish',
                    'post_type'    => 'operator',
                    'post_name'    => $key,
                ) );

                if ( ! is_wp_error( $post_id ) ) {
                    // Add operator meta
                    update_post_meta( $post_id, 'oc_operator_license', $operator['license'] );
                    update_post_meta( $post_id, 'oc_operator_rating', $operator['rating'] );
                    update_post_meta( $post_id, 'oc_operator_affiliate_url', $operator['affiliate_url'] );
                    update_post_meta( $post_id, 'oc_operator_bonus_text', $operator['bonus_text'] );
                    update_post_meta( $post_id, 'oc_operator_bonus_value', $operator['bonus_value'] );
                    $imported++;
                }
            }
        }

        $this->log( 'success', "Imported {$imported} operators", 'operators' );

        return true;
    }

    /**
     * Import matches
     * 
     * @since 1.0.0
     * 
     * @return bool Success status
     */
    public function import_matches() {
        $this->log( 'info', 'Importing matches', 'matches' );

        $matches = OC_Demo_Content::get_matches();
        $imported = 0;

        foreach ( $matches as $key => $match ) {
            $slug = sanitize_title( $match['title'] );
            $existing = get_page_by_path( $slug, OBJECT, 'match' );

            if ( ! $existing ) {
                $post_id = wp_insert_post( array(
                    'post_title'   => $match['title'],
                    'post_content' => '',
                    'post_status'  => 'publish',
                    'post_type'    => 'match',
                    'post_name'    => $slug,
                ) );

                if ( ! is_wp_error( $post_id ) ) {
                    // Add match meta
                    update_post_meta( $post_id, 'oc_match_date', $match['date'] );
                    update_post_meta( $post_id, 'oc_match_time', $match['time'] );
                    update_post_meta( $post_id, 'oc_match_status', $match['status'] );
                    if ( ! empty( $match['stadium'] ) ) {
                        update_post_meta( $post_id, 'oc_match_stadium', $match['stadium'] );
                    }

                    // Set taxonomies
                    if ( ! empty( $match['sport'] ) ) {
                        $sport = get_term_by( 'slug', $match['sport'], 'sport' );
                        if ( $sport ) {
                            wp_set_object_terms( $post_id, $sport->term_id, 'sport' );
                        }
                    }

                    if ( ! empty( $match['league'] ) ) {
                        $league = get_term_by( 'slug', $match['league'], 'league' );
                        if ( $league ) {
                            wp_set_object_terms( $post_id, $league->term_id, 'league' );
                        }
                    }

                    if ( ! empty( $match['home_team'] ) ) {
                        $home_team = get_term_by( 'slug', $match['home_team'], 'team' );
                        if ( $home_team ) {
                            wp_set_object_terms( $post_id, $home_team->term_id, 'team' );
                        }
                    }

                    if ( ! empty( $match['away_team'] ) ) {
                        $away_team = get_term_by( 'slug', $match['away_team'], 'team' );
                        if ( $away_team ) {
                            wp_set_object_terms( $post_id, $away_team->term_id, 'team' );
                        }
                    }

                    $imported++;
                }
            }
        }

        $this->log( 'success', "Imported {$imported} matches", 'matches' );

        return true;
    }

    /**
     * Import odds
     * 
     * @since 1.0.0
     * 
     * @return bool Success status
     */
    public function import_odds() {
        $this->log( 'info', 'Importing odds', 'odds' );

        $matches = OC_Demo_Content::get_matches();
        $odds_ranges = OC_Demo_Content::get_odds_ranges();
        $operators = OC_Demo_Content::get_operators();
        $imported = 0;

        foreach ( $matches as $match ) {
            $match_slug = sanitize_title( $match['title'] );
            $match_post = get_page_by_path( $match_slug, OBJECT, 'match' );

            if ( ! $match_post ) {
                continue;
            }

            foreach ( $operators as $op_key => $operator ) {
                $operator_post = get_page_by_path( $op_key, OBJECT, 'operator' );

                if ( ! $operator_post ) {
                    continue;
                }

                $range = isset( $odds_ranges[ $op_key ] ) ? $odds_ranges[ $op_key ] : array( 'min' => 1.5, 'max' => 2.5 );

                // Generate random odds
                $home_odds = round( $range['min'] + ( mt_rand() / mt_getrandmax() ) * ( $range['max'] - $range['min'] ), 2 );
                $draw_odds = round( $range['min'] + ( mt_rand() / mt_getrandmax() ) * ( $range['max'] - $range['min'] ), 2 );
                $away_odds = round( $range['min'] + ( mt_rand() / mt_getrandmax() ) * ( $range['max'] - $range['min'] ), 2 );

                // Save odds data
                $odds_key = "oc_match_odds_{$op_key}";
                $odds_data = array(
                    'operator_id' => $operator_post->ID,
                    'home_odds'   => $home_odds,
                    'draw_odds'   => $draw_odds,
                    'away_odds'   => $away_odds,
                    'updated'     => current_time( 'mysql' ),
                );

                update_post_meta( $match_post->ID, $odds_key, $odds_data );
                $imported++;
            }
        }

        $this->log( 'success', "Imported odds for {$imported} match-operator combinations", 'odds' );

        return true;
    }

    /**
     * Delete demo posts
     * 
     * @since 1.0.0
     */
    private function delete_demo_posts() {
        $this->log( 'info', 'Deleting demo posts', 'reset' );

        // Delete matches
        $matches = get_posts( array(
            'post_type'   => 'match',
            'post_status' => 'any',
            'numberposts' => -1,
        ) );

        foreach ( $matches as $match ) {
            wp_delete_post( $match->ID, true );
        }

        // Delete operators
        $operators = get_posts( array(
            'post_type'   => 'operator',
            'post_status' => 'any',
            'numberposts' => -1,
        ) );

        foreach ( $operators as $operator ) {
            wp_delete_post( $operator->ID, true );
        }

        // Delete demo pages
        $pages = OC_Demo_Content::get_pages();
        foreach ( $pages as $page ) {
            $page_obj = get_page_by_path( sanitize_title( $page['title'] ) );
            if ( $page_obj ) {
                wp_delete_post( $page_obj->ID, true );
            }
        }

        $this->log( 'success', 'Demo posts deleted', 'reset' );
    }

    /**
     * Delete demo terms
     * 
     * @since 1.0.0
     */
    private function delete_demo_terms() {
        $this->log( 'info', 'Deleting demo terms', 'reset' );

        // Delete sports
        $sports = OC_Demo_Content::get_sports();
        foreach ( $sports as $sport ) {
            $term = get_term_by( 'slug', $sport['slug'], 'sport' );
            if ( $term ) {
                wp_delete_term( $term->term_id, 'sport' );
            }
        }

        // Delete leagues
        $leagues = OC_Demo_Content::get_leagues();
        foreach ( $leagues as $league ) {
            $term = get_term_by( 'slug', $league['slug'], 'league' );
            if ( $term ) {
                wp_delete_term( $term->term_id, 'league' );
            }
        }

        // Delete teams
        $teams = OC_Demo_Content::get_teams();
        foreach ( $teams as $team ) {
            $term = get_term_by( 'slug', $team['slug'], 'team' );
            if ( $term ) {
                wp_delete_term( $term->term_id, 'team' );
            }
        }

        $this->log( 'success', 'Demo terms deleted', 'reset' );
    }

    /**
     * Reset theme options
     * 
     * @since 1.0.0
     */
    private function reset_theme_options() {
        $this->log( 'info', 'Resetting theme options', 'reset' );

        $options = OC_Demo_Content::get_theme_options();

        foreach ( $options as $key => $value ) {
            delete_option( "oc_{$key}" );
        }

        $this->log( 'success', 'Theme options reset', 'reset' );
    }

    /**
     * Reset widgets
     * 
     * @since 1.0.0
     */
    private function reset_widgets() {
        $this->log( 'info', 'Resetting widgets', 'reset' );

        update_option( 'sidebars_widgets', array( 'wp_inactive_widgets' => array() ) );

        $this->log( 'success', 'Widgets reset', 'reset' );
    }

    /**
     * Reset menus
     * 
     * @since 1.0.0
     */
    private function reset_menus() {
        $this->log( 'info', 'Resetting menus', 'reset' );

        $menus = OC_Demo_Content::get_menus();

        foreach ( $menus as $location => $items ) {
            $menu = wp_get_nav_menu_object( $location );
            if ( $menu ) {
                wp_delete_nav_menu( $menu->term_id );
            }
        }

        set_theme_mod( 'nav_menu_locations', array() );

        $this->log( 'success', 'Menus reset', 'reset' );
    }

    /**
     * Reset customizer settings
     * 
     * @since 1.0.0
     */
    private function reset_customizer() {
        $this->log( 'info', 'Resetting customizer settings', 'reset' );

        $settings = OC_Demo_Content::get_customizer_settings();

        foreach ( $settings as $section => $values ) {
            if ( is_array( $values ) ) {
                foreach ( $values as $key => $value ) {
                    remove_theme_mod( "oc_{$section}_{$key}" );
                }
            }
        }

        $this->log( 'success', 'Customizer settings reset', 'reset' );
    }

    /**
     * Get import status summary
     * 
     * @since 1.0.0
     * 
     * @return array
     */
    public function get_status_summary() {
        return array(
            'status'      => $this->get_status(),
            'progress'    => $this->get_progress(),
            'imported'    => get_option( 'oc_demo_imported', false ),
            'import_date' => get_option( 'oc_demo_import_date', '' ),
            'error_count' => count( $this->get_errors() ),
            'log_count'   => count( $this->get_logs() ),
        );
    }
}

