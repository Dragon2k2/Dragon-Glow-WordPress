<?php
/**
 * Template Name: Terms of Service — Dragon Glow
 * Description: Editorial legal document layout with sticky sidebar nav + scroll spy.
 *
 * Data is sourced from template-parts/terms-of-service/data-terms-of-service.php.
 * Sections are rendered via section-section.php; layout uses section-hero.php
 * + section-toc.php.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// JS guard sớm: ẩn phần tử sẽ animate cho tới khi JS chạy (tránh FOUC).
echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

// Tách data khỏi markup: nạp file data trước, gọi hàm bên trong template-parts.
require_once locate_template( 'template-parts/terms-of-service/data-terms-of-service.php' );

$sections = dg_terms_of_service_sections_data();
?>

<main class="dg-terms" id="main-content">
	<div class="dg-terms-wrap">

		<?php
		$hero_part = locate_template( 'template-parts/terms-of-service/section-hero.php' );
		if ( $hero_part ) {
			include $hero_part;
		}
		?>

		<div class="dg-terms-layout">
			<?php
			$toc_part = locate_template( 'template-parts/terms-of-service/section-toc.php' );
			if ( $toc_part ) {
				include $toc_part;
			}
			?>

			<div class="dg-terms-content">
				<?php
				$section_part = locate_template( 'template-parts/terms-of-service/section-section.php' );
				foreach ( $sections as $section ) {
					if ( $section_part ) {
						$id    = $section['id'];
						$num   = $section['num'];
						$title = $section['title'];
						$body  = $section['body'] ?? '';
						include $section_part;
					}
				}
				?>
			</div>
		</div>

	</div>
</main>

<?php get_footer(); ?>
