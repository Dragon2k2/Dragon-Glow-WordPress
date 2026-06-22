<?php
/**
 * Template Name: Track Your Order — Dragon Glow
 * Description: Order tracking page with WooCommerce integration
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

set_query_var( 'dg_hero_title',    esc_html__( 'Track Your Order', 'dragon-glow' ) );
set_query_var( 'dg_hero_subtitle', esc_html__( 'Enter your order details below to see the latest status of your shipment.', 'dragon-glow' ) );
get_template_part( 'template-parts/global/page-hero' );

$woocommerce_active = dg_is_woocommerce_active();
?>

<main class="pb-24" id="main-content">

    <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop mb-16">

        <?php if ( $woocommerce_active ) : ?>

            <?php
            // ── WooCommerce order tracking shortcode (styled container) ──────────
            ?>
            <div class="dg-tracking-form-wrapper max-w-xl mx-auto">
                <div class="bg-surface-container-lowest border border-outline-variant/30 p-10 md:p-16">
                    <?php echo do_shortcode( '[woocommerce_order_tracking]' ); ?>
                </div>
            </div>

        <?php else : ?>

            <?php
            // ── Mock / offline state ────────────────────────────────────────────
            ?>
            <div class="dg-tracking-form-wrapper max-w-xl mx-auto text-center">
                <div class="bg-surface-container-lowest border border-outline-variant/30 p-10 md:p-16 mb-8">
                    <span class="material-symbols-outlined text-primary/50 text-5xl mb-6 block">local_shipping</span>
                    <h2 class="font-headline text-headline-md text-on-surface mb-4">
                        <?php esc_html_e( 'Order Tracking Available Soon', 'dragon-glow' ); ?>
                    </h2>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed max-w-md mx-auto">
                        <?php esc_html_e( 'Our order tracking system is being prepared for launch. In the meantime, our concierge team is happy to assist you with any order enquiry.', 'dragon-glow' ); ?>
                    </p>
                </div>

                <div class="bg-surface-container-low border border-outline-variant/20 p-8 text-left">
                    <p class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] mb-6">
                        <?php esc_html_e( 'To track your order, please have ready:', 'dragon-glow' ); ?>
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-primary text-lg mt-[2px] flex-shrink-0">check_circle</span>
                            <span class="font-body-md text-body-md text-on-surface-variant"><?php esc_html_e( 'Your Order ID (found in your confirmation email)', 'dragon-glow' ); ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-primary text-lg mt-[2px] flex-shrink-0">check_circle</span>
                            <span class="font-body-md text-body-md text-on-surface-variant"><?php esc_html_e( 'The email address used at checkout', 'dragon-glow' ); ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-primary text-lg mt-[2px] flex-shrink-0">check_circle</span>
                            <span class="font-body-md text-body-md text-on-surface-variant"><?php esc_html_e( 'Your billing postcode (for postcode verification)', 'dragon-glow' ); ?></span>
                        </li>
                    </ul>
                </div>
            </div>

        <?php endif; ?>

    </section>

    <?php
    // ── Need help block ────────────────────────────────────────────────────
    ?>
    <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop mt-8 pt-16 border-t border-outline-variant/30">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter items-center">
            <div>
                <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] block mb-3"><?php esc_html_e( 'Need further assistance?', 'dragon-glow' ); ?></span>
                <h2 class="font-headline text-headline-lg text-on-surface mb-4"><?php esc_html_e( 'Our concierge team is standing by', 'dragon-glow' ); ?></h2>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed mb-8">
                    <?php esc_html_e( 'If you have any questions about your order, a delayed shipment, or anything else, our team will respond within one business day.', 'dragon-glow' ); ?>
                </p>
                <?php
                $contact_page = get_page_by_path( 'contact' );
                $contact_url  = $contact_page ? get_permalink( $contact_page->ID ) : '#';
                ?>
                <a href="<?php echo esc_url( $contact_url ); ?>"
                   class="inline-flex items-center gap-2 bg-primary text-on-primary font-label-md text-label-md uppercase tracking-[0.25em] py-4 px-10 hover:opacity-90 transition-opacity">
                    <?php esc_html_e( 'Contact Concierge', 'dragon-glow' ); ?>
                    <span class="material-symbols-outlined text-xl" aria-hidden="true">arrow_forward</span>
                </a>
            </div>
            <div class="flex flex-col gap-4">
                <div class="bg-surface-container-lowest border border-outline-variant/30 p-6 flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary text-2xl flex-shrink-0 mt-[2px]">mail</span>
                    <div>
                        <p class="font-label-sm text-label-sm text-primary uppercase tracking-[0.15em] mb-1"><?php esc_html_e( 'Email', 'dragon-glow' ); ?></p>
                        <p class="font-body-md text-body-md text-on-surface">concierge@dragonglow.com</p>
                    </div>
                </div>
                <div class="bg-surface-container-lowest border border-outline-variant/30 p-6 flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary text-2xl flex-shrink-0 mt-[2px]">phone_iphone</span>
                    <div>
                        <p class="font-label-sm text-label-sm text-primary uppercase tracking-[0.15em] mb-1"><?php esc_html_e( 'Phone', 'dragon-glow' ); ?></p>
                        <p class="font-body-md text-body-md text-on-surface"><?php esc_html_e( '+1 (800) GLOW-NOW', 'dragon-glow' ); ?></p>
                    </div>
                </div>
                <div class="bg-surface-container-lowest border border-outline-variant/30 p-6 flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary text-2xl flex-shrink-0 mt-[2px]">schedule</span>
                    <div>
                        <p class="font-label-sm text-label-sm text-primary uppercase tracking-[0.15em] mb-1"><?php esc_html_e( 'Hours', 'dragon-glow' ); ?></p>
                        <p class="font-body-md text-body-md text-on-surface"><?php esc_html_e( 'Mon – Fri: 9am – 6pm EST', 'dragon-glow' ); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
