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
			'eyebrow'  => esc_html__( 'Concierge', 'dragon-glow' ),
			'title'    => esc_html__( 'The Concierge', 'dragon-glow' ),
			'subtitle' => esc_html__( 'Quiet answers, for the curious. No flourish, no filler — only the facts that shape your ritual.', 'dragon-glow' ),
		),

		/* ── Sidebar categories ───────────────────────────────────────────────── */
		'categories' => array(
			array(
				'id'    => 'glow-ritual',
				'label' => esc_html__( 'The Glow Ritual', 'dragon-glow' ),
			),
			array(
				'id'    => 'ingredients',
				'label' => esc_html__( 'Ingredient Transparency', 'dragon-glow' ),
			),
			array(
				'id'    => 'orders',
				'label' => esc_html__( 'Orders & Shipping', 'dragon-glow' ),
			),
			array(
				'id'    => 'sustainability',
				'label' => esc_html__( 'Sustainability Practice', 'dragon-glow' ),
			),
		),

		/* ── Nhóm câu hỏi — id khớp categories ───────────────────────────────── */
		'groups' => array(

			array(
				'id'    => 'glow-ritual',
				'label' => esc_html__( 'The Glow Ritual', 'dragon-glow' ),
				'items' => array(
					array(
						'q' => esc_html__( 'What makes the Dragon Glow finish different?', 'dragon-glow' ),
						'a' => esc_html__( 'No glitter. No synthetic mica. We suspend micro-crushed minerals in a botanical serum, so the light moves from beneath the skin rather than sitting on top of it.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Can I layer the serums with retinol?', 'dragon-glow' ),
						'a' => esc_html__( 'Yes. We suggest our serums in the morning, retinol at night. On sensitive skin, alternate nights. The barrier stays intact either way.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'How long does each formulation last?', 'dragon-glow' ),
						'a' => esc_html__( 'Six months after opening. The dark glass and airless pump do the work — no synthetic preservatives needed.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Walk me through the morning ritual.', 'dragon-glow' ),
						'a' => esc_html__( 'Milk cleanser. Botanical essence. Two drops of radiance serum. Moisturiser. SPF mist. One breath between each step. The skin tells you when it is ready for the next.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Is the range safe during pregnancy?', 'dragon-glow' ),
						'a' => esc_html__( 'Most of it, yes. Pause the retinol alternative. For everything else, ask your physician, then write to us — we will pare the ritual back to what you need.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Can I try before I commit?', 'dragon-glow' ),
						'a' => esc_html__( 'A discovery set ships with each seasonal launch. The newsletter carries the first invitation. If a full size does not suit, return it within 30 days — opened, sealed, either way.', 'dragon-glow' ),
					),
				),
			),

			array(
				'id'    => 'ingredients',
				'label' => esc_html__( 'Ingredient Transparency', 'dragon-glow' ),
				'items' => array(
					array(
						'q' => esc_html__( 'Where do the botanicals come from?', 'dragon-glow' ),
						'a' => esc_html__( 'Micro-farms in Japan, France, the Pacific Northwest. All run on regenerative practices. Each batch carries a trace code back to the field it left.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Are the formulas vegan and cruelty-free?', 'dragon-glow' ),
						'a' => esc_html__( 'Vegan. Always. No animal testing at any stage — not the raw material, not the finished product. The certificate renews each year and sits at the foot of every product page.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'What is left out?', 'dragon-glow' ),
						'a' => esc_html__( 'Parabens, sulphates, synthetic fragrance, phthalates, mineral oil, formaldehyde donors. The full list of exclusions, with the reason for each, lives in the ingredient glossary.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Will the formulas suit sensitive skin?', 'dragon-glow' ),
						'a' => esc_html__( 'Built for it. Every product page carries the full deck. When in doubt, a 24-hour patch test on the inner forearm. Or write — we will walk through your routine with you.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Where are the formulations made?', 'dragon-glow' ),
						'a' => esc_html__( 'Designed in New York. Compounded in a GMP-certified facility in the United States. The botanicals are traced back to their farms; the hands that make them are paid a wage that lets them stay.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'How does the formula stay fresh without harsh preservatives?', 'dragon-glow' ),
						'a' => esc_html__( 'Airless pumps. UV-protective glass. Fermentation-derived preservatives, chosen for their work, not their harm. The integrity holds from the first drop to the last.', 'dragon-glow' ),
					),
				),
			),

			array(
				'id'    => 'orders',
				'label' => esc_html__( 'Orders & Shipping', 'dragon-glow' ),
				'items' => array(
					array(
						'q' => esc_html__( 'How long until my order arrives?', 'dragon-glow' ),
						'a' => esc_html__( 'Inside the US: 5–7 business days. Express in 2–3. Overnight at checkout. Outside the US: 10–21 days, depending on customs.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Do you ship internationally?', 'dragon-glow' ),
						'a' => esc_html__( 'To 60+ countries. Duties and taxes are calculated at checkout and paid by the recipient. Customs holds each parcel before it moves on.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'How do I track my order?', 'dragon-glow' ),
						'a' => esc_html__( 'A tracking number arrives in your inbox the moment the parcel ships. Or open Track Your Order with your order ID and the email used at purchase.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Can I change or cancel my order?', 'dragon-glow' ),
						'a' => esc_html__( 'We pack within 1–2 hours. Write to concierge@dragonglow.com before the label prints and we will do what we can.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'What if my package arrives damaged?', 'dragon-glow' ),
						'a' => esc_html__( 'Tell us within 48 hours. A photograph of the parcel and the piece inside. A replacement or a refund follows without delay.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Do you offer gift wrapping?', 'dragon-glow' ),
						'a' => esc_html__( 'Every order ships in our ivory linen, sealed with a wax stamp. A handwritten note is added on request. No charge.', 'dragon-glow' ),
					),
				),
			),

			array(
				'id'    => 'sustainability',
				'label' => esc_html__( 'Sustainability Practice', 'dragon-glow' ),
				'items' => array(
					array(
						'q' => esc_html__( 'Is the packaging recyclable?', 'dragon-glow' ),
						'a' => esc_html__( 'Glass vessels. Paper cartons. Soy-based inks. Curbside, in most cities. Pumps and droppers, mixed-material by nature, return to us at no cost — we send them back into the loop.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Do you offer refills?', 'dragon-glow' ),
						'a' => esc_html__( 'The hero serums refill. Send the empty vessel back with the prepaid label in your order. A credit lands in your account toward the next ritual.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'What does carbon-neutral mean in practice?', 'dragon-glow' ),
						'a' => esc_html__( 'Every shipment is offset through verified reforestation and mangrove restoration. The certificate rides with the parcel — open the tracking link to see where your offset landed.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Where does the paper come from?', 'dragon-glow' ),
						'a' => esc_html__( 'FSC-certified. Milled in the Pacific Northwest. Printed with vegetable-based inks. No plastic fillers. The carton is built to protect the vessel, not to fill a landfill.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'Are the manufacturing facilities audited?', 'dragon-glow' ),
						'a' => esc_html__( 'Annually, by an independent third party. Labour, water, waste streams, energy sourcing. The summary ships in our annual impact report.', 'dragon-glow' ),
					),
					array(
						'q' => esc_html__( 'How do I dispose of an empty vessel?', 'dragon-glow' ),
						'a' => esc_html__( 'Rinse it. Send it in our prepaid envelope. We send it through the glass-recovery programme. Or keep it — a bud vase, a travel bottle, a quiet object on a shelf.', 'dragon-glow' ),
					),
				),
			),
		),
	);

	return apply_filters( 'dg_faq_data', $data );
}