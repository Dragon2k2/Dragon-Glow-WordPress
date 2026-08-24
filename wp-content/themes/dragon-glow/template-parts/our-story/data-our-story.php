<?php
/**
 * Dragon Glow — Our Story data
 *
 * Single source of truth cho trang Our Story: hero + 3 sections.
 * Mỗi section có markup riêng (philosophy/alchemy/commitment có layout khác
 * nhau) nên tách thành các section-* parts, data chỉ chứa text + image URLs.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hero — full-bleed image, eyebrow, headline, body, CTA anchor.
 *
 * @return array{eyebrow: string, headline: string, body: string, cta_label: string, cta_href: string, image_url: string, image_alt: string}
 */
function dg_our_story_hero_data(): array {
	$data = array(
		'eyebrow'    => __( 'Established in Wisdom', 'dragon-glow' ),
		'headline'   => __( 'The Essence of Radiance', 'dragon-glow' ),
		'body'       => __( "Blending ancient heritage with clinical precision to unveil your skin's natural luminous potential.", 'dragon-glow' ),
		'cta_label'  => __( 'Watch Our Story', 'dragon-glow' ),
		'cta_href'   => '#philosophy',
		'image_url'  => get_theme_file_uri( 'assets/images/our-story/our-story1.webp' ),
		'image_alt'  => __( 'Serene Botanical Garden', 'dragon-glow' ),
	);

	/** This filter is documented in template-parts/our-story/data-our-story.php */
	return (array) apply_filters( 'dg_our_story_hero_data', $data );
}

/**
 * Philosophy section — left text + right "luminous heritage" card.
 *
 * @return array{eyebrow: string, headline: string, paragraphs: string[], card_title: string, card_quote: string}
 */
function dg_our_story_philosophy_data(): array {
	$data = array(
		'eyebrow'     => __( 'Our Philosophy', 'dragon-glow' ),
		'headline'    => __( 'Illumination through ancient botanical science.', 'dragon-glow' ),
		'paragraphs'  => array(
			__( 'At Dragon Glow, we believe radiance is more than a surface quality—it is the outward manifestation of inner health and ancestral resilience. Our journey began in the secluded high-altitude gardens where rare botanicals have thrived for centuries.', 'dragon-glow' ),
			__( 'We combine these "luminous icons" of the plant world with modern clinical delivery systems, ensuring that every drop honors the heritage of the past while meeting the rigorous demands of today\'s skin science.', 'dragon-glow' ),
		),
		'card_title'  => __( 'Luminous Heritage', 'dragon-glow' ),
		'card_quote'  => __( 'The skin reflects the light we cultivate within. We simply provide the tools to let it shine.', 'dragon-glow' ),
	);

	/** This filter is documented in template-parts/our-story/data-our-story.php */
	return (array) apply_filters( 'dg_our_story_philosophy_data', $data );
}

/**
 * Alchemy section — 3 numbered ingredient tiles (Wild-Harvested Jasmine /
 * Golden Nectar Honey / High-Altitude Green Tea) + right image.
 *
 * @return array{eyebrow: string, headline: string, image_url: string, image_alt: string, items: array<int, array{num: string, title: string, body: string}>}
 */
function dg_our_story_alchemy_data(): array {
	$data = array(
		'eyebrow'    => __( 'The Alchemy', 'dragon-glow' ),
		'headline'   => __( 'Pure. Potent. Proven.', 'dragon-glow' ),
		'image_url'  => get_theme_file_uri( 'assets/images/our-story/our-story2.png' ),
		'image_alt'  => __( 'Premium Ingredients', 'dragon-glow' ),
		'items'      => array(
			array(
				'num'   => '01',
				'title' => __( 'Wild-Harvested Jasmine', 'dragon-glow' ),
				'body'  => __( 'Hand-picked at dawn to preserve the delicate essential oils that stimulate cellular renewal and calm inflammation.', 'dragon-glow' ),
			),
			array(
				'num'   => '02',
				'title' => __( 'Golden Nectar Honey', 'dragon-glow' ),
				'body'  => __( 'A natural humectant that draws moisture deep into the dermis, providing a plump, glass-like finish.', 'dragon-glow' ),
			),
			array(
				'num'   => '03',
				'title' => __( 'High-Altitude Green Tea', 'dragon-glow' ),
				'body'  => __( 'Loaded with polyphenols that shield the skin from modern environmental oxidative stress.', 'dragon-glow' ),
			),
		),
	);

	/** This filter is documented in template-parts/our-story/data-our-story.php */
	return (array) apply_filters( 'dg_our_story_alchemy_data', $data );
}

/**
 * Commitment section — center intro + 4 bento tiles.
 *
 * @return array{eyebrow: string, headline: string, tiles: array<int, array{span: string, variant: string, icon?: string, title: string, body: string, icons?: string[]}>}
 */
function dg_our_story_commitment_data(): array {
	$data = array(
		'eyebrow'  => __( 'Our Commitment', 'dragon-glow' ),
		'headline' => __( 'Defining High-End Clean Beauty', 'dragon-glow' ),
		'tiles'    => array(
			array(
				// Span: how many columns on md+ (bento layout).
				'span'    => '2',
				// Variant drives background + text colour class (light/dark/lightest).
				'variant' => 'light',
				'icon'    => 'eco',
				'title'   => __( 'Sustainably Sourced', 'dragon-glow' ),
				'body'    => __( 'We partner exclusively with family-owned botanical farms that practice regenerative agriculture, ensuring the earth remains as radiant as your skin.', 'dragon-glow' ),
				// Optional decorative icon row at bottom.
				'icons'   => array( 'eco', 'psychology_alt', 'potted_plant' ),
			),
			array(
				'span'    => '1',
				'variant' => 'dark',
				'title'   => __( 'Clinical Trust', 'dragon-glow' ),
				'body'    => __( 'Every formula undergoes three phases of dermatological testing to guarantee safety for sensitive complexions.', 'dragon-glow' ),
			),
			array(
				'span'    => '1',
				'variant' => 'light',
				'title'   => __( 'Cruelty-Free', 'dragon-glow' ),
				'body'    => __( 'Beauty should never come at a cost to others. We are proudly certified vegan and cruelty-free.', 'dragon-glow' ),
			),
			array(
				'span'    => '2',
				'variant' => 'lightest',
				'title'   => __( 'Minimalist Luxury', 'dragon-glow' ),
				'body'    => __( 'We eliminate unnecessary fillers to prioritize high concentrations of active ingredients, resulting in more potent rituals and less waste.', 'dragon-glow' ),
			),
		),
	);

	/** This filter is documented in template-parts/our-story/data-our-story.php */
	return (array) apply_filters( 'dg_our_story_commitment_data', $data );
}
