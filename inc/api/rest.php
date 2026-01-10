<?php
/**
 * REST API Handlers
 *
 * Registers custom REST API endpoints for odds data.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register REST API routes
 *
 * @since 1.0.0
 */
function oc_register_rest_routes() {
    // Matches endpoints
    register_rest_route( 'oc/v1', '/matches', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'oc_rest_get_matches',
        'args'     => array(
            'sport'    => array( 'sanitize_callback' => 'sanitize_text_field' ),
            'league'   => array( 'sanitize_callback' => 'sanitize_text_field' ),
            'status'   => array( 'sanitize_callback' => 'sanitize_text_field' ),
            'per_page' => array( 'sanitize_callback' => 'absint', 'default' => 10 ),
            'page'     => array( 'sanitize_callback' => 'absint', 'default' => 1 ),
            'sort'     => array( 'sanitize_callback' => 'sanitize_text_field', 'default' => 'date' ),
        ),
        'permission_callback' => '__return_true',
    ) );
    
    register_rest_route( 'oc/v1', '/matches/(?P<id>\d+)', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'oc_rest_get_match',
        'args'     => array( 'id' => array( 'sanitize_callback' => 'absint' ) ),
        'permission_callback' => '__return_true',
    ) );
    
    register_rest_route( 'oc/v1', '/matches/(?P<id>\d+)/odds', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'oc_rest_get_match_odds',
        'args'     => array(
            'id'    => array( 'sanitize_callback' => 'absint' ),
            'sort'  => array( 'sanitize_callback' => 'sanitize_text_field', 'default' => 'rating' ),
            'market'=> array( 'sanitize_callback' => 'sanitize_text_field' ),
        ),
        'permission_callback' => '__return_true',
    ) );
    
    // Operators endpoints
    register_rest_route( 'oc/v1', '/operators', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'oc_rest_get_operators',
        'args'     => array(
            'sport'      => array( 'sanitize_callback' => 'sanitize_text_field' ),
            'license'    => array( 'sanitize_callback' => 'sanitize_text_field' ),
            'min_rating' => array( 'sanitize_callback' => 'floatval', 'default' => 0 ),
            'bonus_type' => array( 'sanitize_callback' => 'sanitize_text_field' ),
            'featured'   => array( 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false ),
            'per_page'   => array( 'sanitize_callback' => 'absint', 'default' => 10 ),
            'page'       => array( 'sanitize_callback' => 'absint', 'default' => 1 ),
        ),
        'permission_callback' => '__return_true',
    ) );
    
    register_rest_route( 'oc/v1', '/operators/(?P<id>\d+)', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'oc_rest_get_operator',
        'args'     => array( 'id' => array( 'sanitize_callback' => 'absint' ) ),
        'permission_callback' => '__return_true',
    ) );
    
    // Odds comparison endpoint
    register_rest_route( 'oc/v1', '/compare', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'oc_rest_compare_odds',
        'args'     => array(
            'match_id'  => array( 'sanitize_callback' => 'absint', 'required' => true ),
            'operators' => array( 'sanitize_callback' => function( $param ) {
                return array_map( 'absint', explode( ',', $param ) );
            }),
            'market'    => array( 'sanitize_callback' => 'sanitize_text_field', 'default' => 'all' ),
        ),
        'permission_callback' => '__return_true',
    ) );
    
    // Sports and leagues endpoints
    register_rest_route( 'oc/v1', '/sports', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'oc_rest_get_sports',
        'permission_callback' => '__return_true',
    ) );
    
    register_rest_route( 'oc/v1', '/leagues', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'oc_rest_get_leagues',
        'args'     => array( 'sport' => array( 'sanitize_callback' => 'sanitize_text_field' ) ),
        'permission_callback' => '__return_true',
    ) );
    
    // Search endpoint
    register_rest_route( 'oc/v1', '/search', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'oc_rest_search',
        'args'     => array(
            'q'        => array( 'sanitize_callback' => 'sanitize_text_field', 'required' => true ),
            'type'     => array( 'sanitize_callback' => 'sanitize_text_field', 'default' => 'all' ),
            'per_page' => array( 'sanitize_callback' => 'absint', 'default' => 5 ),
        ),
        'permission_callback' => '__return_true',
    ) );
}
add_action( 'rest_api_init', 'oc_register_rest_routes' );

/**
 * Get matches via REST API
 *
 * @since 1.0.0
 */
