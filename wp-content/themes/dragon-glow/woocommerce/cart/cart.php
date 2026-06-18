<?php
/**
 * Dragon Glow — Cart Page
 * Override: woocommerce/cart/cart.php
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
    <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>

    <h1 class="font-headline text-headline-lg text-primary mb-8">
        <?php esc_html_e( 'Your Ritual Cart', 'dragon-glow' ); ?>
    </h1>

    <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
        <?php do_action( 'woocommerce_before_cart_table' ); ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-sm overflow-hidden">
                    <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="product-remove">&nbsp;</th>
                                <th class="product-thumbnail">&nbsp;</th>
                                <th class="product-name"><?php esc_html_e( 'Product', 'dragon-glow' ); ?></th>
                                <th class="product-price"><?php esc_html_e( 'Price', 'dragon-glow' ); ?></th>
                                <th class="product-quantity"><?php esc_html_e( 'Quantity', 'dragon-glow' ); ?></th>
                                <th class="product-subtotal"><?php esc_html_e( 'Subtotal', 'dragon-glow' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php do_action( 'woocommerce_before_cart_contents' ); ?>

                            <?php
                            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                                $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                                $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                                if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                                    $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                                    ?>
                                    <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

                                        <td class="product-remove">
                                            <?php
                                            echo apply_filters(
                                                'woocommerce_cart_item_remove_link',
                                                sprintf(
                                                    '<a href="%s" class="remove w-10 h-10 rounded-full bg-surface flex items-center justify-center text-on-surface-variant hover:bg-error hover:text-white transition-colors" aria-label="%s" data-product_id="%s" data-product_sku="%s"><span class="material-symbols-outlined">close</span></a>',
                                                    esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                                    esc_html__( 'Remove this item', 'dragon-glow' ),
                                                    esc_attr( $product_id ),
                                                    esc_attr( $_product->get_sku() )
                                                ),
                                                $cart_item_key
                                            );
                                            ?>
                                        </td>

                                        <td class="product-thumbnail">
                                            <?php
                                            $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

                                            if ( ! $product_permalink ) {
                                                echo $thumbnail;
                                            } else {
                                                printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
                                            }
                                            ?>
                                        </td>

                                        <td class="product-name" data-title="<?php esc_attr_e( 'Product', 'dragon-glow' ); ?>">
                                            <?php
                                            if ( ! $product_permalink ) {
                                                echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
                                            } else {
                                                echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
                                            }

                                            do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

                                            echo wc_get_formatted_cart_item_data( $cart_item );

                                            if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
                                                echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'dragon-glow' ) . '</p>', $product_id ) );
                                            }
                                            ?>
                                        </td>

                                        <td class="product-price" data-title="<?php esc_attr_e( 'Price', 'dragon-glow' ); ?>">
                                            <?php
                                            echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                                            ?>
                                        </td>

                                        <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'dragon-glow' ); ?>">
                                            <?php
                                            if ( $_product->is_sold_individually() ) {
                                                $min_quantity = 1;
                                                $max_quantity = 1;
                                            } else {
                                                $min_quantity = 0;
                                                $max_quantity = $_product->get_max_purchase_quantity();
                                            }

                                            $product_quantity = woocommerce_quantity_input(
                                                array(
                                                    'input_name'   => "cart[{$cart_item_key}][qty]",
                                                    'input_value'  => $cart_item['quantity'],
                                                    'max_value'    => $max_quantity,
                                                    'min_value'    => $min_quantity,
                                                    'product_name' => $_product->get_name(),
                                                ),
                                                $_product,
                                                false
                                            );

                                            echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
                                            ?>
                                        </td>

                                        <td class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'dragon-glow' ); ?>">
                                            <?php
                                            echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>

                            <?php do_action( 'woocommerce_cart_contents' ); ?>

                            <tr>
                                <td colspan="6" class="actions">

                                    <?php if ( wc_coupons_enabled() ) { ?>
                                        <div class="coupon flex gap-4">
                                            <label for="coupon_code" class="sr-only"><?php esc_html_e( 'Coupon:', 'dragon-glow' ); ?></label>
                                            <input type="text" name="coupon_code" class="input-text flex-1 px-4 py-3 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'dragon-glow' ); ?>" />
                                            <button type="submit" class="btn-ghost" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'dragon-glow' ); ?>">
                                                <?php esc_html_e( 'Apply coupon', 'dragon-glow' ); ?>
                                            </button>
                                            <?php do_action( 'woocommerce_cart_coupon' ); ?>
                                        </div>
                                    <?php } ?>

                                    <button type="submit" class="hidden" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'dragon-glow' ); ?>">
                                        <?php esc_html_e( 'Update cart', 'dragon-glow' ); ?>
                                    </button>

                                    <?php do_action( 'woocommerce_cart_actions' ); ?>

                                    <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                                </td>
                            </tr>

                            <?php do_action( 'woocommerce_after_cart_contents' ); ?>
                            <?php do_action( 'woocommerce_after_cart_table' ); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cart Totals -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl shadow-sm p-8 sticky top-24">
                    <h2 class="font-headline text-xl text-primary mb-6">
                        <?php esc_html_e( 'Cart Totals', 'dragon-glow' ); ?>
                    </h2>

                    <?php do_action( 'woocommerce_before_cart_totals' ); ?>

                    <div class="wc-cart_totals_table_form">
                        <table cellspacing="0" class="shop_table shop_table_responsive">

                            <tr class="cart-subtotal">
                                <th><?php esc_html_e( 'Subtotal', 'dragon-glow' ); ?></th>
                                <td data-title="<?php esc_attr_e( 'Subtotal', 'dragon-glow' ); ?>">
                                    <?php wc_cart_totals_subtotal_html(); ?>
                                </td>
                            </tr>

                            <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                                <tr class="cart-discount coupon-<?php echo esc_attr( sanitize_html_class( $code ) ); ?>">
                                    <th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
                                    <td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
                                <?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
                                <?php wc_cart_totals_shipping_html(); ?>
                                <?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
                            <?php endif; ?>

                            <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
                                <tr class="fee">
                                    <th><?php echo esc_html( $fee->name ); ?></th>
                                    <td><?php wc_cart_totals_fee_html( $fee ); ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
                                <?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
                                    <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
                                        <tr class="tax-rate tax-rate-<?php echo sanitize_html_class( $code ); ?>">
                                            <th><?php echo esc_html( $tax->label ); ?></th>
                                            <td data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr class="tax-total">
                                        <th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
                                        <td><?php wc_cart_totals_taxes_total_html(); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

                            <tr class="order-total">
                                <th><?php esc_html_e( 'Total', 'dragon-glow' ); ?></th>
                                <td data-title="<?php esc_attr_e( 'Total', 'dragon-glow' ); ?>">
                                    <?php wc_cart_totals_order_total_html(); ?>
                                </td>
                            </tr>

                            <?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

                        </table>

                        <div class="wc-proceed-to-checkout">
                            <?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
                        </div>
                    </div>

                    <?php do_action( 'woocommerce_after_cart_totals' ); ?>

                    <!-- Free shipping progress -->
                    <?php
                    $threshold = 75;
                    $subtotal = WC()->cart->get_subtotal();
                    $remaining = max( 0, $threshold - $subtotal );
                    if ( $remaining > 0 ) :
                    ?>
                    <div class="mt-6 p-4 bg-secondary-container/30 rounded-xl">
                        <p class="text-sm text-on-secondary-container">
                            <span class="material-symbols-outlined text-sm align-middle">local_shipping</span>
                            <?php
                            printf(
                                esc_html__( 'Add %s more for FREE shipping!', 'dragon-glow' ),
                                '<strong>' . wc_price( $remaining ) . '</strong>'
                            );
                            ?>
                        </p>
                        <div class="mt-2 h-2 bg-surface rounded-full overflow-hidden">
                            <div class="h-full bg-primary rounded-full transition-all" style="width: <?php echo esc_attr( min( 100, ( $subtotal / $threshold ) * 100 ) ); ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
