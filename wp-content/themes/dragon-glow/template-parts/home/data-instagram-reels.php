<?php
/**
 * Dragon Glow — Instagram Reels Section Data
 *
 * Shoppable Instagram Reels cards with creator info, quote, product CTA.
 * Single source of truth for reel content.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Instagram Reels section header.
 *
 * @return array{eyebrow: string, headline: string, description: string, cta_label: string, cta_url: string}
 */
function dg_instagram_reels_header_data(): array {
	$data = array(
		'eyebrow'       => __( 'Curated on Instagram', 'dragon-glow' ),
		'headline'      => __( 'Featured on Instagram Reels', 'dragon-glow' ),
		'description'   => __( 'Tap any video to view routine breakdown & snag the exact formula.', 'dragon-glow' ),
		'cta_label'     => __( 'Watch Instagram Reels (@dragonglow.skin)', 'dragon-glow' ),
		'cta_url'       => 'https://instagram.com/dragonglow.skin',
	);

	/**
	 * Filter Instagram Reels section header data.
	 *
	 * @param array $data Header data.
	 * @return array
	 */
	return (array) apply_filters( 'dg_instagram_reels_header_data', $data );
}

/**
 * Instagram Reels cards — 4 shoppable reel cards.
 *
 * Each card contains: image, gradient overlay, creator info, quote, product button.
 *
 * @return array[] Array of reel card data.
 */
function dg_instagram_reels_cards_data(): array {
	$reels = array(
		array(
			'image_url'    => get_theme_file_uri( 'assets/images/home/instagram-01.webp' ),
			'image_alt'     => __( 'Vertical TikTok style selfie video frame showing an aesthetic model testing dewy pink serum drops', 'dragon-glow' ),
			'views'        => '1.4M',
			'creator'      => '@glowbymia',
			'audio'        => __( 'Original audio', 'dragon-glow' ),
			'quote'        => __( '"My skin literally looks like glazed crystal donut. No foundation needed!"', 'dragon-glow' ),
			'product_name' => __( 'Hydra-Stick Glaze', 'dragon-glow' ),
			'product_price' => '$34',
		),
		array(
			'image_url'    => get_theme_file_uri( 'assets/images/home/instagram-02.webp' ),
			'image_alt'     => __( 'Satisfying texture shot of whipped milky dragon fruit pink facial foam cleanser', 'dragon-glow' ),
			'views'        => '2.8M',
			'creator'      => '@alexskinlab',
			'audio'        => __( 'Original audio', 'dragon-glow' ),
			'quote'        => __( '"The barrier reset is unmatched. Cleanses SPF completely without feeling tight."', 'dragon-glow' ),
			'product_name' => __( 'Milky Juice Cleanser', 'dragon-glow' ),
			'product_price' => '$38',
		),
		array(
			'image_url'    => get_theme_file_uri( 'assets/images/home/instagram-03.webp' ),
			'image_alt'     => __( 'Model with damp dewy glowing skin smiling into natural light while holding a high misting facial spray bottle', 'dragon-glow' ),
			'views'        => '940K',
			'creator'      => '@chloe_wellness',
			'audio'        => __( 'Original audio', 'dragon-glow' ),
			'quote'        => __( '"Keep this in my bag 24/7. 2 spritzes and my 3 PM slump skin looks instantly renewed!"', 'dragon-glow' ),
			'product_name' => __( 'Barrier Glow Mist', 'dragon-glow' ),
			'product_price' => '$42',
		),
		array(
			'image_url'    => get_theme_file_uri( 'assets/images/home/instagram-04.webp' ),
			'image_alt'     => __( 'Close up macro view of shimmering warm golden drops blending onto bare dewy collarbone', 'dragon-glow' ),
			'views'        => '3.1M',
			'creator'      => '@kaiaglows',
			'audio'        => __( 'Original audio', 'dragon-glow' ),
			'quote'        => __( '"Golden hour in a bottle! Mix it with my daily moisturizer and get compliments all day."', 'dragon-glow' ),
			'product_name' => __( 'Illuminator Drops', 'dragon-glow' ),
			'product_price' => '$46',
		),
	);

	/**
	 * Filter Instagram Reels cards data.
	 *
	 * @param array[] $reels Array of reel cards.
	 * @return array[]
	 */
	return (array) apply_filters( 'dg_instagram_reels_cards_data', $reels );
}
