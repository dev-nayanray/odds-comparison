<?php
/**
 * Template Name: User Profile
 *
 * Custom user profile page template with modern, responsive design
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url( get_permalink() ) );
    exit;
}

$current_user = wp_get_current_user();
$message = '';
$errors = array();

// Handle password change
if ( isset( $_POST['oc_password_submit'] ) && wp_verify_nonce( $_POST['oc_password_nonce'], 'oc_password_action' ) ) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Validation
    if ( empty( $current_password ) ) {
        $errors[] = __( 'Current password is required.', 'odds-comparison' );
    } elseif ( ! wp_check_password( $current_password, $current_user->user_pass, $current_user->ID ) ) {
        $errors[] = __( 'Current password is incorrect.', 'odds-comparison' );
    }

    if ( empty( $new_password ) ) {
        $errors[] = __( 'New password is required.', 'odds-comparison' );
    } elseif ( strlen( $new_password ) < 6 ) {
        $errors[] = __( 'New password must be at least 6 characters long.', 'odds-comparison' );
    }

    if ( $new_password !== $confirm_new_password ) {
        $errors[] = __( 'New passwords do not match.', 'odds-comparison' );
    }

    if ( empty( $errors ) ) {
        // Update password
        wp_set_password( $new_password, $current_user->ID );

        // Log the user back in
        wp_set_current_user( $current_user->ID );
        wp_set_auth_cookie( $current_user->ID );

        $message = __( 'Password changed successfully!', 'odds-comparison' );
    }
}

if ( isset( $_POST['oc_profile_submit'] ) && wp_verify_nonce( $_POST['oc_profile_nonce'], 'oc_profile_action' ) ) {
    $first_name = sanitize_text_field( $_POST['first_name'] );
    $last_name = sanitize_text_field( $_POST['last_name'] );
    $email = sanitize_email( $_POST['email'] );
    $bio = sanitize_textarea_field( $_POST['bio'] );

    // Validation
    if ( empty( $email ) ) {
        $errors[] = __( 'Email is required.', 'odds-comparison' );
    } elseif ( ! is_email( $email ) ) {
        $errors[] = __( 'Invalid email address.', 'odds-comparison' );
    } elseif ( email_exists( $email ) && $email !== $current_user->user_email ) {
        $errors[] = __( 'Email already exists.', 'odds-comparison' );
    }

    if ( empty( $errors ) ) {
        // Update user meta
        update_user_meta( $current_user->ID, 'first_name', $first_name );
        update_user_meta( $current_user->ID, 'last_name', $last_name );
        wp_update_user( array(
            'ID' => $current_user->ID,
            'user_email' => $email,
            'display_name' => trim( $first_name . ' ' . $last_name )
        ) );

        if ( ! empty( $bio ) ) {
            update_user_meta( $current_user->ID, 'description', $bio );
        }

        $message = __( 'Profile updated successfully!', 'odds-comparison' );
        $current_user = wp_get_current_user(); // Refresh user data
    }
}
?>

<div class="oc-page-wrapper">
    <div class="oc-container">
        <div class="oc-row">
            <main class="oc-main-content oc-col-lg-12">
                <div class="oc-profile-wrapper">
                    <div class="oc-profile-header">
                        <div class="oc-profile-avatar">
                            <?php echo get_avatar( $current_user->ID, 120, '', '', array( 'class' => 'profile-avatar-img' ) ); ?>
                        </div>
                        <div class="oc-profile-info">
                            <h1><?php echo esc_html( $current_user->display_name ); ?></h1>
                            <p class="oc-profile-email"><?php echo esc_html( $current_user->user_email ); ?></p>
                            <p class="oc-profile-joined">
                                <?php printf( __( 'Member since %s', 'odds-comparison' ), date_i18n( get_option( 'date_format' ), strtotime( $current_user->user_registered ) ) ); ?>
                            </p>
                        </div>
                    </div>

                    <?php if ( ! empty( $message ) ) : ?>
                        <div class="oc-alert oc-alert-success">
                            <?php echo esc_html( $message ); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $errors ) ) : ?>
                        <div class="oc-alert oc-alert-error">
                            <ul>
                                <?php foreach ( $errors as $error ) : ?>
                                    <li><?php echo esc_html( $error ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="oc-profile-tabs">
                        <div class="oc-tab-buttons">
                            <button class="oc-tab-btn active" data-tab="profile"><?php esc_html_e( 'Edit Profile', 'odds-comparison' ); ?></button>
                            <button class="oc-tab-btn" data-tab="password"><?php esc_html_e( 'Change Password', 'odds-comparison' ); ?></button>
                            <button class="oc-tab-btn" data-tab="preferences"><?php esc_html_e( 'Preferences', 'odds-comparison' ); ?></button>
                        </div>

                        <div class="oc-tab-content">
                        <div class="oc-tab-pane active" id="profile">
                                <form class="oc-profile-form" method="post" action="" enctype="multipart/form-data">
                                    <?php wp_nonce_field( 'oc_profile_action', 'oc_profile_nonce' ); ?>

                                    <div class="oc-avatar-upload-section">
                                        <div class="oc-current-avatar">
                                            <?php echo get_avatar( $current_user->ID, 100, '', '', array( 'class' => 'oc-avatar-preview' ) ); ?>
                                        </div>
                                        <div class="oc-avatar-upload">
                                            <label for="avatar-upload" class="oc-avatar-upload-btn">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M14.828 14.828a4 4 0 0 1-5.656 0M9 10h1.586a1 1 0 0 1 .707.293l.707.707A1 1 0 0 0 12.414 11H13a4 4 0 0 1 4 4v1a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-1a4 4 0 0 1 4-4h.586a1 1 0 0 0 .707-.293l.707-.707A1 1 0 0 1 11.414 9H12a1 1 0 0 1 1 1z"/>
                                                </svg>
                                                <?php esc_html_e( 'Change Avatar', 'odds-comparison' ); ?>
                                            </label>
                                            <input type="file" id="avatar-upload" name="avatar" accept="image/*" style="display: none;">
                                            <small><?php esc_html_e( 'Max file size: 2MB. JPG, PNG, GIF allowed.', 'odds-comparison' ); ?></small>
                                        </div>
                                    </div>

                                    <div class="oc-form-row">
                                        <div class="oc-form-group oc-col-md-6">
                                            <label for="first_name"><?php esc_html_e( 'First Name', 'odds-comparison' ); ?></label>
                                            <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'first_name', true ) ); ?>">
                                        </div>
                                        <div class="oc-form-group oc-col-md-6">
                                            <label for="last_name"><?php esc_html_e( 'Last Name', 'odds-comparison' ); ?></label>
                                            <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'last_name', true ) ); ?>">
                                        </div>
                                    </div>

                                    <div class="oc-form-group">
                                        <label for="email"><?php esc_html_e( 'Email Address', 'odds-comparison' ); ?> *</label>
                                        <input type="email" id="email" name="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required>
                                    </div>

                                    <div class="oc-form-group">
                                        <label for="bio"><?php esc_html_e( 'Bio', 'odds-comparison' ); ?></label>
                                        <textarea id="bio" name="bio" rows="4" placeholder="<?php esc_attr_e( 'Tell us about yourself...', 'odds-comparison' ); ?>"><?php echo esc_textarea( get_user_meta( $current_user->ID, 'description', true ) ); ?></textarea>
                                    </div>

                                    <div class="oc-form-submit">
                                        <button type="submit" name="oc_profile_submit" class="oc-btn oc-btn-primary">
                                            <?php esc_html_e( 'Update Profile', 'odds-comparison' ); ?>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="oc-tab-pane" id="password">
                                <form class="oc-password-form" method="post" action="">
                                    <?php wp_nonce_field( 'oc_password_action', 'oc_password_nonce' ); ?>

                                    <div class="oc-form-group">
                                        <label for="current_password"><?php esc_html_e( 'Current Password', 'odds-comparison' ); ?> *</label>
                                        <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                                    </div>

                                    <div class="oc-form-group">
                                        <label for="new_password"><?php esc_html_e( 'New Password', 'odds-comparison' ); ?> *</label>
                                        <div class="oc-password-input-wrapper">
                                            <input type="password" id="new_password" name="new_password" required autocomplete="new-password">
                                            <button type="button" class="oc-password-toggle" id="new-password-toggle" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'odds-comparison' ); ?>">
                                                <svg class="oc-eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="oc-password-strength">
                                            <div class="oc-password-strength-meter">
                                                <div class="oc-password-strength-bar" id="new-password-strength-bar"></div>
                                            </div>
                                            <small id="new-password-strength-text"><?php esc_html_e( 'Password must be at least 6 characters', 'odds-comparison' ); ?></small>
                                        </div>
                                        <div class="oc-form-feedback" id="new-password-feedback"></div>
                                    </div>

                                    <div class="oc-form-group">
                                        <label for="confirm_new_password"><?php esc_html_e( 'Confirm New Password', 'odds-comparison' ); ?> *</label>
                                        <input type="password" id="confirm_new_password" name="confirm_new_password" required autocomplete="new-password">
                                        <div class="oc-form-feedback" id="confirm-new-password-feedback"></div>
                                    </div>

                                    <div class="oc-form-submit">
                                        <button type="submit" name="oc_password_submit" class="oc-btn oc-btn-primary">
                                            <?php esc_html_e( 'Change Password', 'odds-comparison' ); ?>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="oc-tab-pane" id="preferences">
                                <div class="oc-preferences-section">
                                    <h3><?php esc_html_e( 'Betting Preferences', 'odds-comparison' ); ?></h3>
                                    <div class="oc-preference-item">
                                        <label><?php esc_html_e( 'Preferred Odds Format:', 'odds-comparison' ); ?></label>
                                        <select id="odds-format" name="odds_format">
                                            <option value="decimal" <?php selected( get_user_meta( $current_user->ID, 'oc_odds_format', true ), 'decimal' ); ?>><?php esc_html_e( 'Decimal (2.10)', 'odds-comparison' ); ?></option>
                                            <option value="fractional" <?php selected( get_user_meta( $current_user->ID, 'oc_odds_format', true ), 'fractional' ); ?>><?php esc_html_e( 'Fractional (11/10)', 'odds-comparison' ); ?></option>
                                            <option value="american" <?php selected( get_user_meta( $current_user->ID, 'oc_odds_format', true ), 'american' ); ?>><?php esc_html_e( 'American (+110)', 'odds-comparison' ); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<style>
.oc-profile-wrapper {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    overflow: hidden;
}

.oc-profile-header {
    background: linear-gradient(135deg, #007cba 0%, #005a87 100%);
    color: #fff;
    padding: 40px 30px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.oc-profile-avatar {
    flex-shrink: 0;
}

.profile-avatar-img {
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.3);
}

.oc-profile-info h1 {
    margin: 0 0 5px 0;
    font-size: 28px;
}

.oc-profile-email {
    margin: 0 0 10px 0;
    opacity: 0.9;
}

.oc-profile-joined {
    margin: 0;
    font-size: 14px;
    opacity: 0.8;
}

.oc-profile-tabs {
    padding: 30px;
}

.oc-tab-buttons {
    display: flex;
    border-bottom: 2px solid #e1e1e1;
    margin-bottom: 30px;
}

.oc-tab-btn {
    background: none;
    border: none;
    padding: 15px 25px;
    font-size: 16px;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.oc-tab-btn.active {
    color: #007cba;
    border-bottom-color: #007cba;
}

.oc-tab-btn:hover {
    color: #007cba;
}

.oc-tab-pane {
    display: none;
}

.oc-tab-pane.active {
    display: block;
}

.oc-form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.oc-col-md-6 {
    flex: 1;
}

.oc-form-group {
    margin-bottom: 20px;
}

.oc-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.oc-form-group input,
.oc-form-group textarea,
.oc-form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e1e1;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.oc-form-group input:focus,
.oc-form-group textarea:focus,
.oc-form-group select:focus {
    outline: none;
    border-color: #007cba;
}

.oc-form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.oc-form-submit {
    text-align: center;
    margin-top: 30px;
}

.oc-btn {
    display: inline-block;
    padding: 12px 30px;
    background: #007cba;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    transition: background 0.3s ease;
    border: none;
    cursor: pointer;
}

.oc-btn:hover {
    background: #005a87;
}

.oc-alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.oc-alert-success {
    background: #e8f5e8;
    border: 1px solid #55a55a;
    color: #2d5a2d;
}

.oc-alert-error {
    background: #ffeaea;
    border: 1px solid #ff6b6b;
    color: #d63031;
}

.oc-alert ul {
    margin: 0;
    padding-left: 20px;
}

.oc-preferences-section h3 {
    margin-bottom: 20px;
    color: #333;
}

.oc-preference-item {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.oc-preference-item label {
    font-weight: 600;
    color: #333;
    min-width: 150px;
}

.oc-avatar-upload-section {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e1e1e1;
}

.oc-current-avatar {
    flex-shrink: 0;
}

.oc-avatar-preview {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #007cba;
}

.oc-avatar-upload {
    flex: 1;
}

.oc-avatar-upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #007cba;
    color: #fff;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease;
    border: none;
    font-size: 14px;
}

.oc-avatar-upload-btn:hover {
    background: #005a87;
}

.oc-avatar-upload-btn svg {
    width: 16px;
    height: 16px;
}

.oc-password-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.oc-password-input-wrapper input {
    padding-right: 50px;
}

.oc-password-toggle {
    position: absolute;
    right: 15px;
    background: none;
    border: none;
    cursor: pointer;
    color: #666;
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.3s ease;
}

.oc-password-toggle:hover {
    color: #007cba;
}

.oc-password-toggle:focus {
    outline: 2px solid #007cba;
    outline-offset: 2px;
}

.oc-password-strength {
    margin-top: 8px;
}

.oc-password-strength-meter {
    width: 100%;
    height: 4px;
    background: #e1e1e1;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 5px;
}

.oc-password-strength-bar {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.oc-password-strength-weak {
    background: #ff6b6b;
}

.oc-password-strength-fair {
    background: #ffa500;
}

.oc-password-strength-good {
    background: #4CAF50;
}

.oc-password-strength-strong {
    background: #2e7d32;
}

.oc-form-feedback {
    margin-top: 5px;
    font-size: 14px;
    min-height: 18px;
}

.oc-form-feedback.error {
    color: #d63031;
}

.oc-form-feedback.success {
    color: #2d5a2d;
}

@media (max-width: 768px) {
    .oc-profile-header {
        flex-direction: column;
        text-align: center;
        padding: 30px 20px;
    }

    .oc-profile-info h1 {
        font-size: 24px;
    }

    .oc-form-row {
        flex-direction: column;
        gap: 0;
    }

    .oc-tab-buttons {
        flex-direction: column;
    }

    .oc-tab-btn {
        text-align: center;
    }

    .oc-profile-tabs {
        padding: 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.oc-tab-btn');
    const tabPanes = document.querySelectorAll('.oc-tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');

            // Remove active class from all buttons and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));

            // Add active class to clicked button and corresponding pane
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Handle odds format preference change
    const oddsFormatSelect = document.getElementById('odds-format');
    if (oddsFormatSelect) {
        oddsFormatSelect.addEventListener('change', function() {
            // Save preference via AJAX
            const format = this.value;
            fetch(oc_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'oc_save_odds_format',
                    'format': format,
                    'nonce': oc_ajax.nonce
                })
            });
        });
    }
});
</script>

<?php get_footer(); ?>
