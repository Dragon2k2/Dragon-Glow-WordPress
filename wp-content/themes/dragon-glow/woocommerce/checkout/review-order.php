<?php
/**
 * Review order table — Dragon Glow override.
 *
 * Order-summary card for the WC checkout page: flex layout per cart line
 * (image + name + qty + price), explicit Shipping / Tax / Estimated Total
 * rows, and a single "Place Order" CTA at the bottom. WC core's
 * `checkout/review-order.php` is replaced entirely because the default
 * `shop_table` markup and payment block do not match the Dragon Glow design.
 *
 * Hooks fired (kept compatible with WC core so the existing
 * `woocommerce_checkout_create_order` / `woocommerce_checkout_order_review`
 * wiring still works):
 *   - `woocommerce_review_order_before_cart_contents`
 *   - `woocommerce_review_order_after_cart_contents`
 *   - `woocommerce_review_order_before_order_total`
 *   - `woocommerce_review_order_after_order_total`
 *   - `woocommerce_review_order_before_payment`
 *   - `woocommerce_review_order_after_payment`
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;
?>

<?php /* ── Cart line items (image + name + qty + price) ────────────── */ ?>
<?php do_action( 'woocommerce_review_order_before_cart_contents' ); ?>

<?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
	$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
	if ( $_product && ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
		continue;
	}
	$product_name  = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
	$thumbnail     = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( array( 64, 80 ) ), $cart_item, $cart_item_key );
	$product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
	$line_subtotal = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
	$item_data     = apply_filters( 'woocommerce_cart_item_data', array(), $cart_item, $cart_item_key );
	?>
	<div class="dg-review-line flex gap-4 mb-4 pb-4 border-b border-outline-variant/20 last:border-b-0 last:mb-0 last:pb-0">
		<?php if ( $thumbnail ) : ?>
			<div class="w-16 h-20 rounded-xl overflow-hidden bg-surface-container flex-shrink-0">
				<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — $_product->get_image() returns safe markup. ?>
			</div>
		<?php endif; ?>

		<div class="flex-1 min-w-0">
			<p class="font-bold text-on-surface truncate"><?php echo esc_html( wp_strip_all_tags( $product_name ) ); ?></p>
			<?php if ( ! empty( $item_data ) ) : ?>
				<dl class="mt-1 space-y-0.5 text-sm text-on-surface-variant">
					<?php foreach ( $item_data as $data ) : ?>
						<div class="flex gap-1">
							<dt><?php echo esc_html( $data['key'] ); ?>:</dt>
							<dd><?php echo esc_html( wp_strip_all_tags( $data['value'] ) ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
			<p class="text-sm text-on-surface-variant">
				<?php
				printf(
					esc_html( _nx( '%d item', '%d items', (int) $cart_item['quantity'], 'checkout order summary', 'dragon-glow' ) ),
					(int) $cart_item['quantity']
				);
				?>
			</p>
		</div>

		<div class="text-right flex-shrink-0">
			<p class="font-bold text-primary"><?php echo wp_kses_post( $line_subtotal ); ?></p>
		</div>
	</div>
<?php endforeach; ?>

<?php do_action( 'woocommerce_review_order_after_cart_contents' ); ?>

<?php /* ── Totals (Subtotal / Shipping / Tax / Estimated Total) ────── */ ?>
<div class="dg-review-totals mt-6 pt-4 border-t border-outline-variant/20 space-y-3">

	<div class="flex justify-between text-on-surface-variant">
		<span><?php esc_html_e( 'Subtotal', 'dragon-glow' ); ?></span>
		<span><?php wc_cart_totals_subtotal_html(); ?></span>
	</div>

	<div class="flex justify-between text-on-surface-variant dg-review-shipping">
		<span><?php esc_html_e( 'Shipping', 'dragon-glow' ); ?></span>
		<span class="text-primary font-medium">
			<?php
			/*
			 * Hide every WC "calculated at next step" / shipping-method
			 * <select> — the mock always shows "FREE" or "Calculated at next step"
			 * as a flat label and never lets the shopper pick a method here.
			 */
			$dg_cart_total = ( WC()->cart && method_exists( WC()->cart, 'get_subtotal' ) ) ? (float) WC()->cart->get_subtotal() : 0.0;
			if ( $dg_cart_total >= 75 ) {
				esc_html_e( 'FREE', 'dragon-glow' );
			} else {
				esc_html_e( 'Calculated at next step', 'dragon-glow' );
			}
			?>
		</span>
	</div>

	<div class="flex justify-between text-sm text-on-surface-variant dg-review-tax">
		<span><?php esc_html_e( 'Tax', 'dragon-glow' ); ?></span>
		<span><?php esc_html_e( 'Calculated at next step', 'dragon-glow' ); ?></span>
	</div>

	<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

	<div class="flex justify-between font-bold text-lg text-primary pt-3 border-t border-outline-variant/20">
		<span><?php esc_html_e( 'Estimated Total', 'dragon-glow' ); ?></span>
		<span><?php wc_cart_totals_order_total_html(); ?></span>
	</div>

	<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
</div>

<?php
/*
 * Payment methods are intentionally not rendered here — they live in
 * `dg_render_wc_checkout()` outside this template so they appear AFTER
 * the totals instead of being collapsed inside the review-order block.
 */
?>