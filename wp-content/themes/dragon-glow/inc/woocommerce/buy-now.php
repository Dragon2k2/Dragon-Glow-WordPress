<?php
/**
 * Dragon Glow — WooCommerce: Buy Now Cart Isolation
 *
 * "Buy Now" lets a customer check out a single product immediately,
 * bypassing the rest of their cart, without losing those other items.
 * Strategy: during checkout, zero out the price of non-Buy-Now items (so
 * totals/order only reflect the Buy Now item), strip them from the created
 * order, then restore them to the cart after the order is placed.
 *
 * Split out of `checkout.php` — distinct concern (cart-item visibility +
 * session-based restore) from the core checkout field/template logic.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Force WooCommerce to calculate totals BEFORE checkout page renders.
 *
 * Without this, the first checkout page load renders the template BEFORE
 * our `woocommerce_before_calculate_totals` hook runs, showing wrong prices.
 * This ensures totals are calculated (triggering our hook) before any output.
 */
function dg_force_calculate_totals_on_checkout(): void {
	if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}

	$cart = WC()->cart;
	if ( ! $cart || $cart->is_empty() ) {
		return;
	}

	// Check if Buy Now items exist.
	$has_buy_now = false;
	foreach ( $cart->get_cart() as $item ) {
		if ( ! empty( $item['dg_is_buy_now'] ) ) {
			$has_buy_now = true;
			break;
		}
	}

	if ( ! $has_buy_now ) {
		return;
	}

	// Force calculate totals — this triggers woocommerce_before_calculate_totals
	// hook, which sets non-Buy-Now item prices to 0.
	$cart->calculate_totals();
}
add_action( 'template_redirect', 'dg_force_calculate_totals_on_checkout', 5 );

/**
 * Exclude non-Buy-Now items from totals calculation on checkout.
 *
 * Sets price to 0 for non-Buy-Now items ONLY during checkout calculation.
 * This excludes them from totals without actually removing from cart.
 *
 * IMPORTANT: Do NOT call calculate_totals() here — WooCommerce is already
 * calculating when this hook fires. Just set prices and let WC do its job.
 *
 * @param WC_Cart $cart Cart object.
 * @return void
 */
function dg_exclude_non_buy_now_from_checkout_totals( $cart ): void {
	// Only run on checkout page, not cart or other pages.
	if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}

	// Prevent infinite loop with static flag.
	static $processing = false;
	if ( $processing ) {
		return;
	}
	$processing = true;

	// Check if any Buy Now items exist.
	$has_buy_now = false;
	foreach ( $cart->get_cart() as $item ) {
		if ( ! empty( $item['dg_is_buy_now'] ) ) {
			$has_buy_now = true;
			break;
		}
	}

	// If no Buy Now items, calculate normally.
	if ( ! $has_buy_now ) {
		$processing = false;
		return;
	}

	// Set price to 0 for non-Buy-Now items (excludes from totals).
	$excluded_count = 0;
	$buy_now_count  = 0;

	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		$product = $cart_item['data'];

		if ( empty( $cart_item['dg_is_buy_now'] ) ) {
			$product->set_price( 0 );
			$excluded_count++;
		} else {
			$buy_now_count++;
		}
	}

	$processing = false;
}
add_action( 'woocommerce_before_calculate_totals', 'dg_exclude_non_buy_now_from_checkout_totals', 5, 1 );

/**
 * Log cart totals after WooCommerce finishes calculation.
 *
 * This runs AFTER WC calculates totals with our price=0 modifications.
 * Used for debugging to verify totals are correct.
 *
 * @param WC_Cart $cart Cart object.
 * @return void
 */
function dg_log_checkout_totals_after_calculation( $cart ): void {
	// Only run on checkout page.
	if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}

	// Check if any Buy Now items exist.
	$has_buy_now = false;
	foreach ( $cart->get_cart() as $item ) {
		if ( ! empty( $item['dg_is_buy_now'] ) ) {
			$has_buy_now = true;
			break;
		}
	}

	if ( ! $has_buy_now ) {
		return;
	}
}
add_action( 'woocommerce_after_calculate_totals', 'dg_log_checkout_totals_after_calculation', 10, 1 );

/**
 * Restore original prices after checkout totals calculation.
 *
 * This prevents price=0 from persisting when user navigates back to cart.
 *
 * @param WC_Cart $cart Cart object.
 * @return void
 */
