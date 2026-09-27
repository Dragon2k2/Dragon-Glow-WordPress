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

// DEBUG: Confirm this template file is loaded.
error_log( '[DG Buy Now DEBUG] review-order.php loaded — file=' . __FILE__ );
?>

<?php /* ── Cart line items (image + name + qty + price) ────────────── */ ?>
<?php do_action( 'woocommerce_review_order_before_cart_contents' ); ?>

<?php
// Check if Buy Now mode is active.
$dg_has_buy_now = false;
foreach ( WC()->cart->get_cart() as $dg_check_item ) {
	if ( ! empty( $dg_check_item['dg_is_buy_now'] ) ) {
		$dg_has_buy_now = true;
		break;
	}
}

// DEBUG: Log what template will render.
$debug_cart       = WC()->cart;
$debug_cart_count = $debug_cart->get_cart_contents_count();
$debug_subtotal   = $debug_cart->get_subtotal();
$debug_total      = $debug_cart->get_total( 'edit' );

error_log( sprintf(
	'[DG Buy Now DEBUG] Template render — cart_count=%d, subtotal=%.2f, total=%s, buy_now_mode=%s',
	$debug_cart_count,
	$debug_subtotal,
	$debug_total,
	$dg_has_buy_now ? 'YES' : 'NO'
) );

foreach ( $debug_cart->get_cart() as $debug_key => $debug_item ) {
	$debug_product   = $debug_item['data'];
	$debug_price     = $debug_product->get_price();
	$debug_qty       = $debug_item['quantity'];
	$debug_is_buy_now = ! empty( $debug_item['dg_is_buy_now'] ) ? 'yes' : 'no';
	$debug_subtotal_item = (float) $debug_price * (int) $debug_qty;

	error_log( sprintf(
		'[DG Buy Now DEBUG] Template item — product_id=%d, price=%.2f, qty=%d, is_buy_now=%s, subtotal=%.2f',
		$debug_product->get_id(),
		$debug_price,
		$debug_qty,
		$debug_is_buy_now,
		$debug_subtotal_item
	) );
}
?>

<?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
	$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
	if ( $_product && ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
		continue;
	}
	
	// CRITICAL: Skip non-Buy-Now items when Buy Now mode is active.
	if ( $dg_has_buy_now && empty( $cart_item['dg_is_buy_now'] ) ) {
		error_log( '[DG Buy Now DEBUG] Template SKIPPED regular item — product_id=' . $cart_item['product_id'] );
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
	<?php
	// DEBUG: Log actual cart state when rendering totals.
	if ( WC()->cart ) {
		$cart_subtotal = WC()->cart->get_subtotal();
		$cart_total    = WC()->cart->get_total( 'edit' );
		$cart_count    = WC()->cart->get_cart_contents_count();
		error_log( '[DG Buy Now DEBUG] Template render — cart_count=' . $cart_count . ', subtotal=' . $cart_subtotal . ', total=' . $cart_total );
		
		// Log each cart item with prices.
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			$is_buy_now = ! empty( $cart_item['dg_is_buy_now'] ) ? 'YES' : 'NO';
			error_log( '[DG Buy Now DEBUG] Template item — product_id=' . $cart_item['product_id'] . ', price=' . $product->get_price() . ', qty=' . $cart_item['quantity'] . ', is_buy_now=' . $is_buy_now . ', cart_key=' . $cart_item_key );
		}
	}
	?>

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
		<span><?php
			// Custom total calculation for Buy Now checkout.
			// Standard wc_cart_totals_order_total_html() uses cached cart total,
			// which includes items we set to price=0 in woocommerce_before_calculate_totals.
			// We need to output the total AFTER our price manipulation.
			
			$cart = WC()->cart;
			$has_buy_now = false;
			
			// Check if Buy Now items exist.
			foreach ( $cart->get_cart() as $item ) {
				if ( ! empty( $item['dg_is_buy_now'] ) ) {
					$has_buy_now = true;
					break;
				}
			}
			
			if ( $has_buy_now ) {
				// Calculate total from Buy Now items only.
				$buy_now_total = 0;
				foreach ( $cart->get_cart() as $cart_item ) {
					if ( ! empty( $cart_item['dg_is_buy_now'] ) ) {
						$product = $cart_item['data'];
						$buy_now_total += (float) $product->get_price() * (int) $cart_item['quantity'];
					}
				}
				
				error_log( sprintf(
					'[DG Buy Now DEBUG] Template total override — calculated_total=%.2f, wc_cart_total=%s',
					$buy_now_total,
					$cart->get_total( 'edit' )
				) );
				
				// Format and display.
				echo wp_kses_post( wc_price( $buy_now_total ) );
			} else {
				// Standard checkout — use WC default.
				wc_cart_totals_order_total_html();
			}
		?></span>
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