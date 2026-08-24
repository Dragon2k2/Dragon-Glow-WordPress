<?php
/**
 * Template part — Accessibility Statement / Section 7: Technical specifications
 *
 * Render list 4 công nghệ lấy từ dg_accessibility_tech_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$tech = dg_accessibility_tech_data();
?>
<section class="dg-accessibility-section" id="section-7">
	<h2 class="dg-accessibility-section-title"><span class="dg-accessibility-section-num">07</span> Technical specifications</h2>
	<p>Accessibility relies on the following technologies working with your browser and any assistive technology:</p>
	<?php if ( ! empty( $tech ) ) : ?>
		<ul>
			<?php foreach ( $tech as $item ) : ?>
				<li><?php echo esc_html( $item ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<p>Where these are not supported, parts of the site may not work as intended.</p>
</section>