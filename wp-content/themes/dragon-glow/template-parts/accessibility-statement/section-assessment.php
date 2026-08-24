<?php
/**
 * Template part — Accessibility Statement / Section 3: Measures we take
 *
 * Render list 5 biện pháp lấy từ dg_accessibility_measures_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$measures = dg_accessibility_measures_data();
?>
<section class="dg-accessibility-section" id="section-3">
	<h2 class="dg-accessibility-section-title"><span class="dg-accessibility-section-num">03</span> Measures we take</h2>
	<p>To support accessibility, we:</p>
	<?php if ( ! empty( $measures ) ) : ?>
		<ul>
			<?php foreach ( $measures as $measure ) : ?>
				<li><?php echo esc_html( $measure ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>