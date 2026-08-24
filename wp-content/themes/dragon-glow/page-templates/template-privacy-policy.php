<?php
/**
 * Template Name: Privacy Policy — Dragon Glow
 * Description: Editorial legal document layout with sticky sidebar nav + scroll spy.
 *
 * Data is sourced from template-parts/privacy-policy/data-privacy-policy.php.
 * Sections are rendered via section-section.php; layout uses section-hero.php
 * + section-toc.php. Body markup for each section lives in the data array
 * (already escaped via wp_kses / esc_html at build time).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// JS guard sớm: ẩn phần tử sẽ animate cho tới khi JS chạy (tránh FOUC).
echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

// Tách data khỏi markup: nạp file data trước, gọi hàm bên trong template-parts.
require_once locate_template( 'template-parts/privacy-policy/data-privacy-policy.php' );

$sections = dg_privacy_policy_sections_data();
?>

<main class="dg-privacy" id="main-content">
	<div class="dg-privacy-wrap">

		<?php
		$hero_part = locate_template( 'template-parts/privacy-policy/section-hero.php' );
		if ( $hero_part ) {
			include $hero_part;
		}
		?>

		<div class="dg-privacy-layout">
			<?php
			$toc_part = locate_template( 'template-parts/privacy-policy/section-toc.php' );
			if ( $toc_part ) {
				include $toc_part;
			}
			?>

			<div class="dg-privacy-content">
				<?php
				$section_part = locate_template( 'template-parts/privacy-policy/section-section.php' );
				foreach ( $sections as $section ) {
					if ( $section_part ) {
						$id      = $section['id'];
						$num     = $section['num'];
						$title   = $section['title'];
						$body    = $section['body'] ?? '';
						include $section_part;
					}
				}
				?>
			</div>
		</div>

	</div>
</main>

<?php get_footer(); ?>
