<?php
/**
 * Template Name: Our Story — Dragon Glow
 * Description: Our Story page — hero + philosophy + alchemy + commitment.
 *
 * Data is sourced from template-parts/our-story/data-our-story.php.
 * Each section is a dedicated partial (hero/philosophy/alchemy/commitment).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Tách data khỏi markup: nạp file data trước, gọi hàm bên trong template-parts.
require_once locate_template( 'template-parts/our-story/data-our-story.php' );

$sections = array(
	'hero'       => 'section-hero.php',
	'philosophy' => 'section-philosophy.php',
	'alchemy'    => 'section-alchemy.php',
	'commitment' => 'section-commitment.php',
);
?>

<main id="main-content">
	<?php
	foreach ( $sections as $part ) {
		$path = locate_template( 'template-parts/our-story/' . $part );
		if ( $path ) {
			include $path;
		}
	}
	?>
</main>

<?php get_footer(); ?>
