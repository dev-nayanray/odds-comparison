<?php
/**
 * Licenses Taxonomy
 *
 * Register license taxonomy for operators.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register license taxonomy
 *
 * @since 1.0.0
 */
function oc_register_license_taxonomy() {
    $labels = array(
        'name'                       => _x( 'Licenses', 'Taxonomy General Name', 'odds-comparison' ),
        'singular_name'              => _x( 'License', 'Taxonomy Singular Name', 'odds-comparison' ),
        'menu_name'                  => __( 'Licenses', 'odds-comparison' ),
        'all_items'                  => __( 'All Licenses', 'odds-comparison' ),
        'new_item_name'              => __( 'New License Name', 'odds-comparison' ),
        'add_new_item'               => __( 'Add New License', 'odds-comparison' ),
        'edit_item'                  => __( 'Edit License', 'odds-comparison' ),
        'update_item'                => __( 'Update License', 'odds-comparison' ),
        'view_item'                  => __( 'View License', 'odds-comparison' ),
        'popular_items'              => __( 'Popular Licenses', 'odds-comparison' ),
        'search_items'               => __( 'Search Licenses', 'odds-comparison' ),
        'not_found'                  => __( 'License Not Found', 'odds-comparison' ),
        'no_terms'                   => __( 'No Licenses', 'odds-comparison' ),
        'items_list'                 => __( 'Licenses List', 'odds-comparison' ),
        'items_list_navigation'      => __( 'Licenses List Navigation', 'odds-comparison' ),
    );
    
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'rewrite'                    => array(
            'slug'         => 'license',
            'with_front'   => true,
            'hierarchical' => true,
        ),
        'show_in_rest'               => true,
        'rest_base'                  => 'licenses',
        'rest_controller_class'      => 'WP_REST_Terms_Controller',
    );
    
    register_taxonomy( 'license', array( 'operator' ), $args );
}
add_action( 'init', 'oc_register_license_taxonomy' );

/**
 * Add default licenses on plugin activation
 *
 * @since 1.0.0
 */
function oc_add_default_licenses() {
    $default_licenses = array(
        'uk-gambling-commission' => array(
            'name'        => __( 'UK Gambling Commission (UKGC)', 'odds-comparison' ),
            'description' => __( 'Licensed by the United Kingdom Gambling Commission', 'odds-comparison' ),
        ),
        'malta-gaming-authority' => array(
            'name'        => __( 'Malta Gaming Authority (MGA)', 'odds-comparison' ),
            'description' => __( 'Licensed by the Malta Gaming Authority', 'odds-comparison' ),
        ),
        'curacao' => array(
            'name'        => __( 'Curacao eGaming', 'odds-comparison' ),
            'description' => __( 'Licensed by Curacao eGaming', 'odds-comparison' ),
        ),
        'gibraltar' => array(
            'name'        => __( 'Gibraltar Regulatory Authority', 'odds-comparison' ),
            'description' => __( 'Licensed by Gibraltar Regulatory Authority', 'odds-comparison' ),
        ),
        'isle-of-man' => array(
            'name'        => __( 'Isle of Man Gambling Supervision Commission', 'odds-comparison' ),
            'description' => __( 'Licensed by Isle of Man Gambling Supervision Commission', 'odds-comparison' ),
        ),
        'alders' => array(
            'name'        => __( 'Alderney Gambling Control Commission', 'odds-comparison' ),
            'description' => __( 'Licensed by Alderney Gambling Control Commission', 'odds-comparison' ),
        ),
        'kahnawake' => array(
            'name'        => __( 'Kahnawake Gaming Commission', 'odds-comparison' ),
            'description' => __( 'Licensed by Kahnawake Gaming Commission', 'odds-comparison' ),
        ),
        'denmark' => array(
            'name'        => __( 'Denmark Gaming Authority (Spillemyndigheden)', 'odds-comparison' ),
            'description' => __( 'Licensed by Denmark Gaming Authority', 'odds-comparison' ),
        ),
        'sweden' => array(
            'name'        => __( 'Swedish Gambling Authority (Spelinspektionen)', 'odds-comparison' ),
            'description' => __( 'Licensed by Swedish Gambling Authority', 'odds-comparison' ),
        ),
        'germany' => array(
            'name'        => __( 'German Interstate Treaty on Gambling', 'odds-comparison' ),
            'description' => __( 'Licensed under German Interstate Treaty on Gambling', 'odds-comparison' ),
        ),
    );
    
    foreach ( $default_licenses as $slug => $data ) {
        if ( ! term_exists( $slug, 'license' ) ) {
            wp_insert_term( $data['name'], 'license', array(
                'slug'        => $slug,
                'description' => $data['description'],
            ) );
        }
    }
}

