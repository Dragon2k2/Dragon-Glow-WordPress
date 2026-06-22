<?php
/**
 * Dragon Glow — Cart Functions
 *
 * Shared cart utility functions used by:
 *   - AJAX handlers (dg_ajax_add_to_cart, dg_ajax_buy_now)
 *   - Mock checkout handler
 *   - Template files that need to inspect or mutate cart state
 *
 * This file centralises cart logic that was previously spread across:
 *   - inc/ajax-handlers.php  (inline helper logic in AJAX callbacks)
 *   - inc/checkout/*.php     (transient cart helpers in DG_Mock_Checkout_Handler)
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add a WooCommerce product to the cart with full variation support.
 *
 * @param int         $product_id Numeric WC product ID.
 * @param int         $quantity  Number of items (default 1).
 * @param int         $variation_id Variation ID (0 for simple products).
 * @param array       $variation_attributes Key/value map of attribute names → values.
 * @param array       $cart_item_data Extra cart item metadata.
 * @return string|false Cart item key on success, false on failure.
 */
function dg_wc_add_to_cart( int $product_id, int $quantity = 1, int $variation_id = 0, array $variation_attributes = array(), array $cart_item_data = array() ) {
	if ( ! dg_is_woocommerce_active() ) {
		return false;
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return false;
	}

	$product_type = $product->get_type();

	if ( 'variable' === $product_type && $variation_id > 0 ) {
		return WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_attributes, $cart_item_data );
	}

	return WC()->cart->add_to_cart( $product_id, $quantity );
}

/**
 * Find a WooCommerce variation ID by matching a size attribute value.
 *
 * Searches available variations of a variable product for one whose
 * attributes contain the given size label (case-insensitive match).
 *
 * @param WC_Product_Variable $product Variable product object.
 * @param string              $size    Size label to match (e.g. "50ml").
 * @return int Variation ID, or 0 if not found.
 */
function dg_wc_find_variation_by_size( WC_Product_Variable $product, string $size ): int {
	$variations = $product->get_available_variations();

	foreach ( $variations as $variation ) {
		$variation_obj = wc_get_product( $variation['variation_id'] );
		if ( ! $variation_obj ) {
			continue;
		}

		$attrs = $variation_obj->get_attributes();
		foreach ( $attrs as $attr_value ) {
			if ( strtolower( trim( (string) $attr_value ) ) === strtolower( trim( $size ) ) ) {
				return (int) $variation['variation_id'];
			}
		}
	}

	return 0;
}

/**
 * Build the correct checkout URL for the current system state.
 *
 * Priority:
 *   1. WooCommerce checkout URL (when WC is active and has items in cart).
 *   2. Mock checkout page URL (when a published mock checkout page exists).
 *   3. Shop URL with dg_checkout_unavailable flag (so the UI can show a meaningful
 *      notice instead of silently redirecting the user).
 *
 * @return string
 */
function dg_get_checkout_url(): string {
	if ( dg_is_woocommerce_active() && class_exists( 'WC' ) && ! WC()->cart->is_empty() ) {
		return wc_get_checkout_url();
	}

	return dg_get_mock_checkout_url();
}

/**
 * Check whether the mock checkout page has been set up.
 *
 * @return bool True if a published mock checkout page exists.
 */
function dg_mock_checkout_page_exists(): bool {
	return (bool) dg_find_mock_checkout_page();
}

/**
 * Get the transient mock cart array.
 *
 * @return array
 */
function dg_get_mock_cart(): array {
	return get_transient( DG_Mock_Checkout_Handler::CART_TRANSIENT_KEY ) ?: array();
}

/**
 * Save the transient mock cart array.
 *
 * @param array $cart
 * @return bool
 */
function dg_save_mock_cart( array $cart ): bool {
	return set_transient( DG_Mock_Checkout_Handler::CART_TRANSIENT_KEY, $cart, 7 * DAY_IN_SECONDS );
}

/**
 * Clear the transient mock cart.
 *
 * @return bool
 */
function dg_clear_mock_cart(): bool {
	return delete_transient( DG_Mock_Checkout_Handler::CART_TRANSIENT_KEY );
}

// ─────────────────────────────────────────────────────────────────────────────
//  Cart Page Resolution  (mirrors mock-checkout page lookup pattern in helpers.php)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Get the WooCommerce Cart page object if it exists and is published.
 *
 * @return WP_Post|null
 */
function dg_find_wc_cart_page() {
	if ( ! dg_is_woocommerce_active() ) {
		return null;
	}

	$page_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'cart' ) : 0;

	if ( $page_id <= 0 ) {
		return null;
	}

	$page = get_post( $page_id );

	if ( ! $page || 'publish' !== $page->post_status ) {
		return null;
	}

	return $page;
}

/**
 * Check whether the WooCommerce Cart page is published.
 *
 * @return bool
 */
function dg_wc_cart_page_exists(): bool {
	return (bool) dg_find_wc_cart_page();
}

