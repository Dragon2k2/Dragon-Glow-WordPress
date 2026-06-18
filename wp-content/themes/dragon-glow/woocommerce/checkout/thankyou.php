<?php
/**
 * Dragon Glow — Order Confirmation
 * Override: woocommerce/checkout/thankyou.php
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_thankyou', $order->get_id() );
?>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
    <?php if ( $order->has_status( 'failed' ) ) : ?>

        <!-- Failed Order -->
        <div class="text-center py-16">
            <div class="w-24 h-24 mx-auto bg-error-container rounded-full flex items-center justify-center mb-6">
                <span class="material-symbols-outlined text-error" style="font-size: 64px;">error</span>
            </div>

            <h1 class="font-headline text-headline-lg text-primary mb-4">
                <?php esc_html_e( 'Payment Failed', 'dragon-glow' ); ?>
            </h1>

            <p class="text-on-surface-variant text-body-lg mb-8 max-w-md mx-auto">
                <?php esc_html_e( 'Unfortunately, your payment could not be processed. Please try again or contact us for assistance.', 'dragon-glow' ); ?>
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="btn-primary">
                    <?php esc_html_e( 'Try Again', 'dragon-glow' ); ?>
                </a>
                <a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ); ?>" class="btn-ghost">
                    <?php esc_html_e( 'Contact Us', 'dragon-glow' ); ?>
                </a>
            </div>
        </div>

    <?php else : ?>

        <!-- Successful Order -->
        <div class="text-center py-8">
            <div class="w-32 h-32 mx-auto bg-secondary-container rounded-full flex items-center justify-center mb-8 animate-float">
                <span class="material-symbols-outlined text-primary" style="font-size: 80px;">check_circle</span>
            </div>

            <h1 class="font-headline text-headline-lg text-primary mb-4">
                <?php esc_html_e( 'Thank You for Your Order!', 'dragon-glow' ); ?>
            </h1>

            <p class="text-on-surface-variant text-body-lg mb-4 max-w-lg mx-auto">
                <?php
                printf(
                    esc_html__( 'Order #%s has been received.', 'dragon-glow' ),
                    '<strong>' . $order->get_order_number() . '</strong>'
                );
                ?>
            </p>

            <p class="text-on-surface-variant mb-8">
                <?php esc_html_e( 'We\'ve sent a confirmation email with your order details. Your luminous skincare is on its way!', 'dragon-glow' ); ?>
            </p>
        </div>

        <!-- Order Details -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-3xl shadow-sm p-8 mb-8">
                <h2 class="font-headline text-xl text-primary mb-6">
                    <?php esc_html_e( 'Order Details', 'dragon-glow' ); ?>
                </h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                    <div>
                        <span class="text-label-sm text-on-surface-variant block mb-1"><?php esc_html_e( 'Order Number', 'dragon-glow' ); ?></span>
                        <span class="font-bold text-primary"><?php echo esc_html( $order->get_order_number() ); ?></span>
                    </div>
                    <div>
                        <span class="text-label-sm text-on-surface-variant block mb-1"><?php esc_html_e( 'Date', 'dragon-glow' ); ?></span>
                        <span class="font-bold"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
                    </div>
                    <div>
                        <span class="text-label-sm text-on-surface-variant block mb-1"><?php esc_html_e( 'Total', 'dragon-glow' ); ?></span>
                        <span class="font-bold text-primary"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
                    </div>
                    <div>
                        <span class="text-label-sm text-on-surface-variant block mb-1"><?php esc_html_e( 'Payment Method', 'dragon-glow' ); ?></span>
                        <span class="font-bold"><?php echo esc_html( $order->get_payment_method_title() ); ?></span>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="border-t border-outline-variant pt-6">
                    <h3 class="font-headline text-lg text-primary mb-4"><?php esc_html_e( 'Items', 'dragon-glow' ); ?></h3>

                    <div class="space-y-4">
                        <?php
                        foreach ( $order->get_items() as $item_id => $item ) {
                            $product = $item->get_product();
                            if ( ! $product ) {
                                continue;
                            }
                        ?>
                            <div class="flex items-center gap-4">
                                <?php
                                $thumbnail = $product->get_image( 'thumbnail' );
                                if ( $thumbnail ) {
                                    echo wp_kses_post( $thumbnail );
                                }
                                ?>
                                <div class="flex-1">
                                    <h4 class="font-medium"><?php echo esc_html( $item->get_name() ); ?></h4>
                                    <p class="text-sm text-on-surface-variant">
                                        <?php echo esc_html( $item->get_quantity() . ' × ' . wc_price( $item->get_subtotal() / $item->get_quantity() ) ); ?>
                                    </p>
                                </div>
                                <span class="font-bold"><?php echo wp_kses_post( wc_price( $item->get_subtotal() ) ); ?></span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Customer Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Billing Address -->
                <div class="bg-white rounded-3xl shadow-sm p-6">
                    <h3 class="font-headline text-lg text-primary mb-4">
                        <?php esc_html_e( 'Billing Address', 'dragon-glow' ); ?>
                    </h3>
                    <address class="not-style-list text-on-surface-variant">
                        <?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?>
                    </address>
                </div>

                <!-- Shipping Address -->
                <?php if ( $order->get_formatted_shipping_address() ) : ?>
                <div class="bg-white rounded-3xl shadow-sm p-6">
                    <h3 class="font-headline text-lg text-primary mb-4">
                        <?php esc_html_e( 'Shipping Address', 'dragon-glow' ); ?>
                    </h3>
                    <address class="not-style-list text-on-surface-variant">
                        <?php echo wp_kses_post( $order->get_formatted_shipping_address() ); ?>
                    </address>
                </div>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <?php if ( $order->has_status( 'processing' ) || $order->has_status( 'completed' ) ) : ?>
                <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="btn-primary">
                    <?php esc_html_e( 'View Order Details', 'dragon-glow' ); ?>
                </a>
                <?php endif; ?>

                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="btn-ghost">
                    <?php esc_html_e( 'Continue Shopping', 'dragon-glow' ); ?>
                </a>

                <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>" class="btn-ghost">
                    <?php esc_html_e( 'My Account', 'dragon-glow' ); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

    <?php endif; ?>
</div>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
.animate-float {
    animation: float 2s ease-in-out infinite;
}
</style>
