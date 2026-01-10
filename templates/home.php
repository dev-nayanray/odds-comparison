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
                <!-- Match Card 1 -->
                <article class="oc-match-card-horizontal">
                    <div class="oc-match-time-badge">
                        <span class="oc-match-time">8:00 PM</span>
                    </div>
                    
                    <div class="oc-match-teams-compact">
                        <div class="oc-team-compact oc-team-home">
                            <div class="oc-team-logo-wrapper">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/teams/atletico-madrid.png' ); ?>" alt="Atlético Madrid" class="oc-team-logo">
                            </div>
                            <span class="oc-team-name-compact"><?php esc_html_e( 'Atlético Madrid', 'odds-comparison' ); ?></span>
                        </div>
                        
                        <div class="oc-vs-compact">
                            <span class="oc-vs-text">VS</span>
                        </div>
                        
                        <div class="oc-team-compact oc-team-away">
                            <span class="oc-team-name-compact"><?php esc_html_e( 'Real Madrid', 'odds-comparison' ); ?></span>
                            <div class="oc-team-logo-wrapper">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/teams/real-madrid.png' ); ?>" alt="Real Madrid" class="oc-team-logo">
                            </div>
                        </div>
                    </div>
                    
                    <div class="oc-match-odds-compact">
                        <button class="oc-odd-btn-compact" data-odds="3.1" data-type="home" data-match="atletico-vs-real">
                            <span class="oc-odd-label">1</span>
                            <span class="oc-odd-value">3.1</span>
                        </button>
                        <button class="oc-odd-btn-compact" data-odds="3.6" data-type="draw" data-match="atletico-vs-real">
                            <span class="oc-odd-label">X</span>
                            <span class="oc-odd-value">3.6</span>
                        </button>
                        <button class="oc-odd-btn-compact" data-odds="2.41" data-type="away" data-match="atletico-vs-real">
                            <span class="oc-odd-label">2</span>
                            <span class="oc-odd-value">2.41</span>
                        </button>
                        <a href="#" class="oc-more-odds-link">
                            <span><?php esc_html_e( 'Más Cuotas', 'odds-comparison' ); ?></span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                        </a>
                    </div>
                </article>
                
                <!-- Match Card 2 -->
                <article class="oc-match-card-horizontal">
                    <div class="oc-match-time-badge">
                        <span class="oc-match-time">7:00 PM</span>
                    </div>
                    
                    <div class="oc-match-teams-compact">
                        <div class="oc-team-compact oc-team-home">
                            <div class="oc-team-logo-wrapper">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/teams/psg.png' ); ?>" alt="PSG" class="oc-team-logo">
                            </div>
                            <span class="oc-team-name-compact"><?php esc_html_e( 'PSG', 'odds-comparison' ); ?></span>
                        </div>
                        
                        <div class="oc-vs-compact">
                            <span class="oc-vs-text">VS</span>
                        </div>
                        
                        <div class="oc-team-compact oc-team-away">
                            <span class="oc-team-name-compact"><?php esc_html_e( 'Olympique Marsella', 'odds-comparison' ); ?></span>
                            <div class="oc-team-logo-wrapper">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/teams/marseille.png' ); ?>" alt="Olympique Marsella" class="oc-team-logo">
                            </div>
                        </div>
                    </div>
                    
                    <div class="oc-match-odds-compact">
                        <button class="oc-odd-btn-compact" data-odds="1.56" data-type="home" data-match="psg-vs-marseille">
                            <span class="oc-odd-label">1</span>
                            <span class="oc-odd-value">1.56</span>
                        </button>
                        <button class="oc-odd-btn-compact" data-odds="4.51" data-type="draw" data-match="psg-vs-marseille">
                            <span class="oc-odd-label">X</span>
                            <span class="oc-odd-value">4.51</span>
                        </button>
                        <button class="oc-odd-btn-compact" data-odds="6" data-type="away" data-match="psg-vs-marseille">
                            <span class="oc-odd-label">2</span>
                            <span class="oc-odd-value">6</span>
                        </button>
                        <a href="#" class="oc-more-odds-link">
                            <span><?php esc_html_e( 'Más Cuotas', 'odds-comparison' ); ?></span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                        </a>
                    </div>
                </article>
            </div>
        </section>
        
        <!-- Friday Matches -->
        <section class="oc-section oc-upcoming-section">
            <div class="oc-section-header">
                <h2><?php esc_html_e( 'VIERNES, 9 DE ENERO', 'odds-comparison' ); ?></h2>
            </div>
            
            <div class="oc-matches-cards">
                <article class="oc-match-card-horizontal">
                    <div class="oc-match-time-badge">
                        <span class="oc-match-time">9:00 PM</span>
                    </div>
                    
                    <div class="oc-match-teams-compact">
                        <div class="oc-team-compact oc-team-home">
                            <div class="oc-team-logo-wrapper">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/teams/getafe.png' ); ?>" alt="Getafe" class="oc-team-logo">
                            </div>
                            <span class="oc-team-name-compact"><?php esc_html_e( 'Getafe', 'odds-comparison' ); ?></span>
                        </div>
                        
                        <div class="oc-vs-compact">
                            <span class="oc-vs-text">VS</span>
                        </div>
                        
                        <div class="oc-team-compact oc-team-away">
                            <span class="oc-team-name-compact"><?php esc_html_e( 'Real Sociedad', 'odds-comparison' ); ?></span>
                            <div class="oc-team-logo-wrapper">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/teams/real-sociedad.png' ); ?>" alt="Real Sociedad" class="oc-team-logo">
                            </div>
                        </div>
                    </div>
                    
                    <div class="oc-match-odds-compact">
                        <button class="oc-odd-btn-compact" data-odds="3.5" data-type="home" data-match="getafe-vs-real-sociedad">
                            <span class="oc-odd-label">1</span>
                            <span class="oc-odd-value">3.5</span>
                        </button>
                        <button class="oc-odd-btn-compact" data-odds="3" data-type="draw" data-match="getafe-vs-real-sociedad">
                            <span class="oc-odd-label">X</span>
                            <span class="oc-odd-value">3</span>
                        </button>
                        <button class="oc-odd-btn-compact" data-odds="2.55" data-type="away" data-match="getafe-vs-real-sociedad">
                            <span class="oc-odd-label">2</span>
                            <span class="oc-odd-value">2.55</span>
                        </button>
                        <a href="#" class="oc-more-odds-link">
                            <span><?php esc_html_e( 'Más Cuotas', 'odds-comparison' ); ?></span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                        </a>
                    </div>
                </article>
            </div>
        </section>
        
        <!-- Saturday Matches -->
        <section class="oc-section oc-upcoming-section">
            <div class="oc-section-header">
                <h2><?php esc_html_e( 'SÁBADO, 10 DE ENERO', 'odds-comparison' ); ?></h2>
            </div>
            
            <div class="oc-matches-cards">
                <article class="oc-match-card-horizontal">
                    <div class="oc-match-time-badge">
                        <span class="oc-match-time">2:00 PM</span>
                    </div>
                    
                    <div class="oc-match-teams-compact">
                        <div class="oc-team-compact oc-team-home">
                            <div class="oc-team-logo-wrapper">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/teams/oviedo.png' ); ?>" alt="Oviedo" class="oc-team-logo">
                            </div>
                            <span class="oc-team-name-compact"><?php esc_html_e( 'Oviedo', 'odds-comparison' ); ?></span>
                        </div>
                        
                        <div class="oc-vs-compact">
                            <span class="oc-vs-text">VS</span>
                        </div>
                        
                        <div class="oc-team-compact oc-team-away">
                            <span class="oc-team-name-compact"><?php esc_html_e( 'Betis', 'odds-comparison' ); ?></span>
                            <div class="oc-team-logo-wrapper">
                                <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/teams/betis.png' ); ?>" alt="Betis" class="oc-team-logo">
                            </div>
                        </div>
                    </div>
                    
                    <div class="oc-match-odds-compact">
                        <button class="oc-odd-btn-compact" data-odds="4.37" data-type="home" data-match="oviedo-vs-betis">
                            <span class="oc-odd-label">1</span>
                            <span class="oc-odd-value">4.37</span>
                        </button>
                        <button class="oc-odd-btn-compact" data-odds="3.6" data-type="draw" data-match="oviedo-vs-betis">
                            <span class="oc-odd-label">X</span>
                            <span class="oc-odd-value">3.6</span>
                        </button>
                        <button class="oc-odd-btn-compact" data-odds="1.98" data-type="away" data-match="oviedo-vs-betis">
                            <span class="oc-odd-label">2</span>
                            <span class="oc-odd-value">1.98</span>
                        </button>
                        <a href="#" class="oc-more-odds-link">
                            <span><?php esc_html_e( 'Más Cuotas', 'odds-comparison' ); ?></span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                        </a>
                    </div>
                </article>
            </div>
        </section>
        
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

