<?php
/**
 * Operators Custom Post Type
 * 
 * Registers the 'operator' custom post type for betting operators.
 * Includes all fields and meta boxes for operator data.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register Operators Custom Post Type
 * 
 * @since 1.0.0
 */
function oc_register_operator_cpt() {
    $labels = array(
        'name'                  => _x( 'Operators', 'Post type general name', 'odds-comparison' ),
        'singular_name'         => _x( 'Operator', 'Post type singular name', 'odds-comparison' ),
        'menu_name'             => _x( 'Operators', 'Admin Menu text', 'odds-comparison' ),
        'name_admin_bar'        => _x( 'Operator', 'Add New on Toolbar', 'odds-comparison' ),
        'add_new'               => __( 'Add New', 'odds-comparison' ),
        'add_new_item'          => __( 'Add New Operator', 'odds-comparison' ),
        'new_item'              => __( 'New Operator', 'odds-comparison' ),
        'edit_item'             => __( 'Edit Operator', 'odds-comparison' ),
        'view_item'             => __( 'View Operator', 'odds-comparison' ),
        'all_items'             => __( 'All Operators', 'odds-comparison' ),
        'search_items'          => __( 'Search Operators', 'odds-comparison' ),
        'parent_item_colon'     => __( 'Parent Operators:', 'odds-comparison' ),
        'not_found'             => __( 'No operators found.', 'odds-comparison' ),
        'not_found_in_trash'    => __( 'No operators found in Trash.', 'odds-comparison' ),
        'featured_image'        => _x( 'Operator Logo', 'Overrides the "Featured Image" phrase', 'odds-comparison' ),
        'set_featured_image'    => _x( 'Set logo', 'Overrides the "Set featured image" phrase', 'odds-comparison' ),
        'remove_featured_image' => _x( 'Remove logo', 'Overrides the "Remove featured image" phrase', 'odds-comparison' ),
        'use_featured_image'    => _x( 'Use as logo', 'Overrides the "Use as featured image" phrase', 'odds-comparison' ),
        'archives'              => _x( 'Operator archives', 'The post type archive label used in nav menus', 'odds-comparison' ),
        'insert_into_item'      => _x( 'Insert into operator', 'Overrides the "Insert into post" phrase', 'odds-comparison' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this operator', 'Overrides the "Uploaded to this post" phrase', 'odds-comparison' ),
        'filter_items_list'     => _x( 'Filter operators list', 'Screen reader text for the filter links heading', 'odds-comparison' ),
        'items_list_navigation' => _x( 'Operators list navigation', 'Screen reader text for the pagination heading', 'odds-comparison' ),
        'items_list'            => _x( 'Operators list', 'Screen reader text for the items list heading', 'odds-comparison' ),
    );
    
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array(
            'slug'       => 'betting-operator',
            'with_front' => false,
        ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'menu_icon'          => 'dashicons-building',
        'menu_position'      => 25,
        'supports'           => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'revisions',
            'author',
        ),
        'taxonomies'         => array(),
        'show_in_rest'       => true,
        'rest_base'          => 'operators',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );
    
    register_post_type( 'operator', $args );
}
add_action( 'init', 'oc_register_operator_cpt', 0 );

/**
 * Add operator meta boxes
 * 
 * @since 1.0.0
 */
