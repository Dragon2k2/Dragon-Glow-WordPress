<?php
/**
 * Dragon Glow — WooCommerce: Cart Selection Checkout
 *
 * Mirrors the Buy Now concern in `buy-now.php`: lets a customer tick
 * checkboxes in the cart page and have ONLY the ticked items appear in
 * the Order Summary (and pay for ONLY those items) at checkout.
 *
 * Strategy:
 *   1. Cart page POSTs the keys the user ticked (AJAX →
 *      `dg_ajax_apply_cart_selection`).
 *   2. That handler stores the *list* of selected cart-item keys in a
 *      dedicated WC session key (`dg_checkout_selection`). We do NOT
 *      stamp any flag onto `$cart->cart_contents` — that turned out
 *      to be the source of a "ghost flag" bug where the in-memory
 *      mutation survived `WC()->session->save_data()` (because WC
 *      re-serializes the cart alongside the rest of the session) and
 *      leaked stale selections into a later request.
 *   3. Checkout time: every filter in this file reads the selection
 *      list on demand via `dg_is_cart_item_selected()` /
 *      `dg_has_active_selection()`. The cart is treated as a pure
 *      projection of WC state with no theme-managed metadata, so
 *      there is no possibility of a stale flag surviving a request
 *      boundary.
 *   4. The order-item metadata (`_dg_selected_for_checkout`) is still
 *      written at order creation, because the order is a long-lived
 *      record that lives outside the session and needs its own audit
 *      trail of which lines the customer paid for.
 *
 * This is intentionally a copy of the Buy Now flow rather than a
 * refactor: the two concerns have the same shape (mark-some /
 * hide-rest / restore-after), but different semantics (one is a
 * feature, one is a UX shortcut) — collapsing them would couple the
 * two and break one of the two happy paths the next time either
 * changes.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Stub filter kept for backward compatibility.
 *
 * The selection list now lives exclusively in
 * `WC()->session['dg_checkout_selection']` and is read on demand via
 * `dg_is_cart_item_selected()` / `dg_has_active_selection()`. No flag
 * is ever written back into the cart, so this filter is a no-op.
 *
 * Previous version stamped a `dg_selected_for_checkout` flag onto
 * each cart item as it was deserialized from the session. That
 * approach was abandoned because of a "ghost flag" bug: a stale flag
 * from a previous request could survive the `save_data()` call inside
 * the AJAX handler and leak into the next page render. The full
 * rationale is recorded in `dg_ajax_apply_cart_selection` in
 * `inc/ajax/cart.php`.
 *
 * @param array  $session_data Cart item data as loaded from the session.
 * @param array  $values       Raw session values.
 * @param string $key          Cart item key (unused).
 * @return array Unmodified $session_data.
 */
function dg_restore_selection_from_session( $session_data, $values, $key = '' ) {
	// No-op: see docblock above.
	return $session_data;
}
add_filter( 'woocommerce_get_cart_item_from_session', 'dg_restore_selection_from_session', 10, 3 );

/**
 * Single source of truth — is a given cart item currently selected for
 * the partial checkout?
 *
 * Reads directly from `WC()->session['dg_checkout_selection']` so the
 * answer is the same for every filter in the request. This replaces
 * the per-item `dg_selected_for_checkout` flag which proved fragile
 * (see commit history for the bug it caused).
 *
 * @param string $cart_item_key Cart item hash.
 * @return bool
 */
function dg_is_cart_item_selected( string $cart_item_key ): bool {
	if ( ! ( dg_is_woocommerce_active() && WC()->session ) ) {
		return false;
	}
	$selection = WC()->session->get( 'dg_checkout_selection' );
	if ( ! is_array( $selection ) || empty( $selection ) ) {
		return false;
	}
	return in_array( $cart_item_key, $selection, true );
}

/**
 * Convenience: does the current cart have ANY item in the selection
 * list? Used by filters that need to short-circuit when no selection
 * is in flight (e.g. hiding non-selected items is a no-op when
 * nothing is selected).
 *
 * @return bool
 */
