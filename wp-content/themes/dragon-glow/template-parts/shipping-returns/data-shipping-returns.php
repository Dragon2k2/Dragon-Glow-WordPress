<?php
/**
 * Dragon Glow — Shipping & Returns Data
 * Single source of truth: chứa toàn bộ data cho trang Shipping & Returns.
 * Edit ở đây → mọi partial đều tự động cập nhật.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lấy toàn bộ data cho trang Shipping & Returns.
 * Dùng hook 'dg_shipping_returns_data' để extend/modify data từ bên ngoài.
 *
 * @return array
 */
function dg_shipping_returns_data(): array {
	$data = array(

		/* ── Stats nổi bật (count-up animation) ─────────────────────────────── */
		'stats' => array(
			array(
				'to'     => 60,
				'suffix' => '+',
				'label'  => esc_html__( 'Countries we ship to', 'dragon-glow' ),
				'accent' => 'gold',
				'icon'   => 'public',
			),
			array(
				'to'     => 30,
				'suffix' => esc_html__( ' days', 'dragon-glow' ),
				'label'  => esc_html__( 'Window to return an item', 'dragon-glow' ),
				'accent' => 'rose',
				'icon'   => 'schedule',
			),
			array(
				'to'     => 100,
				'suffix' => '%',
				'label'  => esc_html__( 'Recyclable packaging', 'dragon-glow' ),
				'accent' => 'green',
				'icon'   => 'eco',
			),
		),

		/* ── Phương thức giao hàng ──────────────────────────────────────────── */
		'delivery' => array(
			array(
				'method'    => esc_html__( 'Standard Shipping', 'dragon-glow' ),
				'time'      => esc_html__( '5–7 business days', 'dragon-glow' ),
				'cost'      => esc_html__( 'Free', 'dragon-glow' ),
				'highlight' => true,
			),
			array(
				'method'    => esc_html__( 'Express Shipping', 'dragon-glow' ),
				'time'      => esc_html__( '2–3 business days', 'dragon-glow' ),
				'cost'      => esc_html__( '$12.00', 'dragon-glow' ),
				'highlight' => false,
			),
			array(
				'method'    => esc_html__( 'Overnight Delivery', 'dragon-glow' ),
				'time'      => esc_html__( 'Next business day', 'dragon-glow' ),
				'cost'      => esc_html__( '$25.00', 'dragon-glow' ),
				'highlight' => false,
			),
			array(
				'method'    => esc_html__( 'International', 'dragon-glow' ),
				'time'      => esc_html__( '10–21 business days', 'dragon-glow' ),
				'cost'      => esc_html__( 'At checkout', 'dragon-glow' ),
				'highlight' => false,
			),
		),

		/* ── Quy trình đổi trả — 4 bước timeline ─────────────────────────── */
		'returns_steps' => array(
			array(
				'title'   => esc_html__( 'Request a Return', 'dragon-glow' ),
				'body'    => esc_html__( 'Contact our concierge within 30 days of delivery via the Contact page or concierge@dragonglow.com, with your order number and reason for return.', 'dragon-glow' ),
				'icon'    => 'chat_bubble_outline',
				'number'  => '01',
			),
			array(
				'title'   => esc_html__( 'Receive Your Label', 'dragon-glow' ),
				'body'    => esc_html__( 'We email a prepaid, carbon-neutral return label within one business day. Standard returns are always complimentary.', 'dragon-glow' ),
				'icon'    => 'local_shipping_outlined',
				'number'  => '02',
			),
			array(
				'title'   => esc_html__( 'Package & Send', 'dragon-glow' ),
				'body'    => esc_html__( 'Reuse the original box or any sturdy packaging, attach the label, and drop it at any authorised point. Keep your proof of postage.', 'dragon-glow' ),
				'icon'    => 'inventory_2_outlined',
				'number'  => '03',
			),
			array(
				'title'   => esc_html__( 'Refund Processed', 'dragon-glow' ),
				'body'    => esc_html__( 'Once received and inspected, a full refund returns to your original payment method within 5–7 business days, with a confirmation email.', 'dragon-glow' ),
				'icon'    => 'verified_outlined',
				'number'  => '04',
			),
		),

		/* ── Vùng phủ sóng giao hàng ──────────────────────────────────────── */
		'coverage' => array(
			array(
				'icon'  => 'flag_outlined',
				'title' => esc_html__( 'Domestic (US)', 'dragon-glow' ),
				'body'  => esc_html__( 'All 50 states and territories, including APO/FPO. Fulfilled via USPS, UPS, or FedEx based on your location.', 'dragon-glow' ),
			),
			array(
				'icon'  => 'public',
				'title' => esc_html__( 'International', 'dragon-glow' ),
				'body'  => esc_html__( 'Canada, UK, EU, Australia, Japan, South Korea, Singapore, UAE & more. Duties shown at checkout.', 'dragon-glow' ),
			),
		),

		/* ── Cam kết bao bì bền vững ─────────────────────────────────────── */
		'packaging' => array(
			array(
				'icon'  => 'eco',
				'title' => esc_html__( 'Responsibly Sourced', 'dragon-glow' ),
				'body'  => esc_html__( 'FSC-certified recycled cartons, fully recyclable glass, and vegetable-based ink free from harmful chemicals.', 'dragon-glow' ),
			),
			array(
				'icon'  => 'recycling',
				'title' => esc_html__( 'Zero-Waste Philosophy', 'dragon-glow' ),
				'body'  => esc_html__( 'Recycled, biodegradable cushioning. No plastic void fill — every element is made to be repurposed or composted.', 'dragon-glow' ),
			),
		),

		/* ── Trust badges ──────────────────────────────────────────────────── */
		'trust_badges' => array(
			array(
				'icon'  => 'lock_outlined',
				'label' => esc_html__( 'Secure Checkout', 'dragon-glow' ),
				'desc'  => esc_html__( '256-bit SSL encryption', 'dragon-glow' ),
			),
			array(
				'icon'  => 'eco',
				'label' => esc_html__( 'Eco-Friendly Packaging', 'dragon-glow' ),
				'desc'  => esc_html__( '100% recyclable materials', 'dragon-glow' ),
			),
			array(
				'icon'  => 'support_agent_outlined',
				'label' => esc_html__( 'Concierge Support', 'dragon-glow' ),
				'desc'  => esc_html__( 'Personalised guidance 24/7', 'dragon-glow' ),
			),
			array(
				'icon'  => 'recycling',
				'label' => esc_html__( 'Free Returns', 'dragon-glow' ),
				'desc'  => esc_html__( 'Prepaid return label included', 'dragon-glow' ),
			),
		),

		/* ── FAQ mini accordion ────────────────────────────────────────────── */
		'faq_mini' => array(
			array(
				'question' => esc_html__( 'How long does standard shipping take?', 'dragon-glow' ),
				'answer'   => esc_html__( 'Standard shipping within the US takes 5–7 business days. International orders typically arrive within 10–21 business days depending on the destination and customs processing.', 'dragon-glow' ),
			),
			array(
				'question' => esc_html__( 'What if my package arrives damaged?', 'dragon-glow' ),
				'answer'   => esc_html__( 'We take great care in packaging, but if your order arrives damaged, please contact us within 48 hours of delivery with photos. We will arrange a replacement or full refund as quickly as possible.', 'dragon-glow' ),
			),
			array(
				'question' => esc_html__( 'Do you ship internationally?', 'dragon-glow' ),
				'answer'   => esc_html__( 'Yes, we ship to over 60 countries worldwide. Import duties and taxes are calculated at checkout and are the responsibility of the recipient.', 'dragon-glow' ),
			),
			array(
				'question' => esc_html__( 'How do I initiate a return?', 'dragon-glow' ),
				'answer'   => esc_html__( 'Contact our concierge team within 30 days of delivery via the Contact page or concierge@dragonglow.com with your order number. We will email a prepaid return label within one business day.', 'dragon-glow' ),
			),
			array(
				'question' => esc_html__( 'When will I receive my refund?', 'dragon-glow' ),
				'answer'   => esc_html__( 'Refunds are processed within 5–7 business days of receiving your return. Depending on your bank, it may take an additional 3–5 business days for the funds to appear in your account.', 'dragon-glow' ),
			),
		),

		/* ── Thông tin giao hàng (footer note) ─────────────────────────────── */
		'delivery_note' => esc_html__( 'Mon–Fri excl. holidays. Orders after 2pm EST ship the next business day.', 'dragon-glow' ),
		'returns_exceptions' => esc_html__( 'Opened items may receive store credit; gift items credit-only; final-sale items are non-returnable.', 'dragon-glow' ),

	);

	return apply_filters( 'dg_shipping_returns_data', $data );
}
