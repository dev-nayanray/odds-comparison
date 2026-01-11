<?php
/**
 * Operators Archive Template
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

/* =========================
 * Filters data
 * ========================= */
$licenses = get_terms( array(
    'taxonomy'   => 'license',
    'hide_empty' => true,
) );

$bonus_types = array(
    'deposit_match' => __( 'Deposit Match', 'odds-comparison' ),
    'free_bet'      => __( 'Free Bet', 'odds-comparison' ),
    'no_deposit'    => __( 'No Deposit Bonus', 'odds-comparison' ),
    'enhanced'      => __( 'Enhanced Odds', 'odds-comparison' ),
    'cashback'      => __( 'Cashback', 'odds-comparison' ),
    'other'         => __( 'Other', 'odds-comparison' ),
);

$payment_methods = array(
    'visa'          => __( 'Visa', 'odds-comparison' ),
    'mastercard'    => __( 'Mastercard', 'odds-comparison' ),
    'paypal'        => __( 'PayPal', 'odds-comparison' ),
    'skrill'        => __( 'Skrill', 'odds-comparison' ),
    'neteller'      => __( 'Neteller', 'odds-comparison' ),
    'paysafecard'   => __( 'Paysafecard', 'odds-comparison' ),
    'bank_transfer' => __( 'Bank Transfer', 'odds-comparison' ),
    'apple_pay'     => __( 'Apple Pay', 'odds-comparison' ),
    'google_pay'    => __( 'Google Pay', 'odds-comparison' ),
);

// Get current filters
$current_filters = array(
    'license'    => isset($_GET['license']) ? sanitize_text_field($_GET['license']) : '',
    'bonus_type' => isset($_GET['bonus_type']) ? sanitize_text_field($_GET['bonus_type']) : '',
    'payment'    => isset($_GET['payment']) ? sanitize_text_field($_GET['payment']) : '',
    'min_rating' => isset($_GET['min_rating']) ? floatval($_GET['min_rating']) : 0,
    'sort'       => isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'rating',
);

// Setup query arguments
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = array(
    'post_type'      => 'operator',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'post_status'    => 'publish',
);

// Add meta query for filtering
$meta_query = array('relation' => 'AND');

// Add rating filter
if ($current_filters['min_rating'] > 0) {
    $meta_query[] = array(
        'key'     => 'oc_operator_rating',
        'value'   => $current_filters['min_rating'],
        'compare' => '>=',
        'type'    => 'DECIMAL',
    );
}

// Add bonus type filter
if (!empty($current_filters['bonus_type'])) {
    $meta_query[] = array(
        'key'     => 'oc_bonus_type',
        'value'   => $current_filters['bonus_type'],
        'compare' => '=',
    );
}

// Add payment method filter (assuming it's stored as post meta)
if (!empty($current_filters['payment'])) {
    $meta_query[] = array(
        'key'     => 'oc_payment_methods',
        'value'   => $current_filters['payment'],
        'compare' => 'LIKE',
    );
}

if (count($meta_query) > 1) {
    $args['meta_query'] = $meta_query;
}

// Add taxonomy filter for license
if (!empty($current_filters['license'])) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'license',
            'field'    => 'slug',
            'terms'    => $current_filters['license'],
        ),
    );
}

// Add sorting
if ($current_filters['sort'] === 'rating') {
    $args['meta_key'] = 'oc_operator_rating';
    $args['orderby'] = 'meta_value_num';
    $args['order'] = 'DESC';
} elseif ($current_filters['sort'] === 'title') {
    $args['orderby'] = 'title';
    $args['order'] = 'ASC';
}

// Create custom query
$operators_query = new WP_Query($args);
?>

<style>
.oc-operators-archive {
     
    margin: 0 auto;
    padding: 30px 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Header */
.oc-archive-header {
    text-align: center;
    margin-bottom: 50px;
}

.oc-archive-title {
    font-size: 2.8rem;
    color: #1a365d;
    margin-bottom: 15px;
    font-weight: 700;
    line-height: 1.2;
}

.oc-archive-description {
    font-size: 1.2rem;
    color: #4a5568;
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;
}

/* Horizontal Filters */
.oc-filters-container {
      
    margin-bottom: 40px;
     
}

.oc-filter-row {
    display: flex;
    flex-wrap: nowrap;
    align-items: flex-end;
    gap: 20px;
    margin-bottom: 20px;
}

.oc-filter-group {
    flex: 1;
    min-width: 250px;
    margin-bottom: 0;
}

.oc-filter-label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #2d3748;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.oc-select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.95rem;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #4a5568;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%234a5568' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 12px;
}

