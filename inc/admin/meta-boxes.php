<?php
/**
 * Additional Meta Boxes
 *
 * Registers custom meta boxes for match and operator post types.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register additional meta boxes
 *
 * @since 1.0.0
 */
function oc_register_meta_boxes() {
    // Match odds meta box
    add_meta_box(
        'oc-match-odds',
        __( 'Match Odds', 'odds-comparison' ),
        'oc_match_odds_meta_box_callback',
        'match',
        'normal',
        'high'
    );
    
    // Match highlights meta box
    add_meta_box(
        'oc-match-highlights',
        __( 'Match Highlights', 'odds-comparison' ),
        'oc_match_highlights_meta_box_callback',
        'match',
        'side',
        'default'
    );
    
    // Operator bonus meta box
    add_meta_box(
        'oc-operator-bonus',
        __( 'Bonus Information', 'odds-comparison' ),
        'oc_operator_bonus_meta_box_callback',
        'operator',
        'normal',
        'high'
    );
    
    // Operator pros/cons meta box
    add_meta_box(
        'oc-operator-pros-cons',
        __( 'Pros & Cons', 'odds-comparison' ),
        'oc_operator_pros_cons_meta_box_callback',
        'operator',
        'normal',
        'default'
    );
    
    // Operator ranking meta box
    add_meta_box(
        'oc-operator-ranking',
        __( 'Ranking Info', 'odds-comparison' ),
        'oc_operator_ranking_meta_box_callback',
        'operator',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'oc_register_meta_boxes' );

/**
 * Match odds meta box callback
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Post object
 */
function oc_match_odds_meta_box_callback( $post ) {
    wp_nonce_field( 'oc_save_odds_meta', 'oc_odds_meta_nonce' );
    
    $odds = oc_get_match_odds( $post->ID );
    $operators = get_posts( array(
        'post_type'   => 'operator',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby'     => 'title',
        'order'       => 'ASC',
    ) );
    
    ?>
    <div class="oc-odds-meta-box">
        <div class="oc-odds-header">
            <span class="bookmaker-header"><?php esc_html_e( 'Bookmaker', 'odds-comparison' ); ?></span>
            <span class="odds-header"><?php esc_html_e( 'Home', 'odds-comparison' ); ?></span>
            <span class="odds-header"><?php esc_html_e( 'Draw', 'odds-comparison' ); ?></span>
            <span class="odds-header"><?php esc_html_e( 'Away', 'odds-comparison' ); ?></span>
            <span class="actions-header"><?php esc_html_e( 'Actions', 'odds-comparison' ); ?></span>
        </div>
        
        <div id="oc-odds-rows">
            <?php if ( ! empty( $odds ) ) : ?>
                <?php foreach ( $odds as $odd ) : ?>
                    <?php
                    $operator = get_post( $odd['bookmaker_id'] );
                    $operator_name = $operator ? $operator->post_title : __( 'Unknown', 'odds-comparison' );
                    ?>
                    <div class="oc-odds-row" data-row-id="<?php echo esc_attr( $odd['id'] ); ?>">
                        <span class="bookmaker-name"><?php echo esc_html( $operator_name ); ?></span>
                        <span class="odds-value"><?php echo esc_html( $odd['odds_home'] ? number_format( $odd['odds_home'], 2 ) : '-' ); ?></span>
                        <span class="odds-value"><?php echo esc_html( $odd['odds_draw'] ? number_format( $odd['odds_draw'], 2 ) : '-' ); ?></span>
                        <span class="odds-value"><?php echo esc_html( $odd['odds_away'] ? number_format( $odd['odds_away'], 2 ) : '-' ); ?></span>
                        <span class="row-actions">
                            <button type="button" class="button button-small oc-edit-odds" data-odds-id="<?php echo esc_attr( $odd['id'] ); ?>">
                                <?php esc_html_e( 'Edit', 'odds-comparison' ); ?>
                            </button>
                            <button type="button" class="button button-small oc-delete-odds" data-odds-id="<?php echo esc_attr( $odd['id'] ); ?>">
                                <?php esc_html_e( 'Delete', 'odds-comparison' ); ?>
                            </button>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="oc-no-odds">
                    <p><?php esc_html_e( 'No odds added yet. Add odds from the form below.', 'odds-comparison' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="oc-add-odds-form">
            <h4><?php esc_html_e( 'Add New Odds', 'odds-comparison' ); ?></h4>
            <div class="form-row">
                <label for="oc-odds-bookmaker"><?php esc_html_e( 'Bookmaker:', 'odds-comparison' ); ?></label>
                <select id="oc-odds-bookmaker" name="oc_odds_bookmaker">
                    <option value=""><?php esc_html_e( 'Select Bookmaker', 'odds-comparison' ); ?></option>
                    <?php foreach ( $operators as $op ) : ?>
                        <option value="<?php echo esc_attr( $op->ID ); ?>"><?php echo esc_html( $op->post_title ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label for="oc-odds-home"><?php esc_html_e( 'Home:', 'odds-comparison' ); ?></label>
                <input type="number" id="oc-odds-home" name="oc_odds_home" step="0.01" min="1" placeholder="1.00">
            </div>
            <div class="form-row">
                <label for="oc-odds-draw"><?php esc_html_e( 'Draw:', 'odds-comparison' ); ?></label>
                <input type="number" id="oc-odds-draw" name="oc_odds_draw" step="0.01" min="1" placeholder="1.00">
            </div>
            <div class="form-row">
                <label for="oc-odds-away"><?php esc_html_e( 'Away:', 'odds-comparison' ); ?></label>
                <input type="number" id="oc-odds-away" name="oc_odds_away" step="0.01" min="1" placeholder="1.00">
            </div>
            <button type="button" class="button button-primary" id="oc-add-odds">
                <?php esc_html_e( 'Add Odds', 'odds-comparison' ); ?>
            </button>
        </div>
        
        <p class="description">
            <?php esc_html_e( 'Note: Odds are saved automatically when added via the form above.', 'odds-comparison' ); ?>
            <br>
            <?php esc_html_e( 'For bulk updates, consider using the Import Odds tool under Odds > Import Odds.', 'odds-comparison' ); ?>
        </p>
    </div>
    
    <style>
        .oc-odds-meta-box { padding: 10px 0; }
        .oc-odds-header, .oc-odds-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 150px;
            gap: 10px;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .oc-odds-header {
            font-weight: 600;
            background: #f9f9f9;
            border-bottom: 2px solid #ddd;
        }
        .oc-odds-row:last-child { border-bottom: none; }
        .oc-no-odds { padding: 20px; text-align: center; color: #666; }
        .oc-add-odds-form {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }
        .oc-add-odds-form h4 { margin-top: 0; margin-bottom: 15px; }
        .oc-add-odds-form .form-row {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 10px;
            vertical-align: middle;
        }
    </style>
    <?php
}

/**
 * Match highlights meta box callback
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Post object
 */
function oc_match_highlights_meta_box_callback( $post ) {
    wp_nonce_field( 'oc_save_highlights_meta', 'oc_highlights_meta_nonce' );
    
    $is_featured = get_post_meta( $post->ID, 'oc_featured_match', true );
    $is_live = get_post_meta( $post->ID, 'oc_live_match', true );
    $importance = get_post_meta( $post->ID, 'oc_match_importance', true );
    ?>
    <div class="oc-highlights-meta-box">
        <p>
            <label for="oc_featured_match">
                <input type="checkbox" id="oc_featured_match" name="oc_featured_match" value="1" <?php checked( $is_featured, 1 ); ?>>
                <?php esc_html_e( 'Featured Match', 'odds-comparison' ); ?>
            </label>
        </p>
        <p>
            <label for="oc_live_match">
                <input type="checkbox" id="oc_live_match" name="oc_live_match" value="1" <?php checked( $is_live, 1 ); ?>>
                <?php esc_html_e( 'Live Match', 'odds-comparison' ); ?>
            </label>
        </p>
        <p>
            <label for="oc_match_importance"><?php esc_html_e( 'Importance Level:', 'odds-comparison' ); ?></label>
            <select id="oc_match_importance" name="oc_match_importance">
                <option value="normal" <?php selected( $importance, 'normal' ); ?>><?php esc_html_e( 'Normal', 'odds-comparison' ); ?></option>
                <option value="high" <?php selected( $importance, 'high' ); ?>><?php esc_html_e( 'High', 'odds-comparison' ); ?></option>
                <option value="top" <?php selected( $importance, 'top' ); ?>><?php esc_html_e( 'Top', 'odds-comparison' ); ?></option>
            </select>
        </p>
    </div>
    <?php
}

/**
 * Operator bonus meta box callback
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Post object
 */
function oc_operator_bonus_meta_box_callback( $post ) {
    wp_nonce_field( 'oc_save_bonus_meta', 'oc_bonus_meta_nonce' );
    
    $bonus_amount = get_post_meta( $post->ID, 'oc_bonus_amount', true );
    $bonus_type = get_post_meta( $post->ID, 'oc_bonus_type', true );
    $bonus_description = get_post_meta( $post->ID, 'oc_bonus_description', true );
    $bonus_code = get_post_meta( $post->ID, 'oc_bonus_code', true );
    $bonus_url = get_post_meta( $post->ID, 'oc_bonus_url', true );
    $min_deposit = get_post_meta( $post->ID, 'oc_min_deposit', true );
    $wagering_requirement = get_post_meta( $post->ID, 'oc_wagering_requirement', true );
    ?>
    <div class="oc-bonus-meta-box">
        <div class="form-row">
            <label for="oc_bonus_amount"><?php esc_html_e( 'Bonus Amount:', 'odds-comparison' ); ?></label>
            <input type="text" id="oc_bonus_amount" name="oc_bonus_amount" value="<?php echo esc_attr( $bonus_amount ); ?>" placeholder="<?php esc_attr_e( 'e.g., 100% up to $200', 'odds-comparison' ); ?>">
        </div>
        <div class="form-row">
            <label for="oc_bonus_type"><?php esc_html_e( 'Bonus Type:', 'odds-comparison' ); ?></label>
            <select id="oc_bonus_type" name="oc_bonus_type">
                <option value="deposit" <?php selected( $bonus_type, 'deposit' ); ?>><?php esc_html_e( 'Deposit Bonus', 'odds-comparison' ); ?></option>
                <option value="freebet" <?php selected( $bonus_type, 'freebet' ); ?>><?php esc_html_e( 'Free Bet', 'odds-comparison' ); ?></option>
                <option value="nodeposit" <?php selected( $bonus_type, 'nodeposit' ); ?>><?php esc_html_e( 'No Deposit Bonus', 'odds-comparison' ); ?></option>
                <option value="reload" <?php selected( $bonus_type, 'reload' ); ?>><?php esc_html_e( 'Reload Bonus', 'odds-comparison' ); ?></option>
                <option value="cashback" <?php selected( $bonus_type, 'cashback' ); ?>><?php esc_html_e( 'Cashback', 'odds-comparison' ); ?></option>
            </select>
        </div>
        <div class="form-row full-width">
            <label for="oc_bonus_description"><?php esc_html_e( 'Bonus Description:', 'odds-comparison' ); ?></label>
            <textarea id="oc_bonus_description" name="oc_bonus_description" rows="3" cols="50"><?php echo esc_textarea( $bonus_description ); ?></textarea>
        </div>
        <div class="form-row">
            <label for="oc_bonus_code"><?php esc_html_e( 'Bonus Code:', 'odds-comparison' ); ?></label>
            <input type="text" id="oc_bonus_code" name="oc_bonus_code" value="<?php echo esc_attr( $bonus_code ); ?>">
        </div>
        <div class="form-row">
            <label for="oc_bonus_url"><?php esc_html_e( 'Bonus URL:', 'odds-comparison' ); ?></label>
            <input type="url" id="oc_bonus_url" name="oc_bonus_url" value="<?php echo esc_url( $bonus_url ); ?>">
        </div>
    <style>
        .oc-bonus-meta-box .form-row { margin-bottom: 15px; }
        .oc-bonus-meta-box .form-row.full-width { width: 100%; }
        .oc-bonus-meta-box label { display: block; font-weight: 500; margin-bottom: 5px; }
        .oc-bonus-meta-box input, .oc-bonus-meta-box select, .oc-bonus-meta-box textarea { width: 100%; max-width: 500px; }
    </style>
    <?php
}

/**
 * Operator pros/cons meta box callback
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Post object
 */
function oc_operator_pros_cons_meta_box_callback( $post ) {
    wp_nonce_field( 'oc_save_pros_cons_meta', 'oc_pros_cons_meta_nonce' );
    
    $pros = get_post_meta( $post->ID, 'oc_operator_pros', true );
    $cons = get_post_meta( $post->ID, 'oc_operator_cons', true );
    
    $pros_list = is_array( $pros ) ? $pros : array();
    $cons_list = is_array( $cons ) ? $cons : array();
    
    while ( count( $pros_list ) < 3 ) { $pros_list[] = ''; }
    while ( count( $cons_list ) < 3 ) { $cons_list[] = ''; }
    ?>
    <div class="oc-pros-cons-meta-box">
        <div class="pros-section">
            <h4><?php esc_html_e( 'Pros', 'odds-comparison' ); ?></h4>
            <?php foreach ( $pros_list as $pro ) : ?>
                <div class="pro-item">
                    <span class="dashicons dashicons-yes-alt" style="color: #27ae60;"></span>
                    <input type="text" name="oc_operator_pros[]" value="<?php echo esc_attr( $pro ); ?>" placeholder="<?php esc_attr_e( 'Enter a pro...', 'odds-comparison' ); ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <div class="cons-section">
            <h4><?php esc_html_e( 'Cons', 'odds-comparison' ); ?></h4>
            <?php foreach ( $cons_list as $con ) : ?>
                <div class="con-item">
                    <span class="dashicons dashicons-dismiss" style="color: #e74c3c;"></span>
                    <input type="text" name="oc_operator_cons[]" value="<?php echo esc_attr( $con ); ?>" placeholder="<?php esc_attr_e( 'Enter a con...', 'odds-comparison' ); ?>">
                </div>
            <?php endforeach; ?>
        </div>
    <style>
        .oc-pros-cons-meta-box { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .oc-pros-cons-meta-box h4 { margin-top: 0; padding-bottom: 10px; border-bottom: 2px solid #eee; }
        .pros-section h4 { border-color: #27ae60; }
        .cons-section h4 { border-color: #e74c3c; }
        .pro-item, .con-item { display: flex; align-items: center; margin-bottom: 10px; }
        .pro-item input, .con-item input { flex: 1; margin-left: 10px; }
    </style>
    <?php
}

/**
 * Operator ranking meta box callback
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Post object
 */
function oc_operator_ranking_meta_box_callback( $post ) {
    wp_nonce_field( 'oc_save_ranking_meta', 'oc_ranking_meta_nonce' );
    
    $rating = get_post_meta( $post->ID, 'oc_operator_rating', true );
    $rating = $rating ? floatval( $rating ) : 0;
    $featured = get_post_meta( $post->ID, 'oc_featured_operator', true );
    ?>
    <div class="oc-ranking-meta-box">
        <div class="rating-section">
            <label><?php esc_html_e( 'Rating:', 'odds-comparison' ); ?></label>
            <div class="rating-stars">
                <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                    <span class="star <?php echo $i <= round( $rating ) ? 'filled' : ''; ?>" data-value="<?php echo esc_attr( $i ); ?>">★</span>
                <?php endfor; ?>
                <input type="hidden" id="oc_operator_rating" name="oc_operator_rating" value="<?php echo esc_attr( $rating ); ?>">
            </div>
            <span class="rating-value"><?php echo esc_html( number_format( $rating, 1 ) ); ?>/5</span>
        </div>
        <p>
            <label for="oc_featured_operator">
                <input type="checkbox" id="oc_featured_operator" name="oc_featured_operator" value="1" <?php checked( $featured, 1 ); ?>>
                <?php esc_html_e( 'Featured Operator', 'odds-comparison' ); ?>
            </label>
        </p>
    </div>
    <style>
        .oc-ranking-meta-box .rating-section { display: flex; align-items: center; margin-bottom: 15px; }
        .oc-ranking-meta-box .rating-stars { margin: 0 10px; }
        .oc-ranking-meta-box .star { font-size: 20px; color: #ddd; cursor: pointer; }
        .oc-ranking-meta-box .star.filled { color: #f1c40f; }
    </style>
    <script>
    jQuery(document).ready(function($) {
        $('.rating-stars .star').on('click', function() {
            var value = $(this).data('value');
            $('#oc_operator_rating').val(value);
            $('.rating-stars .star').each(function() {
                $(this).toggleClass('filled', $(this).data('value') <= value);
            });
            $('.rating-value').text(value + '/5');
        });
    });
    </script>
    <?php
}

/**
 * Save match meta data
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID
 */
function oc_save_match_meta( $post_id ) {
    if ( ! isset( $_POST['oc_highlights_meta_nonce'] ) ) { return $post_id; }
    if ( ! wp_verify_nonce( $_POST['oc_highlights_meta_nonce'], 'oc_save_highlights_meta' ) ) { return $post_id; }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }
    if ( ! current_user_can( 'edit_post', $post_id ) ) { return $post_id; }
    
    if ( isset( $_POST['oc_featured_match'] ) ) {
        update_post_meta( $post_id, 'oc_featured_match', 1 );
    } else {
        delete_post_meta( $post_id, 'oc_featured_match' );
    }
    
    if ( isset( $_POST['oc_live_match'] ) ) {
        update_post_meta( $post_id, 'oc_live_match', 1 );
    } else {
        delete_post_meta( $post_id, 'oc_live_match' );
    }
    
    if ( isset( $_POST['oc_match_importance'] ) ) {
        update_post_meta( $post_id, 'oc_match_importance', sanitize_text_field( $_POST['oc_match_importance'] ) );
    }
}
add_action( 'save_post_match', 'oc_save_match_meta' );

/**
 * Save operator meta data
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID
 */
function oc_save_operator_meta( $post_id ) {
    if ( ! isset( $_POST['oc_bonus_meta_nonce'] ) && ! isset( $_POST['oc_pros_cons_meta_nonce'] ) && ! isset( $_POST['oc_ranking_meta_nonce'] ) ) {
        return $post_id;
    }
    
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }
    if ( ! current_user_can( 'edit_post', $post_id ) ) { return $post_id; }
    
    // Save bonus meta
    if ( isset( $_POST['oc_bonus_amount'] ) ) {
        update_post_meta( $post_id, 'oc_bonus_amount', sanitize_text_field( $_POST['oc_bonus_amount'] ) );
    }
    if ( isset( $_POST['oc_bonus_type'] ) ) {
        update_post_meta( $post_id, 'oc_bonus_type', sanitize_text_field( $_POST['oc_bonus_type'] ) );
    }
    if ( isset( $_POST['oc_bonus_description'] ) ) {
        update_post_meta( $post_id, 'oc_bonus_description', sanitize_textarea_field( $_POST['oc_bonus_description'] ) );
    }
    if ( isset( $_POST['oc_bonus_code'] ) ) {
        update_post_meta( $post_id, 'oc_bonus_code', sanitize_text_field( $_POST['oc_bonus_code'] ) );
    }
    if ( isset( $_POST['oc_bonus_url'] ) ) {
        update_post_meta( $post_id, 'oc_bonus_url', esc_url_raw( $_POST['oc_bonus_url'] ) );
    }
    
    // Save pros and cons
    if ( isset( $_POST['oc_operator_pros'] ) ) {
        $pros = array_filter( array_map( 'sanitize_text_field', $_POST['oc_operator_pros'] ) );
        update_post_meta( $post_id, 'oc_operator_pros', $pros );
    }
    
    if ( isset( $_POST['oc_operator_cons'] ) ) {
        $cons = array_filter( array_map( 'sanitize_text_field', $_POST['oc_operator_cons'] ) );
        update_post_meta( $post_id, 'oc_operator_cons', $cons );
    }
    
    // Save ranking meta
    if ( isset( $_POST['oc_operator_rating'] ) ) {
        update_post_meta( $post_id, 'oc_operator_rating', floatval( $_POST['oc_operator_rating'] ) );
    }
    if ( isset( $_POST['oc_featured_operator'] ) ) {
        update_post_meta( $post_id, 'oc_featured_operator', 1 );
    } else {
        delete_post_meta( $post_id, 'oc_featured_operator' );
    }
}
add_action( 'save_post_operator', 'oc_save_operator_meta' );
