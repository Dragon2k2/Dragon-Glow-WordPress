<?php
/**
 * Dragon Glow — Terms of Service data
 *
 * Single source of truth cho trang Terms of Service: hero + 22 body sections.
 * Mỗi phần có apply_filters() để theme con / plugin mở rộng.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hero — title + last updated + effective date + intro.
 *
 * @return array{title: string, last_updated: string, effective_date: string, intro: string}
 */
function dg_terms_of_service_hero_data(): array {
	$data = array(
		'title'          => __( 'Terms of Service', 'dragon-glow' ),
		'last_updated'   => __( '1 July 2026', 'dragon-glow' ),
		'effective_date' => __( '1 July 2026', 'dragon-glow' ),
		'intro'          => __( 'These Terms govern your access to and use of the Dragon Glow website, products, and services. By browsing, creating an account, or placing an order, you agree to these Terms. Please read them carefully — they cover your rights, our responsibilities, and how any dispute will be handled.', 'dragon-glow' ),
	);

	/** This filter is documented in template-parts/terms-of-service/data-terms-of-service.php */
	return (array) apply_filters( 'dg_terms_of_service_hero_data', $data );
}

/**
 * Trả về danh sách section của trang Terms of Service.
 *
 * Mỗi section có key: id, num, title, body (HTML đã escape từ helper).
 *
 * @return array<int, array{id: string, num: string, title: string, body: string}>
 */