function dg_has_active_selection(): bool {
	if ( ! ( dg_is_woocommerce_active() && WC()->session ) ) {
		return false;
	}
	$selection = WC()->session->get( 'dg_checkout_selection' );
	return is_array( $selection ) && ! empty( $selection );
}

/**
 * Return the raw session selection list as an array of cart-item keys.
 *
 * Used by the JS layer (via wp_localize_script) so the client can
 * decide whether the DOM checkboxes on /cart/ load are stale — e.g.
 * the browser restored the page from its back/forward cache and the
 * checkboxes still reflect a previous visit. An empty array means
 * "no session selection", which is the expected state when the user
 * has not ticked anything in this visit.
 *
 * Filtered so a future extension (e.g. multi-vendor) can swap in a
 * different backing store without touching the JS.
 *
 * @return array<int, string>
 */
function dg_get_session_selection_keys(): array {
	if ( ! ( dg_is_woocommerce_active() && WC()->session ) ) {
		return array();
	}
	$selection = WC()->session->get( 'dg_checkout_selection' );
	if ( ! is_array( $selection ) ) {
		return array();
	}
	/**
	 * Filter the session selection list read by the JS layer.
	 *
	 * @param array $selection List of cart-item keys the server
	 *                        currently considers "selected for
	 *                        checkout". Empty means "no selection".
	 */
	return (array) apply_filters( 'dg_session_selection_keys', array_values( $selection ) );
}

/**
 * Clear the session selection and persist immediately.
 *
 * Centralised so every "selection should be reset" path (cart load,
 * AJAX clear, post-order cleanup) calls the exact same session write
 * + save_data() guard. Without save_data() the next request can race
 * the deferred save and see a stale selection.
 *
 * Idempotent: safe to call when the session already has no selection.
 *
 * @return void
 */
function dg_clear_session_selection(): void {
	if ( ! ( dg_is_woocommerce_active() && WC()->session ) ) {
		return;
	}
	$current = WC()->session->get( 'dg_checkout_selection' );
	if ( ! is_array( $current ) || empty( $current ) ) {
		return; // already clear — skip the write/save churn
	}
	WC()->session->set( 'dg_checkout_selection', null );
	if ( method_exists( WC()->session, 'save_data' ) ) {
		WC()->session->save_data();
	}
}

/**
 * Drop the session selection on a fresh /cart/ load.
 *
 * The cart page is a review surface, not a continuation of the
 * previous visit's selection intent. If the user lands on /cart/ via
 * any route (cold nav, back/forward, deep link, restored tab) we
 * treat the visit as "start over" and forget the previous selection.
 * Ticking a checkbox on /cart/ will re-create a fresh selection; if
 * the user wants nothing selected they simply leave the page.
 *
 * The previous behaviour was to keep the selection across cold loads
 * and have the client re-tick the DOM, but that violated the user's
 * mental model ("I didn't tick anything this visit, why is it
 * pre-selected?"). Server is the source of truth, so the reset must
 * happen here — clearing only on the client races with the reconcile
 * pass that fires immediately on load.
 *
 * Runs at `template_redirect` priority 1 so the cleared session is
 * already in place by the time `body_class` and the JS localize pass
 * read it (both fire after template_redirect).
 *
 * @return void
 */
function dg_clear_stale_selection_on_cart_load(): void {
	if ( ! is_cart() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}
	dg_clear_session_selection();
}
add_action( 'template_redirect', 'dg_clear_stale_selection_on_cart_load', 1 );

/**
 * Force WooCommerce to calculate totals BEFORE checkout page renders.
 *
 * Selection Checkout mirrors Buy Now: set prices to 0 for non-selected
 * items so the checkout totals/order line items only reflect what the
 * customer actually picked. Without this the first paint of the checkout
 * page renders with cart totals that include unchecked items.
 *
 * @return void
 */
