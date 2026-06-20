<?php
/**
 * Dragon Glow — WooCommerce Integration
 * Hooks và filters để WooCommerce hoạt động với theme.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Bail if WooCommerce is not active
if ( ! dg_is_woocommerce_active() ) {
	return;
}

/**
 * Remove default WooCommerce wrapper and add custom wrapper.
 *
 * @return void
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_before_main_content', 'dg_wc_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content',  'dg_wc_wrapper_end', 10 );

/**
 * WooCommerce wrapper start.
 *
 * @return void
 */
function dg_wc_wrapper_start(): void {
    echo '<main class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">';
}

/**
 * WooCommerce wrapper end.
 *
 * @return void
 */
function dg_wc_wrapper_end(): void {
    echo '</main>';
}

/**
 * Remove default sidebar.
 *
 * @return void
 */
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * Set shop columns.
 *
 * @return int
 */
function dg_loop_shop_columns(): int {
    return 4;
}
add_filter( 'loop_shop_columns', 'dg_loop_shop_columns' );

/**
 * Set products per page.
 *
 * @return int
 */
function dg_loop_shop_per_page(): int {
    return 12;
}
add_filter( 'loop_shop_per_page', 'dg_loop_shop_per_page' );

/**
 * Set product thumbnails columns.
 *
 * @return int
 */
function dg_product_thumbnails_columns(): int {
    return 4;
}
add_filter( 'woocommerce_product_thumbnails_columns', 'dg_product_thumbnails_columns' );

/**
 * Remove default breadcrumb.
 *
 * @return void
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

/**
 * Add custom product badges.
 *
 * @return void
 */
add_action( 'woocommerce_before_shop_loop_item_title', 'dg_product_badges', 5 );

/**
 * Render product badges.
 *
 * @return void
 */
function dg_product_badges(): void {
    global $product;

    if ( ! $product ) {
        return;
    }

    echo '<div class="absolute top-4 left-4 z-10 flex flex-col gap-2">';

    // Featured (New) badge
    if ( $product->is_featured() ) {
        echo '<span class="badge-new">' . esc_html__( 'New', 'dragon-glow' ) . '</span>';
    }

    // Bestseller badge
    if ( $product->get_attribute( 'bestseller' ) ) {
        echo '<span class="badge-bestseller">' . esc_html__( 'Bestseller', 'dragon-glow' ) . '</span>';
    }

    // Sale badge
    if ( $product->is_on_sale() ) {
        echo '<span class="badge-new">' . esc_html__( 'Sale', 'dragon-glow' ) . '</span>';
    }

    echo '</div>';
}

/**
 * Remove default add to cart and add custom.
 *
 * @return void
 */
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
add_action( 'woocommerce_after_shop_loop_item', 'dg_custom_loop_add_to_cart', 10 );

/**
 * Render custom add to cart button.
 *
 * @return void
 */
function dg_custom_loop_add_to_cart(): void {
    global $product;

    if ( ! $product ) {
        return;
    }

    $product_type = $product->get_type();
    $button_text  = ( 'simple' === $product_type ) ? __( 'Quick Add', 'dragon-glow' ) : __( 'View Options', 'dragon-glow' );

    echo '<button class="absolute bottom-4 left-4 right-4 bg-primary text-on-primary py-3 rounded-xl font-label-sm text-label-sm opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 hover:brightness-110 wc-add-to-cart-btn z-10" data-product-id="' . esc_attr( $product->get_id() ) . '" data-product-type="' . esc_attr( $product_type ) . '">';
    echo esc_html( $button_text );
    echo '</button>';
}

/**
 * Custom sale flash.
 *
 * @param string $html    Original HTML.
 * @param object $post   Post object.
 * @param object $product Product object.
 * @return string
 */
