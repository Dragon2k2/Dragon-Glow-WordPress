<?php
/**
 * Template part — Our Story / Commitment
 *
 * Center intro (eyebrow + headline) + 4 bento tiles. Mỗi tile có variant
 * (light / dark / lightest) quyết định background + text colour class,
 * span (1 hoặc 2 cột md+), và optional icon + icons row. Data từ
 * dg_our_story_commitment_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$commit = dg_our_story_commitment_data();

// Variant → class mapping. Theo style cũ trong template inline.
$variant_class = array(
	'light'   => 'bg-white text-on-surface border dg-story-card-border shadow-sm hover:shadow-md transition-shadow',
	'dark'    => 'dg-story-bento-dark text-white',
	'lightest'=> 'dg-story-bento-lightest border dg-story-card-border',
);
?>
<section class="py-section-gap px-5 md:px-16 max-w-container-max mx-auto">
	<div class="text-center mb-16 reveal-on-scroll">
		<p class="text-primary dg-story-eyebrow mb-4">
			<?php echo esc_html( $commit['eyebrow'] ); ?>
		</p>
		<h2 class="font-serif text-primary text-3xl md:text-4xl leading-tight">
			<?php echo esc_html( $commit['headline'] ); ?>
		</h2>
	</div>
	<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
		<?php foreach ( $commit['tiles'] as $tile ) :
			$span_int   = max( 1, (int) $tile['span'] );
			$span_class = 2 === $span_int ? 'md:col-span-2' : 'md:col-span-1';
			$body_class = 'dark' === $tile['variant'] ? 'text-white/80' : 'text-on-surface-variant';
			$title_class = 'dark' === $tile['variant'] ? 'text-white' : 'text-primary';
			$pad_class   = 'p-12 flex flex-col justify-center reveal-on-scroll';
			$variant_bg  = isset( $variant_class[ $tile['variant'] ] ) ? $variant_class[ $tile['variant'] ] : $variant_class['light'];
			?>
			<div class="<?php echo esc_attr( $span_class . ' ' . $variant_bg . ' ' . $pad_class ); ?>">
				<h3 class="font-serif text-xl mb-6 leading-tight <?php echo esc_attr( $title_class ); ?>">
					<?php echo esc_html( $tile['title'] ); ?>
				</h3>
				<p class="<?php echo esc_attr( $body_class ); ?> text-base leading-relaxed<?php echo ! empty( $tile['icons'] ) ? ' mb-8 max-w-lg' : ''; ?>">
					<?php echo esc_html( $tile['body'] ); ?>
				</p>
				<?php if ( ! empty( $tile['icons'] ) ) : ?>
					<div class="flex gap-4">
						<?php foreach ( $tile['icons'] as $icon_name ) : ?>
							<span class="material-symbols-outlined text-primary-container"><?php echo esc_html( $icon_name ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
