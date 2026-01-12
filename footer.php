<?php
/**
 * The footer template file
 * 
 * Contains the closing tags and footer content
 * 
 * @package Odds_Comparison
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

    </div><!-- .site-content -->
    
    <?php
    // Hook for content before footer
    do_action( 'oc_before_footer' );
    ?>
    
    <footer id="colophon" class="site-footer">
        
        <!-- Responsible Gaming Banner -->
        <div class="oc-responsible-gaming">
            <div class="container">
                <div class="oc-rg-banner">
                    <div class="oc-rg-badge">
                        <span class="oc-age-badge">18+</span>
                        <span class="oc-rg-text"><?php esc_html_e( 'Play Responsibly', 'odds-comparison' ); ?></span>
                    </div>
                    <div class="oc-rg-content">
                        <p class="oc-rg-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <strong><?php esc_html_e( 'Gambling involves risk. Please bet responsibly.', 'odds-comparison' ); ?></strong>
                            <?php esc_html_e( 'Only bet what you can afford to lose. If you or someone you know has a gambling problem, please seek help.', 'odds-comparison' ); ?>
                        </p>
                        <div class="oc-rg-links">
                            <a href="#" class="oc-rg-link"><?php esc_html_e( 'Self-prohibition', 'odds-comparison' ); ?></a>
                            <a href="#" class="oc-rg-link"><?php esc_html_e( 'Problems with gambling?', 'odds-comparison' ); ?></a>
                            <a href="#" class="oc-rg-link"><?php esc_html_e( 'Get help', 'odds-comparison' ); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Footer Links -->
        <div class="oc-footer-main">
            <div class="container">
                <div class="oc-footer-grid">
                    
                    <!-- Column 1: Responsible Gaming Info -->
                    <div class="oc-footer-col oc-footer-rg">
                        <div class="oc-footer-logo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 16v-4"></path>
                                <path d="M12 8h.01"></path>
                            </svg>
                            <span><?php bloginfo( 'name' ); ?></span>
                        </div>
                        <p class="oc-footer-desc">
                            <?php esc_html_e( 'Your trusted source for sports betting odds comparison. Find the best odds from licensed bookmakers worldwide.', 'odds-comparison' ); ?>
                        </p>
                        <div class="oc-rg-resources">
                            <h4><?php esc_html_e( 'Responsible Gaming', 'odds-comparison' ); ?></h4>
                            <ul>
                                <li><a href="#"><?php esc_html_e( 'Over 18 years old', 'odds-comparison' ); ?></a></li>
                                <li><a href="#"><?php esc_html_e( 'Self-exclusion', 'odds-comparison' ); ?></a></li>
                                <li><a href="#"><?php esc_html_e( 'Problem gambling help', 'odds-comparison' ); ?></a></li>
                                <li><a href="#"><?php esc_html_e( 'Illegal gambling warning', 'odds-comparison' ); ?></a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Column 2: Betting Shops -->
                    <div class="oc-footer-col">
                        <h3 class="oc-footer-title"><?php esc_html_e( 'Betting Shops', 'odds-comparison' ); ?></h3>
                        <ul class="oc-footer-links">
                            <li><a href="#"><?php esc_html_e( 'Betting Shops in Spain', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Betting Houses Mexico', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Betting Houses in Peru', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Betting Houses in Colombia', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Betting Houses in Chile', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Betting Houses in Ecuador', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Betting Houses in Panama', 'odds-comparison' ); ?></a></li>
                        </ul>
                    </div>
                    
                    <!-- Column 3: Opinions & Analysis -->
                    <div class="oc-footer-col">
                        <h3 class="oc-footer-title"><?php esc_html_e( 'Opinions & Analysis', 'odds-comparison' ); ?></h3>
                        <ul class="oc-footer-links">
                            <li><a href="#"><?php esc_html_e( 'Betfair', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'MarathonBet', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Bet365', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Codere', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'William Hill', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( '888Casino', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Betway', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Bizum Casinos', 'odds-comparison' ); ?></a></li>
                        </ul>
                    </div>
                    
                    <!-- Column 4: Casinos & Games -->
                    <div class="oc-footer-col">
                        <h3 class="oc-footer-title"><?php esc_html_e( 'Casinos & Games', 'odds-comparison' ); ?></h3>
                        <ul class="oc-footer-links">
                            <li><a href="#"><?php esc_html_e( 'Online Casinos Spain', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Online Casinos Mexico', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Live Casinos Spain', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Online Casinos Peru', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Real Money Casinos', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Online Slot Machines', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Online Roulette', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Online Blackjack', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Casino Bonuses', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'No Deposit Bonuses', 'odds-comparison' ); ?></a></li>
                        </ul>
                    </div>
                    
                    <!-- Column 5: Main Sports & Events -->
                    <div class="oc-footer-col">
                        <h3 class="oc-footer-title"><?php esc_html_e( 'Main Sports', 'odds-comparison' ); ?></h3>
                        <ul class="oc-footer-links">
                            <li><a href="#"><?php esc_html_e( 'Bet on today\'s matches', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Football Betting', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Europa League Final', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Champions League betting', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'LaLiga betting', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'NBA Betting', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Tennis Betting', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Boxing-MMA Betting', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Formula 1 betting', 'odds-comparison' ); ?></a></li>
                        </ul>
                    </div>
                    
                </div>
                
                <!-- Secondary Links Row -->
                <!-- <div class="oc-footer-secondary">
                    <div class="oc-secondary-col">
                        <h3 class="oc-footer-title"><?php esc_html_e( 'Latest Forecasts', 'odds-comparison' ); ?></h3>
                        <ul class="oc-footer-links">
                            <li><a href="#"><?php esc_html_e( 'Atlético Madrid vs Real Madrid', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Arsenal vs Liverpool', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'PSG vs Marseille', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Milan vs Genoa', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'The Quiniela - Matchday 33', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Progol Prediction', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Ganagol', 'odds-comparison' ); ?></a></li>
                        </ul>
                    </div>
                    <div class="oc-secondary-col">
                        <h3 class="oc-footer-title"><?php esc_html_e( 'Free Bets & Bonuses', 'odds-comparison' ); ?></h3>
                        <ul class="oc-footer-links">
                            <li><a href="#"><?php esc_html_e( 'Welcome Bonuses', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Sports Betting', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Paypal Casinos', 'odds-comparison' ); ?></a></li>
                            <li><a href="#"><?php esc_html_e( 'Paysafecard Casinos', 'odds-comparison' ); ?></a></li>
                        </ul>
                    </div>
                </div> -->
                
                <!-- Social Media -->
                <div class="oc-social-section">
                    <div class="oc-social-content">
                        <h3><?php esc_html_e( 'Follow Us', 'odds-comparison' ); ?></h3>
                        <p><?php esc_html_e( 'Stay updated with the latest odds and betting tips.', 'odds-comparison' ); ?></p>
                    </div>
                    <div class="oc-social-icons">
                        <a href="#" class="oc-social-link" aria-label="<?php esc_attr_e( 'Facebook', 'odds-comparison' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                        </a>
                        <a href="#" class="oc-social-link" aria-label="<?php esc_attr_e( 'Twitter/X', 'odds-comparison' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4l11.733 16H20L8.267 4H4zm0 16l6-8m4-8l6 8"></path>
                            </svg>
                        </a>
                        <a href="#" class="oc-social-link" aria-label="<?php esc_attr_e( 'Instagram', 'odds-comparison' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                            </svg>
                        </a>
                        <a href="#" class="oc-social-link" aria-label="<?php esc_attr_e( 'YouTube', 'odds-comparison' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                                <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                            </svg>
                        </a>
                        <a href="#" class="oc-social-link" aria-label="<?php esc_attr_e( 'Telegram', 'odds-comparison' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21.198 2.692a1 1 0 0 0-1.012-.156l-18.4 7.4a1 1 0 0 0 .092 1.856l4.5 1.4 1.8 5.8a1 1 0 0 0 1.684.21l2.5-3.1a1 1 0 0 1 .6-.3l5.3-.4a1 1 0 0 0 .5-.2l2.7-2.7a1 1 0 0 0 .147-.597l-1.7-6.1a1 1 0 0 0-.64-.59z"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="oc-footer-bottom">
            <div class="container">
                <div class="oc-footer-bottom-inner">
                    
                    <!-- Logo & Copyright -->
                    <div class="oc-bottom-left">
                        <div class="oc-bottom-logo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 16v-4"></path>
                                <path d="M12 8h.01"></path>
                            </svg>
                            <span><?php bloginfo( 'name' ); ?></span>
                        </div>
                        <p class="oc-bottom-copyright">
                            &copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'odds-comparison' ); ?>
                        </p>
                    </div>
                    
                    <!-- Legal Links -->
                    <div class="oc-bottom-right">
                        <nav class="oc-legal-nav" aria-label="<?php esc_attr_e( 'Legal Links', 'odds-comparison' ); ?>">
                            <a href="#"><?php esc_html_e( 'Who we are', 'odds-comparison' ); ?></a>
                            <a href="#"><?php esc_html_e( 'Terms and conditions', 'odds-comparison' ); ?></a>
                            <a href="#"><?php esc_html_e( 'Cookies and Privacy Policy', 'odds-comparison' ); ?></a>
                            <a href="#"><?php esc_html_e( 'Contact', 'odds-comparison' ); ?></a>
                        </nav>
                    </div>
                    
                </div>
                
                <!-- Age Verification Badge -->
                <div class="oc-age-verification">
                    <span class="oc-age-badge-large">18+</span>
                    <span><?php esc_html_e( 'Play responsibly. 18+ only.', 'odds-comparison' ); ?></span>
                </div>
                
            </div>
        </div>
        
    </footer>
    
    <?php
    // Hook for content after footer
    do_action( 'oc_after_footer' );
    ?>
    
    <!-- Back to Top Button -->
    <button id="back-to-top" class="back-to-top" aria-label="<?php esc_attr_e( 'Back to top', 'odds-comparison' ); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15"></polyline>
        </svg>
    </button>
    
</div><!-- .site-wrapper -->

<?php wp_footer(); ?>

</body>
</html>