/**
 * Add license info to REST API response
 *
 * @since 1.0.0
 *
 * @param array   $response   The response data
 * @param WP_Term $term       The term object
 * @param WP_REST_Request $request The request
 * @return array
 */
function oc_rest_license_response( $response, $term, $request ) {
    // Add license number if exists
    $license_number = get_term_meta( $term->term_id, 'oc_license_number', true );
    if ( $license_number ) {
        $response->data['license_number'] = $license_number;
    }
    
    // Add website URL
    $license_url = get_term_meta( $term->term_id, 'oc_license_url', true );
    if ( $license_url ) {
        $response->data['license_url'] = $license_url;
    }
    
    return $response;
}
add_filter( 'rest_prepare_license', 'oc_rest_license_response', 10, 3 );

/**
 * Add license fields to edit form
 *
 * @since 1.0.0
 *
 * @param WP_Term $term Term being edited
 */
function oc_license_edit_form_fields( $term ) {
    $license_number = get_term_meta( $term->term_id, 'oc_license_number', true );
    $license_url = get_term_meta( $term->term_id, 'oc_license_url', true );
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="oc_license_number"><?php esc_html_e( 'License Number', 'odds-comparison' ); ?></label>
        </th>
        <td>
            <input type="text" name="oc_license_number" id="oc_license_number" value="<?php echo esc_attr( $license_number ); ?>">
            <p class="description"><?php esc_html_e( 'The official license number.', 'odds-comparison' ); ?></p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="oc_license_url"><?php esc_html_e( 'License URL', 'odds-comparison' ); ?></label>
        </th>
        <td>
            <input type="url" name="oc_license_url" id="oc_license_url" value="<?php echo esc_attr( $license_url ); ?>">
            <p class="description"><?php esc_html_e( 'URL to verify the license.', 'odds-comparison' ); ?></p>
        </td>
    </tr>
    <?php
}
add_action( 'license_edit_form_fields', 'oc_license_edit_form_fields' );

/**
 * Save license meta
 *
 * @since 1.0.0
 *
 * @param int $term_id Term ID
 */
function oc_save_license_meta( $term_id ) {
    if ( isset( $_POST['oc_license_number'] ) ) {
        update_term_meta( $term_id, 'oc_license_number', sanitize_text_field( $_POST['oc_license_number'] ) );
    }
    
    if ( isset( $_POST['oc_license_url'] ) ) {
        update_term_meta( $term_id, 'oc_license_url', esc_url_raw( $_POST['oc_license_url'] ) );
    }
}
add_action( 'edited_license', 'oc_save_license_meta' );

/**
 * Get operators by license
 *
 * @since 1.0.0
 *
 * @param string $license_slug License slug
 * @param array  $args        Additional arguments
 * @return array
 */
function oc_get_operators_by_license( $license_slug, $args = array() ) {
    $defaults = array(
        'post_type'      => 'operator',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'tax_query'      => array(
            array(
                'taxonomy' => 'license',
                'field'    => 'slug',
                'terms'    => $license_slug,
            ),
        ),
    );
    
    $args = wp_parse_args( $args, $defaults );
    
    $query = new WP_Query( $args );
    
    return $query->posts ?: array();
}

/**
 * Get license badge HTML
 *
 * @since 1.0.0
 *
 * @param string|int $license License term ID or slug
 * @return string
 */
function oc_get_license_badge( $license ) {
    if ( is_numeric( $license ) ) {
        $term = get_term( $license, 'license' );
    } else {
        $term = get_term_by( 'slug', $license, 'license' );
    }
    
    if ( ! $term ) {
        return '';
    }
    
    $license_url = get_term_meta( $term->term_id, 'oc_license_url', true );
    $license_number = get_term_meta( $term->term_id, 'oc_license_number', true );
    
    $content = '<span class="oc-license-badge"';
    if ( $license_url ) {
        $content .= ' title="' . esc_attr( $term->name );
        if ( $license_number ) {
            $content .= ' - ' . $license_number;
        }
        $content .= '"';
    }
    $content .= '>';
    
    if ( $license_url ) {
        $content .= '<a href="' . esc_url( $license_url ) . '" target="_blank" rel="noopener noreferrer">';
        $content .= esc_html( $term->name );
        $content .= '</a>';
    } else {
        $content .= esc_html( $term->name );
    }
    
    $content .= '</span>';
    
    return $content;
}

/**
 * Get all licenses with operator count
 *
 * @since 1.0.0
 *
 * @return array
 */
function oc_get_licenses_with_counts() {
    $licenses = get_terms( array(
        'taxonomy'   => 'license',
        'hide_empty' => true,
    ) );
    
    $result = array();
    
    foreach ( $licenses as $license ) {
        $result[ $license->slug ] = array(
            'name'       => $license->name,
            'slug'       => $license->slug,
            'count'      => $license->count,
            'license_id' => $license->term_id,
        );
    }
    
    return $result;
}

