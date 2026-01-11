<?php
/**
 * Teams Taxonomy
 * 
 * Registers the 'team' taxonomy for sports teams.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register Teams Taxonomy
 * 
 * @since 1.0.0
 */
function oc_register_teams_taxonomy() {
    $labels = array(
        'name'                  => _x( 'Teams', 'Taxonomy general name', 'odds-comparison' ),
        'singular_name'         => _x( 'Team', 'Taxonomy singular name', 'odds-comparison' ),
        'menu_name'             => _x( 'Teams', 'Admin Menu text', 'odds-comparison' ),
        'all_items'             => __( 'All Teams', 'odds-comparison' ),
        'parent_item'           => __( 'Parent Team', 'odds-comparison' ),
        'parent_item_colon'     => __( 'Parent Team:', 'odds-comparison' ),
        'new_item_name'         => __( 'New Team Name', 'odds-comparison' ),
        'add_new_item'          => __( 'Add New Team', 'odds-comparison' ),
        'edit_item'             => __( 'Edit Team', 'odds-comparison' ),
        'update_item'           => __( 'Update Team', 'odds-comparison' ),
        'view_item'             => __( 'View Team', 'odds-comparison' ),
        'search_items'          => __( 'Search Teams', 'odds-comparison' ),
        'not_found'             => __( 'No teams found.', 'odds-comparison' ),
        'not_found_in_trash'    => __( 'No teams found in Trash.', 'odds-comparison' ),
        'back_to_items'         => __( 'Back to Teams', 'odds-comparison' ),
    );
    
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
        'show_tagcloud'      => false,
        'show_in_quick_edit' => true,
        'show_admin_column'  => true,
        'hierarchical'       => false,
        'query_var'          => true,
        'rewrite'            => array(
            'slug'       => 'team',
            'with_front' => false,
        ),
        'capabilities'       => array(
            'manage_terms' => 'edit_posts',
            'edit_terms'   => 'edit_posts',
            'delete_terms' => 'edit_posts',
            'assign_terms' => 'edit_posts',
        ),
        'show_in_rest'       => true,
        'rest_base'          => 'teams',
        'rest_controller_class' => 'WP_REST_Terms_Controller',
    );
    
    register_taxonomy( 'team', array( 'match' ), $args );
}
add_action( 'init', 'oc_register_teams_taxonomy', 0 );

/**
 * Add team meta fields
 *
 * @since 1.0.0
 */
function oc_add_team_meta_fields() {
    add_action( 'team_add_form_fields', 'oc_render_team_meta_fields' );
    add_action( 'team_edit_form_fields', 'oc_render_team_edit_meta_fields' );
    add_action( 'team_add_form_fields', 'oc_render_team_country_field' );
    add_action( 'team_edit_form_fields', 'oc_render_team_country_field' );
    add_action( 'team_add_form_fields', 'oc_render_team_logo_field' );
    add_action( 'team_edit_form_fields', 'oc_render_team_logo_field' );
    add_action( 'created_team', 'oc_save_team_meta' );
    add_action( 'edited_team', 'oc_save_team_meta' );
}
add_action( 'init', 'oc_add_team_meta_fields' );

/**
 * Enqueue media uploader scripts for team taxonomy
 *
 * @since 1.0.0
 */
function oc_enqueue_team_media_scripts() {
    $screen = get_current_screen();

    // Check if we're on the team taxonomy edit/add pages
    if ( $screen && ( strpos( $screen->id, 'team' ) !== false || ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === 'team' ) ) ) {
        wp_enqueue_media();
        wp_enqueue_script( 'oc-team-logo-upload', OC_ASSETS_URI . '/js/team-logo-upload.js', array( 'jquery' ), OC_THEME_VERSION, true );

        // Localize script with translation strings
        wp_localize_script( 'oc-team-logo-upload', 'oc_team_logo_upload_vars', array(
            'select_title' => __( 'Select Team Logo', 'odds-comparison' ),
            'button_text'  => __( 'Use this image', 'odds-comparison' ),
        ) );
    }
}
add_action( 'admin_enqueue_scripts', 'oc_enqueue_team_media_scripts' );

/**
 * Render team meta fields (add form)
 * 
 * @since 1.0.0
 */
