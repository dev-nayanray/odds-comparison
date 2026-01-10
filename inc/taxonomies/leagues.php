<?php
/**
 * Leagues Taxonomy
 * 
 * Registers the 'league' taxonomy for categorizing matches by league/tournament.
 * Also includes teams taxonomy for team management.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register Leagues Taxonomy
 * 
 * @since 1.0.0
 */
function oc_register_league_taxonomy() {
    $labels = array(
        'name'              => _x( 'Leagues', 'taxonomy general name', 'odds-comparison' ),
        'singular_name'     => _x( 'League', 'taxonomy singular name', 'odds-comparison' ),
        'search_items'      => __( 'Search Leagues', 'odds-comparison' ),
        'all_items'         => __( 'All Leagues', 'odds-comparison' ),
        'parent_item'       => __( 'Parent League', 'odds-comparison' ),
        'parent_item_colon' => __( 'Parent League:', 'odds-comparison' ),
        'edit_item'         => __( 'Edit League', 'odds-comparison' ),
        'update_item'       => __( 'Update League', 'odds-comparison' ),
        'add_new_item'      => __( 'Add New League', 'odds-comparison' ),
        'new_item_name'     => __( 'New League Name', 'odds-comparison' ),
        'menu_name'         => __( 'Leagues', 'odds-comparison' ),
    );
    
    $args = array(
        'labels'            => $labels,
        'public'            => true,
        'publicly_queryable' => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => false,
        'rewrite'           => array(
            'slug'       => 'league',
            'with_front' => false,
        ),
        'show_in_rest'      => true,
        'rest_base'         => 'leagues',
        'rest_controller_class' => 'WP_REST_Terms_Controller',
    );
    
    register_taxonomy( 'league', array( 'match' ), $args );
}
add_action( 'init', 'oc_register_league_taxonomy', 0 );

/**
 * Register Teams Taxonomy
 * 
 * @since 1.0.0
 */
function oc_register_team_taxonomy() {
    $labels = array(
        'name'              => _x( 'Teams', 'taxonomy general name', 'odds-comparison' ),
        'singular_name'     => _x( 'Team', 'taxonomy singular name', 'odds-comparison' ),
        'search_items'      => __( 'Search Teams', 'odds-comparison' ),
        'all_items'         => __( 'All Teams', 'odds-comparison' ),
        'parent_item'       => __( 'Parent Team', 'odds-comparison' ),
        'parent_item_colon' => __( 'Parent Team:', 'odds-comparison' ),
        'edit_item'         => __( 'Edit Team', 'odds-comparison' ),
        'update_item'       => __( 'Update Team', 'odds-comparison' ),
        'add_new_item'      => __( 'Add New Team', 'odds-comparison' ),
        'new_item_name'     => __( 'New Team Name', 'odds-comparison' ),
        'menu_name'         => __( 'Teams', 'odds-comparison' ),
    );
    
    $args = array(
        'labels'            => $labels,
        'public'            => true,
        'publicly_queryable' => true,
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'rewrite'           => array(
            'slug'       => 'team',
            'with_front' => false,
        ),
        'show_in_rest'      => true,
        'rest_base'         => 'teams',
        'rest_controller_class' => 'WP_REST_Terms_Controller',
        'supports'          => array( 'thumbnail', 'description' ),
    );
    
    register_taxonomy( 'team', array( 'match', 'operator' ), $args );
}
add_action( 'init', 'oc_register_team_taxonomy', 0 );



/**
 * Get league filter URL
 * 
 * @since 1.0.0
 * 
 * @param string $league_slug League taxonomy slug
 * @return string Filter URL
 */
function oc_get_league_filter_url( $league_slug ) {
    return add_query_arg( 'league', $league_slug, home_url( '/matches/' ) );
}

/**
 * Get all leagues with match counts
 * 
 * @since 1.0.0
 * 
 * @param string $sport Optional sport filter
 * @return array Leagues with match counts
 */
function oc_get_leagues_with_matches( $sport = '' ) {
    $args = array(
        'taxonomy'   => 'league',
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
    );
    
    if ( $sport ) {
        $args['meta_query'] = array(
            array(
                'key'     => 'oc_league_sport',
                'value'   => $sport,
                'compare' => '=',
            ),
        );
    }
    
    $leagues = get_terms( $args );
    
    if ( is_wp_error( $leagues ) ) {
        return array();
    }
    
    $result = array();
    
    foreach ( $leagues as $league ) {
        $result[] = array(
            'slug'  => $league->slug,
            'name'  => $league->name,
            'count' => $league->count,
            'url'   => oc_get_league_filter_url( $league->slug ),
        );
    }
    
    return $result;
}

/**
 * Add league data to REST API response
 * 
 * @since 1.0.0
 * 
 * @param array        $response   Response data
 * @param WP_Term      $term       Term object
 * @param WP_REST_Request $request Request object
 * @return array Modified response
 */
function oc_league_rest_response( $response, $term, $request ) {
    $response->data['sport'] = get_term_meta( $term->term_id, 'oc_league_sport', true );
    $response->data['logo']  = get_term_meta( $term->term_id, 'oc_league_logo_id', true );
    
    return $response;
}
add_filter( 'rest_prepare_league', 'oc_league_rest_response', 10, 3 );

/**
 * Add team data to REST API response
 * 
 * @since 1.0.0
 * 
 * @param array        $response   Response data
 * @param WP_Term      $term       Term object
 * @param WP_REST_Request $request Request object
 * @return array Modified response
 */
function oc_team_rest_response( $response, $term, $request ) {
    $response->data['country'] = get_term_meta( $term->term_id, 'oc_team_country', true );
    $logo_id = get_term_meta( $term->term_id, 'oc_team_logo_id', true );
    $response->data['logo'] = $logo_id ? wp_get_attachment_image_url( $logo_id, 'team-logo' ) : '';
    
    return $response;
}
add_filter( 'rest_prepare_team', 'oc_team_rest_response', 10, 3 );

/**
 * Filter matches by league in admin
 * 
 * @since 1.0.0
 * 
 * @param WP_Query $query Main query
 */
function oc_filter_matches_by_league_admin( $query ) {
    global $pagenow, $post_type;
    
    if ( 'edit.php' !== $pagenow || 'match' !== $post_type ) {
        return;
    }
    
    if ( isset( $_GET['league'] ) && ! empty( $_GET['league'] ) ) {
        $query->set( 'tax_query', array(
            array(
                'taxonomy' => 'league',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $_GET['league'] ),
            ),
        ) );
    }
}
add_action( 'pre_get_posts', 'oc_filter_matches_by_league_admin' );

/**
 * Filter matches by team in admin
 * 
 * @since 1.0.0
 * 
 * @param WP_Query $query Main query
 */
function oc_filter_matches_by_team_admin( $query ) {
    global $pagenow, $post_type;
    
    if ( 'edit.php' !== $pagenow || 'match' !== $post_type ) {
        return;
    }
    
    if ( isset( $_GET['team'] ) && ! empty( $_GET['team'] ) ) {
        $query->set( 'tax_query', array(
            array(
                'taxonomy' => 'team',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $_GET['team'] ),
            ),
        ) );
    }
}
add_action( 'pre_get_posts', 'oc_filter_matches_by_team_admin' );