function dg_restore_prices_after_checkout_totals( $cart ): void {
	// Only run on checkout page.
	if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}

	// Check if any Buy Now items exist.
	$has_buy_now = false;
	foreach ( $cart->get_cart() as $item ) {
		if ( ! empty( $item['dg_is_buy_now'] ) ) {
			$has_buy_now = true;
			break;
		}
	}

	if ( ! $has_buy_now ) {
		return;
	}

	// Get product objects directly and ensure prices are set correctly.
	$restored_count = 0;
	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		if ( empty( $cart_item['dg_is_buy_now'] ) ) {
			$product = $cart_item['data'];
			// Force reload original price from product.
			$original_product = wc_get_product( $cart_item['product_id'] );
			if ( $original_product ) {
				$original_price = $original_product->get_price();
				$product->set_price( $original_price );
				$restored_count++;
			}
		}
	}
}
add_action( 'woocommerce_after_calculate_totals', 'dg_restore_prices_after_checkout_totals', 999, 1 );

/**
 * Force WooCommerce to refresh checkout fragments when Buy Now items exist.
 *
 * WooCommerce caches the review-order HTML fragment — this forces refresh
 * so our price changes are reflected immediately.
 *
 * @param array $fragments Checkout fragments to refresh.
 * @return array
 */
function dg_force_refresh_checkout_fragments( array $fragments ): array {
	// Check if any Buy Now items exist.
	$has_buy_now = false;
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( ! empty( $item['dg_is_buy_now'] ) ) {
			$has_buy_now = true;
			break;
		}
	}

	if ( ! $has_buy_now ) {
		return $fragments;
	}

	// Force refresh by adding a unique timestamp parameter.
	// This prevents WC from using cached fragment HTML.
	$fragments['dg_buy_now_refresh'] = time();

	return $fragments;
}
add_filter( 'woocommerce_update_order_review_fragments', 'dg_force_refresh_checkout_fragments', 10, 1 );

/**
 * Store Buy Now flag in order item meta for identification.
 *
 * @param WC_Order_Item_Product $item          Order item.
 * @param string                $cart_item_key Cart item key.
 * @param array                 $values        Cart item values.
 * @param WC_Order              $order         Order object.
 * @return WC_Order_Item_Product
 */
function dg_store_buy_now_meta_in_order_item( $item, $cart_item_key, $values, $order ) {
	if ( ! empty( $values['dg_is_buy_now'] ) ) {
		$item->add_meta_data( '_dg_is_buy_now', 'yes', true );
	}
	
	return $item;
}
add_filter( 'woocommerce_checkout_create_order_line_item', 'dg_store_buy_now_meta_in_order_item', 10, 4 );

/**
 * Remove non-Buy-Now items immediately after order creation.
 *
 * This runs AFTER order is saved to database, so we can reliably delete items.
 *
 * @param int      $order_id Order ID.
 * @param WC_Order $order    Order object.
 */
function dg_cleanup_buy_now_order_items( int $order_id, $order ): void {
	if ( ! $order_id || ! $order ) {
		return;
	}

	// Check if ANY Buy Now items exist.
	$has_buy_now = false;
	foreach ( $order->get_items() as $item ) {
		if ( $item->get_meta( '_dg_is_buy_now', true ) === 'yes' ) {
			$has_buy_now = true;
			break;
		}
	}

	// If no Buy Now items, nothing to do.
	if ( ! $has_buy_now ) {
		return;
	}

	// CRITICAL: Save order first to ensure items are persisted before manipulation.
	// Without this, get_items() may return stale data and remove_item() won't work.
	$order->save();

	// Collect item IDs to remove (do NOT remove while iterating).
	$items_to_remove = array();
	$items_to_keep   = array();

	foreach ( $order->get_items() as $item_id => $item ) {
		$is_buy_now = ( $item->get_meta( '_dg_is_buy_now', true ) === 'yes' );

		if ( ! $is_buy_now ) {
			$items_to_remove[] = $item_id;
		} else {
			$items_to_keep[] = $item_id;
		}
	}

	// Remove non-Buy-Now items.
	if ( ! empty( $items_to_remove ) ) {
		foreach ( $items_to_remove as $item_id ) {
			$order->remove_item( $item_id );
		}

		$order->calculate_totals();
		$order->save();
	}
}
add_action( 'woocommerce_new_order', 'dg_cleanup_buy_now_order_items', 20, 2 );

/**
 * Hide non-Buy-Now items from checkout review table.
 *
 * @param bool   $visible        Whether item is visible.
 * @param array  $cart_item      Cart item data.
 * @param string $cart_item_key Cart item key.
 * @return bool False to hide, true to show.
 */
function dg_hide_non_buy_now_from_checkout_review( $visible, $cart_item, $cart_item_key ) {
	// Only run on checkout page.
	if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return $visible;
	}

	// Check if ANY Buy Now items exist.
	$has_buy_now = false;
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( ! empty( $item['dg_is_buy_now'] ) ) {
			$has_buy_now = true;
			break;
		}
	}

	// If no Buy Now items, show all items normally.
	if ( ! $has_buy_now ) {
		return $visible;
	}

	// Hide non-Buy-Now items.
	if ( empty( $cart_item['dg_is_buy_now'] ) ) {
		return false;
	}

	return $visible;
}
add_filter( 'woocommerce_cart_item_visible', 'dg_hide_non_buy_now_from_checkout_review', 10, 3 );

