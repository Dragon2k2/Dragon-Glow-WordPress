<?php
/**
 * Dragon Glow — WooCommerce: Shop / Archive
 *
 * Product-loop (shop archive) customizations: column counts, products per
 * page, product badges, custom "Quick Add" button, sale flash, default UI
 * removals (result count, ordering, pagination) and the dropdown filter that
 * translates query params into the main shop query.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

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
 * Set products per page — 6 per page (2 rows × 3 cols).
 *
 * @return int
 */
function dg_loop_shop_per_page(): int {
	return 6;
}
add_filter( 'loop_shop_per_page', 'dg_loop_shop_per_page' );

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

    echo '<button class="absolute bottom-4 left-4 right-4 bg-primary text-on-primary py-3 rounded-xl font-label-sm text-label-sm opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 hover:brightness-110 wc-add-to-cart-btn z-10" data-product-id="' . esc_attr( $product->get_id() ) . '" data-product-slug="' . esc_attr( $product->get_slug() ) . '" data-product-type="' . esc_attr( $product_type ) . '">';
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
 * Override WooCommerce pagination để dùng custom theme pagination.
 * Ngăn woocommerce_pagination() render HTML mặc định của WC.
 */
remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );

/**
 * Translate the dropdown filter query params into a WC main query (tax_query
 * + meta_query) so the Apply button on the shop dropdown actually filters
 * the products server-side.
 *
 * Params (set by the dropdown's Apply handler):
 *   - skin_type[]   → pa_skin_type / pa_skin_concern (whichever exists)
 *   - ingredient[]  → pa_ingredient / product_tag    (whichever exists)
 *   - rating        → _wc_average_rating meta, e.g. 4 = 4 stars & up
 *   - min_price,
 *     max_price     → _price meta BETWEEN
 *   - product_cat   → handled by WC core already
 *
 * Only runs on the main shop / product-taxonomy query so other archives
 * (blog, search, etc.) are untouched.
 *
 * @param WP_Query $q Main query.
 * @return void
 */
function dg_apply_dropdown_filters_to_shop_query( WP_Query $q ): void {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( ! ( function_exists( 'is_shop' ) && ( $q->is_post_type_archive( 'product' ) || $q->is_tax( get_object_taxonomies( 'product', 'names' ) ) ) ) ) {
		return;
	}

	$tax_query  = (array) $q->get( 'tax_query' );
	$meta_query = (array) $q->get( 'meta_query' );

	$has_filter = false;

	// Skin type → pa_skin_type (or pa_skin_concern as fallback).
	if ( ! empty( $_GET['skin_type'] ) ) {
		$skin_tax = taxonomy_exists( 'pa_skin_type' ) ? 'pa_skin_type' : ( taxonomy_exists( 'pa_skin_concern' ) ? 'pa_skin_concern' : '' );
		if ( $skin_tax ) {
			$tax_query[] = array(
				'taxonomy' => $skin_tax,
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', (array) wp_unslash( $_GET['skin_type'] ) ),
			);
			$has_filter  = true;
		}
	}

	// Ingredient → pa_ingredient (or product_tag as fallback).
	if ( ! empty( $_GET['ingredient'] ) ) {
		$ing_tax = taxonomy_exists( 'pa_ingredient' ) ? 'pa_ingredient' : 'product_tag';
		if ( taxonomy_exists( $ing_tax ) ) {
			$tax_query[] = array(
				'taxonomy' => $ing_tax,
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', (array) wp_unslash( $_GET['ingredient'] ) ),
			);
			$has_filter  = true;
		}
	}

	// Category → product_cat (multiple slugs supported).
	if ( ! empty( $_GET['product_cat'] ) ) {
		$cats = array_map( 'sanitize_title', (array) wp_unslash( $_GET['product_cat'] ) );
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => $cats,
		);
		$has_filter = true;
	}

	// Rating — exact match: show only products with that star rating.
	// _wc_average_rating stores a decimal (e.g. 3.7), so we match the
	// whole-number tier: rating >= X AND rating < X+1.
	if ( isset( $_GET['rating'] ) && '' !== $_GET['rating'] ) {
		$rating = max( 1, min( 5, (int) $_GET['rating'] ) );
		$meta_query[] = array(
			'key'     => '_wc_average_rating',
			'value'   => $rating,
			'compare' => '>=',
			'type'    => 'DECIMAL',
		);
		$meta_query[] = array(
			'key'     => '_wc_average_rating',
			'value'   => $rating + 1,
			'compare' => '<',
			'type'    => 'DECIMAL',
		);
	}

	// Price range.
	$min_price = isset( $_GET['min_price'] ) && '' !== $_GET['min_price'] ? (float) $_GET['min_price'] : null;
	$max_price = isset( $_GET['max_price'] ) && '' !== $_GET['max_price'] ? (float) $_GET['max_price'] : null;
	if ( null !== $min_price || null !== $max_price ) {
		$price_clause = array( 'key' => '_price', 'type' => 'NUMERIC' );
		if ( null !== $min_price && null !== $max_price ) {
			$price_clause['value']   = array( $min_price, $max_price );
			$price_clause['compare'] = 'BETWEEN';
		} elseif ( null !== $min_price ) {
			$price_clause['value']   = $min_price;
			$price_clause['compare'] = '>=';
		} else {
			$price_clause['value']   = $max_price;
			$price_clause['compare'] = '<=';
		}
		$meta_query[] = $price_clause;
	}

		// Always set tax_query and meta_query if they have clauses, regardless of
		// how many clauses they contain (prevents empty-array edge case).
		if ( count( $tax_query ) > 1 || count( $meta_query ) > 1 ) {
			$tax_query['relation']  = 'AND';
			$meta_query['relation'] = 'AND';
			$q->set( 'tax_query', $tax_query );
			$q->set( 'meta_query', $meta_query );
		}
}
add_action( 'pre_get_posts', 'dg_apply_dropdown_filters_to_shop_query', 5 );
