<?php
/**
 * Template Name: Careers — Dragon Glow
 * Description: Editorial careers page với 9 section theo stitch_dragon_glow_careers_ui.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// JS guard sớm: ẩn phần tử sẽ reveal cho tới khi JS chạy (tránh FOUC).
echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

// Tách data khỏi markup: nạp file data-careers trước, gọi hàm bên trong template-parts.
require_once locate_template( 'template-parts/careers/data-careers.php' );
?>

<main class="dg-careers" id="main-content">

	<?php
	$sections = array(
		'section-hero'              => 'section-hero.php',
		'section-mission'           => 'section-mission.php',
		'section-why-join'          => 'section-why-join.php',
		'section-life-here'         => 'section-life-here.php',
		'section-what-we-offer'     => 'section-what-we-offer.php',
		'section-open-roles'        => 'section-open-roles.php',
		'section-how-we-hire'       => 'section-how-we-hire.php',
		'section-equal-opportunity' => 'section-equal-opportunity.php',
		'section-closing-cta'       => 'section-closing-cta.php',
	);

	foreach ( $sections as $key => $file ) {
		$part = locate_template( 'template-parts/careers/' . $file );
		if ( $part ) {
			require $part;
		}
	}
	?>

	<?php
	// Apply modal — outside .dg-careers scope so it can use its own token
	// context. Rendered once per page; controlled via [data-apply-trigger].
	$apply_modal = locate_template( 'template-parts/careers/section-apply-modal.php' );
	if ( $apply_modal ) {
		require $apply_modal;
	}
	?>

</main>

<?php get_footer(); ?>