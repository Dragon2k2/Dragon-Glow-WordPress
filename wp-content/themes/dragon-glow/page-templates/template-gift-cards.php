<?php
/**
 * Template Name: Gift Cards — Dragon Glow
 * Description: Trang Gift Cards — Hero + Bento grid (preview) + Configuration panel.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// JS guard sớm — ẩn các phần tử sẽ animate cho tới khi JS chạy (tránh FOUC).
echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

// Data (single source of truth) — nạp trước khi render partial.
require_once locate_template( 'template-parts/gift-cards/data-config.php' );
?>

<main class="dg-gift" id="main-content">
	<div class="dg-gift-wrap">
		<?php
		get_template_part( 'template-parts/gift-cards/section-hero' );

		// Bento grid 12 cột: preview (7) + configuration (5) trên desktop; xếp dọc trên mobile.
		echo '<div class="dg-gift-layout">';
		echo '<div class="dg-gift-layout-visual">';
		get_template_part( 'template-parts/gift-cards/section-bento' );
		echo '</div>';

		echo '<div class="dg-gift-layout-config">';
		get_template_part( 'template-parts/gift-cards/section-config' );
		echo '</div>';
		echo '</div>';
		?>
	</div>
</main>

<?php get_footer(); ?>