function oc_render_team_meta_fields() {
    ?>
    <div class="form-field oc-form-row">
        <label for="oc_team_short_name"><?php esc_html_e( 'Short Name', 'odds-comparison' ); ?></label>
        <input type="text" name="oc_team_short_name" id="oc_team_short_name" 
               placeholder="e.g., MUN, FCB, RMA">
        <p class="description"><?php esc_html_e( 'Abbreviated team name for display in tables.', 'odds-comparison' ); ?></p>
    </div>
    
    <div class="form-field oc-form-row">
        <label for="oc_team_country"><?php esc_html_e( 'Country', 'odds-comparison' ); ?></label>
        <input type="text" name="oc_team_country" id="oc_team_country" 
               placeholder="e.g., England, Spain, Germany">
    </div>
    
    <div class="form-field oc-form-row">
        <label for="oc_team_stadium"><?php esc_html_e( 'Home Stadium', 'odds-comparison' ); ?></label>
        <input type="text" name="oc_team_stadium" id="oc_team_stadium" 
               placeholder="e.g., Old Trafford, Camp Nou">
    </div>
    <?php
}

/**
 * Render team meta fields (edit form)
 * 
 * @since 1.0.0
 * 
 * @param WP_Term $term Current term object
 */
function oc_render_team_edit_meta_fields( $term ) {
    $term_id = is_object( $term ) && is_a( $term, 'WP_Term' ) ? $term->term_id : 0;
    $short_name = $term_id ? get_term_meta( $term_id, 'oc_team_short_name', true ) : '';
    $country = $term_id ? get_term_meta( $term_id, 'oc_team_country', true ) : '';
    $stadium = $term_id ? get_term_meta( $term_id, 'oc_team_stadium', true ) : '';
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="oc_team_short_name"><?php esc_html_e( 'Short Name', 'odds-comparison' ); ?></label>
        </th>
        <td>
            <input type="text" name="oc_team_short_name" id="oc_team_short_name" 
                   value="<?php echo esc_attr( $short_name ); ?>" 
                   placeholder="e.g., MUN, FCB, RMA">
            <p class="description"><?php esc_html_e( 'Abbreviated team name for display in tables.', 'odds-comparison' ); ?></p>
        </td>
    </tr>
    
    <tr class="form-field">
        <th scope="row">
            <label for="oc_team_country"><?php esc_html_e( 'Country', 'odds-comparison' ); ?></label>
        </th>
        <td>
            <input type="text" name="oc_team_country" id="oc_team_country" 
                   value="<?php echo esc_attr( $country ); ?>" 
                   placeholder="e.g., England, Spain, Germany">
        </td>
    </tr>
    
    <tr class="form-field">
        <th scope="row">
            <label for="oc_team_stadium"><?php esc_html_e( 'Home Stadium', 'odds-comparison' ); ?></label>
        </th>
        <td>
            <input type="text" name="oc_team_stadium" id="oc_team_stadium" 
                   value="<?php echo esc_attr( $stadium ); ?>" 
                   placeholder="e.g., Old Trafford, Camp Nou">
        </td>
    </tr>
    <?php
}

/**
 * Save team meta fields
 * 
 * @since 1.0.0
 * 
 * @param int $term_id Term ID
 */
function oc_save_team_meta_fields( $term_id ) {
    if ( isset( $_POST['oc_team_short_name'] ) ) {
        update_term_meta( $term_id, 'oc_team_short_name', sanitize_text_field( $_POST['oc_team_short_name'] ) );
    }
    
    if ( isset( $_POST['oc_team_country'] ) ) {
        update_term_meta( $term_id, 'oc_team_country', sanitize_text_field( $_POST['oc_team_country'] ) );
    }
    
    if ( isset( $_POST['oc_team_stadium'] ) ) {
        update_term_meta( $term_id, 'oc_team_stadium', sanitize_text_field( $_POST['oc_team_stadium'] ) );
    }
}
add_action( 'created_team', 'oc_save_team_meta_fields' );
add_action( 'edited_team', 'oc_save_team_meta_fields' );

/**
 * Add team admin columns
 * 
 * @since 1.0.0
 * 
 * @param array $columns Columns array
 * @return array Modified columns
 */
