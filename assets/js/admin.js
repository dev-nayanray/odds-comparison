/**
 * Odds Comparison Admin JavaScript
 *
 * Handles admin interface functionality for the odds comparison plugin.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Admin functionality
     */
    var OddsComparisonAdmin = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initRepeatableFields();
            this.initBulkActions();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            $(document).on('click', '.oc-add-license', this.addLicense);
            $(document).on('click', '.oc-remove-license', this.removeLicense);
            $(document).on('click', '.oc-add-sport', this.addSport);
            $(document).on('click', '.oc-remove-sport', this.removeSport);
            $(document).on('click', '.oc-bulk-update-rating', this.bulkUpdateRating);
            $(document).on('change', '.oc-bonus-type', this.toggleBonusFields);
            $(document).on('click', '.oc-export-operators', this.exportOperators);
            $(document).on('click', '#oc-add-odds', this.addOdds);
        },

        /**
         * Initialize repeatable fields
         */
        initRepeatableFields: function() {
            $('.oc-repeatable-field').each(function() {
                var container = $(this);
                var template = container.find('.oc-field-template').html();

                container.data('template', template);
                container.data('index', container.find('.oc-field-item').length);
            });
        },

        /**
         * Add license field
         */
        addLicense: function(e) {
            e.preventDefault();

            var container = $('#oc-licenses-container');
            var template = container.data('template') || '<div class="oc-field-item"><input type="text" name="oc_licenses[]" value="" /><button type="button" class="button oc-remove-license">Remove</button></div>';
            var index = container.data('index') || 0;

            var fieldHtml = template.replace(/\{\{index\}\}/g, index);
            container.find('.oc-field-list').append(fieldHtml);

            container.data('index', index + 1);
        },

        /**
         * Remove license field
         */
        removeLicense: function(e) {
            e.preventDefault();
            $(this).closest('.oc-field-item').remove();
        },

        /**
         * Add sport field
         */
        addSport: function(e) {
            e.preventDefault();

            var container = $('#oc-sports-container');
            var template = container.data('template') || '<div class="oc-field-item"><input type="text" name="oc_sports[]" value="" /><button type="button" class="button oc-remove-sport">Remove</button></div>';
            var index = container.data('index') || 0;

            var fieldHtml = template.replace(/\{\{index\}\}/g, index);
            container.find('.oc-field-list').append(fieldHtml);

            container.data('index', index + 1);
        },

        /**
         * Remove sport field
         */
        removeSport: function(e) {
            e.preventDefault();
            $(this).closest('.oc-field-item').remove();
        },

        /**
         * Bulk update ratings
         */
        bulkUpdateRating: function(e) {
            e.preventDefault();

            var newRating = prompt('Enter new rating (0-5):');
            if (newRating === null) return;

            newRating = parseFloat(newRating);
            if (isNaN(newRating) || newRating < 0 || newRating > 5) {
                alert('Please enter a valid rating between 0 and 5.');
                return;
            }

            var checkedBoxes = $('input[name="post[]"]:checked');
            if (checkedBoxes.length === 0) {
                alert('Please select operators to update.');
                return;
            }

            if (!confirm('Update rating to ' + newRating + ' for ' + checkedBoxes.length + ' operators?')) {
                return;
            }

            var postIds = [];
            checkedBoxes.each(function() {
                postIds.push($(this).val());
            });

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'oc_bulk_update_rating',
                    post_ids: postIds,
                    rating: newRating,
                    nonce: oc_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Ratings updated successfully!');
                        location.reload();
                    } else {
                        alert('Error updating ratings: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error updating ratings. Please try again.');
                }
            });
        },

        /**
         * Toggle bonus fields based on bonus type
         */
        toggleBonusFields: function() {
            var bonusType = $(this).val();
            var container = $(this).closest('.oc-bonus-fields');

            // Hide all conditional fields
            container.find('.oc-conditional-field').hide();

            // Show relevant fields based on bonus type
            switch (bonusType) {
                case 'welcome':
                    container.find('.oc-welcome-fields').show();
                    break;
                case 'deposit':
                    container.find('.oc-deposit-fields').show();
                    break;
                case 'free-bet':
                    container.find('.oc-freebet-fields').show();
                    break;
                case 'cashback':
                    container.find('.oc-cashback-fields').show();
                    break;
            }
        },

        /**
         * Export operators
         */
        exportOperators: function(e) {
            e.preventDefault();

            var format = $(this).data('format') || 'csv';

            // Create download link
            var downloadUrl = ajaxurl + '?action=oc_export_operators&format=' + format + '&nonce=' + oc_admin.nonce;
            window.location.href = downloadUrl;
        },

        /**
         * Add odds for a match
         */
        addOdds: function(e) {
            e.preventDefault();

            var button = $(this);
            var container = button.closest('.oc-odds-meta-box');
            var matchId = $('#post_ID').val();

            // Get form data
            var bookmakerId = container.find('#oc-odds-bookmaker').val();
            var oddsHome = container.find('#oc-odds-home').val();
            var oddsDraw = container.find('#oc-odds-draw').val();
            var oddsAway = container.find('#oc-odds-away').val();

            // Validate required fields
            if (!bookmakerId) {
                alert('Please select a bookmaker.');
                return;
            }

            if (!oddsHome && !oddsDraw && !oddsAway) {
                alert('Please enter at least one odds value.');
                return;
            }

            // Disable button and show loading
            button.prop('disabled', true).text('Saving...');

            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'oc_save_odds',
                    match_id: matchId,
                    bookmaker_id: bookmakerId,
                    odds_home: oddsHome || '',
                    odds_draw: oddsDraw || '',
                    odds_away: oddsAway || '',
                    nonce: ocAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Odds saved successfully!');
                        // Clear form
                        container.find('#oc-odds-bookmaker').val('');
                        container.find('#oc-odds-home, #oc-odds-draw, #oc-odds-away').val('');
                        // Refresh the odds list
                        location.reload();
                    } else {
                        alert('Error saving odds: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error saving odds. Please try again.');
                    console.error('AJAX Error:', error);
                },
                complete: function() {
                    // Re-enable button
                    button.prop('disabled', false).text('Add Odds');
                }
            });
        },

        /**
         * Initialize bulk actions
         */
        initBulkActions: function() {
            // Add custom bulk actions
            if (typeof inlineEditPost !== 'undefined') {
                var bulkActions = $('#bulk-action-selector-top, #bulk-action-selector-bottom');

                bulkActions.each(function() {
                    $(this).find('option:last').after('<option value="oc_bulk_update_rating">Update Rating</option>');
                });
            }
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        OddsComparisonAdmin.init();
    });

    /**
     * Handle bulk rating update from bulk actions
     */
    $(document).on('click', '#doaction, #doaction2', function(e) {
        var action = $(this).siblings('select').val();

        if (action === 'oc_bulk_update_rating') {
            e.preventDefault();
            OddsComparisonAdmin.bulkUpdateRating(e);
        }
    });

})(jQuery);
