<?php
/**
 * Dragon Glow — Sustainability: Commitments
 * 3 tiles (eco / cruelty_free / water_drop) with hover blush background.
 * Mirrors stitch: "What we hold to."
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data        = dg_sustainability_data();
$commitments = $data['commitments'];
?>
<section class="dg-sus-commitments" id="practices" aria-label="<?php echo esc_attr( $commitments['heading'] ); ?>">

	<div class="dg-sus-commitments-header" data-sr>
		<h2 class="dg-sus-commitments-heading"><?php echo esc_html( $commitments['heading'] ); ?></h2>
		<span class="dg-sus-hairline" aria-hidden="true"></span>
	</div>

	<div class="dg-sus-commitments-grid">
		<?php foreach ( $commitments['items'] as $idx => $item ) : ?>
		<article class="dg-sus-commit" data-sr data-sr-delay="<?php echo (int) ( $idx * 80 ); ?>">
			<span class="material-symbols-outlined dg-sus-commit-icon" aria-hidden="true"><?php echo esc_html( $item['icon'] ); ?></span>
			<h3 class="dg-sus-commit-title"><?php echo esc_html( $item['title'] ); ?></h3>
			<p class="dg-sus-commit-body"><?php echo esc_html( $item['body'] ); ?></p>
		</article>
		<?php endforeach; ?>
	</div>
</section>