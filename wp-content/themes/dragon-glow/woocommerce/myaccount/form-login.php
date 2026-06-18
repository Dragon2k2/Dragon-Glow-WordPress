<?php
/**
 * Dragon Glow — Login / Register Form
 * Override: woocommerce/myaccount/form-login.php
 * Tab toggle between login and register
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
    return;
}

$register_enabled = get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes';
?>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
    <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>

    <div class="max-w-md mx-auto">
        <h1 class="font-headline text-headline-lg text-primary text-center mb-8">
            <?php esc_html_e( 'Welcome to Dragon Glow', 'dragon-glow' ); ?>
        </h1>

        <!-- Tab Toggle -->
        <?php if ( $register_enabled ) : ?>
        <div class="flex rounded-full bg-surface-container p-1 mb-8">
            <button type="button"
                    id="dg-login-tab"
                    class="flex-1 py-3 px-6 rounded-full font-medium transition-all bg-primary text-white"
                    onclick="window.dgToggleAuth ? window.dgToggleAuth('login') : null">
                <?php esc_html_e( 'Sign In', 'dragon-glow' ); ?>
            </button>
            <button type="button"
                    id="dg-register-tab"
                    class="flex-1 py-3 px-6 rounded-full font-medium transition-all text-on-surface-variant hover:text-on-surface"
                    onclick="window.dgToggleAuth ? window.dgToggleAuth('register') : null">
                <?php esc_html_e( 'Create Account', 'dragon-glow' ); ?>
            </button>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <div id="dg-login-form" class="<?php echo $register_enabled ? '' : 'block'; ?>">
            <form method="post" class="woocommerce-form woocommerce-form-login login space-y-6" <?php do_action( 'woocommerce_login_form_tag' ); ?>>

                <?php do_action( 'woocommerce_login_form_start' ); ?>

                <div>
                    <label for="username" class="block text-label-sm text-on-surface-variant mb-2">
                        <?php esc_html_e( 'Email address', 'dragon-glow' ); ?>
                    </label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"
                           name="username"
                           id="username"
                           autocomplete="username"
                           placeholder="<?php esc_attr_e( 'you@example.com', 'dragon-glow' ); ?>"
                           value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" />
                </div>

                <div>
                    <label for="password" class="block text-label-sm text-on-surface-variant mb-2">
                        <?php esc_html_e( 'Password', 'dragon-glow' ); ?>
                    </label>
                    <input class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"
                           type="password"
                           name="password"
                           id="password"
                           autocomplete="current-password"
                           placeholder="<?php esc_attr_e( 'Your password', 'dragon-glow' ); ?>" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input class="woocommerce-form__input woocommerce-form__input-checkbox w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary/20 accent-primary"
                               name="rememberme"
                               type="checkbox"
                               id="rememberme"
                               value="forever" />
                        <span class="text-sm text-on-surface-variant"><?php esc_html_e( 'Remember me', 'dragon-glow' ); ?></span>
                    </label>

                    <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="text-sm text-primary hover:underline">
                        <?php esc_html_e( 'Forgot password?', 'dragon-glow' ); ?>
                    </a>
                </div>

                <?php do_action( 'woocommerce_login_form' ); ?>

                <input type="hidden" name="woocommerce-login-nonce" value="<?php echo wp_create_nonce( 'woocommerce-login' ); ?>" />

                <button type="submit" class="btn-primary w-full" name="login" value="<?php esc_attr_e( 'Sign In', 'dragon-glow' ); ?>">
                    <?php esc_html_e( 'Sign In', 'dragon-glow' ); ?>
                </button>

                <?php do_action( 'woocommerce_login_form_end' ); ?>

            </form>
        </div>

        <!-- Register Form -->
        <?php if ( $register_enabled ) : ?>
        <div id="dg-register-form" class="hidden">
            <form method="post" class="woocommerce-form woocommerce-form-register register space-y-6" <?php do_action( 'woocommerce_register_form_tag' ); ?>">

                <?php do_action( 'woocommerce_register_form_start' ); ?>

                <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
                <div>
                    <label for="reg_username" class="block text-label-sm text-on-surface-variant mb-2">
                        <?php esc_html_e( 'Username', 'dragon-glow' ); ?>
                    </label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"
                           name="username"
                           id="reg_username"
                           autocomplete="username"
                           value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" />
                </div>
                <?php endif; ?>

                <div>
                    <label for="reg_email" class="block text-label-sm text-on-surface-variant mb-2">
                        <?php esc_html_e( 'Email address', 'dragon-glow' ); ?>
                    </label>
                    <input type="email" class="woocommerce-Input woocommerce-Input--email input-text w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"
                           name="email"
                           id="reg_email"
                           autocomplete="email"
                           placeholder="<?php esc_attr_e( 'you@example.com', 'dragon-glow' ); ?>"
                           value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" />
                </div>

                <div>
                    <label for="reg_password" class="block text-label-sm text-on-surface-variant mb-2">
                        <?php esc_html_e( 'Password', 'dragon-glow' ); ?>
                    </label>
                    <input type="password" class="woocommerce-Input woocommerce-Input--password input-text w-full px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"
                           name="password"
                           id="reg_password"
                           autocomplete="new-password"
                           placeholder="<?php esc_attr_e( 'Create a strong password', 'dragon-glow' ); ?>" />
                </div>

                <p class="text-xs text-on-surface-variant">
                    <?php
                    printf(
                        esc_html__( 'By creating an account, you agree to our %s and %s.', 'dragon-glow' ),
                        '<a href="' . esc_url( get_privacy_policy_url() ) . '" class="text-primary hover:underline">' . esc_html__( 'Privacy Policy', 'dragon-glow' ) . '</a>',
                        '<a href="' . esc_url( get_permalink( get_page_by_path( 'terms-of-service' ) ) ) . '" class="text-primary hover:underline">' . esc_html__( 'Terms of Service', 'dragon-glow' ) . '</a>'
                    );
                    ?>
                </p>

                <?php do_action( 'woocommerce_register_form' ); ?>

                <input type="hidden" name="woocommerce-register-nonce" value="<?php echo wp_create_nonce( 'woocommerce-register' ); ?>" />

                <button type="submit" class="btn-primary w-full" name="register" value="<?php esc_attr_e( 'Create Account', 'dragon-glow' ); ?>">
                    <?php esc_html_e( 'Create Account', 'dragon-glow' ); ?>
                </button>

                <?php do_action( 'woocommerce_register_form_end' ); ?>

            </form>
        </div>
        <?php endif; ?>

        <!-- Social Login (optional - can be enabled with a plugin) -->
        <div class="mt-8 pt-8 border-t border-outline-variant">
            <p class="text-center text-on-surface-variant text-sm mb-4">
                <?php esc_html_e( 'Or continue with', 'dragon-glow' ); ?>
            </p>
            <div class="flex gap-4">
                <button type="button" class="flex-1 py-3 px-4 rounded-xl border border-outline-variant hover:bg-surface-container transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    <span class="text-sm font-medium">Google</span>
                </button>
                <button type="button" class="flex-1 py-3 px-4 rounded-xl border border-outline-variant hover:bg-surface-container transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.342-3.369-1.342-.454-1.155-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.114 2.504.336 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.163 22 16.418 22 12c0-5.523-4.477-10-10-10z"/></svg>
                    <span class="text-sm font-medium">GitHub</span>
                </button>
            </div>
        </div>
    </div>
</div>
