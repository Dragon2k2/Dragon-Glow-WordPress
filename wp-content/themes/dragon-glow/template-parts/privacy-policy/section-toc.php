<?php
/**
 * Template part — Privacy Policy / Table of Contents
 *
 * Sidebar nav (desktop sticky) + mobile dropdown. Render cùng danh sách
 * anchor lấy từ dg_privacy_policy_sections_data().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$sections = dg_privacy_policy_sections_data();
?>
<div class="dg-privacy-toc-mobile" id="dg-privacy-toc-mobile">
	<select class="dg-privacy-toc-select" onchange="document.getElementById(this.value).scrollIntoView({behavior: 'smooth'})">
		<?php foreach ( $sections as $section ) : ?>
			<option value="<?php echo esc_attr( $section['id'] ); ?>">
				<?php echo esc_html( $section['num'] . '. ' . $section['title'] ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<span class="material-symbols-outlined dg-privacy-toc-icon">expand_more</span>
</div>

<aside class="dg-privacy-sidebar">
	<nav class="dg-privacy-nav" aria-label="<?php esc_attr_e( 'Privacy Policy sections', 'dragon-glow' ); ?>">
		<?php foreach ( $sections as $i => $section ) : ?>
			<a class="dg-privacy-nav-link<?php echo 0 === $i ? ' is-active' : ''; ?>"
			   href="#<?php echo esc_attr( $section['id'] ); ?>">
				<?php echo esc_html( $section['num'] . '. ' . $section['title'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
</aside>
