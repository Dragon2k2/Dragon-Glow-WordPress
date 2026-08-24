<?php
/**
 * Dragon Glow — WooCommerce: General Integration
 *
 * Site-wide WooCommerce customizations that are not tied to a single page:
 * content wrapper, sidebar removal, default style dequeue, and add-to-cart /
 * stock button text.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

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
    echo '<main>';
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
 * Change checkout button text.
 *
 * @return string
 */
function dg_checkout_button_text(): string {
    return __( 'Continue to Checkout', 'dragon-glow' );
}
add_filter( 'woocommerce_order_button_text', 'dg_checkout_button_text' );
