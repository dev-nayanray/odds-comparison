<?php
/**
 * Operators Archive Template with Sidebar
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

/**
 * Build Query Args
 */
$paged = max( 1, get_query_var( 'paged' ) );

$args = array(
    'post_type'      => 'operator',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'orderby'        => 'meta_value_num',
    'meta_key'       => 'oc_operator_rating',
    'order'          => 'DESC',
);

/**
 * License filter
 */
$license = isset( $_GET['license'] ) ? sanitize_text_field( $_GET['license'] ) : '';
if ( ! empty( $license ) ) {
    $args['meta_query'] = array(
        array(
            'key'     => 'oc_operator_license',
            'value'   => $license,
            'compare' => 'LIKE',
        ),
    );
}

/**
 * Bonus type filter
 */
$bonus_filter = isset( $_GET['bonus'] ) ? sanitize_text_field( $_GET['bonus'] ) : '';
if ( ! empty( $bonus_filter ) ) {
    if ( ! isset( $args['meta_query'] ) ) {
        $args['meta_query'] = array();
    }
    $args['meta_query'][] = array(
        'key'     => 'oc_operator_bonus_type',
        'value'   => $bonus_filter,
        'compare' => 'LIKE',
    );
}

$operators_query = new WP_Query( $args );

/**
 * Get operator count by license
 */
function oc_get_operators_by_license_count() {
    $args = array(
        'post_type'      => 'operator',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );
    
    $query = new WP_Query( $args );
    $operators = $query->posts;
    
    $by_license = array();
    foreach ( $operators as $operator ) {
        $licenses = get_post_meta( $operator->ID, 'oc_operator_license', true );
        if ( is_array( $licenses ) ) {
            foreach ( $licenses as $lic ) {
                if ( ! isset( $by_license[ $lic ] ) ) {
                    $by_license[ $lic ] = 0;
                }
                $by_license[ $lic ]++;
            }
        }
    }
    
    return $by_license;
}

$license_counts = oc_get_operators_by_license_count();
?>

