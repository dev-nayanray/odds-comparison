<?php
/**
 * Comparison Page Template
 *
 * Template for displaying the advanced filterable comparison tool.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

// Get filter parameters
$bonus_type = isset($_GET['bonus_type']) ? sanitize_text_field($_GET['bonus_type']) : '';
$payment_method = isset($_GET['payment_method']) ? sanitize_text_field($_GET['payment_method']) : '';
$min_rating = isset($_GET['min_rating']) ? floatval($_GET['min_rating']) : 0;
$features = isset($_GET['features']) ? $_GET['features'] : array();
$sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'rating';

// Query operators with filters
$args = array(
    'post_type' => 'operator',
    'posts_per_page' => -1,
    'meta_query' => array(
        'relation' => 'AND',
    ),
    'tax_query' => array(
        'relation' => 'AND',
    ),
);

// Add rating filter
if ($min_rating > 0) {
    $args['meta_query'][] = array(
        'key' => 'oc_operator_rating',
        'value' => $min_rating,
        'compare' => '>=',
        'type' => 'DECIMAL'
    );
}

// Add bonus type filter
if ($bonus_type) {
    $args['meta_query'][] = array(
        'key' => 'oc_bonus_type',
        'value' => $bonus_type,
        'compare' => '='
    );
}

// Add payment method filter
if ($payment_method) {
    $args['meta_query'][] = array(
        'key' => 'oc_payment_methods',
        'value' => $payment_method,
        'compare' => 'LIKE'
    );
}

// Add feature filters
if (!empty($features)) {
    foreach ($features as $feature) {
        $args['meta_query'][] = array(
            'key' => 'oc_' . $feature,
            'value' => '1',
            'compare' => '='
        );
    }
}

// Add sorting
switch ($sort_by) {
    case 'rating':
        $args['meta_key'] = 'oc_operator_rating';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    case 'bonus':
        $args['meta_key'] = 'oc_bonus_amount';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    case 'name':
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';
        break;
}

$operators_query = new WP_Query($args);
$operators = $operators_query->posts;

// Get unique values for filters
$all_bonus_types = array();
$all_payment_methods = array();

$all_operators = get_posts(array(
    'post_type' => 'operator',
    'posts_per_page' => -1,
    'fields' => 'ids'
));

foreach ($all_operators as $op_id) {
    $bonus_type_val = get_post_meta($op_id, 'oc_bonus_type', true);
    if ($bonus_type_val && !in_array($bonus_type_val, $all_bonus_types)) {
        $all_bonus_types[] = $bonus_type_val;
    }

    $payment_methods = get_post_meta($op_id, 'oc_payment_methods', true);
    if (is_array($payment_methods)) {
        foreach ($payment_methods as $method) {
            if ($method && !in_array($method, $all_payment_methods)) {
                $all_payment_methods[] = $method;
            }
        }
    }
}
?>

<div class="oc-comparison-container">
    <div class="oc-comparison-header">
        <h1><?php esc_html_e( 'Compare Sportsbooks', 'odds-comparison' ); ?></h1>
        <p><?php esc_html_e( 'Find the best sportsbook that matches your needs with our advanced comparison tool.', 'odds-comparison' ); ?></p>
    </div>

    <!-- Filters Section -->
    <div class="oc-filters-section">
        <div class="oc-filters-toggle">
            <button id="toggle-filters" class="button">
                <span class="dashicons dashicons-filter"></span>
                <?php esc_html_e( 'Filters', 'odds-comparison' ); ?>
                <span class="filter-count"><?php echo count(array_filter(array($bonus_type, $payment_method, $min_rating > 0 ? 'rating' : '', !empty($features) ? 'features' : ''))); ?></span>
            </button>
        </div>

        <div id="filters-panel" class="oc-filters-panel">
            <form method="GET" action="" class="oc-filters-form">
                <div class="oc-filter-row">
                    <div class="oc-filter-group">
                        <label for="bonus_type"><?php esc_html_e( 'Bonus Type', 'odds-comparison' ); ?></label>
                        <select name="bonus_type" id="bonus_type">
                            <option value=""><?php esc_html_e( 'All Bonus Types', 'odds-comparison' ); ?></option>
                            <?php foreach ($all_bonus_types as $type) : ?>
                                <option value="<?php echo esc_attr($type); ?>" <?php selected($bonus_type, $type); ?>>
                                    <?php echo esc_html(ucfirst($type)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="oc-filter-group">
                        <label for="payment_method"><?php esc_html_e( 'Payment Method', 'odds-comparison' ); ?></label>
                        <select name="payment_method" id="payment_method">
                            <option value=""><?php esc_html_e( 'All Payment Methods', 'odds-comparison' ); ?></option>
                            <?php foreach ($all_payment_methods as $method) : ?>
                                <option value="<?php echo esc_attr($method); ?>" <?php selected($payment_method, $method); ?>>
                                    <?php echo esc_html($method); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="oc-filter-group">
                        <label for="min_rating"><?php esc_html_e( 'Minimum Rating', 'odds-comparison' ); ?></label>
                        <select name="min_rating" id="min_rating">
                            <option value="0"><?php esc_html_e( 'Any Rating', 'odds-comparison' ); ?></option>
                            <option value="4.5" <?php selected($min_rating, 4.5); ?>>4.5+ Stars</option>
                            <option value="4.0" <?php selected($min_rating, 4.0); ?>>4.0+ Stars</option>
                            <option value="3.5" <?php selected($min_rating, 3.5); ?>>3.5+ Stars</option>
                            <option value="3.0" <?php selected($min_rating, 3.0); ?>>3.0+ Stars</option>
                        </select>
                    </div>

                    <div class="oc-filter-group">
                        <label><?php esc_html_e( 'Features', 'odds-comparison' ); ?></label>
                        <div class="oc-features-checkboxes">
                            <label class="checkbox-label">
                                <input type="checkbox" name="features[]" value="live_betting" <?php checked(in_array('live_betting', $features)); ?>>
                                <?php esc_html_e( 'Live Betting', 'odds-comparison' ); ?>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="features[]" value="cash_out" <?php checked(in_array('cash_out', $features)); ?>>
                                <?php esc_html_e( 'Cash Out', 'odds-comparison' ); ?>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="features[]" value="mobile_app" <?php checked(in_array('mobile_app', $features)); ?>>
                                <?php esc_html_e( 'Mobile App', 'odds-comparison' ); ?>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="features[]" value="live_streaming" <?php checked(in_array('live_streaming', $features)); ?>>
                                <?php esc_html_e( 'Live Streaming', 'odds-comparison' ); ?>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="oc-filter-actions">
                    <button type="submit" class="button oc-apply-filters"><?php esc_html_e( 'Apply Filters', 'odds-comparison' ); ?></button>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="button oc-clear-filters"><?php esc_html_e( 'Clear All', 'odds-comparison' ); ?></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Sort Options -->
    <div class="oc-sort-section">
        <div class="oc-sort-options">
            <span><?php esc_html_e( 'Sort by:', 'odds-comparison' ); ?></span>
            <a href="<?php echo add_query_arg('sort_by', 'rating'); ?>" class="sort-link <?php echo $sort_by === 'rating' ? 'active' : ''; ?>">
                <?php esc_html_e( 'Rating', 'odds-comparison' ); ?>
            </a>
            <a href="<?php echo add_query_arg('sort_by', 'bonus'); ?>" class="sort-link <?php echo $sort_by === 'bonus' ? 'active' : ''; ?>">
                <?php esc_html_e( 'Bonus Amount', 'odds-comparison' ); ?>
            </a>
            <a href="<?php echo add_query_arg('sort_by', 'name'); ?>" class="sort-link <?php echo $sort_by === 'name' ? 'active' : ''; ?>">
                <?php esc_html_e( 'Name', 'odds-comparison' ); ?>
            </a>
        </div>
        <div class="oc-results-count">
            <?php printf( esc_html__( '%d operators found', 'odds-comparison' ), count($operators) ); ?>
        </div>
    </div>

    <!-- Comparison Table -->
    <div class="oc-comparison-table-container">
        <div class="oc-comparison-table-wrapper">
            <table class="oc-comparison-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Sportsbook', 'odds-comparison' ); ?></th>
                        <th><?php esc_html_e( 'Rating', 'odds-comparison' ); ?></th>
                        <th><?php esc_html_e( 'Bonus', 'odds-comparison' ); ?></th>
                        <th><?php esc_html_e( 'Features', 'odds-comparison' ); ?></th>
                        <th><?php esc_html_e( 'Payment Methods', 'odds-comparison' ); ?></th>
                        <th><?php esc_html_e( 'Min Deposit', 'odds-comparison' ); ?></th>
                        <th><?php esc_html_e( 'Action', 'odds-comparison' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($operators)) : ?>
                        <?php foreach ($operators as $operator) : ?>
                            <?php
                            $operator_id = $operator->ID;
                            $rating = get_post_meta($operator_id, 'oc_operator_rating', true);
                            $bonus_amount = get_post_meta($operator_id, 'oc_bonus_amount', true);
                            $bonus_type = get_post_meta($operator_id, 'oc_bonus_type', true);
                            $bonus_code = get_post_meta($operator_id, 'oc_bonus_code', true);
                            $affiliate_url = get_post_meta($operator_id, 'oc_affiliate_url', true);
                            $min_deposit = get_post_meta($operator_id, 'oc_min_deposit', true);
                            $live_betting = get_post_meta($operator_id, 'oc_live_betting', true);
                            $cash_out = get_post_meta($operator_id, 'oc_cash_out', true);
                            $mobile_app = get_post_meta($operator_id, 'oc_mobile_app', true);
                            $live_streaming = get_post_meta($operator_id, 'oc_live_streaming', true);
                            $payment_methods = get_post_meta($operator_id, 'oc_payment_methods', true);
                            ?>
                            <tr>
                                <td class="operator-info">
                                    <div class="operator-logo">
                                        <?php if (has_post_thumbnail($operator_id)) : ?>
                                            <?php echo get_the_post_thumbnail($operator_id, 'thumbnail', array('alt' => get_the_title($operator_id))); ?>
                                        <?php else : ?>
                                            <div class="operator-name"><?php echo esc_html(get_the_title($operator_id)); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="operator-name-mobile"><?php echo esc_html(get_the_title($operator_id)); ?></div>
                                </td>
                                <td class="rating-cell">
                                    <?php if ($rating) : ?>
                                        <div class="rating-display">
                                            <div class="stars">
                                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                    <span class="star <?php echo $i <= round($rating) ? 'filled' : ''; ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="rating-value"><?php echo esc_html(number_format($rating, 1)); ?></span>
                                        </div>
                                    <?php else : ?>
                                        <span class="no-rating"><?php esc_html_e( 'N/A', 'odds-comparison' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="bonus-cell">
                                    <?php if ($bonus_amount) : ?>
                                        <div class="bonus-info">
                                            <div class="bonus-amount"><?php echo esc_html($bonus_amount); ?></div>
                                            <div class="bonus-type"><?php echo esc_html(ucfirst($bonus_type)); ?></div>
                                            <?php if ($bonus_code) : ?>
                                                <div class="bonus-code">Code: <?php echo esc_html($bonus_code); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else : ?>
                                        <span class="no-bonus"><?php esc_html_e( 'No bonus', 'odds-comparison' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="features-cell">
                                    <div class="features-list">
                                        <?php if ($live_betting) : ?>
                                            <span class="feature-badge" title="<?php esc_attr_e( 'Live Betting', 'odds-comparison' ); ?>">
                                                <span class="dashicons dashicons-yes"></span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($cash_out) : ?>
                                            <span class="feature-badge" title="<?php esc_attr_e( 'Cash Out', 'odds-comparison' ); ?>">
                                                <span class="dashicons dashicons-yes"></span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($mobile_app) : ?>
                                            <span class="feature-badge" title="<?php esc_attr_e( 'Mobile App', 'odds-comparison' ); ?>">
                                                <span class="dashicons dashicons-yes"></span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($live_streaming) : ?>
                                            <span class="feature-badge" title="<?php esc_attr_e( 'Live Streaming', 'odds-comparison' ); ?>">
                                                <span class="dashicons dashicons-yes"></span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="payment-cell">
                                    <?php if (is_array($payment_methods) && !empty($payment_methods)) : ?>
                                        <div class="payment-methods">
                                            <?php
                                            $display_methods = array_slice($payment_methods, 0, 3);
                                            foreach ($display_methods as $method) :
                                            ?>
                                                <span class="payment-method"><?php echo esc_html($method); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($payment_methods) > 3) : ?>
                                                <span class="more-methods">+<?php echo count($payment_methods) - 3; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else : ?>
                                        <span class="no-payment"><?php esc_html_e( 'N/A', 'odds-comparison' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="deposit-cell">
                                    <?php if ($min_deposit) : ?>
                                        <span class="min-deposit"><?php echo esc_html($min_deposit); ?></span>
                                    <?php else : ?>
                                        <span class="no-deposit"><?php esc_html_e( 'N/A', 'odds-comparison' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-cell">
                                    <a href="<?php echo esc_url(get_permalink($operator_id)); ?>" class="button review-btn">
                                        <?php esc_html_e( 'Review', 'odds-comparison' ); ?>
                                    </a>
                                    <?php if ($affiliate_url) : ?>
                                        <a href="<?php echo esc_url($affiliate_url); ?>" class="button visit-btn" target="_blank" rel="nofollow">
                                            <?php esc_html_e( 'Visit', 'odds-comparison' ); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="no-results">
                                <div class="no-results-message">
                                    <h3><?php esc_html_e( 'No operators found', 'odds-comparison' ); ?></h3>
                                    <p><?php esc_html_e( 'Try adjusting your filters to find more options.', 'odds-comparison' ); ?></p>
                                    <a href="<?php echo esc_url(get_permalink()); ?>" class="button"><?php esc_html_e( 'Clear Filters', 'odds-comparison' ); ?></a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Comparison Tips -->
    <div class="oc-comparison-tips">
        <h2><?php esc_html_e( 'How to Choose the Right Sportsbook', 'odds-comparison' ); ?></h2>
        <div class="tips-grid">
            <div class="tip-card">
                <h3><?php esc_html_e( 'Check Licensing', 'odds-comparison' ); ?></h3>
                <p><?php esc_html_e( 'Always choose licensed and regulated sportsbooks for safe betting.', 'odds-comparison' ); ?></p>
            </div>
            <div class="tip-card">
                <h3><?php esc_html_e( 'Compare Bonuses', 'odds-comparison' ); ?></h3>
                <p><?php esc_html_e( 'Look for attractive welcome bonuses and ongoing promotions.', 'odds-comparison' ); ?></p>
            </div>
            <div class="tip-card">
                <h3><?php esc_html_e( 'Review Payment Options', 'odds-comparison' ); ?></h3>
                <p><?php esc_html_e( 'Ensure your preferred payment methods are supported.', 'odds-comparison' ); ?></p>
            </div>
            <div class="tip-card">
                <h3><?php esc_html_e( 'Consider Features', 'odds-comparison' ); ?></h3>
                <p><?php esc_html_e( 'Choose sportsbooks with features you need like live betting and cash out.', 'odds-comparison' ); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle filters panel
    const toggleBtn = document.getElementById('toggle-filters');
    const filtersPanel = document.getElementById('filters-panel');

    if (toggleBtn && filtersPanel) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            filtersPanel.classList.toggle('active');
            toggleBtn.classList.toggle('active');
        });
    }

    // Update filter count
    function updateFilterCount() {
        const form = document.querySelector('.oc-filters-form');
        if (!form) return;

        const inputs = form.querySelectorAll('select, input[type="checkbox"]:checked');
        let count = 0;

        inputs.forEach(input => {
            if (input.type === 'checkbox' || (input.type === 'select-one' && input.value)) {
                count++;
            }
        });

        const countElement = document.querySelector('.filter-count');
        if (countElement) {
            countElement.textContent = count;
        }
    }

    // Update count on change
    document.addEventListener('change', updateFilterCount);
    updateFilterCount();
});
</script>

<?php get_footer(); ?>
