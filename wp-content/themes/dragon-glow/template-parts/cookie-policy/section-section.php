<?php
/**
 * Template part — Cookie Policy / Section renderer
 *
 * Render 1 section từ data: hiển thị title + body. Nếu section có key
 * `partial`, include file partial tương ứng thay vì echo body.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// ── Extract variables via $args (WordPress-native get_template_part) ──
$id      = isset( $args['id'] )      ? (string) $args['id']      : '';
$num     = isset( $args['num'] )     ? (string) $args['num']     : '';
$title   = isset( $args['title'] )   ? (string) $args['title']   : '';
$body    = isset( $args['body'] )    ? (string) $args['body']    : '';
$partial = isset( $args['partial'] ) ? (string) $args['partial'] : '';

if ( '' === $id || '' === $title ) {
	return;
}
?>
<section class="dg-cookie-section" id="<?php echo esc_attr( $id ); ?>">
	<h2 class="dg-cookie-section-title">
		<span class="dg-cookie-section-num"><?php echo esc_html( $num ); ?></span>
		<?php echo esc_html( $title ); ?>
	</h2>

	<?php
	if ( '' !== $partial ) {
		$partial_path = locate_template( 'template-parts/cookie-policy/' . $partial . '.php' );
		if ( $partial_path ) {
			include $partial_path;
		}
	} else {
		// body is pre-escaped HTML from data-cookie-policy.php.
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>
</section>
