<?php
/**
 * Matches Custom Post Type
 * 
 * Registers the 'match' custom post type for sports matches.
 * Includes all fields and meta boxes for match data and odds.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register Matches Custom Post Type
 * 
 * @since 1.0.0
 */
function oc_register_match_cpt() {
    $labels = array(
        'name'                  => _x( 'Matches', 'Post type general name', 'odds-comparison' ),
        'singular_name'         => _x( 'Match', 'Post type singular name', 'odds-comparison' ),
        'menu_name'             => _x( 'Matches', 'Admin Menu text', 'odds-comparison' ),
        'name_admin_bar'        => _x( 'Match', 'Add New on Toolbar', 'odds-comparison' ),
        'add_new'               => __( 'Add New', 'odds-comparison' ),
        'add_new_item'          => __( 'Add New Match', 'odds-comparison' ),
        'new_item'              => __( 'New Match', 'odds-comparison' ),
        'edit_item'             => __( 'Edit Match', 'odds-comparison' ),
        'view_item'             => __( 'View Match', 'odds-comparison' ),
        'all_items'             => __( 'All Matches', 'odds-comparison' ),
        'search_items'          => __( 'Search Matches', 'odds-comparison' ),
        'parent_item_colon'     => __( 'Parent Matches:', 'odds-comparison' ),
        'not_found'             => __( 'No matches found.', 'odds-comparison' ),
        'not_found_in_trash'    => __( 'No matches found in Trash.', 'odds-comparison' ),
        'featured_image'        => _x( 'Match Image', 'Overrides the "Featured Image" phrase', 'odds-comparison' ),
        'set_featured_image'    => _x( 'Set match image', 'Overrides the "Set featured image" phrase', 'odds-comparison' ),
        'remove_featured_image' => _x( 'Remove match image', 'Overrides the "Remove featured image" phrase', 'odds-comparison' ),
        'use_featured_image'    => _x( 'Use as match image', 'Overrides the "Use as featured image" phrase', 'odds-comparison' ),
        'archives'              => _x( 'Match archives', 'The post type archive label used in nav menus', 'odds-comparison' ),
        'insert_into_item'      => _x( 'Insert into match', 'Overrides the "Insert into post" phrase', 'odds-comparison' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this match', 'Overrides the "Uploaded to this post" phrase', 'odds-comparison' ),
        'filter_items_list'     => _x( 'Filter matches list', 'Screen reader text for the filter links heading', 'odds-comparison' ),
        'items_list_navigation' => _x( 'Matches list navigation', 'Screen reader text for the pagination heading', 'odds-comparison' ),
        'items_list'            => _x( 'Matches list', 'Screen reader text for the items list heading', 'odds-comparison' ),
    );
    
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array(
            'slug'       => 'match',
            'with_front' => false,
        ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'menu_icon'          => 'dashicons-groups',
        'menu_position'      => 26,
        'supports'           => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'revisions',
            'author',
        ),
        'taxonomies'         => array( 'league', 'sport' ),
        'show_in_rest'       => true,
        'rest_base'          => 'matches',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );
    
    register_post_type( 'match', $args );
}
add_action( 'init', 'oc_register_match_cpt', 0 );

/**
 * Add match meta boxes
 * 
 * @since 1.0.0
 */