function oc_rest_get_matches( $request ) {
    $sport = $request->get_param( 'sport' );
    $league = $request->get_param( 'league' );
    $per_page = $request->get_param( 'per_page' );
    $page = $request->get_param( 'page' );
    $sort = $request->get_param( 'sort' );
    
    $args = array(
        'post_type'      => 'match',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
    );
    
    if ( $sport ) {
        $args['tax_query'][] = array( 'taxonomy' => 'sport', 'field' => 'slug', 'terms' => $sport );
    }
    if ( $league ) {
        $args['tax_query'][] = array( 'taxonomy' => 'league', 'field' => 'slug', 'terms' => $league );
    }
    
    if ( 'live' === $sort ) {
        $args['meta_key'] = 'oc_live_match';
        $args['orderby'] = 'meta_value';
        $args['order'] = 'DESC';
    } elseif ( 'featured' === $sort ) {
        $args['meta_key'] = 'oc_featured_match';
        $args['orderby'] = 'meta_value';
        $args['order'] = 'DESC';
    } else {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    }
    
    $query = new WP_Query( $args );
    $matches = array();
    
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $match_id = get_the_ID();
            $matches[] = array(
                'id'         => $match_id,
                'title'      => get_the_title(),
                'slug'       => get_post_field( 'post_name' ),
                'url'        => get_permalink(),
                'home_team'  => get_post_meta( $match_id, 'oc_home_team', true ),
                'away_team'  => get_post_meta( $match_id, 'oc_away_team', true ),
                'match_date' => get_post_meta( $match_id, 'oc_match_date', true ),
                'match_time' => get_post_meta( $match_id, 'oc_match_time', true ),
                'is_live'    => (bool) get_post_meta( $match_id, 'oc_live_match', true ),
                'is_featured'=> (bool) get_post_meta( $match_id, 'oc_featured_match', true ),
            );
        }
        wp_reset_postdata();
    }
    
    return new WP_REST_Response( array( 'data' => $matches, 'total' => $query->found_posts ), 200 );
}

/**
 * Get single match via REST API
 *
 * @since 1.0.0
 */
function oc_rest_get_match( $request ) {
    $id = $request->get_param( 'id' );
    $post = get_post( $id );
    
    if ( ! $post || 'match' !== $post->post_type ) {
        return new WP_REST_Response( array( 'error' => 'not_found', 'message' => __( 'Match not found.', 'odds-comparison' ) ), 404 );
    }
    
    $odds = oc_get_match_odds( $id );
    
    $match = array(
        'id'         => $id,
        'title'      => get_the_title( $id ),
        'slug'       => $post->post_name,
        'url'        => get_permalink( $id ),
        'home_team'  => get_post_meta( $id, 'oc_home_team', true ),
        'away_team'  => get_post_meta( $id, 'oc_away_team', true ),
        'match_date' => get_post_meta( $id, 'oc_match_date', true ),
        'match_time' => get_post_meta( $id, 'oc_match_time', true ),
        'is_live'    => (bool) get_post_meta( $id, 'oc_live_match', true ),
        'best_odds'  => oc_get_best_odds( $odds ),
        'odds_count' => count( $odds ),
    );
    
    return new WP_REST_Response( $match, 200 );
}

/**
 * Get match odds via REST API
 *
 * @since 1.0.0
 */
function oc_rest_get_match_odds( $request ) {
    $id = $request->get_param( 'id' );
    $sort = $request->get_param( 'sort' );
    
    $odds = oc_get_match_odds( $id );
    
    usort( $odds, function( $a, $b ) use ( $sort ) {
        switch ( $sort ) {
            case 'rating':
                return floatval( get_post_meta( $b['bookmaker_id'], 'oc_operator_rating', true ) ) - floatval( get_post_meta( $a['bookmaker_id'], 'oc_operator_rating', true ) );
            case 'odds_high':
                return max( floatval( $b['odds_home'] ), floatval( $b['odds_draw'] ), floatval( $b['odds_away'] ) ) - max( floatval( $a['odds_home'] ), floatval( $a['odds_draw'] ), floatval( $a['odds_away'] ) );
            case 'odds_low':
                return min( floatval( $a['odds_home'] ), floatval( $a['odds_draw'] ), floatval( $a['odds_away'] ) ) - min( floatval( $b['odds_home'] ), floatval( $b['odds_draw'] ), floatval( $b['odds_away'] ) );
            default:
                return 0;
        }
    } );
    
    return new WP_REST_Response( array( 'match_id' => $id, 'odds' => $odds, 'total' => count( $odds ) ), 200 );
}

/**
 * Get operators via REST API
 *
 * @since 1.0.0
 */
