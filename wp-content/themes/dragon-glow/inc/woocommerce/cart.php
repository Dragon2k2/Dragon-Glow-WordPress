<?php
/**
 * Dragon Glow — WooCommerce: Cart
 *
 * Cart-related customizations: the AJAX cart-count fragment and the display
 * of the selected size on cart line items.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cart fragments for AJAX updates.
 *
 * @param array $fragments Fragments array.
 * @return array
 */
function dg_cart_count_fragment( array $fragments ): array {
    if ( ! isset( WC()->cart ) ) {
        return $fragments;
    }
    $count = WC()->cart->get_cart_contents_count();
    $fragments['.dg-cart-count'] = dg_render_cart_count_badge( $count );
    return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'dg_cart_count_fragment' );

// Cart icon is rendered in the header icons bar (header-nav.php) — not in the primary menu.
// Keeping the filter below as a fallback in case a child-theme or plugin expects it,
// but it is NOT currently wired to any menu location.
// function dg_cart_menu_item( string $items, object|array $args ): string {
//     if ( 'primary' !== $args->theme_location ) {
//         return $items;
//     }
//     if ( ! isset( WC()->cart ) ) {
//         return $items;
//     }
//     $cart_count = WC()->cart->get_cart_contents_count();
//     $cart_link = '<li class="menu-item-cart">';
//     $cart_link .= '<a href="' . esc_url( dg_get_cart_url() ) . '" class="relative p-2 hover:bg-primary-container/20 rounded-full transition-all text-primary" aria-label="' . esc_attr__( 'Cart', 'dragon-glow' ) . '">';
//     $cart_link .= '<span class="material-symbols-outlined">shopping_bag</span>';
//     $cart_link .= '<span class="dg-cart-count absolute -top-1 -right-1 bg-primary text-on-primary text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center' . ( $cart_count ? '' : ' hidden' ) . '">';
//     $cart_link .= $cart_count;
//     $cart_link .= '</span>';
//     $cart_link .= '</a>';
//     $cart_link .= '</li>';
//     return $items . $cart_link;
// }
// add_filter( 'wp_nav_menu_items', 'dg_cart_menu_item', 10, 2 );

/**
 * Display the selected size on cart, checkout, and order line items.
 *
 * When a shadow product is added to cart via "Buy Now" with a size selection,
 * the size is stored as cart item data 'dg_selected_size'.  This filter
 * converts that into a "Size: 50ml" line consistent with how WooCommerce
 * displays variation attributes.
 *
 * @param array $item_data Existing cart item data lines.
 * @param array $cart_item The full cart item array.
 * @return array
 */
function dg_display_cart_item_size( array $item_data, array $cart_item ): array {
	if ( empty( $cart_item['dg_selected_size'] ) ) {
		return $item_data;
	}

	$item_data[] = array(
		'key'   => __( 'Size', 'dragon-glow' ),
		'value' => wc_clean( $cart_item['dg_selected_size'] ),
	);

	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'dg_display_cart_item_size', 10, 2 );

/**
 * Persist the selected size into the order line item meta.
 *
 * @param WC_Order_Item_Product $item          Order line item object.
 * @param string                $cart_item_key Cart item key in the cart.
 * @param array                 $cart_item     Full cart item data.
 * @param WC_Order              $order         The order being placed.
 * @return void
 */
function dg_save_cart_item_size_to_order(
	WC_Order_Item_Product $item,
	string $cart_item_key,
	array $cart_item,
	WC_Order $order
): void {
	if ( ! empty( $cart_item['dg_selected_size'] ) ) {
		$item->add_meta_data(
			__( 'Size', 'dragon-glow' ),
			wc_clean( $cart_item['dg_selected_size'] ),
			true
		);
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'dg_save_cart_item_size_to_order', 10, 4 );
