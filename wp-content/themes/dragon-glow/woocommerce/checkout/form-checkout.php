<?php
/**
 * Dragon Glow — Checkout Form
 * Override: woocommerce/checkout/form-checkout.php
 * 3-step checkout stepper
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
    echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'dragon-glow' ) ) );
    return;
}
?>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
    <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>

    <h1 class="font-headline text-headline-lg text-primary mb-8">
        <?php esc_html_e( 'Checkout', 'dragon-glow' ); ?>
    </h1>

    <!-- Progress Stepper -->
    <div class="flex items-center justify-center mb-12" id="dg-checkout-stepper">
        <div class="flex items-center gap-4">
            <!-- Step 1: Information -->
            <div class="flex items-center gap-3" id="dg-step-1">
                <div id="dg-step-1-circle" class="step-active w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 transition-all">
                    <span class="step-number">1</span>
                    <span class="step-check hidden material-symbols-outlined">check</span>
                </div>
                <span id="dg-step-1-label" class="font-medium text-primary hidden sm:block"><?php esc_html_e( 'Information', 'dragon-glow' ); ?></span>
            </div>

            <div class="w-16 h-0.5 bg-outline-variant"></div>

            <!-- Step 2: Shipping -->
            <div class="flex items-center gap-3" id="dg-step-2">
                <div id="dg-step-2-circle" class="step-inactive w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 transition-all">
                    <span class="step-number">2</span>
                    <span class="step-check hidden material-symbols-outlined">check</span>
                </div>
                <span id="dg-step-2-label" class="font-medium text-on-surface-variant hidden sm:block"><?php esc_html_e( 'Shipping', 'dragon-glow' ); ?></span>
            </div>

            <div class="w-16 h-0.5 bg-outline-variant"></div>

            <!-- Step 3: Payment -->
            <div class="flex items-center gap-3" id="dg-step-3">
                <div id="dg-step-3-circle" class="step-inactive w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 transition-all">
                    <span class="step-number">3</span>
                    <span class="step-check hidden material-symbols-outlined">check</span>
                </div>
                <span id="dg-step-3-label" class="font-medium text-on-surface-variant hidden sm:block"><?php esc_html_e( 'Payment', 'dragon-glow' ); ?></span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Checkout Form -->
        <div class="lg:col-span-2">
            <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

                <?php if ( $checkout->get_checkout_fields() ) : ?>

                    <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

                    <div class="col2-set" id="customer_details">
                        <div class="col-1">
                            <?php do_action( 'woocommerce_checkout_billing' ); ?>
                        </div>

                        <div class="col-2">
                            <?php do_action( 'woocommerce_checkout_shipping' ); ?>
                        </div>
                    </div>

                    <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

                <?php endif; ?>

                <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

                <h3 id="order_review_heading" class="hidden"><?php esc_html_e( 'Your order', 'dragon-glow' ); ?></h3>

                <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

                <div id="order_review" class="woocommerce-checkout-review-order">
                    <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                </div>

                <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

            </form>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-surface-container rounded-3xl p-8 sticky top-24">
                <h2 class="font-headline text-xl text-primary mb-6">
                    <?php esc_html_e( 'Order Summary', 'dragon-glow' ); ?>
                </h2>

                <?php do_action( 'woocommerce_checkout_order_review' ); ?>

                <!-- Trust signals -->
                <div class="mt-8 pt-6 border-t border-outline-variant space-y-3">
                    <div class="flex items-center gap-3 text-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-primary text-lg">lock</span>
                        <?php esc_html_e( 'Secure 256-bit SSL encryption', 'dragon-glow' ); ?>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-primary text-lg">local_shipping</span>
                        <?php esc_html_e( 'Free shipping on orders over $75', 'dragon-glow' ); ?>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-on-surface-variant">
                        <span class="material-symbols-outlined text-primary text-lg">replay_circle_filled</span>
                        <?php esc_html_e( '30-day money-back guarantee', 'dragon-glow' ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
