<?php
/**
 * Dragon Glow — Sustainability: Packaging
 * 3 cards (Glass / Paper / Return) with hairline divider above each.
 * Mirrors stitch: "What it comes in"
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data      = dg_sustainability_data();
$packaging = $data['packaging'];
?>
<section class="dg-sus-packaging" aria-label="<?php echo esc_attr( $packaging['heading'] ); ?>">
	<div class="dg-sus-packaging-inner">

		<div class="dg-sus-packaging-header" data-sr>
			<span class="dg-sus-hairline" aria-hidden="true"></span>
			<h2 class="dg-sus-packaging-heading"><?php echo esc_html( $packaging['heading'] ); ?></h2>
			<span class="dg-sus-hairline" aria-hidden="true"></span>
		</div>

		<div class="dg-sus-packaging-grid">
			<?php foreach ( $packaging['items'] as $idx => $item ) : ?>
			<div class="dg-sus-packaging-item" data-sr data-sr-delay="<?php echo (int) ( $idx * 60 ); ?>">
				<h3 class="dg-sus-packaging-title"><?php echo esc_html( $item['title'] ); ?></h3>
				<p class="dg-sus-packaging-body"><?php echo esc_html( $item['body'] ); ?></p>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>