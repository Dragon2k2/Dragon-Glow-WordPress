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
<div class="dg-privacy-toc-mobile" id="dg-privacy-toc-mobile" data-dg-toc>
	<button
		type="button"
		class="dg-privacy-toc-select dg-privacy-toc-btn"
		id="dg-privacy-toc-btn"
		aria-haspopup="listbox"
		aria-expanded="false"
		aria-controls="dg-privacy-toc-list"
	>
		<?php if ( ! empty( $sections ) ) : ?>
			<span class="dg-privacy-toc-btn-label" id="dg-privacy-toc-label"><?php echo esc_html( $sections[0]['num'] . '. ' . $sections[0]['title'] ); ?></span>
		<?php endif; ?>
		<span class="material-symbols-outlined dg-privacy-toc-icon" aria-hidden="true">expand_more</span>
	</button>
	<div class="dg-privacy-toc-panel" hidden>
	<ul class="dg-privacy-toc-list" id="dg-privacy-toc-list" role="listbox" aria-labelledby="dg-privacy-toc-label" hidden>
		<?php foreach ( $sections as $i => $section ) : ?>
			<li class="dg-privacy-toc-item" id="dg-privacy-toc-opt-<?php echo esc_attr( $i + 1 ); ?>" role="option" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>">
				<a class="dg-privacy-toc-link" href="#<?php echo esc_attr( $section['id'] ); ?>"><?php echo esc_html( $section['num'] . '. ' . $section['title'] ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
	</div>
	<noscript>
		<ul class="dg-privacy-toc-noscript">
			<?php foreach ( $sections as $section ) : ?>
				<li><a href="#<?php echo esc_attr( $section['id'] ); ?>"><?php echo esc_html( $section['num'] . '. ' . $section['title'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</noscript>
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
