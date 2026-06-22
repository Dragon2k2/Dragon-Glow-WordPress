<?php
/**
 * Dragon Glow — Cart Page
 * WooCommerce override: woocommerce/cart/cart.php
 *
 * Luxury glassmorphism cart layout.
 * Quantity updates and item removal are handled via AJAX (assets/js/cart.js).
 * Coupon application uses standard WooCommerce form POST.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>

<!-- ── Atmospheric background blobs ─────────────────────── -->
<div class="dg-cart-bg" aria-hidden="true">
    <div class="dg-cart-blob-1 js-cart-blob"></div>
    <div class="dg-cart-blob-2"></div>
</div>

<main class="min-h-screen relative pb-section-gap">
<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">

    <!-- Page heading -->
    <div class="mb-10">
        <h1 class="font-headline text-headline-lg text-primary leading-tight">
            <?php esc_html_e( 'Your Ritual Essentials', 'dragon-glow' ); ?>
        </h1>
        <p class="text-on-surface-variant font-body-md mt-2 opacity-80">
            <?php esc_html_e( 'Review your selections for a revitalized complexion.', 'dragon-glow' ); ?>
        </p>
    </div>

    <!-- ── Cart layout ──────────────────────────────────────── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start" id="dg-cart-view">

        <!-- LEFT COL: Product table + trust badges (8 / 12) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Glass product panel -->
            <div class="dg-glass-panel rounded-3xl p-6 md:p-8 overflow-hidden">

                <form class="woocommerce-cart-form"
                      action="<?php echo esc_url( wc_get_cart_url() ); ?>"
                      method="post"
                      id="dg-cart-form">

                    <?php do_action( 'woocommerce_before_cart_table' ); ?>

                    <table class="w-full border-collapse" id="dg-cart-table">

                        <thead class="hidden md:table-header-group">
                            <tr class="text-left dg-gold-border">
                                <th class="pb-5 font-label-sm text-label-sm text-on-surface-variant uppercase">
                                    <?php esc_html_e( 'Product', 'dragon-glow' ); ?>
                                </th>
                                <th class="pb-5 font-label-sm text-label-sm text-on-surface-variant uppercase text-center">
                                    <?php esc_html_e( 'Quantity', 'dragon-glow' ); ?>
                                </th>
                                <th class="pb-5 font-label-sm text-label-sm text-on-surface-variant uppercase text-right">
                                    <?php esc_html_e( 'Price', 'dragon-glow' ); ?>
                                </th>
                                <th class="pb-5 w-10"></th>
                            </tr>
                        </thead>

                        <tbody id="dg-cart-items">

                        <?php do_action( 'woocommerce_before_cart_contents' ); ?>

                        <?php
                        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
                            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                            if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
                                continue;
                            }
                            if ( ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                                continue;
                            }

                            $product_permalink = apply_filters(
                                'woocommerce_cart_item_permalink',
                                $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '',
                                $cart_item,
                                $cart_item_key
                            );

                            $current_qty = (int) $cart_item['quantity'];
                        ?>

                        <tr class="dg-cart-row group border-b border-outline-variant/20 last:border-0"
                            id="dg-row-<?php echo esc_attr( $cart_item_key ); ?>"
                            data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">

                            <!-- Product: image + name/variant -->
                            <td class="py-7 pr-4">
                                <div class="flex items-center gap-5">

                                    <!-- Thumbnail -->
                                    <div class="dg-cart-img-wrap">
                                        <?php
                                        $thumbnail = apply_filters(
                                            'woocommerce_cart_item_thumbnail',
                                            $_product->get_image( 'woocommerce_thumbnail' ),
                                            $cart_item,
                                            $cart_item_key
                                        );
                                        if ( $product_permalink ) {
                                            echo '<a href="' . esc_url( $product_permalink ) . '">' . wp_kses_post( $thumbnail ) . '</a>';
                                        } else {
                                            echo wp_kses_post( $thumbnail );
                                        }
                                        ?>
                                    </div>

                                    <!-- Name + variant -->
                                    <div>
                                        <?php if ( $product_permalink ) : ?>
                                            <a href="<?php echo esc_url( $product_permalink ); ?>"
                                               class="font-headline text-[18px] text-on-surface hover:text-primary transition-colors mb-1 block italic">
                                                <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
                                            </a>
                                        <?php else : ?>
                                            <span class="font-headline text-[18px] text-on-surface mb-1 block italic">
                                                <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php
                                        // Variant / attribute data (size, colour, etc.)
                                        $item_data = wc_get_formatted_cart_item_data( $cart_item );
                                        if ( $item_data ) :
                                        ?>
                                        <p class="font-body-md text-on-surface-variant text-sm flex items-center gap-2 mt-1">
                                            <span class="w-2 h-2 rounded-full bg-tertiary-container inline-block flex-shrink-0"></span>
                                            <?php echo wp_kses_post( $item_data ); ?>
                                        </p>
                                        <?php endif; ?>

                                        <?php do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>

                                        <?php if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) : ?>
                                            <p class="text-xs text-on-surface-variant/60 mt-1">
                                                <?php esc_html_e( 'Available on backorder', 'dragon-glow' ); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Quantity stepper (AJAX, no page reload) -->
                            <td class="py-7 text-center">
                                <div class="dg-qty-stepper mx-auto w-fit"
                                     data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>"
                                     data-qty="<?php echo esc_attr( $current_qty ); ?>">
                                    <button type="button"
                                            class="dg-qty-stepper-btn dg-qty-decrease"
                                            aria-label="<?php esc_attr_e( 'Decrease quantity', 'dragon-glow' ); ?>"
                                            <?php echo $current_qty <= 1 ? 'disabled' : ''; ?>>
                                        &minus;
                                    </button>
                                    <span class="dg-qty-value"><?php echo esc_html( $current_qty ); ?></span>
                                    <button type="button"
                                            class="dg-qty-stepper-btn dg-qty-increase"
                                            aria-label="<?php esc_attr_e( 'Increase quantity', 'dragon-glow' ); ?>">
                                        +
                                    </button>
                                </div>
                            </td>

                            <!-- Price (subtotal for this line) -->
                            <td class="py-7 text-right">
                                <span class="font-headline text-[18px] text-primary">
                                    <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                                </span>
                            </td>

                            <!-- Remove (AJAX) -->
                            <td class="py-7 text-right pl-2">
                                <button type="button"
                                        class="dg-remove-btn"
                                        data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>"
                                        aria-label="<?php esc_attr_e( 'Remove this item', 'dragon-glow' ); ?>">
                                    <span class="material-symbols-outlined" style="font-size: 20px;">close</span>
                                </button>
                            </td>

                        </tr>

                        <?php endforeach; ?>

                        <?php do_action( 'woocommerce_cart_contents' ); ?>

                        <!-- Hidden WooCommerce form requirements (coupon, nonce, update) -->
                        <tr class="hidden">
                            <td colspan="4">
                                <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                                <?php do_action( 'woocommerce_cart_actions' ); ?>
                                <button type="submit" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'dragon-glow' ); ?>">
                                    <?php esc_html_e( 'Update cart', 'dragon-glow' ); ?>
                                </button>
                            </td>
                        </tr>

                        <?php do_action( 'woocommerce_after_cart_contents' ); ?>
                        <?php do_action( 'woocommerce_after_cart_table' ); ?>

                        </tbody>
                    </table>

                </form><!-- .woocommerce-cart-form -->
            </div><!-- .dg-glass-panel -->

            <!-- Trust badge pills -->
            <div class="flex flex-wrap gap-3 pt-1">
                <span class="dg-trust-badge">
                    <span class="material-symbols-outlined" style="font-size:18px;">verified</span>
                    <?php esc_html_e( 'Vegan Formula', 'dragon-glow' ); ?>
                </span>
                <span class="dg-trust-badge">
                    <span class="material-symbols-outlined" style="font-size:18px;">eco</span>
                    <?php esc_html_e( 'Cruelty-Free', 'dragon-glow' ); ?>
                </span>
                <span class="dg-trust-badge">
                    <span class="material-symbols-outlined" style="font-size:18px;">magic_button</span>
                    <?php esc_html_e( 'Organic Ingredients', 'dragon-glow' ); ?>
                </span>
            </div>

        </div><!-- end col-span-8 -->

        <!-- RIGHT COL: Order summary sidebar (4 / 12) -->
        <aside class="lg:col-span-4 lg:sticky lg:top-28">
            <div class="dg-glass-panel rounded-3xl p-6 md:p-8 flex flex-col gap-6">

                <h2 class="font-headline text-headline-md text-primary">
                    <?php esc_html_e( 'Order Summary', 'dragon-glow' ); ?>
                </h2>

                <!-- Subtotal + Shipping rows -->
                <div class="space-y-3 border-b border-outline-variant/25 pb-5">

                    <div class="flex justify-between items-center text-on-surface-variant font-body-md text-sm">
                        <span><?php esc_html_e( 'Subtotal', 'dragon-glow' ); ?></span>
                        <span class="font-medium text-on-surface">
                            <?php wc_cart_totals_subtotal_html(); ?>
                        </span>
                    </div>

                    <?php
                    // Applied coupons
                    foreach ( WC()->cart->get_coupons() as $code => $coupon ) :
                    ?>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-on-surface-variant"><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
                        <span class="text-primary font-semibold"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
                    </div>
                    <?php endforeach; ?>

                    <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
                        <?php wc_cart_totals_shipping_html(); ?>
                    <?php else : ?>
                    <div class="flex justify-between items-center text-sm italic">
                        <span class="text-on-surface-variant"><?php esc_html_e( 'Shipping', 'dragon-glow' ); ?></span>
                        <span class="text-on-surface-variant opacity-70"><?php esc_html_e( 'Calculated at next step', 'dragon-glow' ); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-on-surface-variant"><?php echo esc_html( $fee->name ); ?></span>
                        <span><?php wc_cart_totals_fee_html( $fee ); ?></span>
                    </div>
                    <?php endforeach; ?>

                </div>

                <!-- Promo code (submits cart form via JS) -->
                <?php if ( wc_coupons_enabled() ) : ?>
                <div class="space-y-2">
                    <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest block">
                        <?php esc_html_e( 'Promo Code', 'dragon-glow' ); ?>
                    </label>
                    <div class="flex gap-2">
                        <input type="text"
                               name="coupon_code"
                               id="dg-coupon-code"
                               form="dg-cart-form"
                               class="dg-promo-input"
                               placeholder="GLOW20"
                               value="" />
                        <button type="submit"
                                name="apply_coupon"
                                form="dg-cart-form"
                                class="dg-promo-apply-btn"
                                value="<?php esc_attr_e( 'Apply coupon', 'dragon-glow' ); ?>">
                            <?php esc_html_e( 'Apply', 'dragon-glow' ); ?>
                        </button>
                    </div>
                    <?php do_action( 'woocommerce_cart_coupon' ); ?>
                </div>
                <?php endif; ?>

                <!-- Total -->
                <div class="flex justify-between items-end pt-1">
                    <div>
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest block">
                            <?php esc_html_e( 'Total', 'dragon-glow' ); ?>
                        </span>
                        <span class="text-[11px] text-on-surface-variant/60 font-body-md">
                            <?php esc_html_e( 'Inclusive of all taxes', 'dragon-glow' ); ?>
                        </span>
                    </div>
                    <div class="font-headline text-[28px] text-primary text-right">
                        <?php wc_cart_totals_order_total_html(); ?>
                    </div>
                </div>

                <!-- Checkout CTA -->
                <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>"
                   class="dg-shimmer-btn w-full py-5 rounded-2xl font-label-sm text-label-sm uppercase tracking-[0.18em] font-bold flex items-center justify-center gap-2 no-underline shadow-lg">
                    <?php esc_html_e( 'Proceed to Checkout', 'dragon-glow' ); ?>
                    <span class="material-symbols-outlined" style="font-size:20px; transition: transform 0.2s ease;" class="group-hover:translate-x-1">arrow_forward</span>
                </a>

                <!-- SSL trust notice -->
                <div class="flex flex-col items-center gap-3 text-center mt-1">
                    <p class="text-[11px] text-on-surface-variant/60 leading-relaxed max-w-[220px]">
                        <?php esc_html_e( 'Secure Checkout with SSL encryption. We accept Visa, Mastercard, and Apple Pay.', 'dragon-glow' ); ?>
                    </p>
                    <div class="flex gap-3 opacity-35">
                        <span class="material-symbols-outlined" style="font-size:20px;">credit_card</span>
                        <span class="material-symbols-outlined" style="font-size:20px;">account_balance_wallet</span>
                        <span class="material-symbols-outlined" style="font-size:20px;">payments</span>
                    </div>
                </div>

            </div><!-- .dg-glass-panel -->
        </aside>

    </div><!-- #dg-cart-view -->

    <!-- ── Empty cart state (shown via JS when last item removed) -->
    <div id="dg-empty-cart-view" class="text-center py-20">
        <div class="w-32 h-32 mx-auto mb-8 bg-primary-container/10 rounded-full flex items-center justify-center">
            <span class="material-symbols-outlined text-primary" style="font-size:64px; font-variation-settings:'FILL' 0;">shopping_basket</span>
        </div>
        <h2 class="font-headline text-headline-lg text-primary mb-4">
            <?php esc_html_e( 'Your cart is empty', 'dragon-glow' ); ?>
        </h2>
        <p class="text-on-surface-variant font-body-lg max-w-md mx-auto mb-10 opacity-70">
            <?php esc_html_e( 'Review your selections for a revitalized complexion.', 'dragon-glow' ); ?>
        </p>
        <a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"
           class="dg-shimmer-btn inline-flex items-center gap-4 px-10 py-5 rounded-full font-label-sm text-label-sm uppercase tracking-widest font-bold no-underline shadow-xl">
            <?php esc_html_e( 'Shop New Arrivals', 'dragon-glow' ); ?>
        </a>
    </div>

</div><!-- .max-w-container-max -->
</main>

<?php do_action( 'woocommerce_after_cart' ); ?>