/**
 * Find the internal Mock Cart page.
 *
 * Looks for a published page by slug "cart" when WooCommerce is inactive, OR
 * any published page using the mock-cart template.
 *
 * @return WP_Post|null
 */
function dg_find_mock_cart_page() {
	// Strategy 1: published page with slug "cart" (only reliable when WC is inactive,
	// since WC owns the "cart" slug when active).
	if ( ! dg_is_woocommerce_active() ) {
		$page_by_slug = get_page_by_path( 'cart' );
		if ( $page_by_slug && 'publish' === $page_by_slug->post_status ) {
			return $page_by_slug;
		}
	}

	// Strategy 2: any published page using the mock-cart template.
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-templates/template-mock-cart.php',
		)
	);

	return ! empty( $pages ) ? $pages[0] : null;
}

/**
 * Single source of truth for the cart page URL.
 *
 * Priority:
 *   1. WooCommerce cart URL (when WC active and cart page is published).
 *   2. Shop URL with dg_cart_unavailable=1 (when WC active but cart page is missing).
 *   3. Mock cart page permalink (when WC inactive and a mock cart page exists).
 *   4. Shop URL with dg_cart_unavailable=1 (fallback — never returns a guessed URL).
 *
 * @return string
 */
function dg_get_cart_url(): string {
	if ( dg_is_woocommerce_active() && dg_wc_cart_page_exists() ) {
		return wc_get_cart_url();
	}

	if ( dg_is_woocommerce_active() ) {
		// WC active but the cart page is unpublished / deleted.
		$shop_url = function_exists( 'wc_get_page_permalink' )
			? wc_get_page_permalink( 'shop' )
			: home_url( '/shop/' );
		return add_query_arg( 'dg_cart_unavailable', '1', $shop_url );
	}

	// WC inactive — try the mock cart page.
	$mock_page = dg_find_mock_cart_page();
	if ( $mock_page ) {
		return get_permalink( $mock_page );
	}

	// No cart page found — return shop URL with a flag so the frontend
	// can display a meaningful "please create a cart page" message.
	$shop_url = home_url( '/shop/' );
	return add_query_arg( 'dg_cart_unavailable', '1', $shop_url );
}

// ─────────────────────────────────────────────────────────────────────────────
//  End Cart Page Resolution
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Silent add-to-cart: adds a product to cart without ever producing a redirect.
 *
 * Handles all three product modes:
 *   - WooCommerce product (has numeric product_id) → uses dg_wc_add_to_cart().
 *   - Shadow WooCommerce product (has numeric product_id, WC active) → same path.
 *   - Mock product (slug-only, no numeric ID, WC inactive or no shadow product)
 *     → stores the item in the transient mock cart so the user can view it on
 *       the mock checkout page without being redirected.
 *
 * After a successful add, always returns a 'redirect' key pointing to the
 * canonical cart URL (dg_get_cart_url()) so the frontend never has to guess.
 *
 * @param array $args {
 *     @type int    $product_id  Numeric WC product ID (0 if using slug for mock product).
 *     @type string $slug        Mock product slug (used when product_id is 0).
 *     @type string $size        Selected size label (e.g. "50ml").
 *     @type int    $quantity    Number of items (default 1).
 * }
 * @return array{success: bool, message?: string, redirect?: string}
 */
function dg_add_to_cart_silently( array $args ): array {
	$product_id = absint( $args['product_id'] ?? 0 );
	$slug       = sanitize_text_field( $args['slug'] ?? '' );
	$size       = sanitize_text_field( $args['size'] ?? '' );
	$quantity   = absint( $args['quantity'] ?? 1 );

	if ( $product_id > 0 ) {
		$added = dg_wc_add_to_cart( $product_id, $quantity );
		if ( $added ) {
			return array(
				'success'  => true,
				'redirect' => dg_get_cart_url(),
			);
		}
		return array(
			'success' => false,
			'message'  => __( 'Could not add to cart.', 'dragon-glow' ),
		);
	}

	if ( empty( $slug ) ) {
		return array(
			'success' => false,
			'message' => __( 'Invalid product.', 'dragon-glow' ),
		);
	}

	$mock_repo = DG_Product_Factory::mock();
	$product  = $mock_repo->get_by_slug( $slug );

	if ( ! $product ) {
		return array(
			'success' => false,
			'message' => __( 'Product not found.', 'dragon-glow' ),
		);
	}

	$cart_items = dg_get_mock_cart();
	$item_key   = $slug . '|' . $size;

	$cart_items[ $item_key ] = array(
		'slug'            => $slug,
		'name'            => $product->get_name(),
		'price'          => $product->get_price(),
		'formatted_price' => $product->get_price_formatted(),
		'image_url'      => $product->get_image_url(),
		'size'           => $size,
		'quantity'       => $quantity,
	);

	dg_save_mock_cart( $cart_items );

	return array(
		'success'  => true,
		'redirect' => dg_get_cart_url(),
	);
}

/**
 * Get the total item count across whichever cart system is currently active.
 *
 * @return int
 */
