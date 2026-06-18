<?php
/**
 * Dragon Glow — Single Product Content
 * Override WooCommerce content-single-product.php
 * Layout: product section (gallery + details) + tabs + related + sticky bar.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product ) {
	return;
}

// Image data
$main_image_id = $product->get_image_id();
$gallery_ids   = $product->get_gallery_image_ids();
$all_image_ids = array_filter( array_merge( array( $main_image_id ), $gallery_ids ) );

$main_image_url = $main_image_id
	? wp_get_attachment_image_url( $main_image_id, 'woocommerce_single' )
	: wc_placeholder_img_src();

$rating         = (float) $product->get_average_rating();
$review_count   = (int) $product->get_review_count();
$is_featured    = $product->is_featured();
$is_vegan       = (bool) $product->get_attribute( 'vegan' );
$is_on_sale     = $product->is_on_sale();
$short_desc     = $product->get_short_description();
$size_attribute = $product->get_attribute( 'pa_size' );

// Detail shots từ local assets
$product_slug  = $product->get_slug();
$detail_dir    = get_template_directory() . '/assets/images/details/' . $product_slug . '/';
$detail_url    = get_template_directory_uri() . '/assets/images/details/' . $product_slug . '/';
$detail_exts   = array( 'webp', 'jpg', 'jpeg', 'png' );
$detail_shots  = array();

for ( $n = 1; $n <= 4; $n++ ) {
    foreach ( $detail_exts as $ext ) {
        $file = $detail_dir . 'shot' . $n . '.' . $ext;
        if ( file_exists( $file ) ) {
            $detail_shots[] = $detail_url . 'shot' . $n . '.' . $ext;
            break;
        }
    }
}

// ── Nguồn 1: Project gallery (ảnh local trong assets/images/details/{slug}/) ──
// ── Nguồn 2: WooCommerce gallery (Featured Image + Product Gallery từ wp-admin) ──
// Ưu tiên project gallery; fallback sang WooCommerce nếu không có project images.
// Chỉ render thumbnail khi có NHIỀU hơn 1 ảnh (vì 1 ảnh duy nhất thì không cần thumbnail).
$use_source = ! empty( $detail_shots ) ? 'project' : 'woocommerce';
$thumbnails  = array();  // Mảng chứa data cho thumbnails [{src, alt, full}, ...]

if ( 'project' === $use_source ) {
    // Nguồn 1: Project gallery
    foreach ( $detail_shots as $i => $shot_url ) {
        $thumbnails[] = array(
            'src'  => $shot_url,
            'alt'  => $product->get_name() . ' shot ' . ( $i + 1 ),
            'full' => $shot_url,
        );
    }
} elseif ( count( $all_image_ids ) > 1 ) {
    // Nguồn 2: WooCommerce gallery — chỉ fallback khi có từ 2 ảnh trở lên.
    foreach ( $all_image_ids as $i => $img_id ) {
        $thumb_url = wp_get_attachment_image_url( $img_id, 'woocommerce_single' );
        if ( ! $thumb_url ) {
            continue;
        }
        $thumbnails[] = array(
            'src'  => $thumb_url,
            'alt'  => trim( (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true ) )
                       ?: $product->get_name(),
            'full' => $thumb_url,
        );
    }
}

// Xác định URL ảnh main khởi tạo dựa trên nguồn đang dùng.
if ( 'project' === $use_source && ! empty( $detail_shots ) ) {
    $initial_main_url = $detail_shots[0];
} elseif ( ! empty( $all_image_ids ) ) {
    $first_img_id      = $all_image_ids[0];
    $initial_main_url = $first_img_id
        ? wp_get_attachment_image_url( $first_img_id, 'woocommerce_single' )
        : wc_placeholder_img_src();
} else {
    $initial_main_url = $main_image_url;
}
?>

<!-- =====================================================
     Product Section — gallery (left) + details (right)
     ===================================================== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start mb-16">

	<!-- LEFT: Gallery -->
	<div class="lg:sticky lg:top-28">
		<div class="flex flex-col-reverse md:flex-row gap-4">

			<!-- Thumbnails — cột bên trái trên desktop, hàng ngang dưới cùng trên mobile -->
			<?php if ( ! empty( $thumbnails ) ) : ?>
			<div class="flex md:flex-col gap-3 overflow-x-auto md:overflow-y-auto pb-2 md:pb-0 md:max-h-[600px] custom-scrollbar">
				<?php foreach ( $thumbnails as $i => $thumb ) : ?>
					<button type="button"
							class="dg-thumb-btn thumbnail-btn flex-shrink-0 w-20 h-24 rounded-xl overflow-hidden border-2 transition-all <?php echo 0 === $i ? 'is-active border-primary ring-2 ring-primary-container/20' : 'border-outline-variant/30 opacity-60 hover:opacity-100'; ?>"
							data-full="<?php echo esc_url( $thumb['full'] ); ?>"
							onclick="dgChangeImage(this)">
						<img src="<?php echo esc_url( $thumb['src'] ); ?>"
							 alt="<?php echo esc_attr( $thumb['alt'] ); ?>"
							 class="w-full h-full object-cover" />
					</button>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<!-- Main image -->
			<div class="flex-grow rounded-3xl overflow-hidden relative group glass-card">
				<img id="dg-main-image"
					 src="<?php echo esc_url( $initial_main_url ); ?>"
					 alt="<?php echo esc_attr( $product->get_name() ); ?>"
					 class="w-full h-[400px] md:h-[600px] object-cover transition-transform duration-500 group-hover:scale-105"
					 loading="eager" />

				<?php if ( $is_on_sale && $product->get_regular_price() && $product->get_sale_price() ) : ?>
					<?php
					$percentage = round( ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100 );
					?>
					<span class="absolute top-4 left-4 badge-new z-10">
						<?php printf( esc_html__( 'Sale %d%%', 'dragon-glow' ), (int) $percentage ); ?>
					</span>
				<?php endif; ?>

				<button type="button"
						class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/70 backdrop-blur flex items-center justify-center text-primary hover:bg-white transition-all"
						aria-label="<?php esc_attr_e( 'Zoom image', 'dragon-glow' ); ?>">
					<span class="material-symbols-outlined text-[20px]">zoom_in</span>
				</button>
			</div>

		</div><!-- end flex row -->
	</div><!-- end sticky -->

	<!-- RIGHT: Details -->
	<div class="space-y-6" id="product-info">

		<!-- Badges -->
		<?php get_template_part( 'template-parts/product/product-badges' ); ?>

		<?php if ( $is_vegan ) : ?>
			<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-primary-container/30 text-on-surface-variant text-label-sm font-label-sm">
				<span class="material-symbols-outlined text-[14px]">eco</span>
				<?php esc_html_e( 'Vegan', 'dragon-glow' ); ?>
			</span>
		<?php endif; ?>

		<!-- Title -->
		<h1 class="font-headline text-headline-lg text-primary leading-tight">
			<?php echo esc_html( $product->get_name() ); ?>
		</h1>

		<!-- Rating -->
		<div class="flex items-center gap-3 dg-stars">
			<div class="flex items-center gap-0.5">
				<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
					<?php $fill = ( $s <= $rating ) ? '1' : '0'; ?>
					<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' <?php echo esc_attr( $fill ); ?>">star</span>
				<?php endfor; ?>
			</div>
			<a href="#product-tabs" class="text-sm text-on-surface-variant hover:text-primary transition-colors">
				(<?php
					printf(
						esc_html( _n( '%d verified review', '%d verified reviews', $review_count, 'dragon-glow' ) ),
						(int) $review_count
					);
				?>)
			</a>
		</div>

		<!-- Price -->
		<div class="flex items-center gap-3">
			<span class="font-headline text-headline-md font-bold text-primary">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</span>
		</div>

		<!-- Short description -->
		<?php if ( $short_desc ) : ?>
			<div class="font-body text-body-md text-on-surface-variant leading-relaxed prose prose-sm max-w-none">
				<?php echo wp_kses_post( $short_desc ); ?>
			</div>
		<?php endif; ?>

		<!-- Size selector (attribute-based fallback) -->
		<?php if ( $size_attribute ) : ?>
			<div>
				<p class="text-label-sm font-label-sm text-on-surface-variant mb-3">
					<?php esc_html_e( 'Size', 'dragon-glow' ); ?>
				</p>
				<div class="flex flex-wrap gap-2">
					<?php
					$sizes = array_map( 'trim', explode( ',', $size_attribute ) );
					$first = true;
					foreach ( $sizes as $size ) :
						if ( ! $size ) {
							continue;
						}
					?>
						<button type="button"
						        class="dg-size-btn px-4 py-2 rounded-xl border text-sm transition-all <?php echo $first ? 'is-active border-primary text-primary font-bold' : 'border-outline-variant/30 text-on-surface-variant hover:border-primary'; ?>">
							<?php echo esc_html( $size ); ?>
						</button>
						<?php $first = false; ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Add to cart form (handles variations automatically) -->
		<div class="pt-2">
			<?php woocommerce_template_single_add_to_cart(); ?>
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

<!-- =====================================================
     Tabs (Description / Ingredients / How to Use / Reviews)
     ===================================================== -->
<?php get_template_part( 'template-parts/product/product-tabs' ); ?>

<!-- =====================================================
     Related Products — "Complete Your Ritual"
     ===================================================== -->
<?php
$related_ids = wc_get_related_products( $product->get_id(), 4 );
if ( $related_ids ) :
	$related_products = wc_get_products( array( 'include' => $related_ids, 'limit' => 4 ) );
?>
<section class="mt-section-gap" id="related-products">
	<h2 class="font-headline text-headline-md text-primary mb-12 text-center">
		<?php esc_html_e( 'Complete Your Ritual', 'dragon-glow' ); ?>
	</h2>
	<div class="grid grid-cols-2 lg:grid-cols-4 gap-gutter">
		<?php
		foreach ( $related_products as $rp ) :
			$rp_image_id = $rp->get_image_id();
			$rp_image    = $rp_image_id
				? wp_get_attachment_image_url( $rp_image_id, 'dg-product-card' )
				: wc_placeholder_img_src();
		?>
			<article class="dg-related-card group">
				<a href="<?php echo esc_url( $rp->get_permalink() ); ?>" class="block">
					<div class="relative aspect-[3/4] rounded-2xl overflow-hidden glass-card mb-4">
						<img src="<?php echo esc_url( $rp_image ); ?>"
						     alt="<?php echo esc_attr( $rp->get_name() ); ?>"
						     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
						     loading="lazy" />
						<div class="dg-related-quick-view absolute inset-x-4 bottom-4">
							<span class="block w-full text-center bg-primary text-on-primary py-2 rounded-xl font-label-sm text-label-sm">
								<?php esc_html_e( 'Quick View', 'dragon-glow' ); ?>
							</span>
						</div>
					</div>
					<h3 class="font-headline text-lg text-on-surface group-hover:text-primary transition-colors mb-1">
						<?php echo esc_html( $rp->get_name() ); ?>
					</h3>
					<p class="text-sm font-bold text-primary">
						<?php echo wp_kses_post( $rp->get_price_html() ); ?>
					</p>
				</a>
			</article>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<!-- =====================================================
     Sticky Add-to-Bag Bar (fixed bottom, shown on scroll)
     ===================================================== -->
<div id="product-info"
     class="fixed bottom-0 left-0 right-0 z-50 bg-white/70 backdrop-blur-2xl border-t border-white/30 shadow-2xl translate-y-full transition-transform duration-500"
     style="transition-timing-function: cubic-bezier(0.16, 1, 0.3, 1);">
	<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-4 flex items-center gap-4">
		<div class="flex items-center gap-3 flex-1 min-w-0">
			<img src="<?php echo esc_url( $main_image_url ); ?>"
			     alt=""
			     class="w-12 h-12 rounded-xl object-cover flex-shrink-0" />
			<div class="min-w-0">
				<p class="font-bold text-sm text-primary truncate">
					<?php echo esc_html( $product->get_name() ); ?>
				</p>
				<p class="text-sm text-on-surface-variant">
					<?php echo wp_kses_post( $product->get_price_html() ); ?>
				</p>
			</div>
		</div>
		<button type="button"
		        class="btn-primary-glow px-6 py-3 rounded-xl font-label-sm text-label-sm whitespace-nowrap"
		        onclick="document.querySelector('.single_add_to_cart_button')?.click()">
			<?php esc_html_e( 'Add to Bag', 'dragon-glow' ); ?>
		</button>
	</div>
</div>
