<?php
/**
 * Dragon Glow — Empty Cart
 * WooCommerce override: woocommerce/cart/cart-empty.php
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/** @hooked wc_no_products_found - 10 */
do_action( 'woocommerce_no_products_found' );
?>

<!-- Atmospheric background -->
<div class="dg-cart-bg" aria-hidden="true">
    <div class="dg-cart-blob-1"></div>
    <div class="dg-cart-blob-2"></div>
</div>

<main class="min-h-screen relative pb-section-gap">
<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-24 text-center">

    <!-- Illustration -->
    <div class="w-36 h-36 mx-auto mb-8 bg-primary-container/10 rounded-full flex items-center justify-center">
        <span class="material-symbols-outlined text-primary" style="font-size:80px; font-variation-settings:'FILL' 0;">shopping_basket</span>
    </div>

    <h1 class="font-headline text-headline-lg text-primary mb-4">
        <?php esc_html_e( 'Your cart is empty', 'dragon-glow' ); ?>
    </h1>

    <p class="text-on-surface-variant font-body-lg max-w-md mx-auto mb-10 opacity-75">
        <?php esc_html_e( 'Looks like you haven\'t added any products to your ritual cart yet. Let\'s change that!', 'dragon-glow' ); ?>
    </p>

    <!-- CTAs -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"
           class="dg-shimmer-btn inline-flex items-center justify-center gap-3 px-10 py-5 rounded-full font-label-sm text-label-sm uppercase tracking-widest font-bold no-underline shadow-xl">
            <?php esc_html_e( 'Shop New Arrivals', 'dragon-glow' ); ?>
            <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
        </a>

        <?php if ( is_user_logged_in() ) : ?>
        <a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>"
           class="btn-ghost inline-flex items-center justify-center px-8 py-5">
            <?php esc_html_e( 'View Your Account', 'dragon-glow' ); ?>
        </a>
        <?php else : ?>
        <a href="<?php echo esc_url( wp_login_url() ); ?>"
           class="btn-ghost inline-flex items-center justify-center px-8 py-5">
            <?php esc_html_e( 'Login / Register', 'dragon-glow' ); ?>
        </a>
        <?php endif; ?>
    </div>

</div>
</main>
