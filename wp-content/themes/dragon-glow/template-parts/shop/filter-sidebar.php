<?php
/**
 * Dragon Glow — Filter Content (body of the dropdown panel)
 * Shared by the desktop dropdown (in the section header) and the
 * mobile filter sheet.
 *
 * NOT a sidebar anymore — just the inner content rendered inside a
 * `position: absolute` panel anchored to the "Filter by Skin Concern"
 * trigger. Mobile usage wraps it in a fixed sheet.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Determine shop URL
$shop_url = dg_is_woocommerce_active()
	? get_permalink( wc_get_page_id( 'shop' ) )
	: home_url( '/shop/' );

// Current category (WC only)
$current_category    = null;
$current_category_id = 0;
if ( dg_is_woocommerce_active() ) {
	$current_category    = get_queried_object();
	$current_category_id = is_product_category() ? $current_category->term_id : 0;
}

// Top-level product categories (WC only)
$categories = array();
if ( dg_is_woocommerce_active() ) {
	$categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => 0,
	) );
}

// Selected values from query string.
$selected_ingredients = isset( $_GET['ingredient'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	? array_map( 'sanitize_title', (array) wp_unslash( $_GET['ingredient'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	: array();
$selected_min_price = isset( $_GET['min_price'] ) ? max( 0, (float) $_GET['min_price'] ) : 0;   // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$selected_max_price = isset( $_GET['max_price'] ) ? max( 0, (float) $_GET['max_price'] ) : 200; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$selected_rating    = isset( $_GET['rating'] ) ? max( 1, min( 5, (int) $_GET['rating'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Dynamic WooCommerce taxonomies for filter data.
$ingredients = array();

if ( dg_is_woocommerce_active() ) {
	$ingredient_taxonomies = array( 'pa_ingredient', 'product_tag' );
	foreach ( $ingredient_taxonomies as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			)
		);
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$ingredients = $terms;
			break;
		}
	}
}
?>
<div class="dg-filter-content" id="dg-filter-sidebar">

	<!-- Categories Glass Card -->
	<section class="dg-filter-glass-card">
		<h3 class="dg-filter-glass-heading">
			<span class="material-symbols-outlined">category</span>
			<?php esc_html_e( 'Category', 'dragon-glow' ); ?>
		</h3>
		<ul class="dg-filter-glass-list">
			<?php if ( dg_is_woocommerce_active() && ! empty( $categories ) ) : ?>
				<?php foreach ( $categories as $category ) : ?>
					<?php
					$is_active = $current_category_id === $category->term_id;
					$item_class = $is_active ? 'dg-filter-glass-item dg-filter-glass-item--active' : 'dg-filter-glass-item';
					?>
					<li class="<?php echo esc_attr( $item_class ); ?>" 
					    data-category-item="<?php echo esc_attr( $category->slug ); ?>" 
					    data-category-label="<?php echo esc_attr( $category->name ); ?>">
						<span class="dg-filter-glass-item-label"><?php echo esc_html( $category->name ); ?></span>
						<span class="dg-filter-glass-item-count"><?php echo esc_html( $category->count ); ?></span>
					</li>
				<?php endforeach; ?>
			<?php else : ?>
				<?php
				$fallback_categories = array(
					array( 'name' => __( 'Cleansers', 'dragon-glow' ),     'key' => 'cleansers',      'count' => 12 ),
					array( 'name' => __( 'Serums & Oils', 'dragon-glow' ),'key' => 'serums',         'count' => 24 ),
					array( 'name' => __( 'Moisturizers', 'dragon-glow' ), 'key' => 'moisturizers',   'count' => 18 ),
					array( 'name' => __( 'Sun Protection', 'dragon-glow' ),'key' => 'sun-protection', 'count' => 8 ),
				);
				foreach ( $fallback_categories as $cat ) :
					$is_active  = $cat['key'] === 'serums';
					$item_class = $is_active ? 'dg-filter-glass-item dg-filter-glass-item--active' : 'dg-filter-glass-item';
				?>
					<li class="<?php echo esc_attr( $item_class ); ?>" 
					    data-category-item="<?php echo esc_attr( $cat['key'] ); ?>" 
					    data-category-label="<?php echo esc_attr( wp_strip_all_tags( $cat['name'] ) ); ?>">
						<span class="dg-filter-glass-item-label"><?php echo wp_kses_post( $cat['name'] ); ?></span>
						<span class="dg-filter-glass-item-count"><?php echo esc_html( $cat['count'] ); ?></span>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
	</section>

	<!-- Price Range Glass Card -->
	<section class="dg-filter-glass-card">
		<h3 class="dg-filter-glass-heading">
			<span class="material-symbols-outlined">payments</span>
			<?php esc_html_e( 'Price', 'dragon-glow' ); ?>
		</h3>
		<div class="dg-filter-glass-price">
			<input type="range"
				   id="price-range"
				   min="0"
				   max="200"
				   value="<?php echo (int) $selected_max_price; ?>"
				   step="10"
				   class="dg-filter-glass-slider" />
			<div class="dg-filter-glass-price-labels">
				<span>$0</span>
				<span>$<span id="price-max-label"><?php echo (int) $selected_max_price; ?></span></span>
			</div>
		</div>
	</section>

	<!-- Apply / Reset Actions -->
	<div class="dg-filter-glass-actions">
		<button type="button"
				id="dg-filter-reset"
				class="dg-filter-glass-btn dg-filter-glass-btn--secondary">
			<?php esc_html_e( 'Reset', 'dragon-glow' ); ?>
		</button>
		<button type="button"
				id="dg-filter-apply"
				class="dg-filter-glass-btn dg-filter-glass-btn--primary">
			<?php esc_html_e( 'Apply Filters', 'dragon-glow' ); ?>
		</button>
	</div>

</div>
