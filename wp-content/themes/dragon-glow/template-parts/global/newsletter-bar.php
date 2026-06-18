<?php
/**
 * Dragon Glow — Newsletter Bar
 * Newsletter signup section
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="bg-surface-container py-16 relative overflow-hidden">
    <!-- Decorative blobs -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-primary-container/30 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 bg-tertiary-container/20 rounded-full blur-[80px] pointer-events-none"></div>

    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop relative z-10">
        <div class="max-w-2xl mx-auto text-center reveal">
            <span class="material-symbols-outlined text-primary text-4xl mb-4">auto_awesome</span>
            <h2 class="font-headline text-headline-lg text-primary mb-4">
                <?php esc_html_e( 'Join the Ritual', 'dragon-glow' ); ?>
            </h2>
            <p class="text-on-surface-variant text-body-lg mb-8">
                <?php esc_html_e( 'Subscribe to receive exclusive offers, early access to new products, and luminous skincare secrets delivered to your inbox.', 'dragon-glow' ); ?>
            </p>

            <form id="dg-newsletter-form" class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
                <input type="email"
                       name="email"
                       placeholder="<?php esc_attr_e( 'Enter your email', 'dragon-glow' ); ?>"
                       required
                       class="flex-1 px-6 py-4 rounded-full border border-outline-variant bg-surface focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                       aria-label="<?php esc_attr_e( 'Email address', 'dragon-glow' ); ?>" />
                <button type="submit"
                        class="btn-primary whitespace-nowrap">
                    <?php esc_html_e( 'Subscribe', 'dragon-glow' ); ?>
                </button>
            </form>

            <p id="dg-newsletter-msg" class="mt-4 text-sm hidden"></p>

            <p class="text-label-sm text-on-surface-variant mt-4">
                <?php esc_html_e( 'By subscribing, you agree to our Privacy Policy. Unsubscribe anytime.', 'dragon-glow' ); ?>
            </p>
        </div>
    </div>
</section>
