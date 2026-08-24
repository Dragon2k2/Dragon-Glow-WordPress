<?php
/**
 * Template part — Careers / Section 9: Closing CTA
 *
 * Centered body + modal trigger button + email label caps.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$closing = dg_careers_closing_data();
?>
<section class="dg-careers-closing" data-sr>
	<p class="dg-careers-closing-body"><?php echo esc_html( $closing['body'] ); ?></p>
	<button
		type="button"
		class="dg-careers-btn dg-careers-btn--primary"
		data-apply-trigger
		data-allow-custom-role="1"
		data-role="<?php echo esc_attr( $closing['default_role'] ?? __( 'General Application', 'dragon-glow' ) ); ?>"
	><?php echo esc_html( $closing['cta_label'] ); ?></button>
	<p class="dg-careers-closing-email"><?php echo esc_html( $closing['email'] ); ?></p>
</section>