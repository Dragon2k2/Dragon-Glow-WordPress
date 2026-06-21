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

get_header();

// Notify the admin if the mock checkout page has not been set up.
if ( ! dg_mock_checkout_page_exists() && ! dg_is_woocommerce_active() ) :
	?>
	<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop pt-6">
		<div class="flex items-start gap-3 p-4 bg-primary-container/20 border border-primary/20 rounded-xl text-sm">
			<span class="material-symbols-outlined text-primary flex-shrink-0 mt-0.5">info</span>
			<p class="text-on-surface-variant">
				<strong class="text-primary"><?php esc_html_e( 'Checkout not configured.', 'dragon-glow' ); ?></strong>
				<?php
				printf(
					/* translators: %s: URL of the Pages admin screen */
					esc_html__( 'To enable Buy Now, please create a WordPress page with the slug %1$s and assign it the %2$s template.', 'dragon-glow' ),
					'<code>mock-checkout</code>',
					'<strong>Mock Checkout — Dragon Glow</strong>'
				);
				?>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>"
				   class="text-primary underline underline-offset-2 ml-1"
				   target="_blank">
					<?php esc_html_e( 'Create page', 'dragon-glow' ); ?>
				</a>
			</p>
		</div>
	</div>
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

	<?php else : ?>

		<!-- No-WooCommerce fallback: show mock products in magazine grid with pagination -->
		<?php
		// Load mock product data via the canonical loader (works from any scope, including AJAX).
		$mock_all_products = array_values( dg_get_mock_products_data() );
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
				$card_slug  = sanitize_title( $p['name'] );
				// Shadow WC product ID for Buy Now buttons (0 if WC is inactive).
				$card_wc_id = dg_is_woocommerce_active()
					? ( dg_get_or_create_mock_shadow_product( $card_slug ) ?: 0 )
					: 0;
				// Query-string URL — doesn't need rewrite, no conflict with WC.
				$shop_base  = dg_is_woocommerce_active()
					? get_permalink( wc_get_page_id( 'shop' ) )
					: home_url( '/shop/' );
				$card_url   = add_query_arg( 'dg_product', $card_slug, $shop_base );
				?>
			<div class="stagger-item group product-card-hover reveal-on-scroll active dg-product-card"
				 data-category="<?php echo esc_attr( $p['category_slug'] ); ?>"
				 data-rating="<?php echo esc_attr( $p['rating'] ); ?>"
				 style="transition-delay: <?php echo esc_attr( $delay_ms ); ?>ms;">
				<a href="<?php echo esc_url( $card_url ); ?>"
				   aria-label="<?php echo esc_attr( $p['name'] ); ?>"
				   class="dg-product-stretched-link">
				</a>
				<div class="relative aspect-[3/4] overflow-hidden bg-surface-container-low mb-6 dg-product-image">
					<img alt="<?php echo esc_attr( $p['name'] ); ?>"
						 class="w-full h-full object-cover dg-product-img"
						 src="<?php echo esc_url( $p['img_main'] ); ?>" />
					<?php if ( $p['badge'] ) : ?>
						<div class="<?php echo esc_attr( $pos_class ); ?>">
							<span class="<?php echo $is_dark ? 'dg-badge-right' : 'dg-badge-left'; ?>">
								<?php echo esc_html( $p['badge'] ); ?>
							</span>
						</div>
					<?php endif; ?>
				<button class="dg-add-to-ritual dg-quick-add inline-flex items-center justify-center gap-2"
						type="button"
						data-product-id="<?php echo esc_attr( $card_wc_id ); ?>"
						data-product-slug="<?php echo esc_attr( $card_slug ); ?>"
						data-original-label="<?php esc_attr_e( 'Add to Ritual', 'dragon-glow' ); ?>">
					<span class="material-symbols-outlined" style="font-size:16px;line-height:1;">shopping_bag</span>
					<span class="dg-quick-add__label"><?php esc_html_e( 'Add to Ritual', 'dragon-glow' ); ?></span>
				</button>
				</div>
				<a href="<?php echo esc_url( $card_url ); ?>" class="text-center px-2 dg-product-info-link">
					<?php echo dg_mock_stars( (float) $p['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<h3 class="dg-product-name">
						<?php echo esc_html( $p['name'] ); ?>
					</h3>
					<div class="dg-product-divider" aria-hidden="true"></div>
					<p class="dg-product-tags"><?php echo esc_html( $p['tags'] ); ?></p>
					<p class="dg-product-price"><?php echo esc_html( $p['price'] ); ?></p>
				</a>
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
	   Local overrides for the shop template.
	   All shared shop card styles have been moved to
	   assets/css/shop.css — no duplication here.
	   ===================================================== */
	:root {
		--luxury-bezier: cubic-bezier(0.16, 1, 0.3, 1);
	}

	/* Material Symbols — FILL variation (already in main.css;
	   repeated here to ensure it loads before JS renders stars) */
	.material-symbols-outlined {
		font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
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
