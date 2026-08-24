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
