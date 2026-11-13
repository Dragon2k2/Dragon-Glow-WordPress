<?php
/**
 * Dragon Glow — Single Product Template
 * Override WooCommerce single-product.php
 * Renders: breadcrumb + content-single-product.php (gallery, details, tabs, related, sticky bar).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16" id="main-content">

	<?php get_template_part( 'template-parts/global/breadcrumb' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<?php wc_get_template_part( 'content', 'single-product' ); ?>
	<?php endwhile; ?>

</main>

<?php
// Lightbox xem ảnh đầy đủ — đặt ngoài <main> để `position: fixed` không bị
// neo sai bởi transform của animation reveal.
get_template_part( 'template-parts/global/zoom-modal' );
?>

<?php get_footer();