function dg_terms_of_service_sections_data(): array {
	// Helper: build a paragraph with optional mailto link.
	$p_mail = static function ( string $prefix, string $email ): string {
		return '<p>' . esc_html( $prefix ) . ' <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>';
	};

	// Helper: plain-text paragraph.
	$p = static function ( string $text ): string {
		return '<p>' . esc_html( $text ) . '</p>';
	};

	$sections = array(
		array(
			'id'    => 'section-1',
			'num'   => '01',
			'title' => __( 'About these Terms', 'dragon-glow' ),
			'body'  => $p( __( 'These Terms form a binding agreement between you and Dragon Glow. They apply alongside our Privacy Policy and Cookie Policy, which are incorporated by reference. If any conflict arises between these Terms and a policy at checkout, the checkout terms apply to that order.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-2',
			'num'   => '02',
			'title' => __( 'Who we are', 'dragon-glow' ),
			'body'  => $p_mail( __( 'Dragon Glow, Inc. is a corporation registered in the State of Delaware, with its principal place of business at 215 Mercer Street, Suite 400, New York, NY 10012, United States. You can reach us at', 'dragon-glow' ), 'concierge@dragonglows.page.gd' ),
		),
		array(
			'id'    => 'section-3',
			'num'   => '03',
			'title' => __( 'Eligibility', 'dragon-glow' ),
			'body'  => $p( __( 'You must be at least 18 years old, or the age of majority where you live, to place an order. By using our website, you confirm that the information you provide is accurate and that you are legally able to enter into these Terms.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-4',
			'num'   => '04',
			'title' => __( 'Your account', 'dragon-glow' ),
			'body'  => $p( __( 'You are responsible for keeping your account credentials confidential and for all activity under your account. A password reset link expires after fifteen minutes. You can enable Multi-Factor Authentication in your account’s Security Preferences. Tell us at once if you suspect unauthorised use. We may suspend or close an account that breaches these Terms.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-5',
			'num'   => '05',
			'title' => __( 'Products and descriptions', 'dragon-glow' ),
			'body'  => $p( __( 'Our products are cosmetics intended for external use. We work to describe and picture them accurately, but colours, textures, and finishes may vary slightly between screens and batches. Product information is for general purposes and is not medical advice. Patch test before first use. If you are pregnant or have a medical condition, consult your physician before use, and pause the retinol alternative and the brightening acid where advised.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-6',
			'num'   => '06',
			'title' => __( 'Orders and acceptance', 'dragon-glow' ),
			'body'  => '<p>' . sprintf( wp_kses( __( 'Your order is an offer to buy. We accept it when we send an order confirmation or ship the product, whichever comes first. We may decline or cancel an order — for example, where stock is unavailable, a price is shown in error, or we suspect fraud. If we cancel a paid order, we refund you in full. We pack within one to two hours of purchase, so the window to amend or cancel is brief, usually under thirty minutes; write to <a href="%1$s">%2$s</a> and we will do what we can.', 'dragon-glow' ), array( 'a' => array( 'href' => array() ) ) ), 'mailto:concierge@dragonglows.page.gd', 'concierge@dragonglows.page.gd' ) . '</p>',
		),
		array(
			'id'    => 'section-7',
			'num'   => '07',
			'title' => __( 'Prices and payment', 'dragon-glow' ),
			'body'  => $p( __( 'Prices are shown in US dollars and may change at any time before you order. We accept major credit and debit cards and the payment methods shown at checkout. Payment is processed by our payment providers; we do not store full card details. You authorise us to charge the payment method for the order total, including shipping and any applicable taxes.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-8',
			'num'   => '08',
			'title' => __( 'Shipping and delivery', 'dragon-glow' ),
			'body'  => $p( __( 'Standard shipping is complimentary and arrives in 3–5 business days within the United States. Express shipping is a flat $25 and arrives in 1–2 business days. Overnight is available at checkout. Estimates begin when the parcel ships, not when the order is placed, and are not guaranteed. Title and risk of loss pass to you on delivery to the carrier.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-9',
			'num'   => '09',
			'title' => __( 'International orders, duties, and taxes', 'dragon-glow' ),
			'body'  => $p( __( 'We ship to more than sixty countries. International delivery takes 10–21 days, depending on customs. Duties and taxes are calculated at checkout and are the responsibility of the recipient. Customs may hold a parcel before it moves on; such delays are outside our control.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-10',
			'num'   => '10',
			'title' => __( 'Returns, exchanges, and refunds', 'dragon-glow' ),
			'body'  => '<p>' . sprintf( wp_kses( __( 'If something is not right, send it back within thirty days of delivery — opened or sealed, no explanation required. To start a return, write to <a href="%1$s">%2$s</a>. Once we receive and inspect the item, we issue a refund to the original payment method or arrange an exchange. For a wrong shade, send it back unopened and we will swap it. Refunds are processed without undue delay after we receive the return. This return policy is in addition to, and does not limit, any statutory rights you have as a consumer.', 'dragon-glow' ), array( 'a' => array( 'href' => array() ) ) ), 'mailto:concierge@dragonglows.page.gd', 'concierge@dragonglows.page.gd' ) . '</p>',
		),
		array(
			'id'    => 'section-11',
			'num'   => '11',
			'title' => __( 'Damaged, lost, or late parcels', 'dragon-glow' ),
			'body'  => $p( __( 'If your parcel arrives damaged, tell us within forty-eight hours and send a photograph of the parcel and the item; a replacement or refund follows without delay. If a parcel appears late, allow five business days, then contact us and we will trace it with you.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-12',
			'num'   => '12',
			'title' => __( 'Gift wrapping and promotions', 'dragon-glow' ),
			'body'  => $p( __( 'Gift wrapping is available at checkout at no charge, with no price shown inside. Promotions, discount codes, and gift cards are subject to their own terms, cannot be combined unless stated, have no cash value, and may be withdrawn at any time. Refill credits are applied to your account toward a future order and are not redeemable for cash.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-13',
			'num'   => '13',
			'title' => __( 'Intellectual property', 'dragon-glow' ),
			'body'  => $p( __( "All content on our website — text, images, logos, the 'Dragon Glow' name, product names, and design — is owned by or licensed to Dragon Glow and protected by intellectual property laws. You may not copy, reproduce, or use it without our written permission, except as needed to browse and shop.", 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-14',
			'num'   => '14',
			'title' => __( 'User content and reviews', 'dragon-glow' ),
			'body'  => $p( __( 'If you submit reviews, photos, or other content, you grant us a non-exclusive, worldwide, royalty-free licence to use, display, and distribute it in connection with our business. You confirm the content is yours to share and is not unlawful, misleading, or infringing. We may remove content at our discretion.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-15',
			'num'   => '15',
			'title' => __( 'Acceptable use', 'dragon-glow' ),
			'body'  => $p( __( 'You agree not to misuse the website: no unlawful activity, no interference with its operation, no unauthorised access, no scraping or data harvesting, and no use that infringes the rights of others. We may restrict or end access for breach.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-16',
			'num'   => '16',
			'title' => __( 'Disclaimers', 'dragon-glow' ),
			'body'  => $p( __( 'Our website and products are provided on an &lsquo;as is&rsquo; and &lsquo;as available&rsquo; basis. To the fullest extent permitted by law, we disclaim all warranties not expressly stated, including implied warranties of merchantability and fitness for a particular purpose. Our products are cosmetics and are not intended to diagnose, treat, cure, or prevent any disease. Nothing in these Terms excludes liability that cannot be excluded by law, including for death or personal injury caused by negligence, or for fraud.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-17',
			'num'   => '17',
			'title' => __( 'Limitation of liability', 'dragon-glow' ),
			'body'  => $p( __( 'To the fullest extent permitted by law, Dragon Glow is not liable for indirect, incidental, special, or consequential damages, or for loss of profits, data, or goodwill, arising from your use of the website or products. Our total liability for any claim relating to an order is limited to the amount you paid for that order. Some jurisdictions do not allow certain limitations, so parts of this section may not apply to you.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-18',
			'num'   => '18',
			'title' => __( 'Indemnification', 'dragon-glow' ),
			'body'  => $p( __( 'You agree to indemnify and hold harmless Dragon Glow and its officers, employees, and agents from claims, losses, and expenses arising out of your breach of these Terms or your misuse of the website.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-19',
			'num'   => '19',
			'title' => __( 'Governing law and dispute resolution', 'dragon-glow' ),
			'body'  => $p( __( 'These Terms are governed by the laws of the State of New York, without regard to conflict-of-law rules. Any dispute will be resolved by binding arbitration administered in New York, New York, on an individual basis; class actions are waived to the extent permitted by law. Where the law gives you the right to bring a claim in your local courts as a consumer — including in the EEA and the UK — these Terms do not remove that right or the mandatory consumer protections of your country.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-20',
			'num'   => '20',
			'title' => __( 'Changes to these Terms', 'dragon-glow' ),
			'body'  => $p( __( "We may update these Terms from time to time. We will revise the 'Last updated' date above. Changes take effect when posted; for material changes, we give notice on the website or by email. Your continued use after changes means you accept them. Orders are governed by the Terms in effect when you placed them.", 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-21',
			'num'   => '21',
			'title' => __( 'General terms', 'dragon-glow' ),
			'body'  => $p( __( 'If any provision is found unenforceable, the rest remain in effect. Our failure to enforce a provision is not a waiver. You may not assign these Terms without our consent; we may assign them in connection with a merger, acquisition, or sale of assets. These Terms, the Privacy Policy, and the Cookie Policy are the entire agreement between you and Dragon Glow regarding the website and your orders.', 'dragon-glow' ) ),
		),
		array(
			'id'    => 'section-22',
			'num'   => '22',
			'title' => __( 'How to contact us', 'dragon-glow' ),
			'body'  => '<p><strong>' . sprintf( wp_kses( __( 'Customer care: <a href="%1$s">%2$s</a>', 'dragon-glow' ), array( 'a' => array( 'href' => array() ) ) ), 'mailto:concierge@dragonglows.page.gd', 'concierge@dragonglows.page.gd' ) . '</strong></p>'
				. '<p><strong>' . sprintf( wp_kses( __( 'Privacy: <a href="%1$s">%2$s</a>', 'dragon-glow' ), array( 'a' => array( 'href' => array() ) ) ), 'mailto:privacy@dragonglows.page.gd', 'privacy@dragonglows.page.gd' ) . '</strong></p>'
				. '<p><strong>' . esc_html__( 'Postal:', 'dragon-glow' ) . '</strong> ' . esc_html__( 'Dragon Glow, Inc., 215 Mercer Street, Suite 400, New York, NY 10012, United States', 'dragon-glow' ) . '</p>',
		),
	);

	/**
	 * Lọc danh sách section trước khi render.
	 *
	 * @param array $sections Danh sách section của Terms of Service.
	 */
	return (array) apply_filters( 'dg_terms_of_service_sections_data', $sections );
}
