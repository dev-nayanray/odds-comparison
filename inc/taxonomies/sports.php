<?php
/**
 * Sports Taxonomy
 *
 * Register sport taxonomy for matches.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register sport taxonomy
 *
 * @since 1.0.0
 */
function oc_register_sport_taxonomy() {
    $labels = array(
        'name'                       => _x( 'Sports', 'Taxonomy General Name', 'odds-comparison' ),
        'singular_name'              => _x( 'Sport', 'Taxonomy Singular Name', 'odds-comparison' ),
        'menu_name'                  => __( 'Sports', 'odds-comparison' ),
        'all_items'                  => __( 'All Sports', 'odds-comparison' ),
        'new_item_name'              => __( 'New Sport Name', 'odds-comparison' ),
        'add_new_item'               => __( 'Add New Sport', 'odds-comparison' ),
        'edit_item'                  => __( 'Edit Sport', 'odds-comparison' ),
        'update_item'                => __( 'Update Sport', 'odds-comparison' ),
        'view_item'                  => __( 'View Sport', 'odds-comparison' ),
        'popular_items'              => __( 'Popular Sports', 'odds-comparison' ),
        'search_items'               => __( 'Search Sports', 'odds-comparison' ),
        'not_found'                  => __( 'Sport Not Found', 'odds-comparison' ),
        'no_terms'                   => __( 'No Sports', 'odds-comparison' ),
        'items_list'                 => __( 'Sports List', 'odds-comparison' ),
        'items_list_navigation'      => __( 'Sports List Navigation', 'odds-comparison' ),
    );
    
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => false,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'rewrite'                    => array(
            'slug'         => 'sport',
            'with_front'   => true,
            'hierarchical' => false,
        ),
        'show_in_rest'               => true,
        'rest_base'                  => 'sports',
        'rest_controller_class'      => 'WP_REST_Terms_Controller',
    );
    
    register_taxonomy( 'sport', array( 'match' ), $args );
}
add_action( 'init', 'oc_register_sport_taxonomy' );

/**
 * Add default sports on plugin activation
 *
 * @since 1.0.0
 */
function oc_add_default_sports() {
    $default_sports = array(
        'football'      => __( 'Football', 'odds-comparison' ),
        'basketball'    => __( 'Basketball', 'odds-comparison' ),
        'tennis'        => __( 'Tennis', 'odds-comparison' ),
        'baseball'      => __( 'Baseball', 'odds-comparison' ),
        'hockey'        => __( 'Hockey', 'odds-comparison' ),
        'american-football' => __( 'American Football', 'odds-comparison' ),
        'rugby'         => __( 'Rugby', 'odds-comparison' ),
        'cricket'       => __( 'Cricket', 'odds-comparison' ),
        'golf'          => __( 'Golf', 'odds-comparison' ),
        'esports'       => __( 'eSports', 'odds-comparison' ),
    );
    
    foreach ( $default_sports as $slug => $name ) {
        if ( ! term_exists( $slug, 'sport' ) ) {
            wp_insert_term( $name, 'sport', array( 'slug' => $slug ) );
        }
    }
}

/**
 * Add sport icon to REST API response
 *
 * @since 1.0.0
 *
 * @param array   $response   The response data
 * @param WP_Term $term       The term object
 * @param WP_REST_Request $request The request
 * @return array
 */
function oc_rest_sport_response( $response, $term, $request ) {
    // Add icon URL if exists
    $icon_id = get_term_meta( $term->term_id, 'oc_sport_icon', true );
    if ( $icon_id ) {
        $response->data['icon_url'] = wp_get_attachment_url( $icon_id );
    }
    
    // Add color if exists
    $color = get_term_meta( $term->term_id, 'oc_sport_color', true );
    if ( $color ) {
        $response->data['color'] = $color;
    }
    
    return $response;
}
add_filter( 'rest_prepare_sport', 'oc_rest_sport_response', 10, 3 );

