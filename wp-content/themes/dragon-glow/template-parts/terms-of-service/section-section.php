<?php
/**
 * Template part — Terms of Service / Section renderer
 *
 * Render 1 section từ data. Biến được truyền vào qua scope của include:
 *   - $id, $num, $title, $body (HTML đã escape từ data-terms-of-service.php).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $id ) || empty( $title ) ) {
	return;
}
?>
<section class="dg-terms-section" id="<?php echo esc_attr( $id ); ?>">
	<h2 class="dg-terms-section-title">
		<span class="dg-terms-section-num"><?php echo esc_html( $num ); ?></span>
		<?php echo esc_html( $title ); ?>
	</h2>
	<?php
	// body is pre-escaped HTML built in data-terms-of-service.php (only safe tags).
	echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
</section>
