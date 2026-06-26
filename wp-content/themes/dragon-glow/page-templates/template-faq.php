<?php
/**
 * Template Name: FAQ — Dragon Glow
 * Description: Frequently asked questions — numbered editorial accordion with live search.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// JS guard sớm: ẩn phần tử sẽ animate cho tới khi JS chạy (tránh FOUC).
echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

// FAQ data (single source of truth) — nạp trước khi render partial.
require_once locate_template( 'template-parts/faq/data-faq.php' );
?>

<main class="dg-faq" id="main-content">
	<div class="dg-faq-progress" aria-hidden="true"><span class="dg-faq-progress-fill"></span></div>

	<div class="dg-faq-wrap">
		<?php
		get_template_part( 'template-parts/faq/section-hero' );
		get_template_part( 'template-parts/faq/section-search' );
		get_template_part( 'template-parts/faq/accordion' );
		get_template_part( 'template-parts/faq/section-cta' );
		?>
	</div>
</main>

<?php get_footer(); ?>
