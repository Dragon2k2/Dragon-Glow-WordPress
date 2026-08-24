<?php
/**
 * Template part — Careers / Section 2: Mission quote
 *
 * Quote full-width centered, bg surface-container-low, section-gap.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$quote = dg_careers_mission_data();
?>
<section class="dg-careers-mission" data-sr>
	<blockquote class="dg-careers-mission-quote"><?php echo esc_html( $quote ); ?></blockquote>
</section>