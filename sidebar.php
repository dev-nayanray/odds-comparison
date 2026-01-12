<?php
/**
 * The sidebar template file
 *
 * Displays the sidebar with registration, newsletter, exclusive offers and apuestas sections
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<aside id="secondary" class="widget-area sidebar">
    <div class="sidebar-inner">
        <?php
        // Registration Section
        oc_render_registration_section();
        
        // Newsletter Section
        oc_render_newsletter_section();
        
        // Check if we're on homepage
        if ( is_front_page() || is_home() ) {
            // Homepage: Show Exclusivas section instead of Pronósticos
            oc_render_exclusivas_section();
        } else {
            // Other pages: Show Pronósticos section (blog posts)
            oc_render_pronosticos_section();
        }
        
        // Apuestas Section
        oc_render_apuestas_section();
        ?>
    </div>
</aside><!-- #secondary -->

<?php
/**
 * Render the registration section
 */
function oc_render_registration_section() {
    ?>
    <div class="sidebar-section oc-register-section">
        <h3 class="sidebar-title"><?php esc_html_e( 'Regístrate en Betqio', 'odds-comparison' ); ?></h3>
        <p class="register-description"><?php esc_html_e( 'Accede a las mejores cuotas y ofertas exclusivas', 'odds-comparison' ); ?></p>
        <div class="register-buttons">
            <a href="<?php echo esc_url( get_permalink( get_page_by_path( 'login' ) ) ); ?>" class="oc-btn oc-login-btn-sidebar">
                <?php esc_html_e( 'Iniciar sesión', 'odds-comparison' ); ?>
            </a>
            <a href="<?php echo esc_url( get_permalink( get_page_by_path( 'register' ) ) ); ?>" class="oc-btn oc-register-btn-sidebar">
                <?php esc_html_e( 'Registrarse', 'odds-comparison' ); ?>
            </a>
        </div>
    </div>
    <?php
}

/**
 * Render the newsletter subscription section
 */
function oc_render_newsletter_section() {
    ?>
    <div class="sidebar-section oc-newsletter-section">
        <h3 class="sidebar-title"><?php esc_html_e( 'Newsletter', 'odds-comparison' ); ?></h3>
        <p class="newsletter-description"><?php esc_html_e( 'Suscríbase a nuestro correo electrónico para recibir las mejores predicciones y pronósticos deportivos', 'odds-comparison' ); ?></p>
        <form class="oc-newsletter-form" action="#" method="post">
            <div class="newsletter-input-wrapper">
                <input type="email" name="oc_newsletter_email" placeholder="<?php esc_attr_e( 'Tu correo electrónico', 'odds-comparison' ); ?>" required class="newsletter-email-input">
                <button type="submit" class="oc-btn oc-newsletter-btn">
                    <?php esc_html_e( 'Suscribirse', 'odds-comparison' ); ?>
                </button>
            </div>
        </form>
    </div>
    <?php
}

/**
 * Render the Exclusivas (exclusive offers) section for homepage sidebar
 */
