<?php
/**
 * Dragon Glow — FAQ Data
 * Single source of truth: toàn bộ câu hỏi/trả lời cho trang FAQ.
 * Sửa ở đây → mọi partial tự cập nhật.
 *
 * Giọng văn: tiết chế, giàu cảm xúc, hạn chế tính từ — giữ nguyên dữ kiện
 * (thời gian, chính sách, email) để không sai lệch thông tin.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lấy toàn bộ data trang FAQ.
 * Dùng hook 'dg_faq_data' để extend/override từ child theme hoặc plugin.
 *
 * @return array
 */
function dg_faq_data(): array {
	$contact_page = get_page_by_path( 'contact' );

	$data = array(

		// Đường dẫn liên hệ — tính một lần, dùng cho cả empty-state lẫn CTA.
		'contact_url' => $contact_page ? get_permalink( $contact_page->ID ) : home_url( '/contact/' ),

		/* ── Hero ─────────────────────────────────────────────────────────────── */
		'intro' => array(
			'eyebrow'  => esc_html__( 'Help Centre', 'dragon-glow' ),
			'title'    => esc_html__( 'Answers', 'dragon-glow' ),
			'subtitle' => esc_html__( 'The questions you arrive with, answered.', 'dragon-glow' ),
		),

		/* ── Nhóm câu hỏi ─────────────────────────────────────────────────────── */
		'groups' => array(

			array(
				'id'    => 'orders',
				'label' => esc_html__( 'Orders & Shipping', 'dragon-glow' ),
				'items' => array(
					array(
						'q' => esc_html__( 'How long until my order arrives?', 'dragon-glow' ),
						'a' => esc_html__( 'Within the US, 5–7 business days. Express in 2–3, or overnight — both at checkout. Beyond the US, 10–21 business days, depending on customs.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Do you ship internationally?', 'dragon-glow' ),
						'a' => esc_html__( 'Yes — to over 60 countries. Customs clears each parcel before it reaches you. Duties and taxes fall to the recipient, shown at checkout.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'How do I track my order?', 'dragon-glow' ),
						'a' => esc_html__( 'When your order ships, a tracking number reaches your inbox. Or open Track Your Order with your order ID and the email you used.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Can I change or cancel my order?', 'dragon-glow' ),
						'a' => esc_html__( 'We begin within 1–2 hours. Write to concierge@dragonglow.com before it ships, and we will do what we can.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'What if my package arrives damaged?', 'dragon-glow' ),
						'a' => esc_html__( 'Tell us within 48 hours, with photos of the parcel and what is inside. A replacement or a refund follows.', 'dragon-glow' ),
					),
				),
			),

			array(
				'id'    => 'products',
				'label' => esc_html__( 'Products & Ingredients', 'dragon-glow' ),
				'items' => array(
					array(
						'q' => esc_html__( 'Will they suit sensitive skin?', 'dragon-glow' ),
						'a' => esc_html__( 'Made for every skin, sensitive included. No synthetic fragrance, no parabens, no sulphates. Each product page lists the ingredients. When in doubt, patch test, or ask us.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Are they vegan and cruelty-free?', 'dragon-glow' ),
						'a' => esc_html__( 'Yes — vegan, and never tested on animals, at any stage. No ingredient comes from one.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'How should I store them?', 'dragon-glow' ),
						'a' => esc_html__( 'Cool, dry, out of the sun. Our airless pumps hold the actives. Once opened, use within 12 months, cap closed.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Where are they made?', 'dragon-glow' ),
						'a' => esc_html__( 'Designed in our New York studio. Made in a GMP facility in the United States, with botanicals sourced worldwide.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Do you offer samples?', 'dragon-glow' ),
						'a' => esc_html__( 'With each seasonal launch, at times. Our newsletter tells you first. Full sizes return within 30 days, if they are not yours.', 'dragon-glow' ),
					),
				),
			),

			array(
				'id'    => 'returns',
				'label' => esc_html__( 'Returns & Refunds', 'dragon-glow' ),
				'items' => array(
					array(
						'q' => esc_html__( 'What is your return policy?', 'dragon-glow' ),
						'a' => esc_html__( 'Unopened, unused, within 30 days. Opened items may earn partial credit, at our discretion. Final-sale items and gifts stay with you. The full steps live on Shipping & Returns.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'How long does a refund take?', 'dragon-glow' ),
						'a' => esc_html__( 'Once your return arrives and clears, 5–7 business days to your original method. A note confirms it. Your bank may add 3–5 more.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Do you offer exchanges?', 'dragon-glow' ),
						'a' => esc_html__( 'Not directly. Return the first, order the next.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Who pays for return shipping?', 'dragon-glow' ),
						'a' => esc_html__( 'We do — a prepaid, carbon-neutral label, yours at no cost. Our error? Every cost is ours.', 'dragon-glow' ),
					),
				),
			),

			array(
				'id'    => 'account',
				'label' => esc_html__( 'Account', 'dragon-glow' ),
				'items' => array(
					array(
						'q' => esc_html__( 'Do I need an account to order?', 'dragon-glow' ),
						'a' => esc_html__( 'No — guest checkout is open. An account keeps your favourites, your orders, and early access close.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'How do I reset my password?', 'dragon-glow' ),
						'a' => esc_html__( 'On the login page, choose Forgot your password. A reset link follows in minutes. Nothing there? Check spam, or reach us.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'How is my information protected?', 'dragon-glow' ),
						'a' => esc_html__( 'Encrypted over SSL, held to privacy law. We never sell your data. The Privacy Policy holds the detail.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Can I delete my account?', 'dragon-glow' ),
						'a' => esc_html__( 'Yes. Write to concierge@dragonglow.com — your account and its data clear within 30 days.', 'dragon-glow' ),
					),
				),
			),
		),
	);

	return apply_filters( 'dg_faq_data', $data );
}
