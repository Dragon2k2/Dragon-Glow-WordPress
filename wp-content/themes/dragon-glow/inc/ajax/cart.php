<?php
/**
 * Dragon Glow — AJAX: Cart
 *
 * Cart operations exposed over AJAX: add, buy-now, remove, update quantity,
 * count, and cart identifier lookups. Mode-aware helpers live in cart-functions.php.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * AJAX: Quick add to cart.
 */
function dg_ajax_add_to_cart(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	$product_id          = absint( $_POST['product_id'] ?? 0 );
	$slug                = sanitize_text_field( $_POST['slug'] ?? '' );
	$size                = sanitize_text_field( $_POST['size'] ?? '' );
	$quantity            = absint( $_POST['quantity'] ?? 1 );
	$variation_id        = absint( $_POST['variation_id'] ?? 0 );
	$redirect_after      = ! empty( $_POST['redirect_after_add'] );

	// Decode variation_attributes JSON if present (used by cart undo to
	// re-add the exact configuration that was removed).
	$variation_attributes = array();
	if ( ! empty( $_POST['variation_attributes'] ) ) {
		$decoded = json_decode( wp_unslash( $_POST['variation_attributes'] ), true );
		if ( is_array( $decoded ) ) {
			$variation_attributes = array_map( 'sanitize_text_field', $decoded );
		}
	}

	$result = dg_add_to_cart_silently( array(
		'product_id'           => $product_id,
		'slug'                 => $slug,
		'size'                 => $size,
		'quantity'             => $quantity,
		'variation_id'         => $variation_id,
		'variation_attributes' => $variation_attributes,
	) );

	if ( $result['success'] ) {
		$cart_item_key = '';
		if ( dg_is_woocommerce_active() && WC()->cart ) {
			foreach ( WC()->cart->get_cart() as $key => $item ) {
				if ( (int) $item['product_id'] === $product_id ) {
					$cart_item_key = $key;
					break;
				}
			}
		}

		$payload = array(
			'message'       => __( 'Added to bag!', 'dragon-glow' ),
			'redirect'      => $result['redirect'] ?? dg_get_cart_url(),
			'cart_item_key' => $cart_item_key,
		);

		if ( $redirect_after ) {
			$payload['redirect'] = dg_get_checkout_url();
		}

		wp_send_json_success( $payload );
	} else {
		wp_send_json_error( array( 'message' => $result['message'] ) );
	}
}
add_action( 'wp_ajax_dg_ajax_add_to_cart', 'dg_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_dg_ajax_add_to_cart', 'dg_ajax_add_to_cart' );

/**
 * AJAX: Buy Now.
 */
function dg_ajax_buy_now(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	$product_id = absint( $_POST['product_id'] ?? 0 );
	$slug       = sanitize_text_field( $_POST['slug'] ?? '' );
	$size       = sanitize_text_field( $_POST['size'] ?? '' );
	$quantity   = absint( $_POST['quantity'] ?? 1 );

	$result = DG_Checkout_Router::handle( array(
		'product_id' => $product_id,
		'slug'       => $slug,
		'size'       => $size,
		'quantity'   => $quantity,
	) );

	if ( $result['success'] ) {
		wp_send_json_success( $result );
	} else {
		wp_send_json_error( $result );
	}
}
add_action( 'wp_ajax_dg_ajax_buy_now', 'dg_ajax_buy_now' );
add_action( 'wp_ajax_nopriv_dg_ajax_buy_now', 'dg_ajax_buy_now' );

/**
 * AJAX: Update cart quantity (WooCommerce).
 */
