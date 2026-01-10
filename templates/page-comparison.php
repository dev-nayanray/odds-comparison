<?php
/**
 * Comparison Tool Page Template
 *
 * A dedicated page for comparing betting operators with advanced filtering.
 * Can be used as a page template or via shortcode [oc_operator_compare].
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

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

// Get query parameters for filtering
$current_filters = array(
    'license'      => isset( $_GET['license'] ) ? sanitize_text_field( $_GET['license'] ) : '',
    'bonus_type'   => isset( $_GET['bonus_type'] ) ? sanitize_text_field( $_GET['bonus_type'] ) : '',
    'payment'      => isset( $_GET['payment'] ) ? sanitize_text_field( $_GET['payment'] ) : '',
    'min_rating'   => isset( $_GET['min_rating'] ) ? floatval( $_GET['min_rating'] ) : 0,
    'sort'         => isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : 'rating',
);

get_header();
?>

<div class="oc-comparison-tool">
    <header class="oc-comparison-header">
        <h1 class="oc-comparison-title"><?php esc_html_e( 'Compare Betting Operators', 'odds-comparison' ); ?></h1>
        <p class="oc-comparison-description">
            <?php esc_html_e( 'Find the best betting sites with our advanced comparison tool. Filter by license, bonus type, payment methods, and more.', 'odds-comparison' ); ?>
        </p>
    </header>
    
    <div class="oc-comparison-layout">
        <!-- Sidebar Filters -->
        <aside class="oc-comparison-sidebar">
            <form id="oc-comparison-filter-form" class="oc-filter-form" method="get">
                <h3 class="oc-filter-heading"><?php esc_html_e( 'Filter Operators', 'odds-comparison' ); ?></h3>
                
                <!-- License Filter -->
                <div class="oc-filter-section">
                    <label for="oc-filter-license" class="oc-filter-label"><?php esc_html_e( 'License', 'odds-comparison' ); ?></label>
                    <select id="oc-filter-license" name="license" class="oc-filter-select">
                        <option value=""><?php esc_html_e( 'All Licenses', 'odds-comparison' ); ?></option>
                        <?php foreach ( $licenses as $license ) : ?>
                        <option value="<?php echo esc_attr( $license->slug ); ?>" <?php selected( $current_filters['license'], $license->slug ); ?>>
                                <?php echo esc_html( $license->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Bonus Type Filter -->
                <div class="oc-filter-section">
                    <label for="oc-filter-bonus-type" class="oc-filter-label"><?php esc_html_e( 'Bonus Type', 'odds-comparison' ); ?></label>
                    <select id="oc-filter-bonus-type" name="bonus_type" class="oc-filter-select">
                        <option value=""><?php esc_html_e( 'All Bonus Types', 'odds-comparison' ); ?></option>
                        <?php foreach ( $bonus_types as $bonus_slug => $bonus_label ) : ?>
                            <option value="<?php echo esc_attr( $bonus_slug ); ?>" <?php selected( $current_filters['bonus_type'], $bonus_slug ); ?>>
                                <?php echo esc_html( $bonus_label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Payment Method Filter -->
                <div class="oc-filter-section">
                    <label for="oc-filter-payment" class="oc-filter-label"><?php esc_html_e( 'Payment Method', 'odds-comparison' ); ?></label>
                    <select id="oc-filter-payment" name="payment" class="oc-filter-select">
                        <option value=""><?php esc_html_e( 'All Methods', 'odds-comparison' ); ?></option>
                        <?php foreach ( $payment_methods as $pm_slug => $pm_label ) : ?>
                            <option value="<?php echo esc_attr( $pm_slug ); ?>" <?php selected( $current_filters['payment'], $pm_slug ); ?>>
                                <?php echo esc_html( $pm_label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Minimum Rating Filter -->
                <div class="oc-filter-section">
                    <label for="oc-filter-rating" class="oc-filter-label"><?php esc_html_e( 'Minimum Rating', 'odds-comparison' ); ?></label>
                    <select id="oc-filter-rating" name="min_rating" class="oc-filter-select">
                        <option value="0" <?php selected( $current_filters['min_rating'], 0 ); ?>><?php esc_html_e( 'Any Rating', 'odds-comparison' ); ?></option>
                        <option value="3" <?php selected( $current_filters['min_rating'], 3 ); ?>><?php esc_html_e( '3+ Stars', 'odds-comparison' ); ?></option>
                        <option value="4" <?php selected( $current_filters['min_rating'], 4 ); ?>><?php esc_html_e( '4+ Stars', 'odds-comparison' ); ?></option>
                        <option value="4.5" <?php selected( $current_filters['min_rating'], 4.5 ); ?>><?php esc_html_e( '4.5+ Stars', 'odds-comparison' ); ?></option>
                        <option value="4.8" <?php selected( $current_filters['min_rating'], 4.8 ); ?>><?php esc_html_e( '4.8+ Stars', 'odds-comparison' ); ?></option>
                    </select>
                </div>
                
                <!-- Sort By -->
                <div class="oc-filter-section">
                    <label for="oc-filter-sort" class="oc-filter-label"><?php esc_html_e( 'Sort By', 'odds-comparison' ); ?></label>
                    <select id="oc-filter-sort" name="sort" class="oc-filter-select">
                        <option value="rating" <?php selected( $current_filters['sort'], 'rating' ); ?>><?php esc_html_e( 'Highest Rated', 'odds-comparison' ); ?></option>
                        <option value="name" <?php selected( $current_filters['sort'], 'name' ); ?>><?php esc_html_e( 'Name A-Z', 'odds-comparison' ); ?></option>
                        <option value="bonus" <?php selected( $current_filters['sort'], 'bonus' ); ?>><?php esc_html_e( 'Bonus Amount', 'odds-comparison' ); ?></option>
                        <option value="newest" <?php selected( $current_filters['sort'], 'newest' ); ?>><?php esc_html_e( 'Newest Added', 'odds-comparison' ); ?></option>
                    </select>
                </div>
                
                <!-- Filter Actions -->
                <div class="oc-filter-actions">
                    <button type="submit" class="button oc-apply-filters-btn">
                        <?php esc_html_e( 'Apply Filters', 'odds-comparison' ); ?>
                    </button>
                    <a href="<?php echo esc_url( remove_query_arg( array( 'license', 'bonus_type', 'payment', 'min_rating', 'sort' ) ) ); ?>" class="button oc-reset-filters-btn">
                        <?php esc_html_e( 'Reset', 'odds-comparison' ); ?>
                    </a>
                </div>
            </form>
            
            <!-- Quick Stats -->
            <div class="oc-quick-stats">
                <h4><?php esc_html_e( 'Quick Stats', 'odds-comparison' ); ?></h4>
                <?php
                $total_operators = wp_count_posts( 'operator' );
                $published_operators = isset( $total_operators->publish ) ? $total_operators->publish : 0;
                ?>
                <p><strong><?php echo esc_html( $published_operators ); ?></strong> <?php esc_html_e( 'operators compared', 'odds-comparison' ); ?></p>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="oc-comparison-content">
            <!-- Active Filters Display -->
            <?php if ( $current_filters['license'] || $current_filters['bonus_type'] || $current_filters['payment'] || $current_filters['min_rating'] > 0 ) : ?>
            <div class="oc-active-filters-bar">
                <span class="oc-active-label"><?php esc_html_e( 'Active filters:', 'odds-comparison' ); ?></span>
                <?php if ( $current_filters['license'] ) : ?>
                    <?php $license_term = get_term_by( 'slug', $current_filters['license'], 'license' ); ?>
                    <span class="oc-active-filter-tag">
                        <?php echo esc_html( $license_term->name ); ?>
                        <a href="<?php echo esc_url( remove_query_arg( 'license' ) ); ?>" class="oc-remove-filter">&times;</a>
                    </span>
                <?php endif; ?>
                <?php if ( $current_filters['bonus_type'] ) : ?>
                    <span class="oc-active-filter-tag">
                        <?php echo esc_html( $bonus_types[ $current_filters['bonus_type'] ] ); ?>
                        <a href="<?php echo esc_url( remove_query_arg( 'bonus_type' ) ); ?>" class="oc-remove-filter">&times;</a>
                    </span>
                <?php endif; ?>
                <?php if ( $current_filters['payment'] ) : ?>
                    <span class="oc-active-filter-tag">
                        <?php echo esc_html( $payment_methods[ $current_filters['payment'] ] ); ?>
                        <a href="<?php echo esc_url( remove_query_arg( 'payment' ) ); ?>" class="oc-remove-filter">&times;</a>
                    </span>
                <?php endif; ?>
                <?php if ( $current_filters['min_rating'] > 0 ) : ?>
                    <span class="oc-active-filter-tag">
                        <?php printf( esc_html__( '%s+ Stars', 'odds-comparison' ), esc_html( $current_filters['min_rating'] ) ); ?>
                        <a href="<?php echo esc_url( remove_query_arg( 'min_rating' ) ); ?>" class="oc-remove-filter">&times;</a>
                    </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Operators List -->
            <div id="oc-operators-comparison" class="oc-operators-comparison-grid">
                <?php
                // Build query with filters
                $operators_args = array(
                    'post_type'      => 'operator',
                    'post_status'    => 'publish',
                    'posts_per_page' => 20,
                    'meta_query'     => array(),
                    'meta_key'       => 'oc_operator_rating',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'DESC',
                );
                
                // Add meta queries for filters
                if ( $current_filters['license'] ) {
                    $operators_args['tax_query'][] = array(
                        'taxonomy' => 'license',
                        'field'    => 'slug',
                        'terms'    => $current_filters['license'],
                    );
                }
                
                if ( $current_filters['bonus_type'] ) {
                    $operators_args['meta_query'][] = array(
                        'key'     => 'oc_operator_bonus_type',
                        'value'   => $current_filters['bonus_type'],
                        'compare' => '=',
                    );
                }
                
                if ( $current_filters['payment'] ) {
                    $operators_args['meta_query'][] = array(
                        'key'     => 'oc_operator_payment_methods',
                        'value'   => $current_filters['payment'],
                        'compare' => 'LIKE',
                    );
                }
                
                if ( $current_filters['min_rating'] > 0 ) {
                    $operators_args['meta_query'][] = array(
                        'key'     => 'oc_operator_rating',
                        'value'   => $current_filters['min_rating'],
                        'compare' => '>=',
                        'type'    => 'NUMERIC',
                    );
                }
                
                if ( ! empty( $operators_args['meta_query'] ) ) {
                    $operators_args['meta_query']['relation'] = 'AND';
                }
                
                // Handle sorting
                switch ( $current_filters['sort'] ) {
                    case 'name':
                        $operators_args['orderby'] = 'title';
                        $operators_args['order'] = 'ASC';
                        unset( $operators_args['meta_key'] );
                        break;
                    case 'bonus':
                        $operators_args['meta_key'] = 'oc_operator_bonus_value';
                        $operators_args['orderby'] = 'meta_value_num';
                        $operators_args['order'] = 'DESC';
                        break;
                    case 'newest':
                        $operators_args['orderby'] = 'date';
                        $operators_args['order'] = 'DESC';
                        unset( $operators_args['meta_key'] );
                        break;
                }
                
                $operators_query = new WP_Query( $operators_args );
                
                if ( $operators_query->have_posts() ) :
                    while ( $operators_query->have_posts() ) : $operators_query->the_post();
                        $operator_id = get_the_ID();
                        $rating = get_post_meta( $operator_id, 'oc_operator_rating', true );
                        $bonus_text = get_post_meta( $operator_id, 'oc_operator_bonus_text', true );
                        $bonus_type = get_post_meta( $operator_id, 'oc_operator_bonus_type', true );
                        $bonus_value = get_post_meta( $operator_id, 'oc_operator_bonus_value', true );
                        $min_deposit = get_post_meta( $operator_id, 'oc_operator_min_deposit', true );
                        $payment_methods_str = get_post_meta( $operator_id, 'oc_operator_payment_methods', true );
                        $affiliate_url = get_post_meta( $operator_id, 'oc_operator_affiliate_url', true );
                        $is_featured = get_post_meta( $operator_id, 'oc_featured_operator', true );
                        $pros = get_post_meta( $operator_id, 'oc_operator_pros', true );
                        $license_terms = get_the_terms( $operator_id, 'license' );
                        $license_name = ! empty( $license_terms ) && ! is_wp_error( $license_terms ) ? $license_terms[0]->name : '';
                        ?>
                        <article class="oc-comparison-card <?php echo $is_featured ? 'featured' : ''; ?>">
                            <?php if ( $is_featured ) : ?>
                                <div class="oc-card-badge oc-featured">
                                    <?php esc_html_e( 'Recommended', 'odds-comparison' ); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="oc-card-header">
                                <div class="oc-operator-logo">
                                    <?php if ( has_post_thumbnail() ) : ?>
                                        <?php the_post_thumbnail( 'medium', array( 'alt' => get_the_title() ) ); ?>
                                    <?php else : ?>
                                        <span class="oc-logo-fallback"><?php echo esc_html( get_the_title()[0] ); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="oc-operator-basic-info">
                                    <h2 class="oc-operator-name">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h2>
                                    
                                    <?php if ( $license_name ) : ?>
                                        <span class="oc-license-badge"><?php echo esc_html( $license_name ); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ( $rating ) : ?>
                                        <div class="oc-rating-display">
                                            <div class="oc-stars">
                                                <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                                    <span class="star <?php echo $i <= round( $rating ) ? 'filled' : ''; ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="oc-rating-value"><?php echo esc_html( number_format( $rating, 1 ) ); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="oc-card-bonus">
                                <span class="oc-bonus-label"><?php esc_html_e( 'Welcome Bonus', 'odds-comparison' ); ?></span>
                                <span class="oc-bonus-value"><?php echo esc_html( $bonus_text ); ?></span>
                                <?php if ( $bonus_type && isset( $bonus_types[ $bonus_type ] ) ) : ?>
                                    <span class="oc-bonus-type-badge"><?php echo esc_html( $bonus_types[ $bonus_type ] ); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="oc-card-details">
                                <?php if ( $min_deposit ) : ?>
                                    <div class="oc-detail-item">
                                        <span class="oc-detail-label"><?php esc_html_e( 'Min. Deposit', 'odds-comparison' ); ?></span>
                                        <span class="oc-detail-value"><?php echo esc_html( $min_deposit ); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ( $payment_methods_str ) : ?>
                                    <div class="oc-detail-item">
                                        <span class="oc-detail-label"><?php esc_html_e( 'Payments', 'odds-comparison' ); ?></span>
                                        <span class="oc-detail-value"><?php echo esc_html( $payment_methods_str ); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ( ! empty( $pros ) && is_array( $pros ) ) : ?>
                                <div class="oc-card-pros">
                                    <span class="oc-pros-label"><?php esc_html_e( 'Pros', 'odds-comparison' ); ?></span>
                                    <ul class="oc-pros-list">
                                        <?php foreach ( array_slice( $pros, 0, 3 ) as $pro ) : ?>
                                            <?php if ( $pro ) : ?>
                                                <li><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html( $pro ); ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <div class="oc-card-actions">
                                <?php if ( $affiliate_url ) : ?>
                                    <a href="<?php echo esc_url( $affiliate_url ); ?>" 
                                       class="button oc-visit-btn" 
                                       target="_blank" 
                                       rel="nofollow sponsored"
                                       data-operator-id="<?php echo absint( $operator_id ); ?>">
                                        <?php esc_html_e( 'Visit Site', 'odds-comparison' ); ?>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php the_permalink(); ?>" class="oc-review-link">
                                    <?php esc_html_e( 'Read Full Review', 'odds-comparison' ); ?>
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                    
                    <?php wp_reset_postdata(); ?>
                    
                    <!-- Pagination -->
                    <?php if ( $operators_query->max_num_pages > 1 ) : ?>
                        <nav class="oc-pagination">
                            <?php
                            echo paginate_links( array(
                                'base'    => add_query_arg( 'paged', '%#%' ),
                                'format'  => '?paged=%#%',
                                'current' => max( 1, get_query_var( 'paged' ) ),
                                'total'   => $operators_query->max_num_pages,
                                'prev_text' => __( '&laquo; Previous', 'odds-comparison' ),
                                'next_text' => __( 'Next &raquo;', 'odds-comparison' ),
                            ) );
                            ?>
                        </nav>
                    <?php endif; ?>
                    
                else :
                    ?>
                    <div class="oc-no-results">
                        <p><?php esc_html_e( 'No operators found matching your criteria.', 'odds-comparison' ); ?></p>
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'operator' ) ); ?>" class="button">
                            <?php esc_html_e( 'View All Operators', 'odds-comparison' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php get_footer(); ?>

