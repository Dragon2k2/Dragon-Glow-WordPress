<?php
/**
 * Dragon Glow — WooCommerce Product Repository
 *
 * Wraps WooCommerce product data behind the DG_Product interface.
 * Safe to instantiate even when WooCommerce is inactive — all methods
 * return null/false/empty rather than throwing.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

class DG_WooCommerce_Product_Repository {

	/**
	 * Return a DG_Product for the given WC product ID.
	 *
	 * @param int $product_id
	 * @return DG_Product|null
	 */
	public function get_by_id( int $product_id ): ?DG_Product {
		if ( ! dg_is_woocommerce_active() ) {
			return null;
		}

		$wc_product = wc_get_product( $product_id );
		if ( ! $wc_product ) {
			return null;
		}

		return $this->wc_to_dg_product( $wc_product );
	}

	/**
	 * Return a DG_Product by post slug (post_name) under the product post type.
	 *
	 * @param string $slug
	 * @return DG_Product|null
	 */
	public function get_by_slug( string $slug ): ?DG_Product {
		if ( ! dg_is_woocommerce_active() ) {
			return null;
		}

		$post = get_page_by_path( $slug, OBJECT, 'product' );
		if ( ! $post ) {
			return null;
		}

		return $this->get_by_id( (int) $post->ID );
	}

	/**
	 * Check whether a slug exists as a WooCommerce product.
	 *
	 * @param string $slug
	 * @return bool
	 */
	public function exists( string $slug ): bool {
		if ( ! dg_is_woocommerce_active() ) {
			return false;
		}

		$post = get_page_by_path( $slug, OBJECT, 'product' );
		return null !== $post;
	}

	/**
	 * Convert a WC_Product into a DG_Product.
	 *
	 * @param WC_Product $wc_product
	 * @return DG_Product
	 */
	private function wc_to_dg_product( WC_Product $wc_product ): DG_Product {
		$image_id   = $wc_product->get_image_id();
		$gallery_ids = $wc_product->get_gallery_image_ids();

		$gallery_urls = array();
		foreach ( $gallery_ids as $img_id ) {
			$url = wp_get_attachment_image_url( $img_id, 'woocommerce_single' );
			if ( $url ) {
				$gallery_urls[] = $url;
			}
		}

		$sizes = array();
		$size_attr = $wc_product->get_attribute( 'pa_size' );
		if ( $size_attr ) {
			$sizes = array_map( 'trim', explode( ',', $size_attr ) );
			$sizes = array_filter( $sizes );
		}

		$categories = get_the_terms( $wc_product->get_id(), 'product_cat' );
		$category = '';
		$category_slug = '';
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			$first = $categories[0];
			$category = $first->name;
			$category_slug = $first->slug;
		}

		$badge = '';
		if ( $wc_product->is_on_sale() ) {
			$badge = __( 'Sale', 'dragon-glow' );
		} elseif ( $wc_product->is_featured() ) {
			$badge = __( 'New', 'dragon-glow' );
		}

		$rating = (float) $wc_product->get_average_rating();
		$review_count = (int) $wc_product->get_review_count();

		return new DG_Product(
			array(
				'id'               => $wc_product->get_id(),
				'slug'             => $wc_product->get_slug(),
				'name'             => $wc_product->get_name(),
				'price'            => (float) $wc_product->get_price(),
				'price_formatted'  => $wc_product->get_price_html(),
				'short_desc'       => $wc_product->get_short_description(),
				'description'      => $wc_product->get_description(),
				'category'         => $category,
				'category_slug'    => $category_slug,
				'image_url'        => $image_id
					? wp_get_attachment_image_url( $image_id, 'woocommerce_single' )
					: wc_placeholder_img_src(),
				'gallery_urls'     => $gallery_urls,
				'sizes'            => $sizes,
				'rating'           => $rating,
				'review_count'     => $review_count,
				'badge'            => $badge,
				'badge_pos'        => 'left',
				'source'           => 'woocommerce',
				'source_object'    => $wc_product,
			)
		);
	}
}
