<?php
/**
 * Template Name: Cookie Policy — Dragon Glow
 * Description: Editorial legal document layout with sticky sidebar nav + scroll spy.
 *
 * Data is sourced from template-parts/cookie-policy/data-cookie-policy.php.
 * Sections are rendered via section-section.php; layout uses section-hero.php
 * + section-toc.php. Body markup for plain sections lives in the data array
 * (already escaped). Complex sections (categories / table / management) use
 * dedicated partials.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// JS guard sớm: ẩn phần tử sẽ animate cho tới khi JS chạy (tránh FOUC).
echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

// Tách data khỏi markup: nạp file data trước, gọi hàm bên trong template-parts.
require_once locate_template( 'template-parts/cookie-policy/data-cookie-policy.php' );

$sections = dg_cookie_policy_sections_data();
?>

<main class="dg-cookie" id="main-content">
	<div class="dg-cookie-wrap">

		<?php
		$hero_part = locate_template( 'template-parts/cookie-policy/section-hero.php' );
		if ( $hero_part ) {
			include $hero_part;
		}
		?>

		<div class="dg-cookie-layout">
			<?php
			$toc_part = locate_template( 'template-parts/cookie-policy/section-toc.php' );
			if ( $toc_part ) {
				include $toc_part;
			}
			?>

			<div class="dg-cookie-content">
				<?php
				$section_part = locate_template( 'template-parts/cookie-policy/section-section.php' );
				foreach ( $sections as $section ) {
					if ( $section_part ) {
						// Build $args for get_template_part include path.
						$args = $section;
						// Inline-render via include to pass $args scope.
						$id      = $section['id'];
						$num     = $section['num'];
						$title   = $section['title'];
						$body    = $section['body'] ?? '';
						$partial = $section['partial'] ?? '';
						include $section_part;
					}
				}
				?>
			</div>
		</div>

	</div>
</main>

<?php get_footer(); ?>
