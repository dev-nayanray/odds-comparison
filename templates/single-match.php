<?php
/**
 * Match Single Template
 *
 * Dynamic template for displaying single match with comprehensive odds comparison.
 * Features: Best odds, full bookmaker comparison, special bets, forecasts.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

$match_id = get_the_ID();
$home_team = get_post_meta( $match_id, 'oc_home_team', true );
$away_team = get_post_meta( $match_id, 'oc_away_team', true );

// If team names are not set in meta, parse from post title
if ( empty( $home_team ) || empty( $away_team ) ) {
    $title = get_the_title();
    $teams = explode( ' vs ', $title );
    if ( count( $teams ) == 2 ) {
        $home_team = trim( $teams[0] );
        $away_team = trim( $teams[1] );
    }
}
$match_date = get_post_meta( $match_id, 'oc_match_date', true );
$match_time = get_post_meta( $match_id, 'oc_match_time', true );
$venue = get_post_meta( $match_id, 'oc_venue', true );
$is_live = get_post_meta( $match_id, 'oc_live_match', true );
$league = get_the_terms( $match_id, 'league' );
$league_name = !empty( $league ) && !is_wp_error( $league ) ? $league[0]->name : '';

// Get team terms for logos
$home_team_term = get_term_by( 'name', $home_team, 'team' );
$away_team_term = get_term_by( 'name', $away_team, 'team' );

// Get team logos
$home_team_logo_id = $home_team_term ? get_term_meta( $home_team_term->term_id, 'oc_team_logo_id', true ) : 0;
$away_team_logo_id = $away_team_term ? get_term_meta( $away_team_term->term_id, 'oc_team_logo_id', true ) : 0;
$home_team_logo_url = $home_team_logo_id ? wp_get_attachment_image_url( $home_team_logo_id, 'medium' ) : '';
$away_team_logo_url = $away_team_logo_id ? wp_get_attachment_image_url( $away_team_logo_id, 'medium' ) : '';

// Get odds data
$odds = oc_get_match_odds( $match_id );
$best_odds = oc_get_best_odds( $odds );

// Organize odds by bookmaker for the comparison table
$bookmaker_odds = array();
if ( ! empty( $odds ) ) {
    foreach ( $odds as $odd ) {
        $bookmaker_id = $odd['bookmaker_id'];
        if ( ! isset( $bookmaker_odds[ $bookmaker_id ] ) ) {
            $bookmaker_odds[ $bookmaker_id ] = array();
        }
        $bookmaker_odds[ $bookmaker_id ][] = $odd;
    }
}

// Get bookmakers for the dropdown
$args = array(
    'post_type' => 'operator',
    'posts_per_page' => -1,
    'post_status' => 'publish',
);
$operators = get_posts( $args );

// Get forecasts/news
$forecast_args = array(
    'post_type' => 'post',
    'posts_per_page' => 5,
    'category_name' => 'forecasts',
    'post_status' => 'publish',
);
$forecasts = get_posts( $forecast_args );
?>

<div class="oc-single-match-page">
    
    <!-- Breadcrumb -->
    <div class="oc-breadcrumb">
        <div class="container">
            <nav class="oc-breadcrumb-nav">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'odds-comparison' ); ?></a>
                <span class="oc-breadcrumb-separator">/</span>
                <?php if ( $league_name ) : ?>
                    <a href="<?php echo esc_url( get_term_link( $league[0] ) ); ?>"><?php echo esc_html( $league_name ); ?></a>
                    <span class="oc-breadcrumb-separator">/</span>
                <?php endif; ?>
                <span class="oc-current"><?php echo esc_html( $home_team . ' ' . esc_html__( 'vs', 'odds-comparison' ) . ' ' . $away_team ); ?></span>
            </nav>
        </div>
    </div>

    <!-- Match Header Section -->
    <section class="oc-match-header-section">
        <div class="container">
            <div class="oc-match-header-modern">
                
                <!-- League Badge -->
                <?php if ( $league_name ) : ?>
                    <div class="oc-league-badge">
                        <span class="oc-league-name"><?php echo esc_html( $league_name ); ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- Live Badge -->
                <?php if ( $is_live ) : ?>
                    <div class="oc-live-indicator">
                        <span class="oc-live-dot"></span>
                        <span><?php esc_html_e( 'LIVE', 'odds-comparison' ); ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- Teams Display -->
                <div class="oc-match-teams-display">
                    <!-- Home Team -->
                    <div class="oc-team-display oc-team-home">
                        <?php if ( $home_team_logo_url ) : ?>
                            <div class="oc-team-emblem">
                                <img src="<?php echo esc_url( $home_team_logo_url ); ?>" alt="<?php echo esc_attr( $home_team ); ?>" />
                            </div>
                        <?php else : ?>
                            <div class="oc-team-emblem oc-emblem-placeholder">
                                <span><?php echo esc_html( substr( $home_team, 0, 3 ) ); ?></span>
                            </div>
                        <?php endif; ?>
                        <h1 class="oc-team-name-display"><?php echo esc_html( $home_team ); ?></h1>
                    </div>
                    
                    <!-- VS / Date -->
                    <div class="oc-match-central">
                        <div class="oc-vs-display"><?php esc_html_e( 'VS', 'odds-comparison' ); ?></div>
                        <div class="oc-match-datetime">
                            <?php if ( $match_date ) : ?>
                                <div class="oc-date"><?php echo esc_html( date_i18n( 'd F Y', strtotime( $match_date ) ) ); ?></div>
                            <?php endif; ?>
                            <?php if ( $match_time ) : ?>
                                <div class="oc-time"><?php echo esc_html( date_i18n( 'H:i', strtotime( $match_time ) ) ); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Away Team -->
                    <div class="oc-team-display oc-team-away">
                        <?php if ( $away_team_logo_url ) : ?>
                            <div class="oc-team-emblem">
                                <img src="<?php echo esc_url( $away_team_logo_url ); ?>" alt="<?php echo esc_attr( $away_team ); ?>" />
                            </div>
                        <?php else : ?>
                            <div class="oc-team-emblem oc-emblem-placeholder">
                                <span><?php echo esc_html( substr( $away_team, 0, 3 ) ); ?></span>
                            </div>
                        <?php endif; ?>
                        <h1 class="oc-team-name-display"><?php echo esc_html( $away_team ); ?></h1>
                    </div>
                </div>
                
                <!-- Venue -->
                <?php if ( $venue ) : ?>
                    <div class="oc-venue-info">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span><?php echo esc_html( $venue ); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Best Odds Section -->
    <section class="oc-best-odds-section">
        <div class="container">
            <div class="oc-best-odds-header">
                <h2><?php esc_html_e( 'BEST ODDS', 'odds-comparison' ); ?></h2>
            </div>
            
            <div class="oc-best-odds-grid">
                <!-- Home Win -->
                <div class="oc-best-odd-card">
                    <div class="oc-odd-selection-header">
                        <span class="oc-selection-name"><?php echo esc_html( $home_team ); ?></span>
                        <span class="oc-selection-type"><?php esc_html_e( 'Winner', 'odds-comparison' ); ?></span>
                    </div>
                    <div class="oc-odd-value-large">
                        <?php echo $best_odds['home']['odds'] > 0 ? esc_html( number_format( $best_odds['home']['odds'] ?? 0, 2 ) ) : '-'; ?>
                    </div>
                    <a href="#odds-comparison" class="oc-compare-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <?php esc_html_e( 'Compare all rates', 'odds-comparison' ); ?>
                    </a>
                </div>
                
                <!-- Draw -->
                <div class="oc-best-odd-card">
                    <div class="oc-odd-selection-header">
                        <span class="oc-selection-name"><?php esc_html_e( 'Draw', 'odds-comparison' ); ?></span>
                        <span class="oc-selection-type"><?php esc_html_e( 'Winner', 'odds-comparison' ); ?></span>
                    </div>
                    <div class="oc-odd-value-large">
                        <?php echo $best_odds['draw']['odds'] > 0 ? esc_html( number_format( $best_odds['draw']['odds'] ?? 0, 2 ) ) : '-'; ?>
                    </div>
                    <a href="#odds-comparison" class="oc-compare-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <?php esc_html_e( 'Compare all rates', 'odds-comparison' ); ?>
                    </a>
                </div>
                
                <!-- Away Win -->
                <div class="oc-best-odd-card">
                    <div class="oc-odd-selection-header">
                        <span class="oc-selection-name"><?php echo esc_html( $away_team ); ?></span>
                        <span class="oc-selection-type"><?php esc_html_e( 'Winner', 'odds-comparison' ); ?></span>
                    </div>
                    <div class="oc-odd-value-large">
                        <?php echo $best_odds['away']['odds'] > 0 ? esc_html( number_format( $best_odds['away']['odds'] ?? 0, 2 ) ) : '-'; ?>
                    </div>
                    <a href="#odds-comparison" class="oc-compare-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <?php esc_html_e( 'Compare all rates', 'odds-comparison' ); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Special Bets Section -->
    <section class="oc-special-bets-section">
        <div class="container">
            <div class="oc-special-bets-grid">
                
                <!-- Asian Handicap -->
                <div class="oc-special-bet-card">
                    <div class="oc-special-bet-header">
                        <h3><?php esc_html_e( 'Asian Handicap', 'odds-comparison' ); ?></h3>
                    </div>
                    <div class="oc-special-bet-content">
                        <div class="oc-handicap-row">
                            <span class="oc-handicap-team"><?php echo esc_html( $away_team ); ?></span>
                            <span class="oc-handicap-value">-0.5</span>
                            <span class="oc-handicap-odds"><?php echo isset( $best_odds['away']['odds'] ) && $best_odds['away']['odds'] > 0 ? esc_html( number_format( $best_odds['away']['odds'] - 0.1, 2 ) ) : '-'; ?></span>
                        </div>
                        <div class="oc-handicap-row">
                            <span class="oc-handicap-team"><?php echo esc_html( $home_team ); ?></span>
                            <span class="oc-handicap-value">+0.5</span>
                            <span class="oc-handicap-odds"><?php echo isset( $best_odds['home']['odds'] ) && $best_odds['home']['odds'] > 0 ? esc_html( number_format( $best_odds['home']['odds'] - 0.1, 2 ) ) : '-'; ?></span>
                        </div>
                    </div>
                    <a href="#special-bets" class="oc-view-more-btn">
                        <?php esc_html_e( 'Show more', 'odds-comparison' ); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </a>
                </div>

                <!-- Over/Under -->
                <div class="oc-special-bet-card">
                    <div class="oc-special-bet-header">
                        <h3><?php esc_html_e( 'Over/Under 2.5', 'odds-comparison' ); ?></h3>
                    </div>
                    <div class="oc-special-bet-content">
                        <div class="oc-overunder-row">
                            <span class="oc-overunder-label"><?php esc_html_e( 'Over 2.5', 'odds-comparison' ); ?></span>
                            <span class="oc-overunder-odds">2.10</span>
                        </div>
                        <div class="oc-overunder-row">
                            <span class="oc-overunder-label"><?php esc_html_e( 'Under 2.5', 'odds-comparison' ); ?></span>
                            <span class="oc-overunder-odds">1.75</span>
                        </div>
                    </div>
                    <a href="#special-bets" class="oc-view-more-btn">
                        <?php esc_html_e( 'Show more', 'odds-comparison' ); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </a>
                </div>

                <!-- Both Teams to Score -->
                <div class="oc-special-bet-card">
                    <div class="oc-special-bet-header">
                        <h3><?php esc_html_e( 'Both Teams Score', 'odds-comparison' ); ?></h3>
                    </div>
                    <div class="oc-special-bet-content">
                        <div class="oc-btts-row">
                            <span class="oc-btts-label"><?php esc_html_e( 'Yes', 'odds-comparison' ); ?></span>
                            <span class="oc-btts-odds">1.85</span>
                        </div>
                        <div class="oc-btts-row">
                            <span class="oc-btts-label"><?php esc_html_e( 'No', 'odds-comparison' ); ?></span>
                            <span class="oc-btts-odds">1.95</span>
                        </div>
                    </div>
                    <a href="#special-bets" class="oc-view-more-btn">
                        <?php esc_html_e( 'Show more', 'odds-comparison' ); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </a>
                </div>

                <!-- Correct Score -->
                <div class="oc-special-bet-card">
                    <div class="oc-special-bet-header">
                        <h3><?php esc_html_e( 'Correct Score', 'odds-comparison' ); ?></h3>
                    </div>
                    <div class="oc-special-bet-content">
                        <div class="oc-correct-score-row">
                            <span class="oc-score">1-0</span>
                            <span class="oc-score-odds">8.5</span>
                        </div>
                        <div class="oc-correct-score-row">
                            <span class="oc-score">1-1</span>
                            <span class="oc-score-odds">6.0</span>
                        </div>
                        <div class="oc-correct-score-row">
                            <span class="oc-score">0-1</span>
                            <span class="oc-score-odds">8.0</span>
                        </div>
                    </div>
                    <a href="#special-bets" class="oc-view-more-btn">
                        <?php esc_html_e( 'Show more', 'odds-comparison' ); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Full Odds Comparison Section -->
    <section id="odds-comparison" class="oc-full-odds-section">
        <div class="container">
            <div class="oc-odds-comparison-wrapper">
                <div class="oc-odds-comparison-header">
                    <h2><?php esc_html_e( 'Full Odds Comparison', 'odds-comparison' ); ?></h2>
                    <div class="oc-odds-tabs">
                        <button class="oc-odds-tab active" data-tab="ganador"><?php esc_html_e( 'Winner', 'odds-comparison' ); ?></button>
                        <button class="oc-odds-tab" data-tab="handicap"><?php esc_html_e( 'Handicap', 'odds-comparison' ); ?></button>
                        <button class="oc-odds-tab" data-tab="masmenos"><?php esc_html_e( 'Over/Under', 'odds-comparison' ); ?></button>
                    </div>
                </div>
                
                <div class="oc-bookmakers-table">
                    <!-- Table Header -->
                    <div class="oc-table-header">
                        <div class="oc-bookmaker-col"><?php esc_html_e( 'Bookmakers', 'odds-comparison' ); ?></div>
                        <div class="oc-odds-col oc-home-col"><?php echo esc_html( $home_team ); ?></div>
                        <div class="oc-odds-col oc-draw-col"><?php esc_html_e( 'Draw', 'odds-comparison' ); ?></div>
                        <div class="oc-odds-col oc-away-col"><?php echo esc_html( $away_team ); ?></div>
                        <div class="oc-action-col"></div>
                    </div>
                    
                    <!-- Bookmaker Rows -->
                    <?php
                    // Get real odds data from database
                    $odds_comparison = oc_get_odds_comparison($match_id);
                    $bookmakers_data = $odds_comparison['bookmakers'];

                    if (!empty($bookmakers_data)) :
                        foreach ($bookmakers_data as $index => $bookmaker) :
                            $is_top = $index < 3;
                            $affiliate_url = $bookmaker['affiliate_url'] ?: '#';
                            $rating = $bookmaker['rating'] ?: 0;
                    ?>
                        <div class="oc-bookmaker-row <?php echo $is_top ? 'oc-top-bookmaker' : ''; ?>">
                            <div class="oc-bookmaker-col">
                                <div class="oc-bookmaker-info">
                                    <span class="oc-bookmaker-rank"><?php echo $index + 1; ?></span>
                                    <div class="oc-bookmaker-logo-text">
                                        <?php if ($bookmaker['logo']) : ?>
                                            <img src="<?php echo esc_url($bookmaker['logo']); ?>" alt="<?php echo esc_attr($bookmaker['name']); ?>" />
                                        <?php else : ?>
                                            <span><?php echo esc_html( substr( $bookmaker['name'], 0, 2 ) ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="oc-bookmaker-details">
                                        <span class="oc-bookmaker-name"><?php echo esc_html($bookmaker['name']); ?></span>
                                        <?php if ($rating > 0) : ?>
                                            <span class="oc-bookmaker-rating">
                                                <?php echo esc_html( number_format( $rating, 1 ) ); ?> ★
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ( $is_top ) : ?>
                                    <div class="oc-top-badge">TOP <?php echo $index + 1; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="oc-odds-col oc-home-col">
                                <?php if ($bookmaker['odds_home'] > 0) : ?>
                                    <button class="oc-odds-btn <?php echo $bookmaker['is_best']['home'] ? 'best-odds' : ''; ?>" data-odds="<?php echo esc_attr( $bookmaker['odds_home'] ); ?>" data-selection="1" data-match-id="<?php echo esc_attr( $match_id ); ?>" data-bookmaker-id="<?php echo esc_attr( $bookmaker['id'] ); ?>" data-bookmaker-name="<?php echo esc_attr( $bookmaker['name'] ); ?>">
                                        <?php echo esc_html( number_format( $bookmaker['odds_home'], 2 ) ); ?>
                                    </button>
                                <?php else : ?>
                                    <span class="oc-no-odds">-</span>
                                <?php endif; ?>
                            </div>
                            <div class="oc-odds-col oc-draw-col">
                                <?php if ($bookmaker['odds_draw'] > 0) : ?>
                                    <button class="oc-odds-btn <?php echo $bookmaker['is_best']['draw'] ? 'best-odds' : ''; ?>" data-odds="<?php echo esc_attr( $bookmaker['odds_draw'] ); ?>" data-selection="X" data-match-id="<?php echo esc_attr( $match_id ); ?>" data-bookmaker-id="<?php echo esc_attr( $bookmaker['id'] ); ?>" data-bookmaker-name="<?php echo esc_attr( $bookmaker['name'] ); ?>">
                                        <?php echo esc_html( number_format( $bookmaker['odds_draw'], 2 ) ); ?>
                                    </button>
                                <?php else : ?>
                                    <span class="oc-no-odds">-</span>
                                <?php endif; ?>
                            </div>
                            <div class="oc-odds-col oc-away-col">
                                <?php if ($bookmaker['odds_away'] > 0) : ?>
                                    <button class="oc-odds-btn <?php echo $bookmaker['is_best']['away'] ? 'best-odds' : ''; ?>" data-odds="<?php echo esc_attr( $bookmaker['odds_away'] ); ?>" data-selection="2" data-match-id="<?php echo esc_attr( $match_id ); ?>" data-bookmaker-id="<?php echo esc_attr( $bookmaker['id'] ); ?>" data-bookmaker-name="<?php echo esc_attr( $bookmaker['name'] ); ?>">
                                        <?php echo esc_html( number_format( $bookmaker['odds_away'], 2 ) ); ?>
                                    </button>
                                <?php else : ?>
                                    <span class="oc-no-odds">-</span>
                                <?php endif; ?>
                            </div>
                            <div class="oc-action-col">
                                <a href="<?php echo esc_url( $affiliate_url ); ?>" class="oc-visit-btn" target="_blank" rel="nofollow">
                                    <?php esc_html_e( 'Visit', 'odds-comparison' ); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php else : ?>
                        <div class="oc-no-odds-row">
                            <div class="oc-no-odds-message" colspan="5">
                                <?php esc_html_e('No odds available for this match yet.', 'odds-comparison'); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="oc-odds-legend">
                    <p><?php esc_html_e( '* Odds may change. Minimum stake and T&Cs apply.', 'odds-comparison' ); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Match Preview / Content -->
    <section class="oc-match-preview-section">
        <div class="container">
            <div class="oc-match-preview-wrapper">
                <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                    <div class="oc-match-content">
                        <h2><?php esc_html_e( 'Match Preview', 'odds-comparison' ); ?></h2>
                        <?php the_content(); ?>
                    </div>
                <?php endwhile; endif; ?>
            </div>
        </div>
    </section>

    <!-- Forecasts Section -->
    <section class="oc-forecasts-section">
        <div class="container">
            <div class="oc-forecasts-header">
                <h2><?php esc_html_e( 'Forecasts & Predictions', 'odds-comparison' ); ?></h2>
                <a href="#" class="oc-view-all-link">
                    <?php esc_html_e( 'See more', 'odds-comparison' ); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>
            
            <div class="oc-forecasts-grid">
                <?php
                // Sample forecast data - in production these would be actual posts
                $sample_forecasts = array(
                    array(
                        'title' => 'Betting and Predictions for Atlético Madrid vs Real Madrid',
                        'league' => 'Spanish Super Cup',
                        'date' => 'January 7, 2026',
                        'time' => '5 minutes ago',
                        'excerpt' => 'Prediction for the Atlético Madrid vs Real Madrid Spanish Super Cup semi-final, to be played this Thursday.',
                    ),
                    array(
                        'title' => 'Arsenal vs Liverpool Betting and Prediction',
                        'league' => 'Premier League',
                        'date' => 'January 7, 2026',
                        'time' => '5 minutes ago',
                        'excerpt' => 'Arsenal vs Liverpool Premier League prediction, the English Premiership, which takes place this Thursday.',
                    ),
                    array(
                        'title' => 'PSG vs Marseille Betting Odds and Predictions',
                        'league' => 'French Super Cup',
                        'date' => 'January 7, 2026',
                        'time' => '5 minutes ago',
                        'excerpt' => 'Prediction for the PSG vs Marseille French Super Cup match, which will be played this Thursday.',
                    ),
                );
                
                foreach ( $sample_forecasts as $forecast ) :
                ?>
                    <article class="oc-forecast-card">
                        <div class="oc-forecast-meta">
                            <span class="oc-forecast-league"><?php echo esc_html( $forecast['league'] ); ?></span>
                            <span class="oc-forecast-date"><?php echo esc_html( $forecast['date'] ); ?></span>
                        </div>
                        <h3 class="oc-forecast-title">
                            <a href="#"><?php echo esc_html( $forecast['title'] ); ?></a>
                        </h3>
                        <p class="oc-forecast-excerpt"><?php echo esc_html( $forecast['excerpt'] ); ?></p>
                        <div class="oc-forecast-footer">
                            <span class="oc-forecast-time"><?php echo esc_html( $forecast['time'] ); ?></span>
                            <span class="oc-forecast-source"><?php esc_html_e( 'OddsChecker', 'odds-comparison' ); ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section class="oc-news-section">
        <div class="container">
            <div class="oc-news-header">
                <h2><?php esc_html_e( 'News', 'odds-comparison' ); ?></h2>
                <a href="#" class="oc-view-all-link">
                    <?php esc_html_e( 'Load more', 'odds-comparison' ); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>
            
            <div class="oc-news-grid">
                <?php
                // Sample news data
                $sample_news = array(
                    array(
                        'title' => 'Real Madrid, Liverpool...? Who is Steve McManaman\'s favorite to win the Champions League?',
                        'author' => 'Steve McManaman',
                        'date' => 'September 12, 2025',
                        'time' => '5 minutes ago',
                    ),
                    array(
                        'title' => 'Oddschecker expands to Chile with the launch of specialized content on Sports Betting Sites',
                        'author' => 'OddsChecker',
                        'date' => 'February 18, 2024',
                        'time' => '11:55 AM',
                    ),
                );
                
                foreach ( $sample_news as $news ) :
                ?>
                    <article class="oc-news-card">
                        <h3 class="oc-news-title">
                            <a href="#"><?php echo esc_html( $news['title'] ); ?></a>
                        </h3>
                        <div class="oc-news-meta">
                            <span class="oc-news-author"><?php echo esc_html( $news['author'] ); ?></span>
                            <span class="oc-news-date"><?php echo esc_html( $news['date'] ); ?></span>
                            <span class="oc-news-time"><?php echo esc_html( $news['time'] ); ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

</div>

<?php get_footer(); ?>