function dg_custom_sale_flash( string $html, $post, $product ): string {
    return '<span class="badge-new absolute top-4 left-4 z-10">' . esc_html__( 'Sale', 'dragon-glow' ) . '</span>';
}
add_filter( 'woocommerce_sale_flash', 'dg_custom_sale_flash', 10, 3 );

/**
 * Disable WooCommerce default styles.
 *
 * @param array $enqueue_styles Styles to enqueue.
 * @return array
 */
function dg_dequeue_styles( array $enqueue_styles ): array {
    return array();
}
add_filter( 'woocommerce_enqueue_styles', 'dg_dequeue_styles' );

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
    $fragments['.dg-cart-count'] = '<span class="dg-cart-count' . ( $count ? '' : ' hidden' ) . '">' . $count . '</span>';
    return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'dg_cart_count_fragment' );

/**
 * Change "Add to Cart" text.
 *
 * @param string $text    Button text.
 * @param object $product Product object.
 * @return string
 */
function dg_add_to_cart_text( string $text ): string {
    return __( 'Add to Bag', 'dragon-glow' );
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'dg_add_to_cart_text' );
add_filter( 'woocommerce_product_add_to_cart_text', 'dg_add_to_cart_text' );

/**
 * Change "Out of Stock" text.
 *
 * @param string $text Stock status text.
 * @return string
 */
function dg_out_of_stock_text( string $text ): string {
    return __( 'Sold Out', 'dragon-glow' );
}
add_filter( 'woocommerce_get_availability_text', function( $text, $product ) {
    if ( ! $product->is_in_stock() ) {
        return __( 'Sold Out', 'dragon-glow' );
    }
    return $text;
}, 10, 2 );

/**
 * Add cart icon to menu.
 *
 * @param string $items Menu items HTML.
 * @param array  $args  Menu arguments.
 * @return string
 */
function dg_cart_menu_item( string $items, array $args ): string {
    if ( 'primary' !== $args->theme_location ) {
        return $items;
    }

    if ( ! isset( WC()->cart ) ) {
        return $items;
    }

    $cart_count = WC()->cart->get_cart_contents_count();

    $cart_link = '<li class="menu-item-cart">';
    $cart_link .= '<a href="' . esc_url( wc_get_cart_url() ) . '" class="relative p-2 hover:bg-primary-container/20 rounded-full transition-all text-primary" aria-label="' . esc_attr__( 'Cart', 'dragon-glow' ) . '">';
    $cart_link .= '<span class="material-symbols-outlined">shopping_bag</span>';
    $cart_link .= '<span class="dg-cart-count absolute -top-1 -right-1 bg-primary text-on-primary text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center' . ( $cart_count ? '' : ' hidden' ) . '">';
    $cart_link .= $cart_count;
    $cart_link .= '</span>';
    $cart_link .= '</a>';
    $cart_link .= '</li>';

    return $items . $cart_link;
}
add_filter( 'wp_nav_menu_items', 'dg_cart_menu_item', 10, 2 );

/**
 * Remove result count from shop archives.
 *
 * @return void
 */
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

/**
 * Remove catalog ordering from shop archives (we add custom).
 *
 * @return void
 */
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

/**
 * Change number of related products.
 *
 * @return int
 */
function dg_related_products_args( array $args ): array {
    $args['posts_per_page'] = 4;
    $args['columns']        = 4;
    return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'dg_related_products_args' );

/**
 * Change checkout button text.
 *
 * @return string
 */
function dg_checkout_button_text(): string {
    return __( 'Continue to Checkout', 'dragon-glow' );
}
add_filter( 'woocommerce_order_button_text', 'dg_checkout_button_text' );

/**
 * Override WooCommerce pagination để dùng custom theme pagination.
 * Ngăn woocommerce_pagination() render HTML mặc định của WC.
 */
remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );

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
 * @param WC_Order_Item_Product $item         Order line item object.
 * @param string                $cart_item_key Cart item key in the cart.
 * @param array                 $cart_item     Full cart item data.
 * @param WC_Order              $order        The order being placed.
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
