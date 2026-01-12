<?php
/**
 * Enhanced Operator Single Template
 *
 * Template for displaying detailed operator review pages with live bonuses.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

$operator_id = get_the_ID();

// Get operator data
$rating = get_post_meta( $operator_id, 'oc_operator_rating', true );
$review_count = get_post_meta( $operator_id, 'oc_review_count', true );
$bonus_amount = get_post_meta( $operator_id, 'oc_bonus_amount', true );
$bonus_type = get_post_meta( $operator_id, 'oc_bonus_type', true );
$bonus_description = get_post_meta( $operator_id, 'oc_bonus_description', true );
$bonus_code = get_post_meta( $operator_id, 'oc_bonus_code', true );
$affiliate_url = get_post_meta( $operator_id, 'oc_affiliate_url', true );
$min_deposit = get_post_meta( $operator_id, 'oc_min_deposit', true );
$wagering_requirement = get_post_meta( $operator_id, 'oc_wagering_requirement', true );
$bonus_expiry = get_post_meta( $operator_id, 'oc_bonus_expiry', true );
$bonus_last_updated = get_post_meta( $operator_id, 'oc_bonus_last_updated', true );

$pros = get_post_meta( $operator_id, 'oc_operator_pros', true );
$cons = get_post_meta( $operator_id, 'oc_operator_cons', true );

$live_betting = get_post_meta( $operator_id, 'oc_live_betting', true );
$cash_out = get_post_meta( $operator_id, 'oc_cash_out', true );
$mobile_app = get_post_meta( $operator_id, 'oc_mobile_app', true );
$live_streaming = get_post_meta( $operator_id, 'oc_live_streaming', true );
$payment_methods = get_post_meta( $operator_id, 'oc_payment_methods', true );
$sports_supported = get_post_meta( $operator_id, 'oc_sports_supported', true );

$licenses = wp_get_post_terms( $operator_id, 'license', array( 'fields' => 'names' ) );

// Get user reviews
$user_reviews = get_comments( array(
    'post_id' => $operator_id,
    'status' => 'approve',
    'type' => 'operator_review'
) );

// Calculate average rating from reviews
$review_ratings = array();
foreach ( $user_reviews as $review ) {
    $review_rating = get_comment_meta( $review->comment_ID, 'rating', true );
    if ( $review_rating ) {
        $review_ratings[] = $review_rating;
    }
}
$average_review_rating = !empty( $review_ratings ) ? array_sum( $review_ratings ) / count( $review_ratings ) : 0;
?>

<div class="oc-operator-container">
    <div class="oc-operator-header">
        <div class="oc-operator-logo">
            <?php if ( has_post_thumbnail( $operator_id ) ) : ?>
                <?php the_post_thumbnail( 'medium', array( 'alt' => get_the_title() ) ); ?>
            <?php else : ?>
                <h1><?php the_title(); ?></h1>
            <?php endif; ?>
        </div>
        
        <div class="oc-operator-info">
            <h1 class="oc-operator-title"><?php the_title(); ?></h1>
            
            <div class="oc-operator-rating">
                <?php if ( $rating ) : ?>
                    <div class="oc-stars">
                        <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                            <span class="star <?php echo $i <= round( $rating ) ? 'filled' : ''; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="oc-rating-value"><?php echo esc_html( number_format( $rating, 1 ) ); ?></span>
                    <?php if ( $review_count ) : ?>
                        <span class="oc-review-count">(<?php echo esc_html( $review_count ); ?> <?php esc_html_e( 'reviews', 'odds-comparison' ); ?>)</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <?php if ( ! empty( $licenses ) ) : ?>
                <div class="oc-operator-licenses">
                    <?php foreach ( $licenses as $license ) : ?>
                        <span class="oc-license-badge"><?php echo esc_html( $license ); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="oc-operator-features">
                <?php if ( $live_betting ) : ?>
                    <span class="oc-feature"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Live Betting', 'odds-comparison' ); ?></span>
                <?php endif; ?>
                <?php if ( $cash_out ) : ?>
                    <span class="oc-feature"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Cash Out', 'odds-comparison' ); ?></span>
                <?php endif; ?>
                <?php if ( $mobile_app ) : ?>
                    <span class="oc-feature"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Mobile App', 'odds-comparison' ); ?></span>
                <?php endif; ?>
                <?php if ( $live_streaming ) : ?>
                    <span class="oc-feature"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Live Streaming', 'odds-comparison' ); ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="oc-operator-cta">
            <?php if ( $affiliate_url ) : ?>
                <a href="<?php echo esc_url( $affiliate_url ); ?>" class="button oc-visit-btn" target="_blank" rel="nofollow">
                    <?php esc_html_e( 'Visit Site', 'odds-comparison' ); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ( $bonus_amount ) : ?>
        <div class="oc-operator-bonus">
            <div class="oc-bonus-card">
                <div class="oc-bonus-amount"><?php echo esc_html( $bonus_amount ); ?></div>
                <?php if ( $bonus_type ) : ?>
                    <div class="oc-bonus-type"><?php echo esc_html( ucfirst( $bonus_type ) ); ?> <?php esc_html_e( 'Bonus', 'odds-comparison' ); ?></div>
                <?php endif; ?>
                <?php if ( $bonus_code ) : ?>
                    <div class="oc-bonus-code">
                        <span><?php esc_html_e( 'Use code:', 'odds-comparison' ); ?></span>
                        <code><?php echo esc_html( $bonus_code ); ?></code>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ( $bonus_description || $min_deposit || $wagering_requirement ) : ?>
                <div class="oc-bonus-details">
                    <?php if ( $bonus_description ) : ?>
                        <div class="oc-bonus-description"><?php echo wp_kses_post( nl2br( $bonus_description ) ); ?></div>
                    <?php endif; ?>
                    <ul class="oc-bonus-terms">
                        <?php if ( $min_deposit ) : ?>
                            <li><?php printf( esc_html__( 'Minimum Deposit: %s', 'odds-comparison' ), esc_html( $min_deposit ) ); ?></li>
                        <?php endif; ?>
                        <?php if ( $wagering_requirement ) : ?>
                            <li><?php printf( esc_html__( 'Wagering: %s', 'odds-comparison' ), esc_html( $wagering_requirement ) ); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Navigation Tabs -->
    <div class="oc-operator-tabs">
        <button class="oc-tab-button active" data-tab="overview"><?php esc_html_e( 'Overview', 'odds-comparison' ); ?></button>
        <button class="oc-tab-button" data-tab="bonuses"><?php esc_html_e( 'Bonuses', 'odds-comparison' ); ?></button>
        <button class="oc-tab-button" data-tab="reviews"><?php esc_html_e( 'Reviews', 'odds-comparison' ); ?></button>
        <button class="oc-tab-button" data-tab="sports"><?php esc_html_e( 'Sports & Betting', 'odds-comparison' ); ?></button>
    </div>

    <div class="oc-operator-content">
        <!-- Overview Tab -->
        <div id="overview-tab" class="oc-tab-content active">
            <div class="oc-operator-main">
                <div class="oc-operator-description">
                    <h2><?php esc_html_e( 'About', 'odds-comparison' ); ?> <?php the_title(); ?></h2>
                    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                        <?php the_content(); ?>
                    <?php endwhile; endif; ?>
                </div>

                <?php if ( ( is_array( $pros ) && ! empty( $pros ) ) || ( is_array( $cons ) && ! empty( $cons ) ) ) : ?>
                    <div class="oc-operator-pros-cons">
                        <div class="oc-pros">
                            <h3><?php esc_html_e( 'Pros', 'odds-comparison' ); ?></h3>
                            <ul>
                                <?php if ( is_array( $pros ) ) : ?>
                                    <?php foreach ( $pros as $pro ) : ?>
                                        <?php if ( $pro ) : ?>
                                            <li><span class="dashicons dashicons-yes-alt" style="color: #27ae60;"></span> <?php echo esc_html( $pro ); ?></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="oc-cons">
                            <h3><?php esc_html_e( 'Cons', 'odds-comparison' ); ?></h3>
                            <ul>
                                <?php if ( is_array( $cons ) ) : ?>
                                    <?php foreach ( $cons as $con ) : ?>
                                        <?php if ( $con ) : ?>
                                            <li><span class="dashicons dashicons-dismiss" style="color: #e74c3c;"></span> <?php echo esc_html( $con ); ?></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Payment Methods Section -->
                <?php if ( is_array( $payment_methods ) && ! empty( $payment_methods ) ) : ?>
                    <div class="oc-payment-methods">
                        <h3><?php esc_html_e( 'Payment Methods', 'odds-comparison' ); ?></h3>
                        <div class="oc-payment-grid">
                            <?php foreach ( $payment_methods as $method ) : ?>
                                <?php if ( $method ) : ?>
                                    <span class="oc-payment-method"><?php echo esc_html( $method ); ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="oc-operator-sidebar">
                <!-- Quick Stats -->
                <div class="oc-sidebar-widget">
                    <h4><?php esc_html_e( 'Quick Stats', 'odds-comparison' ); ?></h4>
                    <ul class="oc-stats-list">
                        <?php if ( $rating ) : ?>
                            <li><strong><?php esc_html_e( 'Rating:', 'odds-comparison' ); ?></strong> <?php echo esc_html( number_format( $rating, 1 ) ); ?>/5</li>
                        <?php endif; ?>
                        <?php if ( $review_count ) : ?>
                            <li><strong><?php esc_html_e( 'Reviews:', 'odds-comparison' ); ?></strong> <?php echo esc_html( $review_count ); ?></li>
                        <?php endif; ?>
                        <?php if ( $min_deposit ) : ?>
                            <li><strong><?php esc_html_e( 'Min Deposit:', 'odds-comparison' ); ?></strong> <?php echo esc_html( $min_deposit ); ?></li>
                        <?php endif; ?>
                        <?php if ( $bonus_amount ) : ?>
                            <li><strong><?php esc_html_e( 'Bonus:', 'odds-comparison' ); ?></strong> <?php echo esc_html( $bonus_amount ); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Features -->
                <div class="oc-sidebar-widget">
                    <h4><?php esc_html_e( 'Features', 'odds-comparison' ); ?></h4>
                    <ul class="oc-features-list">
                        <?php if ( $live_betting ) : ?>
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Live Betting', 'odds-comparison' ); ?></li>
                        <?php endif; ?>
                        <?php if ( $cash_out ) : ?>
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Cash Out', 'odds-comparison' ); ?></li>
                        <?php endif; ?>
                        <?php if ( $mobile_app ) : ?>
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Mobile App', 'odds-comparison' ); ?></li>
                        <?php endif; ?>
                        <?php if ( $live_streaming ) : ?>
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Live Streaming', 'odds-comparison' ); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <?php if ( is_active_sidebar( 'operator-sidebar' ) ) : ?>
                    <?php dynamic_sidebar( 'operator-sidebar' ); ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bonuses Tab -->
        <div id="bonuses-tab" class="oc-tab-content">
            <div class="oc-bonuses-section">
                <h2><?php esc_html_e( 'Current Bonuses', 'odds-comparison' ); ?></h2>

                <?php if ( $bonus_amount ) : ?>
                    <div class="oc-current-bonus">
                        <div class="oc-bonus-header">
                            <h3><?php echo esc_html( $bonus_amount ); ?> <?php echo esc_html( ucfirst( $bonus_type ) ); ?> <?php esc_html_e( 'Bonus', 'odds-comparison' ); ?></h3>
                            <?php if ( $bonus_last_updated ) : ?>
                                <span class="oc-bonus-updated"><?php printf( esc_html__( 'Last updated: %s', 'odds-comparison' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( $bonus_last_updated ) ) ) ); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ( $bonus_description ) : ?>
                            <div class="oc-bonus-description">
                                <?php echo wp_kses_post( nl2br( $bonus_description ) ); ?>
                            </div>
                        <?php endif; ?>

                        <div class="oc-bonus-details">
                            <div class="oc-bonus-terms">
                                <h4><?php esc_html_e( 'Terms & Conditions', 'odds-comparison' ); ?></h4>
                                <ul>
                                    <?php if ( $min_deposit ) : ?>
                                        <li><?php printf( esc_html__( 'Minimum deposit: %s', 'odds-comparison' ), esc_html( $min_deposit ) ); ?></li>
                                    <?php endif; ?>
                                    <?php if ( $wagering_requirement ) : ?>
                                        <li><?php printf( esc_html__( 'Wagering requirement: %s', 'odds-comparison' ), esc_html( $wagering_requirement ) ); ?></li>
                                    <?php endif; ?>
                                    <?php if ( $bonus_expiry ) : ?>
                                        <li><?php printf( esc_html__( 'Expires: %s', 'odds-comparison' ), esc_html( $bonus_expiry ) ); ?></li>
                                    <?php endif; ?>
                                    <?php if ( $bonus_code ) : ?>
                                        <li><?php printf( esc_html__( 'Bonus code: %s', 'odds-comparison' ), '<code>' . esc_html( $bonus_code ) . '</code>' ); ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <div class="oc-bonus-cta">
                                <?php if ( $affiliate_url ) : ?>
                                    <a href="<?php echo esc_url( $affiliate_url ); ?>" class="button oc-claim-bonus" target="_blank" rel="nofollow">
                                        <?php esc_html_e( 'Claim Bonus', 'odds-comparison' ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <p><?php esc_html_e( 'No current bonuses available for this operator.', 'odds-comparison' ); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reviews Tab -->
        <div id="reviews-tab" class="oc-tab-content">
            <div class="oc-reviews-section">
                <div class="oc-reviews-header">
                    <h2><?php esc_html_e( 'Customer Reviews', 'odds-comparison' ); ?></h2>
                    <div class="oc-reviews-summary">
                        <?php if ( $average_review_rating > 0 ) : ?>
                            <div class="oc-average-rating">
                                <div class="oc-stars">
                                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                        <span class="star <?php echo $i <= round( $average_review_rating ) ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="oc-rating-value"><?php echo esc_html( number_format( $average_review_rating, 1 ) ); ?>/5</span>
                                <span class="oc-review-count">(<?php echo esc_html( count( $user_reviews ) ); ?> <?php esc_html_e( 'reviews', 'odds-comparison' ); ?>)</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Review Form -->
                <?php if ( is_user_logged_in() ) : ?>
                    <div class="oc-review-form">
                        <h3><?php esc_html_e( 'Write a Review', 'odds-comparison' ); ?></h3>
                        <form method="post" action="">
                            <?php wp_nonce_field( 'oc_submit_review', 'oc_review_nonce' ); ?>
                            <input type="hidden" name="oc_review_post_id" value="<?php echo esc_attr( $operator_id ); ?>">

                            <div class="oc-form-group">
                                <label for="oc_review_rating"><?php esc_html_e( 'Rating', 'odds-comparison' ); ?></label>
                                <div class="oc-rating-input">
                                    <?php for ( $i = 5; $i >= 1; $i-- ) : ?>
                                        <input type="radio" name="oc_review_rating" value="<?php echo esc_attr( $i ); ?>" id="rating-<?php echo esc_attr( $i ); ?>">
                                        <label for="rating-<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?> <?php esc_html_e( 'stars', 'odds-comparison' ); ?></label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="oc-form-group">
                                <label for="oc_review_title"><?php esc_html_e( 'Review Title', 'odds-comparison' ); ?></label>
                                <input type="text" name="oc_review_title" id="oc_review_title" required>
                            </div>

                            <div class="oc-form-group">
                                <label for="oc_review_content"><?php esc_html_e( 'Your Review', 'odds-comparison' ); ?></label>
                                <textarea name="oc_review_content" id="oc_review_content" rows="4" required></textarea>
                            </div>

                            <button type="submit" name="oc_submit_review" class="button"><?php esc_html_e( 'Submit Review', 'odds-comparison' ); ?></button>
                        </form>
                    </div>
                <?php else : ?>
                    <div class="oc-login-prompt">
                        <p><?php printf( esc_html__( 'Please %s to write a review.', 'odds-comparison' ), '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'login', 'odds-comparison' ) . '</a>' ); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Reviews List -->
                <div class="oc-reviews-list">
                    <?php if ( ! empty( $user_reviews ) ) : ?>
                        <?php foreach ( $user_reviews as $review ) : ?>
                            <div class="oc-review-item">
                                <div class="oc-review-header">
                                    <div class="oc-review-author">
                                        <?php echo esc_html( $review->comment_author ); ?>
                                    </div>
                                    <div class="oc-review-rating">
                                        <?php
                                        $review_rating = get_comment_meta( $review->comment_ID, 'rating', true );
                                        if ( $review_rating ) :
                                        ?>
                                            <div class="oc-stars">
                                                <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                                    <span class="star <?php echo $i <= $review_rating ? 'filled' : ''; ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="oc-review-date">
                                        <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $review->comment_date ) ) ); ?>
                                    </div>
                                </div>
                                <div class="oc-review-content">
                                    <?php echo wp_kses_post( $review->comment_content ); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p><?php esc_html_e( 'No reviews yet. Be the first to review this operator!', 'odds-comparison' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sports & Betting Tab -->
        <div id="sports-tab" class="oc-tab-content">
            <div class="oc-sports-section">
                <h2><?php esc_html_e( 'Sports & Betting Options', 'odds-comparison' ); ?></h2>

                <?php if ( is_array( $sports_supported ) && ! empty( $sports_supported ) ) : ?>
                    <div class="oc-sports-supported">
                        <h3><?php esc_html_e( 'Supported Sports', 'odds-comparison' ); ?></h3>
                        <div class="oc-sports-grid">
                            <?php foreach ( $sports_supported as $sport ) : ?>
                                <?php if ( $sport ) : ?>
                                    <span class="oc-sport-item"><?php echo esc_html( $sport ); ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="oc-betting-features">
                    <h3><?php esc_html_e( 'Betting Features', 'odds-comparison' ); ?></h3>
                    <div class="oc-features-grid">
                        <?php if ( $live_betting ) : ?>
                            <div class="oc-feature-card">
                                <span class="dashicons dashicons-yes"></span>
                                <h4><?php esc_html_e( 'Live Betting', 'odds-comparison' ); ?></h4>
                                <p><?php esc_html_e( 'Bet on matches as they happen with live odds.', 'odds-comparison' ); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ( $cash_out ) : ?>
                            <div class="oc-feature-card">
                                <span class="dashicons dashicons-yes"></span>
                                <h4><?php esc_html_e( 'Cash Out', 'odds-comparison' ); ?></h4>
                                <p><?php esc_html_e( 'Secure your winnings before the match ends.', 'odds-comparison' ); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ( $live_streaming ) : ?>
                            <div class="oc-feature-card">
                                <span class="dashicons dashicons-yes"></span>
                                <h4><?php esc_html_e( 'Live Streaming', 'odds-comparison' ); ?></h4>
                                <p><?php esc_html_e( 'Watch matches live while betting.', 'odds-comparison' ); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ( $mobile_app ) : ?>
                            <div class="oc-feature-card">
                                <span class="dashicons dashicons-yes"></span>
                                <h4><?php esc_html_e( 'Mobile App', 'odds-comparison' ); ?></h4>
                                <p><?php esc_html_e( 'Bet on the go with our mobile application.', 'odds-comparison' ); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.oc-tab-button');
    const tabContents = document.querySelectorAll('.oc-tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');

            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        });
    });
});
</script>

<?php get_footer(); ?>