/**
 * Add sport color field to edit form
 *
 * @since 1.0.0
 *
 * @param WP_Term $term Term being edited
 */
function oc_sport_edit_form_fields( $term ) {
    $icon_id = get_term_meta( $term->term_id, 'oc_sport_icon', true );
    $color = get_term_meta( $term->term_id, 'oc_sport_color', true );
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="oc_sport_color"><?php esc_html_e( 'Color', 'odds-comparison' ); ?></label>
        </th>
        <td>
            <input type="color" name="oc_sport_color" id="oc_sport_color" value="<?php echo esc_attr( $color ); ?>">
            <p class="description"><?php esc_html_e( 'Color for this sport (used in displays).', 'odds-comparison' ); ?></p>
        </td>
    </tr>
    <?php
}
add_action( 'sport_edit_form_fields', 'oc_sport_edit_form_fields' );

/**
 * Save sport meta
 *
 * @since 1.0.0
 *
 * @param int $term_id Term ID
 */
function oc_save_sport_meta( $term_id ) {
    if ( isset( $_POST['oc_sport_color'] ) ) {
        update_term_meta( $term_id, 'oc_sport_color', sanitize_hex_color( $_POST['oc_sport_color'] ) );
    }
}
add_action( 'edited_sport', 'oc_save_sport_meta' );

/**
 * Get sport icon
 *
 * @since 1.0.0
 *
 * @param string|int $sport Sport term ID or slug
 * @return string
 */
function oc_get_sport_icon( $sport ) {
    if ( is_numeric( $sport ) ) {
        $term = get_term( $sport, 'sport' );
    } else {
        $term = get_term_by( 'slug', $sport, 'sport' );
    }
    
    if ( ! $term ) {
        return '';
    }
    
    $icon_id = get_term_meta( $term->term_id, 'oc_sport_icon', true );
    
    if ( $icon_id && wp_get_attachment_url( $icon_id ) ) {
        return '<img src="' . esc_url( wp_get_attachment_url( $icon_id ) ) . '" alt="' . esc_attr( $term->name ) . '" class="oc-sport-icon">';
    }
    
    // Return default icon based on slug
    $default_icons = array(
        'football'      => '⚽',
        'basketball'    => '🏀',
        'tennis'        => '🎾',
        'baseball'      => '⚾',
        'hockey'        => '🏒',
        'american-football' => '🏈',
        'rugby'         => '🏉',
        'cricket'       => '🏏',
        'golf'          => '⛳',
        'esports'       => '🎮',
    );
    
    $slug = $term->slug;
    
    if ( isset( $default_icons[ $slug ] ) ) {
        return '<span class="oc-sport-icon-default">' . $default_icons[ $slug ] . '</span>';
    }
    
    return '';
}

/**
 * Get sport color
 *
 * @since 1.0.0
 *
 * @param string|int $sport Sport term ID or slug
 * @return string
 */
function oc_get_sport_color( $sport ) {
    if ( is_numeric( $sport ) ) {
        $term = get_term( $sport, 'sport' );
    } else {
        $term = get_term_by( 'slug', $sport, 'sport' );
    }
    
    if ( ! $term ) {
        return '#2563eb';
    }
    
    $color = get_term_meta( $term->term_id, 'oc_sport_color', true );
    
    return $color ?: '#2563eb';
}

/**
 * Get matches by sport
 *
 * @since 1.0.0
 *
 * @param string $sport_slug Sport slug
 * @param array  $args      Additional arguments
 * @return array
 */
function oc_get_matches_by_sport_slug( $sport_slug, $args = array() ) {
    $defaults = array(
        'post_type'      => 'match',
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'tax_query'      => array(
            array(
                'taxonomy' => 'sport',
                'field'    => 'slug',
                'terms'    => $sport_slug,
            ),
        ),
    );
    
    $args = wp_parse_args( $args, $defaults );
    
    $query = new WP_Query( $args );
    
    return $query->posts ?: array();
}

