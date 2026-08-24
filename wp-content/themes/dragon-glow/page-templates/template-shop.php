<?php
/**
 * Template Name: Shop — Dragon Glow
 * Product listing page — Immersive narrative layout
 * Matches Stitch design: shop-page1 / shop-page2
 *
 * The Curated Glow section uses a 3-column magazine grid that takes the
 * full container width. The filter UI is exposed as a Material-style
 * dropdown panel anchored to the section header (no fixed left sidebar).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Show admin notice if WooCommerce is not set up (no cart/checkout available).
if ( ! dg_is_woocommerce_active() ) :
	?>
	<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop pt-6">
		<div class="flex items-start gap-3 p-4 bg-primary-container/20 border border-primary/20 rounded-xl text-sm">
			<span class="material-symbols-outlined text-primary flex-shrink-0 mt-0.5">info</span>
			<p class="text-on-surface-variant">
				<strong class="text-primary"><?php esc_html_e( 'WooCommerce is not active.', 'dragon-glow' ); ?></strong>
				<?php esc_html_e( 'Please enable WooCommerce to enable cart and checkout functionality.', 'dragon-glow' ); ?>
			</p>
		</div>
	</div>
	<?php
endif;

// Show admin notice if WooCommerce checkout is not set up.
if ( ! empty( $_GET['dg_checkout_unavailable'] ) ) :
	?>
	<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop pt-6" id="dg-checkout-unavailable-banner">
		<div class="flex items-start gap-3 p-4 bg-primary-container/20 border border-primary/20 rounded-xl text-sm">
			<span class="material-symbols-outlined text-primary flex-shrink-0 mt-0.5">error</span>
			<p class="text-on-surface-variant">
				<strong class="text-primary"><?php esc_html_e( 'Checkout not available.', 'dragon-glow' ); ?></strong>
				<?php esc_html_e( 'Please ensure WooCommerce is properly configured with a checkout page set.', 'dragon-glow' ); ?>
			</p>
		</div>
	</div>
	<script>
		(function () {
			var banner = document.getElementById('dg-checkout-unavailable-banner');
			if (banner) {
				var url = new URL(window.location);
				if (url.searchParams.has('dg_checkout_unavailable')) {
					url.searchParams.delete('dg_checkout_unavailable');
					history.replaceState({}, '', url.toString());
				}
			}
		})();
	</script>
	<?php
endif;

	$page_title = get_the_title() ?: __( 'The Collection', 'dragon-glow' );
	$shop_url   = dg_is_woocommerce_active()
		? get_permalink( wc_get_page_id( 'shop' ) )
		: home_url( '/shop/' );
	?>

<!-- 1. Immersive narrative hero (matches Stitch sample) -->
<?php get_template_part( 'template-parts/shop/hero' ); ?>

<!-- 2. Curated Glow section: header + product grid + pagination -->
<section class="py-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto" id="products">

	<?php get_template_part( 'template-parts/shop/section-header' ); ?>

	<!-- Active filter tags (driven by URL params) -->
	<?php get_template_part( 'template-parts/shop/active-filters' ); ?>

	<?php if ( dg_is_woocommerce_active() ) : ?>

		<?php
		// Sort dropdown + result count row
		$total = wc_get_loop_prop( 'total' );
		?>
		<div class="flex flex-wrap items-center justify-between gap-4 mb-8 pb-6 border-b border-outline-variant">
			<p class="text-on-surface-variant text-body-sm">
				<?php
				if ( $total ) {
					printf(
						esc_html( _nx( '%d Product', '%d Products', $total, 'shop product count', 'dragon-glow' ) ),
						esc_html( $total )
					);
				} else {
					esc_html_e( 'No Products', 'dragon-glow' );
				}
				?>
			</p>
			<form class="woocommerce-ordering" method="get">
				<label class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-widest mr-3 hidden sm:inline-block"
					   for="dg-sort">
					<?php esc_html_e( 'Sort By', 'dragon-glow' ); ?>
				</label>
				<select class="bg-transparent border-b border-outline py-2 pr-8 focus:ring-0 focus:border-primary text-body-md cursor-pointer"
						id="dg-sort"
						name="orderby"
						onchange="this.form.submit()">
					<option value="popularity"   <?php selected( isset( $_GET['orderby'] ) ? $_GET['orderby'] : '', 'popularity' ); ?>><?php esc_html_e( 'Popularity', 'dragon-glow' ); ?></option>
					<option value="rating"       <?php selected( isset( $_GET['orderby'] ) ? $_GET['orderby'] : '', 'rating' ); ?>><?php esc_html_e( 'Average rating', 'dragon-glow' ); ?></option>
					<option value="date"         <?php selected( isset( $_GET['orderby'] ) ? $_GET['orderby'] : '', 'date' ); ?>><?php esc_html_e( 'Newest Arrivals', 'dragon-glow' ); ?></option>
					<option value="price"        <?php selected( isset( $_GET['orderby'] ) ? $_GET['orderby'] : '', 'price' ); ?>><?php esc_html_e( 'Price: Low to High', 'dragon-glow' ); ?></option>
					<option value="price-desc"   <?php selected( isset( $_GET['orderby'] ) ? $_GET['orderby'] : '', 'price-desc' ); ?>><?php esc_html_e( 'Price: High to Low', 'dragon-glow' ); ?></option>
				</select>
				<?php
				// Preserve other query vars when submitting form
				foreach ( $_GET as $name => $value ) {
					if ( 'orderby' === $name || ! is_scalar( $value ) ) {
						continue;
					}
					echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
				}
				?>
			</form>
		</div>

		<?php
		// Custom WP_Query cho WooCommerce products với pagination
		$paged       = max( 1, (int) ( get_query_var( 'paged' ) ?: get_query_var( 'page' ) ) );
		$per_page    = 12;
		$orderby_raw = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'date';

		// Map WC orderby sang WP_Query args
		$orderby_map = array(
			'popularity'  => array( 'orderby' => 'meta_value_num', 'meta_key' => 'total_sales', 'order' => 'DESC' ),
			'rating'      => array( 'orderby' => 'meta_value_num', 'meta_key' => '_wc_average_rating', 'order' => 'DESC' ),
			'date'        => array( 'orderby' => 'date', 'order' => 'DESC' ),
			'price'       => array( 'orderby' => 'meta_value_num', 'meta_key' => '_price', 'order' => 'ASC' ),
			'price-desc'  => array( 'orderby' => 'meta_value_num', 'meta_key' => '_price', 'order' => 'DESC' ),
		);
		$order_args  = isset( $orderby_map[ $orderby_raw ] ) ? $orderby_map[ $orderby_raw ] : $orderby_map['date'];

		// Merge filter query vars (product_cat, product_tag, s, on_sale, min/max price)
		$tax_query  = array( 'relation' => 'AND' );
		$meta_query = array( 'relation' => 'AND' );

		if ( ! empty( $_GET['product_cat'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_text_field', (array) wp_unslash( $_GET['product_cat'] ) ),
			);
		}
		if ( ! empty( $_GET['product_tag'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_tag',
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_text_field', (array) wp_unslash( $_GET['product_tag'] ) ),
			);
		}
		if ( ! empty( $_GET['on_sale'] ) ) {
			$meta_query[] = array(
				'key'     => '_sale_price',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC',
			);
		}
		$min_price = isset( $_GET['min_price'] ) ? (float) $_GET['min_price'] : null;
		$max_price = isset( $_GET['max_price'] ) ? (float) $_GET['max_price'] : null;
		if ( null !== $min_price || null !== $max_price ) {
			$price_clause = array( 'key' => '_price', 'type' => 'NUMERIC' );
			if ( null !== $min_price ) {
				$price_clause['value']   = $min_price;
				$price_clause['compare'] = ( null !== $max_price ) ? 'BETWEEN' : '>=';
			}
			if ( null !== $max_price ) {
				$price_clause['value']   = ( null !== $min_price ) ? array( $min_price, $max_price ) : $max_price;
				$price_clause['compare'] = ( null !== $min_price ) ? 'BETWEEN' : '<=';
			}
			$meta_query[] = $price_clause;
		}

		$product_args = array_merge( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $paged,
		), $order_args );
		if ( count( $tax_query ) > 1 ) {
			$product_args['tax_query'] = $tax_query;
		}
		if ( count( $meta_query ) > 1 ) {
			$product_args['meta_query'] = $meta_query;
		}
		// Search by title/content (skip if searching products with empty result expected)
		if ( ! empty( $_GET['s'] ) ) {
			$product_args['s'] = sanitize_text_field( wp_unslash( $_GET['s'] ) );
		}

		$product_query = new WP_Query( $product_args );
		?>

		<?php if ( $product_query->have_posts() ) : ?>

			<!-- Magazine staggered grid: 1/2/3 columns (full container width) -->
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-gutter gap-y-32 dg-shop-grid" id="dg-product-grid">
				<?php
				$delay = 0;
				while ( $product_query->have_posts() ) :
					$product_query->the_post();
					set_query_var( 'dg_product_delay', $delay );
					get_template_part( 'template-parts/shop/product-card' );
					$delay += 100;
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php
			// Truyền $product_query vào pagination template qua global
			$GLOBALS['dg_product_query'] = $product_query;
			get_template_part( 'template-parts/shop/pagination' );
			?>

		<?php else : ?>

			<div class="text-center py-24">
				<div class="w-32 h-32 mx-auto bg-surface-container rounded-full flex items-center justify-center mb-6">
					<span class="material-symbols-outlined text-primary" style="font-size: 64px;">search</span>
				</div>
				<h2 class="font-headline text-headline-md text-primary mb-4">
					<?php esc_html_e( 'No products found', 'dragon-glow' ); ?>
				</h2>
				<p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-8">
					<?php esc_html_e( 'We could not find any products matching your selection. Try adjusting your filters.', 'dragon-glow' ); ?>
				</p>
				<a class="btn-luxury bg-primary text-on-primary px-10 py-4 font-label-sm text-label-sm uppercase tracking-widest inline-block"
				   href="<?php echo esc_url( $shop_url ); ?>">
					<?php esc_html_e( 'Clear Filters', 'dragon-glow' ); ?>
				</a>
			</div>

		<?php endif; ?>

	<?php endif; // WC active ?>

</section>

<!-- 3. Mobile filter sheet (uses the same filter sidebar content) -->
<div id="dg-mobile-filter-panel" class="fixed inset-0 z-[200] hidden">
	<div class="absolute inset-0 bg-inverse-surface/50" id="dg-filter-overlay"></div>
	<div class="absolute right-0 top-0 bottom-0 w-80 bg-surface overflow-y-auto p-6">
		<div class="flex justify-between items-center mb-6">
			<h3 class="font-headline text-xl text-primary"><?php esc_html_e( 'Filters', 'dragon-glow' ); ?></h3>
			<button type="button" id="dg-close-filter" class="p-2 hover:bg-surface-container rounded-full transition-colors">
				<span class="material-symbols-outlined">close</span>
			</button>
		</div>
		<?php get_template_part( 'template-parts/shop/filter-sidebar' ); ?>
	</div>
</div>

<!-- 4. Ingredient philosophy section -->
<?php get_template_part( 'template-parts/shop/philosophy' ); ?>

<!-- 5. Brand rituals section (AM / PM) -->
<?php get_template_part( 'template-parts/shop/rituals' ); ?>

<?php get_footer(); ?>
