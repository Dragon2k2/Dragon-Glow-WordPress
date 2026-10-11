<?php
/**
 * Dragon Glow — Wishlist Lifecycle
 *
 * Cross-page integration that keeps the per-user wishlist in sync with
 * real-world events. Currently handles a single scenario:
 *
 *   - When a WooCommerce order is created at checkout, the items the
 *     customer just bought are removed from their wishlist. The wishlist
 *     represents "saved for later" — once an item is committed to a real
 *     order, keeping it in the saved list is visual noise.
 *
 * Hook contract
 * ─────────────
 * `woocommerce_checkout_order_processed` fires AFTER the order is fully
 * created and saved to the database, but BEFORE the redirect to the
 * Thank You page. This is the safest point to mutate the wishlist:
 *   - Order persistence is complete → we can read authoritative line items.
 *   - Cart is about to be emptied by `dg_force_empty_cart_after_order_created`
 *     (priority 55) — running at priority 65 keeps us after the empty, so
 *     `get_items()` on the order object is the only source of truth we need.
 *   - Thank You page hasn't rendered yet → any "you saved N items" copy
 *     reflects the new (cleaner) wishlist state on next page load.
 *
 * Idempotency
 * ───────────
 * The hook can fire more than once per request in edge cases (e.g. when
 * the order-received page is reloaded). The helper
 * `dg_wishlist_remove_many()` already diffs out absent IDs, so calling
 * it again is a harmless no-op.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove the just-purchased products from the customer's wishlist.
 *
 * Only fires for logged-in users — guests never have a wishlist, so
 * skipping the work avoids a needless `get_user_meta()` round-trip.
 *
 * @param int      $order_id    Order ID.
 * @param array    $posted_data Posted checkout data.
 * @param WC_Order $order       Order object.
 * @return void
 */
function dg_remove_purchased_items_from_wishlist( int $order_id, $posted_data, $order ): void {
	if ( ! $order_id || ! ( $order instanceof WC_Order ) ) {
		return;
	}

	$user_id = (int) $order->get_user_id();
	if ( $user_id <= 0 ) {
		// Guest checkout — nothing to clean up.
		return;
	}

	// Authoritative source: line items persisted on the order. Reading the
	// cart at this point is unsafe (it may have just been emptied).
	$product_ids = array();
	foreach ( $order->get_items() as $item ) {
		if ( ! method_exists( $item, 'get_product_id' ) ) {
			continue;
		}
		$pid = (int) $item->get_product_id();
		if ( $pid > 0 ) {
			$product_ids[] = $pid;
		}
	}

	if ( empty( $product_ids ) ) {
		return;
	}

	dg_wishlist_remove_many( $user_id, $product_ids );
}
add_action( 'woocommerce_checkout_order_processed', 'dg_remove_purchased_items_from_wishlist', 65, 3 );
