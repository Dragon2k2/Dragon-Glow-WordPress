<?php
/**
 * Dragon Glow — Announcement Bar Data
 * Single source of truth for the scrolling promo messages above the header nav.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Messages shown in the announcement bar marquee.
 * Each item: 'icon' (Material Symbols name) + 'text' (already translated).
 * Order matters: the first item is the only one shown when the user prefers reduced motion.
 * Filter: 'dg_announcement_bar_data'.
 *
 * @return array<int, array{icon: string, text: string}>
 */
function dg_announcement_bar_data(): array {
	$items = array(
		array(
			'icon' => 'redeem',
			'text' => __( 'Complimentary Radiant Discovery Ritual Kit with all orders over $120', 'dragon-glow' ),
		),
		array(
			'icon' => 'local_shipping',
			'text' => __( 'Free standard shipping on orders over $75', 'dragon-glow' ),
		),
		array(
			'icon' => 'auto_awesome',
			'text' => __( '2026 Cellular Drop 04 is live: shop the Glaze Ritual today', 'dragon-glow' ),
		),
		array(
			'icon' => 'flight_takeoff',
			'text' => __( 'Order now: express delivery in 1–2 business days', 'dragon-glow' ),
		),
		array(
			'icon' => 'eco',
			'text' => __( '100% vegan and cruelty-free formulas', 'dragon-glow' ),
		),
		array(
			'icon' => 'medical_services',
			'text' => __( 'Dermatologist approved for mirror-clear, glass-like skin', 'dragon-glow' ),
		),
		array(
			'icon' => 'history',
			'text' => __( 'Love it or send it back: 30-day returns, no story required', 'dragon-glow' ),
		),
	);

	return apply_filters( 'dg_announcement_bar_data', $items );
}
