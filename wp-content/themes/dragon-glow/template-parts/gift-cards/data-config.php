<?php
/**
 * Dragon Glow — Gift Cards Data
 * Single source of truth cho trang Gift Cards: copy, mệnh giá, preview ảnh,
 * và hằng số dùng chung giữa template-part + JS.
 *
 * Quy ước:
 *  - Tiền tệ: USD ($). Khi cần đổi, sửa ở đây hoặc qua filter `dg_gift_cards_data`.
 *  - 4 mệnh giá hard-coded theo reference. Sau này nếu cần admin-configurable,
 *    thay hằng bằng get_option()/Customizer.
 *  - Ảnh preview dùng URL Google từ code.html (placeholder) cho tới khi có ảnh
 *    thật trong WP Media.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lấy toàn bộ data trang Gift Cards.
 *
 * @return array
 */
function dg_gift_cards_data(): array {
	$shop_page = get_page_by_path( 'shop' );
	$cart_url  = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );

	$data = array(

		/* ── Đường dẫn ───────────────────────────────────────────────────────── */
		'shop_url' => $shop_page ? get_permalink( $shop_page->ID ) : home_url( '/shop/' ),
		'cart_url' => $cart_url,

		/* ── Mock product (dùng khi WC tắt) ──────────────────────────────────── */
		// Khi WC bật: code đọc wc_get_product_id_by_slug('gift-card').
		// Khi WC tắt: dùng giá trị này làm id ảo cho DGCart mock.
		'mock_product_id'    => 9001,
		'mock_product_title' => esc_html__( 'Dragon Glow Gift Card', 'dragon-glow' ),

		/* ── Hero ────────────────────────────────────────────────────────────── */
		'hero' => array(
			'eyebrow'  => esc_html__( 'Gift Cards', 'dragon-glow' ),
			'title'    => esc_html__( 'Elegance to share', 'dragon-glow' ),
			'subtitle' => esc_html__(
				'Present the ultimate gift of quiet luxury. A beautifully textured experience, whether delivered digitally or physically.',
				'dragon-glow'
			),
		),

		/* ── Format (toggle Digital ↔ Physical) ──────────────────────────────── */
		'formats' => array(
			array(
				'id'          => 'digital',
				'label'       => esc_html__( 'Digital', 'dragon-glow' ),
				'icon'        => 'mail',
				'tag_label'   => esc_html__( 'Digital Edition', 'dragon-glow' ),
				'description' => esc_html__( 'Instant elegance to their inbox.', 'dragon-glow' ),
			),
			array(
				'id'          => 'physical',
				'label'       => esc_html__( 'Physical', 'dragon-glow' ),
				'icon'        => 'local_post_office',
				'tag_label'   => esc_html__( 'Physical Edition', 'dragon-glow' ),
				'description' => esc_html__( 'A textured card, delivered in ivory linen.', 'dragon-glow' ),
			),
		),

		/* ── Preview images ──────────────────────────────────────────────────── */
		// 4 ảnh thẻ theo mệnh giá (50/100/250/500) — lưu trong theme để không phụ thuộc bên ngoài.
		// Khóa ảnh mặc định theo mệnh giá khởi tạo ($default_amount) để không nhấp nháy khi load.
		'preview' => array(
			'main_alt'   => esc_attr__(
				'Dragon Glow gift card — premium textured card with embossed brand mark on a soft marble surface.',
				'dragon-glow'
			),
			'main_src'   => DG_URI . '/assets/images/gift-cards/card-$100.webp',
			'cards_base' => DG_URI . '/assets/images/gift-cards/',
			'cards'      => array(
				50  => DG_URI . '/assets/images/gift-cards/card-$50.webp',
				100 => DG_URI . '/assets/images/gift-cards/card-$100.webp',
				250 => DG_URI . '/assets/images/gift-cards/card-$250.webp',
				500 => DG_URI . '/assets/images/gift-cards/card-$500.webp',
			),
			'second_alt' => esc_attr__(
				'Translucent vellum envelopes scattered on a clean, ivory background. Soft, warm morning light illuminates the scene.',
				'dragon-glow'
			),
			'second_src' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCtH6qUUYOD4UovS06tTRppXv-Bj1gbCcGE5HDEQ-Bn4iA6D-CUlxdP2I7bkCIlZLzYPjx0O9vECROI9yl8e5rGMNMsL7y8D5OW4y-T6aHBKLXa9h08fpUOyhpQCJIAFxu3q5HVmlImngmuPqVwIr0KbdFRQhGx0xE1WynriGtfuO02q9o_hvFjXECDSvMnulw6AIqemgoJ4H7NFPmVKxOsrT-WSD0Yj_JApODecZjCuPKqzEvZ6_lZwtnBoWDtbo7_tI0yYw-HM2mC',
		),

		/* ── Mệnh giá — USD, hard-coded ─────────────────────────────────────── */
		'values' => array(
			array( 'amount' => 50,  'slug' => 'usd-50'  ),
			array( 'amount' => 100, 'slug' => 'usd-100' ),
			array( 'amount' => 250, 'slug' => 'usd-250' ),
			array( 'amount' => 500, 'slug' => 'usd-500' ),
		),

		/* ── Form labels ─────────────────────────────────────────────────────── */
		'form' => array(
			'configure_title'        => esc_html__( 'Configure Gift', 'dragon-glow' ),
			'configure_subtitle'     => esc_html__( 'Select the format and value.', 'dragon-glow' ),
			'format_label'           => esc_html__( 'Format', 'dragon-glow' ),
			'value_label'            => esc_html__( 'Value', 'dragon-glow' ),
			'recipient_title'        => esc_html__( 'Recipient Details', 'dragon-glow' ),
			'recipient_to_label'     => esc_html__( 'To', 'dragon-glow' ),
			'recipient_to_placeholder' => esc_attr__( 'Recipient’s Name', 'dragon-glow' ),
			'recipient_email_label'  => esc_html__( 'Email', 'dragon-glow' ),
			'recipient_email_placeholder' => esc_attr__( 'Recipient’s Email Address', 'dragon-glow' ),
			'message_label'          => esc_html__( 'Message (Optional)', 'dragon-glow' ),
			'message_placeholder'    => esc_attr__( 'Add a personal note…', 'dragon-glow' ),
			'submit_label'           => esc_html__( 'Add to Bag', 'dragon-glow' ),
			'submit_separator'       => esc_html__( '—', 'dragon-glow' ),
		),
	);

	/**
	 * Filter toàn bộ data — child theme / plugin có thể đổi copy, mệnh giá, ảnh.
	 *
	 * @param array $data
	 */
	return apply_filters( 'dg_gift_cards_data', $data );
}

/**
 * Format số tiền theo locale hiện tại. Mặc định USD ($).
 *
 * @param int $amount
 * @return string
 */
function dg_gift_cards_format_price( int $amount ): string {
	$symbol = '$';
	return $symbol . number_format( $amount );
}
