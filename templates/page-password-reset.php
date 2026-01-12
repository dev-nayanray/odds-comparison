<?php
/**
 * Template Name: User Password Reset
 *
 * Custom password reset page template with modern, responsive design
 *
 * @package Odds_Comparison
 * @since 1.0.0
 */

get_header();

$errors = oc_get_reset_errors();
$success_message = oc_get_reset_success();
?>

<div class="oc-page-wrapper">
    <div class="oc-container">
        <div class="oc-row">
            <main class="oc-main-content oc-col-lg-8">
                <div class="oc-reset-form-wrapper">
                    <div class="oc-form-header">
                        <h1><?php esc_html_e( 'Reset Password', 'odds-comparison' ); ?></h1>
                        <p><?php esc_html_e( 'Enter your username or email address and we\'ll send you a link to reset your password.', 'odds-comparison' ); ?></p>
                    </div>

                    <?php if ( ! empty( $success_message ) ) : ?>
                        <div class="oc-alert oc-alert-success">
                            <p><?php echo esc_html( $success_message ); ?></p>
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

                    <?php if ( empty( $success_message ) ) : ?>
                        <form class="oc-reset-form" method="post" action="">
                            <?php wp_nonce_field( 'oc_reset_action', 'oc_reset_nonce' ); ?>

                            <div class="oc-form-group">
                                <label for="user_login"><?php esc_html_e( 'Username or Email Address', 'odds-comparison' ); ?></label>
                                <input type="text" id="user_login" name="user_login" value="<?php echo esc_attr( isset( $_POST['user_login'] ) ? $_POST['user_login'] : '' ); ?>" required>
                            </div>

                            <div class="oc-form-submit">
                                <button type="submit" name="oc_reset_submit" class="oc-btn oc-btn-large">
                                    <?php esc_html_e( 'Send Reset Link', 'odds-comparison' ); ?>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <div class="oc-form-footer">
                        <p><?php esc_html_e( 'Remember your password?', 'odds-comparison' ); ?> <a href="<?php echo esc_url( get_permalink( get_page_by_path( 'login' ) ) ); ?>"><?php esc_html_e( 'Log in here', 'odds-comparison' ); ?></a></p>
                        <p><?php esc_html_e( 'Don\'t have an account?', 'odds-comparison' ); ?> <a href="<?php echo esc_url( get_permalink( get_page_by_path( 'register' ) ) ); ?>"><?php esc_html_e( 'Create one here', 'odds-comparison' ); ?></a></p>
                    </div>
                </div>
            </main>

            <aside class="oc-sidebar oc-col-lg-4">
                <?php get_sidebar(); ?>
            </aside>
        </div>
    </div>
</div>

<style>
.oc-reset-form-wrapper {
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

.oc-form-footer p {
    margin-bottom: 10px;
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

.oc-alert-success {
    background: #e8f5e8;
    border: 1px solid #4caf50;
    color: #2e7d32;
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
    .oc-reset-form-wrapper {
        padding: 20px 15px;
        margin: 20px;
    }

    .oc-form-header h1 {
        font-size: 24px;
    }
}
</style>

<?php get_footer(); ?>
