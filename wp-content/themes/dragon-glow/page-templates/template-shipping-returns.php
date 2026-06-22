<?php
/**
 * Template Name: Shipping & Returns — Dragon Glow
 * Description: Shipping policy and returns process page
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

set_query_var( 'dg_hero_title',    esc_html__( 'Shipping & Returns', 'dragon-glow' ) );
set_query_var( 'dg_hero_subtitle', esc_html__( 'Everything you need to know about receiving your ritual — and what happens if it isn\'t perfect.', 'dragon-glow' ) );
get_template_part( 'template-parts/global/page-hero' );
?>

<main class="pb-24" id="main-content">

<?php
// ── In-page navigation ──────────────────────────────────────────────────────
?>
<nav class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop mb-16 md:mb-20 overflow-x-auto scrollbar-hide" aria-label="<?php esc_attr_e( 'Page sections', 'dragon-glow' ); ?>">
    <ul class="flex gap-8 whitespace-nowrap justify-center md:justify-start">
        <li><a class="dg-toc-link font-label-md text-label-md text-primary uppercase tracking-[0.2em] border-b-2 border-transparent hover:border-primary transition-all pb-1" href="#delivery"><?php esc_html_e( 'Delivery', 'dragon-glow' ); ?></a></li>
        <li><a class="dg-toc-link font-label-md text-label-md text-primary uppercase tracking-[0.2em] border-b-2 border-transparent hover:border-primary transition-all pb-1" href="#returns"><?php esc_html_e( 'Returns', 'dragon-glow' ); ?></a></li>
        <li><a class="dg-toc-link font-label-md text-label-md text-primary uppercase tracking-[0.2em] border-b-2 border-transparent hover:border-primary transition-all pb-1" href="#coverage"><?php esc_html_e( 'Coverage', 'dragon-glow' ); ?></a></li>
        <li><a class="dg-toc-link font-label-md text-label-md text-primary uppercase tracking-[0.2em] border-b-2 border-transparent hover:border-primary transition-all pb-1" href="#packaging"><?php esc_html_e( 'Packaging', 'dragon-glow' ); ?></a></li>
    </ul>
</nav>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop space-y-section-gap">

    <?php
    // ── Delivery Methods Table ───────────────────────────────────────────────
    ?>
    <section id="delivery" class="scroll-mt-32" data-reveal>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start">
            <div class="lg:col-span-4">
                <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] block mb-3"><?php esc_html_e( 'Section 01', 'dragon-glow' ); ?></span>
                <h2 class="font-headline text-headline-lg mb-4 text-on-surface"><?php esc_html_e( 'Delivery', 'dragon-glow' ); ?></h2>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                    <?php esc_html_e( 'We offer complimentary standard shipping on all orders. Express and overnight options are available at checkout for those who cannot wait to begin their ritual.', 'dragon-glow' ); ?>
                </p>
            </div>

            <div class="lg:col-span-8 lg:col-start-5 bg-surface-container-lowest border border-outline-variant/30 p-8 md:p-12">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-outline-variant/40">
                            <th class="font-label-md text-label-md text-primary uppercase tracking-[0.15em] pb-4 pr-8"><?php esc_html_e( 'Method', 'dragon-glow' ); ?></th>
                            <th class="font-label-md text-label-md text-primary uppercase tracking-[0.15em] pb-4 pr-8"><?php esc_html_e( 'Timeframe', 'dragon-glow' ); ?></th>
                            <th class="font-label-md text-label-md text-primary uppercase tracking-[0.15em] pb-4"><?php esc_html_e( 'Cost', 'dragon-glow' ); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/30">
                        <tr class="hover:bg-surface-container-low transition-colors duration-200">
                            <td class="py-5 pr-8 font-body-md text-on-surface"><?php esc_html_e( 'Standard Shipping', 'dragon-glow' ); ?></td>
                            <td class="py-5 pr-8 font-body-md text-on-surface-variant"><?php esc_html_e( '5–7 business days', 'dragon-glow' ); ?></td>
                            <td class="py-5 font-body-md text-tertiary font-medium"><?php esc_html_e( 'Free', 'dragon-glow' ); ?></td>
                        </tr>
                        <tr class="hover:bg-surface-container-low transition-colors duration-200">
                            <td class="py-5 pr-8 font-body-md text-on-surface"><?php esc_html_e( 'Express Shipping', 'dragon-glow' ); ?></td>
                            <td class="py-5 pr-8 font-body-md text-on-surface-variant"><?php esc_html_e( '2–3 business days', 'dragon-glow' ); ?></td>
                            <td class="py-5 font-body-md text-on-surface-variant"><?php esc_html_e( '$12.00', 'dragon-glow' ); ?></td>
                        </tr>
                        <tr class="hover:bg-surface-container-low transition-colors duration-200">
                            <td class="py-5 pr-8 font-body-md text-on-surface"><?php esc_html_e( 'Overnight Delivery', 'dragon-glow' ); ?></td>
                            <td class="py-5 pr-8 font-body-md text-on-surface-variant"><?php esc_html_e( 'Next business day', 'dragon-glow' ); ?></td>
                            <td class="py-5 font-body-md text-on-surface-variant"><?php esc_html_e( '$25.00', 'dragon-glow' ); ?></td>
                        </tr>
                        <tr class="hover:bg-surface-container-low transition-colors duration-200">
                            <td class="py-5 pr-8 font-body-md text-on-surface"><?php esc_html_e( 'International', 'dragon-glow' ); ?></td>
                            <td class="py-5 pr-8 font-body-md text-on-surface-variant"><?php esc_html_e( '10–21 business days', 'dragon-glow' ); ?></td>
                            <td class="py-5 font-body-md text-on-surface-variant"><?php esc_html_e( 'Calculated at checkout', 'dragon-glow' ); ?></td>
                        </tr>
                    </tbody>
                </table>
                <p class="mt-6 font-body-sm text-on-surface-variant/70 text-xs">
                    <?php esc_html_e( 'Business days are Monday through Friday, excluding public holidays. Orders placed after 2pm EST will be processed the following business day.', 'dragon-glow' ); ?>
                </p>
            </div>
        </div>
    </section>

    <?php
    // ── Returns Process ──────────────────────────────────────────────────────
    ?>
    <section id="returns" class="scroll-mt-32" data-reveal>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start">
            <div class="lg:col-span-4">
                <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] block mb-3"><?php esc_html_e( 'Section 02', 'dragon-glow' ); ?></span>
                <h2 class="font-headline text-headline-lg mb-4 text-on-surface"><?php esc_html_e( 'Returns', 'dragon-glow' ); ?></h2>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                    <?php esc_html_e( 'We want you to love your ritual. If something isn\'t right, our returns process is designed to be as seamless as the experience itself.', 'dragon-glow' ); ?>
                </p>
            </div>

            <div class="lg:col-span-8 lg:col-start-5">
                <p class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] mb-8">
                    <?php esc_html_e( 'How to return an item', 'dragon-glow' ); ?>
                </p>
                <ol class="space-y-0">
                    <?php
                    $return_steps = array(
                        esc_html__( 'Request a Return', 'dragon-glow' ) => esc_html__( 'Contact our concierge team within 30 days of delivery via the Contact page or by emailing concierge@dragonglow.com. Include your order number and reason for return.', 'dragon-glow' ),
                        esc_html__( 'Receive Your Label', 'dragon-glow' ) => esc_html__( 'We will email a prepaid, carbon-neutral return shipping label within 1 business day. No cost to you for standard returns.', 'dragon-glow' ),
                        esc_html__( 'Package & Send', 'dragon-glow' ) => esc_html__( 'Reuse your original packaging or any sturdy box. Seal securely and drop off at any authorised shipping point. Keep your proof of postage.', 'dragon-glow' ),
                        esc_html__( 'Refund Processed', 'dragon-glow' ) => esc_html__( 'Once received and inspected, we will issue a full refund to your original payment method within 5–7 business days. You will receive a confirmation email.', 'dragon-glow' ),
                    );
                    $step_num = 1;
                    foreach ( $return_steps as $step_title => $step_desc ) :
                    ?>
                    <li class="dg-return-step flex gap-6 pb-10 relative
                        <?php
                        if ( $step_num < count( $return_steps ) ) {
                            echo 'border-l-2 border-outline-variant/40';
                            echo ' pl-8';
                        } else {
                            echo 'pl-14';
                        }
                        ?>">
                        <?php if ( $step_num < count( $return_steps ) ) : ?>
                            <span class="dg-step-number absolute left-[-13px] top-0 w-6 h-6 rounded-full bg-primary text-on-primary font-label-sm text-label-sm flex items-center justify-center"><?php echo esc_html( $step_num ); ?></span>
                        <?php else : ?>
                            <span class="dg-step-check absolute left-[2px] top-[2px] w-4 h-4 rounded-full bg-secondary text-on-secondary flex items-center justify-center">
                                <span class="material-symbols-outlined text-xs">check</span>
                            </span>
                        <?php endif; ?>
                        <div>
                            <h3 class="font-headline text-headline-md text-on-surface mb-2"><?php echo esc_html( $step_title ); ?></h3>
                            <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed"><?php echo esc_html( $step_desc ); ?></p>
                        </div>
                    </li>
                    <?php
                    $step_num++;
                    endforeach;
                    ?>
                </ol>

                <div class="mt-4 p-6 bg-surface-container-low border-l-4 border-primary">
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                        <span class="font-medium text-on-surface"><?php esc_html_e( 'Exceptions: ', 'dragon-glow' ); ?></span>
                        <?php esc_html_e( 'Sealed products that have been opened or used may not be eligible for a full refund. Gift items may be returned for store credit only. Final-sale items are non-returnable.', 'dragon-glow' ); ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <?php
    // ── Delivery Coverage ────────────────────────────────────────────────────
    ?>
    <section id="coverage" class="scroll-mt-32" data-reveal>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start">
            <div class="lg:col-span-4">
                <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] block mb-3"><?php esc_html_e( 'Section 03', 'dragon-glow' ); ?></span>
                <h2 class="font-headline text-headline-lg mb-4 text-on-surface"><?php esc_html_e( 'Delivery Coverage', 'dragon-glow' ); ?></h2>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                    <?php esc_html_e( 'We currently ship to over 60 countries. All international orders are customs-cleared before delivery — any import duties or taxes are the responsibility of the recipient.', 'dragon-glow' ); ?>
                </p>
            </div>

            <div class="lg:col-span-8 lg:col-start-5 grid grid-cols-1 md:grid-cols-2 gap-gutter">
                <div class="bg-surface-container-lowest border border-outline-variant/30 p-8 hover:shadow-md hover:shadow-primary/5 transition-shadow duration-300">
                    <span class="material-symbols-outlined text-primary text-2xl mb-4 block">public</span>
                    <h3 class="font-headline text-headline-md text-on-surface mb-3"><?php esc_html_e( 'Domestic (US)', 'dragon-glow' ); ?></h3>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                        <?php esc_html_e( 'All 50 states plus territories. APO/FPO addresses welcome. Standard shipping uses USPS, UPS, or FedEx based on your location.', 'dragon-glow' ); ?>
                    </p>
                </div>
                <div class="bg-surface-container-lowest border border-outline-variant/30 p-8 hover:shadow-md hover:shadow-primary/5 transition-shadow duration-300">
                    <span class="material-symbols-outlined text-primary text-2xl mb-4 block">globe_uk</span>
                    <h3 class="font-headline text-headline-md text-on-surface mb-3"><?php esc_html_e( 'International', 'dragon-glow' ); ?></h3>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                        <?php esc_html_e( 'Canada, United Kingdom, European Union, Australia, Japan, South Korea, Singapore, UAE, and more. Duties and taxes calculated at checkout.', 'dragon-glow' ); ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <?php
    // ── Sustainable Packaging ─────────────────────────────────────────────────
    ?>
    <section id="packaging" class="scroll-mt-32" data-reveal>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start">
            <div class="lg:col-span-4">
                <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] block mb-3"><?php esc_html_e( 'Section 04', 'dragon-glow' ); ?></span>
                <h2 class="font-headline text-headline-lg mb-4 text-on-surface"><?php esc_html_e( 'Packaging', 'dragon-glow' ); ?></h2>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                    <?php esc_html_e( 'Every detail matters — including what holds your ritual before it reaches your hands. Our packaging reflects the same care we put into our formulas.', 'dragon-glow' ); ?>
                </p>
            </div>

            <div class="lg:col-span-8 lg:col-start-5">
                <div class="bg-surface-container-lowest border border-outline-variant/30 overflow-hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-outline-variant/30">
                        <div class="p-8 md:p-10">
                            <span class="material-symbols-outlined text-primary text-2xl mb-4 block">eco</span>
                            <h3 class="font-headline text-headline-md text-on-surface mb-3"><?php esc_html_e( 'Responsibly Sourced', 'dragon-glow' ); ?></h3>
                            <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                                <?php esc_html_e( 'Our outer cartons are made from FSC-certified recycled board. The glass bottles are 100% recyclable. Ink is vegetable-based and free from harmful chemicals.', 'dragon-glow' ); ?>
                            </p>
                        </div>
                        <div class="p-8 md:p-10 bg-surface-container-low">
                            <span class="material-symbols-outlined text-primary text-2xl mb-4 block">recycling</span>
                            <h3 class="font-headline text-headline-md text-on-surface mb-3"><?php esc_html_e( 'Zero-Waste Philosophy', 'dragon-glow' ); ?></h3>
                            <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                                <?php esc_html_e( 'Our inner cushioning is made from recycled and biodegradable materials. No plastic void fill, no unnecessary wrapping. Each element is designed to be repurposed or composted.', 'dragon-glow' ); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
</main>

<?php get_footer(); ?>
