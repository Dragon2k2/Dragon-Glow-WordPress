<?php
/**
 * Dragon Glow — FAQ: Categories (sidebar)
 * Sticky vertical list. Active state: gold left border + tertiary colour.
 * Bấm vào category → lọc accordion xuống nhóm đó (JS xử lý).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$categories = dg_faq_data()['categories'];
?>
<aside class="dg-faq-categories" data-sr>
	<nav class="dg-faq-categories-nav" aria-label="<?php esc_attr_e( 'FAQ categories', 'dragon-glow' ); ?>">
		<?php foreach ( $categories as $i => $cat ) : ?>
			<button
				type="button"
				class="dg-faq-category<?php echo 0 === $i ? ' is-active' : ''; ?>"
				data-faq-category="<?php echo esc_attr( $cat['id'] ); ?>"
				aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>"
			>
				<?php echo esc_html( $cat['label'] ); ?>
			</button>
		<?php endforeach; ?>
	</nav>
</aside>