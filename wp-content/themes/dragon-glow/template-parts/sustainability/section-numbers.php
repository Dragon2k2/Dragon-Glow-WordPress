<?php
/**
 * Dragon Glow — Sustainability: Numbers strip
 * 4 uppercase stats on dark (primary) background, separated by hairline dots.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data    = dg_sustainability_data();
$numbers = $data['numbers'];
?>
<section class="dg-sus-numbers" aria-label="Certifications">
	<div class="dg-sus-numbers-inner">
		<?php foreach ( $numbers['items'] as $idx => $label ) : ?>
			<span class="dg-sus-numbers-item" data-sr data-sr-delay="<?php echo (int) ( $idx * 60 ); ?>"><?php echo esc_html( $label ); ?></span>
			<?php if ( $idx < count( $numbers['items'] ) - 1 ) : ?>
				<span class="dg-sus-numbers-dot" aria-hidden="true"></span>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</section>