function oc_add_operator_meta_boxes() {
    add_meta_box(
        'oc_operator_details',
        __( 'Operator Details', 'odds-comparison' ),
        'oc_render_operator_meta_box',
        'operator',
        'normal',
        'high'
    );
    
    add_meta_box(
        'oc_operator_bonus',
        __( 'Bonus & Offers', 'odds-comparison' ),
        'oc_render_operator_bonus_meta_box',
        'operator',
        'normal',
        'high'
    );
    
    add_meta_box(
        'oc_operator_pros_cons',
        __( 'Pros & Cons', 'odds-comparison' ),
        'oc_render_operator_pros_cons_meta_box',
        'side',
        'default'
    );
    
    add_meta_box(
        'oc_operator_ranking',
        __( 'Ranking Score', 'odds-comparison' ),
        'oc_render_operator_ranking_meta_box',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'oc_add_operator_meta_boxes' );

/**
 * Render operator details meta box
 * 
 * @since 1.0.0
 * 
 * @param WP_Post $post Current post object
 */
function oc_render_operator_meta_box( $post ) {
    wp_nonce_field( 'oc_operator_meta_box', 'oc_operator_nonce' );
    
    // Get existing values
    $license      = get_post_meta( $post->ID, 'oc_operator_license', true );
    $rating       = get_post_meta( $post->ID, 'oc_operator_rating', true );
    $affiliate_url = get_post_meta( $post->ID, 'oc_operator_affiliate_url', true );
    $payment_methods = get_post_meta( $post->ID, 'oc_operator_payment_methods', true );
    $min_deposit = get_post_meta( $post->ID, 'oc_operator_min_deposit', true );
    $min_bet     = get_post_meta( $post->ID, 'oc_operator_min_bet', true );
    $license_authorities = array(
        'UKGC'      => __( 'UK Gambling Commission (UKGC)', 'odds-comparison' ),
        'MGA'       => __( 'Malta Gaming Authority (MGA)', 'odds-comparison' ),
        'Gibraltar' => __( 'Gibraltar Regulatory Authority', 'odds-comparison' ),
        'Curacao'   => __( 'Curacao eGaming', 'odds-comparison' ),
        'Kahnawake' => __( 'Kahnawake Gaming Commission', 'odds-comparison' ),
        'Isle of Man' => __( 'Isle of Man Gambling Supervision', 'odds-comparison' ),
        'Other'     => __( 'Other / None', 'odds-comparison' ),
    );
    
    ?>
    <div class="oc-meta-box">
        <div class="oc-form-row">
            <label for="oc_operator_license"><?php esc_html_e( 'License Authority', 'odds-comparison' ); ?></label>
            <select id="oc_operator_license" name="oc_operator_license">
                <option value=""><?php esc_html_e( 'Select license...', 'odds-comparison' ); ?></option>
                <?php foreach ( $license_authorities as $code => $label ) : ?>
                    <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $license, $code ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="oc-form-row">
            <label for="oc_operator_rating"><?php esc_html_e( 'Rating (0-5)', 'odds-comparison' ); ?></label>
            <input type="number" id="oc_operator_rating" name="oc_operator_rating" 
                   value="<?php echo esc_attr( $rating ); ?>" 
                   min="0" max="5" step="0.1">
            <p class="description"><?php esc_html_e( 'Overall rating out of 5 stars', 'odds-comparison' ); ?></p>
        </div>
        
        <div class="oc-form-row">
            <label for="oc_operator_affiliate_url"><?php esc_html_e( 'Affiliate URL', 'odds-comparison' ); ?></label>
            <input type="url" id="oc_operator_affiliate_url" name="oc_operator_affiliate_url" 
                   value="<?php echo esc_attr( $affiliate_url ); ?>" 
                   placeholder="https://...">
            <p class="description"><?php esc_html_e( 'Your unique affiliate link for this operator', 'odds-comparison' ); ?></p>
        </div>
        
        <div class="oc-form-row">
            <label for="oc_operator_payment_methods"><?php esc_html_e( 'Payment Methods', 'odds-comparison' ); ?></label>
            <input type="text" id="oc_operator_payment_methods" name="oc_operator_payment_methods" 
                   value="<?php echo esc_attr( $payment_methods ); ?>" 
                   placeholder="Visa, Mastercard, PayPal, Skrill...">
            <p class="description"><?php esc_html_e( 'Comma-separated list of accepted payment methods', 'odds-comparison' ); ?></p>
        </div>
        
        <div class="oc-form-row oc-form-inline">
            <div class="oc-half">
                <label for="oc_operator_min_deposit"><?php esc_html_e( 'Min. Deposit', 'odds-comparison' ); ?></label>
                <input type="text" id="oc_operator_min_deposit" name="oc_operator_min_deposit" 
                       value="<?php echo esc_attr( $min_deposit ); ?>" 
                       placeholder="€10">
            </div>
            <div class="oc-half">
                <label for="oc_operator_min_bet"><?php esc_html_e( 'Min. Bet', 'odds-comparison' ); ?></label>
                <input type="text" id="oc_operator_min_bet" name="oc_operator_min_bet" 
                       value="<?php echo esc_attr( $min_bet ); ?>" 
                       placeholder="€1">
            </div>
        </div>
    </div>
    
    <style>
        .oc-meta-box .oc-form-row { margin-bottom: 15px; }
        .oc-meta-box label { display: block; font-weight: 600; margin-bottom: 5px; }
        .oc-meta-box input[type="text"],
        .oc-meta-box input[type="url"],
        .oc-meta-box input[type="number"],
        .oc-meta-box select { width: 100%; max-width: 400px; }
        .oc-meta-box .oc-form-inline { display: flex; gap: 20px; }
        .oc-meta-box .oc-half { flex: 1; }
        .oc-meta-box .description { font-size: 12px; color: #666; margin-top: 3px; }
    </style>
    <?php
}

/**
 * Render operator bonus meta box
 * 
 * @since 1.0.0
 * 
 * @param WP_Post $post Current post object
 */
function oc_render_operator_bonus_meta_box( $post ) {
    $bonus_text    = get_post_meta( $post->ID, 'oc_operator_bonus_text', true );
    $bonus_value   = get_post_meta( $post->ID, 'oc_operator_bonus_value', true );
    $bonus_type    = get_post_meta( $post->ID, 'oc_operator_bonus_type', true );
    $bonus_min_odds = get_post_meta( $post->ID, 'oc_operator_bonus_min_odds', true );
    $bonus_wager_requirement = get_post_meta( $post->ID, 'oc_operator_bonus_wager', true );
    $bonus_expiry  = get_post_meta( $post->ID, 'oc_operator_bonus_expiry', true );
    
    $bonus_types = array(
        'deposit_match' => __( 'Deposit Match', 'odds-comparison' ),
        'free_bet'      => __( 'Free Bet', 'odds-comparison' ),
        'no_deposit'    => __( 'No Deposit Bonus', 'odds-comparison' ),
        'enhanced'      => __( 'Enhanced Odds', 'odds-comparison' ),
        'cashback'      => __( 'Cashback', 'odds-comparison' ),
        'other'         => __( 'Other', 'odds-comparison' ),
    );
    
    ?>
    <div class="oc-meta-box">
        <div class="oc-form-row">
            <label for="oc_operator_bonus_text"><?php esc_html_e( 'Bonus Offer Text', 'odds-comparison' ); ?></label>
            <input type="text" id="oc_operator_bonus_text" name="oc_operator_bonus_text" 
                   value="<?php echo esc_attr( $bonus_text ); ?>" 
                   placeholder="100% up to €100">
        </div>
        
        <div class="oc-form-row oc-form-inline">
            <div class="oc-third">
                <label for="oc_operator_bonus_value"><?php esc_html_e( 'Bonus Value', 'odds-comparison' ); ?></label>
                <input type="text" id="oc_operator_bonus_value" name="oc_operator_bonus_value" 
                       value="<?php echo esc_attr( $bonus_value ); ?>" 
                       placeholder="100">
            </div>
            <div class="oc-third">
                <label for="oc_operator_bonus_type"><?php esc_html_e( 'Bonus Type', 'odds-comparison' ); ?></label>
                <select id="oc_operator_bonus_type" name="oc_operator_bonus_type">
                    <?php foreach ( $bonus_types as $code => $label ) : ?>
                        <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $bonus_type, $code ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="oc-third">
                <label for="oc_operator_bonus_expiry"><?php esc_html_e( 'Expiry (days)', 'odds-comparison' ); ?></label>
                <input type="number" id="oc_operator_bonus_expiry" name="oc_operator_bonus_expiry" 
                       value="<?php echo esc_attr( $bonus_expiry ); ?>" 
                       placeholder="30" min="0">
            </div>
        </div>
        
        <div class="oc-form-row">
            <label for="oc_operator_bonus_min_odds"><?php esc_html_e( 'Minimum Odds Required', 'odds-comparison' ); ?></label>
            <input type="text" id="oc_operator_bonus_min_odds" name="oc_operator_bonus_min_odds" 
                   value="<?php echo esc_attr( $bonus_min_odds ); ?>" 
                   placeholder="1.50 or higher">
            <p class="description"><?php esc_html_e( 'Minimum odds for bets placed with bonus funds', 'odds-comparison' ); ?></p>
        </div>
        
        <div class="oc-form-row">
            <label for="oc_operator_bonus_wager"><?php esc_html_e( 'Wagering Requirement', 'odds-comparison' ); ?></label>
            <input type="text" id="oc_operator_bonus_wager" name="oc_operator_bonus_wager" 
                   value="<?php echo esc_attr( $bonus_wager_requirement ); ?>" 
                   placeholder="5x deposit + bonus">
            <p class="description"><?php esc_html_e( 'How many times the bonus must be wagered before withdrawal', 'odds-comparison' ); ?></p>
        </div>
    </div>
    
    <style>
        .oc-meta-box .oc-form-row { margin-bottom: 15px; }
        .oc-meta-box label { display: block; font-weight: 600; margin-bottom: 5px; }
        .oc-meta-box input[type="text"],
        .oc-meta-box input[type="number"],
        .oc-meta-box select { width: 100%; }
        .oc-meta-box .oc-form-inline { display: flex; gap: 20px; }
        .oc-meta-box .oc-third { flex: 1; }
        .oc-meta-box .description { font-size: 12px; color: #666; margin-top: 3px; }
    </style>
    <?php
}

/**
 * Render operator pros & cons meta box
 * 
 * @since 1.0.0
 * 
 * @param WP_Post $post Current post object
 */
function oc_render_operator_pros_cons_meta_box( $post ) {
    $pros = get_post_meta( $post->ID, 'oc_operator_pros', true );
    $cons = get_post_meta( $post->ID, 'oc_operator_cons', true );
    
    // Convert to array if string
    if ( ! is_array( $pros ) && ! empty( $pros ) ) {
        $pros = array_filter( explode( "\n", $pros ) );
    }
    if ( ! is_array( $cons ) && ! empty( $cons ) ) {
        $cons = array_filter( explode( "\n", $cons ) );
    }
    
    ?>
    <div class="oc-meta-box">
        <div class="oc-form-row">
            <label for="oc_operator_pros"><?php esc_html_e( 'Pros', 'odds-comparison' ); ?></label>
            <textarea id="oc_operator_pros" name="oc_operator_pros" rows="4" 
                      placeholder="- Great odds&#10;- Fast withdrawals&#10;- Mobile app"><?php 
                echo is_array( $pros ) ? esc_textarea( implode( "\n", $pros ) ) : ''; 
            ?></textarea>
            <p class="description"><?php esc_html_e( 'One pro per line, starting with a dash (-)', 'odds-comparison' ); ?></p>
        </div>
        
        <div class="oc-form-row">
            <label for="oc_operator_cons"><?php esc_html_e( 'Cons', 'odds-comparison' ); ?></label>
            <textarea id="oc_operator_cons" name="oc_operator_cons" rows="4" 
                      placeholder="- Limited markets&#10;- No live chat"><?php 
                echo is_array( $cons ) ? esc_textarea( implode( "\n", $cons ) ) : ''; 
            ?></textarea>
            <p class="description"><?php esc_html_e( 'One con per line, starting with a dash (-)', 'odds-comparison' ); ?></p>
        </div>
    </div>
    
    <style>
        .oc-meta-box .oc-form-row { margin-bottom: 15px; }
        .oc-meta-box label { display: block; font-weight: 600; margin-bottom: 5px; }
        .oc-meta-box textarea { width: 100%; }
        .oc-meta-box .description { font-size: 12px; color: #666; margin-top: 3px; }
    </style>
    <?php
}

/**
 * Render operator ranking meta box
 * 
 * @since 1.0.0
 * 
 * @param WP_Post $post Current post object
 */
function oc_render_operator_ranking_meta_box( $post ) {
    $score = oc_get_operator_ranking_score( $post->ID );
    
    ?>
    <div class="oc-meta-box">
        <div class="ranking-display">
            <div class="ranking-score"><?php echo esc_html( $score ); ?></div>
            <div class="ranking-label"><?php esc_html_e( 'Overall Score', 'odds-comparison' ); ?></div>
        </div>
        <p class="description">
            <?php esc_html_e( 'Score is calculated based on rating, bonus value, license trust, and odds quality. This value is used for automatic operator ranking.', 'odds-comparison' ); ?>
        </p>
    </div>
    
    <style>
        .ranking-display { text-align: center; padding: 20px 0; }
        .ranking-score { 
            font-size: 48px; 
            font-weight: 700; 
            color: #1a5f7a;
            line-height: 1;
        }
        .ranking-label { 
            font-size: 12px; 
            color: #666; 
            margin-top: 5px;
        }
        .oc-meta-box .description { font-size: 12px; color: #666; margin-top: 10px; }
    </style>
    <?php
}

/**
 * Save operator meta box data
 * 
 * @since 1.0.0
 * 
 * @param int $post_id Post ID
 * @return int Post ID
 */
function oc_save_operator_meta_box( $post_id ) {
    // Check if nonce is valid
    if ( ! isset( $_POST['oc_operator_nonce'] ) || ! wp_verify_nonce( $_POST['oc_operator_nonce'], 'oc_operator_meta_box' ) ) {
        return $post_id;
    }
    
    // Check if user has permission
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }
    
    // Save license authority
    if ( isset( $_POST['oc_operator_license'] ) ) {
        update_post_meta( $post_id, 'oc_operator_license', sanitize_text_field( $_POST['oc_operator_license'] ) );
    }
    
    // Save rating
    if ( isset( $_POST['oc_operator_rating'] ) ) {
        $rating = floatval( $_POST['oc_operator_rating'] );
        $rating = min( 5, max( 0, $rating ) ); // Clamp between 0-5
        update_post_meta( $post_id, 'oc_operator_rating', $rating );
    }
    
    // Save affiliate URL
    if ( isset( $_POST['oc_operator_affiliate_url'] ) ) {
        update_post_meta( $post_id, 'oc_operator_affiliate_url', esc_url_raw( $_POST['oc_operator_affiliate_url'] ) );
    }
    
    // Save payment methods
    if ( isset( $_POST['oc_operator_payment_methods'] ) ) {
        update_post_meta( $post_id, 'oc_operator_payment_methods', sanitize_text_field( $_POST['oc_operator_payment_methods'] ) );
    }
    
    // Save min deposit
    if ( isset( $_POST['oc_operator_min_deposit'] ) ) {
        update_post_meta( $post_id, 'oc_operator_min_deposit', sanitize_text_field( $_POST['oc_operator_min_deposit'] ) );
    }
    
    // Save min bet
    if ( isset( $_POST['oc_operator_min_bet'] ) ) {
        update_post_meta( $post_id, 'oc_operator_min_bet', sanitize_text_field( $_POST['oc_operator_min_bet'] ) );
    }
    
    // Save bonus text
    if ( isset( $_POST['oc_operator_bonus_text'] ) ) {
        update_post_meta( $post_id, 'oc_operator_bonus_text', sanitize_text_field( $_POST['oc_operator_bonus_text'] ) );
    }
    
    // Save bonus value
    if ( isset( $_POST['oc_operator_bonus_value'] ) ) {
        update_post_meta( $post_id, 'oc_operator_bonus_value', floatval( $_POST['oc_operator_bonus_value'] ) );
    }
    
    // Save bonus type
    if ( isset( $_POST['oc_operator_bonus_type'] ) ) {
        update_post_meta( $post_id, 'oc_operator_bonus_type', sanitize_text_field( $_POST['oc_operator_bonus_type'] ) );
    }
    
    // Save bonus min odds
    if ( isset( $_POST['oc_operator_bonus_min_odds'] ) ) {
        update_post_meta( $post_id, 'oc_operator_bonus_min_odds', sanitize_text_field( $_POST['oc_operator_bonus_min_odds'] ) );
    }
    
    // Save bonus wager requirement
    if ( isset( $_POST['oc_operator_bonus_wager'] ) ) {
        update_post_meta( $post_id, 'oc_operator_bonus_wager', sanitize_text_field( $_POST['oc_operator_bonus_wager'] ) );
    }
    
    // Save bonus expiry
    if ( isset( $_POST['oc_operator_bonus_expiry'] ) ) {
        update_post_meta( $post_id, 'oc_operator_bonus_expiry', intval( $_POST['oc_operator_bonus_expiry'] ) );
    }
    
    // Save pros
    if ( isset( $_POST['oc_operator_pros'] ) ) {
        $pros = array_filter( array_map( 'trim', explode( "\n", $_POST['oc_operator_pros'] ) ) );
        update_post_meta( $post_id, 'oc_operator_pros', $pros );
    }
    
    // Save cons
    if ( isset( $_POST['oc_operator_cons'] ) ) {
        $cons = array_filter( array_map( 'trim', explode( "\n", $_POST['oc_operator_cons'] ) ) );
        update_post_meta( $post_id, 'oc_operator_cons', $cons );
    }
    
    return $post_id;
}
add_action( 'save_post_operator', 'oc_save_operator_meta_box' );

/**
 * Customize operators archive title
 * 
 * @since 1.0.0
 * 
 * @param string $title Archive title
 * @param string $sep   Title separator
 * @return string Modified title
 */
function oc_operator_archive_title( $title, $sep = '' ) {
    if ( is_post_type_archive( 'operator' ) ) {
        $title = __( 'Betting Operators', 'odds-comparison' ) . " {$sep} " . get_bloginfo( 'name' );
    }
    return $title;
}
add_filter( 'wp_title', 'oc_operator_archive_title', 10, 2 );
add_filter( 'document_title_parts', 'oc_operator_archive_title' );

/**
 * Add operators to main query on home page
 * 
 * @since 1.0.0
 * 
 * @param WP_Query $query Main query
 */
function oc_add_operators_to_home( $query ) {
    if ( is_home() && $query->is_main_query() ) {
        $query->set( 'post_type', array( 'post', 'operator' ) );
    }
}
add_action( 'pre_get_posts', 'oc_add_operators_to_home' );

/**
 * Modify operators archive query
 *
 * Handles filtering by bonus type, payment method, and custom sorting.
 *
 * @since 1.0.0
 *
 * @param WP_Query $query Main query
 */
function oc_modify_operators_archive_query( $query ) {
    if ( is_post_type_archive( 'operator' ) && $query->is_main_query() ) {
        $meta_query = array();

        // Filter by minimum rating
        if ( isset( $_GET['min_rating'] ) && floatval( $_GET['min_rating'] ) > 0 ) {
            $meta_query[] = array(
                'key'     => 'oc_operator_rating',
                'value'   => floatval( $_GET['min_rating'] ),
                'compare' => '>=',
                'type'    => 'NUMERIC',
            );
        }

        // Filter by bonus type
        if ( isset( $_GET['bonus_type'] ) && ! empty( $_GET['bonus_type'] ) ) {
            $meta_query[] = array(
                'key'     => 'oc_operator_bonus_type',
                'value'   => sanitize_text_field( $_GET['bonus_type'] ),
                'compare' => '=',
            );
        }

        // Filter by payment method (partial match in comma-separated list)
        if ( isset( $_GET['payment'] ) && ! empty( $_GET['payment'] ) ) {
            $meta_query[] = array(
                'key'     => 'oc_operator_payment_methods',
                'value'   => sanitize_text_field( $_GET['payment'] ),
                'compare' => 'LIKE',
            );
        }

        // Add meta query if we have filters
        if ( ! empty( $meta_query ) ) {
            $meta_query['relation'] = 'AND';
            $query->set( 'meta_query', $meta_query );
        }

        // Handle sorting
        $sort = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : 'rating';

        switch ( $sort ) {
            case 'name':
                $query->set( 'orderby', 'title' );
                $query->set( 'order', 'ASC' );
                break;
            case 'bonus':
                $query->set( 'meta_key', 'oc_operator_bonus_value' );
                $query->set( 'orderby', 'meta_value_num' );
                $query->set( 'order', 'DESC' );
                break;
            case 'newest':
                $query->set( 'orderby', 'date' );
                $query->set( 'order', 'DESC' );
                break;
            case 'rating':
            default:
                $query->set( 'meta_key', 'oc_operator_rating' );
                $query->set( 'orderby', 'meta_value_num' );
                $query->set( 'order', 'DESC' );
                break;
        }
    }
}
add_action( 'pre_get_posts', 'oc_modify_operators_archive_query' );

/**
 * Add operator data to REST API response
 * 
 * @since 1.0.0
 * 
 * @param WP_REST_Response $response Response object
 * @param WP_Post          $post     Post object
 * @return WP_REST_Response Modified response
 */
function oc_add_operator_to_rest_response( $response, $post ) {
    if ( 'operator' !== $post->post_type ) {
        return $response;
    }
    
    $response->data['operator_data'] = array(
        'rating'         => oc_get_operator_rating( $post->ID ),
        'license'        => get_post_meta( $post->ID, 'oc_operator_license', true ),
        'bonus'          => get_post_meta( $post->ID, 'oc_operator_bonus_text', true ),
        'bonus_value'    => get_post_meta( $post->ID, 'oc_operator_bonus_value', true ),
        'affiliate_url'  => oc_get_affiliate_link( $post->ID ),
        'logo'           => get_the_post_thumbnail_url( $post->ID, 'operator-logo' ),
        'payment_methods' => get_post_meta( $post->ID, 'oc_operator_payment_methods', true ),
        'ranking_score'  => oc_get_operator_ranking_score( $post->ID ),
    );
    
    return $response;
}
add_filter( 'rest_prepare_post', 'oc_add_operator_to_rest_response', 10, 2 );

