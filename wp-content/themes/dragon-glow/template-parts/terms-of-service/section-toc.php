<?php
/**
 * Template part — Terms of Service / Table of Contents
 *
 * Sidebar nav (desktop sticky) + mobile dropdown. Render cùng danh sách
 * anchor lấy từ dg_terms_of_service_sections_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$sections = dg_terms_of_service_sections_data();
?>
<div class="dg-terms-toc-mobile" id="dg-terms-toc-mobile">
	<select class="dg-terms-toc-select" onchange="document.getElementById(this.value).scrollIntoView({behavior: 'smooth'})">
		<?php foreach ( $sections as $section ) : ?>
			<option value="<?php echo esc_attr( $section['id'] ); ?>">
				<?php echo esc_html( $section['num'] . '. ' . $section['title'] ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<span class="material-symbols-outlined dg-terms-toc-icon">expand_more</span>
</div>

<aside class="dg-terms-sidebar">
	<nav class="dg-terms-nav" aria-label="<?php esc_attr_e( 'Terms of Service sections', 'dragon-glow' ); ?>">
		<?php foreach ( $sections as $i => $section ) : ?>
			<a class="dg-terms-nav-link<?php echo 0 === $i ? ' is-active' : ''; ?>"
			   href="#<?php echo esc_attr( $section['id'] ); ?>">
				<?php echo esc_html( $section['num'] . '. ' . $section['title'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
</aside>
