/**
 * Odds Comparison Main JavaScript
 *
 * Handles dynamic functionality for the odds comparison plugin.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Main Odds Comparison object
     */
    var OddsComparison = {

        /**
         * Initialize the plugin
         */
        init: function() {
            this.bindEvents();
            this.initTabs();
            this.initFilters();
            this.initReviews();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            $(document).on('click', '.oc-tab-button', this.handleTabClick);
            $(document).on('click', '.oc-filters-toggle .button', this.toggleFilters);
            $(document).on('submit', '.oc-filters-form', this.handleFilterSubmit);
            $(document).on('change', '.oc-filters-form input, .oc-filters-form select', this.updateFilterCount);
            $(document).on('click', '.sort-link', this.handleSortClick);
            $(document).on('submit', '.oc-review-form', this.handleReviewSubmit);
            $(document).on('click', '.oc-load-more', this.loadMoreOperators);
        },

        /**
         * Initialize tab functionality
         */
        initTabs: function() {
            var activeTab = this.getUrlParameter('tab') || 'overview';
            this.showTab(activeTab);
        },

        /**
         * Handle tab click
         */
        handleTabClick: function(e) {
            e.preventDefault();
            var tabName = $(this).data('tab');
            OddsComparison.showTab(tabName);
            OddsComparison.updateUrlParameter('tab', tabName);
        },

        /**
         * Show specific tab
         */
        showTab: function(tabName) {
            $('.oc-tab-button').removeClass('active');
            $('.oc-tab-content').removeClass('active');

            $('.oc-tab-button[data-tab="' + tabName + '"]').addClass('active');
            $('#' + tabName + '-tab').addClass('active');
        },

        /**
         * Initialize filters
         */
        initFilters: function() {
            this.updateFilterCount();
        },

        /**
         * Toggle filters panel
         */
        toggleFilters: function(e) {
            e.preventDefault();
            $('.oc-filters-panel').slideToggle();
            $(this).toggleClass('active');
        },

        /**
         * Handle filter form submission
         */
        handleFilterSubmit: function(e) {
            e.preventDefault();

            var formData = $(this).serialize();
            var currentUrl = window.location.href.split('?')[0];

            // Show loading state
            $('.oc-comparison-table-container').addClass('oc-loading');

            // Update URL
            window.history.pushState({}, '', currentUrl + '?' + formData);

            // Reload comparison data
            OddsComparison.loadComparisonData(formData);
        },

        /**
         * Update filter count
         */
        updateFilterCount: function() {
            var count = 0;
            var form = $('.oc-filters-form');

            if (form.length) {
                // Count select fields with values
                form.find('select').each(function() {
                    if ($(this).val()) {
                        count++;
                    }
                });

                // Count checked checkboxes
                form.find('input[type="checkbox"]:checked').each(function() {
                    count++;
                });

                $('.filter-count').text(count);
            }
        },

        /**
         * Handle sort click
         */
        handleSortClick: function(e) {
            e.preventDefault();
            var sortBy = $(this).data('sort') || $(this).attr('href').split('sort_by=')[1];

            // Update URL
            var currentUrl = OddsComparison.updateUrlParameter('sort_by', sortBy);

            // Update active state
            $('.sort-link').removeClass('active');
            $(this).addClass('active');

            // Reload data
            $('.oc-comparison-table-container').addClass('oc-loading');
            OddsComparison.loadComparisonData(window.location.search.substring(1));
        },

        /**
         * Load comparison data via AJAX
         */
        loadComparisonData: function(formData) {
            $.ajax({
                url: oc_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'oc_load_comparison_data',
                    nonce: oc_ajax.nonce,
                    filters: this.parseFormData(formData)
                },
                success: function(response) {
                    if (response.success) {
                        OddsComparison.updateComparisonTable(response.data.operators);
                        OddsComparison.updateResultsCount(response.data.count);
                    }
                },
                error: function() {
                    alert('Error loading comparison data. Please try again.');
                },
                complete: function() {
                    $('.oc-comparison-table-container').removeClass('oc-loading');
                }
            });
        },

        /**
         * Parse form data into filters object
         */
        parseFormData: function(formData) {
            var filters = {};
            var pairs = formData.split('&');

            for (var i = 0; i < pairs.length; i++) {
                var pair = pairs[i].split('=');
                var key = decodeURIComponent(pair[0]);
                var value = decodeURIComponent(pair[1] || '');

                if (key.indexOf('features[') === 0) {
                    if (!filters.features) {
                        filters.features = [];
                    }
                    filters.features.push(value);
                } else if (key === 'min_rating') {
                    filters.min_rating = parseFloat(value);
                } else {
                    filters[key] = value;
                }
            }

            return filters;
        },

        /**
         * Update comparison table with new data
         */
        updateComparisonTable: function(operators) {
            var tbody = $('.oc-comparison-table tbody');

            if (operators.length === 0) {
                tbody.html('<tr><td colspan="7" class="no-results"><div class="no-results-message"><h3>No operators found</h3><p>Try adjusting your filters to find more options.</p></div></td></tr>');
                return;
            }

            var html = '';
            operators.forEach(function(operator) {
                html += OddsComparison.generateOperatorRow(operator);
            });

            tbody.html(html);
        },

        /**
         * Generate HTML for operator row
         */
        generateOperatorRow: function(operator) {
            var stars = '';
            for (var i = 1; i <= 5; i++) {
                stars += '<span class="star ' + (i <= Math.round(operator.rating) ? 'filled' : '') + '">★</span>';
            }

            var features = '';
            if (operator.live_betting) {
                features += '<span class="feature-badge" title="Live Betting"><span class="dashicons dashicons-yes"></span></span>';
            }
            if (operator.cash_out) {
                features += '<span class="feature-badge" title="Cash Out"><span class="dashicons dashicons-yes"></span></span>';
            }
            if (operator.mobile_app) {
                features += '<span class="feature-badge" title="Mobile App"><span class="dashicons dashicons-yes"></span></span>';
            }
            if (operator.live_streaming) {
                features += '<span class="feature-badge" title="Live Streaming"><span class="dashicons dashicons-yes"></span></span>';
            }

            var paymentMethods = '';
            if (operator.payment_methods && operator.payment_methods.length > 0) {
                var displayMethods = operator.payment_methods.slice(0, 3);
                displayMethods.forEach(function(method) {
                    paymentMethods += '<span class="payment-method">' + method + '</span>';
                });
                if (operator.payment_methods.length > 3) {
                    paymentMethods += '<span class="more-methods">+' + (operator.payment_methods.length - 3) + '</span>';
                }
            }

            return '<tr>' +
                '<td class="operator-info">' +
                    '<div class="operator-logo">' +
                        (operator.thumbnail ? '<img src="' + operator.thumbnail + '" alt="' + operator.title + '">' : '<div class="operator-name">' + operator.title + '</div>') +
                    '</div>' +
                    '<div class="operator-name-mobile">' + operator.title + '</div>' +
                '</td>' +
                '<td class="rating-cell">' +
                    '<div class="rating-display">' +
                        '<div class="stars">' + stars + '</div>' +
                        '<span class="rating-value">' + (operator.rating || 'N/A') + '</span>' +
                    '</div>' +
                '</td>' +
                '<td class="bonus-cell">' +
                    (operator.bonus_amount ? '<div class="bonus-info"><div class="bonus-amount">' + operator.bonus_amount + '</div><div class="bonus-type">' + (operator.bonus_type || '') + '</div>' + (operator.bonus_code ? '<div class="bonus-code">Code: ' + operator.bonus_code + '</div>' : '') + '</div>' : '<span class="no-bonus">No bonus</span>') +
                '</td>' +
                '<td class="features-cell"><div class="features-list">' + features + '</div></td>' +
                '<td class="payment-cell">' + (paymentMethods || '<span class="no-payment">N/A</span>') + '</td>' +
                '<td class="deposit-cell">' + (operator.min_deposit ? '<span class="min-deposit">' + operator.min_deposit + '</span>' : '<span class="no-deposit">N/A</span>') + '</td>' +
                '<td class="action-cell">' +
                    '<a href="' + operator.permalink + '" class="button review-btn">Review</a>' +
                    (operator.affiliate_url ? '<a href="' + operator.affiliate_url + '" class="button visit-btn" target="_blank" rel="nofollow">Visit</a>' : '') +
                '</td>' +
            '</tr>';
        },

        /**
         * Update results count
         */
        updateResultsCount: function(count) {
            $('.oc-results-count').text(count + ' operators found');
        },

        /**
         * Initialize reviews functionality
         */
        initReviews: function() {
            // Initialize star rating input
            $('.oc-rating-input input[type="radio"]').on('change', function() {
                var rating = $(this).val();
                $(this).closest('.oc-rating-input').find('label').removeClass('selected');
                $(this).next('label').addClass('selected');
            });
        },

        /**
         * Handle review submission
         */
        handleReviewSubmit: function(e) {
            e.preventDefault();

            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');

            // Disable submit button
            submitBtn.prop('disabled', true).text('Submitting...');

            $.ajax({
                url: oc_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'oc_submit_review',
                    nonce: oc_ajax.nonce,
                    form_data: form.serialize()
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        form.before('<div class="oc-notice oc-notice-success">Review submitted successfully! It will be published after moderation.</div>');

                        // Reset form
                        form[0].reset();

                        // Reload reviews
                        location.reload();
                    } else {
                        // Show error message
                        form.before('<div class="oc-notice oc-notice-error">' + (response.data || 'Error submitting review. Please try again.') + '</div>');
                    }
                },
                error: function() {
                    form.before('<div class="oc-notice oc-notice-error">Error submitting review. Please try again.</div>');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Submit Review');
                }
            });
        },

        /**
         * Load more operators
         */
        loadMoreOperators: function(e) {
            e.preventDefault();

            var button = $(this);
            var page = button.data('page') || 2;

            button.text('Loading...').prop('disabled', true);

            $.ajax({
                url: oc_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'oc_load_more_operators',
                    nonce: oc_ajax.nonce,
                    page: page,
                    filters: OddsComparison.parseFormData(window.location.search.substring(1))
                },
                success: function(response) {
                    if (response.success && response.data.operators.length > 0) {
                        var html = '';
                        response.data.operators.forEach(function(operator) {
                            html += OddsComparison.generateOperatorRow(operator);
                        });

                        $('.oc-comparison-table tbody').append(html);
                        button.data('page', page + 1);

                        if (!response.data.has_more) {
                            button.hide();
                        }
                    } else {
                        button.hide();
                    }
                },
                error: function() {
                    alert('Error loading more operators. Please try again.');
                },
                complete: function() {
                    button.text('Load More').prop('disabled', false);
                }
            });
        },

        /**
         * Get URL parameter
         */
        getUrlParameter: function(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        },

        /**
         * Update URL parameter
         */
        updateUrlParameter: function(key, value) {
            var url = window.location.href;
            var re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi");

            if (re.test(url)) {
                if (typeof value !== 'undefined' && value !== null) {
                    return url.replace(re, '$1' + key + "=" + value + '$2$3');
                } else {
                    var hash = url.split('#');
                    url = hash[0].replace(re, '$1$2').replace(/(&|\?)$/, '');
                    if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
                        url += '#' + hash[1];
                    }
                    return url;
                }
            } else {
                if (typeof value !== 'undefined' && value !== null) {
                    var separator = url.indexOf('?') !== -1 ? '&' : '?';
                    var hash = url.split('#');
                    url = hash[0] + separator + key + '=' + value;
                    if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
                        url += '#' + hash[1];
                    }
                    return url;
                } else {
                    return url;
                }
            }
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        OddsComparison.init();
    });

    /**
     * Handle browser back/forward buttons
     */
    $(window).on('popstate', function() {
        var tab = OddsComparison.getUrlParameter('tab');
        if (tab) {
            OddsComparison.showTab(tab);
        }
    });

})(jQuery);
