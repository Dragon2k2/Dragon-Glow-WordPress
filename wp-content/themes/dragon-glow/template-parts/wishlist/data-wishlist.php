<?php
/**
 * Dragon Glow — Wishlist data
 *
 * Single source of truth for the Wishlist page. Builds the array of
 * hydrated products that the template loops over. Anything that might
 * need to be themed (labels, lookback window, behaviour when WC is off)
 * is funneled through this file so the template-part stays presentational.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hydrate the current user's wishlist into a list of render-ready product
 * arrays. Each item carries every field the template needs — image URLs,
 * price HTML, badges, stock state, average rating, sale percentage — so
 * the markup never has to call back into WooCommerce.
 *
 * Items whose product has been permanently deleted are silently dropped
 * (the helper already filters them out, this is a defensive second pass).
 *
 * @return array<int, array<string, mixed>>
 */
function dg_wishlist_page_data(): array {
	if ( ! dg_is_woocommerce_active() || ! is_user_logged_in() ) {
		return array();
	}

	$ids = dg_get_wishlist();
	if ( empty( $ids ) ) {
		return array();
	}

	$items = array();

	foreach ( $ids as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			continue;
		}

		$thumb = get_the_post_thumbnail_url( $product_id, 'dg-product-card' );
		$gallery_ids = $product->get_gallery_image_ids();
		$gallery = $gallery_ids ? wp_get_attachment_image_url( $gallery_ids[0], 'dg-product-card' ) : '';

		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();
		$percent = 0;
		if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
			$percent = (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
		}

		$badge = '';
		if ( $product->is_featured() ) {
			$badge = __( 'New', 'dragon-glow' );
		} else {
			$attr_badge = $product->get_attribute( 'badge' );
			if ( $attr_badge ) {
				$badge = ucfirst( $attr_badge );
			}
		}

		$primary_category = dg_get_product_primary_category( $product_id );

		$items[] = array(
			'id'             => (int) $product_id,
			'name'           => $product->get_name(),
			'permalink'      => (string) get_permalink( $product_id ),
			'image'          => $thumb ?: wc_placeholder_img_src(),
			'image_hover'    => $gallery ?: $thumb ?: wc_placeholder_img_src(),
			'price_html'     => $product->get_price_html(),
			'regular_price'  => $regular,
			'sale_price'     => $sale,
			'discount_pct'   => $percent,
			'on_sale'        => $product->is_on_sale(),
			'in_stock'       => $product->is_in_stock(),
			'purchasable'    => $product->is_purchasable(),
			'type'           => (string) $product->get_type(),
			'slug'           => (string) $product->get_slug(),
			'rating'         => (float) $product->get_average_rating(),
			'review_count'   => (int) $product->get_review_count(),
			'badge'          => $badge,
			'category_name'  => $primary_category ? $primary_category->name : '',
			'category_url'   => $primary_category ? (string) get_term_link( $primary_category ) : '',
		);
	}

	/**
	 * Filter the hydrated wishlist items before the template renders them.
	 *
	 * @param array<int, array<string, mixed>> $items Resolved product data.
	 */
	return (array) apply_filters( 'dg_wishlist_page_data', $items );
}

/**
 * Compute aggregate stats for the wishlist hero.
 *
 * Returned keys: total (count), in_stock, on_sale, total_value (sum of
 * active sale/regular prices — sale wins when on sale), saved_amount
 * (sum of regular − sale across on-sale items).
 *
 * @param array<int, array<string, mixed>> $items Items from dg_wishlist_page_data().
 * @return array<string, int|float>
 */
function dg_wishlist_page_stats( array $items ): array {
	$total         = count( $items );
	$in_stock      = 0;
	$on_sale       = 0;
	$total_value   = 0.0;
	$saved_amount  = 0.0;

	foreach ( $items as $item ) {
		if ( $item['in_stock'] ) {
			++$in_stock;
		}
		if ( $item['on_sale'] ) {
			++$on_sale;
		}
		$price = $item['on_sale'] && $item['sale_price'] > 0
			? (float) $item['sale_price']
			: (float) $item['regular_price'];
		$total_value += $price;

		if ( $item['on_sale'] && $item['sale_price'] > 0 ) {
			$saved_amount += max( 0, (float) $item['regular_price'] - (float) $item['sale_price'] );
		}
	}

	return array(
		'total'        => $total,
		'in_stock'     => $in_stock,
		'on_sale'      => $on_sale,
		'total_value'  => $total_value,
		'saved_amount' => $saved_amount,
	);
}