/**
 * Preserve regular cart items before WooCommerce empties cart after order.
 *
 * WooCommerce default: empties entire cart after successful order.
 * Buy Now behavior: keep regular items, only remove Buy Now items.
 *
 * Strategy: Store non-Buy-Now items immediately after order is created,
 * before WC has a chance to empty cart.
 *
 * @param int      $order_id Order ID.
 * @param array    $posted_data Posted checkout data.
 * @param WC_Order $order Order object.
 * @return void
 */
function dg_preserve_regular_items_after_order_created( int $order_id, $posted_data, $order ): void {
	if ( ! $order_id || ! WC()->cart ) {
		return;
	}

	// Check if Buy Now mode is active.
	$has_buy_now = false;
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		if ( ! empty( $cart_item['dg_is_buy_now'] ) ) {
			$has_buy_now = true;
			break;
		}
	}

	if ( ! $has_buy_now ) {
		// No Buy Now items — let WC handle cart normally.
		return;
	}

	// Store non-Buy-Now items in session to restore later.
	$regular_items = array();
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		if ( empty( $cart_item['dg_is_buy_now'] ) ) {
			$regular_items[ $cart_item_key ] = $cart_item;
		}
	}

	if ( ! empty( $regular_items ) ) {
		WC()->session->set( 'dg_preserved_cart_items', $regular_items );
	}
}
add_action( 'woocommerce_checkout_order_processed', 'dg_preserve_regular_items_after_order_created', 50, 3 );

/**
 * Force empty cart after order creation.
 *
 * WooCommerce does NOT always empty cart automatically — it depends on
 * payment gateway and order status. For example:
 * - COD (Cash on Delivery): order status = 'on-hold', cart NOT emptied
 * - BACS (Bank Transfer): order status = 'on-hold', cart NOT emptied
 * - Card payments: order status = 'processing', cart emptied
 *
 * This ensures consistent behavior across ALL checkout scenarios:
 *
 * 1. Buy Now checkout → cart is emptied, regular items will be restored
 * 2. Normal checkout → cart is emptied (standard e-commerce behavior)
 *
 * The hook `woocommerce_checkout_order_processed` fires AFTER the order is
 * fully created and saved to database, but BEFORE redirect to Thank You page.
 * This is the safest point to force empty — early enough that Thank You page
 * sees the clean state, late enough that preserve logic has already captured
 * regular items.
 *
 * @param int      $order_id    Order ID.
 * @param array    $posted_data Posted checkout data.
 * @param WC_Order $order       Order object.
 * @return void
 */
function dg_force_empty_cart_after_order_created( int $order_id, $posted_data, $order ): void {
	if ( ! $order_id || ! WC()->cart ) {
		return;
	}

	// Force empty cart to ensure clean state.
	// This runs AFTER preserve logic (priority 50) so any regular items
	// that need to be restored are already safely stored in session.
	if ( ! WC()->cart->is_empty() ) {
		WC()->cart->empty_cart();
	}
}
add_action( 'woocommerce_checkout_order_processed', 'dg_force_empty_cart_after_order_created', 55, 3 );

/**
 * Restore regular items from session into the cart.
 *
 * Runs twice on the Thank You endpoint — once early (on the `wp` action)
 * to make sure the global header renders the correct cart count, and
 * once later (`woocommerce_before_thankyou`) to cover any other consumer
 * of that legacy hook. Both entries are guarded by `! is_empty()` so
 * the second call is a harmless no-op; the early one is what makes the
 * header badge reflect the preserved items in the very first paint.
 *
 * Why two hooks?
 * ──────────────
 * `template-parts/global/header-nav.php` reads
 * `WC()->cart->get_cart_contents_count()` *while* `get_header()` is
 * executing, which happens **before** `woocommerce_before_thankyou`
 * fires (the template's `wc_get_template()` call dispatches the
 * action later). Without the early hook, the header always sees the
 * emptied cart and renders `<span class="hidden">0</span>`. The badge
 * then corrects itself ~2 s later when
 * `DGCart.refreshCount()` POSTs back to the server. This file is here
 * to eliminate that 2 s window.
 *
 * Why `wp_head` priority 5 for the early hook?
 *   • `wp_head` fires *after* `wp` (the legacy guard documented in
 *     `template-wc-thankyou.php` and in `class-dg-thankyou.php` as the
 *     same WC issue #63966 timing trap) has fully resolved the request,
 *     so `is_order_received_page()` is reliable here.
 *   • Priority 5 beats the data-island printer (priority 6 in
 *     `inc/enqueue.php`) so the JSON blob already reflects the
 *     restored count when it is printed to the response.
 *   • Priority 5 still fires before the enqueue hooks at `wp_head`
 *     priority 10, before CSS / JS links, before `header.php` paints
 *     the HTML.
 *
 * @param int $order_id WooCommerce order ID (0 when not on Thank You).
 * @return void
 */