function oc_add_match_meta_boxes() {
    add_meta_box(
        'oc_match_details',
        __( 'Match Details', 'odds-comparison' ),
        'oc_render_match_details_meta_box',
        'match',
        'normal',
        'high'
    );
    
    add_meta_box(
        'oc_match_teams',
        __( 'Teams & Score', 'odds-comparison' ),
        'oc_render_match_teams_meta_box',
        'normal',
        'high'
    );
    
    add_meta_box(
        'oc_match_odds_summary',
        __( 'Odds Summary', 'odds-comparison' ),
        'oc_render_match_odds_summary_meta_box',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'oc_add_match_meta_boxes' );

/**
 * Render match details meta box
 * 
 * @since 1.0.0
 * 
 * @param WP_Post $post Current post object
 */
function oc_render_match_details_meta_box( $post ) {
    wp_nonce_field( 'oc_match_meta_box', 'oc_match_nonce' );
    
    // Get existing values
    $match_date   = get_post_meta( $post->ID, 'oc_match_date', true );
    $match_time   = get_post_meta( $post->ID, 'oc_match_time', true );
    $league       = get_post_meta( $post->ID, 'oc_match_league', true );
    $status       = get_post_meta( $post->ID, 'oc_match_status', true );
    $stadium      = get_post_meta( $post->ID, 'oc_match_stadium', true );
    $referee      = get_post_meta( $post->ID, 'oc_match_referee', true );
    
    // Format date for input
    $date_value = '';
    if ( ! empty( $match_date ) ) {
        $date_value = date( 'Y-m-d', strtotime( $match_date ) );
    }
    
    $statuses = array(
        'upcoming'  => __( 'Upcoming', 'odds-comparison' ),
        'live'      => __( 'Live', 'odds-comparison' ),
        'finished'  => __( 'Finished', 'odds-comparison' ),
        'cancelled' => __( 'Cancelled', 'odds-comparison' ),
        'postponed' => __( 'Postponed', 'odds-comparison' ),
    );
    
    ?>
    <div class="oc-meta-box">
        <div class="oc-form-row oc-form-inline">
            <div class="oc-third">
                <label for="oc_match_date"><?php esc_html_e( 'Match Date', 'odds-comparison' ); ?></label>
                <input type="date" id="oc_match_date" name="oc_match_date" 
                       value="<?php echo esc_attr( $date_value ); ?>">
            </div>
            <div class="oc-third">
                <label for="oc_match_time"><?php esc_html_e( 'Match Time', 'odds-comparison' ); ?></label>
                <input type="time" id="oc_match_time" name="oc_match_time" 
                       value="<?php echo esc_attr( $match_time ); ?>">
            </div>
            <div class="oc-third">
                <label for="oc_match_status"><?php esc_html_e( 'Status', 'odds-comparison' ); ?></label>
                <select id="oc_match_status" name="oc_match_status">
                    <?php foreach ( $statuses as $code => $label ) : ?>
                        <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $status, $code ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="oc-form-row">
            <label for="oc_match_league"><?php esc_html_e( 'League/Tournament', 'odds-comparison' ); ?></label>
            <input type="text" id="oc_match_league" name="oc_match_league" 
                   value="<?php echo esc_attr( $league ); ?>" 
                   placeholder="Premier League, La Liga, Champions League...">
            <p class="description"><?php esc_html_e( 'League or tournament name. Also assign via League taxonomy.', 'odds-comparison' ); ?></p>
        </div>
        
        <div class="oc-form-row oc-form-inline">
            <div class="oc-half">
                <label for="oc_match_stadium"><?php esc_html_e( 'Stadium/Venue', 'odds-comparison' ); ?></label>
                <input type="text" id="oc_match_stadium" name="oc_match_stadium" 
                       value="<?php echo esc_attr( $stadium ); ?>" 
                       placeholder="Stadium name, City">
            </div>
            <div class="oc-half">
                <label for="oc_match_referee"><?php esc_html_e( 'Referee', 'odds-comparison' ); ?></label>
                <input type="text" id="oc_match_referee" name="oc_match_referee" 
                       value="<?php echo esc_attr( $referee ); ?>" 
                       placeholder="Main referee name">
            </div>
        </div>
        
        <div class="oc-form-row">
            <label><?php esc_html_e( 'Teams', 'odds-comparison' ); ?></label>
            <p class="description"><?php esc_html_e( 'Assign teams using the Teams taxonomy. Then set home/away below.', 'odds-comparison' ); ?></p>
            <?php
            // Get teams taxonomy terms
            $teams = get_terms( array(
                'taxonomy'   => 'team',
                'hide_empty' => false,
            ) );
            
            $home_team_id = get_post_meta( $post->ID, 'oc_match_home_team', true );
            $away_team_id = get_post_meta( $post->ID, 'oc_match_away_team', true );
            ?>
            <div class="oc-form-inline">
                <div class="oc-half">
                    <label for="oc_match_home_team"><?php esc_html_e( 'Home Team', 'odds-comparison' ); ?></label>
                    <select id="oc_match_home_team" name="oc_match_home_team">
                        <option value=""><?php esc_html_e( 'Select team...', 'odds-comparison' ); ?></option>
                        <?php if ( ! is_wp_error( $teams ) ) : ?>
                            <?php foreach ( $teams as $team ) : ?>
                                <option value="<?php echo esc_attr( $team->term_id ); ?>" <?php selected( $home_team_id, $team->term_id ); ?>>
                                    <?php echo esc_html( $team->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="oc-half">
                    <label for="oc_match_away_team"><?php esc_html_e( 'Away Team', 'odds-comparison' ); ?></label>
                    <select id="oc_match_away_team" name="oc_match_away_team">
                        <option value=""><?php esc_html_e( 'Select team...', 'odds-comparison' ); ?></option>
                        <?php if ( ! is_wp_error( $teams ) ) : ?>
                            <?php foreach ( $teams as $team ) : ?>
                                <option value="<?php echo esc_attr( $team->term_id ); ?>" <?php selected( $away_team_id, $team->term_id ); ?>>
                                    <?php echo esc_html( $team->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .oc-meta-box .oc-form-row { margin-bottom: 15px; }
        .oc-meta-box label { display: block; font-weight: 600; margin-bottom: 5px; }
        .oc-meta-box input[type="text"],
        .oc-meta-box input[type="date"],
        .oc-meta-box input[type="time"],
        .oc-meta-box select { width: 100%; }
        .oc-meta-box .oc-form-inline { display: flex; gap: 20px; }
        .oc-meta-box .oc-half { flex: 1; }
        .oc-meta-box .oc-third { flex: 1; }
        .oc-meta-box .description { font-size: 12px; color: #666; margin-top: 3px; }
    </style>
    <?php
}

/**
 * Render match teams and score meta box
 * 
 * @since 1.0.0
 * 
 * @param WP_Post $post Current post object
 */
function oc_render_match_teams_meta_box( $post ) {
    $home_score      = get_post_meta( $post->ID, 'oc_match_home_score', true );
    $away_score      = get_post_meta( $post->ID, 'oc_match_away_score', true );
    $home_penalties  = get_post_meta( $post->ID, 'oc_match_home_penalties', true );
    $away_penalties  = get_post_meta( $post->ID, 'oc_match_away_penalties', true );
    $home_yellow     = get_post_meta( $post->ID, 'oc_match_home_yellow', true );
    $away_yellow     = get_post_meta( $post->ID, 'oc_match_away_yellow', true );
    $home_red        = get_post_meta( $post->ID, 'oc_match_home_red', true );
    $away_red        = get_post_meta( $post->ID, 'oc_match_away_red', true );
    
    $status = get_post_meta( $post->ID, 'oc_match_status', true );
    ?>
    <div class="oc-meta-box">
        <div class="match-scoreboard">
            <div class="score-team">
                <label><?php esc_html_e( 'Home Score', 'odds-comparison' ); ?></label>
                <input type="number" id="oc_match_home_score" name="oc_match_home_score" 
                       value="<?php echo esc_attr( $home_score ); ?>" min="0" 
                       class="score-input" <?php disabled( $status, 'upcoming' ); ?>>
            </div>
            <div class="score-separator">-</div>
            <div class="score-team">
                <label><?php esc_html_e( 'Away Score', 'odds-comparison' ); ?></label>
                <input type="number" id="oc_match_away_score" name="oc_match_away_score" 
                       value="<?php echo esc_attr( $away_score ); ?>" min="0" 
                       class="score-input" <?php disabled( $status, 'upcoming' ); ?>>
            </div>
        </div>
        
        <div class="extra-time-section">
            <h4><?php esc_html_e( 'Extra Time / Penalties', 'odds-comparison' ); ?></h4>
            <div class="oc-form-inline">
                <div class="oc-half">
                    <label><?php esc_html_e( 'Home Penalties', 'odds-comparison' ); ?></label>
                    <input type="number" name="oc_match_home_penalties" 
                           value="<?php echo esc_attr( $home_penalties ); ?>" min="0">
                </div>
                <div class="oc-half">
                    <label><?php esc_html_e( 'Away Penalties', 'odds-comparison' ); ?></label>
                    <input type="number" name="oc_match_away_penalties" 
                           value="<?php echo esc_attr( $away_penalties ); ?>" min="0">
                </div>
            </div>
        </div>
        
        <div class="cards-section">
            <h4><?php esc_html_e( 'Cards', 'odds-comparison' ); ?></h4>
            <div class="oc-form-inline">
                <div class="oc-quarter">
                    <label><?php esc_html_e( 'Home Yellow', 'odds-comparison' ); ?></label>
                    <input type="number" name="oc_match_home_yellow" 
                           value="<?php echo esc_attr( $home_yellow ); ?>" min="0">
                </div>
                <div class="oc-quarter">
                    <label><?php esc_html_e( 'Away Yellow', 'odds-comparison' ); ?></label>
                    <input type="number" name="oc_match_away_yellow" 
                           value="<?php echo esc_attr( $away_yellow ); ?>" min="0">
                </div>
                <div class="oc-quarter">
                    <label><?php esc_html_e( 'Home Red', 'odds-comparison' ); ?></label>
                    <input type="number" name="oc_match_home_red" 
                           value="<?php echo esc_attr( $home_red ); ?>" min="0">
                </div>
                <div class="oc-quarter">
                    <label><?php esc_html_e( 'Away Red', 'odds-comparison' ); ?></label>
                    <input type="number" name="oc_match_away_red" 
                           value="<?php echo esc_attr( $away_red ); ?>" min="0">
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .match-scoreboard { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 20px;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .score-team { text-align: center; }
        .score-team label { display: block; margin-bottom: 5px; font-weight: 600; }
        .score-input { 
            width: 80px; 
            font-size: 32px; 
            text-align: center; 
            font-weight: 700;
        }
        .score-separator { font-size: 32px; font-weight: 700; }
        .extra-time-section, .cards-section { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e0e0e0; }
        .extra-time-section h4, .cards-section h4 { margin: 0 0 15px 0; font-size: 14px; }
        .oc-meta-box label { font-size: 12px; margin-bottom: 3px; }
        .oc-form-inline { display: flex; gap: 15px; }
        .oc-quarter { flex: 1; }
    </style>
    <?php
}

/**
 * Render match odds summary meta box
 * 
 * @since 1.0.0
 * 
 * @param WP_Post $post Current post object
 */
function oc_render_match_odds_summary_meta_box( $post ) {
    $best_odds = oc_get_best_odds( $post->ID );
    
    ?>
    <div class="oc-meta-box">
        <div class="odds-summary">
            <div class="odds-row">
                <span class="odds-label"><?php esc_html_e( '1', 'odds-comparison' ); ?></span>
                <span class="odds-value"><?php echo $best_odds['home']['odds'] ? esc_html( oc_format_odds( $best_odds['home']['odds'] ) ) : '-'; ?></span>
            </div>
            <div class="odds-row">
                <span class="odds-label"><?php esc_html_e( 'X', 'odds-comparison' ); ?></span>
                <span class="odds-value"><?php echo $best_odds['draw']['odds'] ? esc_html( oc_format_odds( $best_odds['draw']['odds'] ) ) : '-'; ?></span>
            </div>
            <div class="odds-row">
                <span class="odds-label"><?php esc_html_e( '2', 'odds-comparison' ); ?></span>
                <span class="odds-value"><?php echo $best_odds['away']['odds'] ? esc_html( oc_format_odds( $best_odds['away']['odds'] ) ) : '-'; ?></span>
            </div>
        </div>
        <p class="description">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=oc-odds&match_id=' . $post->ID ) ); ?>">
                <?php esc_html_e( 'Manage Odds', 'odds-comparison' ); ?>
            </a>
        </p>
    </div>
    
    <style>
        .odds-summary { padding: 10px 0; }
        .odds-row { 
            display: flex; 
            justify-content: space-between; 
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .odds-row:last-child { border-bottom: none; }
        .odds-label { 
            font-weight: 600; 
            color: #1a5f7a;
            font-size: 14px;
        }
        .odds-value { font-weight: 700; }
        .oc-meta-box .description { font-size: 12px; color: #666; margin-top: 10px; }
    </style>
    <?php
}

/**
 * Save match meta box data
 * 
 * @since 1.0.0
 * 
 * @param int $post_id Post ID
 * @return int Post ID
 */
function oc_save_match_meta_box( $post_id ) {
    // Check if nonce is valid
    if ( ! isset( $_POST['oc_match_nonce'] ) || ! wp_verify_nonce( $_POST['oc_match_nonce'], 'oc_match_meta_box' ) ) {
        return $post_id;
    }
    
    // Check if user has permission
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }
    
    // Save match date
    if ( isset( $_POST['oc_match_date'] ) ) {
        update_post_meta( $post_id, 'oc_match_date', sanitize_text_field( $_POST['oc_match_date'] ) );
    }
    
    // Save match time
    if ( isset( $_POST['oc_match_time'] ) ) {
        update_post_meta( $post_id, 'oc_match_time', sanitize_text_field( $_POST['oc_match_time'] ) );
    }
    
    // Save league
    if ( isset( $_POST['oc_match_league'] ) ) {
        update_post_meta( $post_id, 'oc_match_league', sanitize_text_field( $_POST['oc_match_league'] ) );
    }
    
    // Save status
    if ( isset( $_POST['oc_match_status'] ) ) {
        $status = sanitize_text_field( $_POST['oc_match_status'] );
        update_post_meta( $post_id, 'oc_match_status', $status );
        
        // Update post status based on match status
        if ( 'finished' === $status || 'cancelled' === $status || 'postponed' === $status ) {
            remove_action( 'save_post_match', 'oc_save_match_meta_box' );
            wp_update_post( array(
                'ID'          => $post_id,
                'post_status' => 'draft',
            ) );
            add_action( 'save_post_match', 'oc_save_match_meta_box' );
        }
    }
    
    // Save stadium
    if ( isset( $_POST['oc_match_stadium'] ) ) {
        update_post_meta( $post_id, 'oc_match_stadium', sanitize_text_field( $_POST['oc_match_stadium'] ) );
    }
    
    // Save referee
    if ( isset( $_POST['oc_match_referee'] ) ) {
        update_post_meta( $post_id, 'oc_match_referee', sanitize_text_field( $_POST['oc_match_referee'] ) );
    }
    
    // Save home team
    if ( isset( $_POST['oc_match_home_team'] ) ) {
        update_post_meta( $post_id, 'oc_match_home_team', intval( $_POST['oc_match_home_team'] ) );
    }
    
    // Save away team
    if ( isset( $_POST['oc_match_away_team'] ) ) {
        update_post_meta( $post_id, 'oc_match_away_team', intval( $_POST['oc_match_away_team'] ) );
    }
    
    // Save scores
    if ( isset( $_POST['oc_match_home_score'] ) ) {
        update_post_meta( $post_id, 'oc_match_home_score', intval( $_POST['oc_match_home_score'] ) );
    }
    
    if ( isset( $_POST['oc_match_away_score'] ) ) {
        update_post_meta( $post_id, 'oc_match_away_score', intval( $_POST['oc_match_away_score'] ) );
    }
    
    // Save penalties
    if ( isset( $_POST['oc_match_home_penalties'] ) ) {
        update_post_meta( $post_id, 'oc_match_home_penalties', intval( $_POST['oc_match_home_penalties'] ) );
    }
    
    if ( isset( $_POST['oc_match_away_penalties'] ) ) {
        update_post_meta( $post_id, 'oc_match_away_penalties', intval( $_POST['oc_match_away_penalties'] ) );
    }
    
    // Save cards
    if ( isset( $_POST['oc_match_home_yellow'] ) ) {
        update_post_meta( $post_id, 'oc_match_home_yellow', intval( $_POST['oc_match_home_yellow'] ) );
    }
    
    if ( isset( $_POST['oc_match_away_yellow'] ) ) {
        update_post_meta( $post_id, 'oc_match_away_yellow', intval( $_POST['oc_match_away_yellow'] ) );
    }
    
    if ( isset( $_POST['oc_match_home_red'] ) ) {
        update_post_meta( $post_id, 'oc_match_home_red', intval( $_POST['oc_match_home_red'] ) );
    }
    
    if ( isset( $_POST['oc_match_away_red'] ) ) {
        update_post_meta( $post_id, 'oc_match_away_red', intval( $_POST['oc_match_away_red'] ) );
    }
    
    // Clear odds cache when match is updated
    oc_clear_odds_cache( $post_id );
    
    return $post_id;
}
add_action( 'save_post_match', 'oc_save_match_meta_box' );

/**
 * Auto-update match status based on date
 * 
 * @since 1.0.0
 */
function oc_update_match_status() {
    $matches = get_posts( array(
        'post_type'      => 'match',
        'post_status'    => 'publish',
        'meta_key'       => 'oc_match_date',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => 'oc_match_status',
                'value'   => 'upcoming',
                'compare' => '=',
            ),
            array(
                'key'     => 'oc_match_status',
                'compare' => 'NOT EXISTS',
            ),
        ),
    ) );
    
    $now = current_time( 'mysql' );
    
    foreach ( $matches as $match ) {
        $match_date = get_post_meta( $match->ID, 'oc_match_date', true );
        $match_time = get_post_meta( $match->ID, 'oc_match_time', true );
        
        if ( empty( $match_date ) ) {
            continue;
        }
        
        $match_datetime = $match_date . ' ' . ( $match_time ? $match_time : '00:00:00' );
        
        if ( strtotime( $match_datetime ) < strtotime( $now ) ) {
            update_post_meta( $match->ID, 'oc_match_status', 'finished' );
        }
    }
}
// Run daily via WP Cron
if ( ! wp_next_scheduled( 'oc_daily_match_status_update' ) ) {
    wp_schedule_event( time(), 'daily', 'oc_daily_match_status_update' );
}
add_action( 'oc_daily_match_status_update', 'oc_update_match_status' );

/**
 * Customize matches archive title
 * 
 * @since 1.0.0
 * 
 * @param string $title Archive title
 * @param string $sep   Title separator
 * @return string Modified title
 */
function oc_match_archive_title( $title, $sep = '' ) {
    if ( is_post_type_archive( 'match' ) ) {
        $title = __( 'Sports Matches', 'odds-comparison' ) . " {$sep} " . get_bloginfo( 'name' );
    }
    return $title;
}
add_filter( 'wp_title', 'oc_match_archive_title', 10, 2 );
add_filter( 'document_title_parts', 'oc_match_archive_title' );

/**
 * Add match data to REST API response
 * 
 * @since 1.0.0
 * 
 * @param WP_REST_Response $response Response object
 * @param WP_Post          $post     Post object
 * @return WP_REST_Response Modified response
 */
function oc_add_match_to_rest_response( $response, $post ) {
    if ( 'match' !== $post->post_type ) {
        return $response;
    }
    
    $home_team_id = get_post_meta( $post->ID, 'oc_match_home_team', true );
    $away_team_id = get_post_meta( $post->ID, 'oc_match_away_team', true );
    
    $response->data['match_data'] = array(
        'date'           => get_post_meta( $post->ID, 'oc_match_date', true ),
        'time'           => get_post_meta( $post->ID, 'oc_match_time', true ),
        'league'         => get_post_meta( $post->ID, 'oc_match_league', true ),
        'status'         => get_post_meta( $post->ID, 'oc_match_status', true ),
        'stadium'        => get_post_meta( $post->ID, 'oc_match_stadium', true ),
        'home_team'      => $home_team_id ? get_term( $home_team_id )->name : '',
        'away_team'      => $away_team_id ? get_term( $away_team_id )->name : '',
        'home_score'     => get_post_meta( $post->ID, 'oc_match_home_score', true ),
        'away_score'     => get_post_meta( $post->ID, 'oc_match_away_score', true ),
        'best_odds'      => oc_get_best_odds( $post->ID ),
    );
    
    return $response;
}
add_filter( 'rest_prepare_post', 'oc_add_match_to_rest_response', 10, 2 );

/**
 * Add columns to matches list table
 * 
 * @since 1.0.0
 * 
 * @param array $columns Columns array
 * @return array Modified columns
 */
function oc_match_admin_columns( $columns ) {
    $columns['oc_match_date']  = __( 'Date', 'odds-comparison' );
    $columns['oc_match_league'] = __( 'League', 'odds-comparison' );
    $columns['oc_match_status'] = __( 'Status', 'odds-comparison' );
    $columns['oc_match_odds']   = __( 'Odds', 'odds-comparison' );
    return $columns;
}
add_filter( 'manage_match_posts_columns', 'oc_match_admin_columns' );

/**
 * Render custom column content in matches list
 * 
 * @since 1.0.0
 * 
 * @param string $column_name Column name
 * @param int    $post_id     Post ID
 */
function oc_match_admin_column_content( $column_name, $post_id ) {
    switch ( $column_name ) {
        case 'oc_match_date':
            $date = get_post_meta( $post_id, 'oc_match_date', true );
            $time = get_post_meta( $post_id, 'oc_match_time', true );
            echo $date ? esc_html( $date . ' ' . $time ) : '-';
            break;
            
        case 'oc_match_league':
            echo esc_html( get_post_meta( $post_id, 'oc_match_league', true ) );
            break;
            
        case 'oc_match_status':
            $status = get_post_meta( $post_id, 'oc_match_status', true );
            $statuses = array(
                'upcoming'  => 'upcoming',
                'live'      => 'live',
                'finished'  => 'finished',
                'cancelled' => 'cancelled',
                'postponed' => 'postponed',
            );
            $class = isset( $statuses[ $status ] ) ? $statuses[ $status ] : '';
            echo '<span class="status-badge ' . esc_attr( $class ) . '">' . esc_html( ucfirst( $status ) ) . '</span>';
            break;
            
        case 'oc_match_odds':
            $best = oc_get_best_odds( $post_id );
            $odds = array();
            if ( $best['home']['odds'] ) $odds[] = '1: ' . $best['home']['odds'];
            if ( $best['draw']['odds'] ) $odds[] = 'X: ' . $best['draw']['odds'];
            if ( $best['away']['odds'] ) $odds[] = '2: ' . $best['away']['odds'];
            echo $odds ? esc_html( implode( ', ', $odds ) ) : '<span style="color:#999">No odds</span>';
            break;
    }
}
add_action( 'manage_match_posts_custom_column', 'oc_match_admin_column_content', 10, 2 );

/**
 * Make match columns sortable
 * 
 * @since 1.0.0
 * 
 * @param array $columns Sortable columns
 * @return array Modified columns
 */
function oc_match_admin_sortable_columns( $columns ) {
    $columns['oc_match_date']   = 'oc_match_date';
    $columns['oc_match_status'] = 'oc_match_status';
    return $columns;
}
add_filter( 'manage_edit-match_sortable_columns', 'oc_match_admin_sortable_columns' );

