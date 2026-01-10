<?php
/**
 * Matches Archive Template
 *
 * Template for displaying archive of matches.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

$sport = get_queried_object();
?>

<div class="oc-matches-archive">
    <header class="oc-archive-header">
        <h1 class="oc-archive-title">
            <?php if ( $sport && ! is_wp_error( $sport ) && isset( $sport->taxonomy ) && 'sport' === $sport->taxonomy ) : ?>
                <?php printf( esc_html__( '%s Matches', 'odds-comparison' ), esc_html( $sport->name ) ); ?>
            <?php else : ?>
                <?php esc_html_e( 'All Matches', 'odds-comparison' ); ?>
            <?php endif; ?>
        </h1>
        
        <div class="oc-archive-description">
            <?php if ( $sport && ! is_wp_error( $sport ) ) : ?>
                <?php echo esc_html( $sport->description ); ?>
            <?php else : ?>
                <p><?php esc_html_e( 'Browse upcoming and live matches from various sports.', 'odds-comparison' ); ?></p>
            <?php endif; ?>
        </div>
    </header>
    
    <div class="oc-archive-filters">
        <div class="oc-filter-group">
            <label for="oc-filter-sport"><?php esc_html_e( 'Sport:', 'odds-comparison' ); ?></label>
            <select id="oc-filter-sport" class="oc-select">
                <option value=""><?php esc_html_e( 'All Sports', 'odds-comparison' ); ?></option>
                <?php
                $sports = get_terms( array( 'taxonomy' => 'sport', 'hide_empty' => true ) );
                foreach ( $sports as $s ) :
                ?>
                    <option value="<?php echo esc_attr( $s->slug ); ?>" <?php selected( $sport, $s ); ?>>
                        <?php echo esc_html( $s->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="oc-filter-group">
            <label for="oc-filter-league"><?php esc_html_e( 'League:', 'odds-comparison' ); ?></label>
            <select id="oc-filter-league" class="oc-select">
                <option value=""><?php esc_html_e( 'All Leagues', 'odds-comparison' ); ?></option>
                <?php
                $leagues = get_terms( array( 'taxonomy' => 'league', 'hide_empty' => true ) );
                foreach ( $leagues as $league ) :
                ?>
                    <option value="<?php echo esc_attr( $league->slug ); ?>">
                        <?php echo esc_html( $league->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="oc-filter-group">
            <label for="oc-filter-sort"><?php esc_html_e( 'Sort by:', 'odds-comparison' ); ?></label>
            <select id="oc-filter-sort" class="oc-select">
                <option value="date"><?php esc_html_e( 'Date', 'odds-comparison' ); ?></option>
                <option value="live"><?php esc_html_e( 'Live Now', 'odds-comparison' ); ?></option>
                <option value="featured"><?php esc_html_e( 'Featured', 'odds-comparison' ); ?></option>
            </select>
        </div>
    </div>
    
    <div id="oc-matches-list" class="oc-matches-grid">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <?php
                $match_id = get_the_ID();
                $odds = oc_get_match_odds( $match_id );
                $best_odds = oc_get_best_odds( $odds );
                
                $home_team = get_post_meta( $match_id, 'oc_home_team', true );
                $away_team = get_post_meta( $match_id, 'oc_away_team', true );
                $match_date = get_post_meta( $match_id, 'oc_match_date', true );
                $match_time = get_post_meta( $match_id, 'oc_match_time', true );
                $is_live = get_post_meta( $match_id, 'oc_live_match', true );
                $is_featured = get_post_meta( $match_id, 'oc_featured_match', true );
                ?>
                
                <article class="oc-match-card <?php echo $is_featured ? 'featured' : ''; ?> <?php echo $is_live ? 'live' : ''; ?>">
                    <?php if ( $is_featured ) : ?>
                        <div class="oc-card-badge oc-featured">
                            <?php esc_html_e( 'Featured', 'odds-comparison' ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $is_live ) : ?>
                        <div class="oc-card-badge oc-live">
                            <span class="live-indicator"></span>
                            <?php esc_html_e( 'LIVE', 'odds-comparison' ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="oc-match-teams">
                        <div class="oc-team oc-home">
                            <?php if ( has_post_thumbnail( $match_id ) ) : ?>
                                <div class="oc-team-logo">
                                    <?php echo get_the_post_thumbnail( $match_id, 'thumbnail', array( 'alt' => $home_team ) ); ?>
                                </div>
                            <?php endif; ?>
                            <span class="oc-team-name"><?php echo esc_html( $home_team ); ?></span>
                        </div>
                        
                        <div class="oc-match-vs">
                            <span class="vs"><?php esc_html_e( 'vs', 'odds-comparison' ); ?></span>
                            <?php if ( $match_date ) : ?>
                                <span class="oc-match-date"><?php echo esc_html( date_i18n( 'd.m', strtotime( $match_date ) ) ); ?></span>
                            <?php endif; ?>
                            <?php if ( $match_time ) : ?>
                                <span class="oc-match-time"><?php echo esc_html( date_i18n( 'H:i', strtotime( $match_time ) ) ); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="oc-team oc-away">
                            <?php if ( has_post_thumbnail( $match_id, 'thumbnail' ) ) : ?>
                                <div class="oc-team-logo">
                                    <?php echo get_the_post_thumbnail( $match_id, 'thumbnail', array( 'alt' => $away_team ) ); ?>
                                </div>
                            <?php endif; ?>
                            <span class="oc-team-name"><?php echo esc_html( $away_team ); ?></span>
                        </div>
                    </div>
                    
                    <div class="oc-match-odds-summary">
                        <div class="oc-odds-mini">
                            <span class="oc-label"><?php esc_html_e( '1', 'odds-comparison' ); ?></span>
                            <?php if ( $best_odds['home'] ) : ?>
                                <span class="oc-value"><?php echo esc_html( number_format( $best_odds['home']['odds'], 2 ) ); ?></span>
                            <?php else : ?>
                                <span class="oc-value na">-</span>
                            <?php endif; ?>
                        </div>
                        <div class="oc-odds-mini">
                            <span class="oc-label"><?php esc_html_e( 'X', 'odds-comparison' ); ?></span>
                            <?php if ( $best_odds['draw'] ) : ?>
                                <span class="oc-value"><?php echo esc_html( number_format( $best_odds['draw']['odds'], 2 ) ); ?></span>
                            <?php else : ?>
                                <span class="oc-value na">-</span>
                            <?php endif; ?>
                        </div>
                        <div class="oc-odds-mini">
                            <span class="oc-label"><?php esc_html_e( '2', 'odds-comparison' ); ?></span>
                            <?php if ( $best_odds['away'] ) : ?>
                                <span class="oc-value"><?php echo esc_html( number_format( $best_odds['away']['odds'], 2 ) ); ?></span>
                            <?php else : ?>
                                <span class="oc-value na">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="oc-match-meta">
                        <span class="oc-odds-count">
                            <?php printf( esc_html__( '%d bookmakers', 'odds-comparison' ), count( $odds ) ); ?>
                        </span>
                        <a href="<?php the_permalink(); ?>" class="oc-view-btn">
                            <?php esc_html_e( 'Compare Odds', 'odds-comparison' ); ?>
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
            
            <?php the_posts_navigation(); ?>
        <?php else : ?>
            <div class="oc-no-matches">
                <p><?php esc_html_e( 'No matches found.', 'odds-comparison' ); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>

