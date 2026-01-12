<?php
/**
 * Template Name: User Registration
 *
 * Custom registration page template with modern, responsive design
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

if ( is_user_logged_in() ) {
    wp_redirect( home_url( '/profile' ) );
    exit;
}

$message = '';
$errors = array();

if ( isset( $_POST['oc_register_submit'] ) && wp_verify_nonce( $_POST['oc_register_nonce'], 'oc_register_action' ) ) {
    $username = sanitize_user( $_POST['username'] );
    $email = sanitize_email( $_POST['email'] );
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if ( empty( $username ) ) {
        $errors[] = __( 'Username is required.', 'odds-comparison' );
    } elseif ( username_exists( $username ) ) {
        $errors[] = __( 'Username already exists.', 'odds-comparison' );
    }

    if ( empty( $email ) ) {
        $errors[] = __( 'Email is required.', 'odds-comparison' );
    } elseif ( ! is_email( $email ) ) {
        $errors[] = __( 'Invalid email address.', 'odds-comparison' );
    } elseif ( email_exists( $email ) ) {
        $errors[] = __( 'Email already exists.', 'odds-comparison' );
    }

    if ( empty( $password ) ) {
        $errors[] = __( 'Password is required.', 'odds-comparison' );
    } elseif ( strlen( $password ) < 6 ) {
        $errors[] = __( 'Password must be at least 6 characters.', 'odds-comparison' );
    }

    if ( $password !== $confirm_password ) {
        $errors[] = __( 'Passwords do not match.', 'odds-comparison' );
    }

    if ( empty( $errors ) ) {
        $user_id = wp_create_user( $username, $password, $email );

        if ( ! is_wp_error( $user_id ) ) {
            // Send activation email or auto-login
            wp_set_current_user( $user_id );
            wp_set_auth_cookie( $user_id );
            wp_redirect( home_url( '/profile' ) );
            exit;
        } else {
            $errors[] = $user_id->get_error_message();
        }
    }
}
?>

<div class="oc-page-wrapper">
    <div class="oc-container">
        <div class="oc-row">
            <main class="oc-main-content oc-col-lg-12">
                <div class="oc-register-form-wrapper">
                    <div class="oc-form-header">
                        <h1><?php esc_html_e( 'Create Your Account', 'odds-comparison' ); ?></h1>
                        <p><?php esc_html_e( 'Join us to access exclusive betting tips and odds comparisons', 'odds-comparison' ); ?></p>
                    </div>

                    <?php if ( ! empty( $errors ) ) : ?>
                        <div class="oc-alert oc-alert-error">
                            <ul>
                                <?php foreach ( $errors as $error ) : ?>
                                    <li><?php echo esc_html( $error ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $message ) ) : ?>
                        <div class="oc-alert oc-alert-success">
                            <?php echo esc_html( $message ); ?>
                        </div>
                    <?php endif; ?>

                    <form class="oc-register-form" method="post" action="">
                        <?php wp_nonce_field( 'oc_register_action', 'oc_register_nonce' ); ?>

                        <div class="oc-form-group">
                            <label for="username"><?php esc_html_e( 'Username', 'odds-comparison' ); ?> *</label>
                            <input type="text" id="username" name="username" value="<?php echo esc_attr( isset( $_POST['username'] ) ? $_POST['username'] : '' ); ?>" required>
                        </div>

                        <div class="oc-form-group">
                            <label for="email"><?php esc_html_e( 'Email Address', 'odds-comparison' ); ?> *</label>
                            <input type="email" id="email" name="email" value="<?php echo esc_attr( isset( $_POST['email'] ) ? $_POST['email'] : '' ); ?>" required>
                        </div>

                        <div class="oc-form-group">
                            <label for="password"><?php esc_html_e( 'Password', 'odds-comparison' ); ?> *</label>
                            <div class="oc-password-input-wrapper">
                                <input type="password" id="password" name="password" required autocomplete="new-password">
                                <button type="button" class="oc-password-toggle" id="password-toggle" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'odds-comparison' ); ?>">
                                    <svg class="oc-eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <div class="oc-password-strength">
                                <div class="oc-password-strength-meter">
                                    <div class="oc-password-strength-bar" id="password-strength-bar"></div>
                                </div>
                                <small id="password-strength-text"><?php esc_html_e( 'Password must be at least 6 characters', 'odds-comparison' ); ?></small>
                            </div>
                            <div class="oc-form-feedback" id="password-feedback"></div>
                        </div>

                        <div class="oc-form-group">
                            <label for="confirm_password"><?php esc_html_e( 'Confirm Password', 'odds-comparison' ); ?> *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                            <div class="oc-form-feedback" id="confirm-password-feedback"></div>
                        </div>

                        <div class="oc-form-group">
                            <label for="terms" class="oc-checkbox-label">
                                <input type="checkbox" id="terms" name="terms" value="1" required>
                                <?php printf(
                                    __( 'I agree to the %s and %s', 'odds-comparison' ),
                                    '<a href="#" target="_blank">' . __( 'Terms of Service', 'odds-comparison' ) . '</a>',
                                    '<a href="#" target="_blank">' . __( 'Privacy Policy', 'odds-comparison' ) . '</a>'
                                ); ?>
                            </label>
                            <div class="oc-form-feedback" id="terms-feedback"></div>
                        </div>

                        <div class="oc-form-group oc-form-submit">
                            <button type="submit" name="oc_register_submit" class="oc-btn oc-btn-primary oc-btn-large">
                                <?php esc_html_e( 'Create Account', 'odds-comparison' ); ?>
                            </button>
                        </div>

                        <div class="oc-form-footer">
                            <p><?php esc_html_e( 'Already have an account?', 'odds-comparison' ); ?> <a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Sign In', 'odds-comparison' ); ?></a></p>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</div>

<style>
.oc-register-form-wrapper {
    max-width: 500px;
    margin: 0 auto;
    padding: 40px 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.oc-form-header {
    text-align: center;
    margin-bottom: 30px;
}

.oc-form-header h1 {
    color: #333;
    margin-bottom: 10px;
    font-size: 28px;
}

.oc-form-header p {
    color: #666;
    font-size: 16px;
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

.oc-form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e1e1;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.oc-form-group input:focus {
    outline: none;
    border-color: #007cba;
}

.oc-form-group small {
    color: #666;
    font-size: 14px;
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

.oc-checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-weight: normal !important;
    cursor: pointer;
    line-height: 1.4;
}

.oc-checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
    margin-top: 2px;
    flex-shrink: 0;
}

.oc-checkbox-label a {
    color: #007cba;
    text-decoration: none;
}

.oc-checkbox-label a:hover {
    text-decoration: underline;
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

.oc-btn-large {
    padding: 15px 40px;
    font-size: 18px;
}

.oc-form-footer {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e1e1e1;
}

.oc-form-footer a {
    color: #007cba;
    text-decoration: none;
}

.oc-form-footer a:hover {
    text-decoration: underline;
}

.oc-alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.oc-alert-error {
    background: #ffeaea;
    border: 1px solid #ff6b6b;
    color: #d63031;
}

.oc-alert-success {
    background: #e8f5e8;
    border: 1px solid #55a55a;
    color: #2d5a2d;
}

.oc-alert ul {
    margin: 0;
    padding-left: 20px;
}

.oc-alert li {
    margin-bottom: 5px;
}

@media (max-width: 768px) {
    .oc-register-form-wrapper {
        padding: 20px 15px;
        margin: 20px;
    }

    .oc-form-header h1 {
        font-size: 24px;
    }
}
</style>

<?php get_footer(); ?>
