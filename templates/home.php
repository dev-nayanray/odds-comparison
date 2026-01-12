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
    <!-- Mobile: Main Content First, Sidebar Second -->
    <!-- Main Content Area -->
    <main class="oc-main-content">
        
        <!-- Banner Slider -->
        <section class="oc-banner-section">
            <div class="oc-banner-slider">
                <?php
                $options = get_option( 'oc_theme_options' );
                $banners = array();

                // Collect banner data
                for ( $i = 1; $i <= 3; $i++ ) {
                    $banner = array(
                        'title' => isset( $options["banner_{$i}_title"] ) ? $options["banner_{$i}_title"] : '',
                        'description' => isset( $options["banner_{$i}_description"] ) ? $options["banner_{$i}_description"] : '',
                        'button_text' => isset( $options["banner_{$i}_button_text"] ) ? $options["banner_{$i}_button_text"] : '',
                        'button_url' => isset( $options["banner_{$i}_button_url"] ) ? $options["banner_{$i}_button_url"] : '',
                        'image' => isset( $options["banner_{$i}_image"] ) ? $options["banner_{$i}_image"] : '',
                    );

                    // Only add banner if it has content
                    if ( ! empty( $banner['title'] ) || ! empty( $banner['description'] ) ) {
                        $banners[] = $banner;
                    }
                }

                // If no banners configured, show default
                if ( empty( $banners ) ) {
                    $banners[] = array(
                        'title' => __( 'Compare the Best Odds', 'odds-comparison' ),
                        'description' => __( 'Find the highest odds from reliable betting houses.', 'odds-comparison' ),
                        'button_text' => __( 'View Betting Houses', 'odds-comparison' ),
                        'button_url' => get_post_type_archive_link( 'operator' ),
                        'image' => '',
                    );
                }

                foreach ( $banners as $index => $banner ) :
                    $is_active = $index === 0 ? 'active' : '';
                ?>
                <div class="oc-banner-slide <?php echo esc_attr( $is_active ); ?>">
                    <div class="oc-banner-content">
                        <span class="oc-banner-badge"><?php esc_html_e( 'Nuevo', 'odds-comparison' ); ?></span>
                        <h2><?php echo esc_html( $banner['title'] ); ?></h2>
                        <p><?php echo esc_html( $banner['description'] ); ?></p>
                        <?php if ( ! empty( $banner['button_text'] ) && ! empty( $banner['button_url'] ) ) : ?>
                            <a href="<?php echo esc_url( $banner['button_url'] ); ?>" class="oc-btn oc-btn-primary">
                                <?php echo esc_html( $banner['button_text'] ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="oc-banner-image">
                        <?php if ( ! empty( $banner['image'] ) ) : ?>
                            <img src="<?php echo esc_url( $banner['image'] ); ?>" alt="<?php echo esc_attr( $banner['title'] ); ?>" class="oc-banner-img">
                        <?php else : ?>
                            <div class="oc-banner-placeholder">
                                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <path d="M4 11a9 9 0 0 1 9 9"></path>
                                    <path d="M4 4a16 16 0 0 1 16 16"></path>
                                    <circle cx="5" cy="19" r="1"></circle>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Slider Controls -->
            <div class="oc-slider-controls">
                <?php for ( $i = 0; $i < count( $banners ); $i++ ) : ?>
                    <button class="oc-slider-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-slide="<?php echo esc_attr( $i ); ?>"></button>
                <?php endfor; ?>
            </div>
        </section>
        
        <!-- Live Matches Section -->
        <section class="oc-section oc-live-section">
            <div class="oc-section-header">
                <h2><?php esc_html_e( 'Live Matches', 'odds-comparison' ); ?></h2>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'match' ) ); ?>" class="oc-view-all-link">
                    <?php esc_html_e( 'Ver todos los partidos', 'odds-comparison' ); ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="oc-matches-cards">
                <?php
                $live_matches = oc_get_live_matches( 20 ); // Get 20 live matches for homepage
                if ( ! empty( $live_matches ) ) :
                    foreach ( $live_matches as $match ) :
                        $match_id = $match['ID'];
                        $match_date = get_post_meta( $match_id, 'oc_match_date', true );
                        $match_time = $match['match_time'];
                        $best_odds = oc_get_best_odds( $match_id );

                        // Get team names and logos from taxonomy
                        $home_team_name = '';
                        $away_team_name = '';
                        $home_team_logo = '';
                        $away_team_logo = '';
                        if ( ! empty( $match['teams'] ) ) {
                            $team_count = count( $match['teams'] );
                            if ( $team_count >= 1 ) {
                                $home_team_name = $match['teams'][0]->name;
                                $logo_id = get_term_meta( $match['teams'][0]->term_id, 'oc_team_logo_id', true );
                                if ( ! empty( $logo_id ) ) {
                                    $home_team_logo = wp_get_attachment_url( $logo_id );
                                }
                            }
                            if ( $team_count >= 2 ) {
                                $away_team_name = $match['teams'][1]->name;
                                $logo_id = get_term_meta( $match['teams'][1]->term_id, 'oc_team_logo_id', true );
                                if ( ! empty( $logo_id ) ) {
                                    $away_team_logo = wp_get_attachment_url( $logo_id );
                                }
                            }
                        }

                        // Fallback to default logos if not found
                        if ( empty( $home_team_logo ) ) {
                            $home_team_logo = get_template_directory_uri() . '/assets/images/teams/default.png';
                        }
                        if ( empty( $away_team_logo ) ) {
                            $away_team_logo = get_template_directory_uri() . '/assets/images/teams/default.png';
                        }
                        ?>
                        <article class="oc-match-card-horizontal">
                            <div class="oc-match-time-badge">
                                <span class="oc-match-date"><?php echo esc_html( date_i18n( 'd M', strtotime( $match_date ) ) ); ?></span>
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
                                <button class="oc-odd-btn-compact" data-odds="<?php echo esc_attr( $best_odds['home']['odds'] ); ?>" data-selection="1" data-match-id="<?php echo esc_attr( $match_id ); ?>" data-bookmaker-id="<?php echo esc_attr( $best_odds['home']['bookmaker_id'] ); ?>" data-bookmaker-name="<?php echo esc_attr( $best_odds['home']['bookmaker_name'] ); ?>">
                                    <span class="oc-odd-label">1</span>
                                    <span class="oc-odd-value"><?php echo $best_odds['home']['odds'] ? esc_html( number_format( $best_odds['home']['odds'], 2 ) ) : '-'; ?></span>
                                </button>
                                <button class="oc-odd-btn-compact" data-odds="<?php echo esc_attr( $best_odds['draw']['odds'] ); ?>" data-selection="X" data-match-id="<?php echo esc_attr( $match_id ); ?>" data-bookmaker-id="<?php echo esc_attr( $best_odds['draw']['bookmaker_id'] ); ?>" data-bookmaker-name="<?php echo esc_attr( $best_odds['draw']['bookmaker_name'] ); ?>">
                                    <span class="oc-odd-label">X</span>
                                    <span class="oc-odd-value"><?php echo $best_odds['draw']['odds'] ? esc_html( number_format( $best_odds['draw']['odds'], 2 ) ) : '-'; ?></span>
                                </button>
                                <button class="oc-odd-btn-compact" data-odds="<?php echo esc_attr( $best_odds['away']['odds'] ); ?>" data-selection="2" data-match-id="<?php echo esc_attr( $match_id ); ?>" data-bookmaker-id="<?php echo esc_attr( $best_odds['away']['bookmaker_id'] ); ?>" data-bookmaker-name="<?php echo esc_attr( $best_odds['away']['bookmaker_name'] ); ?>">
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
        
        <!-- Upcoming Matches Section -->
        <section class="oc-section oc-upcoming-section">
            <div class="oc-section-header">
                <h2><?php esc_html_e( 'PRÓXIMOS PARTIDOS', 'odds-comparison' ); ?></h2>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'match' ) ); ?>" class="oc-view-all-link">
                    <?php esc_html_e( 'Ver todos los partidos', 'odds-comparison' ); ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="oc-matches-cards">
                <?php
                $upcoming_matches = oc_get_upcoming_matches( array( 'posts_per_page' => 6 ) ); // Get 6 upcoming matches for homepage
                if ( ! empty( $upcoming_matches ) ) :
                    foreach ( $upcoming_matches as $match ) :
                        $match_id = $match['ID'];
                        $match_date = get_post_meta( $match_id, 'oc_match_date', true );
                        $match_time = $match['match_time'];
                        $best_odds = oc_get_best_odds( $match_id );

                        // Get team names and logos from taxonomy
                        $home_team_name = '';
                        $away_team_name = '';
                        $home_team_logo = '';
                        $away_team_logo = '';
                        if ( ! empty( $match['teams'] ) ) {
                            $team_count = count( $match['teams'] );
                            if ( $team_count >= 1 ) {
                                $home_team_name = $match['teams'][0]->name;
                                $logo_id = get_term_meta( $match['teams'][0]->term_id, 'oc_team_logo_id', true );
                                if ( ! empty( $logo_id ) ) {
                                    $home_team_logo = wp_get_attachment_url( $logo_id );
                                }
                            }
                            if ( $team_count >= 2 ) {
                                $away_team_name = $match['teams'][1]->name;
                                $logo_id = get_term_meta( $match['teams'][1]->term_id, 'oc_team_logo_id', true );
                                if ( ! empty( $logo_id ) ) {
                                    $away_team_logo = wp_get_attachment_url( $logo_id );
                                }
                            }
                        }

                        // Fallback to default logos if not found
                        if ( empty( $home_team_logo ) ) {
                            $home_team_logo = get_template_directory_uri() . '/assets/images/teams/default.png';
                        }
                        if ( empty( $away_team_logo ) ) {
                            $away_team_logo = get_template_directory_uri() . '/assets/images/teams/default.png';
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
                                <button class="oc-odd-btn-compact" data-odds="<?php echo esc_attr( $best_odds['home']['odds'] ); ?>" data-selection="1" data-match-id="<?php echo esc_attr( $match_id ); ?>" data-bookmaker-id="<?php echo esc_attr( $best_odds['home']['bookmaker_id'] ); ?>" data-bookmaker-name="<?php echo esc_attr( $best_odds['home']['bookmaker_name'] ); ?>">
                                    <span class="oc-odd-label">1</span>
                                    <span class="oc-odd-value"><?php echo $best_odds['home']['odds'] ? esc_html( number_format( $best_odds['home']['odds'], 2 ) ) : '-'; ?></span>
                                </button>
                                <button class="oc-odd-btn-compact" data-odds="<?php echo esc_attr( $best_odds['draw']['odds'] ); ?>" data-selection="X" data-match-id="<?php echo esc_attr( $match_id ); ?>" data-bookmaker-id="<?php echo esc_attr( $best_odds['draw']['bookmaker_id'] ); ?>" data-bookmaker-name="<?php echo esc_attr( $best_odds['draw']['bookmaker_name'] ); ?>">
                                    <span class="oc-odd-label">X</span>
                                    <span class="oc-odd-value"><?php echo $best_odds['draw']['odds'] ? esc_html( number_format( $best_odds['draw']['odds'], 2 ) ) : '-'; ?></span>
                                </button>
                                <button class="oc-odd-btn-compact" data-odds="<?php echo esc_attr( $best_odds['away']['odds'] ); ?>" data-selection="2" data-match-id="<?php echo esc_attr( $match_id ); ?>" data-bookmaker-id="<?php echo esc_attr( $best_odds['away']['bookmaker_id'] ); ?>" data-bookmaker-name="<?php echo esc_attr( $best_odds['away']['bookmaker_name'] ); ?>">
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
                        <p><?php esc_html_e( 'No hay partidos próximos programados.', 'odds-comparison' ); ?></p>
                    </div>
                <?php endif; ?>
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
        
        
            <div class="oc-top-operators-header">
                <h1 class="oc-top-operators-title"><?php esc_html_e( 'Mejores Casas de Apuestas', 'odds-comparison' ); ?></h1>
                <p class="oc-top-operators-subtitle"><?php esc_html_e( 'Descubre las mejores casas de apuestas con las ofertas más competitivas y confiables del mercado', 'odds-comparison' ); ?></p>
            </div>

            <div class="oc-grid-slider">
                <div class="oc-grid-container">
                    <?php
                    // Get operators using the existing function
                    $operators = oc_get_featured_operators(10);

                    // If no operators found, use demo data
                    if (empty($operators)) {
                        $demo_operators = array(
                            array('name' => 'Bet365', 'logo' => 'data:image/png;base64,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', 'rating' => 4.8, 'bonus' => '100%'),
                            array('name' => 'William Hill', 'logo' => 'data:image/png;base64,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', 'rating' => 4.6, 'bonus' => '50€'),
                            array('name' => 'Betfair', 'logo' => 'data:image/jpeg;base64,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', 'rating' => 4.7, 'bonus' => '200%'),
                            array('name' => 'Paddy Power', 'logo' => 'data:image/jpeg;base64,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', 'rating' => 4.5, 'bonus' => '€30'),
                            array('name' => '888sport', 'logo' => 'data:image/png;base64,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', 'rating' => 4.4, 'bonus' => '€10'),
                            array('name' => 'BetVictor', 'logo' => 'data:image/jpeg;base64,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', 'rating' => 4.3, 'bonus' => '£25'),
                            array('name' => 'Sky Bet', 'logo' => 'data:image/png;base64,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', 'rating' => 4.5, 'bonus' => '£10'),
                            array('name' => 'Ladbrokes', 'logo' => 'data:image/jpeg;base64,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', 'rating' => 4.2, 'bonus' => '£20'),
                            array('name' => 'Unibet', 'logo' => 'https://via.placeholder.com/70x70/8e44ad/ffffff?text=U', 'rating' => 4.6, 'bonus' => '€25'),
                        );

                        // Duplicate for infinite scroll effect
                        $all_operators = array_merge($demo_operators, $demo_operators);
                    } else {
                        // Convert WP_Post objects to array format
                        $operator_data = array();
                        foreach ($operators as $operator) {
                            $operator_data[] = array(
                                'name' => get_the_title($operator->ID),
                                'logo' => get_the_post_thumbnail_url($operator->ID, 'medium') ?: 'https://via.placeholder.com/70x70/27ae60/ffffff?text=' . substr(get_the_title($operator->ID), 0, 3),
                                'rating' => get_post_meta($operator->ID, 'oc_operator_rating', true) ?: 4.5,
                                'bonus' => get_post_meta($operator->ID, 'oc_bonus_amount', true) ?: 'Bonus'
                            );
                        }
                        // Duplicate for infinite scroll effect
                        $all_operators = array_merge($operator_data, $operator_data);
                    }

                    // Display operators
                    foreach ($all_operators as $operator) {
                        ?>
                        <div class="oc-grid-item">
                            <img src="<?php echo esc_url($operator['logo']); ?>" alt="<?php echo esc_attr($operator['name']); ?>" class="oc-grid-logo">
                            <h3 class="oc-grid-name"><?php echo esc_html($operator['name']); ?></h3>
                            <div class="oc-grid-rating">
                                <div class="oc-stars">
                                    <?php
                                    $rating = $operator['rating'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo '<span class="star ' . ($i <= $rating ? 'filled' : '') . '">★</span>';
                                    }
                                    ?>
                                </div>
                                <span><?php echo esc_html($rating); ?></span>
                            </div>
                            <div class="oc-grid-bonus"><?php echo esc_html($operator['bonus']); ?></div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </section> -->
<!-- Blog Section -->
        <section class="oc-blog-section">
            <div class="oc-section-header">
                <h2><?php esc_html_e( 'Latest Articles', 'odds-comparison' ); ?></h2>
                <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="oc-view-all-link">
                    <?php esc_html_e( 'View all articles', 'odds-comparison' ); ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="oc-blog-grid">
                <?php
                // Get latest 6 blog posts
                $blog_posts = new WP_Query( array(
                    'posts_per_page' => 6,
                    'post_type'      => 'post',
                    'post_status'    => 'publish',
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                ) );

                if ( $blog_posts->have_posts() ) :
                    while ( $blog_posts->have_posts() ) :
                        $blog_posts->the_post();
                        ?>
                        <article class="oc-blog-card">
                            <div class="oc-blog-card-image">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'medium_large', array( 'alt' => get_the_title(), 'loading' => 'lazy' ) ); ?>
                                <?php else : ?>
                                    <div class="oc-image-placeholder">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path d="M4 11a9 9 0 0 1 9 9"></path>
                                            <path d="M4 4a16 16 0 0 1 16 16"></path>
                                            <circle cx="5" cy="19" r="1"></circle>
                                        </svg>
                                    </div>
                                <?php endif; ?>

                                <?php
                                // Get first category
                                $categories = get_the_category();
                                if ( ! empty( $categories ) ) :
                                    $category = $categories[0];
                                    ?>
                                    <a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" class="oc-blog-category-badge">
                                        <?php echo esc_html( $category->name ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="oc-blog-card-content">
                                <div class="oc-blog-card-meta">
                                    <span class="oc-post-date">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        <?php echo esc_html( date_i18n( 'd M Y', get_the_date() ) ); ?>
                                    </span>
                                    <span class="oc-post-author">
                                        <?php echo get_avatar( get_the_author_meta( 'ID' ), 24 ); ?>
                                        <span class="oc-author-name"><?php the_author(); ?></span>
                                    </span>
                                </div>

                                <h3 class="oc-blog-card-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>

                                <div class="oc-blog-card-excerpt">
                                    <?php
                                    $excerpt = get_the_excerpt();
                                    $excerpt = wp_trim_words( $excerpt, 20, '...' );
                                    echo esc_html( $excerpt );
                                    ?>
                                </div>

                                <div class="oc-blog-card-footer">
                                    <a href="<?php the_permalink(); ?>" class="oc-read-more">
                                        <?php esc_html_e( 'Read more', 'odds-comparison' ); ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14M12 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                    <span class="oc-comment-count">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                        </svg>
                                        <?php echo esc_html( get_comments_number() ); ?>
                                    </span>
                                </div>
                            </div>
                        </article>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <div class="oc-blog-no-posts">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 11a9 9 0 0 1 9 9"></path>
                            <path d="M4 4a16 16 0 0 1 16 16"></path>
                            <circle cx="5" cy="19" r="1"></circle>
                        </svg>
                        <h3><?php esc_html_e( 'No articles yet', 'odds-comparison' ); ?></h3>
                        <p><?php esc_html_e( 'Check back soon for new content.', 'odds-comparison' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="oc-blog-view-all-section">
                <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="oc-btn">
                    <?php esc_html_e( 'View All Articles', 'odds-comparison' ); ?>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </section>
        <!-- BETQIO Content Sections -->
        <section class="oc-section oc-about-section">
            <div class="oc-content-wrapper">
                <h2><?php esc_html_e( 'BETQIO ODDS COMPARATOR & BETTING SITES', 'odds-comparison' ); ?></h2>
                <p><?php esc_html_e( 'Betqio is an advanced odds comparison platform designed to help bettors access the best betting sites and compare sports odds in real time. With accurate tools, updated insights, and clear data presentation, Betqio allows you to analyze every available option and make confident betting decisions.', 'odds-comparison' ); ?></p>
                <p><?php esc_html_e( 'Our mission is to provide users with a transparent, comprehensive overview of the betting market, helping them identify value opportunities quickly and efficiently.', 'odds-comparison' ); ?></p>
            </div>
        </section>

        <section class="oc-section oc-sports-betting-section">
            <div class="oc-content-wrapper">
                <h2><?php esc_html_e( 'ONLINE SPORTS BETTING', 'odds-comparison' ); ?></h2>
                <p><?php esc_html_e( 'Online sports betting offers an огром wide range of options. From popular sports such as football, tennis, basketball, and boxing, to major tournaments and special events, the number of markets and betting providers continues to grow.', 'odds-comparison' ); ?></p>
                <p><?php esc_html_e( 'With so many choices available, selecting the best odds can be challenging. That\'s why Betqio delivers a powerful odds comparison service combined with expert support, allowing you to find the best prices across trusted bookmakers in just a few clicks.', 'odds-comparison' ); ?></p>
            </div>
        </section>

        <section class="oc-section oc-realtime-odds-section">
            <div class="oc-content-wrapper">
                <h2><?php esc_html_e( 'REAL-TIME SPORTS BETTING ODDS', 'odds-comparison' ); ?></h2>
                <p><?php esc_html_e( 'At Betqio, we display live odds from top betting sites worldwide, updated in real time. This allows you to instantly compare prices for any event, market, or sport, ensuring you always choose the most competitive odds.', 'odds-comparison' ); ?></p>
                <p><?php esc_html_e( 'We collaborate with reputable betting operators and cover a wide variety of sports, so whenever odds are available for an event, you can count on Betqio to show them accurately and clearly.', 'odds-comparison' ); ?></p>
            </div>
        </section>

        <section class="oc-section oc-comparison-tool-section">
            <div class="oc-content-wrapper">
                <h2><?php esc_html_e( 'SPORTS ODDS COMPARISON TOOL', 'odds-comparison' ); ?></h2>
                <p><?php esc_html_e( 'The Betqio odds comparison tool is designed to be both simple and powerful. Just search for the event you\'re interested in, and you\'ll see a clear, visual comparison of odds from multiple bookmakers.', 'odds-comparison' ); ?></p>
                <p><?php esc_html_e( 'Compare traditional markets such as match winner alongside more exciting betting options like first goal scorer, correct score, total points, or winning margin — all presented in an easy-to-read format and updated live.', 'odds-comparison' ); ?></p>
            </div>
        </section>

        <section class="oc-section oc-betting-sites-section">
            <div class="oc-content-wrapper">
                <h2><?php esc_html_e( 'ONLINE BETTING SITES', 'odds-comparison' ); ?></h2>
                <p><?php esc_html_e( 'The rise of online betting has led to the creation of countless betting platforms. However, not all operators offer the same level of security, reliability, or regulatory compliance.', 'odds-comparison' ); ?></p>
                <p><?php esc_html_e( 'At Betqio, we list and compare licensed and trustworthy betting sites, ensuring users can bet with confidence. Regulated bookmakers provide a safer environment, audited processes, and secure transactions — essential elements for responsible online betting.', 'odds-comparison' ); ?></p>
                <p><?php esc_html_e( 'We also feature betting sites available in multiple regions, including international and Latin American markets, giving users access to leading global brands and region-specific promotions where permitted by local regulations.', 'odds-comparison' ); ?></p>
            </div>
        </section>

        <section class="oc-section oc-betting-tips-section">
            <div class="oc-content-wrapper">
                <h2><?php esc_html_e( 'SPORTS BETTING TIPS & PREDICTIONS', 'odds-comparison' ); ?></h2>
                <p><?php esc_html_e( 'Information is key when it comes to successful betting. Betqio provides expert-driven sports betting tips and predictions based on in-depth analysis, statistics, and market trends.', 'odds-comparison' ); ?></p>
                <p><?php esc_html_e( 'Our insights are designed to help you identify high-value bets, understand market movement, and stay ahead of the competition. By combining expert predictions with real-time odds comparison, Betqio gives you a smarter approach to online sports betting.', 'odds-comparison' ); ?></p>
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
?>

