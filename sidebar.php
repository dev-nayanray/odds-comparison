<?php
/**
 * The sidebar template file
 *
 * Displays the sidebar with registration, newsletter, pronósticos and apuestas sections
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
        
        // Pronósticos Section
        oc_render_pronosticos_section();
        
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
            <a href="<?php echo esc_url( wp_login_url() ); ?>" class="oc-btn oc-login-btn-sidebar">
                <?php esc_html_e( 'Iniciar sesión', 'odds-comparison' ); ?>
            </a>
            <a href="<?php echo esc_url( wp_registration_url() ); ?>" class="oc-btn oc-register-btn-sidebar">
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
 * Render the pronósticos (predictions) section
 */
function oc_render_pronosticos_section() {
    $pronosticos = array(
        array(
            'home_team' => 'Atlético Madrid',
            'away_team' => 'Real Madrid',
            'date'      => '08/01/26',
            'prediction'=> __( 'Gana Real Madrid', 'odds-comparison' ),
            'odds'      => '2.10',
        ),
        array(
            'home_team' => 'Arsenal',
            'away_team' => 'Liverpool',
            'date'      => '08/01/26',
            'prediction'=> __( 'Gana Arsenal', 'odds-comparison' ),
            'odds'      => '2.30',
        ),
        array(
            'home_team' => 'PSG',
            'away_team' => 'Marseille',
            'date'      => '08/01/26',
            'prediction'=> __( 'Gana PSG', 'odds-comparison' ),
            'odds'      => '1.75',
        ),
        array(
            'home_team' => 'Milan',
            'away_team' => 'Genoa',
            'date'      => '08/01/26',
            'prediction'=> __( 'Gana Milan', 'odds-comparison' ),
            'odds'      => '1.65',
        ),
        array(
            'home_team' => 'Barcelona',
            'away_team' => 'Athletic Club',
            'date'      => '07/01/26',
            'prediction'=> __( 'Gana Barcelona', 'odds-comparison' ),
            'odds'      => '1.55',
        ),
    );
    ?>
    <div class="sidebar-section oc-pronosticos-section">
        <h3 class="sidebar-title"><?php esc_html_e( 'Pronósticos', 'odds-comparison' ); ?></h3>
        <div class="oc-pronosticos-list">
            <?php foreach ( $pronosticos as $pronostico ) : ?>
            <div class="oc-pronostico-card">
                <div class="oc-pronostico-match">
                    <span class="oc-team"><?php echo esc_html( $pronostico['home_team'] ); ?></span>
                    <span class="oc-vs"><?php esc_html_e( 'vs', 'odds-comparison' ); ?></span>
                    <span class="oc-team"><?php echo esc_html( $pronostico['away_team'] ); ?></span>
                </div>
                <div class="oc-pronostico-date"><?php echo esc_html( $pronostico['date'] ); ?></div>
                <div class="oc-pronostico-prediction">
                    <span class="oc-prediction-label"><?php esc_html_e( 'Pronóstico', 'odds-comparison' ); ?></span>
                    <span class="oc-prediction-value"><?php echo esc_html( $pronostico['prediction'] ); ?></span>
                    <span class="oc-prediction-odds">@ <?php echo esc_html( $pronostico['odds'] ); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <a href="<?php echo esc_url( home_url( '/pronosticos' ) ); ?>" class="oc-view-all-link">
            <?php esc_html_e( 'Ver más', 'odds-comparison' ); ?>
        </a>
    </div>
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
                    <img src="<?php echo esc_url( OC_ASSETS_URI . '/images/bookmakers/' . $apuesta['logo'] ); ?>" alt="<?php echo esc_attr( $apuesta['name'] ); ?>">
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

