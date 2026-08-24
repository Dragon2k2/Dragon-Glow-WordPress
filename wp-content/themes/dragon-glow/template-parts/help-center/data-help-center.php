<?php
/**
 * Dragon Glow — Help Center Data
 * Single source of truth: toàn bộ nội dung cho trang Help Center.
 *
 * Năm nhóm câu hỏi:
 *  1. Orders & Delivery   — theo dõi, giao hàng
 *  2. Account & Privacy   — tài khoản, bảo mật, dữ liệu cá nhân
 *  3. The Glow Ritual     — cách dùng, layering, bảo quản
 *  4. Ingredient Transparency — nguồn gốc, vegan, hạn chế
 *  5. Sustainability Practice — đóng gói, refill, trả hũ
 *
 * Voice: tiết chế, giàu cảm xúc, hạn chế tính từ — giống data-faq.php.
 * Filter: 'dg_help_center_data' — extend/override từ child theme hoặc plugin.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL trang Track Order (hoặc trang orders của khách).
 * Ưu tiên WC endpoint khi plugin bật; fallback permalink cố định.
 *
 * @return string
 */
function dg_hc_orders_url(): string {
	if ( function_exists( 'wc_get_account_endpoint_url' ) ) {
		return (string) wc_get_account_endpoint_url( 'orders' );
	}
	return home_url( '/my-account/orders/' );
}

/**
 * Toàn bộ data trang Help Center.
 *
 * @return array
 */
