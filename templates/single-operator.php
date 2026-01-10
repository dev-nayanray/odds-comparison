<?php
/**
 * Operator Single Template
 *
 * Template for displaying single operator page.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

$operator_id = get_the_ID();

$rating = get_post_meta( $operator_id, 'oc_operator_rating', true );
$review_count = get_post_meta( $operator_id, 'oc_review_count', true );
$bonus_amount = get_post_meta( $operator_id, 'oc_bonus_amount', true );
$bonus_type = get_post_meta( $operator_id, 'oc_bonus_type', true );
$bonus_description = get_post_meta( $operator_id, 'oc_bonus_description', true );
$bonus_code = get_post_meta( $operator_id, 'oc_bonus_code', true );
$affiliate_url = get_post_meta( $operator_id, 'oc_affiliate_url', true );
$min_deposit = get_post_meta( $operator_id, 'oc_min_deposit', true );
$wagering_requirement = get_post_meta( $operator_id, 'oc_wagering_requirement', true );

$pros = get_post_meta( $operator_id, 'oc_operator_pros', true );
$cons = get_post_meta( $operator_id, 'oc_operator_cons', true );

$live_betting = get_post_meta( $operator_id, 'oc_live_betting', true );
$cash_out = get_post_meta( $operator_id, 'oc_cash_out', true );
$mobile_app = get_post_meta( $operator_id, 'oc_mobile_app', true );
$live_streaming = get_post_meta( $operator_id, 'oc_live_streaming', true );

$licenses = wp_get_post_terms( $operator_id, 'license', array( 'fields' => 'names' ) );
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
    
    <div class="oc-operator-content">
        <div class="oc-operator-main">
            <div class="oc-operator-description">
                <h2><?php esc_html_e( 'About', 'odds-comparison' ); ?></h2>
                <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                    <?php the_content(); ?>
                <?php endwhile; endif; ?>
            </div>
            
            <?php if ( ! empty( $pros ) || ! empty( $cons ) ) : ?>
                <div class="oc-operator-pros-cons">
                    <div class="oc-pros">
                        <h3><?php esc_html_e( 'Pros', 'odds-comparison' ); ?></h3>
                        <ul>
                            <?php foreach ( $pros as $pro ) : ?>
                                <?php if ( $pro ) : ?>
                                    <li><span class="dashicons dashicons-yes-alt" style="color: #27ae60;"></span> <?php echo esc_html( $pro ); ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="oc-cons">
                        <h3><?php esc_html_e( 'Cons', 'odds-comparison' ); ?></h3>
                        <ul>
                            <?php foreach ( $cons as $con ) : ?>
                                <?php if ( $con ) : ?>
                                    <li><span class="dashicons dashicons-dismiss" style="color: #e74c3c;"></span> <?php echo esc_html( $con ); ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="oc-operator-sidebar">
            <?php if ( is_active_sidebar( 'operator-sidebar' ) ) : ?>
                <?php dynamic_sidebar( 'operator-sidebar' ); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>

