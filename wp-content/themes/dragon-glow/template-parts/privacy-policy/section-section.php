<?php
/**
 * Template part — Privacy Policy / Section renderer
 *
 * Render 1 section từ data. Biến được truyền vào qua scope của include:
 *   - $id, $num, $title, $body (HTML đã escape từ data-privacy-policy.php).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $id ) || empty( $title ) ) {
	return;
}
?>
<section class="dg-privacy-section" id="<?php echo esc_attr( $id ); ?>">
	<h2 class="dg-privacy-section-title">
		<span class="dg-privacy-section-num"><?php echo esc_html( $num ); ?></span>
		<?php echo esc_html( $title ); ?>
	</h2>
	<?php
	// body is pre-escaped HTML built in data-privacy-policy.php (only safe tags:
	// <p>, <ul>, <ol>, <li>, <strong>, <address>, <a>).
	echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
</section>