function dg_force_calculate_totals_on_checkout_selection(): void {
	if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}

	$cart = WC()->cart;
	if ( ! $cart || $cart->is_empty() ) {
		return;
	}

	if ( ! dg_has_active_selection() ) {
		return;
	}

	$cart->calculate_totals();
}
add_action( 'template_redirect', 'dg_force_calculate_totals_on_checkout_selection', 5 );

/**
 * Set the price of non-selected items to 0 during checkout totals.
 *
 * Mirrors `dg_exclude_non_buy_now_from_checkout_totals` exactly — the
 * static `$processing` guard avoids the infinite loop that occurs when
 * `calculate_totals()` fires the same hook recursively.
 *
 * @param WC_Cart $cart Cart object.
 * @return void
 */
function dg_exclude_non_selected_from_checkout_totals( $cart ): void {
	if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}

	static $processing = false;
	if ( $processing ) {
		return;
	}
	$processing = true;

	if ( ! dg_has_active_selection() ) {
		$processing = false;
		return;
	}

	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		if ( ! dg_is_cart_item_selected( $cart_item_key ) ) {
			$product = $cart_item['data'];
			if ( $product && method_exists( $product, 'set_price' ) ) {
				$product->set_price( 0 );
			}
		}
	}

	$processing = false;
}
add_action( 'woocommerce_before_calculate_totals', 'dg_exclude_non_selected_from_checkout_totals', 5, 1 );

/**
 * Restore original prices after checkout totals calculation.
 *
 * Without this the price=0 set by `dg_exclude_non_selected_from_checkout_totals`
 * could persist on the product object after the user navigates back to the
 * cart, briefly showing $0 line subtotals before the next totals refresh
 * runs. Same trick as Buy Now: reload from `wc_get_product()`.
 *
 * @param WC_Cart $cart Cart object.
 * @return void
 */
function dg_restore_selection_prices_after_totals( $cart ): void {
	if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}

	if ( ! dg_has_active_selection() ) {
		return;
	}

	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		if ( dg_is_cart_item_selected( $cart_item_key ) ) {
			continue;
		}
		$product = $cart_item['data'];
		if ( ! $product ) {
			continue;
		}
		$original_product = wc_get_product( $cart_item['product_id'] );
		if ( $original_product ) {
			$product->set_price( $original_product->get_price() );
		}
	}
}
add_action( 'woocommerce_after_calculate_totals', 'dg_restore_selection_prices_after_totals', 999, 1 );

/**
 * Force WC to refresh the checkout review-order fragment when a selection
 * is in flight. Same reasoning as Buy Now — WC caches the rendered HTML
 * fragment, and the price-zeroing above would otherwise be invisible on
 * the first paint.
 *
 * @param array $fragments Checkout fragments to refresh.
 * @return array
 */
function dg_force_refresh_selection_fragments( array $fragments ): array {
	if ( ! dg_has_active_selection() ) {
		return $fragments;
	}

	$fragments['dg_selection_refresh'] = time();
	return $fragments;
}
add_filter( 'woocommerce_update_order_review_fragments', 'dg_force_refresh_selection_fragments', 10, 1 );

/**
 * Persist the selection flag into the order line item meta so post-order
 * admin views can still see which items came from a selection checkout
 * (useful for analytics + troubleshooting).
 *
 * @param WC_Order_Item_Product $item          Order item.
 * @param string                $cart_item_key Cart item key.
 * @param array                 $values        Cart item values.
 * @param WC_Order              $order         Order object.
 * @return WC_Order_Item_Product
 */
function dg_store_selection_meta_in_order_item( $item, $cart_item_key, $values, $order ) {
	if ( dg_is_cart_item_selected( $cart_item_key ) ) {
		$item->add_meta_data( '_dg_selected_for_checkout', 'yes', true );
	}
	return $item;
}
add_filter( 'woocommerce_checkout_create_order_line_item', 'dg_store_selection_meta_in_order_item', 10, 4 );

