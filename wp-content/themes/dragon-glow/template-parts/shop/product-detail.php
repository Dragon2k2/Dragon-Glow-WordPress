<?php
/**
 * Template Part: Shop — Product Detail
 *
 * Reusable product detail section: gallery + info + tabs + related products.
 * Included by:
 *   - template-shop.php  (when ?dg_product=slug is present)
 *   - template-mock-product.php  (replaces the inline markup block)
 *
 * Variables are passed via the WordPress-native $args parameter of get_template_part().
 * Expected keys in $args:
 *   - current_slug  (string)  sanitize_title of the product key
 *   - p            (array)   $mock_products_data[$current_slug]
 *   - related      (array)   4 other products keyed by slug
 *   - detail_shots (array)   URLs from dg_mock_detail_shots() — optional; falls back to helper
 *
 * Dependencies (must be included before this file):
 *   - inc/mock-products-data.php  (provides $mock_products_data, dg_mock_stars(), dg_mock_detail_shots())
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// ── Extract variables passed via get_template_part(..., $args) ────────────
$current_slug  = isset( $args['current_slug'] ) ? (string) $args['current_slug'] : '';
$p             = isset( $args['p'] )            ? (array)  $args['p']            : null;
$related       = isset( $args['related'] )      ? (array)  $args['related']      : array();
$detail_shots  = isset( $args['detail_shots'] ) ? (array)  $args['detail_shots'] : array();

// ── Guard: validate required product data ───────────────────────────────
// If called without proper args or slug not found, show a gentle error instead of crashing.
if ( empty( $current_slug ) || empty( $p ) ) :
?>
<main class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16 text-center">
	<div class="w-32 h-32 mx-auto bg-surface-container rounded-full flex items-center justify-center mb-6">
		<span class="material-symbols-outlined text-primary" style="font-size: 64px;">search_off</span>
	</div>
	<h2 class="font-headline text-headline-md text-primary mb-4">
		<?php esc_html_e( 'Product not found', 'dragon-glow' ); ?>
	</h2>
	<p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-8">
		<?php esc_html_e( 'The product you are looking for does not exist or may have been removed.', 'dragon-glow' ); ?>
	</p>
	<a class="btn-luxury bg-primary text-on-primary px-10 py-4 font-label-sm text-label-sm uppercase tracking-widest inline-block"
	   href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">
		<?php esc_html_e( 'Back to Shop', 'dragon-glow' ); ?>
	</a>
</main>
<?php
	return;
endif;

// ── Detail shots: use passed value or call helper if not provided ─────────
if ( empty( $detail_shots ) ) {
	$detail_shots = dg_mock_detail_shots( $current_slug );
}

// ── Prepend shop-card image (img_main) as the first thumbnail ──────────────
// This ensures the gallery opens with the same image the user saw on the
// shop card, creating a seamless visual transition from card → detail page.
// Deduplication prevents double-insertion if img_main is already in the list.
if ( ! empty( $p['img_main'] ) ) {
	$detail_shots = array_filter( $detail_shots, function( $url ) use ( $p ) {
		return basename( $url ) !== basename( $p['img_main'] );
	} );
	array_unshift( $detail_shots, $p['img_main'] );
	$detail_shots = array_values( $detail_shots );
}
?>
<main class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16" id="dg-product-detail-content">

	<!-- Breadcrumb: Home → Shop → Category → Product -->
	<nav class="flex items-center gap-2 mb-8 text-sm text-on-surface-variant flex-wrap" aria-label="Breadcrumb">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-primary transition-colors">
			<?php esc_html_e( 'Home', 'dragon-glow' ); ?>
		</a>
		<span class="material-symbols-outlined text-[14px]">chevron_right</span>
		<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>" class="hover:text-primary transition-colors">
			<?php esc_html_e( 'Shop', 'dragon-glow' ); ?>
		</a>
		<span class="material-symbols-outlined text-[14px]">chevron_right</span>
		<a href="<?php echo esc_url( add_query_arg( 'product_cat', $p['category_slug'], home_url( '/shop/' ) ) ); ?>" class="hover:text-primary transition-colors">
			<?php echo esc_html( $p['category'] ); ?>
		</a>
		<span class="material-symbols-outlined text-[14px]">chevron_right</span>
		<span class="text-primary font-bold"><?php echo esc_html( $p['name'] ); ?></span>
	</nav>

	<!-- ===== Product Section: gallery (left) + details (right) ===== -->
	<div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start mb-16">

			<!-- LEFT: Gallery -->
			<div class="lg:sticky lg:top-28">
				<!--
					Mobile (default):   thumbnails BELOW main image as horizontal row
					Desktop (md+):      grid — thumbnails LEFT, main image RIGHT
				-->
				<div class="flex flex-col-reverse md:grid md:grid-cols-[auto_1fr] md:items-start gap-4">

					<!-- Thumbnails — full-width row on mobile, left column on desktop -->
					<?php if ( ! empty( $detail_shots ) ) : ?>
					<div class="flex md:flex-col gap-3 overflow-x-auto md:overflow-y-auto pb-2 md:pb-0 md:max-h-[600px] custom-scrollbar">
						<?php foreach ( $detail_shots as $i => $shot_url ) : ?>
							<button type="button"
							        class="dg-thumb-btn thumbnail-btn flex-shrink-0 w-20 h-24 rounded-xl overflow-hidden border-2 transition-all <?php echo 0 === $i ? 'is-active border-primary ring-2 ring-primary-container/20' : 'border-outline-variant/30 opacity-60 hover:opacity-100'; ?>"
							        data-full="<?php echo esc_url( $shot_url ); ?>"
							        onclick="dgChangeImage(this)">
								<img src="<?php echo esc_url( $shot_url ); ?>"
								     alt="<?php echo esc_attr( $p['name'] ) . ' shot ' . ( $i + 1 ); ?>"
								     class="w-full h-full object-cover" />
							</button>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>

					<!-- Main image — fixed height, always fills available width -->
					<div class="rounded-3xl overflow-hidden relative group glass-card h-[400px] md:h-[600px]">
						<img id="dg-main-image"
						     src="<?php echo esc_url( ! empty( $detail_shots ) ? $detail_shots[0] : $p['img_main'] ); ?>"
						     alt="<?php echo esc_attr( $p['name'] ); ?>"
						     class="w-full h-full object-cover transition-opacity duration-500 cursor-zoom-in"
						     loading="eager"
						     onclick="dgOpenLightbox(this)" />
						<button type="button"
						        class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/70 backdrop-blur flex items-center justify-center text-primary hover:bg-white transition-all"
						        aria-label="<?php esc_attr_e( 'Zoom image', 'dragon-glow' ); ?>"
						        onclick="dgOpenLightbox(document.getElementById('dg-main-image'))">
							<span class="material-symbols-outlined text-[20px]">zoom_in</span>
						</button>
					</div>

				</div><!-- end flex/grid -->
			</div><!-- end sticky -->

		<!-- RIGHT: Details -->
		<div class="space-y-6" id="product-info">

			<!-- Badges row -->
			<?php if ( ! empty( $p['badges_extra'] ) ) : ?>
				<div class="flex flex-wrap gap-2">
					<?php foreach ( $p['badges_extra'] as $bi => $badge_label ) : ?>
						<span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
							<?php echo 0 === $bi ? 'bg-secondary-container text-on-secondary-container' : 'bg-primary-container/30 text-primary'; ?>">
							<?php echo esc_html( $badge_label ); ?>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- Title -->
			<h1 class="font-headline text-headline-lg text-primary leading-tight">
				<?php echo esc_html( $p['name'] ); ?>
			</h1>

			<!-- Rating -->
			<div class="flex items-center gap-3 flex-wrap">
				<?php echo dg_mock_stars( (float) $p['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper returns pre-escaped HTML ?>
				<span class="text-sm text-on-surface-variant">
					(<?php
						printf(
							esc_html( _n( '%d verified review', '%d verified reviews', (int) $p['review_count'], 'dragon-glow' ) ),
							(int) $p['review_count']
						);
					?>)
				</span>
			</div>

			<!-- Price -->
			<div class="dg-detail-price text-headline-md text-primary">
				<?php echo esc_html( $p['price'] ); ?>
			</div>

			<!-- Short description -->
			<p class="font-body text-body-md text-on-surface-variant leading-relaxed">
				<?php echo esc_html( $p['short_desc'] ); ?>
			</p>

			<!-- Size selector -->
			<?php if ( ! empty( $p['sizes'] ) ) : ?>
				<div>
					<p class="text-label-sm font-label-sm text-on-surface-variant mb-3 uppercase tracking-widest">
						<?php esc_html_e( 'Select Size', 'dragon-glow' ); ?>
					</p>
					<div class="flex flex-wrap gap-2">
						<?php foreach ( $p['sizes'] as $si => $sz ) : ?>
							<button type="button"
							        class="dg-size-btn px-4 py-2 rounded-xl border text-sm transition-all
									<?php echo 0 === $si ? 'is-active border-primary text-primary font-bold bg-primary-container/20' : 'border-outline-variant/30 text-on-surface-variant hover:border-primary'; ?>">
								<?php echo esc_html( $sz ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Quantity stepper -->
			<div>
				<p class="text-label-sm font-label-sm text-on-surface-variant mb-3 uppercase tracking-widest">
					<?php esc_html_e( 'Quantity', 'dragon-glow' ); ?>
				</p>
				<div class="dg-qty-stepper">
					<button type="button" class="dg-qty-btn" id="dg-qty-minus" aria-label="<?php esc_attr_e( 'Decrease', 'dragon-glow' ); ?>">
						<span class="material-symbols-outlined">remove</span>
					</button>
					<span class="dg-qty-display" id="dg-qty-display">1</span>
					<button type="button" class="dg-qty-btn" id="dg-qty-plus" aria-label="<?php esc_attr_e( 'Increase', 'dragon-glow' ); ?>">
						<span class="material-symbols-outlined">add</span>
					</button>
				</div>
			</div>

			<!-- CTAs -->
			<div class="flex flex-col sm:flex-row gap-4 pt-2">
				<button type="button"
				        class="btn-primary-glow flex-1 py-4 rounded-2xl font-bold uppercase tracking-widest text-sm flex items-center justify-center gap-2">
					<span class="material-symbols-outlined">shopping_bag</span>
					<?php esc_html_e( 'Add to Bag', 'dragon-glow' ); ?>
				</button>
				<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"
				   class="flex-1 py-4 rounded-2xl border-2 border-primary text-primary font-bold uppercase tracking-widest text-sm flex items-center justify-center hover:bg-primary/5 transition-all">
					<?php esc_html_e( 'Browse More', 'dragon-glow' ); ?>
				</a>
			</div>

			<!-- Trust badges -->
			<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-6 border-t border-outline-variant/20">
				<div class="flex items-center gap-3 text-sm text-on-surface-variant">
					<span class="material-symbols-outlined text-primary">local_shipping</span>
					<span><?php esc_html_e( 'Free Shipping on orders $75+', 'dragon-glow' ); ?></span>
				</div>
				<div class="flex items-center gap-3 text-sm text-on-surface-variant">
					<span class="material-symbols-outlined text-primary">verified</span>
					<span><?php esc_html_e( '30-Day Ritual Trial', 'dragon-glow' ); ?></span>
				</div>
			</div>
		</div>
	</div>

	<!-- ===== Tabs Section ===== -->
	<section class="mt-16 border-t border-outline-variant/20 pt-12" id="product-tabs">

		<!-- Tab nav -->
		<div class="flex border-b border-outline-variant/30 mb-8 overflow-x-auto custom-scrollbar">
			<button type="button" data-tab="description"
			        class="dg-tab-btn is-active px-6 py-4 text-primary font-bold border-b-2 border-tertiary-container whitespace-nowrap">
				<?php esc_html_e( 'Description', 'dragon-glow' ); ?>
			</button>
			<button type="button" data-tab="ingredients"
			        class="dg-tab-btn px-6 py-4 text-on-surface-variant font-medium hover:text-primary transition-colors whitespace-nowrap">
				<?php esc_html_e( 'Ingredients', 'dragon-glow' ); ?>
			</button>
			<button type="button" data-tab="how-to-use"
			        class="dg-tab-btn px-6 py-4 text-on-surface-variant font-medium hover:text-primary transition-colors whitespace-nowrap">
				<?php esc_html_e( 'How to Use', 'dragon-glow' ); ?>
			</button>
			<button type="button" data-tab="reviews"
			        class="dg-tab-btn px-6 py-4 text-on-surface-variant font-medium hover:text-primary transition-colors whitespace-nowrap">
				<?php
				printf(
					esc_html__( 'Reviews (%d)', 'dragon-glow' ),
					(int) $p['review_count']
				);
				?>
			</button>
		</div>

		<!-- Tab: Description -->
		<div class="dg-tab-pane max-w-4xl mx-auto" id="tab-description">
			<h3 class="font-headline text-headline-md text-primary mb-6 text-center">
				<?php esc_html_e( 'Unveil Your Inner Light', 'dragon-glow' ); ?>
			</h3>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
				<div class="rounded-3xl overflow-hidden shadow-lg h-80">
					<img src="<?php echo esc_url( $p['img_main'] ); ?>"
					     alt="<?php echo esc_attr( $p['name'] ); ?>"
					     class="w-full h-full object-cover" loading="lazy" />
				</div>
				<div class="space-y-4">
					<p class="text-body-lg text-on-surface-variant"><?php echo esc_html( $p['description'] ); ?></p>
					<?php if ( ! empty( $p['benefits'] ) ) : ?>
						<ul class="space-y-3">
							<?php foreach ( $p['benefits'] as $benefit ) : ?>
								<li class="flex items-center gap-3 text-on-surface">
									<span class="material-symbols-outlined text-primary">check_circle</span>
									<?php echo esc_html( $benefit ); ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Tab: Ingredients -->
		<div class="dg-tab-pane hidden max-w-4xl mx-auto" id="tab-ingredients">
			<div class="glass-card p-10 rounded-3xl">
				<h4 class="font-bold text-primary mb-4"><?php esc_html_e( 'Core Actives', 'dragon-glow' ); ?></h4>
				<p class="text-on-surface-variant leading-relaxed mb-6"><?php echo esc_html( $p['ingredients'] ); ?></p>
				<hr class="border-outline-variant/30 my-6" />
				<div class="flex flex-wrap gap-4 text-sm text-on-surface-variant">
					<span class="flex items-center gap-2">
						<span class="material-symbols-outlined text-primary text-base">eco</span>
						<?php esc_html_e( 'Vegan & Cruelty-Free', 'dragon-glow' ); ?>
					</span>
					<span class="flex items-center gap-2">
						<span class="material-symbols-outlined text-primary text-base">science</span>
						<?php esc_html_e( 'Dermatologist Tested', 'dragon-glow' ); ?>
					</span>
				</div>
			</div>
		</div>

		<!-- Tab: How to Use -->
		<div class="dg-tab-pane hidden max-w-4xl mx-auto" id="tab-how-to-use">
			<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
				<?php foreach ( $p['how_to_use'] as $step ) : ?>
					<div class="text-center p-6 bg-surface-container-low rounded-3xl">
						<div class="w-12 h-12 bg-primary-container flex items-center justify-center rounded-full mx-auto mb-4 text-primary font-bold text-lg">
							<?php echo esc_html( $step['step'] ); ?>
						</div>
						<h5 class="font-bold text-primary mb-2"><?php echo esc_html( $step['title'] ); ?></h5>
						<p class="text-on-surface-variant text-sm"><?php echo esc_html( $step['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Tab: Reviews -->
		<div class="dg-tab-pane hidden max-w-4xl mx-auto" id="tab-reviews">
			<div class="flex justify-between items-center bg-primary-container/10 p-6 rounded-2xl mb-6 flex-wrap gap-4">
				<div>
					<div class="font-headline text-headline-md text-primary">
						<?php echo esc_html( number_format( (float) $p['rating'], 1 ) ); ?> / 5.0
					</div>
					<div class="text-on-surface-variant text-sm">
						<?php
						printf(
							esc_html__( 'Based on %d reviews', 'dragon-glow' ),
							(int) $p['review_count']
						);
						?>
					</div>
				</div>
				<?php echo dg_mock_stars( (float) $p['rating'], '24px' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<p class="text-center text-on-surface-variant italic">
				<?php esc_html_e( 'Reviews are shown for verified purchases.', 'dragon-glow' ); ?>
				<?php
				// Safe WooCommerce guard — fallback to standard WP login URL when WC is inactive.
				$login_url = class_exists( 'WooCommerce' ) && function_exists( 'wc_get_page_permalink' )
					? wc_get_page_permalink( 'myaccount' )
					: wp_login_url( get_permalink() );
				?>
				<a href="<?php echo esc_url( $login_url ); ?>" class="text-primary hover:underline">
					<?php esc_html_e( 'Log in', 'dragon-glow' ); ?>
				</a>
				<?php esc_html_e( 'to leave a review.', 'dragon-glow' ); ?>
			</p>
		</div>
	</section>

	<!-- ===== Related Products ===== -->
	<?php if ( ! empty( $related ) ) : ?>
		<section class="mt-24" id="related-products">
			<h2 class="font-headline text-headline-md text-primary mb-12 text-center">
				<?php esc_html_e( 'Complete Your Ritual', 'dragon-glow' ); ?>
			</h2>
			<div class="grid grid-cols-2 lg:grid-cols-4 gap-gutter">
				<?php foreach ( $related as $rel_slug => $rp ) : ?>
					<article class="dg-related-card group">
						<?php
						$rel_shop_base = class_exists( 'WooCommerce' )
							? get_permalink( wc_get_page_id( 'shop' ) )
							: home_url( '/shop/' );
						$rel_url       = add_query_arg( 'dg_product', $rel_slug, $rel_shop_base );
						?>
						<a href="<?php echo esc_url( $rel_url ); ?>" class="block">
							<div class="relative aspect-[3/4] rounded-2xl overflow-hidden glass-card mb-4">
								<img src="<?php echo esc_url( $rp['img_main'] ); ?>"
								     alt="<?php echo esc_attr( $rp['name'] ); ?>"
								     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
								     loading="lazy" />
								<div class="dg-related-quick-view absolute inset-x-4 bottom-4">
									<span class="block w-full text-center bg-primary text-on-primary py-2 rounded-xl text-xs font-bold uppercase tracking-wider">
										<?php esc_html_e( 'Quick View', 'dragon-glow' ); ?>
									</span>
								</div>
							</div>
							<h3 class="font-headline text-lg text-on-surface group-hover:text-primary transition-colors mb-1">
								<?php echo esc_html( $rp['name'] ); ?>
							</h3>
							<p class="dg-related-price text-sm font-bold text-primary"><?php echo esc_html( $rp['price'] ); ?></p>
						</a>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

</main>

<!-- ===== Lightbox / Fullscreen Image Viewer ===== -->
<div id="dg-lightbox"
     class="fixed inset-0 z-[9999] flex items-center justify-center hidden"
     role="dialog"
     aria-modal="true"
     aria-label="<?php esc_attr_e( 'Image viewer', 'dragon-glow' ); ?>">
	<!-- Overlay click area -->
	<div id="dg-lightbox-overlay"
	     class="absolute inset-0 bg-black/90 backdrop-blur-xs cursor-zoom-out"
	     onclick="dgCloseLightbox()"></div>

	<!-- Close button -->
	<button type="button"
	        id="dg-lightbox-close"
	        class="absolute top-4 right-4 z-10 w-11 h-11 rounded-full bg-white/15 backdrop-blur hover:bg-white/30 flex items-center justify-center text-white transition-all focus:outline-none focus:ring-2 focus:ring-white/50"
	        aria-label="<?php esc_attr_e( 'Close', 'dragon-glow' ); ?>"
	        onclick="dgCloseLightbox()">
		<span class="material-symbols-outlined text-[22px] leading-none">close</span>
	</button>

	<!-- Prev arrow -->
	<button type="button"
	        id="dg-lightbox-prev"
	        class="absolute left-4 top-1/2 -translate-y-1/2 z-10 w-11 h-11 rounded-full bg-white/15 backdrop-blur hover:bg-white/30 flex items-center justify-center text-white transition-all focus:outline-none focus:ring-2 focus:ring-white/50 hidden md:flex"
	        aria-label="<?php esc_attr_e( 'Previous image', 'dragon-glow' ); ?>"
	        onclick="dgLightboxNav(-1)">
		<span class="material-symbols-outlined text-[24px] leading-none">chevron_left</span>
	</button>

	<!-- Next arrow -->
	<button type="button"
	        id="dg-lightbox-next"
	        class="absolute right-4 top-1/2 -translate-y-1/2 z-10 w-11 h-11 rounded-full bg-white/15 backdrop-blur hover:bg-white/30 flex items-center justify-center text-white transition-all focus:outline-none focus:ring-2 focus:ring-white/50 hidden md:flex"
	        aria-label="<?php esc_attr_e( 'Next image', 'dragon-glow' ); ?>"
	        onclick="dgLightboxNav(1)">
		<span class="material-symbols-outlined text-[24px] leading-none">chevron_right</span>
	</button>

	<!-- Image container -->
	<div id="dg-lightbox-image-container" class="relative z-10 max-w-[92vw] max-h-[92vh] flex items-center justify-center">
		<img id="dg-lightbox-image"
		     src=""
		     alt="<?php echo esc_attr( $p['name'] ); ?>"
		     class="max-w-full max-h-[92vh] object-contain rounded-2xl shadow-2xl select-none"
		     draggable="false" />
	</div>

	<!-- Mobile swipe hint: tap arrows on small screens -->
	<div class="absolute bottom-4 left-0 right-0 flex justify-center md:hidden pointer-events-none">
		<span class="text-white/50 text-xs tracking-wider uppercase">
			<?php esc_html_e( 'Swipe or use arrows', 'dragon-glow' ); ?>
		</span>
	</div>
</div>

<!-- ===== Sticky Bar (visible on scroll) ===== -->
<div id="dg-sticky-bar"
     class="fixed bottom-0 left-0 right-0 z-50 bg-white/70 backdrop-blur-2xl border-t border-white/30 shadow-2xl">
	<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-4 flex items-center gap-4">
		<div class="flex items-center gap-3 flex-1 min-w-0">
			<img src="<?php echo esc_url( $p['img_main'] ); ?>"
			     alt=""
			     class="w-12 h-12 rounded-xl object-cover flex-shrink-0" />
			<div class="min-w-0">
				<p class="font-bold text-sm text-primary truncate"><?php echo esc_html( $p['name'] ); ?></p>
				<p class="dg-sticky-price text-sm text-on-surface-variant"><?php echo esc_html( $p['price'] ); ?></p>
			</div>
		</div>
		<button type="button"
		        class="btn-primary-glow px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest whitespace-nowrap">
			<?php esc_html_e( 'Add to Bag', 'dragon-glow' ); ?>
		</button>
	</div>
</div>

<script>
(function () {
	'use strict';

	// ── Tab switching ──
	document.querySelectorAll('.dg-tab-btn').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var tabId = this.dataset.tab;

			document.querySelectorAll('.dg-tab-pane').forEach(function (p) { p.classList.add('hidden'); });
			document.querySelectorAll('.dg-tab-btn').forEach(function (b) {
				b.classList.remove('is-active', 'text-primary', 'font-bold', 'border-b-2', 'border-tertiary-container');
				b.classList.add('text-on-surface-variant', 'font-medium');
			});

			var pane = document.getElementById('tab-' + tabId);
			if (pane) { pane.classList.remove('hidden'); }

			this.classList.add('is-active', 'text-primary', 'font-bold', 'border-b-2', 'border-tertiary-container');
			this.classList.remove('text-on-surface-variant', 'font-medium');
		});
	});

	// ── Quantity stepper ──
	var qty = 1;
	var qtyDisplay = document.getElementById('dg-qty-display');
	var minusBtn = document.getElementById('dg-qty-minus');
	var plusBtn = document.getElementById('dg-qty-plus');
	if (minusBtn) {
		minusBtn.addEventListener('click', function () {
			if (qty > 1) { qty--; if (qtyDisplay) { qtyDisplay.textContent = qty; } }
		});
	}
	if (plusBtn) {
		plusBtn.addEventListener('click', function () {
			qty++; if (qtyDisplay) { qtyDisplay.textContent = qty; }
		});
	}

	// ── Size selector ──
	document.querySelectorAll('.dg-size-btn').forEach(function (btn) {
		btn.addEventListener('click', function () {
			document.querySelectorAll('.dg-size-btn').forEach(function (b) {
				b.classList.remove('is-active', 'border-primary', 'text-primary', 'font-bold', 'bg-primary-container/20');
				b.classList.add('border-outline-variant/30', 'text-on-surface-variant');
			});
			this.classList.add('is-active', 'border-primary', 'text-primary', 'font-bold', 'bg-primary-container/20');
			this.classList.remove('border-outline-variant/30', 'text-on-surface-variant');
		});
	});

	// ── Sticky bar on scroll ──
	var stickyBar = document.getElementById('dg-sticky-bar');
	if (stickyBar) {
		window.addEventListener('scroll', function () {
			if (window.scrollY > 600) {
				stickyBar.style.transform = 'translateY(0)';
			} else {
				stickyBar.style.transform = 'translateY(100%)';
			}
		}, { passive: true });
	}

	// ── Gallery thumbnail swap ──
	window.dgChangeImage = function (btn) {
		var fullSrc = btn.dataset.full;
		var mainImg = document.getElementById('dg-main-image');

		if (mainImg && fullSrc) {
			mainImg.style.opacity = '0';
			setTimeout(function () {
				mainImg.src = fullSrc;
				mainImg.style.opacity = '1';
			}, 150);
		}

		document.querySelectorAll('.thumbnail-btn').forEach(function (t) {
			t.classList.remove('is-active', 'border-primary', 'ring-2', 'ring-primary-container/20');
			t.classList.add('border-outline-variant/30', 'opacity-60');
		});

		btn.classList.remove('border-outline-variant/30', 'opacity-60');
		btn.classList.add('is-active', 'border-primary', 'ring-2', 'ring-primary-container/20');
	};

	// ── Lightbox / Fullscreen image viewer ──
	var lbEl      = document.getElementById('dg-lightbox');
	var lbImg     = document.getElementById('dg-lightbox-image');
	var lbPrev    = document.getElementById('dg-lightbox-prev');
	var lbNext    = document.getElementById('dg-lightbox-next');
	var lbImgs    = [];       // [{src, thumbBtn}]
	var lbIndex   = 0;        // current position in lbImgs
	var lbTrigger = null;      // element that opened the lightbox (for focus return)

	// Build gallery array from existing thumbnail buttons at DOM-ready time.
	document.querySelectorAll('.thumbnail-btn[data-full]').forEach(function (btn) {
		lbImgs.push({ src: btn.dataset.full, thumbBtn: btn });
	});

	// Show / hide prev/next arrows depending on gallery size.
	function lbUpdateArrows() {
		if (lbPrev) { lbImgs.length > 1 ? lbPrev.classList.remove('hidden') : lbPrev.classList.add('hidden'); }
		if (lbNext) { lbImgs.length > 1 ? lbNext.classList.remove('hidden') : lbNext.classList.add('hidden'); }
	}

	// Sync main gallery image + active thumbnail state to match lightbox position.
	// Reuses the same logic dgChangeImage performs so the two UIs stay in sync.
	function lbSyncGallery(idx) {
		var item = lbImgs[idx];
		if (!item) return;
		// Mirror what dgChangeImage does with the thumb button.
		if (item.thumbBtn) {
			document.querySelectorAll('.thumbnail-btn').forEach(function (t) {
				t.classList.remove('is-active', 'border-primary', 'ring-2', 'ring-primary-container/20');
				t.classList.add('border-outline-variant/30', 'opacity-60');
			});
			item.thumbBtn.classList.remove('border-outline-variant/30', 'opacity-60');
			item.thumbBtn.classList.add('is-active', 'border-primary', 'ring-2', 'ring-primary-container/20');
		}
		// Update main image via same opacity-crossfade technique.
		var mainImg = document.getElementById('dg-main-image');
		if (mainImg && mainImg.src !== item.src) {
			mainImg.style.opacity = '0';
			setTimeout(function () {
				mainImg.src = item.src;
				mainImg.style.opacity = '1';
			}, 150);
		}
	}

	// Open lightbox starting from the image shown in #dg-main-image.
	window.dgOpenLightbox = function (triggerEl) {
		if (!lbEl || !lbImg || lbImgs.length === 0) return;

		var startSrc = document.getElementById('dg-main-image').src;
		// Find which gallery item matches the currently displayed image.
		lbIndex   = lbImgs.findIndex(function (item) { return item.src === startSrc; });
		lbIndex   = lbIndex === -1 ? 0 : lbIndex;
		lbTrigger = triggerEl || null;

		lbImg.src = lbImgs[lbIndex].src;
		lbUpdateArrows();

		// Show overlay with fade-in.
		lbEl.style.opacity = '0';
		lbEl.classList.remove('hidden');
		// Force reflow so the opacity transition fires.
		lbEl.offsetWidth;
		lbEl.style.opacity = '1';

		// Move focus into modal for a11y.
		var closeBtn = document.getElementById('dg-lightbox-close');
		if (closeBtn) { closeBtn.focus(); }

		// Prevent background scroll.
		document.body.style.overflow = 'hidden';
	};

	// Navigate by `dir` (+1 or -1).
	window.dgLightboxNav = function (dir) {
		if (lbImgs.length <= 1) return;
		lbIndex = (lbIndex + dir + lbImgs.length) % lbImgs.length;
		lbImg.style.opacity = '0';
		setTimeout(function () {
			lbImg.src = lbImgs[lbIndex].src;
			lbImg.style.opacity = '1';
			lbSyncGallery(lbIndex);
		}, 200);
	};

	// Close lightbox — optionally sync the final gallery state.
	window.dgCloseLightbox = function (syncGallery) {
		if (!lbEl) return;
		lbEl.style.opacity = '0';
		setTimeout(function () {
			lbEl.classList.add('hidden');
			document.body.style.overflow = '';
			// Return focus to the element that triggered the modal.
			if (lbTrigger && lbTrigger.focus) { lbTrigger.focus(); }
			else {
				var mainImg = document.getElementById('dg-main-image');
				if (mainImg) { mainImg.focus(); }
			}
		}, 300);

		// If user navigated to a different image inside the lightbox, propagate
		// that choice back to the main gallery so both UIs agree on the same image.
		if (syncGallery !== false) {
			lbSyncGallery(lbIndex);
		}
	};

	// Keyboard navigation + ESC to close.
	document.addEventListener('keydown', function (e) {
		if (!lbEl || lbEl.classList.contains('hidden')) return;
		if (e.key === 'Escape')        { e.preventDefault(); dgCloseLightbox(); }
		if (e.key === 'ArrowLeft')     { e.preventDefault(); dgLightboxNav(-1); }
		if (e.key === 'ArrowRight')    { e.preventDefault(); dgLightboxNav(1); }
	});

	// Mobile touch swipe — update arrows after gallery is built.
	if (lbImgs.length > 1) {
		(function () {
			var startX = 0;
			var THRESHOLD = 50;
			var lbOverlay = document.getElementById('dg-lightbox-overlay');
			if (!lbOverlay) return;
			lbOverlay.addEventListener('touchstart', function (e) {
				startX = e.changedTouches[0].screenX;
			}, { passive: true });
			lbOverlay.addEventListener('touchend', function (e) {
				var dx = e.changedTouches[0].screenX - startX;
				if (Math.abs(dx) > THRESHOLD) {
					dgLightboxNav(dx < 0 ? 1 : -1);
				}
			}, { passive: true });
		})();
	}
})();
</script>
