<?php
/**
 * Template Name: Help Center — Dragon Glow
 * Description: Hero search + 5 FAQ accordions (Orders, Account, Ritual, Ingredients, Sustainability).
 * Nav + footer giữ nguyên theme.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// FOUC guard — ẩn phần tử sẽ animate cho tới khi JS chạy.
echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

require_once locate_template( 'template-parts/help-center/data-help-center.php' );
?>
<main id="main-content" class="dg-hc" data-page="help-center">

	<?php get_template_part( 'template-parts/help-center/section-hero' ); ?>

	<?php get_template_part( 'template-parts/help-center/section-search' ); ?>

	<div class="dg-hc-wrap">
		<?php
		get_template_part( 'template-parts/help-center/section-orders' );
		get_template_part( 'template-parts/help-center/section-account' );
		get_template_part( 'template-parts/help-center/section-ritual' );
		get_template_part( 'template-parts/help-center/section-ingredients' );
		get_template_part( 'template-parts/help-center/section-sustainability' );
		?>
	</div>

</main>
<?php get_footer(); ?>