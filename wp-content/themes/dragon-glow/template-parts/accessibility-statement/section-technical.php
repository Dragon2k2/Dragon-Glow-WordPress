<?php
/**
 * Template part — Accessibility Statement / Section 5: Known limitations
 *
 * Render list 3 hạn chế lấy từ dg_accessibility_limitations_data() +
 * 1 đoạn thông báo "đang cải thiện".
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$limits = dg_accessibility_limitations_data();
?>
<section class="dg-accessibility-section" id="section-5">
	<h2 class="dg-accessibility-section-title"><span class="dg-accessibility-section-num">05</span> Known limitations</h2>
	<p>Despite our efforts, some limitations remain:</p>
	<?php if ( ! empty( $limits ) ) : ?>
		<ul>
			<?php foreach ( $limits as $limit ) : ?>
				<li><?php echo esc_html( $limit ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<p>We are working to resolve these and welcome your reports.</p>
</section>