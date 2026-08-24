<?php
/**
 * Template part — Accessibility Statement / Section 4: Accessibility features of this site
 *
 * Render list 7 tính năng lấy từ dg_accessibility_features_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$features = dg_accessibility_features_data();
?>
<section class="dg-accessibility-section" id="section-4">
	<h2 class="dg-accessibility-section-title"><span class="dg-accessibility-section-num">04</span> Accessibility features of this site</h2>
	<p>This website includes:</p>
	<?php if ( ! empty( $features ) ) : ?>
		<ul>
			<?php foreach ( $features as $feature ) : ?>
				<li><?php echo esc_html( $feature ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>