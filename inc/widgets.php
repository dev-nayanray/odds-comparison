<?php
/**
 * Widgets
 *
 * Custom widgets for Odds Comparison plugin.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

/**
 * Best Odds Widget
 *
 * Displays best odds for upcoming matches.
 *
 * @since 1.0.0
 */
class OC_Best_Odds_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct(
            'oc_best_odds',
            __( 'Best Odds', 'odds-comparison' ),
            array(
                'description' => __( 'Displays the best odds for upcoming matches.', 'odds-comparison' ),
                'classname'   => 'oc-widget-best-odds',
            )
        );
    }

    /**
     * Widget display
     *
     * @since 1.0.0
     *
     * @param array $args     Widget arguments
     * @param array $instance Widget instance
     */
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Best Odds', 'odds-comparison' );
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
        
        if ( $title ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
        }
        
        $matches = oc_get_upcoming_matches( absint( $instance['limit'] ) );
        
        if ( empty( $matches ) ) {
            echo '<p class="oc-no-matches">' . esc_html__( 'No upcoming matches.', 'odds-comparison' ) . '</p>';
            echo $args['after_widget'];
            return;
        }
        
        echo '<ul class="oc-best-odds-list">';
        
        foreach ( $matches as $match ) {
            $match_id = $match->ID;
            $odds = oc_get_match_odds( $match_id );
            $best_odds = oc_get_best_odds( $odds );
            
            $home_team = get_post_meta( $match_id, 'oc_home_team', true );
            $away_team = get_post_meta( $match_id, 'oc_away_team', true );
            $match_date = get_post_meta( $match_id, 'oc_match_date', true );
            
            ?>
            <li class="oc-best-odds-item">
                <div class="oc-match-teams-small">
                    <span class="oc-team-small"><?php echo esc_html( $home_team ); ?></span>
                    <span class="oc-vs-small">vs</span>
                    <span class="oc-team-small"><?php echo esc_html( $away_team ); ?></span>
                </div>
                <div class="oc-odds-small">
                    <?php if ( $best_odds['home'] ) : ?>
                        <span class="oc-odd-small" title="<?php esc_attr_e( 'Home', 'odds-comparison' ); ?>">
                            <?php echo esc_html( number_format( $best_odds['home']['odds'], 2 ) ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $best_odds['draw'] ) : ?>
                        <span class="oc-odd-small" title="<?php esc_attr_e( 'Draw', 'odds-comparison' ); ?>">
                            <?php echo esc_html( number_format( $best_odds['draw']['odds'], 2 ) ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $best_odds['away'] ) : ?>
                        <span class="oc-odd-small" title="<?php esc_attr_e( 'Away', 'odds-comparison' ); ?>">
                            <?php echo esc_html( number_format( $best_odds['away']['odds'], 2 ) ); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <a href="<?php echo esc_url( get_permalink( $match_id ) ); ?>" class="oc-more-link">
                    <?php esc_html_e( 'More', 'odds-comparison' ); ?>
                </a>
            </li>
            <?php
        }
        
        echo '</ul>';
        echo $args['after_widget'];
    }

    /**
     * Widget form
     *
     * @since 1.0.0
     *
     * @param array $instance Widget instance
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Best Odds', 'odds-comparison' );
        $limit = ! empty( $instance['limit'] ) ? absint( $instance['limit'] ) : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php esc_html_e( 'Title:', 'odds-comparison' ); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
                   type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
                <?php esc_html_e( 'Number of matches:', 'odds-comparison' ); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" 
                   type="number" min="1" max="10" value="<?php echo esc_attr( $limit ); ?>">
        </p>
        <?php
    }

    /**
     * Widget update
     *
     * @since 1.0.0
     *
     * @param array $new_instance New instance
     * @param array $old_instance Old instance
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        
        $instance['title'] = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['limit'] = ! empty( $new_instance['limit'] ) ? absint( $new_instance['limit'] ) : 5;
        
        return $instance;
    }
}

/**
 * Matches Widget
 *
 * Displays list of upcoming/live matches.
 *
 * @since 1.0.0
 */
