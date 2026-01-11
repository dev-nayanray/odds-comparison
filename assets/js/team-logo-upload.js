/**
 * Team Logo Upload Script
 *
 * Handles media uploader for team logos in taxonomy forms.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        var frame;

        // Handle upload button click
        $(document).on('click', '#oc_upload_team_logo', function(e) {
            e.preventDefault();

            // If frame already exists, open it
            if (frame) {
                frame.open();
                return;
            }

            // Create media frame
            frame = wp.media({
                title: oc_team_logo_upload_vars.select_title || 'Select Team Logo',
                button: {
                    text: oc_team_logo_upload_vars.button_text || 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            // Handle selection
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();

                // Update hidden input with attachment ID
                $('#oc_team_logo_id').val(attachment.id);

                // Update preview image
                var previewHtml = '<img src="' + attachment.url + '" alt="" style="max-width: 100px; max-height: 100px; margin-bottom: 10px;">';
                $('.team-logo-wrapper img').remove();
                $(previewHtml).insertBefore('#oc_upload_team_logo');

                // Show remove button
                $('#oc_remove_team_logo').show();
            });

            // Open frame
            frame.open();
        });

        // Handle remove button click
        $(document).on('click', '#oc_remove_team_logo', function(e) {
            e.preventDefault();

            // Clear hidden input
            $('#oc_team_logo_id').val('');

            // Remove preview image
            $('.team-logo-wrapper img').remove();

            // Hide remove button
            $(this).hide();
        });
    });

})(jQuery);
