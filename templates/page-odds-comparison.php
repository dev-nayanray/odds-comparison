<?php
/**
 * Odds Comparison Page Template
 * 
 * Template for displaying the odds comparison page with live odds from different bookmakers.
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

// Get current date for filtering
$today = date( 'Y-m-d' );
$selected_date = isset( $_GET['date'] ) ? sanitize_text_field( $_GET['date'] ) : $today;
$selected_sport = isset( $_GET['sport'] ) ? sanitize_text_field( $_GET['sport'] ) : '';

// Get upcoming matches for the selected date
$args = array(
    'post_type'      => 'match',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_key'       => 'oc_match_date',
    'meta_value'     => $selected_date,
    'meta_compare'   => '=',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
);

// Filter by sport if selected
if ( $selected_sport ) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'sport',
            'field'    => 'slug',
            'terms'    => $selected_sport,
        ),
    );
}

$matches_query = new WP_Query( $args );
$matches = $matches_query->posts;

// Get all sports for filter dropdown
$sports = get_terms( array(
    'taxonomy'   => 'sport',
    'hide_empty' => true,
) );

// Get all operators (bookmakers)
$operators = get_posts( array(
    'post_type'      => 'operator',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
) );

// Get odds for all matches from database
global $wpdb;
$table_name = $wpdb->prefix . 'oc_match_odds';

$odds_data = array();
if ( ! empty( $matches ) ) {
    $match_ids = wp_list_pluck( $matches, 'ID' );
    $placeholders = implode( ',', array_fill( 0, count( $match_ids ), '%d' ) );
    
    $odds_results = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE match_id IN ($placeholders)",
        $match_ids
    ) );
    
    foreach ( $odds_results as $odd ) {
        $odds_data[ $odd->match_id ][ $odd->bookmaker_id ] = array(
            'home' => $odd->odds_home,
            'draw' => $odd->odds_draw,
            'away' => $odd->odds_away,
        );
    }
}

// Get dates with matches for the next 7 days
$upcoming_dates = array();
for ( $i = 0; $i < 14; $i++ ) {
    $date = date( 'Y-m-d', strtotime( "+$i days" ) );
    $date_args = array(
        'post_type'      => 'match',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'meta_key'       => 'oc_match_date',
        'meta_value'     => $date,
        'meta_compare'   => '=',
    );
    $date_query = new WP_Query( $date_args );
    if ( $date_query->have_posts() ) {
        $upcoming_dates[] = $date;
    }
    wp_reset_postdata();
}

?>

<div class="odds-comparison-page">
    <!-- Page Header -->
    <section class="oc-page-header">
        <div class="container">
            <h1><?php esc_html_e( 'Odds Comparison', 'odds-comparison' ); ?></h1>
            <p><?php esc_html_e( 'Compare the best betting odds from top bookmakers for upcoming matches', 'odds-comparison' ); ?></p>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="oc-filters">
        <div class="container">
            <div class="oc-filters-inner">
                <!-- Date Navigation -->
                <div class="oc-date-filter">
                    <span class="filter-label"><?php esc_html_e( 'Select Date:', 'odds-comparison' ); ?></span>
                    <div class="oc-date-nav">
                        <?php if ( ! empty( $upcoming_dates ) ) : ?>
                            <?php foreach ( $upcoming_dates as $date ) : ?>
                                <?php
                                $date_formatted = date_i18n( 'D, d M', strtotime( $date ) );
                                $is_selected = ( $date === $selected_date );
                                $date_link = add_query_arg( 'date', $date );
                                if ( $selected_sport ) {
                                    $date_link = add_query_arg( 'sport', $selected_sport, $date_link );
                                }
                                ?>
                                <a href="<?php echo esc_url( $date_link ); ?>" 
                                   class="oc-date-btn <?php echo $is_selected ? 'active' : ''; ?>">
                                    <?php echo esc_html( $date_formatted ); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <span class="no-matches"><?php esc_html_e( 'No matches found for upcoming days', 'odds-comparison' ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sport Filter -->
                <div class="oc-sport-filter">
                    <form method="GET" action="" class="oc-sport-form">
                        <input type="hidden" name="date" value="<?php echo esc_attr( $selected_date ); ?>">
                        <label for="sport-select"><?php esc_html_e( 'Sport:', 'odds-comparison' ); ?></label>
                        <select name="sport" id="sport-select" onchange="this.form.submit()">
                            <option value=""><?php esc_html_e( 'All Sports', 'odds-comparison' ); ?></option>
                            <?php if ( ! is_wp_error( $sports ) ) : ?>
                                <?php foreach ( $sports as $sport ) : ?>
                                    <option value="<?php echo esc_attr( $sport->slug ); ?>" <?php selected( $selected_sport, $sport->slug ); ?>>
                                        <?php echo esc_html( $sport->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="oc-main-content">
        <div class="container">
            <?php if ( ! empty( $matches ) ) : ?>
                <div class="oc-matches-list">
                    <?php foreach ( $matches as $match ) : ?>
                        <?php
                        $match_id = $match->ID;
                        $match_date = get_post_meta( $match_id, 'oc_match_date', true );
                        $match_time = get_post_meta( $match_id, 'oc_match_time', true );
                        $match_status = get_post_meta( $match_id, 'oc_match_status', true );
                        $match_league = get_post_meta( $match_id, 'oc_match_league', true );
                        $match_stadium = get_post_meta( $match_id, 'oc_match_stadium', true );
                        
                        // Get teams
                        $teams = wp_get_object_terms( $match_id, 'team' );
                        $home_team = ! empty( $teams[0] ) ? $teams[0] : null;
                        $away_team = ! empty( $teams[1] ) ? $teams[1] : ( ! empty( $teams[0] ) ? $teams[0] : null );
                        
                        // Get odds for this match
                        $match_odds = isset( $odds_data[ $match_id ] ) ? $odds_data[ $match_id ] : array();
                        ?>
                        
                        <article class="oc-match-card" id="match-<?php echo esc_attr( $match_id ); ?>">
                            <!-- Match Header -->
                            <div class="oc-match-header">
                                <div class="oc-match-info">
                                    <span class="oc-match-league"><?php echo esc_html( $match_league ); ?></span>
                                    <span class="oc-match-datetime">
                                        <?php echo esc_html( date_i18n( 'D, d M', strtotime( $match_date ) ) ); ?>
                                        <?php if ( $match_time ) : ?>
                                            - <?php echo esc_html( date_i18n( 'H:i', strtotime( $match_time ) ) ); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <span class="oc-match-status <?php echo esc_attr( $match_status ); ?>">
                                    <?php echo esc_html( ucfirst( $match_status ) ); ?>
                                </span>
                            </div>

                            <!-- Teams -->
                            <div class="oc-match-teams">
                                <div class="oc-team oc-team-home">
                                    <?php if ( $home_team ) : ?>
                                        <span class="oc-team-name"><?php echo esc_html( $home_team->name ); ?></span>
                                    <?php else : ?>
                                        <span class="oc-team-name"><?php esc_html_e( 'Home Team', 'odds-comparison' ); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="oc-match-vs">
                                    <span><?php esc_html_e( 'VS', 'odds-comparison' ); ?></span>
                                </div>
                                <div class="oc-team oc-team-away">
                                    <?php if ( $away_team && $away_team !== $home_team ) : ?>
                                        <span class="oc-team-name"><?php echo esc_html( $away_team->name ); ?></span>
                                    <?php else : ?>
                                        <span class="oc-team-name"><?php esc_html_e( 'Away Team', 'odds-comparison' ); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Odds Table -->
                            <div class="oc-odds-table-wrapper">
                                <table class="oc-odds-table">
                                    <thead>
                                        <tr>
                                            <th class="oc-bookmaker-col"><?php esc_html_e( 'Bookmaker', 'odds-comparison' ); ?></th>
                                            <th class="oc-odds-col"><?php esc_html_e( '1', 'odds-comparison' ); ?></th>
                                            <th class="oc-odds-col"><?php esc_html_e( 'X', 'odds-comparison' ); ?></th>
                                            <th class="oc-odds-col"><?php esc_html_e( '2', 'odds-comparison' ); ?></th>
                                            <th class="oc-action-col"><?php esc_html_e( 'Bet', 'odds-comparison' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ( ! empty( $match_odds ) && ! empty( $operators ) ) : ?>
                                            <?php foreach ( $operators as $operator ) : ?>
                                                <?php
                                                $operator_id = $operator->ID;
                                                $has_odds = isset( $match_odds[ $operator_id ] );
                                                $odds = $has_odds ? $match_odds[ $operator_id ] : null;
                                                
                                                $affiliate_url = get_post_meta( $operator_id, 'oc_operator_affiliate_url', true );
                                                $rating = get_post_meta( $operator_id, 'oc_operator_rating', true );
                                                ?>
                                                <tr class="oc-odds-row <?php echo $has_odds ? '' : 'no-odds'; ?>">
                                                    <td class="oc-bookmaker-cell">
                                                        <div class="oc-bookmaker-info">
                                                            <span class="oc-bookmaker-name"><?php echo esc_html( get_the_title( $operator_id ) ); ?></span>
                                                            <?php if ( $rating ) : ?>
                                                                <span class="oc-bookmaker-rating">★ <?php echo esc_html( number_format( $rating, 1 ) ); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="oc-odds-cell">
                                                        <?php if ( $odds && $odds['home'] > 0 ) : ?>
                                                            <button class="oc-odds-btn" data-type="home" data-odds="<?php echo esc_attr( $odds['home'] ); ?>" data-bookmaker="<?php echo esc_attr( $operator_id ); ?>" data-match="<?php echo esc_attr( $match_id ); ?>">
                                                                <?php echo esc_html( $odds['home'] ); ?>
                                                            </button>
                                                        <?php else : ?>
                                                            <span class="oc-no-odds">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="oc-odds-cell">
                                                        <?php if ( $odds && $odds['draw'] > 0 ) : ?>
                                                            <button class="oc-odds-btn" data-type="draw" data-odds="<?php echo esc_attr( $odds['draw'] ); ?>" data-bookmaker="<?php echo esc_attr( $operator_id ); ?>" data-match="<?php echo esc_attr( $match_id ); ?>">
                                                                <?php echo esc_html( $odds['draw'] ); ?>
                                                            </button>
                                                        <?php else : ?>
                                                            <span class="oc-no-odds">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="oc-odds-cell">
                                                        <?php if ( $odds && $odds['away'] > 0 ) : ?>
                                                            <button class="oc-odds-btn" data-type="away" data-odds="<?php echo esc_attr( $odds['away'] ); ?>" data-bookmaker="<?php echo esc_attr( $operator_id ); ?>" data-match="<?php echo esc_attr( $match_id ); ?>">
                                                                <?php echo esc_html( $odds['away'] ); ?>
                                                            </button>
                                                        <?php else : ?>
                                                            <span class="oc-no-odds">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="oc-action-cell">
                                                        <?php if ( $affiliate_url && $has_odds ) : ?>
                                                            <a href="<?php echo esc_url( $affiliate_url ); ?>" class="oc-bet-btn" target="_blank" rel="nofollow">
                                                                <?php esc_html_e( 'Bet Now', 'odds-comparison' ); ?>
                                                            </a>
                                                        <?php else : ?>
                                                            <span class="oc-no-bet">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="5" class="no-odds-message">
                                                    <?php esc_html_e( 'No odds available for this match yet.', 'odds-comparison' ); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination / Load More -->
                <?php if ( $matches_query->max_num_pages > 1 ) : ?>
                    <div class="oc-pagination">
                        <?php
                        echo paginate_links( array(
                            'base'    => add_query_arg( 'paged', '%#%' ),
                            'format'  => '?paged=%#%',
                            'current' => max( 1, get_query_var( 'paged' ) ),
                            'total'   => $matches_query->max_num_pages,
                            'prev_text' => __( '&laquo; Previous', 'odds-comparison' ),
                            'next_text' => __( 'Next &raquo;', 'odds-comparison' ),
                        ) );
                        ?>
                    </div>
                <?php endif; ?>
                
            <?php else : ?>
                <!-- No Matches Found -->
                <div class="oc-no-matches">
                    <div class="oc-no-matches-content">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <h2><?php esc_html_e( 'No Matches Found', 'odds-comparison' ); ?></h2>
                        <p><?php esc_html_e( 'There are no matches scheduled for the selected date. Please choose a different date.', 'odds-comparison' ); ?></p>
                        
                        <!-- Date Quick Links -->
                        <div class="oc-quick-dates">
                            <h3><?php esc_html_e( 'Popular Upcoming Matches', 'odds-comparison' ); ?></h3>
                            <div class="oc-quick-dates-list">
                                <?php if ( ! empty( $upcoming_dates ) ) : ?>
                                    <?php foreach ( array_slice( $upcoming_dates, 0, 5 ) as $date ) : ?>
                                        <a href="<?php echo esc_url( add_query_arg( 'date', $date ) ); ?>" class="oc-quick-date-btn">
                                            <?php echo esc_html( date_i18n( 'D, d M', strtotime( $date ) ) ); ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <p><?php esc_html_e( 'Check back soon for upcoming matches!', 'odds-comparison' ); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Info Section -->
    <section class="oc-info-section">
        <div class="container">
            <div class="oc-info-grid">
                <div class="oc-info-card">
                    <div class="oc-info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                    </div>
                    <h3><?php esc_html_e( 'Best Odds', 'odds-comparison' ); ?></h3>
                    <p><?php esc_html_e( 'We compare odds from all major bookmakers to find you the best value bets.', 'odds-comparison' ); ?></p>
                </div>
                <div class="oc-info-card">
                    <div class="oc-info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3><?php esc_html_e( 'Trusted Bookmakers', 'odds-comparison' ); ?></h3>
                    <p><?php esc_html_e( 'Only licensed and regulated sportsbooks are included in our comparisons.', 'odds-comparison' ); ?></p>
                </div>
                <div class="oc-info-card">
                    <div class="oc-info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <h3><?php esc_html_e( 'Live Updates', 'odds-comparison' ); ?></h3>
                    <p><?php esc_html_e( 'Odds are updated in real-time as bookmakers adjust their prices.', 'odds-comparison' ); ?></p>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
jQuery(document).ready(function($) {
    // Odds button click handler
    $('.oc-odds-btn').on('click', function() {
        var btn = $(this);
        var odds = btn.data('odds');
        var type = btn.data('type');
        var bookmaker = btn.data('bookmaker');
        var match = btn.data('match');

        // Get match details
        var matchCard = btn.closest('.oc-match-card');
        var homeTeam = matchCard.find('.oc-team-home .oc-team-name').text().trim();
        var awayTeam = matchCard.find('.oc-team-away .oc-team-name').text().trim();
        var matchName = homeTeam + ' vs ' + awayTeam;

        // Get bookmaker name
        var bookmakerName = matchCard.find('.oc-bookmaker-cell .oc-bookmaker-name').text().trim();

        // Toggle selection
        if (btn.hasClass('selected')) {
            // Remove from coupon
            if (typeof removeFromCoupon === 'function') {
                removeFromCoupon(match, type);
            }
            btn.removeClass('selected');
        } else {
            // Remove selection from other buttons in same match
            matchCard.find('.oc-odds-btn').removeClass('selected');

            // Add to coupon
            if (typeof addToCoupon === 'function') {
                addToCoupon({
                    matchId: match,
                    matchName: matchName,
                    selection: type,
                    odds: odds,
                    bookmakerId: bookmaker,
                    bookmakerName: bookmakerName
                });
            }
            btn.addClass('selected');
        }

        // Update coupon UI
        if (typeof updateCouponUI === 'function') {
            updateCouponUI();
        }
    });

    // Date button click handler
    $('.oc-date-btn').on('click', function() {
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
    });
});
</script>

<?php
wp_reset_postdata();
get_footer();