function oc_team_admin_columns( $columns ) {
    $columns['oc_team_country'] = __( 'Country', 'odds-comparison' );
    $columns['oc_team_short_name'] = __( 'Short Name', 'odds-comparison' );
    return $columns;
}
add_filter( 'manage_edit-team_columns', 'oc_team_admin_columns' );

/**
 * Render team admin column content
 * 
 * @since 1.0.0
 * 
 * @param string $column_name Column name
 * @param int    $term_id     Term ID
 */
function oc_team_admin_column_content( $column_name, $term_id ) {
    switch ( $column_name ) {
        case 'oc_team_country':
            echo esc_html( get_term_meta( $term_id, 'oc_team_country', true ) );
            break;
        case 'oc_team_short_name':
            echo esc_html( get_term_meta( $term_id, 'oc_team_short_name', true ) );
            break;
    }
}
add_action( 'manage_team_custom_column', 'oc_team_admin_column_content', 10, 2 );

/**
 * Render team country field (dropdown)
 * 
 * @since 1.0.0
 * 
 * @param WP_Term $term Current term object
 */
function oc_render_team_country_field( $term ) {
    // In add form, $term is not passed or may be a string
    $term_id = is_object( $term ) && is_a( $term, 'WP_Term' ) ? $term->term_id : 0;
    $country = $term_id ? get_term_meta( $term_id, 'oc_team_country', true ) : '';
    
    $countries = oc_get_countries_list();
    
    ?>
    <div class="form-field term-group">
        <label for="oc_team_country"><?php esc_html_e( 'Country', 'odds-comparison' ); ?></label>
        <select id="oc_team_country" name="oc_team_country">
            <option value=""><?php esc_html_e( 'Select country...', 'odds-comparison' ); ?></option>
            <?php foreach ( $countries as $code => $name ) : ?>
                <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $country, $code ); ?>>
                    <?php echo esc_html( $name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
}

/**
 * Render team logo field
 * 
 * @since 1.0.0
 * 
 * @param WP_Term $term Current term object
 */
function oc_render_team_logo_field( $term ) {
    // In add form, $term is not passed or may be a string
    $term_id = is_object( $term ) && is_a( $term, 'WP_Term' ) ? $term->term_id : 0;
    $logo_id = $term_id ? get_term_meta( $term_id, 'oc_team_logo_id', true ) : 0;
    $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'team-logo' ) : '';
    
    ?>
    <tr class="form-field">
        <th scope="row"><label for="oc_team_logo"><?php esc_html_e( 'Team Logo', 'odds-comparison' ); ?></label></th>
        <td>
            <div class="team-logo-wrapper">
                <?php if ( $logo_url ) : ?>
                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="" style="max-width: 100px; max-height: 100px; margin-bottom: 10px;">
                <?php endif; ?>
                <input type="hidden" id="oc_team_logo_id" name="oc_team_logo_id" value="<?php echo esc_attr( $logo_id ); ?>">
                <button type="button" class="button" id="oc_upload_team_logo"><?php esc_html_e( 'Upload Logo', 'odds-comparison' ); ?></button>
                <?php if ( $logo_id ) : ?>
                    <button type="button" class="button" id="oc_remove_team_logo"><?php esc_html_e( 'Remove', 'odds-comparison' ); ?></button>
                <?php endif; ?>
            </div>
            <p class="description"><?php esc_html_e( 'Upload a square logo for this team (PNG or JPG recommended).', 'odds-comparison' ); ?></p>
        </td>
    </tr>
    
    <script>
    jQuery(document).ready(function($) {
        var frame;
        $('#oc_upload_team_logo').on('click', function(e) {
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title: '<?php esc_html_e( 'Select Team Logo', 'odds-comparison' ); ?>',
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#oc_team_logo_id').val(attachment.id);
                var html = '<img src="' + attachment.url + '" style="max-width: 100px; max-height: 100px; margin-bottom: 10px;">';
                $('.team-logo-wrapper img').remove();
                $(html).insertBefore('#oc_upload_team_logo');
                $('#oc_remove_team_logo').show();
            });
            frame.open();
        });
        $('#oc_remove_team_logo').on('click', function() {
            $('#oc_team_logo_id').val('');
            $('.team-logo-wrapper img').remove();
            $(this).hide();
        });
    });
    </script>
    <?php
}

