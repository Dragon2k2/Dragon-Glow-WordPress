<?php
/**
 * Template Name: Accessibility Statement — Dragon Glow
 * Description: Editorial legal document layout with sticky sidebar nav + scroll spy.
 *              Hero (eyebrow + H1 + intro) + 10 section theo nội dung user cung cấp.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// JS guard sớm: ẩn phần tử sẽ animate cho tới khi JS chạy (tránh FOUC).
echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

// Tách data khỏi markup: nạp file data-accessibility-statement trước, gọi hàm bên trong template-parts.
require_once locate_template( 'template-parts/accessibility-statement/data-accessibility-statement.php' );
?>

<main class="dg-accessibility" id="main-content">
	<div class="dg-accessibility-wrap">

		<!-- Hero Block -->
		<?php
		$hero_part = locate_template( 'template-parts/accessibility-statement/section-hero.php' );
		if ( $hero_part ) {
			require $hero_part;
		}
		?>

		<!-- Main Layout: Sidebar + Content -->
		<div class="dg-accessibility-layout">

			<!-- Mobile: Sticky Table of Contents Dropdown (custom listbox, <1024px) -->
			<div class="dg-accessibility-toc-mobile" id="dg-accessibility-toc-mobile" data-dg-toc>
				<button
					type="button"
					class="dg-accessibility-toc-select dg-a11y-toc-btn"
					id="dg-a11y-toc-btn"
					aria-haspopup="listbox"
					aria-expanded="false"
					aria-controls="dg-a11y-toc-list"
				>
					<span class="dg-a11y-toc-btn-label" id="dg-a11y-toc-label">1. Our commitment</span>
					<span class="material-symbols-outlined dg-accessibility-toc-icon" aria-hidden="true">expand_more</span>
				</button>
				<ul class="dg-a11y-toc-list" id="dg-a11y-toc-list" role="listbox" aria-labelledby="dg-a11y-toc-label" hidden>
					<li class="dg-a11y-toc-item" id="dg-a11y-toc-opt-1" role="option" aria-selected="true"><a class="dg-a11y-toc-link" href="#section-1">1. Our commitment</a></li>
					<li class="dg-a11y-toc-item" id="dg-a11y-toc-opt-2" role="option" aria-selected="false"><a class="dg-a11y-toc-link" href="#section-2">2. Conformance status</a></li>
					<li class="dg-a11y-toc-item" id="dg-a11y-toc-opt-3" role="option" aria-selected="false"><a class="dg-a11y-toc-link" href="#section-3">3. Measures we take</a></li>
					<li class="dg-a11y-toc-item" id="dg-a11y-toc-opt-4" role="option" aria-selected="false"><a class="dg-a11y-toc-link" href="#section-4">4. Accessibility features of this site</a></li>
					<li class="dg-a11y-toc-item" id="dg-a11y-toc-opt-5" role="option" aria-selected="false"><a class="dg-a11y-toc-link" href="#section-5">5. Known limitations</a></li>
					<li class="dg-a11y-toc-item" id="dg-a11y-toc-opt-6" role="option" aria-selected="false"><a class="dg-a11y-toc-link" href="#section-6">6. Compatibility</a></li>
					<li class="dg-a11y-toc-item" id="dg-a11y-toc-opt-7" role="option" aria-selected="false"><a class="dg-a11y-toc-link" href="#section-7">7. Technical specifications</a></li>
					<li class="dg-a11y-toc-item" id="dg-a11y-toc-opt-8" role="option" aria-selected="false"><a class="dg-a11y-toc-link" href="#section-8">8. How we assess accessibility</a></li>
					<li class="dg-a11y-toc-item" id="dg-a11y-toc-opt-9" role="option" aria-selected="false"><a class="dg-a11y-toc-link" href="#section-9">9. Feedback and contact</a></li>
					<li class="dg-a11y-toc-item" id="dg-a11y-toc-opt-10" role="option" aria-selected="false"><a class="dg-a11y-toc-link" href="#section-10">10. Formal complaints</a></li>
				</ul>
				<noscript>
					<ul class="dg-a11y-toc-noscript">
						<li><a href="#section-1">1. Our commitment</a></li>
						<li><a href="#section-2">2. Conformance status</a></li>
						<li><a href="#section-3">3. Measures we take</a></li>
						<li><a href="#section-4">4. Accessibility features of this site</a></li>
						<li><a href="#section-5">5. Known limitations</a></li>
						<li><a href="#section-6">6. Compatibility</a></li>
						<li><a href="#section-7">7. Technical specifications</a></li>
						<li><a href="#section-8">8. How we assess accessibility</a></li>
						<li><a href="#section-9">9. Feedback and contact</a></li>
						<li><a href="#section-10">10. Formal complaints</a></li>
					</ul>
				</noscript>
			</div>

			<!-- Desktop: Sticky Sidebar Navigation -->
			<aside class="dg-accessibility-sidebar">
				<nav class="dg-accessibility-nav" aria-label="Accessibility Statement sections">
					<p class="dg-accessibility-nav-label"><?php esc_html_e( 'Table of Contents', 'dragon-glow' ); ?></p>
					<a class="dg-accessibility-nav-link is-active" href="#section-1">1. Our commitment</a>
					<a class="dg-accessibility-nav-link" href="#section-2">2. Conformance status</a>
					<a class="dg-accessibility-nav-link" href="#section-3">3. Measures we take</a>
					<a class="dg-accessibility-nav-link" href="#section-4">4. Accessibility features of this site</a>
					<a class="dg-accessibility-nav-link" href="#section-5">5. Known limitations</a>
					<a class="dg-accessibility-nav-link" href="#section-6">6. Compatibility</a>
					<a class="dg-accessibility-nav-link" href="#section-7">7. Technical specifications</a>
					<a class="dg-accessibility-nav-link" href="#section-8">8. How we assess accessibility</a>
					<a class="dg-accessibility-nav-link" href="#section-9">9. Feedback and contact</a>
					<a class="dg-accessibility-nav-link" href="#section-10">10. Formal complaints</a>
				</nav>
			</aside>

			<!-- Main Content -->
			<div class="dg-accessibility-content">

				<?php
				$sections = array(
					'section-conformance'      => 'section-conformance.php',
					'section-measures'         => 'section-measures.php',
					'section-assessment'       => 'section-assessment.php',
					'section-feedback'         => 'section-feedback.php',
					'section-technical'        => 'section-technical.php',
					'section-limitations'      => 'section-limitations.php',
					'section-evaluation'       => 'section-evaluation.php',
					'section-third-party'      => 'section-third-party.php',
					'section-enforcement'      => 'section-enforcement.php',
					'section-revision-history' => 'section-revision-history.php',
				);

				foreach ( $sections as $key => $file ) {
					$part = locate_template( 'template-parts/accessibility-statement/' . $file );
					if ( $part ) {
						require $part;
					}
				}
				?>

			</div>
		</div>

	</div>
</main>

<?php get_footer(); ?>