<div class="oc-operators-archive-with-sidebar">

    <!-- Main Content -->
    <main class="oc-operators-archive-main">
    
        <!-- Archive Header -->
        <header class="oc-archive-header">
            <h1 class="oc-archive-title">
                <?php esc_html_e( 'Betting Operators', 'odds-comparison' ); ?>
            </h1>

            <p class="oc-archive-description">
                <?php esc_html_e( 'Compare the best online betting operators, their bonuses, licenses, and features.', 'odds-comparison' ); ?>
            </p>
        </header>

        <!-- Filters -->
        <div class="oc-archive-filters">

            <!-- License Filter -->
            <div class="oc-filter-group">
                <label><?php esc_html_e( 'License:', 'odds-comparison' ); ?></label>
                <select id="oc-filter-license" class="oc-select" onchange="if (this.value) window.location.href = this.value;">
                    <option value=""><?php esc_html_e( 'All Licenses', 'odds-comparison' ); ?></option>
                    <?php foreach ( $license_counts as $lic => $count ) : ?>
                        <option value="<?php echo esc_url( add_query_arg( 'license', urlencode( $lic ) ) ); ?>"
                            <?php selected( $lic, $license ); ?>>
                            <?php echo esc_html( $lic ); ?> (<?php echo esc_html( $count ); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Bonus Type Filter -->
            <div class="oc-filter-group">
                <label><?php esc_html_e( 'Bonus:', 'odds-comparison' ); ?></label>
                <select id="oc-filter-bonus" class="oc-select" onchange="if (this.value) window.location.href = this.value;">
                    <option value=""><?php esc_html_e( 'All Bonuses', 'odds-comparison' ); ?></option>
                    <option value="<?php echo esc_url( add_query_arg( 'bonus', urlencode( 'Welcome Bonus' ) ) ); ?>"
                        <?php selected( 'Welcome Bonus', $bonus_filter ); ?>>
                        <?php esc_html_e( 'Welcome Bonus', 'odds-comparison' ); ?>
                    </option>
                    <option value="<?php echo esc_url( add_query_arg( 'bonus', urlencode( 'Free Bet' ) ) ); ?>"
                        <?php selected( 'Free Bet', $bonus_filter ); ?>>
                        <?php esc_html_e( 'Free Bet', 'odds-comparison' ); ?>
                    </option>
                    <option value="<?php echo esc_url( add_query_arg( 'bonus', urlencode( 'Deposit Bonus' ) ) ); ?>"
                        <?php selected( 'Deposit Bonus', $bonus_filter ); ?>>
                        <?php esc_html_e( 'Deposit Bonus', 'odds-comparison' ); ?>
                    </option>
                    <option value="<?php echo esc_url( add_query_arg( 'bonus', urlencode( 'No Deposit Bonus' ) ) ); ?>"
                        <?php selected( 'No Deposit Bonus', $bonus_filter ); ?>>
                        <?php esc_html_e( 'No Deposit Bonus', 'odds-comparison' ); ?>
                    </option>
                </select>
            </div>

        </div>

        <!-- Operators Grid -->
        <div id="oc-operators-list" class="oc-operators-grid">

            <?php if ( $operators_query->have_posts() ) : ?>

                <?php while ( $operators_query->have_posts() ) : $operators_query->the_post(); ?>

                    <?php
                    $operator_id   = get_the_ID();
                    $logo_url      = get_post_meta( $operator_id, 'oc_operator_logo', true );
                    $rating        = get_post_meta( $operator_id, 'oc_operator_rating', true );
                    $review_count  = get_post_meta( $operator_id, 'oc_operator_review_count', true ) ?: 0;
                    $bonus_amount  = get_post_meta( $operator_id, 'oc_operator_bonus_amount', true );
                    $bonus_type    = get_post_meta( $operator_id, 'oc_operator_bonus_type', true );
                    $license_arr   = get_post_meta( $operator_id, 'oc_operator_license', true );
                    $website_url   = get_post_meta( $operator_id, 'oc_operator_website', true );
                    $is_featured   = (bool) get_post_meta( $operator_id, 'oc_featured_operator', true );
                    ?>

                    <article class="oc-operator-card<?php echo $is_featured ? ' featured' : ''; ?>">

                        <?php if ( $is_featured ) : ?>
                            <span class="oc-card-badge oc-featured"><?php esc_html_e( 'Featured', 'odds-comparison' ); ?></span>
                        <?php endif; ?>

                        <div class="oc-operator-logo">
                            <?php if ( $logo_url ) : ?>
                                <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php the_title_attribute(); ?>">
                            <?php else : ?>
                                <div class="oc-logo-fallback">
                                    <?php echo esc_html( substr( get_the_title(), 0, 2 ) ); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <h2 class="oc-operator-name">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>

                        <!-- Rating -->
                        <div class="oc-rating">
                            <div class="oc-stars">
                                <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                    <span class="star<?php echo $i <= round( $rating ) ? ' filled' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <span class="oc-rating-value"><?php echo esc_html( number_format( $rating, 1 ) ); ?></span>
                            <span class="oc-review-count">(<?php echo esc_html( sprintf( _n( '%d review', '%d reviews', $review_count, 'odds-comparison' ), $review_count ) ); ?>)</span>
                        </div>

                        <!-- Bonus -->
                        <?php if ( $bonus_amount ) : ?>
                            <div class="oc-operator-bonus">
                                <span class="oc-bonus-amount"><?php echo esc_html( $bonus_amount ); ?></span>
                                <?php if ( $bonus_type ) : ?>
                                    <span class="oc-bonus-type"><?php echo esc_html( $bonus_type ); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- License Badge -->
                        <?php if ( $license_arr && is_array( $license_arr ) && ! empty( $license_arr ) ) : ?>
                            <div class="oc-license-badges">
                                <?php foreach ( $license_arr as $lic ) : ?>
                                    <span class="oc-license-badge"><?php echo esc_html( $lic ); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Quick Pros -->
                        <ul class="oc-operator-pros-mini">
                            <?php
                            // Get first 3 pros from meta
                            $pros = get_post_meta( $operator_id, 'oc_operator_pros', true );
                            if ( is_array( $pros ) && ! empty( $pros ) ) :
                                $display_pros = array_slice( $pros, 0, 3 );
                                foreach ( $display_pros as $pro ) :
                            ?>
                                <li>
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    <?php echo esc_html( $pro ); ?>
                                </li>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </ul>

                        <!-- Actions -->
                        <div class="oc-operator-actions">
                            <a href="<?php the_permalink(); ?>" class="oc-review-btn">
                                <?php esc_html_e( 'Read Review', 'odds-comparison' ); ?>
                            </a>
                            <?php if ( $website_url ) : ?>
                                <a href="<?php echo esc_url( $website_url ); ?>" class="oc-visit-btn" target="_blank" rel="nofollow noopener">
                                    <?php esc_html_e( 'Visit', 'odds-comparison' ); ?>
                                </a>
                            <?php endif; ?>
                        </div>

                    </article>

                <?php endwhile; ?>

                <!-- Pagination -->
                <?php
                echo paginate_links( array(
                    'total' => $operators_query->max_num_pages,
                ) );
                ?>

            <?php else : ?>

                <p class="oc-no-operators"><?php esc_html_e( 'No operators found.', 'odds-comparison' ); ?></p>

            <?php endif; wp_reset_postdata(); ?>

        </div>
    </main>

    <!-- Sidebar -->
    <aside class="oc-archive-sidebar">
        <?php dynamic_sidebar( 'sidebar-1' ); ?>
    </aside>

</div>

<?php get_footer(); ?>

