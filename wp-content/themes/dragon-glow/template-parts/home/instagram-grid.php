<?php
/**
 * Dragon Glow — Instagram Reels Section
 * Shoppable reel cards with creator info, quote, product CTA.
 *
 * Data from `template-parts/home/data-instagram-reels.php`
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Load data
require_once locate_template( 'template-parts/home/data-instagram-reels.php' );

$header = dg_instagram_reels_header_data();
$reels  = dg_instagram_reels_cards_data();
?>

<section class="w-full bg-surface-container-low py-16 lg:py-24">
	<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
		
		<!-- Header with Hashtag and Feed Link -->
		<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
			<div>
				<div class="flex items-center gap-2 text-secondary font-label-md text-label-md uppercase tracking-[0.2em] mb-2 font-semibold">
					<span class="material-symbols-outlined text-sm">photo_camera</span>
					<span><?php echo esc_html( $header['eyebrow'] ); ?></span>
				</div>
				<h2 class="font-headline-lg text-headline-lg text-on-surface font-medium">
					<?php echo esc_html( $header['headline'] ); ?>
				</h2>
				<p class="font-body-md text-body-md text-on-surface-variant mt-1">
					<?php echo esc_html( $header['description'] ); ?>
				</p>
			</div>
			<a class="inline-flex items-center gap-2 font-label-lg text-label-lg text-secondary uppercase tracking-wider hover:opacity-80 transition-opacity" href="<?php echo esc_url( $header['cta_url'] ); ?>">
				<span class="material-symbols-outlined text-[18px]">smart_display</span>
				<span><?php echo esc_html( $header['cta_label'] ); ?></span>
				<span class="material-symbols-outlined text-[16px]">arrow_forward</span>
			</a>
		</div>

		<!-- 4 Shoppable Reel Cards -->
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
			<?php foreach ( $reels as $index => $reel ) : ?>
			<div class="dg-home-instagram-reel group relative rounded-3xl overflow-hidden bg-surface-container-lowest shadow-md hover:shadow-2xl transition-all duration-300 flex flex-col">
				
				<!-- Image Container -->
				<div class="relative aspect-[9/14] w-full overflow-hidden">
					<img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
						 src="<?php echo esc_url( $reel['image_url'] ); ?>"
						 alt="<?php echo esc_attr( $reel['image_alt'] ); ?>"
						 loading="lazy" />
					
					<!-- Gradient Overlay -->
					<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/20"></div>
					
					<!-- Views Badge (top-left) -->
					<div class="absolute top-4 left-4 flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/40 backdrop-blur-md text-white font-label-sm text-label-sm">
						<span class="material-symbols-outlined text-[15px]">movie</span>
						<span><?php echo esc_html( $reel['views'] ); ?> <?php esc_html_e( 'Reels', 'dragon-glow' ); ?></span>
					</div>
					
					<!-- Play Icon (top-right) -->
					<div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-black/40 backdrop-blur-md text-white flex items-center justify-center">
						<span class="material-symbols-outlined text-[18px]">play_arrow</span>
					</div>
					
					<!-- Creator Info + Quote (bottom-middle) -->
					<div class="absolute bottom-20 left-4 right-4">
						<div class="flex items-center gap-1.5">
							<span class="font-label-sm text-[11px] text-primary-fixed uppercase tracking-wider font-semibold">
								<?php echo esc_html( $reel['creator'] ); ?>
							</span>
							<span class="text-[10px] text-white/70">•</span>
							<span class="font-label-sm text-[10px] text-white/80 flex items-center gap-1">
								<span class="material-symbols-outlined text-[11px]">graphic_eq</span>
								<?php echo esc_html( $reel['audio'] ); ?>
							</span>
						</div>
						<p class="font-headline-sm text-[16px] text-white leading-snug mt-1 font-normal">
							<?php echo esc_html( $reel['quote'] ); ?>
						</p>
					</div>
					
					<!-- Product Button (bottom) -->
					<div class="absolute bottom-4 left-4 right-4">
						<button class="dg-home-instagram-reel__product-button" type="button">
							<span class="product-name"><?php echo esc_html( $reel['product_name'] ); ?></span>
							<span class="product-price"><?php echo esc_html( $reel['product_price'] ); ?> • Add</span>
						</button>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
