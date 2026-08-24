<?php
/**
 * Template part — Careers / Section 7: How we hire
 *
 * 4-col grid (mobile 1col) × STEP 01-04, mỗi step: step label caps + title + body.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$steps = dg_careers_hire_steps_data();
?>
<section class="dg-careers-hire" data-sr>
	<h2 class="dg-careers-section-title dg-careers-section-title--center dg-careers-section-title--emphasis"><?php esc_html_e( 'How We Hire', 'dragon-glow' ); ?></h2>
	<?php if ( ! empty( $steps ) ) : ?>
		<div class="dg-careers-hire-grid">
			<?php foreach ( $steps as $step ) : ?>
				<div class="dg-careers-hire-step">
					<span class="dg-careers-hire-step-label"><?php echo esc_html( $step['step'] ); ?></span>
					<h3 class="dg-careers-hire-step-title"><?php echo esc_html( $step['title'] ); ?></h3>
					<p class="dg-careers-hire-step-body"><?php echo esc_html( $step['body'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>