function dg_ajax_update_cart(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	if ( ! dg_is_woocommerce_active() ) {
		wp_send_json_error( array( 'message' => __( 'WooCommerce is not active.', 'dragon-glow' ) ) );
	}

	$cart_item_key = sanitize_text_field( $_POST['cart_item_key'] ?? '' );
	$quantity      = absint( $_POST['quantity'] ?? 0 );

	if ( empty( $cart_item_key ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid cart item.', 'dragon-glow' ) ) );
	}

	if ( $quantity < 1 ) {
		WC()->cart->remove_cart_item( $cart_item_key );
	} else {
		WC()->cart->set_quantity( $cart_item_key, $quantity );
	}

	wp_send_json_success( array(
		'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
	) );
}
add_action( 'wp_ajax_dg_ajax_update_cart', 'dg_ajax_update_cart' );

/**
 * AJAX: Get current cart count.
 */
function dg_ajax_get_cart_count(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	wp_send_json_success( array( 'count' => dg_get_cart_item_count() ) );
}
add_action( 'wp_ajax_dg_ajax_get_cart_count', 'dg_ajax_get_cart_count' );
add_action( 'wp_ajax_nopriv_dg_ajax_get_cart_count', 'dg_ajax_get_cart_count' );

/**
 * AJAX: Remove product from cart (mode-aware).
 */
function dg_ajax_remove_product_from_cart(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	$product_id = absint( $_POST['product_id'] ?? 0 );
	$slug       = sanitize_text_field( $_POST['slug'] ?? '' );

	$result = dg_remove_from_cart_silently( array(
		'product_id' => $product_id,
		'slug'       => $slug,
	) );

	if ( $result['success'] ) {
		wp_send_json_success( array(
			'message' => __( 'Item removed from bag.', 'dragon-glow' ),
			'count'   => $result['count'] ?? 0,
		) );
	} else {
		wp_send_json_error( array( 'message' => $result['message'] ?? __( 'Could not remove item.', 'dragon-glow' ) ) );
	}
}
add_action( 'wp_ajax_dg_ajax_remove_product_from_cart',        'dg_ajax_remove_product_from_cart' );
add_action( 'wp_ajax_nopriv_dg_ajax_remove_product_from_cart', 'dg_ajax_remove_product_from_cart' );

/**
 * AJAX: Bulk-remove a list of cart-item keys.
 *
 * `cart_item_keys` is sent as a comma-separated string so it round-trips
 * through FormData (the same convention `dg_wishlist_remove_many` uses
 * for product IDs). Only keys that exist in the current cart are
 * removed — anything stale or spoofed is silently dropped so the
 * caller always receives a coherent, server-truth response.
 *
 * Returns:
 *   - removed: count actually removed (≤ requested).
 *   - count:   current cart contents count after the operation.
 *   - cart_hash: WC cart hash so the client can detect divergence.
 *   - fragments: refreshed order-summary fragment so totals update
 *     without a full page reload.
 */
function dg_ajax_remove_cart_items(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	if ( ! dg_is_woocommerce_active() || ! isset( WC()->cart ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Your bag is currently unavailable. Please try again later.', 'dragon-glow' ) ),
			503
		);
	}

	$raw = isset( $_POST['cart_item_keys'] )
		? sanitize_text_field( wp_unslash( (string) $_POST['cart_item_keys'] ) )
		: '';

	if ( '' === $raw ) {
		wp_send_json_error(
			array( 'message' => __( 'Please choose at least one item to remove.', 'dragon-glow' ) ),
			400
		);
	}

	$requested_keys = array_values(
		array_unique(
			array_filter(
				array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', $raw ) ) ),
				static function ( string $key ): bool {
					return '' !== $key;
				}
			)
		)
	);

	if ( empty( $requested_keys ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Please choose at least one item to remove.', 'dragon-glow' ) ),
			400
		);
	}

	// Only operate on keys that still exist server-side.
	$cart = WC()->cart->get_cart();
	$valid_keys = array_values( array_intersect( $requested_keys, array_keys( $cart ) ) );

	if ( empty( $valid_keys ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Those items are no longer in your bag.', 'dragon-glow' ) ),
			409
		);
	}

	$removed = 0;
	foreach ( $valid_keys as $key ) {
		if ( WC()->cart->remove_cart_item( $key ) ) {
			$removed++;
		}
	}

	if ( $removed <= 0 ) {
		wp_send_json_error(
			array( 'message' => __( 'Could not remove items from your bag.', 'dragon-glow' ) ),
			500
		);
	}

	// Drop the "checkout with selected" session list in this same request.
	// It may reference keys that no longer exist, which would make
	// /checkout/ hide or zero-price every remaining line. Doing it here
	// (one request, one session save) avoids a separate client-side sync
	// request racing this one and restoring the pre-removal cart.
	dg_clear_session_selection();

	wp_send_json_success(
		array(
			'removed'    => $removed,
			'requested'  => count( $requested_keys ),
			'count'      => WC()->cart->get_cart_contents_count(),
			'cart_hash'  => WC()->cart->get_cart_hash(),
			'fragments'  => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
			'message'    => sprintf(
				/* translators: %d: number of items removed. */
				_n( '%d item removed from your bag.', '%d items removed from your bag.', $removed, 'dragon-glow' ),
				$removed
			),
		)
	);
}
add_action( 'wp_ajax_dg_ajax_remove_cart_items',        'dg_ajax_remove_cart_items' );
add_action( 'wp_ajax_nopriv_dg_ajax_remove_cart_items', 'dg_ajax_remove_cart_items' );

/**
 * AJAX: Empty the entire cart.
 *
 * Returns the same shape as `dg_ajax_remove_cart_items` so the client
 * can share its reconciliation path. Uses `empty_cart()` so WC hooks
 * (`woocommerce_cart_emptied`, etc.) still fire.
 */
function dg_ajax_clear_cart(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	if ( ! dg_is_woocommerce_active() || ! isset( WC()->cart ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Your bag is currently unavailable. Please try again later.', 'dragon-glow' ) ),
			503
		);
	}

	$previous_count = WC()->cart->get_cart_contents_count();
	if ( $previous_count <= 0 ) {
		wp_send_json_success(
			array(
				'removed'   => 0,
				'count'     => 0,
				'cart_hash' => WC()->cart->get_cart_hash(),
				'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
				'message'   => __( 'Your bag is already empty.', 'dragon-glow' ),
			)
		);
	}

	WC()->cart->empty_cart();

	// Same reasoning as `dg_ajax_remove_cart_items`: reset the selection
	// session key in this request so no stale list survives the clear.
	dg_clear_session_selection();

	wp_send_json_success(
		array(
			'removed'   => $previous_count,
			'count'     => 0,
			'cart_hash' => WC()->cart->get_cart_hash(),
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
			'message'   => sprintf(
				/* translators: %d: number of items cleared. */
				_n( '%d item cleared from your bag.', '%d items cleared from your bag.', $previous_count, 'dragon-glow' ),
				$previous_count
			),
		)
	);
}
add_action( 'wp_ajax_dg_ajax_clear_cart',        'dg_ajax_clear_cart' );
add_action( 'wp_ajax_nopriv_dg_ajax_clear_cart', 'dg_ajax_clear_cart' );

/**
 * AJAX: Return cart identifiers.
 */
function dg_ajax_get_cart_identifiers(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	wp_send_json_success( dg_get_cart_identifiers() );
}
add_action( 'wp_ajax_dg_ajax_get_cart_identifiers',        'dg_ajax_get_cart_identifiers' );
add_action( 'wp_ajax_nopriv_dg_ajax_get_cart_identifiers', 'dg_ajax_get_cart_identifiers' );

/**
 * AJAX: Return product IDs in cart (DEPRECATED).
 */
function dg_ajax_get_cart_product_ids(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );
	$ids = dg_get_cart_identifiers();
	wp_send_json_success( array( 'product_ids' => $ids['product_ids'] ) );
}
add_action( 'wp_ajax_dg_ajax_get_cart_product_ids',        'dg_ajax_get_cart_product_ids' );
add_action( 'wp_ajax_nopriv_dg_ajax_get_cart_product_ids', 'dg_ajax_get_cart_product_ids' );

