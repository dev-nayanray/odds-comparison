<?php
/**
 * Matches Archive Template
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

$queried_object = get_queried_object();
$current_sport  = ( $queried_object instanceof WP_Term && $queried_object->taxonomy === 'sport' )
    ? $queried_object
    : null;

/**
 * Build Query Args
 */
$paged = max( 1, get_query_var( 'paged' ) );

$args = array(
    'post_type'      => 'match',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'orderby'        => 'meta_value',
    'meta_key'       => 'oc_match_date',
    'order'          => 'ASC',
);

/**
 * Sport taxonomy filter (if on sport archive)
 */
if ( $current_sport ) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'sport',
            'field'    => 'term_id',
            'terms'    => $current_sport->term_id,
        ),
    );
}

$matches_query = new WP_Query( $args );
?>

<div class="oc-matches-archive">

    <!-- Archive Header -->
    <header class="oc-archive-header">
        <h1 class="oc-archive-title">
            <?php
            echo $current_sport
                ? esc_html( $current_sport->name ) . ' ' . esc_html__( 'Matches', 'odds-comparison' )
                : esc_html__( 'All Matches', 'odds-comparison' );
            ?>
        </h1>

        <p class="oc-archive-description">
            <?php
            echo ( $current_sport && $current_sport->description )
                ? esc_html( $current_sport->description )
                : esc_html__( 'Browse upcoming and live matches from various sports.', 'odds-comparison' );
            ?>
        </p>
    </header>

    <!-- Filters -->
    <div class="oc-archive-filters">

        <!-- Sport Filter -->
        <div class="oc-filter-group">
            <label><?php esc_html_e( 'Sport:', 'odds-comparison' ); ?></label>
            <select id="oc-filter-sport" class="oc-select">
                <option value=""><?php esc_html_e( 'All Sports', 'odds-comparison' ); ?></option>
                <?php
                $sports = get_terms( array(
                    'taxonomy'   => 'sport',
                    'hide_empty' => true,
                ) );

                foreach ( $sports as $sport ) :
                ?>
                    <option value="<?php echo esc_attr( get_term_link( $sport ) ); ?>"
                        <?php selected( $current_sport && $sport->term_id === $current_sport->term_id ); ?>>
                        <?php echo esc_html( $sport->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- League Filter (UI only – AJAX later) -->
        <div class="oc-filter-group">
            <label><?php esc_html_e( 'League:', 'odds-comparison' ); ?></label>
            <select class="oc-select">
                <option><?php esc_html_e( 'All Leagues', 'odds-comparison' ); ?></option>
                <?php
                $leagues = get_terms( array(
                    'taxonomy'   => 'league',
                    'hide_empty' => true,
                ) );

                foreach ( $leagues as $league ) :
                    echo '<option>' . esc_html( $league->name ) . '</option>';
                endforeach;
                ?>
            </select>
        </div>

    </div>

    <!-- Matches Grid -->
    <div id="oc-matches-list" class="oc-matches-grid">

        <?php if ( $matches_query->have_posts() ) : ?>

            <?php while ( $matches_query->have_posts() ) : $matches_query->the_post();

                $match_id    = get_the_ID();
                $odds        = oc_get_match_odds( $match_id ) ?: array();
                $best_odds   = oc_get_best_odds( $odds ) ?: array();

                $home_team   = get_post_meta( $match_id, 'oc_home_team', true );
                $away_team   = get_post_meta( $match_id, 'oc_away_team', true );
                $match_date  = get_post_meta( $match_id, 'oc_match_date', true );
                $match_time  = get_post_meta( $match_id, 'oc_match_time', true );
                $is_live     = (bool) get_post_meta( $match_id, 'oc_live_match', true );
                $is_featured = (bool) get_post_meta( $match_id, 'oc_featured_match', true );
            ?>

            <article class="oc-match-card<?php echo $is_live ? ' live' : ''; echo $is_featured ? ' featured' : ''; ?>">

                <?php if ( $is_featured ) : ?>
                    <span class="oc-badge oc-featured"><?php esc_html_e( 'Featured', 'odds-comparison' ); ?></span>
                <?php endif; ?>

                <?php if ( $is_live ) : ?>
                    <span class="oc-badge oc-live"><?php esc_html_e( 'LIVE', 'odds-comparison' ); ?></span>
                <?php endif; ?>

                <div class="oc-match-teams">
                    <span><?php echo esc_html( $home_team ); ?></span>
                    <span class="oc-vs">vs</span>
                    <span><?php echo esc_html( $away_team ); ?></span>
                </div>

                <div class="oc-match-datetime">
                    <?php echo esc_html( date_i18n( 'd M H:i', strtotime( $match_date . ' ' . $match_time ) ) ); ?>
                </div>

                <div class="oc-odds-summary">
                    <?php foreach ( array( 'home' => '1', 'draw' => 'X', 'away' => '2' ) as $key => $label ) : ?>
                        <button class="oc-odds-btn" data-odds="<?php echo esc_attr( isset( $best_odds[ $key ]['odds'] ) ? $best_odds[ $key ]['odds'] : 0 ); ?>" data-selection="<?php echo esc_attr( $label ); ?>" data-match-id="<?php echo esc_attr( $match_id ); ?>" data-bookmaker-id="<?php echo esc_attr( isset( $best_odds[ $key ]['bookmaker_id'] ) ? $best_odds[ $key ]['bookmaker_id'] : 1 ); ?>" data-bookmaker-name="<?php echo esc_attr( isset( $best_odds[ $key ]['bookmaker_name'] ) ? $best_odds[ $key ]['bookmaker_name'] : 'Unknown' ); ?>">
                            <span><?php echo esc_html( $label ); ?></span>
                            <strong>
                                <?php
                                echo isset( $best_odds[ $key ]['odds'] )
                                    ? esc_html( number_format( $best_odds[ $key ]['odds'], 2 ) )
                                    : '-';
                                ?>
                            </strong>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="oc-match-footer">
                    <span><?php echo count( $odds ); ?> bookmakers</span>
                    <a href="<?php the_permalink(); ?>" class="oc-view-btn">
                        <?php esc_html_e( 'Compare Odds', 'odds-comparison' ); ?>
                    </a>
                </div>

            </article>

            <?php endwhile; ?>

            <!-- Pagination -->
            <?php
            echo paginate_links( array(
                'total' => $matches_query->max_num_pages,
            ) );
            ?>

        <?php else : ?>

            <p class="oc-no-matches"><?php esc_html_e( 'No matches found.', 'odds-comparison' ); ?></p>

        <?php endif; wp_reset_postdata(); ?>

    </div>
</div>

<?php get_footer(); ?>
