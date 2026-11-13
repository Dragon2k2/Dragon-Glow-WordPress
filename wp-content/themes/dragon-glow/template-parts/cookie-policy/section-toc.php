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
<div class="dg-cookie-toc-mobile" id="dg-cookie-toc-mobile" data-dg-toc>
	<button
		type="button"
		class="dg-cookie-toc-select dg-cookie-toc-btn"
		id="dg-cookie-toc-btn"
		aria-haspopup="listbox"
		aria-expanded="false"
		aria-controls="dg-cookie-toc-list"
	>
		<?php if ( ! empty( $sections ) ) : ?>
			<span class="dg-cookie-toc-btn-label" id="dg-cookie-toc-label"><?php echo esc_html( $sections[0]['num'] . '. ' . $sections[0]['title'] ); ?></span>
		<?php endif; ?>
		<span class="material-symbols-outlined dg-cookie-toc-icon" aria-hidden="true">expand_more</span>
	</button>
	<div class="dg-cookie-toc-panel" hidden>
	<ul class="dg-cookie-toc-list" id="dg-cookie-toc-list" role="listbox" aria-labelledby="dg-cookie-toc-label" hidden>
		<?php foreach ( $sections as $i => $section ) : ?>
			<li class="dg-cookie-toc-item" id="dg-cookie-toc-opt-<?php echo esc_attr( $i + 1 ); ?>" role="option" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>">
				<a class="dg-cookie-toc-link" href="#<?php echo esc_attr( $section['id'] ); ?>"><?php echo esc_html( $section['num'] . '. ' . $section['title'] ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
	</div>
	<noscript>
		<ul class="dg-cookie-toc-noscript">
			<?php foreach ( $sections as $section ) : ?>
				<li><a href="#<?php echo esc_attr( $section['id'] ); ?>"><?php echo esc_html( $section['num'] . '. ' . $section['title'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</noscript>
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