/**
 * AJAX: Apply a "checkout with selected" flag onto a list of cart-item keys.
 *
 * Flow:
 *   1. Validate the supplied keys against the live cart — silently
 *      drop stale or spoofed keys (mirrors `dg_ajax_remove_cart_items`).
 *   2. Clear any pre-existing `dg_selected_for_checkout` flags so the
 *      call is fully idempotent (re-ticking the same set or switching
 *      selection always ends in the same state).
 *   3. Stamp the flag onto each requested key.
 *   4. Re-render the cart-count fragment so the header badge stays in
 *      sync (no count change here, but keeps the contract uniform with
 *      the other cart AJAX handlers).
 *   5. Return the checkout URL the client should redirect to.
 *
 * Returning the URL (instead of letting `wp_safe_redirect()` do the
 * redirect) keeps the JS layer in control — it gets to show a toast
 * and animate the transition before navigation, matching the bulk-
 * remove / clear-cart UX.
 *
 * Failure shapes:
 *   - 400 when no keys supplied or none match the live cart.
 *
 * @return void
 */
function dg_ajax_apply_cart_selection(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	if ( ! dg_is_woocommerce_active() || ! isset( WC()->cart ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Your bag is currently unavailable. Please try again later.', 'dragon-glow' ) ),
			503
		);
	}

	$raw = isset( $_POST['cart_item_keys'] )
		? sanitize_text_field( wp_unslash( (string) $_POST['cart_item_keys'] ) )
		: '';

	$requested_keys = array_values(
		array_unique(
			array_filter(
				array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', $raw ) ) ),
				static function ( string $key ): bool {
					return '' !== $key;
				}
			)
		)
	);

	$cart = WC()->cart;

	// An empty key list is a valid request from the reactive sync
	// on /cart/ (the user un-ticked every checkbox). The intent is
	// "clear the session selection" so /checkout/ falls back to the
	// full cart. We treat that as success-with-empty-selection rather
	// than a 400, because the original `dg_ajax_apply_cart_selection`
	// action doubles as the "clear" channel for the sync flow.
	if ( empty( $requested_keys ) ) {
		dg_clear_session_selection();
		wp_send_json_success(
			array(
				'count'        => $cart->get_cart_contents_count(),
				'cart_hash'    => $cart->get_cart_hash(),
				'fragments'    => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
				'redirect'     => null,
				'selected_keys' => array(),
				'message'      => __( 'Selection cleared.', 'dragon-glow' ),
			)
		);
	}

	$live_keys  = array_keys( $cart->get_cart() );
	$valid_keys = array_values( array_intersect( $requested_keys, $live_keys ) );

	// Every requested key fell off the cart (e.g. items removed in
	// another tab). Fall back to "clear" so the user is not stuck
	// with a stale session that points at nothing.
	if ( empty( $valid_keys ) ) {
		dg_clear_session_selection();
		wp_send_json_success(
			array(
				'count'        => $cart->get_cart_contents_count(),
				'cart_hash'    => $cart->get_cart_hash(),
				'fragments'    => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
				'redirect'     => null,
				'selected_keys' => array(),
				'message'      => __( 'Selection cleared — those items are no longer in your bag.', 'dragon-glow' ),
			)
		);
	}

	// Step 2 — persist into the WooCommerce session (the only storage
	// that survives a redirect). We deliberately do NOT mutate
	// `$cart->cart_contents` here: WC's session serializer drops custom
	// keys that aren't part of its allowlist, so in-memory mutations
	// would not survive the page navigation. Instead we keep the list
	// in a dedicated session key and read it on demand via
	// `dg_is_cart_item_selected()` (see inc/woocommerce/cart-selection.php).
	if ( WC()->session ) {
		WC()->session->set( 'dg_checkout_selection', array_values( $valid_keys ) );
	}

	// Force a save so the redirect to /checkout/ in the JS layer
	// always sees the updated session. The default save path runs on
	// `shutdown`, which is normally fine — but when the AJAX handler
	// returns a JSON payload and the client navigates with
	// `window.location.href`, the request terminates cleanly. Saving
	// here is a belt-and-braces guard for any hosting setup that
	// short-circuits the shutdown chain (object cache, OPCache edge
	// cases, etc.).
	if ( WC()->session && method_exists( WC()->session, 'save_data' ) ) {
		WC()->session->save_data();
	}

	// NOTE: We deliberately do NOT mutate `$cart->cart_contents` here.
	// In a previous version of this code we set
	// `dg_selected_for_checkout` on each in-memory cart item so the
	// same request could detect the selection via `$item['…']`. That
	// turned out to be the source of a "ghost flag" bug:
	//   1. The previous request loaded the cart with `f7177163…` flagged
	//      from a stale `dg_checkout_selection` session key.
	//   2. This AJAX stamped the new `a5771bce…` key but did NOT
	//      unflag `f7177163…` in the in-memory `cart_contents`.
	//   3. `WC()->session->save_data()` then re-serialised the cart
	//      (WC keeps the cart in its own session bucket), so the
	//      stale flag survived the navigation to /checkout/ and the
	//      non-selected item was still counted as selected.
	//
	// The fix is to make `WC()->session['dg_checkout_selection']` the
	// ONLY source of truth and read it on demand via
	// `dg_is_cart_item_selected()`. No flag is ever written back into
	// the cart, so the cart is always a pure projection of WC state.

	// Step 4 — uniform fragments payload (count does not change, but
	// the contract stays simple for the client).
	$payload = array(
		'count'        => $cart->get_cart_contents_count(),
		'cart_hash'    => $cart->get_cart_hash(),
		'fragments'    => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
		'redirect'     => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' ),
		'selected_keys' => array_values( $valid_keys ),
		'message'      => sprintf(
			/* translators: %d: number of items selected. */
			_n( '%d item ready for checkout.', '%d items ready for checkout.', count( $valid_keys ), 'dragon-glow' ),
			count( $valid_keys )
		),
	);

	wp_send_json_success( $payload );
}
add_action( 'wp_ajax_dg_ajax_apply_cart_selection',        'dg_ajax_apply_cart_selection' );
add_action( 'wp_ajax_nopriv_dg_ajax_apply_cart_selection', 'dg_ajax_apply_cart_selection' );

