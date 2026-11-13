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
<div class="dg-terms-toc-mobile" id="dg-terms-toc-mobile" data-dg-toc>
	<button
		type="button"
		class="dg-terms-toc-select dg-terms-toc-btn"
		id="dg-terms-toc-btn"
		aria-haspopup="listbox"
		aria-expanded="false"
		aria-controls="dg-terms-toc-list"
	>
		<?php if ( ! empty( $sections ) ) : ?>
			<span class="dg-terms-toc-btn-label" id="dg-terms-toc-label"><?php echo esc_html( $sections[0]['num'] . '. ' . $sections[0]['title'] ); ?></span>
		<?php endif; ?>
		<span class="material-symbols-outlined dg-terms-toc-icon" aria-hidden="true">expand_more</span>
	</button>
	<div class="dg-terms-toc-panel" hidden>
	<ul class="dg-terms-toc-list" id="dg-terms-toc-list" role="listbox" aria-labelledby="dg-terms-toc-label" hidden>
		<?php foreach ( $sections as $i => $section ) : ?>
			<li class="dg-terms-toc-item" id="dg-terms-toc-opt-<?php echo esc_attr( $i + 1 ); ?>" role="option" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>">
				<a class="dg-terms-toc-link" href="#<?php echo esc_attr( $section['id'] ); ?>"><?php echo esc_html( $section['num'] . '. ' . $section['title'] ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
	</div>
	<noscript>
		<ul class="dg-terms-toc-noscript">
			<?php foreach ( $sections as $section ) : ?>
				<li><a href="#<?php echo esc_attr( $section['id'] ); ?>"><?php echo esc_html( $section['num'] . '. ' . $section['title'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</noscript>
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
