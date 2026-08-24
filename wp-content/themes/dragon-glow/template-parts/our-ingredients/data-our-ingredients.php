<?php
/**
 * Dragon Glow — Our Ingredients Data
 * Single source of truth for the Our Ingredients page.
 * Mirrors the stitch_dragon_glow_ingredients_page prototype.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * All data for the Our Ingredients page.
 * Filter: 'dg_our_ingredients_data'.
 *
 * @return array
 */
function dg_our_ingredients_data(): array {
	$data = array(

		/* ── Hero ───────────────────────────────────────────────────────── */
		'hero' => array(
			'badge'       => 'Our Ingredients',
			'headline'    => 'From the source.',
			'accent_line' => true,
			'body'        => "We name what we use.\nWe show where it comes from.",
			'cta_text'    => 'See the sources',
			'cta_url'     => '#traceable',
			'image_url'   => get_template_directory_uri() . '/assets/images/our-ingredients/hero.webp',
			'image_alt'   => 'A hyper-realistic, editorial-style photograph of a single dragon fruit sliced in half, resting on a stark, pale cream studio surface.',
		),

		/* ── Pullquote ─────────────────────────────────────────────────── */
		'pullquote' => array(
			'text' => 'Nothing hidden. Nothing we cannot trace.',
		),

		/* ── Signature ingredient: Dragon Fruit Enzyme ───────────────────── */
		'signature' => array(
			'badge'     => 'The Signature',
			'title'     => 'Dragon Fruit Enzyme',
			'subtitle'  => 'Cold-pressed from Hylocereus.',
			'body'      => "It wakes the skin.\nIt asks for nothing in return.",
			'image_url' => get_template_directory_uri() . '/assets/images/our-ingredients/signature.webp',
			'image_alt' => 'A macroscopic, high-fashion photograph capturing the exact moment clear, viscous fluid is cold-pressed from raw, vibrant pink dragon fruit chunks.',
		),

		/* ── Ingredients grid ──────────────────────────────────────────── */
		'ingredients' => array(
			array(
				'name'         => 'Centella Asiatica',
				'source'       => 'Grown in highland fields.',
				'description'  => 'It calms what the day leaves behind.',
				'offset_class' => '',
				'image_url'    => get_template_directory_uri() . '/assets/images/our-ingredients/ingredient-centella.webp',
				'image_alt'    => 'A minimalist, top-down photograph of a single, perfectly formed Centella Asiatica leaf resting on a smooth, pale stone surface.',
			),
			array(
				'name'         => 'Hyaluronic Acid',
				'source'       => 'Layered in three weights.',
				'description'  => 'Water, held where skin needs it.',
				'offset_class' => 'dg-oi-offsets--mt12',
				'image_url'    => get_template_directory_uri() . '/assets/images/our-ingredients/ingredient-hyaluronic-acid.webp',
				'image_alt'    => 'An abstract, macro photograph of a perfectly spherical drop of clear, viscous water suspended against a pristine, soft white background.',
			),
			array(
				'name'         => 'Colloidal Gold',
				'source'       => 'Suspended in trace amounts.',
				'description'  => 'A weight you wear, not see.',
				'offset_class' => 'dg-oi-offsets--mt24',
				'image_url'    => get_template_directory_uri() . '/assets/images/our-ingredients/ingredient-colloidal-gold.webp',
				'image_alt'    => 'A macro, editorial photograph of fine, shimmering 24k gold flakes suspended in a clear, transparent gel.',
			),
			array(
				'name'         => 'Papaya & Pineapple Enzymes',
				'source'       => 'Pressed from ripe fruit.',
				'description'  => 'They lift the old, they keep the new.',
				'offset_class' => 'dg-oi-offsets--col3',
				'image_url'    => get_template_directory_uri() . '/assets/images/our-ingredients/ingredient-papaya-pineapple.webp',
				'image_alt'    => 'An editorial still-life photograph featuring a geometric cube of ripe, glowing orange papaya resting next to a precise, sharp slice of golden pineapple.',
			),
			array(
				'name'         => 'Jasmine, Green Tea, Honey',
				'source'       => 'Gathered by hand at dawn.',
				'description'  => 'The plants our grandmothers knew.',
				'offset_class' => 'dg-oi-offsets--mt12',
				'image_url'    => get_template_directory_uri() . '/assets/images/our-ingredients/ingredient-jasmine-green-tea-honey.webp',
				'image_alt'    => 'A delicate, artistic photograph of a single white jasmine flower, a fresh green tea leaf, and a small, perfect drop of golden honey arranged linearly on a raw, unbleached linen surface.',
			),
		),

		/* ── Traceable section ──────────────────────────────────────────── */
		'traceable' => array(
			'headline'   => 'Traceable. Always.',
			'body'       => "Each batch carries a code, etched on the carton.\nIt walks back to the field the plants left.",
			'countries'  => array( 'Japan', 'France', 'Pacific Northwest' ),
			'footnote'   => 'Micro-farms. Regenerative practice.',
			'image_url'  => get_template_directory_uri() . '/assets/images/our-ingredients/traceable.webp',
			'image_alt'  => 'A luxury skincare serum glass bottle with DRAGON GLOW branding, minimal editorial design, sitting next to raw botanical ingredients.',
		),

		/* ── What we leave out ──────────────────────────────────────────── */
		'leave_out' => array(
			'headline' => 'What we leave out.',
			'items'    => array(
				'No fillers.',
				'No animal testing.',
				'Nothing the formula does not need.',
			),
		),

		/* ── Outro ──────────────────────────────────────────────────────── */
		'outro' => array(
			'headline' => 'Made from what the earth gives',
			'cta_text' => 'Discover the ritual',
			'cta_url'  => home_url( '/shop/' ),
		),
	);

	return apply_filters( 'dg_our_ingredients_data', $data );
}
