<?php
/**
 * Template Name: User Login
 *
 * Custom login page template with modern, responsive design
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

$message = '';
$errors = array();

if ( isset( $_POST['oc_login_submit'] ) && wp_verify_nonce( $_POST['oc_login_nonce'], 'oc_login_action' ) ) {
    $username = sanitize_user( $_POST['username'] );
    $password = $_POST['password'];
    $remember = isset( $_POST['remember'] ) ? true : false;

    // Validation
    if ( empty( $username ) ) {
        $errors[] = __( 'Username or email is required.', 'odds-comparison' );
    }

    if ( empty( $password ) ) {
        $errors[] = __( 'Password is required.', 'odds-comparison' );
    }

    if ( empty( $errors ) ) {
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        );

        $user = wp_signon( $creds, false );

        if ( ! is_wp_error( $user ) ) {
            wp_redirect( home_url( '/profile' ) );
            exit;
        } else {
            $errors[] = $user->get_error_message();
        }
    }
}
?>

<div class="oc-page-wrapper">
    <div class="oc-container">
        <div class="oc-row">
            <main class="oc-main-content oc-col-lg-12">
                <div class="oc-login-form-wrapper">
                    <div class="oc-form-header">
                        <h1><?php esc_html_e( 'Welcome Back', 'odds-comparison' ); ?></h1>
                        <p><?php esc_html_e( 'Sign in to access your account and exclusive betting offers', 'odds-comparison' ); ?></p>
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

                    <form class="oc-login-form" method="post" action="" id="oc-login-form">
                        <?php wp_nonce_field( 'oc_login_action', 'oc_login_nonce' ); ?>

                        <div class="oc-form-group">
                            <label for="username"><?php esc_html_e( 'Username or Email', 'odds-comparison' ); ?></label>
                            <input type="text" id="username" name="username" value="<?php echo esc_attr( isset( $_POST['username'] ) ? $_POST['username'] : '' ); ?>" required autocomplete="username">
                            <div class="oc-form-feedback" id="username-feedback"></div>
                        </div>

                        <div class="oc-form-group">
                            <label for="password"><?php esc_html_e( 'Password', 'odds-comparison' ); ?></label>
                            <div class="oc-password-input-wrapper">
                                <input type="password" id="password" name="password" required autocomplete="current-password">
                                <button type="button" class="oc-password-toggle" id="password-toggle" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'odds-comparison' ); ?>">
                                    <svg class="oc-eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <div class="oc-form-feedback" id="password-feedback"></div>
                        </div>

                        <div class="oc-form-group oc-form-group-inline">
                            <label for="remember" class="oc-checkbox-label">
                                <input type="checkbox" id="remember" name="remember" value="1" <?php checked( isset( $_POST['remember'] ) && $_POST['remember'] ); ?>>
                                <?php esc_html_e( 'Remember me', 'odds-comparison' ); ?>
                            </label>
                            <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="oc-forgot-password-link">
                                <?php esc_html_e( 'Forgot password?', 'odds-comparison' ); ?>
                            </a>
                        </div>

                        <div class="oc-form-submit">
                            <button type="submit" name="oc_login_submit" class="oc-btn oc-btn-large">
                                <?php esc_html_e( 'Sign In', 'odds-comparison' ); ?>
                            </button>
                        </div>
                    </form>

                    <div class="oc-form-footer">
                        <p><?php esc_html_e( 'Don\'t have an account?', 'odds-comparison' ); ?> <a href="<?php echo esc_url( home_url( '/register' ) ); ?>"><?php esc_html_e( 'Create one here', 'odds-comparison' ); ?></a></p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<style>
.oc-login-form-wrapper {
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

.oc-form-group-inline {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.oc-checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: normal !important;
    cursor: pointer;
}

.oc-checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
}

.oc-forgot-password-link {
    color: #007cba;
    text-decoration: none;
    font-size: 14px;
}

.oc-forgot-password-link:hover {
    text-decoration: underline;
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

.oc-alert ul {
    margin: 0;
    padding-left: 20px;
}

.oc-alert li {
    margin-bottom: 5px;
}

@media (max-width: 768px) {
    .oc-login-form-wrapper {
        padding: 20px 15px;
        margin: 20px;
    }

    .oc-form-header h1 {
        font-size: 24px;
    }

    .oc-form-group-inline {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const passwordToggle = document.getElementById('password-toggle');
    const passwordInput = document.getElementById('password');

    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Update icon
            const icon = this.querySelector('.oc-eye-icon');
            if (icon) {
                if (type === 'text') {
                    icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
                } else {
                    icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                }
            }
        });
    }

    // Form validation
    const loginForm = document.getElementById('oc-login-form');
    const usernameInput = document.getElementById('username');
    const usernameFeedback = document.getElementById('username-feedback');
    const passwordFeedback = document.getElementById('password-feedback');

    if (loginForm) {
        // Real-time validation
        usernameInput.addEventListener('blur', function() {
            validateUsername(this.value);
        });

        passwordInput.addEventListener('blur', function() {
            validatePassword(this.value);
        });

        // Form submission
        loginForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;

            // Show loading state
            submitBtn.textContent = '<?php esc_js( _e( "Signing In...", "odds-comparison" ) ); ?>';
            submitBtn.disabled = true;

            // Validate all fields
            const isUsernameValid = validateUsername(usernameInput.value);
            const isPasswordValid = validatePassword(passwordInput.value);

            if (!isUsernameValid || !isPasswordValid) {
                e.preventDefault();
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                return false;
            }

            // Form will submit normally
        });
    }

    function validateUsername(value) {
        if (usernameFeedback) {
            if (!value.trim()) {
                usernameFeedback.textContent = '<?php esc_js( _e( "Username or email is required.", "odds-comparison" ) ); ?>';
                usernameFeedback.className = 'oc-form-feedback error';
                return false;
            } else {
                usernameFeedback.textContent = '';
                usernameFeedback.className = 'oc-form-feedback';
                return true;
            }
        }
        return true;
    }

    function validatePassword(value) {
        if (passwordFeedback) {
            if (!value) {
                passwordFeedback.textContent = '<?php esc_js( _e( "Password is required.", "odds-comparison" ) ); ?>';
                passwordFeedback.className = 'oc-form-feedback error';
                return false;
            } else {
                passwordFeedback.textContent = '';
                passwordFeedback.className = 'oc-form-feedback';
                return true;
            }
        }
        return true;
    }
});
</script>

<?php get_footer(); ?>
