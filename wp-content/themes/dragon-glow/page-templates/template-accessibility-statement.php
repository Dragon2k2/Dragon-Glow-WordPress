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

			<!-- Mobile: Sticky Table of Contents Dropdown -->
			<div class="dg-accessibility-toc-mobile" id="dg-accessibility-toc-mobile">
				<select class="dg-accessibility-toc-select" onchange="document.getElementById(this.value).scrollIntoView({behavior: 'smooth'})">
					<option value="section-1">1. Our commitment</option>
					<option value="section-2">2. Conformance status</option>
					<option value="section-3">3. Measures we take</option>
					<option value="section-4">4. Accessibility features of this site</option>
					<option value="section-5">5. Known limitations</option>
					<option value="section-6">6. Compatibility</option>
					<option value="section-7">7. Technical specifications</option>
					<option value="section-8">8. How we assess accessibility</option>
					<option value="section-9">9. Feedback and contact</option>
					<option value="section-10">10. Formal complaints</option>
				</select>
				<span class="material-symbols-outlined dg-accessibility-toc-icon">expand_more</span>
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