.oc-select:focus {
    outline: none;
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
}

.oc-filter-actions {
    display: flex;
    gap: 15px;
    margin-left: auto;
    align-items: flex-end;
}

.oc-filter-btn {
    padding: 14px 28px;
    background: linear-gradient(135deg, #2c5282, #1a365d);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    height: 46px;
}

.oc-filter-btn:hover {
    background: linear-gradient(135deg, #1a365d, #2c5282);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(26, 54, 93, 0.2);
}

.oc-reset-btn {
    padding: 14px 28px;
    background: white;
    color: #4a5568;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    height: 46px;
    display: flex;
    align-items: center;
}

.oc-reset-btn:hover {
    background: #f7fafc;
    border-color: #cbd5e0;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Results Count */
.oc-results-count {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 10px;
    border-left: 4px solid #4299e1;
}

.oc-results-count p {
    margin: 0;
    font-size: 1.1rem;
    color: #4a5568;
    font-weight: 500;
}

/* Operators Grid */
.oc-operators-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 30px;
    margin-bottom: 60px;
}

/* Operator Card */
.oc-operator-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    border: 1px solid #e2e8f0;
}

.oc-operator-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
}

.oc-operator-card.featured {
    border: 2px solid #ecc94b;
    box-shadow: 0 4px 20px rgba(236, 201, 75, 0.2);
}

.oc-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #d69e2e, #b7791f);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    z-index: 10;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.oc-card-content {
    padding: 30px 30px 0;
}

.oc-card-header {
    display: flex;
    align-items: center;
    gap: 25px;
    margin-bottom: 25px;
}

.oc-operator-logo {
    flex-shrink: 0;
    width: 90px;
    height: 90px;
    background: #f8fafc;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    padding: 10px;
}

.oc-logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.oc-logo-fallback {
    font-size: 2.2rem;
    font-weight: 700;
    color: #a0aec0;
    text-transform: uppercase;
}

.oc-operator-title {
    margin: 0 0 12px 0;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.3;
}

.oc-operator-title a {
    color: #1a365d;
    text-decoration: none;
    transition: color 0.3s ease;
}

.oc-operator-title a:hover {
    color: #2c5282;
}

.oc-rating {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 5px;
}

.oc-stars {
    display: flex;
    gap: 3px;
}

.oc-star {
    font-size: 1.3rem;
    color: #e2e8f0;
}

.oc-star.filled {
    color: #f6ad55;
}

.oc-rating-value {
    font-weight: 600;
    color: #4a5568;
    font-size: 1.1rem;
}

.oc-reviews-count {
    color: #718096;
    font-size: 0.9rem;
}