/**
 * Save team meta fields (from leagues.php)
 * 
 * @since 1.0.0
 * 
 * @param int $term_id Term ID
 */
function oc_save_team_meta( $term_id ) {
    if ( isset( $_POST['oc_team_country'] ) ) {
        update_term_meta( $term_id, 'oc_team_country', sanitize_text_field( $_POST['oc_team_country'] ) );
    }
    
    if ( isset( $_POST['oc_team_logo_id'] ) ) {
        update_term_meta( $term_id, 'oc_team_logo_id', intval( $_POST['oc_team_logo_id'] ) );
    }
}

/**
 * Get countries list
 * 
 * @since 1.0.0
 * 
 * @return array Countries with codes as keys
 */
function oc_get_countries_list() {
    return array(
        'AF' => __( 'Afghanistan', 'odds-comparison' ),
        'AL' => __( 'Albania', 'odds-comparison' ),
        'DZ' => __( 'Algeria', 'odds-comparison' ),
        'AR' => __( 'Argentina', 'odds-comparison' ),
        'AU' => __( 'Australia', 'odds-comparison' ),
        'AT' => __( 'Austria', 'odds-comparison' ),
        'BE' => __( 'Belgium', 'odds-comparison' ),
        'BR' => __( 'Brazil', 'odds-comparison' ),
        'BG' => __( 'Bulgaria', 'odds-comparison' ),
        'CA' => __( 'Canada', 'odds-comparison' ),
        'CL' => __( 'Chile', 'odds-comparison' ),
        'CN' => __( 'China', 'odds-comparison' ),
        'CO' => __( 'Colombia', 'odds-comparison' ),
        'HR' => __( 'Croatia', 'odds-comparison' ),
        'CZ' => __( 'Czech Republic', 'odds-comparison' ),
        'DK' => __( 'Denmark', 'odds-comparison' ),
        'EG' => __( 'Egypt', 'odds-comparison' ),
        'FI' => __( 'Finland', 'odds-comparison' ),
        'FR' => __( 'France', 'odds-comparison' ),
        'DE' => __( 'Germany', 'odds-comparison' ),
        'GR' => __( 'Greece', 'odds-comparison' ),
        'HU' => __( 'Hungary', 'odds-comparison' ),
        'IS' => __( 'Iceland', 'odds-comparison' ),
        'IN' => __( 'India', 'odds-comparison' ),
        'ID' => __( 'Indonesia', 'odds-comparison' ),
        'IE' => __( 'Ireland', 'odds-comparison' ),
        'IT' => __( 'Italy', 'odds-comparison' ),
        'JP' => __( 'Japan', 'odds-comparison' ),
        'KR' => __( 'South Korea', 'odds-comparison' ),
        'MX' => __( 'Mexico', 'odds-comparison' ),
        'NL' => __( 'Netherlands', 'odds-comparison' ),
        'NZ' => __( 'New Zealand', 'odds-comparison' ),
        'NO' => __( 'Norway', 'odds-comparison' ),
        'PE' => __( 'Peru', 'odds-comparison' ),
        'PL' => __( 'Poland', 'odds-comparison' ),
        'PT' => __( 'Portugal', 'odds-comparison' ),
        'RO' => __( 'Romania', 'odds-comparison' ),
        'RU' => __( 'Russia', 'odds-comparison' ),
        'SA' => __( 'Saudi Arabia', 'odds-comparison' ),
        'RS' => __( 'Serbia', 'odds-comparison' ),
        'SK' => __( 'Slovakia', 'odds-comparison' ),
        'SI' => __( 'Slovenia', 'odds-comparison' ),
        'ZA' => __( 'South Africa', 'odds-comparison' ),
        'ES' => __( 'Spain', 'odds-comparison' ),
        'SE' => __( 'Sweden', 'odds-comparison' ),
        'CH' => __( 'Switzerland', 'odds-comparison' ),
        'TR' => __( 'Turkey', 'odds-comparison' ),
        'UA' => __( 'Ukraine', 'odds-comparison' ),
        'AE' => __( 'United Arab Emirates', 'odds-comparison' ),
        'GB' => __( 'United Kingdom', 'odds-comparison' ),
        'US' => __( 'United States', 'odds-comparison' ),
        'UY' => __( 'Uruguay', 'odds-comparison' ),
    );
}

