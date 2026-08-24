<?php
/**
 * Dragon Glow — Sustainability: Closing CTA
 * Centered statement + outline button.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data    = dg_sustainability_data();
$closing = $data['closing'];
?>
<section class="dg-sus-closing" aria-label="Closing">
	<div class="dg-sus-closing-inner" data-sr>
		<p class="dg-sus-closing-headline"><?php echo esc_html( $closing['headline'] ); ?></p>
		<a href="<?php echo esc_url( $closing['cta_url'] ); ?>" class="dg-sus-btn">
			<?php echo esc_html( $closing['cta_text'] ); ?>
		</a>
	</div>
</section>