function oc_render_exclusivas_section() {
    // Get operators with exclusive offers/bonuses
    $args = array(
        'post_type'      => 'operator',
        'posts_per_page' => 5,
        'post_status'    => 'publish',
        'meta_key'       => 'oc_operator_rating',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    );

    $operators = get_posts( $args );

    // Sample exclusivas data if no operators found
    $exclusivas = array();
    
    if ( ! empty( $operators ) ) {
        foreach ( $operators as $operator ) {
            $bonus_amount = get_post_meta( $operator->ID, 'oc_bonus_amount', true );
            $bonus_type = get_post_meta( $operator->ID, 'oc_bonus_type', true );
            $affiliate_url = get_post_meta( $operator->ID, 'oc_affiliate_url', true );
            
            $exclusivas[] = array(
                'name'   => $operator->post_title,
                'bonus'  => $bonus_amount ? $bonus_amount : 'Mejor Cuota',
                'logo'   => has_post_thumbnail( $operator->ID ) ? get_the_post_thumbnail_url( $operator->ID, 'thumbnail' ) : '',
                'url'    => $affiliate_url ?: get_permalink( $operator->ID ),
                'rating' => get_post_meta( $operator->ID, 'oc_operator_rating', true ),
            );
        }
    } else {
        // Fallback sample data
        $exclusivas = array(
            array(
                'name'   => 'Bet365',
                'bonus'  => '100% hasta 200€',
                'logo'   => '',
                'url'    => '#',
                'rating' => 4.8,
            ),
            array(
                'name'   => 'Betway',
                'bonus'  => '100% hasta 250€',
                'logo'   => '',
                'url'    => '#',
                'rating' => 4.5,
            ),
            array(
                'name'   => '888sport',
                'bonus'  => '100€ gratis',
                'logo'   => '',
                'url'    => '#',
                'rating' => 4.3,
            ),
            array(
                'name'   => 'Unibet',
                'bonus'  => '30€ gratis',
                'logo'   => '',
                'url'    => '#',
                'rating' => 4.4,
            ),
            array(
                'name'   => 'Bwin',
                'bonus'  => '100€ bono',
                'logo'   => '',
                'url'    => '#',
                'rating' => 4.2,
            ),
        );
    }
    ?>
    <div class="sidebar-section oc-exclusivas-section">
        <h3 class="sidebar-title"><?php esc_html_e( 'Exclusivas', 'odds-comparison' ); ?></h3>
        <div class="oc-exclusivas-list">
            <?php foreach ( $exclusivas as $index => $exclusiva ) : ?>
            <div class="oc-exclusiva-card">
                <div class="oc-exclusiva-rank"><?php echo esc_html( $index + 1 ); ?></div>
                <div class="oc-exclusiva-logo">
                    <?php if ( ! empty( $exclusiva['logo'] ) ) : ?>
                        <img src="<?php echo esc_url( $exclusiva['logo'] ); ?>" alt="<?php echo esc_attr( $exclusiva['name'] ); ?>">
                    <?php else : ?>
                        <div class="oc-exclusiva-placeholder">
                            <span><?php echo esc_html( substr( $exclusiva['name'], 0, 2 ) ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="oc-exclusiva-info">
                    <span class="oc-exclusiva-name"><?php echo esc_html( $exclusiva['name'] ); ?></span>
                    <span class="oc-exclusiva-bonus"><?php echo esc_html( $exclusiva['bonus'] ); ?></span>
                    <?php if ( ! empty( $exclusiva['rating'] ) ) : ?>
                        <span class="oc-exclusiva-rating">★ <?php echo esc_html( number_format( $exclusiva['rating'], 1 ) ); ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?php echo esc_url( $exclusiva['url'] ); ?>" class="oc-btn oc-exclusiva-btn" target="_blank" rel="nofollow">
                    <?php esc_html_e( 'Ver', 'odds-comparison' ); ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <style>
        .oc-exclusivas-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .oc-exclusivas-section .sidebar-title {
            color: #fff;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .oc-exclusivas-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .oc-exclusiva-card {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .oc-exclusiva-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }
        
        .oc-exclusiva-rank {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            margin-right: 10px;
            flex-shrink: 0;
        }
        
        .oc-exclusiva-logo {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            overflow: hidden;
            margin-right: 10px;
            flex-shrink: 0;
            background: #f5f5f5;
        }
        
        .oc-exclusiva-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .oc-exclusiva-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            font-weight: 700;
            font-size: 12px;
        }
        
        .oc-exclusiva-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        
        .oc-exclusiva-name {
            font-weight: 600;
            font-size: 13px;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .oc-exclusiva-bonus {
            font-size: 11px;
            color: #28a745;
            font-weight: 600;
        }
        
        .oc-exclusiva-rating {
            font-size: 10px;
            color: #ffc107;
        }
        
        .oc-exclusiva-btn {
            padding: 6px 12px;
            font-size: 11px;
            border-radius: 6px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            white-space: nowrap;
            flex-shrink: 0;
            margin-left: 8px;
        }
        
        .oc-exclusiva-btn:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: #fff;
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .oc-exclusiva-card {
                padding: 10px;
            }
            
            .oc-exclusiva-rank {
                width: 24px;
                height: 24px;
                font-size: 10px;
            }
            
            .oc-exclusiva-logo {
                width: 36px;
                height: 36px;
            }
            
            .oc-exclusiva-name {
                font-size: 12px;
            }
            
            .oc-exclusiva-btn {
                padding: 5px 10px;
                font-size: 10px;
            }
        }
    </style>
    <?php
}

/**
 * Render the pronósticos (predictions) section with blog posts
 */
function oc_render_pronosticos_section() {
    // Query recent blog posts
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 5,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $recent_posts = get_posts( $args );

    if ( empty( $recent_posts ) ) {
        return;
    }
    ?>
    <div class="sidebar-section oc-pronosticos-section">
        <h3 class="sidebar-title"><?php esc_html_e( 'Pronósticos', 'odds-comparison' ); ?></h3>
        <div class="oc-pronosticos-list">
            <?php foreach ( $recent_posts as $post ) : setup_postdata( $post ); ?>
            <article class="oc-pronostico-card modern-post-card">
                <div class="oc-post-left">
                    <div class="oc-post-thumbnail">
                        <a href="<?php the_permalink(); ?>">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'thumbnail', array( 'alt' => get_the_title() ) ); ?>
                            <?php else : ?>
                                <div class="oc-post-placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <circle cx="9" cy="9" r="2"/>
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
                <div class="oc-post-right">
                    <h4 class="oc-post-title">
                        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                            <?php echo wp_trim_words( get_the_title(), 8, '...' ); ?>
                        </a>
                    </h4>

                    <div class="oc-post-meta">
                        <time class="oc-post-date" datetime="<?php echo get_the_date( 'c' ); ?>">
                            <?php echo get_the_date( 'd/m/y' ); ?>
                        </time>
                        <?php if ( get_the_category() ) : ?>
                        <span class="oc-post-category">
                            <?php echo esc_html( get_the_category()[0]->name ); ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <a href="<?php the_permalink(); ?>" class="oc-read-more-link">
                        <?php esc_html_e( 'Leer más', 'odds-comparison' ); ?>
                        <span class="oc-arrow">→</span>
                    </a>
                </div>
            </article>
            <?php endforeach; wp_reset_postdata(); ?>
        </div>

        <div class="oc-view-all-container">
            <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="oc-view-all-link">
                <?php esc_html_e( 'Ver todos los pronósticos', 'odds-comparison' ); ?>
            </a>
        </div>
    </div>

    <style>
        .oc-pronosticos-section .modern-post-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #f0f0f0;
            display: flex;
        }

        .oc-pronosticos-section .modern-post-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .oc-post-left {
            flex-shrink: 0;
            width: 100px;
        }

        .oc-post-thumbnail {
            position: relative;
            overflow: hidden;
            width: 100%;
            height: 100px;
        }

        .oc-post-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .oc-pronosticos-section .modern-post-card:hover .oc-post-thumbnail img {
            transform: scale(1.05);
        }

        .oc-post-right {
            flex: 1;
            padding: 12px 15px;
        }

        .oc-post-title {
            margin: 0 0 8px 0;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.3;
        }

        .oc-post-title a {
            color: #333;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .oc-post-title a:hover {
            color: #007cba;
        }

        .oc-post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 11px;
            color: #666;
        }

        .oc-post-date {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 500;
        }

        .oc-post-category {
            background: #007cba;
            color: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .oc-read-more-link {
            display: inline-flex;
            align-items: center;
            font-size: 12px;
            font-weight: 600;
            color: #007cba;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: color 0.3s ease;
        }

        .oc-read-more-link:hover {
            color: #005a87;
        }

        .oc-arrow {
            margin-left: 4px;
            transition: transform 0.3s ease;
        }

        .oc-read-more-link:hover .oc-arrow {
            transform: translateX(3px);
        }

        .oc-view-all-container {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .oc-view-all-link {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(135deg, #007cba 0%, #005a87 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,123,186,0.3);
        }

        .oc-view-all-link:hover {
            background: linear-gradient(135deg, #005a87 0%, #004070 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,123,186,0.4);
        }

        @media (max-width: 768px) {
            .oc-pronosticos-section .modern-post-card {
                margin-bottom: 12px;
            }

            .oc-post-content {
                padding: 12px;
            }

            .oc-post-title {
                font-size: 13px;
            }
        }
    </style>
    <?php
}

/**
 * Render the apuestas (betting) section
 */
function oc_render_apuestas_section() {
    $apuestas = array(
        array(
            'name'      => 'Tonybet',
            'bonus'     => '100% hasta 250€',
            'logo'      => 'tonybet-logo.png',
            'url'       => '#',
        ),
        array(
            'name'      => 'Codere',
            'bonus'     => '100% hasta 200€',
            'logo'      => 'codere-logo.png',
            'url'       => '#',
        ),
        array(
            'name'      => 'LeoVegas',
            'bonus'     => '100% hasta 200€',
            'logo'      => 'leovegas-logo.png',
            'url'       => '#',
        ),
        array(
            'name'      => 'Casumo',
            'bonus'     => '50€ free bet',
            'logo'      => 'casumo-logo.png',
            'url'       => '#',
        ),
        array(
            'name'      => '888 Sports',
            'bonus'     => '10€ free + 100€ refund',
            'logo'      => '888-logo.png',
            'url'       => '#',
        ),
    );
    ?>
    <div class="sidebar-section oc-apuestas-section">
        <h3 class="sidebar-title"><?php esc_html_e( 'Apuestas', 'odds-comparison' ); ?></h3>
        <div class="oc-apuestas-list">
            <?php foreach ( $apuestas as $apuesta ) : ?>
            <div class="oc-apuesta-card">
                <div class="oc-apuesta-logo">
                    <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/bookmakers/' . $apuesta['logo'] ); ?>" alt="<?php echo esc_attr( $apuesta['name'] ); ?>">
                </div>
                <div class="oc-apuesta-info">
                    <span class="oc-bonus-amount"><?php echo esc_html( $apuesta['bonus'] ); ?></span>
                </div>
                <a href="<?php echo esc_url( $apuesta['url'] ); ?>" class="oc-btn oc-apuesta-btn">
                    <?php esc_html_e( 'Apuesta', 'odds-comparison' ); ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