class OC_Matches_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct(
            'oc_matches',
            __( 'Matches List', 'odds-comparison' ),
            array(
                'description' => __( 'Displays a list of upcoming and live matches.', 'odds-comparison' ),
                'classname'   => 'oc-widget-matches',
            )
        );
    }

    /**
     * Widget display
     *
     * @since 1.0.0
     *
     * @param array $args     Widget arguments
     * @param array $instance Widget instance
     */
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Upcoming Matches', 'odds-comparison' );
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
        
        if ( $title ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
        }
        
        $query_args = array(
            'post_type'      => 'match',
            'post_status'    => 'publish',
            'posts_per_page' => absint( $instance['limit'] ),
            'orderby'        => 'meta_value',
            'meta_key'       => 'oc_match_date',
            'order'          => 'ASC',
        );
        
        if ( ! empty( $instance['sport'] ) ) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'sport',
                    'field'    => 'slug',
                    'terms'    => $instance['sport'],
                ),
            );
        }
        
        if ( ! empty( $instance['featured'] ) ) {
            $query_args['meta_query'][] = array(
                'key'   => 'oc_featured_match',
                'value' => '1',
            );
        }
        
        $matches = get_posts( $query_args );
        
        if ( empty( $matches ) ) {
            echo '<p class="oc-no-matches">' . esc_html__( 'No matches found.', 'odds-comparison' ) . '</p>';
            echo $args['after_widget'];
            return;
        }
        
        echo '<ul class="oc-matches-widget-list">';
        
        foreach ( $matches as $match ) {
            $match_id = $match->ID;
            $home_team = get_post_meta( $match_id, 'oc_home_team', true );
            $away_team = get_post_meta( $match_id, 'oc_away_team', true );
            $match_date = get_post_meta( $match_id, 'oc_match_date', true );
            $match_time = get_post_meta( $match_id, 'oc_match_time', true );
            $is_live = get_post_meta( $match_id, 'oc_live_match', true );
            
            ?>
            <li class="oc-match-widget-item <?php echo $is_live ? 'oc-live' : ''; ?>">
                <?php if ( $is_live ) : ?>
                    <span class="oc-live-badge"><?php esc_html_e( 'LIVE', 'odds-comparison' ); ?></span>
                <?php endif; ?>
                <div class="oc-match-teams-widget">
                    <span class="oc-team-widget"><?php echo esc_html( $home_team ); ?></span>
                    <span class="oc-vs-widget">vs</span>
                    <span class="oc-team-widget"><?php echo esc_html( $away_team ); ?></span>
                </div>
                <?php if ( $match_date ) : ?>
                    <div class="oc-match-date-widget">
                        <?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $match_date ) ) ); ?>
                        <?php if ( $match_time ) : ?>
                            <?php echo ' ' . esc_html( date_i18n( 'H:i', strtotime( $match_time ) ) ); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <a href="<?php echo esc_url( get_permalink( $match_id ) ); ?>" class="oc-match-link">
                    <?php esc_html_e( 'View Odds', 'odds-comparison' ); ?>
                </a>
            </li>
            <?php
        }
        
        echo '</ul>';
        
        if ( ! empty( $instance['show_archive_link'] ) ) {
            $archive_link = get_post_type_archive_link( 'match' );
            if ( $archive_link ) {
                echo '<a href="' . esc_url( $archive_link ) . '" class="oc-archive-link">';
                esc_html_e( 'View All Matches', 'odds-comparison' );
                echo '</a>';
            }
        }
        
        echo $args['after_widget'];
    }

    /**
     * Widget form
     *
     * @since 1.0.0
     *
     * @param array $instance Widget instance
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Upcoming Matches', 'odds-comparison' );
        $limit = ! empty( $instance['limit'] ) ? absint( $instance['limit'] ) : 5;
        $sport = ! empty( $instance['sport'] ) ? $instance['sport'] : '';
        $show_archive_link = ! empty( $instance['show_archive_link'] ) ? $instance['show_archive_link'] : '';
        
        $sports = get_terms( array( 'taxonomy' => 'sport', 'hide_empty' => true ) );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php esc_html_e( 'Title:', 'odds-comparison' ); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
                   type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
                <?php esc_html_e( 'Number of matches:', 'odds-comparison' ); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" 
                   type="number" min="1" max="20" value="<?php echo esc_attr( $limit ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'sport' ) ); ?>">
                <?php esc_html_e( 'Filter by Sport:', 'odds-comparison' ); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'sport' ) ); ?>" 
                    name="<?php echo esc_attr( $this->get_field_name( 'sport' ) ); ?>">
                <option value=""><?php esc_html_e( 'All Sports', 'odds-comparison' ); ?></option>
                <?php foreach ( $sports as $s ) : ?>
                    <option value="<?php echo esc_attr( $s->slug ); ?>" <?php selected( $sport, $s->slug ); ?>>
                        <?php echo esc_html( $s->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_archive_link' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'show_archive_link' ) ); ?>" 
                   value="1" <?php checked( $show_archive_link, 1 ); ?>>
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_archive_link' ) ); ?>">
                <?php esc_html_e( 'Show archive link', 'odds-comparison' ); ?>
            </label>
        </p>
        <?php
    }

    /**
     * Widget update
     *
     * @since 1.0.0
     *
     * @param array $new_instance New instance
     * @param array $old_instance Old instance
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        
        $instance['title'] = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['limit'] = ! empty( $new_instance['limit'] ) ? absint( $new_instance['limit'] ) : 5;
        $instance['sport'] = ! empty( $new_instance['sport'] ) ? sanitize_text_field( $new_instance['sport'] ) : '';
        $instance['show_archive_link'] = ! empty( $new_instance['show_archive_link'] ) ? '1' : '';
        
        return $instance;
    }
}

/**
 * Operators Widget
 *
 * Displays list of betting operators.
 *
 * @since 1.0.0
 */
