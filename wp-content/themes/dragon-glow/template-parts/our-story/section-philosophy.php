<?php
/**
 * Template part — Our Story / Philosophy
 *
 * Left text block (eyebrow + headline + paragraphs) + right "Luminous
 * Heritage" card với quote. Data từ dg_our_story_philosophy_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$philo = dg_our_story_philosophy_data();
?>
<section id="philosophy" class="py-section-gap px-5 md:px-16 max-w-container-max mx-auto">
	<div class="grid grid-cols-1 md:grid-cols-2 gap-20 items-center">
		<div class="reveal-on-scroll">
			<p class="text-primary dg-story-eyebrow mb-6">
				<?php echo esc_html( $philo['eyebrow'] ); ?>
			</p>
			<h2 class="font-serif text-primary text-3xl md:text-4xl mb-8 leading-tight">
				<?php echo esc_html( $philo['headline'] ); ?>
			</h2>
			<div class="space-y-6 text-on-surface-variant text-base leading-relaxed">
				<?php foreach ( $philo['paragraphs'] as $p ) : ?>
					<p><?php echo esc_html( $p ); ?></p>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="relative reveal-on-scroll">
			<div class="dg-story-card-bg aspect-[4/5] flex items-center justify-center p-12 text-center dg-story-card-shadow">
				<div>
					<span class="material-symbols-outlined text-primary-container text-5xl mb-6">auto_awesome</span>
					<h3 class="font-serif text-primary text-2xl mb-4 leading-tight">
						<?php echo esc_html( $philo['card_title'] ); ?>
					</h3>
					<p class="text-on-surface-variant text-base italic leading-relaxed">
						&ldquo;<?php echo esc_html( $philo['card_quote'] ); ?>&rdquo;
					</p>
				</div>
			</div>
			<div class="dg-story-card-offset-border"></div>
		</div>
	</div>
</section>