/**
 * AJAX: Clear every active "checkout with selected" flag without
 * removing any items from the cart.
 *
 * Companion to `dg_ajax_apply_cart_selection` — the Order Summary has
 * a "Show all items" link that needs to reset the flag without forcing
 * a full page reload (so the cart-select state in /cart/ stays in sync
 * too).
 *
 * @return void
 */
function dg_ajax_clear_cart_selection(): void {
	check_ajax_referer( 'dg_nonce', 'nonce' );

	if ( ! dg_is_woocommerce_active() || ! isset( WC()->cart ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Your bag is currently unavailable. Please try again later.', 'dragon-glow' ) ),
			503
		);
	}

	// Step 1 — drop the session key. The selection lives only in
	// `WC()->session['dg_checkout_selection']` (single source of
	// truth), so clearing it is sufficient — every filter in
	// `inc/woocommerce/cart-selection.php` will stop matching on the
	// next request. Reuse the shared helper so the cart-load path
	// (template_redirect) and this AJAX path apply the same write +
	// save_data() guard.
	dg_clear_session_selection();

	wp_send_json_success(
		array(
			'count'     => WC()->cart->get_cart_contents_count(),
			'cart_hash' => WC()->cart->get_cart_hash(),
			'message'   => __( 'Showing every item in your bag.', 'dragon-glow' ),
		)
	);
}
add_action( 'wp_ajax_dg_ajax_clear_cart_selection',        'dg_ajax_clear_cart_selection' );
add_action( 'wp_ajax_nopriv_dg_ajax_clear_cart_selection', 'dg_ajax_clear_cart_selection' );
