<?php
/**
 * Dragon Glow — WooCommerce: Single Product
 *
 * Single product page customizations: product-thumbnail column count and the
 * number/columns of related products.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

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
 * Change number of related products.
 *
 * @param array $args Related products query args.
 * @return array
 */
function dg_related_products_args( array $args ): array {
    $args['posts_per_page'] = 4;
    $args['columns']        = 4;
    return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'dg_related_products_args' );

/**
 * Enable AJAX add to cart on single product pages.
 * Prevents page reload when adding products to cart.
 *
 * @return bool
 */
function dg_enable_ajax_add_to_cart(): bool {
	return true;
}
add_filter( 'woocommerce_product_add_to_cart_url', '__return_false' );
add_filter( 'woocommerce_loop_add_to_cart_link', function( $html ) {
	return str_replace( 'ajax_add_to_cart', 'ajax_add_to_cart ajax-enabled', $html );
} );

/**
 * Add "Buy Now" button after "Add to Cart" button.
 * Uses woocommerce_after_add_to_cart_button hook to inject the button
 * directly into the form.cart, creating a horizontal button row.
 *
 * @return void
 */
function dg_add_buy_now_button(): void {
	global $product;
	
	if ( ! $product ) {
		return;
	}
	?>
	<button type="button" 
	        class="dg-buy-now-btn" 
	        data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
	        aria-label="<?php esc_attr_e( 'Buy now', 'dragon-glow' ); ?>">
		<span class="material-symbols-outlined" aria-hidden="true">shopping_bag_speed</span>
		<?php esc_html_e( 'Buy Now', 'dragon-glow' ); ?>
	</button>
	<?php
}
add_action( 'woocommerce_after_add_to_cart_button', 'dg_add_buy_now_button' );
