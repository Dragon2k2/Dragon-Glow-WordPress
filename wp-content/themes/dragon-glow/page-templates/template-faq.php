<?php
/**
 * Template Name: FAQ — Dragon Glow
 * Description: The Concierge — sidebar categories + glass-panel accordion, with live search.
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
	<div class="dg-faq-wrap">
		<?php
		get_template_part( 'template-parts/faq/section-hero' );
		get_template_part( 'template-parts/faq/section-search' );

		// Layout 12-col: sidebar categories (3) + accordion (9)
		echo '<div class="dg-faq-layout">';
		get_template_part( 'template-parts/faq/section-categories' );
		echo '<div class="dg-faq-main">';
		get_template_part( 'template-parts/faq/accordion' );
		echo '</div>';
		echo '</div>';

		get_template_part( 'template-parts/faq/section-cta' );
		?>
	</div>
</main>

<?php get_footer(); ?>