<?php
/**
 * Template part — Cookie Policy / Categories
 *
 * Render grid 2 cột với 4 thẻ category. Data lấy từ dg_cookie_categories_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$categories = dg_cookie_categories_data();
if ( empty( $categories ) ) {
	return;
}
?>
<div class="dg-cookie-categories-grid">
	<?php foreach ( $categories as $cat ) : ?>
		<div class="dg-cookie-category">
			<h3 class="dg-cookie-category-label"><?php echo esc_html( $cat['label'] ); ?></h3>
			<p class="dg-cookie-category-body"><?php echo esc_html( $cat['body'] ); ?></p>
		</div>
	<?php endforeach; ?>
</div>