<?php
/**
 * About Page Template
 *
 * Template for displaying the about page with company information and team details.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();
?>

<div class="oc-about-container">
    <!-- Hero Section -->
    <section class="oc-about-hero">
        <div class="oc-hero-content">
            <h1><?php esc_html_e('About Odds Comparison', 'odds-comparison'); ?></h1>
            <p class="oc-hero-subtitle"><?php esc_html_e('Your trusted guide to finding the best sportsbooks and betting opportunities online.', 'odds-comparison'); ?></p>
            <div class="oc-hero-stats">
                <div class="oc-stat">
                    <span class="oc-stat-number">500+</span>
                    <span class="oc-stat-label"><?php esc_html_e('Sportsbooks Reviewed', 'odds-comparison'); ?></span>
                </div>
                <div class="oc-stat">
                    <span class="oc-stat-number">50K+</span>
                    <span class="oc-stat-label"><?php esc_html_e('Happy Users', 'odds-comparison'); ?></span>
                </div>
                <div class="oc-stat">
                    <span class="oc-stat-number">24/7</span>
                    <span class="oc-stat-label"><?php esc_html_e('Support Available', 'odds-comparison'); ?></span>
                </div>
            </div>
        </div>
        <div class="oc-hero-image">
            <img src="<?php echo esc_url(OC_PLUGIN_URL . 'assets/images/about-hero.svg'); ?>" alt="<?php esc_attr_e('About Odds Comparison', 'odds-comparison'); ?>" />
        </div>
    </section>

    <!-- Mission Section -->
    <section class="oc-about-mission">
        <div class="oc-container">
            <div class="oc-mission-content">
                <h2><?php esc_html_e('Our Mission', 'odds-comparison'); ?></h2>
                <p><?php esc_html_e('We believe that everyone deserves access to fair, transparent, and enjoyable sports betting experiences. Our mission is to empower bettors with comprehensive information, honest reviews, and powerful comparison tools to make informed decisions.', 'odds-comparison'); ?></p>

                <div class="oc-mission-values">
                    <div class="oc-value">
                        <div class="oc-value-icon">
                            <span class="dashicons dashicons-visibility"></span>
                        </div>
                        <h3><?php esc_html_e('Transparency', 'odds-comparison'); ?></h3>
                        <p><?php esc_html_e('We provide unbiased, honest reviews and clear information about all aspects of sports betting.', 'odds-comparison'); ?></p>
                    </div>

                    <div class="oc-value">
                        <div class="oc-value-icon">
                            <span class="dashicons dashicons-shield"></span>
                        </div>
                        <h3><?php esc_html_e('Safety First', 'odds-comparison'); ?></h3>
                        <p><?php esc_html_e('We only recommend licensed and regulated sportsbooks that prioritize responsible gambling.', 'odds-comparison'); ?></p>
                    </div>

                    <div class="oc-value">
                        <div class="oc-value-icon">
                            <span class="dashicons dashicons-chart-line"></span>
                        </div>
                        <h3><?php esc_html_e('Data-Driven', 'odds-comparison'); ?></h3>
                        <p><?php esc_html_e('Our recommendations are based on real user experiences, ratings, and comprehensive analysis.', 'odds-comparison'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- What We Do Section -->
    <section class="oc-about-services">
        <div class="oc-container">
            <div class="oc-section-header">
                <h2><?php esc_html_e('What We Do', 'odds-comparison'); ?></h2>
                <p><?php esc_html_e('We provide comprehensive tools and information to help you make the best betting decisions.', 'odds-comparison'); ?></p>
            </div>

            <div class="oc-services-grid">
                <div class="oc-service-card">
                    <div class="oc-service-icon">
                        <span class="dashicons dashicons-search"></span>
                    </div>
                    <h3><?php esc_html_e('Sportsbook Reviews', 'odds-comparison'); ?></h3>
                    <p><?php esc_html_e('In-depth reviews of sportsbooks covering bonuses, features, user experience, and more. Each review includes real user feedback and ratings.', 'odds-comparison'); ?></p>
                </div>

                <div class="oc-service-card">
                    <div class="oc-service-icon">
                        <span class="dashicons dashicons-randomize"></span>
                    </div>
                    <h3><?php esc_html_e('Comparison Tools', 'odds-comparison'); ?></h3>
                    <p><?php esc_html_e('Advanced filtering and comparison tools to help you find the perfect sportsbook based on your preferences and needs.', 'odds-comparison'); ?></p>
                </div>

                <div class="oc-service-card">
                    <div class="oc-service-icon">
                        <span class="dashicons dashicons-star-filled"></span>
                    </div>
                    <h3><?php esc_html_e('User Reviews', 'odds-comparison'); ?></h3>
                    <p><?php esc_html_e('Community-driven reviews and ratings from real users help you understand the actual experience of betting with different operators.', 'odds-comparison'); ?></p>
                </div>

                <div class="oc-service-card">
                    <div class="oc-service-icon">
                        <span class="dashicons dashicons-info"></span>
                    </div>
                    <h3><?php esc_html_e('Betting Guides', 'odds-comparison'); ?></h3>
                    <p><?php esc_html_e('Educational content about responsible gambling, betting strategies, and understanding odds to help you bet smarter.', 'odds-comparison'); ?></p>
                </div>

                <div class="oc-service-card">
                    <div class="oc-service-icon">
                        <span class="dashicons dashicons-update"></span>
                    </div>
                    <h3><?php esc_html_e('Latest Bonuses', 'odds-comparison'); ?></h3>
                    <p><?php esc_html_e('Stay updated with the latest bonus offers, promotions, and special deals from top sportsbooks.', 'odds-comparison'); ?></p>
                </div>

                <div class="oc-service-card">
                    <div class="oc-service-icon">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                    <h3><?php esc_html_e('Expert Support', 'odds-comparison'); ?></h3>
                    <p><?php esc_html_e('Our team of experts is available to answer your questions and provide guidance on all aspects of sports betting.', 'odds-comparison'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="oc-about-team">
        <div class="oc-container">
            <div class="oc-section-header">
                <h2><?php esc_html_e('Meet Our Team', 'odds-comparison'); ?></h2>
                <p><?php esc_html_e('Our experienced team is dedicated to providing you with the best sports betting information and tools.', 'odds-comparison'); ?></p>
            </div>

            <div class="oc-team-grid">
                <div class="oc-team-member">
                    <div class="oc-member-avatar">
                        <img src="<?php echo esc_url(OC_PLUGIN_URL . 'assets/images/team-member-1.jpg'); ?>" alt="John Smith" />
                    </div>
                    <div class="oc-member-info">
                        <h3>John Smith</h3>
                        <span class="oc-member-role"><?php esc_html_e('Founder & CEO', 'odds-comparison'); ?></span>
                        <p><?php esc_html_e('Former professional sports analyst with 15+ years of experience in the betting industry.', 'odds-comparison'); ?></p>
                    </div>
                </div>

                <div class="oc-team-member">
                    <div class="oc-member-avatar">
                        <img src="<?php echo esc_url(OC_PLUGIN_URL . 'assets/images/team-member-2.jpg'); ?>" alt="Sarah Johnson" />
                    </div>
                    <div class="oc-member-info">
                        <h3>Sarah Johnson</h3>
                        <span class="oc-member-role"><?php esc_html_e('Head of Reviews', 'odds-comparison'); ?></span>
                        <p><?php esc_html_e('Expert reviewer specializing in sportsbook analysis and user experience testing.', 'odds-comparison'); ?></p>
                    </div>
                </div>

                <div class="oc-team-member">
                    <div class="oc-member-avatar">
                        <img src="<?php echo esc_url(OC_PLUGIN_URL . 'assets/images/team-member-3.jpg'); ?>" alt="Mike Davis" />
                    </div>
                    <div class="oc-member-info">
                        <h3>Mike Davis</h3>
                        <span class="oc-member-role"><?php esc_html_e('Technical Director', 'odds-comparison'); ?></span>
                        <p><?php esc_html_e('Lead developer ensuring our tools and website provide the best user experience.', 'odds-comparison'); ?></p>
                    </div>
                </div>

                <div class="oc-team-member">
                    <div class="oc-member-avatar">
                        <img src="<?php echo esc_url(OC_PLUGIN_URL . 'assets/images/team-member-4.jpg'); ?>" alt="Emma Wilson" />
                    </div>
                    <div class="oc-member-info">
                        <h3>Emma Wilson</h3>
                        <span class="oc-member-role"><?php esc_html_e('Content Manager', 'odds-comparison'); ?></span>
                        <p><?php esc_html_e('Creates educational content and guides to help users understand sports betting better.', 'odds-comparison'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="oc-about-why-choose">
        <div class="oc-container">
            <div class="oc-why-choose-content">
                <div class="oc-why-choose-text">
                    <h2><?php esc_html_e('Why Choose Odds Comparison?', 'odds-comparison'); ?></h2>
                    <p><?php esc_html_e('We stand out from other betting review sites with our commitment to accuracy, transparency, and user experience.', 'odds-comparison'); ?></p>

                    <ul class="oc-why-choose-list">
                        <li><?php esc_html_e('Unbiased reviews based on real user experiences', 'odds-comparison'); ?></li>
                        <li><?php esc_html_e('Advanced comparison tools to find your perfect match', 'odds-comparison'); ?></li>
                        <li><?php esc_html_e('Regular updates of bonus offers and promotions', 'odds-comparison'); ?></li>
                        <li><?php esc_html_e('Expert analysis and educational content', 'odds-comparison'); ?></li>
                        <li><?php esc_html_e('Mobile-friendly design for betting on the go', 'odds-comparison'); ?></li>
                        <li><?php esc_html_e('Dedicated support team for all your questions', 'odds-comparison'); ?></li>
                    </ul>
                </div>

                <div class="oc-why-choose-image">
                    <img src="<?php echo esc_url(OC_PLUGIN_URL . 'assets/images/why-choose-us.svg'); ?>" alt="<?php esc_attr_e('Why Choose Us', 'odds-comparison'); ?>" />
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="oc-about-cta">
        <div class="oc-container">
            <div class="oc-cta-content">
                <h2><?php esc_html_e('Ready to Find Your Perfect Sportsbook?', 'odds-comparison'); ?></h2>
                <p><?php esc_html_e('Use our comparison tool to discover the best sportsbooks for your betting needs.', 'odds-comparison'); ?></p>
                <div class="oc-cta-buttons">
                    <a href="<?php echo esc_url(home_url('/sportsbook-comparison')); ?>" class="oc-button oc-button-primary">
                        <?php esc_html_e('Compare Sportsbooks', 'odds-comparison'); ?>
                    </a>
                    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="oc-button oc-button-secondary">
                        <?php esc_html_e('Contact Us', 'odds-comparison'); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>
