<?php
/**
 * Dragon Glow — Order Confirmation
 * Override: woocommerce/checkout/thankyou.php
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// wc_get_template() in WC 10.x passes $order via $args['order']. WC does
// extract() into local scope, so $order IS available below — but we read it
// from $args explicitly + guard against false/empty so we always render a
// usable page (don't crash if the order can't be loaded).
$order = is_array( $args ) && isset( $args['order'] ) ? $args['order'] : false;

if ( ! $order ) {
	// Fallback: WC passes order=false when the order ID is invalid or the
	// order key doesn't match. Render the default partial so the page still
	// shows "Order received" without breaking on $order->...() calls.
	wc_get_template( 'checkout/order-received.php', array( 'order' => false ) );
	return;
}

do_action( 'woocommerce_before_thankyou', $order->get_id() );
?>

<div class="dg-thankyou-main">
    <?php if ( $order->has_status( 'failed' ) ) : ?>

        <!-- Failed Order -->
        <div class="dg-thankyou-header">
            <div class="dg-thankyou-icon dg-thankyou-icon--error mb-6">
                <span class="material-symbols-outlined text-error">error</span>
            </div>

            <h1 class="dg-thankyou-title">
                <?php esc_html_e( 'Payment Failed', 'dragon-glow' ); ?>
            </h1>

            <p class="dg-thankyou-subtitle">
                <?php esc_html_e( 'Unfortunately, your payment could not be processed. Please try again or contact us for assistance.', 'dragon-glow' ); ?>
            </p>

            <div class="dg-thankyou-actions">
                <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="dg-btn dg-btn--primary">
                    <?php esc_html_e( 'Try Again', 'dragon-glow' ); ?>
                </a>
                <a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ); ?>" class="dg-btn dg-btn--ghost">
                    <?php esc_html_e( 'Contact Us', 'dragon-glow' ); ?>
                </a>
            </div>
        </div>

    <?php else : ?>

        <!-- Successful Order -->
        <div class="dg-thankyou-header">
            <div class="dg-thankyou-icon dg-thankyou-icon--animate">
                <span class="material-symbols-outlined">check_circle</span>
            </div>

            <h1 class="dg-thankyou-title">
                <?php esc_html_e( 'Thank You for Your Order!', 'dragon-glow' ); ?>
            </h1>

            <p class="dg-thankyou-subtitle">
                <?php
                printf(
                    esc_html__( 'Order #%s has been received.', 'dragon-glow' ),
                    '<strong>' . $order->get_order_number() . '</strong>'
                );
                ?>
            </p>

            <p class="dg-thankyou-email">
                <?php esc_html_e( 'We\'ve sent a confirmation email with your order details. Your luminous skincare is on its way!', 'dragon-glow' ); ?>
            </p>
        </div>

        <?php
        /*
         * BACS payment instructions — only when the customer chose
         * Direct Bank Transfer. Show the Order ID prominently (it doubles as
         * the bank-transfer reference) and render the QR / bank details panel
         * via the same dg_bacs_qr_panel() used on the checkout page, now
         * hydrated with the placed order so the QR encodes the real order ID.
         */
        if ( 'bacs' === $order->get_payment_method() ) :
            $bacs_order_id = $order->get_order_number();
        ?>
            <div class="dg-thankyou-bacs">
                <div class="dg-thankyou-bacs-card">
                    <p class="dg-thankyou-bacs-eyebrow">
                        <?php esc_html_e( 'Complete your payment', 'dragon-glow' ); ?>
                    </p>
                    <h2 class="dg-thankyou-bacs-title">
                        <?php
                        printf(
                            esc_html__( 'Transfer to: %s', 'dragon-glow' ),
                            esc_html( (string) get_theme_mod( 'dg_bacs_qr_bank_name', __( 'JPMorgan Chase Bank, N.A.', 'dragon-glow' ) ) )
                        );
                        ?>
                    </h2>
                    <p class="dg-thankyou-bacs-desc">
                        <?php
                        printf(
                            /* translators: %s: order number, used as the bank-transfer reference. */
                            esc_html__( 'Use Order ID #%s as the payment reference so we can match your transfer to your order.', 'dragon-glow' ),
                            '<strong>' . esc_html( $bacs_order_id ) . '</strong>'
                        );
                        ?>
                    </p>

                    <?php
                    dg_bacs_qr_panel( $order );
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Order Details -->
        <div class="dg-thankyou-details-wrap">
            <div class="dg-thankyou-card">
                <h2 class="dg-thankyou-card-title">
                    <?php esc_html_e( 'Order Details', 'dragon-glow' ); ?>
                </h2>

                <div class="dg-thankyou-meta-grid">
                    <div>
                        <span class="dg-thankyou-meta-label"><?php esc_html_e( 'Order Number', 'dragon-glow' ); ?></span>
                        <span class="dg-thankyou-meta-value dg-thankyou-meta-value--accent">#<?php echo esc_html( $order->get_order_number() ); ?></span>
                    </div>
                    <div>
                        <span class="dg-thankyou-meta-label"><?php esc_html_e( 'Date', 'dragon-glow' ); ?></span>
                        <span class="dg-thankyou-meta-value"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
                    </div>
                    <div>
                        <span class="dg-thankyou-meta-label"><?php esc_html_e( 'Total', 'dragon-glow' ); ?></span>
                        <span class="dg-thankyou-meta-value dg-thankyou-meta-value--accent"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
                    </div>
                    <div>
                        <span class="dg-thankyou-meta-label"><?php esc_html_e( 'Payment Method', 'dragon-glow' ); ?></span>
                        <span class="dg-thankyou-meta-value"><?php echo esc_html( $order->get_payment_method_title() ); ?></span>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="dg-thankyou-items">
                    <h3 class="dg-thankyou-items-title"><?php esc_html_e( 'Items', 'dragon-glow' ); ?></h3>

                    <?php
                    foreach ( $order->get_items() as $item_id => $item ) {
                        $product = $item->get_product();
                        if ( ! $product ) {
                            continue;
                        }
                        $thumbnail = $product->get_image( 'thumbnail' );
                    ?>
                        <div class="dg-thankyou-item">
                            <?php if ( $thumbnail ) : ?>
                                <div class="dg-thankyou-item-thumb">
                                    <?php echo wp_kses_post( $thumbnail ); ?>
                                </div>
                            <?php else : ?>
                                <div class="dg-thankyou-item-thumb">
                                    <span class="material-symbols-outlined">spa</span>
                                </div>
                            <?php endif; ?>
                            <div class="dg-thankyou-item-info">
                                <h4 class="dg-thankyou-item-name"><?php echo esc_html( $item->get_name() ); ?></h4>
                                <p class="dg-thankyou-item-qty">
                                    <?php
                                    $line_unit_price = $item->get_subtotal() / max( 1, $item->get_quantity() );
                                    echo wp_kses_post( sprintf(
                                        /* translators: 1: quantity, 2: formatted unit price. */
                                        esc_html__( '%1$s × %2$s', 'dragon-glow' ),
                                        esc_html( $item->get_quantity() ),
                                        wc_price( $line_unit_price )
                                    ) );
                                    ?>
                                </p>
                            </div>
                            <span class="dg-thankyou-item-price"><?php echo wp_kses_post( wc_price( $item->get_subtotal() ) ); ?></span>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Customer Details — 2-column grid on desktop:
                 col 1 = Billing Address, col 2 = Contact (email + phone).
                 Stacks to 1 column on mobile (<768px). Shipping card is
                 omitted because shipping falls back to billing when "Ship
                 to different address" is unchecked — those 2 cards would be
                 identical, so a 2-column grid with Billing + Contact reads
                 cleaner. -->
            <div class="dg-thankyou-addresses">
                <div class="dg-thankyou-address-card">
                    <h3 class="dg-thankyou-address-title">
                        <?php esc_html_e( 'Billing Address', 'dragon-glow' ); ?>
                    </h3>
                    <address class="dg-thankyou-address">
                        <?php
                        $raw_address = $order->get_formatted_billing_address();
                        // WooCommerce wraps the customer name (first line) in <strong>.
                        // If already wrapped, re-wrap with our marker class. If not (plain text),
                        // inject <strong> around the first line so CSS can target it for gold highlight.
                        if ( preg_match( '/^<strong>(.*?)<\/strong>/s', $raw_address, $m ) ) {
                            // Already has <strong> — add our marker class.
                            echo '<strong class="dg-thankyou-customer-name">' . wp_kses_post( $m[1] ) . '</strong>';
                            echo substr( $raw_address, strlen( $m[0] ) );
                        } else {
                            // Plain text — inject <strong> on the first line (before first <br>).
                            $parts = explode( '<br', $raw_address, 2 );
                            if ( count( $parts ) >= 2 ) {
                                echo '<strong class="dg-thankyou-customer-name">' . wp_kses_post( trim( $parts[0] ) ) . '</strong><br';
                                echo $parts[1];
                            } else {
                                echo wp_kses_post( $raw_address );
                            }
                        }
                        ?>
                    </address>
                </div>

                <div class="dg-thankyou-address-card dg-thankyou-contact-card">
                    <h3 class="dg-thankyou-address-title">
                        <?php esc_html_e( 'Contact', 'dragon-glow' ); ?>
                    </h3>
                    <ul class="dg-thankyou-contact-list">
                        <li class="dg-thankyou-contact-row">
                            <span class="dg-thankyou-contact-icon" aria-hidden="true">
                                <span class="material-symbols-outlined">mail</span>
                            </span>
                            <span class="dg-thankyou-contact-text">
                                <span class="dg-thankyou-contact-label"><?php esc_html_e( 'Email', 'dragon-glow' ); ?></span>
                                <a class="dg-thankyou-contact-value" href="mailto:<?php echo esc_attr( $order->get_billing_email() ); ?>">
                                    <?php echo esc_html( $order->get_billing_email() ); ?>
                                </a>
                            </span>
                        </li>
                        <?php if ( $order->get_billing_phone() ) : ?>
                            <li class="dg-thankyou-contact-row">
                                <span class="dg-thankyou-contact-icon" aria-hidden="true">
                                    <span class="material-symbols-outlined">call</span>
                                </span>
                                <span class="dg-thankyou-contact-text">
                                    <span class="dg-thankyou-contact-label"><?php esc_html_e( 'Phone', 'dragon-glow' ); ?></span>
                                    <a class="dg-thankyou-contact-value" href="tel:<?php echo esc_attr( $order->get_billing_phone() ); ?>">
                                        <?php echo esc_html( $order->get_billing_phone() ); ?>
                                    </a>
                                </span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Actions -->
            <div class="dg-thankyou-actions">
                <?php if ( $order->has_status( 'processing' ) || $order->has_status( 'completed' ) ) : ?>
                <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="dg-btn dg-btn--primary">
                    <?php esc_html_e( 'View Order Details', 'dragon-glow' ); ?>
                </a>
                <?php endif; ?>

                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="dg-btn dg-btn--ghost">
                    <?php esc_html_e( 'Continue Shopping', 'dragon-glow' ); ?>
                </a>

                <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>" class="dg-btn dg-btn--ghost">
                    <?php esc_html_e( 'My Account', 'dragon-glow' ); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

    <?php endif; ?>
</div>
