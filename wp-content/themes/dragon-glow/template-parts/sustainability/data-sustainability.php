<?php
/**
 * Dragon Glow — Sustainability Data
 * Single source of truth for the Sustainability page.
 * Mirrors the stitch_dragon_glow_sustainability_page prototype.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * All data for the Sustainability page.
 * Filter: 'dg_sustainability_data'.
 *
 * @return array
 */
function dg_sustainability_data(): array {
	$dir = get_template_directory_uri() . '/assets/images/sustainability';

	$data = array(

		/* ── Hero ───────────────────────────────────────────────────────── */
		'hero' => array(
			'badge'     => 'Sustainability',
			'headline'  => 'As radiant as your skin.',
			'body'      => "We take only what the earth renews.\nWe return what we can.",
			'cta_text'  => 'See our practice',
			'cta_url'   => '#practices',
			'image_url' => $dir . '/hero.webp',
			'image_alt' => 'A serene, vertical photograph of a lush botanical farm field at dawn. A soft, hazy mist clings to the ground, illuminated by gentle, warm morning light. Dewdrops glisten on delicate green leaves.',
		),

		/* ── Intro pullquote ───────────────────────────────────────────── */
		'intro' => array(
			'text' => 'Sustainability and ethics are at our core',
		),

		/* ── Commitments (3 tiles) ─────────────────────────────────────── */
		'commitments' => array(
			'heading' => 'What we hold to',
			'items'   => array(
				array(
					'icon'  => 'eco',
					'title' => 'Sustainably Sourced',
					'body'  => 'We partner with family-owned farms that practice regenerative agriculture.',
				),
				array(
					'icon'  => 'cruelty_free',
					'title' => 'Cruelty-Free',
					'body'  => 'Certified vegan. No animal testing, at any stage.',
				),
				array(
					'icon'  => 'water_drop',
					'title' => 'Minimalist Luxury',
					'body'  => 'We cut the fillers. More in the formula, less in the waste.',
				),
			),
		),

		/* ── Sourcing (Traceable. Always.) ─────────────────────────────── */
		'sourcing' => array(
			'headline'  => 'Traceable. Always.',
			'body'      => "Each batch carries a code, etched on the carton.\nIt walks back to the field the botanicals left.",
			'regions'   => array( 'Japan', 'France', 'Pacific Northwest' ),
			'footnote'  => 'Micro-farms. Regenerative practice.',
			'image_url' => $dir . '/sourcing.webp',
			'image_alt' => 'Close-up photograph of gently cupped hands holding fresh, raw botanicals—delicate petals and leaves—next to a minimalist, textured paper skincare carton.',
		),

		/* ── Packaging (3 cards) ───────────────────────────────────────── */
		'packaging' => array(
			'heading' => 'What it comes in',
			'items'   => array(
				array(
					'title' => 'Glass',
					'body'  => 'Glass vessels. Curbside, in most cities.',
				),
				array(
					'title' => 'Paper',
					'body'  => 'FSC-certified cartons. Vegetable-based inks.',
				),
				array(
					'title' => 'Return',
					'body'  => 'Pumps and droppers travel back to us, prepaid.',
				),
			),
		),

		/* ── Refill ────────────────────────────────────────────────────── */
		'refill' => array(
			'headline'  => 'Send it back. Begin again.',
			'body'      => "The hero serums refill.\nA credit lands in your account toward the next ritual.",
			'image_url' => $dir . '/refill.webp',
			'image_alt' => 'A meticulously styled still life showing an empty, frosted glass serum bottle lying elegantly next to a minimalist, eco-friendly prepaid return envelope.',
		),

		/* ── Carbon ────────────────────────────────────────────────────── */
		'carbon' => array(
			'icon'     => 'air',
			'headline' => 'Carbon, accounted for',
			'body'     => "Every shipment is offset through verified reforestation and mangrove restoration.\nThe certificate rides with the parcel.",
		),

		/* ── Accountability ────────────────────────────────────────────── */
		'accountability' => array(
			'headline'  => 'Audited. Every year.',
			'body'      => "Labour, water, waste, energy — reviewed by an independent third party.\nThe summary ships in our annual impact report.",
			'image_url' => $dir . '/accountability.webp',
			'image_alt' => 'A sweeping, wide-angle photograph of a vibrant botanical farm field under a clear, bright sky. Small figures of workers can be seen gently tending to the crops in the distance.',
		),

		/* ── Numbers strip ─────────────────────────────────────────────── */
		'numbers' => array(
			'items' => array(
				'100% Vegan',
				'Cruelty-Free Certified',
				'Carbon-Neutral Shipping',
				'FSC-Certified Paper',
			),
		),

		/* ── Closing CTA ───────────────────────────────────────────────── */
		'closing' => array(
			'headline' => 'The earth gives. We give back.',
			'cta_text' => 'Discover Our Story',
			'cta_url'  => get_permalink( get_page_by_path( 'our-story' ) ) ?: home_url( '/our-story/' ),
		),
	);

	return apply_filters( 'dg_sustainability_data', $data );
}