<?php
/**
 * Template part — Our Story / Alchemy
 *
 * Layout: 2 cột (lg+), trên mobile order ảnh trước. Cột phải = 3 numbered
 * ingredient tiles; cột trái = eyebrow + headline + ảnh. Data từ
 * dg_our_story_alchemy_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$alc = dg_our_story_alchemy_data();
?>
<section class="dg-story-section-white py-section-gap px-5 md:px-16">
	<div class="max-w-container-max mx-auto">
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
			<div class="reveal-on-scroll lg:order-2">
				<p class="text-primary dg-story-eyebrow mb-4">
					<?php echo esc_html( $alc['eyebrow'] ); ?>
				</p>
				<h2 class="font-serif text-primary text-3xl md:text-4xl mb-12 leading-tight">
					<?php echo esc_html( $alc['headline'] ); ?>
				</h2>
				<div class="space-y-10">
					<?php foreach ( $alc['items'] as $item ) : ?>
						<div class="flex gap-6 group">
							<span class="font-serif text-primary-container/30 text-5xl transition-colors group-hover:text-primary-container leading-none">
								<?php echo esc_html( $item['num'] ); ?>
							</span>
							<div>
								<h4 class="font-serif text-primary text-xl mb-2 leading-tight">
									<?php echo esc_html( $item['title'] ); ?>
								</h4>
								<p class="text-on-surface-variant text-base leading-relaxed">
									<?php echo esc_html( $item['body'] ); ?>
								</p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="reveal-on-scroll lg:order-1">
				<img alt="<?php echo esc_attr( $alc['image_alt'] ); ?>"
					 class="w-full h-auto rounded-sm shadow-2xl dg-story-img-ingredients transition-all duration-700"
					 src="<?php echo esc_url( $alc['image_url'] ); ?>" />
			</div>
		</div>
	</div>
</section>