/**
 * After the order is saved to the DB, remove any non-selected items
 * from the order so the customer is only charged for the items they
 * ticked in the cart page.
 *
 * Mirrors `dg_cleanup_buy_now_order_items` 1:1.
 *
 * @param int      $order_id Order ID.
 * @param WC_Order $order    Order object.
 * @return void
 */
function dg_cleanup_selection_order_items( int $order_id, $order ): void {
	if ( ! $order_id || ! $order ) {
		return;
	}

	$has_selection = false;
	foreach ( $order->get_items() as $item ) {
		if ( $item->get_meta( '_dg_selected_for_checkout', true ) === 'yes' ) {
			$has_selection = true;
			break;
		}
	}

	if ( ! $has_selection ) {
		return;
	}

	$order->save();

	$items_to_remove = array();
	foreach ( $order->get_items() as $item_id => $item ) {
		$is_selected = ( $item->get_meta( '_dg_selected_for_checkout', true ) === 'yes' );
		if ( ! $is_selected ) {
			$items_to_remove[] = $item_id;
		}
	}

	if ( ! empty( $items_to_remove ) ) {
		foreach ( $items_to_remove as $item_id ) {
			$order->remove_item( $item_id );
		}
		$order->calculate_totals();
		$order->save();
	}
}
add_action( 'woocommerce_new_order', 'dg_cleanup_selection_order_items', 20, 2 );

/**
 * Hide non-selected items from the checkout review table.
 *
 * Same shape as the Buy Now visibility filter — return false for any
 * cart-item that isn't flagged, and the WC review-order loop skips it.
 *
 * @param bool   $visible        Whether item is visible.
 * @param array  $cart_item      Cart item data.
 * @param string $cart_item_key Cart item key.
 * @return bool
 */
function dg_hide_non_selected_from_checkout_review( $visible, $cart_item, $cart_item_key ) {
	if ( ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return $visible;
	}

	if ( ! dg_has_active_selection() ) {
		return $visible;
	}

	if ( ! dg_is_cart_item_selected( $cart_item_key ) ) {
		return false;
	}

	return $visible;
}
add_filter( 'woocommerce_cart_item_visible', 'dg_hide_non_selected_from_checkout_review', 10, 3 );

/**
 * Clear every `dg_selected_for_checkout` flag from the cart once the
 * order has been processed.
 *
 * Why a dedicated hook:
 *   - Without it, the flags would persist on the session and the next
 *     visit to /checkout/ would silently re-apply the same isolation.
 *   - We deliberately do NOT clear on thank-you render: that runs after
 *     the header cart count is read, and clearing earlier risks the
 *     header showing the wrong count for a beat.
 *   - We hook on `woocommerce_checkout_order_processed` (priority 60,
 *     after the preserve logic) so order persistence is complete before
 *     we mutate the cart.
 *
 * @return void
 */
function dg_clear_selection_flags_after_order(): void {
	if ( ! WC()->cart ) {
		return;
	}

	if ( ! dg_has_active_selection() ) {
		return;
	}

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		if ( ! dg_is_cart_item_selected( $cart_item_key ) ) {
			WC()->cart->remove_cart_item( $cart_item_key );
		}
	}
}
add_action( 'woocommerce_checkout_order_processed', 'dg_clear_selection_flags_after_order', 60 );

/**
 * Inject `.dg-has-selection` body class so CSS can toggle the two CTAs.
 *
 * Registered unconditionally (no template_redirect gate) because
 * `body_class` runs late in the request — by the time WP asks for body
 * classes inside `get_header()`, `WC()->cart` has been populated.
 * The small foreach cost only fires when the cart has items and the
 * presence-check short-circuits on the first flag, which keeps this
 * negligible on every WC page load.
 *
 * @param array $classes Body classes.
 * @return array
 */
function dg_add_selection_body_class( array $classes ): array {
	if ( ! ( dg_is_woocommerce_active() && WC()->cart && WC()->session ) ) {
		return $classes;
	}
	if ( dg_has_active_selection() ) {
		$classes[] = 'dg-has-selection';
	}
	return $classes;
}
add_filter( 'body_class', 'dg_add_selection_body_class' );
