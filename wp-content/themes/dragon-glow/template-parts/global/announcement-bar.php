<?php
/**
 * Dragon Glow — Announcement Bar
 * Auto-scrolling promo strip (right to left) rendered above the global header nav.
 *
 * The message list is printed twice so the CSS loop is seamless (translateX 0 → -50%).
 * The second copy is aria-hidden so screen readers announce each message once.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

require_once locate_template( 'template-parts/global/data-announcement-bar.php' );

$items = dg_announcement_bar_data();

if ( empty( $items ) ) {
	return;
}
?>
<div class="dg-announcement-bar">
	<div class="dg-announcement-bar__track">
		<?php for ( $copy = 0; $copy < 2; $copy++ ) : ?>
		<ul class="dg-announcement-bar__group<?php echo 0 === $copy ? '' : ' dg-announcement-bar__group--clone'; ?>"<?php echo 0 === $copy ? '' : ' aria-hidden="true"'; ?>>
			<?php foreach ( $items as $index => $item ) : ?>
			<li class="dg-announcement-bar__item<?php echo 0 === $index ? '' : ' dg-announcement-bar__item--secondary'; ?>">
				<span class="material-symbols-outlined dg-announcement-bar__icon" aria-hidden="true"><?php echo esc_html( $item['icon'] ); ?></span>
				<span class="dg-announcement-bar__text"><?php echo esc_html( $item['text'] ); ?></span>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php endfor; ?>
	</div>
</div>