function oc_rest_get_operators( $request ) {
    $per_page = $request->get_param( 'per_page' );
    $page = $request->get_param( 'page' );
    
    $args = array(
        'post_type'      => 'operator',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
    );
    
    $query = new WP_Query( $args );
    $operators = array();
    
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $op_id = get_the_ID();
            $operators[] = array(
                'id'     => $op_id,
                'name'   => get_the_title(),
                'slug'   => get_post_field( 'post_name' ),
                'url'    => get_permalink(),
                'rating' => get_post_meta( $op_id, 'oc_operator_rating', true ),
                'bonus'  => get_post_meta( $op_id, 'oc_bonus_amount', true ),
            );
        }
        wp_reset_postdata();
    }
    
    return new WP_REST_Response( array( 'data' => $operators, 'total' => $query->found_posts ), 200 );
}

/**
 * Get single operator via REST API
 *
 * @since 1.0.0
 */
function oc_rest_get_operator( $request ) {
    $id = $request->get_param( 'id' );
    $post = get_post( $id );
    
    if ( ! $post || 'operator' !== $post->post_type ) {
        return new WP_REST_Response( array( 'error' => 'not_found', 'message' => __( 'Operator not found.', 'odds-comparison' ) ), 404 );
    }
    
    $operator = array(
        'id'      => $id,
        'name'    => get_the_title( $id ),
        'slug'    => $post->post_name,
        'url'     => get_permalink( $id ),
        'rating'  => get_post_meta( $id, 'oc_operator_rating', true ),
        'bonus'   => get_post_meta( $id, 'oc_bonus_amount', true ),
        'pros'    => get_post_meta( $id, 'oc_operator_pros', true ),
        'cons'    => get_post_meta( $id, 'oc_operator_cons', true ),
    );
    
    return new WP_REST_Response( $operator, 200 );
}

/**
 * Compare odds via REST API
 *
 * @since 1.0.0
 */
function oc_rest_compare_odds( $request ) {
    $match_id = $request->get_param( 'match_id' );
    $operators = $request->get_param( 'operators' );
    $market = $request->get_param( 'market' );
    
    $odds = oc_get_match_odds( $match_id );
    
    if ( ! empty( $operators ) ) {
        $odds = array_filter( $odds, function( $odd ) use ( $operators ) {
            return in_array( $odd['bookmaker_id'], $operators );
        } );
    }
    
    return new WP_REST_Response( array(
        'match_id' => $match_id,
        'market'   => $market,
        'best'     => oc_get_best_odds( $odds, $market ),
        'operators'=> $odds,
    ), 200 );
}

/**
 * Get sports via REST API
 *
 * @since 1.0.0
 */
function oc_rest_get_sports( $request ) {
    $sports = get_terms( array( 'taxonomy' => 'sport', 'hide_empty' => true, 'orderby' => 'name' ) );
    $data = array();
    foreach ( $sports as $sport ) {
        $data[] = array( 'id' => $sport->term_id, 'name' => $sport->name, 'slug' => $sport->slug );
    }
    return new WP_REST_Response( $data, 200 );
}

/**
 * Get leagues via REST API
 *
 * @since 1.0.0
 */
function oc_rest_get_leagues( $request ) {
    $sport = $request->get_param( 'sport' );
    $args = array( 'taxonomy' => 'league', 'hide_empty' => true, 'orderby' => 'name' );
    if ( $sport ) {
        $args['meta_query'] = array( array( 'key' => 'oc_sport', 'value' => $sport ) );
    }
    $leagues = get_terms( $args );
    $data = array();
    foreach ( $leagues as $league ) {
        $data[] = array( 'id' => $league->term_id, 'name' => $league->name, 'slug' => $league->slug );
    }
    return new WP_REST_Response( $data, 200 );
}

/**
 * Search via REST API
 *
 * @since 1.0.0
 */
function oc_rest_search( $request ) {
    $query = $request->get_param( 'q' );
    $type = $request->get_param( 'type' );
    $per_page = $request->get_param( 'per_page' );
    
    $results = array();
    
    if ( 'all' === $type || 'matches' === $type ) {
        $matches = get_posts( array( 'post_type' => 'match', 'post_status' => 'publish', 'posts_per_page' => $per_page, 's' => $query ) );
        foreach ( $matches as $match ) {
            $results['matches'][] = array( 'id' => $match->ID, 'title' => $match->post_title, 'url' => get_permalink( $match->ID ), 'type' => 'match' );
        }
    }
    
    if ( 'all' === $type || 'operators' === $type ) {
        $operators = get_posts( array( 'post_type' => 'operator', 'post_status' => 'publish', 'posts_per_page' => $per_page, 's' => $query ) );
        foreach ( $operators as $op ) {
            $results['operators'][] = array( 'id' => $op->ID, 'title' => $op->post_title, 'url' => get_permalink( $op->ID ), 'type' => 'operator' );
        }
    }
    
    return new WP_REST_Response( array( 'query' => $query, 'results' => $results ), 200 );
}
