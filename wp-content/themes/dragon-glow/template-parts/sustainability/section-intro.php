<?php
/**
 * Dragon Glow — Sustainability: Intro pullquote
 * Centered, surface-container-low background.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data  = dg_sustainability_data();
$intro = $data['intro'];
?>
<section class="dg-sus-intro" aria-label="Intro">
	<div class="dg-sus-intro-inner" data-sr>
		<h2 class="dg-sus-intro-text"><?php echo esc_html( $intro['text'] ); ?></h2>
	</div>
</section>