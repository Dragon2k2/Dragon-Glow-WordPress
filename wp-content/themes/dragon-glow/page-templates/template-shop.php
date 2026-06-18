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
 * Product detail requests (?dg_product=slug) are handled by
 * dg_mock_product_template_redirect() in inc/setup.php (hooked to
 * template_redirect), which exits before WordPress ever loads this template.
 * This template only processes normal shop-page requests.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/mock-products-data.php';

get_header();

$page_title = get_the_title() ?: __( 'The Collection', 'dragon-glow' );
$shop_url   = class_exists( 'WooCommerce' )
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

	<?php if ( class_exists( 'WooCommerce' ) ) : ?>

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

	<?php else : ?>

		<!-- No-WooCommerce fallback: show mock products in magazine grid with pagination -->
		<?php
		/**
		 * Render star rating HTML (4, 4.5, hoặc 5 sao) — màu vàng đồng.
		 * Note: uses dg_mock_stars() internally for consistent star rendering.
		 * (phpcs:ignore covers the inline SVG / escaped-attribute patterns below.)
		 * @param float $rating Star rating value.
		 * @return string       HTML string.
		 */
		function dg_render_stars( float $rating ): string {
			$full    = (int) floor( $rating );
			$half    = ( $rating - $full ) >= 0.5;
			$star_f  = '<svg width="11" height="11" viewBox="0 0 14 14" aria-hidden="true"><polygon points="7,1 8.8,5.2 13.5,5.5 10,8.5 11.1,13 7,10.5 2.9,13 4,8.5 0.5,5.5 5.2,5.2" fill="#f1ca50"/></svg>';
			$star_h  = '<svg width="11" height="11" viewBox="0 0 14 14" aria-hidden="true"><defs><linearGradient id="hg"><stop offset="50%" stop-color="#f1ca50"/><stop offset="50%" stop-color="#f1ca50" stop-opacity="0.2"/></linearGradient></defs><polygon points="7,1 8.8,5.2 13.5,5.5 10,8.5 11.1,13 7,10.5 2.9,13 4,8.5 0.5,5.5 5.2,5.2" fill="url(#hg)"/></svg>';
			$star_e  = '<svg width="11" height="11" viewBox="0 0 14 14" aria-hidden="true"><polygon points="7,1 8.8,5.2 13.5,5.5 10,8.5 11.1,13 7,10.5 2.9,13 4,8.5 0.5,5.5 5.2,5.2" fill="#f1ca50" fill-opacity="0.25"/></svg>';
			$html    = '<div class="dg-star-rating" style="display:flex;align-items:center;justify-content:center;gap:3px;margin-bottom:6px;" role="img" aria-label="' . esc_attr( $rating ) . ' out of 5 stars">';
			for ( $i = 1; $i <= 5; $i++ ) {
				if ( $i <= $full ) {
					$html .= $star_f;
				} elseif ( $half && $i === $full + 1 ) {
					$html .= $star_h;
				} else {
					$html .= $star_e;
				}
			}
			$html .= '</div>';
			return $html;
		}

		// Pagination logic — reuse $mock_products_data from the shared include.
		$mock_all_products = array_values( $mock_products_data );
		$mock_per_page     = 6;
		$mock_total        = count( $mock_all_products );
		$mock_total_pages  = (int) ceil( $mock_total / $mock_per_page );
		$mock_current      = max( 1, (int) get_query_var( 'paged' ) ?: (int) get_query_var( 'page' ) );
		$mock_current      = min( $mock_current, $mock_total_pages );
		$mock_offset       = ( $mock_current - 1 ) * $mock_per_page;
		$mock_products_paged = array_slice( $mock_all_products, $mock_offset, $mock_per_page );

		// Truyền vào pagination template
		$GLOBALS['dg_mock_pagination'] = array(
			'current' => $mock_current,
			'total'   => $mock_total_pages,
		);
		?>
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-gutter gap-y-32" id="dg-product-grid">
			<?php foreach ( $mock_products_paged as $i => $p ) : ?>
				<?php
				$delay_ms   = $i * 100;
				$is_dark    = ( 'right' === $p['badge_pos'] );
				$pos_class  = $is_dark ? 'absolute top-4 right-4 z-10' : 'absolute top-4 left-4 z-10';
				// Query-string URL — không cần rewrite, không conflict với WC.
				$shop_base  = class_exists( 'WooCommerce' )
					? get_permalink( wc_get_page_id( 'shop' ) )
					: home_url( '/shop/' );
				$card_url   = add_query_arg( 'dg_product', sanitize_title( $p['name'] ), $shop_base );
				?>
				<div class="stagger-item group product-card-hover reveal-on-scroll active dg-product-card"
					 data-category="<?php echo esc_attr( $p['category_slug'] ); ?>"
					 data-rating="<?php echo esc_attr( $p['rating'] ); ?>"
					 style="transition-delay: <?php echo esc_attr( $delay_ms ); ?>ms;">
					<div class="relative aspect-[3/4] overflow-hidden bg-surface-container-low mb-6 dg-product-image">
						<a href="<?php echo esc_url( $card_url ); ?>" class="absolute inset-0 block" aria-label="<?php echo esc_attr( $p['name'] ); ?>">
							<img alt="<?php echo esc_attr( $p['name'] ); ?>"
								 class="w-full h-full object-cover dg-product-img"
								 src="<?php echo esc_url( $p['img_main'] ); ?>" />
						</a>
						<?php if ( $p['badge'] ) : ?>
							<div class="<?php echo esc_attr( $pos_class ); ?>">
								<span class="<?php echo $is_dark ? 'dg-badge-right' : 'dg-badge-left'; ?>">
									<?php echo esc_html( $p['badge'] ); ?>
								</span>
							</div>
						<?php endif; ?>
						<button class="dg-add-to-ritual absolute z-10 inline-flex items-center justify-center gap-2"
								type="button">
							<span class="material-symbols-outlined" style="font-size:16px;line-height:1;">shopping_bag</span>
							<span><?php esc_html_e( 'Add to Ritual', 'dragon-glow' ); ?></span>
						</button>
					</div>
					<div class="text-center px-2">
						<?php echo dg_render_stars( (float) $p['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<h3 class="dg-product-name">
							<a href="<?php echo esc_url( $card_url ); ?>" class="hover:text-primary transition-colors">
								<?php echo esc_html( $p['name'] ); ?>
							</a>
						</h3>
						<div class="dg-product-divider" aria-hidden="true"></div>
						<p class="dg-product-tags"><?php echo esc_html( $p['tags'] ); ?></p>
						<p class="dg-product-price"><?php echo esc_html( $p['price'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php get_template_part( 'template-parts/shop/pagination' ); ?>

	<?php endif; ?>

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

<style>
	/* =====================================================
	   Local styles for the shop template — luxury / glassy
	   ===================================================== */
	:root {
		--luxury-bezier: cubic-bezier(0.16, 1, 0.3, 1);
	}

	.material-symbols-outlined {
		font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
	}

	/* =====================================================
	   Product card — luxury redesign
	   (template-parts/shop/product-card.php + mock loop)
	   ===================================================== */
	.dg-product-card {
		cursor: pointer;
		transition: box-shadow 0.5s ease;
	}
	.dg-product-card:hover {
		box-shadow: 0 20px 60px rgba(115, 92, 0, 0.13);
	}

	/* Permanent gradient scrim on the image area (::after pseudo) */
	.dg-product-image {
		position: relative;
	}
	.dg-product-image::after {
		content: '';
		position: absolute;
		inset: 0;
		background: linear-gradient(to top, rgba(28, 27, 27, 0.55) 0%, transparent 45%);
		pointer-events: none;
		z-index: 1;
	}
	/* Image zoom on card hover (1s ease) */
	.dg-product-img {
		transform: scale(1);
		transition: transform 1s ease;
	}
	.dg-product-card:hover .dg-product-img {
		transform: scale(1.06);
	}

	/* Badges */
	.dg-badge-left,
	.dg-badge-right {
		display: inline-block;
		border-radius: 9999px;
		padding: 4px 12px;
		font-size: 11px;
		font-weight: 600;
		letter-spacing: 0.1em;
		text-transform: uppercase;
		line-height: 1.2;
	}
	.dg-badge-left {
		background-color: rgba(255, 255, 255, 0.6);
		backdrop-filter: blur(12px);
		-webkit-backdrop-filter: blur(12px);
		border: 1px solid rgba(255, 255, 255, 0.4);
		box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
		color: var(--color-primary, #735c00);
	}
	.dg-badge-right {
		background-color: var(--color-primary, #735c00);
		color: var(--color-on-primary, #ffffff);
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
	}

	/* CTA "Add to Ritual" — pill at bottom, hover-revealed */
	.dg-add-to-ritual {
		bottom: 1rem;
		left: 1rem;
		right: 1rem;
		background-color: var(--color-tertiary-container, #f1ca50);
		color: var(--color-on-tertiary-container, #6b5500);
		border-radius: 9999px;
		padding-top: 0.75rem;
		padding-bottom: 0.75rem;
		font-size: 11px;
		font-weight: 600;
		letter-spacing: 0.1em;
		text-transform: uppercase;
		opacity: 0;
		transform: translateY(16px);
		overflow: hidden;
		position: absolute;
		transition: opacity 0.35s ease, transform 0.35s ease;
	}

	/* Ánh sáng chạy qua (sheen) bằng ::before pseudo-element */
	.dg-add-to-ritual::before {
		content: '';
		position: absolute;
		top: 0;
		left: -100%;
		width: 60%;
		height: 100%;
		background: linear-gradient(
			120deg,
			transparent 0%,
			rgba(255, 255, 255, 0.55) 50%,
			transparent 100%
		);
		transform: skewX(-20deg);
		transition: left 0s;
		pointer-events: none;
	}

	/* Khi hover vào card: hiện button + giữ màu vàng đồng + chạy sheen */
	.dg-product-card:hover .dg-add-to-ritual {
		opacity: 1;
		transform: translateY(0);
		background-color: var(--color-tertiary-container, #f1ca50);
		color: var(--color-on-tertiary-container, #6b5500);
	}
	.dg-product-card:hover .dg-add-to-ritual::before {
		left: 160%;
		transition: left 0.65s ease;
	}

	/* Text block */
	.dg-product-name {
		margin: 0;
		font-family: 'Playfair Display', Georgia, serif;
		font-size: 22px;
		font-weight: 500;
		font-style: italic;
		color: #1c1b1b;
		line-height: 1.2;
		transition: color 0.3s ease;
	}
	.dg-product-name-link {
		color: inherit;
		text-decoration: none;
	}
	.dg-product-card:hover .dg-product-name,
	.dg-product-card:hover .dg-product-name-link {
		color: #735c00;
	}

	.dg-product-divider {
		width: 32px;
		height: 1px;
		background: rgba(115, 92, 0, 0.35);
		margin: 10px auto;
	}

	.dg-product-tags {
		margin: 0;
		font-family: 'Montserrat', system-ui, sans-serif;
		font-size: 11px;
		font-weight: 600;
		letter-spacing: 0.1em;
		text-transform: uppercase;
		color: #7f7663;
	}

	.dg-product-price {
		margin: 14px 0 0;
		font-family: 'Bodoni Moda', Georgia, serif;
		font-size: 16px;
		font-weight: 600;
		font-style: italic;
		color: #735c00;
	}

	.luxury-shadow {
		box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.04);
	}

	.reveal-on-scroll {
		opacity: 0;
		transform: translateY(20px);
		transition: all 1s var(--luxury-bezier);
	}
	.reveal-on-scroll.active {
		opacity: 1;
		transform: translateY(0);
	}

	.stagger-item:nth-child(even) {
		margin-top: 4rem;
	}
	@media (max-width: 768px) {
		.stagger-item:nth-child(even) {
			margin-top: 0;
		}
	}

	/* Nav-link underline animation (used by pagination numbers) */
	.nav-link-underline {
		position: relative;
	}
	.nav-link-underline::after {
		content: '';
		position: absolute;
		bottom: -2px;
		left: 0;
		width: 0;
		height: 1px;
		background-color: currentColor;
		transition: width 0.4s var(--luxury-bezier);
	}
	.nav-link-underline:hover::after {
		width: 100%;
	}

	/* Luxury button sheen */
	.btn-luxury {
		transition: all 0.4s var(--luxury-bezier);
		position: relative;
		overflow: hidden;
	}
	.btn-luxury::before {
		content: '';
		position: absolute;
		top: 0;
		left: -100%;
		width: 100%;
		height: 100%;
		background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
		transition: left 0.6s var(--luxury-bezier);
	}
	.btn-luxury:hover::before {
		left: 100%;
	}

	/* Product card hover */
	.product-card-hover {
		transition:
			transform 0.5s var(--luxury-bezier),
			box-shadow 0.5s var(--luxury-bezier);
	}
	.product-card-hover:hover {
		transform: translateY(-8px);
		box-shadow: 0 40px 80px -15px rgba(0, 0, 0, 0.1);
	}

	.filter-transition {
		transition: opacity 0.3s var(--luxury-bezier), transform 0.3s var(--luxury-bezier);
	}
	.filter-transition:hover {
		opacity: 0.7;
		transform: translateX(4px);
	}

	/* Custom scrollbar for filter dropdown / sidebar */
	.custom-scrollbar::-webkit-scrollbar {
		width: 6px;
	}
	.custom-scrollbar::-webkit-scrollbar-track {
		background: transparent;
	}
	.custom-scrollbar::-webkit-scrollbar-thumb {
		background: rgba(115, 92, 0, 0.2);
		border-radius: 9999px;
	}

	/* ── Filter dropdown panel (Material style) ─────────────
	   Anchored under the "Filter by Skin Concern" trigger. */
	.dg-filter-dropdown {
		position: absolute;
		top: calc(100% + 12px);
		right: 0;
		width: 360px;
		max-width: calc(100vw - 2 * 20px);
		background: var(--color-surface, #fbf9f5);
		border: 1px solid var(--color-outline-variant, #d4c2c2);
		box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.15),
		            0 8px 24px -8px rgba(123, 84, 85, 0.1);
		padding: 28px;
		z-index: 50;
		opacity: 0;
		transform: translateY(-8px) scale(0.98);
		pointer-events: none;
		transition: opacity 0.25s var(--luxury-bezier),
		            transform 0.25s var(--luxury-bezier);
	}
	.dg-filter-dropdown.is-open {
		opacity: 1;
		transform: translateY(0) scale(1);
		pointer-events: auto;
	}
	@media (max-width: 640px) {
		.dg-filter-dropdown {
			position: fixed;
			top: auto;
			bottom: 0;
			right: 0;
			left: 0;
			width: 100%;
			max-width: 100vw;
			max-height: 85vh;
			overflow-y: auto;
			padding: 24px;
			transform: translateY(100%);
		}
		.dg-filter-dropdown.is-open {
			transform: translateY(0);
		}
	}

	/* Caret / arrow on top of dropdown */
	.dg-filter-dropdown::before {
		content: '';
		position: absolute;
		top: -6px;
		right: 28px;
		width: 12px;
		height: 12px;
		background: var(--color-surface, #fbf9f5);
		border-top: 1px solid var(--color-outline-variant, #d4c2c2);
		border-left: 1px solid var(--color-outline-variant, #d4c2c2);
		transform: rotate(45deg);
	}
	@media (max-width: 640px) {
		.dg-filter-dropdown::before {
			display: none;
		}
	}

	/* Filter trigger open state */
	.dg-filter-trigger.is-open .dg-filter-trigger-text,
	.dg-filter-trigger.is-open .dg-filter-trigger-icon {
		color: var(--color-primary, #7b5455);
		border-color: var(--color-primary, #7b5455);
	}
	.dg-filter-trigger.is-open .dg-filter-trigger-icon {
		transform: rotate(180deg);
	}
</style>

<script>
(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {

		// ── Reveal on scroll (luxury fade-up) ────────────────
		if ('IntersectionObserver' in window) {
			var observer = new IntersectionObserver(function(entries) {
				entries.forEach(function(entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('active');
						entry.target.style.transitionDelay = '0ms';
						observer.unobserve(entry.target);
					}
				});
			}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

			document.querySelectorAll('.reveal-on-scroll').forEach(function(el) {
				observer.observe(el);
			});
		} else {
			document.querySelectorAll('.reveal-on-scroll').forEach(function(el) {
				el.classList.add('active');
			});
		}

		// ── Hero parallax ────────────────────────────────────
		var heroImg = document.getElementById('dg-shop-hero-img');
		if (heroImg) {
			window.addEventListener('scroll', function() {
				var scrolled = window.pageYOffset;
				heroImg.style.transform = 'translateY(' + (scrolled * 0.15) + 'px) scale(1.05)';
			}, { passive: true });
		}

		// ── Filter dropdown (Material style) ────────────────
		var filterTrigger = document.getElementById('dg-shop-filter-trigger');
		var filterDropdown = document.getElementById('dg-filter-dropdown');
		var filterClose = document.getElementById('dg-filter-close');

		function openFilterDropdown() {
			if (!filterDropdown || !filterTrigger) return;
			filterDropdown.classList.add('is-open');
			filterTrigger.classList.add('is-open');
			filterTrigger.setAttribute('aria-expanded', 'true');
		}
		function closeFilterDropdown() {
			if (!filterDropdown || !filterTrigger) return;
			filterDropdown.classList.remove('is-open');
			filterTrigger.classList.remove('is-open');
			filterTrigger.setAttribute('aria-expanded', 'false');
		}
		function toggleFilterDropdown() {
			if (!filterDropdown) return;
			if (filterDropdown.classList.contains('is-open')) {
				closeFilterDropdown();
			} else {
				openFilterDropdown();
			}
		}

		if (filterTrigger) {
			filterTrigger.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				toggleFilterDropdown();
			});
		}
		if (filterClose) {
			filterClose.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				closeFilterDropdown();
			});
		}

		// Close on outside click
		document.addEventListener('click', function(e) {
			if (!filterDropdown || !filterDropdown.classList.contains('is-open')) return;
			var inside = filterDropdown.contains(e.target);
			var onTrigger = filterTrigger && filterTrigger.contains(e.target);
			if (!inside && !onTrigger) {
				closeFilterDropdown();
			}
		});

		// Close on ESC
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' || e.keyCode === 27) {
				closeFilterDropdown();
			}
		});

		// ── Mobile filter sheet ──────────────────────────────
		var mobileToggle  = document.getElementById('dg-mobile-filter-toggle');
		var mobilePanel   = document.getElementById('dg-mobile-filter-panel');
		var mobileOverlay = document.getElementById('dg-filter-overlay');
		var mobileClose   = document.getElementById('dg-close-filter');

		function openMobileFilter() {
			if (!mobilePanel) return;
			mobilePanel.classList.remove('hidden');
			document.body.style.overflow = 'hidden';
		}
		function closeMobileFilter() {
			if (!mobilePanel) return;
			mobilePanel.classList.add('hidden');
			document.body.style.overflow = '';
		}
		if (mobileToggle)  mobileToggle.addEventListener('click', openMobileFilter);
		if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobileFilter);
		if (mobileClose)   mobileClose.addEventListener('click', closeMobileFilter);

		// ── Quick Add (mock — falls back to product link) ────
		document.querySelectorAll('.dg-quick-add').forEach(function(btn) {
			btn.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				var id  = this.dataset.productId;
				var url = '/?add-to-cart=' + id;
				window.location.href = url;
			});
		});

		// ── Active filter tags (used when WC is not active) ──
		var activeFilters = {
			category: null,
			skins: [],
			ingredients: [],
			minRating: 0
		};

		// Category pill
		document.querySelectorAll('[data-category-item]').forEach(function(item) {
			item.addEventListener('click', function() {
				var cat = this.dataset.categoryItem;
				document.querySelectorAll('[data-category-item]').forEach(function(el) {
					var isActive = el.dataset.categoryItem === cat;
					el.classList.toggle('text-primary', isActive);
					el.classList.toggle('font-semibold', isActive);
					el.classList.toggle('text-on-surface-variant', !isActive);
					var badge = el.querySelector('[data-badge]');
					if (badge) {
						badge.className = isActive
							? 'text-[10px] bg-tertiary-container text-on-tertiary-container px-2 py-0.5 rounded-full'
							: 'text-[10px] bg-secondary-container px-2 py-0.5 rounded-full';
					}
				});
				activeFilters.category = cat;
				filterProducts();
				updateActiveTags();
			});
		});

		// Rating
		document.querySelectorAll('[data-rating-filter]').forEach(function(radio) {
			radio.addEventListener('change', function() {
				activeFilters.minRating = this.checked ? parseInt(this.dataset.ratingFilter, 10) : 0;
				filterProducts();
				updateActiveTags();
			});
		});

		// Ingredient pill toggle
		document.querySelectorAll('[data-ingredient]').forEach(function(btn) {
			btn.addEventListener('click', function() {
				var ing = this.dataset.ingredient;
				var idx = activeFilters.ingredients.indexOf(ing);
				if (idx > -1) {
					activeFilters.ingredients.splice(idx, 1);
					this.className = 'px-3 py-1 bg-surface-container-high rounded-full text-label-sm font-label-sm hover:bg-primary-container transition-colors';
				} else {
					activeFilters.ingredients.push(ing);
					this.className = 'px-3 py-1 bg-primary-container text-on-primary-container rounded-full text-label-sm font-label-sm';
				}
				updateActiveTags();
			});
		});

		// Skin type checkbox
		document.querySelectorAll('[data-skin]').forEach(function(checkbox) {
			checkbox.addEventListener('change', function() {
				var skin = this.dataset.skin;
				var label = this.closest('label').querySelector('span');
				if (this.checked) {
					if (activeFilters.skins.indexOf(skin) === -1) activeFilters.skins.push(skin);
					if (label) label.classList.add('text-primary');
				} else {
					activeFilters.skins = activeFilters.skins.filter(function(s) { return s !== skin; });
					if (label) label.classList.remove('text-primary');
				}
				updateActiveTags();
			});
			if (checkbox.checked) {
				var label = checkbox.closest('label').querySelector('span');
				if (label) label.classList.add('text-primary');
			}
		});

		// Render active filter tags
		function updateActiveTags() {
			var container = document.getElementById('dg-active-filter-tags');
			if (!container) return;
			container.innerHTML = '';

			var tags = [];
			var catNames = {
				'serums': 'Serums & Oils',
				'cleansers': 'Cleansers',
				'moisturizers': 'Moisturizers',
				'sun-protection': 'Sun Protection'
			};
			if (activeFilters.category) {
				tags.push({ label: catNames[activeFilters.category] || activeFilters.category, key: 'category', value: activeFilters.category });
			}
			var skinNames = { 'dry':'Dry Skin', 'oily':'Oily Skin', 'sensitive':'Sensitive Skin', 'combination':'Combination' };
			activeFilters.skins.forEach(function(s) {
				tags.push({ label: skinNames[s] || s, key: 'skin', value: s });
			});
			var ingNames = { 'vitamin-c':'Vitamin C', 'retinol':'Retinol', 'hyaluronic':'Hyaluronic Acid', 'niacinamide':'Niacinamide' };
			activeFilters.ingredients.forEach(function(i) {
				tags.push({ label: ingNames[i] || i, key: 'ingredient', value: i });
			});
			if (activeFilters.minRating > 0) {
				tags.push({ label: activeFilters.minRating + '★ & Up', key: 'rating', value: activeFilters.minRating });
			}
			if (tags.length === 0) return;

			tags.forEach(function(tag) {
				var span = document.createElement('span');
				span.className = 'inline-flex items-center gap-2 bg-secondary-container text-on-secondary-container px-4 py-1.5 rounded-full text-label-sm font-label-sm';
				span.innerHTML = tag.label +
					'<span class="material-symbols-outlined text-[16px] cursor-pointer hover:rotate-90 transition-transform" ' +
					'data-remove-key="' + tag.key + '" data-remove-value="' + tag.value + '">close</span>';
				container.appendChild(span);
			});

			var clearBtn = document.createElement('button');
			clearBtn.className = 'text-label-sm font-label-sm text-primary underline underline-offset-4 decoration-tertiary-container hover:text-on-surface transition-colors';
			clearBtn.textContent = 'Clear All';
			clearBtn.addEventListener('click', clearAllFilters);
			container.appendChild(clearBtn);

			container.querySelectorAll('[data-remove-key]').forEach(function(icon) {
				icon.addEventListener('click', function() {
					removeFilter(this.dataset.removeKey, this.dataset.removeValue);
				});
			});
		}

		function removeFilter(key, value) {
			if (key === 'category') {
				activeFilters.category = null;
				document.querySelectorAll('[data-category-item]').forEach(function(el) {
					el.classList.remove('text-primary', 'font-semibold');
					el.classList.add('text-on-surface-variant');
					var badge = el.querySelector('[data-badge]');
					if (badge) badge.className = 'text-[10px] bg-secondary-container px-2 py-0.5 rounded-full';
				});
			} else if (key === 'skin') {
				activeFilters.skins = activeFilters.skins.filter(function(s) { return s !== value; });
				var cb = document.querySelector('[data-skin="' + value + '"]');
				if (cb) {
					cb.checked = false;
					var label = cb.closest('label') && cb.closest('label').querySelector('span');
					if (label) label.classList.remove('text-primary');
				}
			} else if (key === 'ingredient') {
				activeFilters.ingredients = activeFilters.ingredients.filter(function(i) { return i !== value; });
				var btn = document.querySelector('[data-ingredient="' + value + '"]');
				if (btn) btn.className = 'px-3 py-1 bg-surface-container-high rounded-full text-label-sm font-label-sm hover:bg-primary-container transition-colors';
			} else if (key === 'rating') {
				activeFilters.minRating = 0;
				document.querySelectorAll('[data-rating-filter]').forEach(function(r) { r.checked = false; });
			}
			filterProducts();
			updateActiveTags();
		}

		function clearAllFilters() {
			activeFilters.category = null;
			activeFilters.skins = [];
			activeFilters.ingredients = [];
			document.querySelectorAll('[data-category-item]').forEach(function(el) {
				el.classList.remove('text-primary', 'font-semibold');
				el.classList.add('text-on-surface-variant');
				var badge = el.querySelector('[data-badge]');
				if (badge) badge.className = 'text-[10px] bg-secondary-container px-2 py-0.5 rounded-full';
			});
			document.querySelectorAll('[data-skin]').forEach(function(cb) {
				cb.checked = false;
				var label = cb.closest('label') && cb.closest('label').querySelector('span');
				if (label) label.classList.remove('text-primary');
			});
			document.querySelectorAll('[data-ingredient]').forEach(function(btn) {
				btn.className = 'px-3 py-1 bg-surface-container-high rounded-full text-label-sm font-label-sm hover:bg-primary-container transition-colors';
			});
			document.querySelectorAll('[data-rating-filter]').forEach(function(r) { r.checked = false; });
			activeFilters.minRating = 0;
			filterProducts();
			updateActiveTags();
		}

		// Show/hide product cards (only for non-WC fallback)
		function filterProducts() {
			document.querySelectorAll('[data-category]').forEach(function(card) {
				var catMatch    = !activeFilters.category || card.dataset.category === activeFilters.category;
				var ratingMatch = activeFilters.minRating === 0 || parseInt(card.dataset.rating, 10) >= activeFilters.minRating;
				card.style.display = (catMatch && ratingMatch) ? '' : 'none';
			});
		}
		filterProducts();
		updateActiveTags();
	});
})();
</script>

<?php get_footer(); ?>
