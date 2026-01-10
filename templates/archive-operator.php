<?php
/**
 * Operators Archive Template
 *
 * Template for displaying archive of betting operators.
 * Enhanced with bonus type, payment method, and additional filters.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

// Get all licenses for filter
$licenses = get_terms( array( 'taxonomy' => 'license', 'hide_empty' => true ) );

// Define bonus types for filter
$bonus_types = array(
    'deposit_match' => __( 'Deposit Match', 'odds-comparison' ),
    'free_bet'      => __( 'Free Bet', 'odds-comparison' ),
    'no_deposit'    => __( 'No Deposit Bonus', 'odds-comparison' ),
    'enhanced'      => __( 'Enhanced Odds', 'odds-comparison' ),
    'cashback'      => __( 'Cashback', 'odds-comparison' ),
    'other'         => __( 'Other', 'odds-comparison' ),
);

// Common payment methods for filter
$payment_methods = array(
    'visa'         => __( 'Visa', 'odds-comparison' ),
    'mastercard'   => __( 'Mastercard', 'odds-comparison' ),
    'paypal'       => __( 'PayPal', 'odds-comparison' ),
    'skrill'       => __( 'Skrill', 'odds-comparison' ),
    'neteller'     => __( 'Neteller', 'odds-comparison' ),
    'paysafecard'  => __( 'Paysafecard', 'odds-comparison' ),
    'bank_transfer' => __( 'Bank Transfer', 'odds-comparison' ),
    'apple_pay'    => __( 'Apple Pay', 'odds-comparison' ),
    'google_pay'   => __( 'Google Pay', 'odds-comparison' ),
);

// Handle filtering via query params for SEO-friendly URLs
$current_filters = array(
    'license'      => isset( $_GET['license'] ) ? sanitize_text_field( $_GET['license'] ) : '',
    'bonus_type'   => isset( $_GET['bonus_type'] ) ? sanitize_text_field( $_GET['bonus_type'] ) : '',
    'payment'      => isset( $_GET['payment'] ) ? sanitize_text_field( $_GET['payment'] ) : '',
    'min_rating'   => isset( $_GET['min_rating'] ) ? floatval( $_GET['min_rating'] ) : 0,
    'sort'         => isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : 'rating',
);
?>

<div class="oc-operators-archive">
    <header class="oc-archive-header">
        <h1 class="oc-archive-title"><?php esc_html_e( 'Betting Operators', 'odds-comparison' ); ?></h1>
        <div class="oc-archive-description">
            <p><?php esc_html_e( 'Compare the best betting operators, their bonuses, and features.', 'odds-comparison' ); ?></p>
        </div>
    </header>
    
    <form id="oc-operators-filter-form" class="oc-archive-filters" method="get" action="">
        <div class="oc-filter-row">
            <div class="oc-filter-group">
                <label for="oc-filter-license"><?php esc_html_e( 'License:', 'odds-comparison' ); ?></label>
                <select id="oc-filter-license" name="license" class="oc-select">
                    <option value=""><?php esc_html_e( 'All Licenses', 'odds-comparison' ); ?></option>
                    <?php foreach ( $licenses as $license ) : ?>
                        <option value="<?php echo esc_attr( $license->slug ); ?>" <?php selected( $current_filters['license'], $license->slug ); ?>>
                            <?php echo esc_html( $license->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="oc-filter-group">
                <label for="oc-filter-bonus-type"><?php esc_html_e( 'Bonus Type:', 'odds-comparison' ); ?></label>
                <select id="oc-filter-bonus-type" name="bonus_type" class="oc-select">
                    <option value=""><?php esc_html_e( 'All Bonus Types', 'odds-comparison' ); ?></option>
                    <?php foreach ( $bonus_types as $bonus_slug => $bonus_label ) : ?>
                        <option value="<?php echo esc_attr( $bonus_slug ); ?>" <?php selected( $current_filters['bonus_type'], $bonus_slug ); ?>>
                            <?php echo esc_html( $bonus_label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="oc-filter-group">
                <label for="oc-filter-payment"><?php esc_html_e( 'Payment Method:', 'odds-comparison' ); ?></label>
                <select id="oc-filter-payment" name="payment" class="oc-select">
                    <option value=""><?php esc_html_e( 'All Methods', 'odds-comparison' ); ?></option>
                    <?php foreach ( $payment_methods as $pm_slug => $pm_label ) : ?>
                        <option value="<?php echo esc_attr( $pm_slug ); ?>" <?php selected( $current_filters['payment'], $pm_slug ); ?>>
                            <?php echo esc_html( $pm_label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="oc-filter-group">
                <label for="oc-filter-rating"><?php esc_html_e( 'Min Rating:', 'odds-comparison' ); ?></label>
                <select id="oc-filter-rating" name="min_rating" class="oc-select">
                    <option value="0" <?php selected( $current_filters['min_rating'], 0 ); ?>><?php esc_html_e( 'Any', 'odds-comparison' ); ?></option>
                    <option value="4" <?php selected( $current_filters['min_rating'], 4 ); ?>><?php esc_html_e( '4+ Stars', 'odds-comparison' ); ?></option>
                    <option value="4.5" <?php selected( $current_filters['min_rating'], 4.5 ); ?>><?php esc_html_e( '4.5+ Stars', 'odds-comparison' ); ?></option>
                    <option value="4.8" <?php selected( $current_filters['min_rating'], 4.8 ); ?>><?php esc_html_e( '4.8+ Stars', 'odds-comparison' ); ?></option>
                </select>
            </div>
            
            <div class="oc-filter-group">
                <label for="oc-filter-sort"><?php esc_html_e( 'Sort by:', 'odds-comparison' ); ?></label>
                <select id="oc-filter-sort" name="sort" class="oc-select">
                    <option value="rating" <?php selected( $current_filters['sort'], 'rating' ); ?>><?php esc_html_e( 'Highest Rated', 'odds-comparison' ); ?></option>
                    <option value="name" <?php selected( $current_filters['sort'], 'name' ); ?>><?php esc_html_e( 'Name A-Z', 'odds-comparison' ); ?></option>
                    <option value="bonus" <?php selected( $current_filters['sort'], 'bonus' ); ?>><?php esc_html_e( 'Bonus Amount', 'odds-comparison' ); ?></option>
                    <option value="newest" <?php selected( $current_filters['sort'], 'newest' ); ?>><?php esc_html_e( 'Newest', 'odds-comparison' ); ?></option>
                </select>
            </div>
            
            <div class="oc-filter-actions">
                <button type="submit" class="button oc-filter-btn">
                    <span class="dashicons dashicons-filter"></span>
                    <?php esc_html_e( 'Apply Filters', 'odds-comparison' ); ?>
                </button>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'operator' ) ); ?>" class="button oc-reset-btn">
                    <?php esc_html_e( 'Reset', 'odds-comparison' ); ?>
                </a>
            </div>
        </div>
        
        <!-- Active filters display -->
        <?php if ( $current_filters['license'] || $current_filters['bonus_type'] || $current_filters['payment'] || $current_filters['min_rating'] > 0 ) : ?>
        <div class="oc-active-filters">
            <span class="oc-active-label"><?php esc_html_e( 'Active filters:', 'odds-comparison' ); ?></span>
            <?php if ( $current_filters['license'] ) : ?>
                <?php $license_term = get_term_by( 'slug', $current_filters['license'], 'license' ); ?>
                <span class="oc-active-filter">
                    <?php echo esc_html( $license_term->name ); ?>
                    <a href="<?php echo esc_url( remove_query_arg( 'license' ) ); ?>" class="oc-remove-filter">&times;</a>
                </span>
            <?php endif; ?>
            <?php if ( $current_filters['bonus_type'] ) : ?>
                <span class="oc-active-filter">
                    <?php echo esc_html( $bonus_types[ $current_filters['bonus_type'] ] ); ?>
                    <a href="<?php echo esc_url( remove_query_arg( 'bonus_type' ) ); ?>" class="oc-remove-filter">&times;</a>
                </span>
            <?php endif; ?>
            <?php if ( $current_filters['payment'] ) : ?>
                <span class="oc-active-filter">
                    <?php echo esc_html( $payment_methods[ $current_filters['payment'] ] ); ?>
                    <a href="<?php echo esc_url( remove_query_arg( 'payment' ) ); ?>" class="oc-remove-filter">&times;</a>
                </span>
            <?php endif; ?>
            <?php if ( $current_filters['min_rating'] > 0 ) : ?>
                <span class="oc-active-filter">
                    <?php printf( esc_html__( '%s+ Stars', 'odds-comparison' ), esc_html( $current_filters['min_rating'] ) ); ?>
                    <a href="<?php echo esc_url( remove_query_arg( 'min_rating' ) ); ?>" class="oc-remove-filter">&times;</a>
                </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </form>
    
    <div id="oc-operators-list" class="oc-operators-grid">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <?php
                $operator_id = get_the_ID();
                $rating = get_post_meta( $operator_id, 'oc_operator_rating', true );
                $review_count = get_post_meta( $operator_id, 'oc_review_count', true );
                $bonus_amount = get_post_meta( $operator_id, 'oc_bonus_amount', true );
                $bonus_type = get_post_meta( $operator_id, 'oc_bonus_type', true );
                $affiliate_url = get_post_meta( $operator_id, 'oc_affiliate_url', true );
                $is_featured = get_post_meta( $operator_id, 'oc_featured_operator', true );
                $pros = get_post_meta( $operator_id, 'oc_operator_pros', true );
                ?>
                
                <article class="oc-operator-card <?php echo $is_featured ? 'featured' : ''; ?>">
                    <?php if ( $is_featured ) : ?>
                        <div class="oc-card-badge oc-featured">
                            <?php esc_html_e( 'Recommended', 'odds-comparison' ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="oc-operator-logo">
                        <?php if ( has_post_thumbnail( $operator_id ) ) : ?>
                            <?php the_post_thumbnail( 'medium', array( 'alt' => get_the_title() ) ); ?>
                        <?php else : ?>
                            <span class="oc-logo-fallback"><?php echo esc_html( get_the_title()[0] ); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="oc-operator-info">
                        <h2 class="oc-operator-name">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        
                        <div class="oc-operator-rating">
                            <?php if ( $rating ) : ?>
                                <div class="oc-stars">
                                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                        <span class="star <?php echo $i <= round( $rating ) ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="oc-rating-value"><?php echo esc_html( number_format( $rating, 1 ) ); ?></span>
                                <?php if ( $review_count ) : ?>
                                    <span class="oc-review-count">(<?php echo esc_html( $review_count ); ?>)</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ( $bonus_amount ) : ?>
                            <div class="oc-operator-bonus">
                                <span class="oc-bonus-amount"><?php echo esc_html( $bonus_amount ); ?></span>
                                <?php if ( $bonus_type ) : ?>
                                    <span class="oc-bonus-type"><?php echo esc_html( ucfirst( $bonus_type ) ); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $pros ) && is_array( $pros ) ) : ?>
                            <ul class="oc-operator-pros-mini">
                                <?php foreach ( array_slice( $pros, 0, 3 ) as $pro ) : ?>
                                    <?php if ( $pro ) : ?>
                                        <li><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html( $pro ); ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    
                    <div class="oc-operator-actions">
                        <?php if ( $affiliate_url ) : ?>
                            <a href="<?php echo esc_url( $affiliate_url ); ?>" class="button oc-visit-btn" target="_blank" rel="nofollow">
                                <?php esc_html_e( 'Visit Site', 'odds-comparison' ); ?>
                            </a>
                        <?php endif; ?>
                        <a href="<?php the_permalink(); ?>" class="oc-review-btn">
                            <?php esc_html_e( 'Read Review', 'odds-comparison' ); ?>
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
            
            <?php the_posts_navigation(); ?>
        <?php else : ?>
            <div class="oc-no-operators">
                <p><?php esc_html_e( 'No operators found.', 'odds-comparison' ); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>