function dg_restore_regular_items( int $order_id ): void {
	if ( ! $order_id || ! WC()->cart || ! WC()->session ) {
		return;
	}

	$preserved_items = WC()->session->get( 'dg_preserved_cart_items' );
	if ( empty( $preserved_items ) || ! is_array( $preserved_items ) ) {
		return;
	}

	// CRITICAL: Only restore if cart is actually empty.
	// This prevents double-restore when Thank You page is reloaded, and
	// makes the second hook registration below a safe no-op.
	if ( ! WC()->cart->is_empty() ) {
		return;
	}

	// Restore each regular item to cart.
	$restored_count = 0;
	foreach ( $preserved_items as $cart_item_key => $cart_item ) {
		$product_id   = $cart_item['product_id'];
		$variation_id = $cart_item['variation_id'] ?? 0;
		$quantity     = $cart_item['quantity'];
		$variation    = $cart_item['variation'] ?? array();

		// Preserve custom cart item data (size, etc).
		$cart_item_data = array();
		if ( ! empty( $cart_item['dg_size'] ) ) {
			$cart_item_data['dg_size'] = $cart_item['dg_size'];
		}

		WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );
		$restored_count++;
	}

	// NOTE: Do NOT clear session here. Keep preserved items until user
	// views the cart page. This allows multiple Thank You page reloads
	// without losing data.
}

/**
 * Early bridge from the WP head action to the restore function.
 *
 * Hooked at `wp_head` priority 5 — chosen deliberately:
 *
 *   • WP runs `wp_head` *after* `wp` (the legacy filter guard) has fully
 *     resolved the request — so `is_order_received_page()` is reliable
 *     here (the same guard is used in `template-wc-thankyou.php` and in
 *     WC's own `template_include` filter to avoid the same race).
 *
 *   • Priority 5 beats the built-in `dg_print_cart_data_island` (priority
 *     1 in this file). We want `__DG_CART__` to read the count *after*
 *     restore runs so the JSON blob already contains the right number
 *     on the very first byte the browser sees.
 *
 *   • Priority 5 still fires before any CSS / JS enqueue hooks at
 *     `wp_head` priority 10, before `header.php` paints the HTML.
 *
 *   • If we hook earlier (`wp` priority 1) `is_order_received_page()`
 *     returns false — same WC timing trap documented in WC issue
 *     #63966 and in `class-dg-thankyou.php`.
 *
 * @return void
 */
function dg_restore_regular_items_on_wp_action(): void {
	// Only run on the WC order-received endpoint. Bail on every other
	// page (shop, cart, etc.) — the cart is authoritative there.
	if ( ! function_exists( 'is_order_received_page' ) || ! is_order_received_page() ) {
		return;
	}

	$dg_order_id = absint( get_query_var( 'order-received' ) );
	if ( $dg_order_id <= 0 ) {
		// No order ID yet — the URL might not match the WC endpoint
		// structure (some gateways use a different shape). Defer to the
		// legacy `woocommerce_before_thankyou` hook which receives
		// `$order_id` as an argument.
		return;
	}

	dg_restore_regular_items( $dg_order_id );
}
add_action( 'wp_head', 'dg_restore_regular_items_on_wp_action', 5 );

/**
 * Legacy entry point kept for parity with the original behaviour.
 *
 * Keeps a single source of truth by forwarding to the shared
 * `dg_restore_regular_items()` helper. The cart-emptiness guard inside
 * the helper makes this a no-op when the early `wp` hook already
 * populated the cart — so it costs nothing to keep both callbacks.
 */
function dg_restore_regular_items_on_thankyou_page( int $order_id ): void {
	dg_restore_regular_items( $order_id );
}
add_action( 'woocommerce_before_thankyou', 'dg_restore_regular_items_on_thankyou_page', 10, 1 );

/**
 * Clear preserved cart items session when user views cart page.
 *
 * This runs after user navigates to cart page (meaning restore was successful
 * and user can see their items). Safe cleanup point.
 *
 * @return void
 */
function dg_clear_preserved_items_on_cart_page(): void {
	if ( ! is_cart() || ! WC()->session ) {
		return;
	}

	$preserved_items = WC()->session->get( 'dg_preserved_cart_items' );
	if ( ! empty( $preserved_items ) ) {
		WC()->session->set( 'dg_preserved_cart_items', null );
	}
}
add_action( 'template_redirect', 'dg_clear_preserved_items_on_cart_page', 10 );
