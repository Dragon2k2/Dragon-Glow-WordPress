<?php
/**
 * Dragon Glow — 404 Page
 * Custom 404 error page
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="min-h-[70vh] flex items-center justify-center" id="main-content">
    <div class="text-center px-margin-mobile md:px-margin-desktop py-24">
        <!-- Decorative blob -->
        <div class="absolute w-96 h-96 bg-primary-container/30 rounded-full blur-[120px] -z-10 pointer-events-none"></div>

        <!-- Icon -->
        <div class="w-48 h-48 mx-auto mb-8 relative">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-container to-secondary-container rounded-full opacity-20 animate-pulse"></div>
            <div class="relative w-full h-full rounded-full bg-surface-container flex items-center justify-center">
                <span class="material-symbols-outlined text-primary" style="font-size: 96px;">search_off</span>
            </div>
        </div>

        <!-- Content -->
        <span class="inline-block px-4 py-1 rounded-full bg-primary-container text-primary font-label-sm text-label-sm uppercase tracking-widest mb-6">
            404 Error
        </span>

        <h1 class="font-headline text-headline-lg text-primary mb-6">
            <?php esc_html_e( 'Page Not Found', 'dragon-glow' ); ?>
        </h1>

        <p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-12 leading-relaxed">
            <?php esc_html_e( "Oops! The page you're looking for seems to have wandered off. Let's get you back on track.", 'dragon-glow' ); ?>
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary">
                <span class="material-symbols-outlined align-middle mr-2">home</span>
                <?php esc_html_e( 'Back to Home', 'dragon-glow' ); ?>
            </a>
            <?php $shop_url = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : '#'; ?>
            <a href="<?php echo esc_url( $shop_url ); ?>" class="btn-ghost">
                <?php esc_html_e( 'Browse Products', 'dragon-glow' ); ?>
            </a>
        </div>
</main>

<style>
@keyframes pulse {
    0%, 100% { opacity: 0.2; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(1.05); }
}
.animate-pulse {
    animation: pulse 3s ease-in-out infinite;
}
</style>

<?php get_footer(); ?>
