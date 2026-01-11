<?php
/**
 * Homepage Template
 * 
 * The main homepage template with 2-column layout:
 * - Left: Banner slider, live matches, league matches
 * - Right: Full-height sidebar
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 * @template Home
 */

get_header(); ?>

<div class="oc-homepage-layout">
    <!-- Main Content Area -->
    <main class="oc-main-content">
        
        <!-- Banner Slider -->
        <section class="oc-banner-section">
            <div class="oc-banner-slider">
                <div class="oc-banner-slide active">
                    <div class="oc-banner-content">
                        <span class="oc-banner-badge"><?php esc_html_e( 'Nuevo', 'odds-comparison' ); ?></span>
                        <h2><?php esc_html_e( 'Compara las Mejores Cuotas', 'odds-comparison' ); ?></h2>
                        <p><?php esc_html_e( 'Encuentra las cuotas más altas de las casas de apuestas confiables.', 'odds-comparison' ); ?></p>
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'operator' ) ); ?>" class="oc-btn oc-btn-primary">
                            <?php esc_html_e( 'Ver Casas de Apuestas', 'odds-comparison' ); ?>
                        </a>
                    </div>
                    <div class="oc-banner-image">
                        <div class="oc-banner-placeholder">
                            <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <path d="M4 11a9 9 0 0 1 9 9"></path>
                                <path d="M4 4a16 16 0 0 1 16 16"></path>
                                <circle cx="5" cy="19" r="1"></circle>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Slider Controls -->
            <div class="oc-slider-controls">
                <button class="oc-slider-dot active" data-slide="0"></button>
                <button class="oc-slider-dot" data-slide="1"></button>
                <button class="oc-slider-dot" data-slide="2"></button>
            </div>
        </section>
        
        <!-- Live Matches Section -->
        <section class="oc-section oc-live-section">
            <div class="oc-section-header">
                <h2><?php esc_html_e( 'HOY', 'odds-comparison' ); ?></h2>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'match' ) ); ?>" class="oc-view-all-link">
                    <?php esc_html_e( 'Ver todos los partidos', 'odds-comparison' ); ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="oc-matches-cards">
                <?php
                $live_matches = oc_get_live_matches( 2 ); // Get 2 live matches for homepage
                if ( ! empty( $live_matches ) ) :
                    foreach ( $live_matches as $match ) :
                        $match_id = $match['ID'];
                        $home_team_name = $match['home_team'];
                        $away_team_name = $match['away_team'];
                        $match_time = $match['match_time'];
                        $best_odds = oc_get_best_odds( $match_id );

                        // Get team logos from taxonomy
                        $home_team_logo = '';
                        $away_team_logo = '';
                        if ( ! empty( $match['teams'] ) ) {
                            foreach ( $match['teams'] as $team ) {
                                $logo = get_term_meta( $team->term_id, 'oc_team_logo', true );
                                if ( $team->name === $home_team_name && $logo ) {
                                    $home_team_logo = $logo;
                                } elseif ( $team->name === $away_team_name && $logo ) {
                                    $away_team_logo = $logo;
                                }
                            }
                        }

                        // Fallback to default logos if not found
                        if ( empty( $home_team_logo ) ) {
                            $home_team_logo = OC_ASSETS_URI . '/images/teams/default.png';
                        }
                        if ( empty( $away_team_logo ) ) {
                            $away_team_logo = OC_ASSETS_URI . '/images/teams/default.png';
                        }
                        ?>
                        <article class="oc-match-card-horizontal">
                            <div class="oc-match-time-badge">
                                <span class="oc-match-time"><?php echo $match_time ? esc_html( date_i18n( 'H:i', strtotime( $match_time ) ) ) : 'TBD'; ?></span>
                            </div>

                            <div class="oc-match-teams-compact">
                                <div class="oc-team-compact oc-team-home">
                                    <div class="oc-team-logo-wrapper">
                                        <img src="<?php echo esc_url( $home_team_logo ); ?>" alt="<?php echo esc_attr( $home_team_name ); ?>" class="oc-team-logo">
                                    </div>
                                    <span class="oc-team-name-compact"><?php echo esc_html( $home_team_name ); ?></span>
                                </div>

                                <div class="oc-vs-compact">
                                    <span class="oc-vs-text">VS</span>
                                </div>

                                <div class="oc-team-compact oc-team-away">
                                    <span class="oc-team-name-compact"><?php echo esc_html( $away_team_name ); ?></span>
                                    <div class="oc-team-logo-wrapper">
                                        <img src="<?php echo esc_url( $away_team_logo ); ?>" alt="<?php echo esc_attr( $away_team_name ); ?>" class="oc-team-logo">
                                    </div>
                                </div>
                            </div>

                            <div class="oc-match-odds-compact">
                                <button class="oc-odd-btn-compact" data-odds="<?php echo esc_attr( $best_odds['home']['odds'] ); ?>" data-type="home" data-match="<?php echo esc_attr( $match_id ); ?>">
                                    <span class="oc-odd-label">1</span>
                                    <span class="oc-odd-value"><?php echo $best_odds['home']['odds'] ? esc_html( number_format( $best_odds['home']['odds'], 2 ) ) : '-'; ?></span>
                                </button>
                                <button class="oc-odd-btn-compact" data-odds="<?php echo esc_attr( $best_odds['draw']['odds'] ); ?>" data-type="draw" data-match="<?php echo esc_attr( $match_id ); ?>">
                                    <span class="oc-odd-label">X</span>
                                    <span class="oc-odd-value"><?php echo $best_odds['draw']['odds'] ? esc_html( number_format( $best_odds['draw']['odds'], 2 ) ) : '-'; ?></span>
                                </button>
                                <button class="oc-odd-btn-compact" data-odds="<?php echo esc_attr( $best_odds['away']['odds'] ); ?>" data-type="away" data-match="<?php echo esc_attr( $match_id ); ?>">
                                    <span class="oc-odd-label">2</span>
                                    <span class="oc-odd-value"><?php echo $best_odds['away']['odds'] ? esc_html( number_format( $best_odds['away']['odds'], 2 ) ) : '-'; ?></span>
                                </button>
                                <a href="<?php echo esc_url( get_permalink( $match_id ) ); ?>" class="oc-more-odds-link">
                                    <span><?php esc_html_e( 'Más Cuotas', 'odds-comparison' ); ?></span>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                        <polyline points="15 3 21 3 21 9"/>
                                        <line x1="10" y1="14" x2="21" y2="3"/>
                                    </svg>
                                </a>
                            </div>
                        </article>
                    <?php endforeach;
                else : ?>
                    <div class="oc-no-matches">
                        <p><?php esc_html_e( 'No hay partidos programados para hoy.', 'odds-comparison' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- Upcoming Matches -->
        <?php
        $grouped_matches = oc_get_grouped_matches();
        $displayed_sections = 0;
        $max_sections = 2; // Show up to 2 upcoming date sections

        foreach ( $grouped_matches as $date_key => $date_group ) :
            if ( $displayed_sections >= $max_sections ) {
                break;
            }

            // Skip today section as it's already shown above
            if ( $date_key === 'today' ) {
                continue;
            }

            $displayed_sections++;
            ?>
            <section class="oc-section oc-upcoming-section">
                <div class="oc-section-header">
                    <h2><?php echo esc_html( $date_group['label'] ); ?></h2>
                </div>

                <div class="oc-matches-cards">
                    <?php
                    $matches_shown = 0;
                    foreach ( $date_group['matches'] as $match ) :
                        if ( $matches_shown >= 3 ) { // Limit to 3 matches per date section
                            break;
                        }
                        $matches_shown++;

                        $match_id = $match['id'];
                        $home_team_name = $match['home_team'];
                        $away_team_name = $match['away_team'];
                        $match_time = $match['time'];
                        $best_odds = oc_get_best_odds( $match_id );

                        // Get team logos from taxonomy
                        $home_team_logo = '';
                        $away_team_logo = '';
                        if ( ! empty( $match['home_logo'] ) ) {
                            $home_team_logo = $match['home_logo'];
                        }
                        if ( ! empty( $match['away_logo'] ) ) {
                            $away_team_logo = $match['away_logo'];
                        }

                        // Fallback to default logos if not found
                        if ( empty( $home_team_logo ) ) {
                            $home_team_logo = OC_ASSETS_URI . '/images/teams/default.png';
                        }
                        if ( empty( $away_team_logo ) ) {
                            $away_team_logo = OC_ASSETS_URI . '/images/teams/default.png';
                        }
                        ?>
                        <article class="oc-match-card-horizontal">
                            <div class="oc-match-time-badge">
                                <span class="oc-match-time"><?php echo $match_time ? esc_html( $match_time ) : 'TBD'; ?></span>
                            </div>

                            <div class="oc-match-teams-compact">
                                <div class="oc-team-compact oc-team-home">
                                    <div class="oc-team-logo-wrapper">
                                        <img src="<?php echo esc_url( $home_team_logo ); ?>" alt="<?php echo esc_attr( $home_team_name ); ?>" class="oc-team-logo">
                                    </div>
                                    <span class="oc-team-name-compact"><?php echo esc_html( $home_team_name ); ?></span>
                                </div>

                                <div class="oc-vs-compact">
                                    <span class="oc-vs-text">VS</span>
                                </div>

                                <div class="oc-team-compact oc-team-away">
                                    <span class="oc-team-name-compact"><?php echo esc_html( $away_team_name ); ?></span>
                                    <div class="oc-team-logo-wrapper">
                                        <img src="<?php echo esc_url( $away_team_logo ); ?>" alt="<?php echo esc_attr( $away_team_name ); ?>" class="oc-team-logo">
                                    </div>
                                </div>
                            </div>

                            <div class="oc-match-odds-compact">
                                <button class="oc-odd-btn-compact" data-odds="<?php echo esc_attr( $best_odds['home']['odds'] ); ?>" data-type="home" data-match="<?php echo esc_attr( $match_id ); ?>">
                                    <span class="oc-odd-label">1</span>
                                    <span class="oc-odd-value"><?php echo $best_odds['home']['odds'] ? esc_html( number_format( $best_odds['home']['odds'], 2 ) ) : '-'; ?></span>
                                </button>
                                <button class="oc-odd-btn-compact" data-odds="<?php echo esc_attr( $best_odds['draw']['odds'] ); ?>" data-type="draw" data-match="<?php echo esc_attr( $match_id ); ?>">
                                    <span class="oc-odd-label">X</span>
                                    <span class="oc-odd-value"><?php echo $best_odds['draw']['odds'] ? esc_html( number_format( $best_odds['draw']['odds'], 2 ) ) : '-'; ?></span>
                                </button>
                                <button class="oc-odd-btn-compact" data-odds="<?php echo esc_attr( $best_odds['away']['odds'] ); ?>" data-type="away" data-match="<?php echo esc_attr( $match_id ); ?>">
                                    <span class="oc-odd-label">2</span>
                                    <span class="oc-odd-value"><?php echo $best_odds['away']['odds'] ? esc_html( number_format( $best_odds['away']['odds'], 2 ) ) : '-'; ?></span>
                                </button>
                                <a href="<?php echo esc_url( get_permalink( $match_id ) ); ?>" class="oc-more-odds-link">
                                    <span><?php esc_html_e( 'Más Cuotas', 'odds-comparison' ); ?></span>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                        <polyline points="15 3 21 3 21 9"/>
                                        <line x1="10" y1="14" x2="21" y2="3"/>
                                    </svg>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <?php if ( $matches_shown === 0 ) : ?>
                        <div class="oc-no-matches">
                            <p><?php esc_html_e( 'No hay partidos programados para esta fecha.', 'odds-comparison' ); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
        
        <!-- View All Link -->
        <div class="oc-view-all-section">
            <a href="<?php echo esc_url( get_post_type_archive_link( 'match' ) ); ?>" class="oc-btn oc-btn-outline">
                <?php esc_html_e( 'Ver todos los partidos', 'odds-comparison' ); ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        
        <!-- Top Operators Section -->
        <section class="oc-section oc-top-operators">
            <div class="oc-section-header">
                <h2><?php esc_html_e( 'Mejores Casas de Apuestas', 'odds-comparison' ); ?></h2>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'operator' ) ); ?>" class="oc-view-all-link">
                    <?php esc_html_e( 'Ver todas', 'odds-comparison' ); ?>
                </a>
            </div>
            
            <div class="oc-operators-scroller">
                <div class="oc-operators-track">
                    <?php
                    $top_operators = oc_get_featured_operators( 10 );
                    foreach ( $top_operators as $operator ) :
                        $operator_id = $operator->ID;
                        $rating = get_post_meta( $operator_id, 'oc_operator_rating', true );
                        $bonus = get_post_meta( $operator_id, 'oc_bonus_amount', true );
                        $affiliate_url = get_post_meta( $operator_id, 'oc_affiliate_url', true );
                    ?>
                    <div class="oc-operator-card-compact">
                        <div class="oc-operator-rank"><?php echo esc_html( get_post_field( 'menu_order', $operator_id ) ?: '1' ); ?></div>
                        <div class="oc-operator-logo-compact">
                            <?php if ( has_post_thumbnail( $operator_id ) ) : ?>
                                <?php echo get_the_post_thumbnail( $operator_id, 'thumbnail', array( 'alt' => $operator->post_title ) ); ?>
                            <?php else : ?>
                                <span class="oc-logo-text"><?php echo esc_html( substr( $operator->post_title, 0, 2 ) ); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="oc-operator-info-compact">
                            <span class="oc-operator-name-compact"><?php echo esc_html( $operator->post_title ); ?></span>
                            <?php if ( $bonus ) : ?>
                                <span class="oc-operator-bonus-compact"><?php echo esc_html( $bonus ); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo esc_url( $affiliate_url ?: get_permalink( $operator_id ) ); ?>" 
                           class="oc-visit-btn-compact" target="_blank" rel="nofollow sponsored">
                            <?php esc_html_e( 'Visitar', 'odds-comparison' ); ?>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        
    </main>
    
    <!-- Sidebar -->
    <aside class="oc-sidebar-area">
        <?php get_sidebar(); ?>
    </aside>
</div>

<?php
get_footer();

