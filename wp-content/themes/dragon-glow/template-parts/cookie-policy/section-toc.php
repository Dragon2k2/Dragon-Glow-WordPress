<?php
/**
 * Template part — Cookie Policy / Table of Contents
 *
 * Sidebar nav (desktop sticky) + mobile dropdown. Cả hai render cùng một
 * danh sách anchor lấy từ dg_cookie_policy_sections_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$sections = dg_cookie_policy_sections_data();
?>
<div class="dg-cookie-toc-mobile" id="dg-cookie-toc-mobile">
	<select class="dg-cookie-toc-select" onchange="document.getElementById(this.value).scrollIntoView({behavior: 'smooth'})">
		<?php foreach ( $sections as $section ) : ?>
			<option value="<?php echo esc_attr( $section['id'] ); ?>">
				<?php echo esc_html( $section['num'] . '. ' . $section['title'] ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<span class="material-symbols-outlined dg-cookie-toc-icon">expand_more</span>
</div>

<aside class="dg-cookie-sidebar">
	<nav class="dg-cookie-nav" aria-label="<?php esc_attr_e( 'Cookie Policy sections', 'dragon-glow' ); ?>">
		<?php foreach ( $sections as $i => $section ) : ?>
			<a class="dg-cookie-nav-link<?php echo 0 === $i ? ' is-active' : ''; ?>"
			   href="#<?php echo esc_attr( $section['id'] ); ?>">
				<?php echo esc_html( $section['num'] . '. ' . $section['title'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
</aside>
