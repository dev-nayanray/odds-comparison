<?php
/**
 * Contact Page Template
 *
 * Template for displaying the contact page with contact form and information.
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

// Handle form submission
$submission_message = '';
$form_errors = array();

if (isset($_POST['oc_contact_submit']) && wp_verify_nonce($_POST['oc_contact_nonce'], 'oc_contact_form')) {
    $name = sanitize_text_field($_POST['oc_name']);
    $email = sanitize_email($_POST['oc_email']);
    $subject = sanitize_text_field($_POST['oc_subject']);
    $message = wp_kses_post($_POST['oc_message']);

    // Validate required fields
    if (empty($name)) {
        $form_errors[] = __('Name is required.', 'odds-comparison');
    }

    if (empty($email) || !is_email($email)) {
        $form_errors[] = __('Valid email is required.', 'odds-comparison');
    }

    if (empty($subject)) {
        $form_errors[] = __('Subject is required.', 'odds-comparison');
    }

    if (empty($message)) {
        $form_errors[] = __('Message is required.', 'odds-comparison');
    }

    // If no errors, send email
    if (empty($form_errors)) {
        $to = get_option('admin_email');
        $email_subject = sprintf(__('Contact Form: %s', 'odds-comparison'), $subject);
        $email_message = sprintf(
            __("Name: %s\nEmail: %s\nSubject: %s\n\nMessage:\n%s", 'odds-comparison'),
            $name,
            $email,
            $subject,
            $message
        );

        $headers = array(
            'From: ' . $name . ' <' . $email . '>',
            'Reply-To: ' . $email
        );

        if (wp_mail($to, $email_subject, $email_message, $headers)) {
            $submission_message = __('Thank you for your message! We will get back to you soon.', 'odds-comparison');
        } else {
            $form_errors[] = __('There was an error sending your message. Please try again.', 'odds-comparison');
        }
    }
}
?>

<div class="oc-contact-container">
    <div class="oc-contact-header">
        <h1><?php esc_html_e('Contact Us', 'odds-comparison'); ?></h1>
        <p><?php esc_html_e('Get in touch with our team. We\'re here to help with any questions about sports betting.', 'odds-comparison'); ?></p>
    </div>

    <div class="oc-contact-content">
        <div class="oc-contact-form-section">
            <div class="oc-contact-form-wrapper">
                <h2><?php esc_html_e('Send us a Message', 'odds-comparison'); ?></h2>

                <?php if (!empty($submission_message)) : ?>
                    <div class="oc-notice oc-notice-success">
                        <?php echo esc_html($submission_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($form_errors)) : ?>
                    <div class="oc-notice oc-notice-error">
                        <ul>
                            <?php foreach ($form_errors as $error) : ?>
                                <li><?php echo esc_html($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="" class="oc-contact-form">
                    <?php wp_nonce_field('oc_contact_form', 'oc_contact_nonce'); ?>

                    <div class="oc-form-row">
                        <div class="oc-form-group">
                            <label for="oc_name"><?php esc_html_e('Name *', 'odds-comparison'); ?></label>
                            <input type="text" id="oc_name" name="oc_name" value="<?php echo isset($_POST['oc_name']) ? esc_attr($_POST['oc_name']) : ''; ?>" required>
                        </div>

                        <div class="oc-form-group">
                            <label for="oc_email"><?php esc_html_e('Email *', 'odds-comparison'); ?></label>
                            <input type="email" id="oc_email" name="oc_email" value="<?php echo isset($_POST['oc_email']) ? esc_attr($_POST['oc_email']) : ''; ?>" required>
                        </div>
                    </div>

                    <div class="oc-form-group">
                        <label for="oc_subject"><?php esc_html_e('Subject *', 'odds-comparison'); ?></label>
                        <select id="oc_subject" name="oc_subject" required>
                            <option value=""><?php esc_html_e('Select a subject', 'odds-comparison'); ?></option>
                            <option value="general" <?php selected(isset($_POST['oc_subject']) && $_POST['oc_subject'] === 'general'); ?>><?php esc_html_e('General Inquiry', 'odds-comparison'); ?></option>
                            <option value="support" <?php selected(isset($_POST['oc_subject']) && $_POST['oc_subject'] === 'support'); ?>><?php esc_html_e('Technical Support', 'odds-comparison'); ?></option>
                            <option value="partnership" <?php selected(isset($_POST['oc_subject']) && $_POST['oc_subject'] === 'partnership'); ?>><?php esc_html_e('Partnership Opportunities', 'odds-comparison'); ?></option>
                            <option value="advertising" <?php selected(isset($_POST['oc_subject']) && $_POST['oc_subject'] === 'advertising'); ?>><?php esc_html_e('Advertising', 'odds-comparison'); ?></option>
                            <option value="other" <?php selected(isset($_POST['oc_subject']) && $_POST['oc_subject'] === 'other'); ?>><?php esc_html_e('Other', 'odds-comparison'); ?></option>
                        </select>
                    </div>

                    <div class="oc-form-group">
                        <label for="oc_message"><?php esc_html_e('Message *', 'odds-comparison'); ?></label>
                        <textarea id="oc_message" name="oc_message" rows="6" required><?php echo isset($_POST['oc_message']) ? esc_textarea($_POST['oc_message']) : ''; ?></textarea>
                    </div>

                    <div class="oc-form-group">
                        <button type="submit" name="oc_contact_submit" class="oc-submit-btn">
                            <?php esc_html_e('Send Message', 'odds-comparison'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="oc-contact-info-section">
            <div class="oc-contact-info-wrapper">
                <h2><?php esc_html_e('Get in Touch', 'odds-comparison'); ?></h2>

                <div class="oc-contact-methods">
                    <div class="oc-contact-method">
                        <div class="oc-contact-icon">
                            <span class="dashicons dashicons-email"></span>
                        </div>
                        <div class="oc-contact-details">
                            <h3><?php esc_html_e('Email Us', 'odds-comparison'); ?></h3>
                            <p><?php esc_html_e('Send us an email and we\'ll respond within 24 hours.', 'odds-comparison'); ?></p>
                            <a href="mailto:<?php echo esc_attr(get_option('admin_email')); ?>"><?php echo esc_html(get_option('admin_email')); ?></a>
                        </div>
                    </div>

                    <div class="oc-contact-method">
                        <div class="oc-contact-icon">
                            <span class="dashicons dashicons-clock"></span>
                        </div>
                        <div class="oc-contact-details">
                            <h3><?php esc_html_e('Response Time', 'odds-comparison'); ?></h3>
                            <p><?php esc_html_e('We typically respond to all inquiries within 24 hours during business days.', 'odds-comparison'); ?></p>
                            <span class="oc-response-time"><?php esc_html_e('Mon-Fri: 9AM-6PM EST', 'odds-comparison'); ?></span>
                        </div>
                    </div>

                    <div class="oc-contact-method">
                        <div class="oc-contact-icon">
                            <span class="dashicons dashicons-info"></span>
                        </div>
                        <div class="oc-contact-details">
                            <h3><?php esc_html_e('Need Help?', 'odds-comparison'); ?></h3>
                            <p><?php esc_html_e('Check our FAQ section or browse our sportsbook reviews for more information.', 'odds-comparison'); ?></p>
                            <a href="<?php echo esc_url(home_url('/faq')); ?>" class="oc-link"><?php esc_html_e('Visit FAQ', 'odds-comparison'); ?></a>
                        </div>
                    </div>
                </div>

                <div class="oc-contact-faq">
                    <h3><?php esc_html_e('Frequently Asked Questions', 'odds-comparison'); ?></h3>
                    <div class="oc-faq-list">
                        <div class="oc-faq-item">
                            <h4><?php esc_html_e('How do I choose the right sportsbook?', 'odds-comparison'); ?></h4>
                            <p><?php esc_html_e('Use our comparison tool to filter by your preferences including bonuses, payment methods, and features.', 'odds-comparison'); ?></p>
                        </div>

                        <div class="oc-faq-item">
                            <h4><?php esc_html_e('Are the bonuses listed guaranteed?', 'odds-comparison'); ?></h4>
                            <p><?php esc_html_e('We strive to keep all bonus information current, but terms can change. Always check with the operator directly.', 'odds-comparison'); ?></p>
                        </div>

                        <div class="oc-faq-item">
                            <h4><?php esc_html_e('How do I submit a sportsbook review?', 'odds-comparison'); ?></h4>
                            <p><?php esc_html_e('You must be logged in to submit reviews. Visit any operator page and use the reviews tab.', 'odds-comparison'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