.oc-bonus {
    background: linear-gradient(135deg, #f0fff4, #c6f6d5);
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    border-left: 4px solid #38a169;
}

.oc-bonus-amount {
    font-weight: 700;
    color: #276749;
    font-size: 1.3rem;
    margin-bottom: 8px;
}

.oc-bonus-type {
    color: #2f855a;
    font-size: 0.95rem;
    font-weight: 500;
}

.oc-excerpt {
    color: #4a5568;
    line-height: 1.7;
    margin-bottom: 25px;
    font-size: 1rem;
}

.oc-pros {
    margin: 0 0 25px 0;
    padding: 0;
    list-style: none;
}

.oc-pros li {
    padding: 10px 0;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.oc-pros li:last-child {
    border-bottom: none;
}

.oc-pro-icon {
    color: #38a169;
    font-size: 1rem;
    font-weight: bold;
}

.oc-pro-text {
    color: #4a5568;
    font-size: 0.95rem;
}

.oc-actions {
    background: #f8fafc;
    padding: 25px 30px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 20px;
}

.oc-visit-btn {
    flex: 1;
    padding: 16px;
    background: linear-gradient(135deg, #2c5282, #1a365d);
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.95rem;
}

.oc-visit-btn:hover {
    background: linear-gradient(135deg, #1a365d, #2c5282);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(26, 54, 93, 0.2);
}

.oc-review-btn {
    flex: 1;
    padding: 16px;
    background: white;
    color: #4a5568;
    text-decoration: none;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.95rem;
}

.oc-review-btn:hover {
    background: #f7fafc;
    border-color: #cbd5e0;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* No Operators */
.oc-no-operators {
    grid-column: 1 / -1;
    text-align: center;
    padding: 80px 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
}

.oc-no-operators-icon {
    font-size: 4rem;
    color: #cbd5e0;
    margin-bottom: 25px;
    opacity: 0.7;
}

.oc-no-operators h3 {
    color: #4a5568;
    margin-bottom: 15px;
    font-size: 1.8rem;
    font-weight: 600;
}

.oc-no-operators p {
    color: #718096;
    max-width: 500px;
    margin: 0 auto 30px;
    line-height: 1.7;
    font-size: 1.1rem;
}

/* Pagination */
.oc-pagination {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 50px;
}

.oc-pagination a,
.oc-pagination span {
    padding: 12px 18px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #4a5568;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    min-width: 45px;
    text-align: center;
}

.oc-pagination a:hover {
    background: #4299e1;
    border-color: #4299e1;
    color: white;
    transform: translateY(-2px);
}

.oc-pagination .current {
    background: #2c5282;
    border-color: #2c5282;
    color: white;
}

/* Responsive */
@media (max-width: 1200px) {
    .oc-filter-row {
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .oc-filter-group {
        flex: 0 0 calc(50% - 15px);
        min-width: auto;
    }
    
    .oc-filter-actions {
        flex: 0 0 100%;
        justify-content: center;
        margin-left: 0;
        margin-top: 10px;
    }
}

@media (max-width: 992px) {
    .oc-operators-grid {
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 25px;
    }
}

@media (max-width: 768px) {
    .oc-operators-archive {
        padding: 20px 15px;
    }
    
    .oc-archive-title {
        font-size: 2.2rem;
    }
    
    .oc-archive-description {
        font-size: 1.1rem;
    }
    
    .oc-filters-container {
        padding: 20px;
    }
    
    .oc-filter-row {
        flex-direction: column;
        gap: 15px;
    }
    
    .oc-filter-group {
        flex: 0 0 100%;
        width: 100%;
    }
    
    .oc-filter-actions {
        flex-direction: column;
        gap: 10px;
        width: 100%;
    }
    
    .oc-filter-btn,
    .oc-reset-btn {
        width: 100%;
        text-align: center;
    }
    
    .oc-operators-grid {
        grid-template-columns: 1fr;
        gap: 25px;
    }
    
    .oc-card-header {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }
    
    .oc-actions {
        flex-direction: column;
        gap: 15px;
    }
    
    .oc-pagination {
        flex-wrap: wrap;
    }
}
</style>

<div class="oc-operators-archive">

    <!-- Header -->
    <header class="oc-archive-header">
        <h1 class="oc-archive-title"><?php esc_html_e( 'Betting Operators', 'odds-comparison' ); ?></h1>
        <p class="oc-archive-description">
            <?php esc_html_e( 'Compare the best betting operators, their bonuses, and features.', 'odds-comparison' ); ?>
        </p>
    </header>

    <!-- Modern Filters - Horizontal Layout -->
    <div class="oc-filters-container">
        <form class="oc-archive-filters" method="get">
            <div class="oc-filter-row">
                
                <!-- License Filter -->
                <div class="oc-filter-group">
                    <label class="oc-filter-label"><?php esc_html_e( 'License', 'odds-comparison' ); ?></label>
                    <select name="license" class="oc-select">
                        <option value=""><?php esc_html_e( 'All Licenses', 'odds-comparison' ); ?></option>
                        <?php if ( !is_wp_error( $licenses ) && !empty( $licenses ) ) : ?>
                            <?php foreach ( $licenses as $license ) : ?>
                                <option value="<?php echo esc_attr( $license->slug ); ?>" 
                                    <?php selected( $current_filters['license'], $license->slug ); ?>>
                                    <?php echo esc_html( $license->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Bonus Type Filter -->
                <div class="oc-filter-group">
                    <label class="oc-filter-label"><?php esc_html_e( 'Bonus Type', 'odds-comparison' ); ?></label>
                    <select name="bonus_type" class="oc-select">
                        <option value=""><?php esc_html_e( 'All Bonus Types', 'odds-comparison' ); ?></option>
                        <?php foreach ( $bonus_types as $slug => $label ) : ?>
                            <option value="<?php echo esc_attr( $slug ); ?>"
                                <?php selected( $current_filters['bonus_type'], $slug ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Payment Method Filter -->
                <div class="oc-filter-group">
                    <label class="oc-filter-label"><?php esc_html_e( 'Payment Method', 'odds-comparison' ); ?></label>
                    <select name="payment" class="oc-select">
                        <option value=""><?php esc_html_e( 'All Methods', 'odds-comparison' ); ?></option>
                        <?php foreach ( $payment_methods as $slug => $label ) : ?>
                            <option value="<?php echo esc_attr( $slug ); ?>"
                                <?php selected( $current_filters['payment'], $slug ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Rating Filter -->
                <div class="oc-filter-group">
                    <label class="oc-filter-label"><?php esc_html_e( 'Min Rating', 'odds-comparison' ); ?></label>
                    <select name="min_rating" class="oc-select">
                        <option value="0"><?php esc_html_e( 'Any Rating', 'odds-comparison' ); ?></option>
                        <option value="4" <?php selected( $current_filters['min_rating'], 4 ); ?>>4+ Stars</option>
                        <option value="4.5" <?php selected( $current_filters['min_rating'], 4.5 ); ?>>4.5+ Stars</option>
                        <option value="4.8" <?php selected( $current_filters['min_rating'], 4.8 ); ?>>4.8+ Stars</option>
                    </select>
                </div>

                <!-- Sort Filter -->
                <div class="oc-filter-group">
                    <label class="oc-filter-label"><?php esc_html_e( 'Sort By', 'odds-comparison' ); ?></label>
                    <select name="sort" class="oc-select">
                        <option value="rating" <?php selected( $current_filters['sort'], 'rating' ); ?>>
                            <?php esc_html_e( 'Highest Rating', 'odds-comparison' ); ?>
                        </option>
                        <option value="title" <?php selected( $current_filters['sort'], 'title' ); ?>>
                            <?php esc_html_e( 'A to Z', 'odds-comparison' ); ?>
                        </option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="oc-filter-actions">
                    <button type="submit" class="oc-filter-btn">
                        <?php esc_html_e( 'Apply Filters', 'odds-comparison' ); ?>
                    </button>
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'operator' ) ); ?>" 
                       class="oc-reset-btn">
                        <?php esc_html_e( 'Reset All', 'odds-comparison' ); ?>
                    </a>
                </div>

            </div>
        </form>
    </div>

    <!-- Results Count -->
    <div class="oc-results-count">
        <p>
            <?php 
            $found_posts = $operators_query->found_posts;
            printf(
                esc_html( _n( 'Found %d operator', 'Found %d operators', $found_posts, 'odds-comparison' ) ),
                $found_posts
            );
            ?>
        </p>
    </div>

    <!-- Operators Grid -->
    <div class="oc-operators-grid">

        <?php if ( $operators_query->have_posts() ) : ?>
            <?php while ( $operators_query->have_posts() ) : $operators_query->the_post(); ?>

                <?php
                $operator_id  = get_the_ID();
                $rating       = floatval( get_post_meta( $operator_id, 'oc_operator_rating', true ) );
                $reviews      = absint( get_post_meta( $operator_id, 'oc_review_count', true ) );
                $bonus_amount = get_post_meta( $operator_id, 'oc_bonus_amount', true );
                $bonus_type   = get_post_meta( $operator_id, 'oc_bonus_type', true );
                $affiliate    = esc_url( get_post_meta( $operator_id, 'oc_affiliate_url', true ) );
                $featured     = (bool) get_post_meta( $operator_id, 'oc_featured_operator', true );
                $pros         = (array) get_post_meta( $operator_id, 'oc_operator_pros', true );
                $excerpt      = get_the_excerpt();
                ?>

                <article class="oc-operator-card<?php echo $featured ? ' featured' : ''; ?>">

                    <?php if ( $featured ) : ?>
                        <div class="oc-badge"><?php esc_html_e( 'Recommended', 'odds-comparison' ); ?></div>
                    <?php endif; ?>

                    <div class="oc-card-content">
                        <!-- Logo and Title Row -->
                        <div class="oc-card-header">
                            <div class="oc-operator-logo">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'medium', array('class' => 'oc-logo-img') ); ?>
                                <?php else : ?>
                                    <span class="oc-logo-fallback">
                                        <?php echo esc_html( mb_substr( get_the_title(), 0, 1 ) ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div>
                                <h2 class="oc-operator-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>

                                <?php if ( $rating ) : ?>
                                    <div class="oc-rating">
                                        <div class="oc-stars">
                                            <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                                <span class="oc-star<?php echo $i <= round( $rating ) ? ' filled' : ''; ?>">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="oc-rating-value">
                                            <?php echo esc_html( number_format( $rating, 1 ) ); ?>
                                        </span>
                                        <?php if ( $reviews ) : ?>
                                            <span class="oc-reviews-count">
                                                (<?php echo esc_html( $reviews ); ?>)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Bonus -->
                        <?php if ( $bonus_amount ) : ?>
                            <div class="oc-bonus">
                                <div class="oc-bonus-amount">
                                    <?php echo esc_html( $bonus_amount ); ?>
                                </div>
                                <?php if ( isset( $bonus_types[ $bonus_type ] ) ) : ?>
                                    <div class="oc-bonus-type">
                                        <?php echo esc_html( $bonus_types[ $bonus_type ] ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Excerpt -->
                        <?php if ( $excerpt ) : ?>
                            <div class="oc-excerpt">
                                <?php echo esc_html( wp_trim_words( $excerpt, 25, '...' ) ); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Pros -->
                        <?php if ( !empty( $pros ) ) : ?>
                            <ul class="oc-pros">
                                <?php foreach ( array_slice( $pros, 0, 3 ) as $pro ) : ?>
                                    <?php if ( $pro ) : ?>
                                        <li>
                                            <span class="oc-pro-icon">✓</span>
                                            <span class="oc-pro-text"><?php echo esc_html( $pro ); ?></span>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="oc-actions">
                        <?php if ( $affiliate ) : ?>
                            <a href="<?php echo $affiliate; ?>" target="_blank" rel="nofollow"
                               class="oc-visit-btn">
                                <?php esc_html_e( 'Visit Site', 'odds-comparison' ); ?>
                            </a>
                        <?php endif; ?>
                        <a href="<?php the_permalink(); ?>" class="oc-review-btn">
                            <?php esc_html_e( 'Read Review', 'odds-comparison' ); ?>
                        </a>
                    </div>

                </article>

            <?php endwhile; ?>
            
            <?php wp_reset_postdata(); ?>

        <?php else : ?>
            <div class="oc-no-operators">
                <div class="oc-no-operators-icon">─</div>
                <h3><?php esc_html_e( 'No operators found', 'odds-comparison' ); ?></h3>
                <p><?php esc_html_e( 'Try adjusting your filters to find more operators.', 'odds-comparison' ); ?></p>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'operator' ) ); ?>" 
                   class="oc-visit-btn" style="max-width: 200px; margin: 0 auto;">
                    <?php esc_html_e( 'Reset Filters', 'odds-comparison' ); ?>
                </a>
            </div>
        <?php endif; ?>

    </div>

    <!-- Pagination -->
    <?php if ( $operators_query->have_posts() ) : ?>
        <div class="oc-pagination">
            <?php
            $big = 999999999;
            echo paginate_links( array(
                'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                'format'    => '?paged=%#%',
                'current'   => max( 1, $paged ),
                'total'     => $operators_query->max_num_pages,
                'prev_text' => '← ' . esc_html__( 'Previous', 'odds-comparison' ),
                'next_text' => esc_html__( 'Next', 'odds-comparison' ) . ' →',
                'mid_size'  => 2,
            ) );
            ?>
        </div>
    <?php endif; ?>

</div>

<?php get_footer(); ?>