class OC_Operators_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct(
            'oc_operators',
            __( 'Betting Operators', 'odds-comparison' ),
            array(
                'description' => __( 'Displays a list of betting operators with ratings.', 'odds-comparison' ),
                'classname'   => 'oc-widget-operators',
            )
        );
    }

    /**
     * Widget display
     *
     * @since 1.0.0
     *
     * @param array $args     Widget arguments
     * @param array $instance Widget instance
     */
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Top Operators', 'odds-comparison' );
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
        
        if ( $title ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
        }
        
        $query_args = array(
            'post_type'      => 'operator',
            'post_status'    => 'publish',
            'posts_per_page' => absint( $instance['limit'] ),
            'meta_key'       => 'oc_operator_rating',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        );
        
        if ( ! empty( $instance['featured'] ) ) {
            $query_args['meta_query'][] = array(
                'key'   => 'oc_featured_operator',
                'value' => '1',
            );
        }
        
        $operators = get_posts( $query_args );
        
        if ( empty( $operators ) ) {
            echo '<p class="oc-no-operators">' . esc_html__( 'No operators found.', 'odds-comparison' ) . '</p>';
            echo $args['after_widget'];
            return;
        }
        
        echo '<ul class="oc-operators-widget-list">';
        
        foreach ( $operators as $operator ) {
            $operator_id = $operator->ID;
            $rating = get_post_meta( $operator_id, 'oc_operator_rating', true );
            $bonus_amount = get_post_meta( $operator_id, 'oc_bonus_amount', true );
            $affiliate_url = get_post_meta( $operator_id, 'oc_affiliate_url', true );
            
            ?>
            <li class="oc-operator-widget-item">
                <div class="oc-operator-widget-logo">
                    <?php if ( has_post_thumbnail( $operator_id ) ) : ?>
                        <?php the_post_thumbnail( 'thumbnail', array( 'alt' => $operator->post_title ) ); ?>
                    <?php else : ?>
                        <span class="oc-logo-text"><?php echo esc_html( $operator->post_title[0] ); ?></span>
                    <?php endif; ?>
                </div>
                <div class="oc-operator-widget-info">
                    <span class="oc-operator-widget-name"><?php echo esc_html( $operator->post_title ); ?></span>
                    <?php if ( $rating ) : ?>
                        <span class="oc-operator-widget-rating">
                            <?php echo esc_html( number_format( $rating, 1 ) ); ?> ★
                        </span>
                    <?php endif; ?>
                    <?php if ( $bonus_amount ) : ?>
                        <span class="oc-operator-widget-bonus"><?php echo esc_html( $bonus_amount ); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ( $affiliate_url ) : ?>
                    <a href="<?php echo esc_url( $affiliate_url ); ?>" class="button oc-visit-btn" target="_blank" rel="nofollow">
                        <?php esc_html_e( 'Visit', 'odds-comparison' ); ?>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url( get_permalink( $operator_id ) ); ?>" class="oc-review-link">
                        <?php esc_html_e( 'Review', 'odds-comparison' ); ?>
                    </a>
                <?php endif; ?>
            </li>
            <?php
        }
        
        echo '</ul>';
        
        if ( ! empty( $instance['show_archive_link'] ) ) {
            $archive_link = get_post_type_archive_link( 'operator' );
            if ( $archive_link ) {
                echo '<a href="' . esc_url( $archive_link ) . '" class="oc-archive-link">';
                esc_html_e( 'View All Operators', 'odds-comparison' );
                echo '</a>';
            }
        }
        
        echo $args['after_widget'];
    }

    /**
     * Widget form
     *
     * @since 1.0.0
     *
     * @param array $instance Widget instance
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Top Operators', 'odds-comparison' );
        $limit = ! empty( $instance['limit'] ) ? absint( $instance['limit'] ) : 4;
        $featured = ! empty( $instance['featured'] ) ? $instance['featured'] : '';
        $show_archive_link = ! empty( $instance['show_archive_link'] ) ? $instance['show_archive_link'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php esc_html_e( 'Title:', 'odds-comparison' ); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
                   type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
                <?php esc_html_e( 'Number of operators:', 'odds-comparison' ); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" 
                   type="number" min="1" max="10" value="<?php echo esc_attr( $limit ); ?>">
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'featured' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'featured' ) ); ?>" 
                   value="1" <?php checked( $featured, 1 ); ?>>
            <label for="<?php echo esc_attr( $this->get_field_id( 'featured' ) ); ?>">
                <?php esc_html_e( 'Featured only', 'odds-comparison' ); ?>
            </label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_archive_link' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'show_archive_link' ) ); ?>" 
                   value="1" <?php checked( $show_archive_link, 1 ); ?>>
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_archive_link' ) ); ?>">
                <?php esc_html_e( 'Show archive link', 'odds-comparison' ); ?>
            </label>
        </p>
        <?php
    }

    /**
     * Widget update
     *
     * @since 1.0.0
     *
     * @param array $new_instance New instance
     * @param array $old_instance Old instance
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        
        $instance['title'] = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['limit'] = ! empty( $new_instance['limit'] ) ? absint( $new_instance['limit'] ) : 4;
        $instance['featured'] = ! empty( $new_instance['featured'] ) ? '1' : '';
        $instance['show_archive_link'] = ! empty( $new_instance['show_archive_link'] ) ? '1' : '';
        
        return $instance;
    }
}