function dg_hc_data(): array {
	$contact_page = dg_hc_data_contact_page();
	$contact_url  = $contact_page ? get_permalink( $contact_page->ID ) : home_url( '/contact/' );

	$data = array(

		/* ── Hero ─────────────────────────────────────────────────────────── */
		'hero' => array(
			'title'    => esc_html__( 'How can we assist?', 'dragon-glow' ),
			'subtitle' => esc_html__( 'Quiet answers, no flourish. Type a word and the page narrows to what matters.', 'dragon-glow' ),
		),

		/* ── Search ───────────────────────────────────────────────────────── */
		'search' => array(
			'placeholder' => esc_html__( 'Search orders, ritual, ingredients, or sustainability', 'dragon-glow' ),
			'aria_label'  => esc_html__( 'Search the help center', 'dragon-glow' ),
		),

		/* ── Sections ─────────────────────────────────────────────────────── */
		'sections' => array(

			'orders' => array(
				'id'    => 'orders',
				'title' => esc_html__( 'Orders & Delivery', 'dragon-glow' ),
				'icon'  => 'local_shipping',
				'faqs'  => array(
					array(
						'question' => esc_html__( 'Where is my recent order?', 'dragon-glow' ),
						'answer'   => esc_html__( "A tracking number arrives in your inbox the moment the parcel leaves our hands. Open it; the parcel moves through the carrier's portal in real time. Or open Track Your Order with the email used at purchase and the order ID.", 'dragon-glow' ),
						'has_cta'  => true,
						'cta_url'  => dg_hc_orders_url(),
						'cta_label'=> esc_html__( 'Track Order', 'dragon-glow' ),
					),
					array(
						'question' => esc_html__( 'Why is my tracking information not updating?', 'dragon-glow' ),
						'answer'   => esc_html__( "Carriers refresh their nodes at intervals, sometimes hourly, sometimes by the next morning. Customs holds each international parcel before it moves on. Give it 24 to 48 hours; if the page still sleeps, write to us and we will reach the carrier on your behalf.", 'dragon-glow' ),
					),
					array(
						'question' => esc_html__( 'Can I change the shipping address after I order?', 'dragon-glow' ),
						'answer'   => esc_html__( "We pack within one to two hours of purchase, so the window is brief — usually under thirty minutes. Open the order confirmation email and follow the link to amend. Once the label prints, the address belongs to the parcel.", 'dragon-glow' ),
					),
				),
			),

			'account' => array(
				'id'    => 'account',
				'title' => esc_html__( 'Account & Privacy', 'dragon-glow' ),
				'icon'  => 'shield',
				'faqs'  => array(
					array(
						'question' => esc_html__( 'How do I reset a forgotten password?', 'dragon-glow' ),
						'answer'   => esc_html__( "Open the sign-in page and choose Recover Access. A secure link is sent to the email on file; it lives for fifteen minutes, then it dissolves. Open it on a device you trust, choose a new phrase, and you are back in.", 'dragon-glow' ),
					),
					array(
						'question' => esc_html__( 'Can I enable a second layer of sign-in?', 'dragon-glow' ),
						'answer'   => esc_html__( "Yes. Open Security Preferences inside your account and switch on Multi-Factor Authentication. The page accepts any TOTP application — Google Authenticator, Authy, 1Password — and SMS, where the carrier permits it.", 'dragon-glow' ),
						'has_cta'  => true,
						'cta_url'  => $contact_url,
						'cta_label'=> esc_html__( 'Configure MFA', 'dragon-glow' ),
					),
					array(
						'question' => esc_html__( 'Where do I request that my data be erased?', 'dragon-glow' ),
						'answer'   => esc_html__( "Write to the privacy desk. We anonymize or erase what the law allows; some transactional records stay, by tax and audit requirement, for the period the law asks of us. You will hear back within thirty days.", 'dragon-glow' ),
					),
				),
			),

			'ritual' => array(
				'id'    => 'ritual',
				'title' => esc_html__( 'The Glow Ritual', 'dragon-glow' ),
				'icon'  => 'spa',
				'faqs'  => array(
					array(
						'question' => esc_html__( 'How do I layer the serums?', 'dragon-glow' ),
						'answer'   => esc_html__( "The lightest first. Botanical essence, then two drops of radiance serum, then moisturiser. One breath between each step — the skin tells you when it is ready for the next. Retinol lives at night; the glow lives at dawn.", 'dragon-glow' ),
					),
					array(
						'question' => esc_html__( 'How long does each formulation last?', 'dragon-glow' ),
						'answer'   => esc_html__( "Six months after the first opening. The dark glass and airless pump do the work; no synthetic preservative is needed for that span. A small pencil mark on the base of the jar, the day you break the seal, keeps the count honest.", 'dragon-glow' ),
					),
					array(
						'question' => esc_html__( 'Is the range safe during pregnancy?', 'dragon-glow' ),
						'answer'   => esc_html__( "Most of it, yes. Pause the retinol alternative and the brightening acid; everything else can stay, with your physician's nod. Write to us and we will pare the ritual back to what your skin asks for, season by season.", 'dragon-glow' ),
					),
				),
			),

			'ingredients' => array(
				'id'    => 'ingredients',
				'title' => esc_html__( 'Ingredient Transparency', 'dragon-glow' ),
				'icon'  => 'science',
				'faqs'  => array(
					array(
						'question' => esc_html__( 'Where do the botanicals come from?', 'dragon-glow' ),
						'answer'   => esc_html__( "Micro-farms in Japan, France, the Pacific Northwest. Each one runs on regenerative practice. Each batch carries a trace code, etched on the carton, that walks back to the field the botanicals left.", 'dragon-glow' ),
					),
					array(
						'question' => esc_html__( 'Are the formulas vegan and cruelty-free?', 'dragon-glow' ),
						'answer'   => esc_html__( "Vegan. Always. No animal testing at any stage — not the raw material, not the finished product. The certificate renews each year and sits at the foot of every product page for you to read.", 'dragon-glow' ),
					),
					array(
						'question' => esc_html__( 'What is left out of every formulation?', 'dragon-glow' ),
						'answer'   => esc_html__( "Parabens, sulphates, synthetic fragrance, phthalates, mineral oil, formaldehyde donors. The full list of exclusions, with the reason each sits on it, lives in the ingredient glossary — open it before you place a jar on your shelf.", 'dragon-glow' ),
					),
				),
			),

			'sustainability' => array(
				'id'    => 'sustainability',
				'title' => esc_html__( 'Sustainability Practice', 'dragon-glow' ),
				'icon'  => 'eco',
				'faqs'  => array(
					array(
						'question' => esc_html__( 'Is the packaging recyclable?', 'dragon-glow' ),
						'answer'   => esc_html__( "Glass vessels, paper cartons, soy-based inks. Curbside, in most cities. Pumps and droppers, mixed-material by nature, travel back to us in a prepaid envelope; we send them into the loop again.", 'dragon-glow' ),
					),
					array(
						'question' => esc_html__( 'Do you offer refills?', 'dragon-glow' ),
						'answer'   => esc_html__( "The hero serums refill. Send the empty vessel back with the label that rides in your first order. A credit lands in your account toward the next ritual; the original jar returns to the bench, clean and ready.", 'dragon-glow' ),
					),
					array(
						'question' => esc_html__( 'How do I dispose of an empty vessel?', 'dragon-glow' ),
						'answer'   => esc_html__( "Rinse it. Send it in the prepaid envelope, or keep it — a bud vase, a travel bottle, a quiet object on a shelf. Either way, the glass leaves your hands and begins the next part of its life.", 'dragon-glow' ),
					),
				),
			),

		),

		/* ── Misc ─────────────────────────────────────────────────────────── */
		'orders_url'  => dg_hc_orders_url(),
		'contact_url' => $contact_url,
	);

	return apply_filters( 'dg_help_center_data', $data );
}

/**
 * Tìm trang Contact (cache trong 1 phút để khỏi gọi DB lặp).
 *
 * @return WP_Post|null
 */
function dg_hc_data_contact_page() {
	$cache_key = 'dg_hc_contact_page';
	$cached    = wp_cache_get( $cache_key, 'dg_help_center' );
	if ( $cached === 'none' ) {
		return null;
	}
	if ( $cached ) {
		return $cached;
	}
	$page = get_page_by_path( 'contact' );
	if ( ! $page ) {
		wp_cache_set( $cache_key, 'none', 'dg_help_center', MINUTE_IN_SECONDS );
		return null;
	}
	wp_cache_set( $cache_key, $page, 'dg_help_center', MINUTE_IN_SECONDS );
	return $page;
}