function dg_get_cart_item_count(): int {
	if ( dg_is_woocommerce_active() && isset( WC()->cart ) ) {
		return WC()->cart->get_cart_contents_count();
	}

	$total = 0;
	foreach ( dg_get_mock_cart() as $item ) {
		$total += (int) ( $item['quantity'] ?? 0 );
	}
	return $total;
}

/**
 * Render the cart-count badge markup. Single source of truth — used by both
 * the initial server-side render (header-nav.php) and the WooCommerce
 * fragments filter (dg_cart_count_fragment) so the two never drift apart.
 *
 * @param int $count Item count.
 * @return string
 */
function dg_render_cart_count_badge( int $count ): string {
	$classes = 'dg-cart-count absolute -top-1 -right-1 bg-primary text-on-primary text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center';
	if ( 0 === $count ) {
		$classes .= ' hidden';
	}
	return '<span class="' . esc_attr( $classes ) . '">' . esc_html( $count ) . '</span>';
}

// ─────────────────────────────────────────────────────────────────────────────
//  Silent Remove  (single source of truth for cart removal)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Remove a product from whichever cart system is currently active.
 *
 * Mode-aware:
 *   - product_id > 0  → remove from WooCommerce cart (scans by product_id).
 *   - slug provided   → remove from transient mock cart.
 *
 * Used exclusively by AJAX handlers; exposed as a function so the same logic
 * can be called from any context without repeating the scan logic.
 *
 * @param array $args {
 *     @type int    $product_id  Numeric WC product ID (0 if removing mock-only).
 *     @type string $slug        Mock product slug (used when product_id is 0).
 * }
 * @return array{success: bool, message?: string, count?: int}
 */
function dg_remove_from_cart_silently( array $args ): array {
	$product_id = absint( $args['product_id'] ?? 0 );
	$slug       = sanitize_text_field( $args['slug'] ?? '' );

	// ── WooCommerce path ──────────────────────────────────────────────────────
	if ( $product_id > 0 && dg_is_woocommerce_active() && isset( WC()->cart ) ) {
		$removed = false;
		foreach ( WC()->cart->get_cart() as $cart_item_key => $item ) {
			if ( (int) $item['product_id'] === $product_id ) {
				$removed = WC()->cart->remove_cart_item( $cart_item_key );
				break;
			}
		}

		if ( $removed ) {
			return array(
				'success' => true,
				'count'   => WC()->cart->get_cart_contents_count(),
			);
		}
		return array(
			'success' => false,
			'message'  => __( 'Could not remove item.', 'dragon-glow' ),
		);
	}

	// ── Mock cart path ───────────────────────────────────────────────────────
	if ( empty( $slug ) ) {
		return array(
			'success' => false,
			'message'  => __( 'No identifier provided for removal.', 'dragon-glow' ),
		);
	}

	$cart     = dg_get_mock_cart();
	$item_key = $slug; // In mock cart, items are keyed by slug only (no size variant here).
	$found    = false;

	// Scan for any mock item whose key starts with the slug (handles size-variant keys
	// like "luminous-serum|50ml" — we only need to match the slug prefix).
	foreach ( $cart as $key => $item ) {
		if ( 0 === strpos( $key, $slug . '|' ) || $key === $slug ) {
			unset( $cart[ $key ] );
			$found = true;
		}
	}

	if ( ! $found ) {
		return array(
			'success' => false,
			'message'  => __( 'Item not found in cart.', 'dragon-glow' ),
		);
	}

	dg_save_mock_cart( $cart );

	$total = 0;
	foreach ( $cart as $item ) {
		$total += (int) ( $item['quantity'] ?? 0 );
	}

	return array(
		'success' => true,
		'count'   => $total,
	);
}

// ─────────────────────────────────────────────────────────────────────────────
//  Cart Identifiers  (single source of truth for restore-cart-state)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Get all stable identifiers of items currently in the cart.
 *
 * Mode-aware:
 *   - WooCommerce active → reads WC()->cart → returns product_ids.
 *   - WooCommerce inactive → reads transient mock cart → returns slugs.
 *
 * Used by the shop page to pre-mark quick-add buttons on page load so the
 * "Added" state survives hard refreshes.
 *
 * @return array{product_ids: int[], slugs: string[]}
 */
function dg_get_cart_identifiers(): array {
	$product_ids = array();
	$slugs       = array();

	if ( dg_is_woocommerce_active() && isset( WC()->cart ) && WC()->cart ) {
		foreach ( WC()->cart->get_cart() as $item ) {
			$product_ids[] = (int) $item['product_id'];
		}
		$product_ids = array_values( array_unique( $product_ids ) );
	} else {
		$cart = dg_get_mock_cart();
		foreach ( $cart as $key => $item ) {
			$slugs[] = $item['slug'] ?? '';
		}
		$slugs = array_values( array_unique( array_filter( $slugs ) ) );
	}

	return array(
		'product_ids' => $product_ids,
		'slugs'       => $slugs,
	);
}
