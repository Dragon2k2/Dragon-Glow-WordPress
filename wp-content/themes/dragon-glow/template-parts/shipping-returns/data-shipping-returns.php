<?php
/**
 * Dragon Glow — Shipping & Returns Data
 * Single source of truth for the Shipping & Returns page.
 * Mirrors the stitch_dragon_glow_logistics_design prototype.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the orders endpoint URL.
 * Falls back to a plain `/my-account/orders/` permalink when WooCommerce is inactive.
 *
 * @return string
 */
function dg_sr_orders_url(): string {
	if ( function_exists( 'wc_get_account_endpoint_url' ) ) {
		return (string) wc_get_account_endpoint_url( 'orders' );
	}
	return home_url( '/my-account/orders/' );
}

/**
 * All data for the Shipping & Returns page.
 * Filter: 'dg_shipping_returns_data'.
 *
 * @return array
 */
function dg_shipping_returns_data(): array {
	$data = array(

		/* ── Hero ───────────────────────────────────────────────────────── */
		'hero' => array(
			'title'    => 'Shipping & Returns',
			'subtitle' => 'Held with care. Released with ease.',
		),

		/* ── Shipping methods (mirror stitch) ──────────────────────────── */
		'shipping_methods' => array(
			array(
				'icon'  => 'local_shipping',
				'title' => 'Standard',
				'time'  => '3–5 business days',
				'price' => 'Complimentary',
			),
			array(
				'icon'  => 'flight_takeoff',
				'title' => 'Express',
				'time'  => '1–2 business days',
				'price' => '$25 flat',
			),
		),

		/* ── Journey timeline (3 phases, mirror stitch) ─────────────────── */
		'timeline' => array(
			array(
				'label' => 'Phase 01',
				'title' => 'Held',
				'body'  => 'Your order is in our hands. Read twice, packed once.',
			),
			array(
				'label' => 'Phase 02',
				'title' => 'Wrapped',
				'body'  => 'Recycled paper. Soft folds. Climate-controlled throughout.',
			),
			array(
				'label' => 'Phase 03',
				'title' => 'Sent',
				'body'  => 'Handed to our couriers. Tracked from door to door.',
			),
		),

		/* ── Returns & exchanges card ───────────────────────────────────── */
		'returns' => array(
			'title'    => 'Returns & Exchanges',
			'body'     => 'If something feels off, send it back. Thirty days. No story required.',
			'cta_text' => 'Begin a return',
			'cta_url'  => dg_sr_orders_url(),
		),

		/* ── FAQ mini accordion (mirror stitch) ─────────────────────────── */
		'faqs' => array(
			array(
				'question' => 'International?',
				'answer'   => 'Yes. Sixty countries. Duties land at checkout — yours to carry.',
			),
			array(
				'question' => 'Damaged?',
				'answer'   => 'Write to us within 48 hours. Send a photo. We move fast.',
			),
			array(
				'question' => 'Late?',
				'answer'   => 'Give it five business days. Then tell us. We trace it with you.',
			),
			array(
				'question' => 'Gift wrap?',
				'answer'   => 'Add it at checkout. Soft paper, hand-tied ribbon, no price inside.',
			),
			array(
				'question' => 'Wrong shade?',
				'answer'   => 'Send it back unopened. We swap it, no questions.',
			),
		),
	);

	return apply_filters( 'dg_shipping_returns_data', $data );
}