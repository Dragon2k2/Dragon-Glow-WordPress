<?php
/**
 * Dragon Glow — Cookie Policy data
 *
 * Single source of truth cho trang Cookie Policy: hero, TOC list, body sections.
 * Mỗi phần có apply_filters() để theme con / plugin mở rộng mà không phải edit
 * template. Section list chứa các section theo thứ tự render; mỗi section có
 * key `id`, `num`, `title`, `body` (HTML được phép, đã escape trong template),
 * `partial` (optional — render thay cho body nếu cần markup phức tạp).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hero — header + meta + intro + manage cookies button.
 *
 * @return array{title: string, last_updated: string, effective_date: string, intro: string}
 */
function dg_cookie_policy_hero_data(): array {
	$data = array(
		'title'          => __( 'Cookie Policy', 'dragon-glow' ),
		'last_updated'   => __( '1 July 2026', 'dragon-glow' ),
		'effective_date' => __( '1 July 2026', 'dragon-glow' ),
		'intro'          => __( 'This Cookie Policy explains what cookies and similar technologies are, why Dragon Glow uses them on our website, the choices you have, and how to contact us if you have any question.', 'dragon-glow' ),
	);

	/** This filter is documented in template-parts/cookie-policy/data-cookie-policy.php */
	return (array) apply_filters( 'dg_cookie_policy_hero_data', $data );
}

/**
 * Trả về danh sách 4 nhóm cookie dùng cho section "Categories".
 *
 * Nhãn phải khớp với cột "Type" trong bảng cookies ở section 4 — script dùng
 * `strtolower` để map modifier CSS nên dù "Strictly necessary" và "Analytics"
 * đều dùng được.
 *
 * @return array<int, array{label: string, body: string}>
 */
function dg_cookie_categories_data(): array {
	$categories = array(
		array(
			'label' => __( 'Strictly necessary', 'dragon-glow' ),
			'body'  => __( 'Required for the site to function — cart, checkout, sign-in, and security. These cannot be switched off.', 'dragon-glow' ),
		),
		array(
			'label' => __( 'Functional', 'dragon-glow' ),
			'body'  => __( 'Remember your choices and preferences to improve your experience.', 'dragon-glow' ),
		),
		array(
			'label' => __( 'Analytics', 'dragon-glow' ),
			'body'  => __( 'Help us understand how visitors use the site so we can improve it.', 'dragon-glow' ),
		),
		array(
			'label' => __( 'Advertising', 'dragon-glow' ),
			'body'  => __( 'Used to show and measure relevant content across sites.', 'dragon-glow' ),
		),
	);

	/**
	 * Lọc danh sách 4 nhóm cookie trước khi render.
	 *
	 * @param array $categories Mảng các category với key `label` và `body`.
	 */
	return (array) apply_filters( 'dg_cookie_categories_data', $categories );
}

/**
 * Trả về danh sách cookie cụ thể dùng cho section "Specific Cookies Table".
 *
 * Mỗi phần tử gồm: name, provider, purpose, type, duration.
 *
 * @return array<int, array{name: string, provider: string, purpose: string, type: string, duration: string}>
 */
function dg_cookies_table_data(): array {
	$rows = array(
		array(
			'name'     => 'woocommerce_cart_hash',
			'provider' => __( 'Dragon Glow', 'dragon-glow' ),
			'purpose'  => __( 'Stores the contents of your cart', 'dragon-glow' ),
			'type'     => __( 'Strictly necessary', 'dragon-glow' ),
			'duration' => __( 'Session', 'dragon-glow' ),
		),
		array(
			'name'     => 'woocommerce_items_in_cart',
			'provider' => __( 'Dragon Glow', 'dragon-glow' ),
			'purpose'  => __( 'Tracks the number of items in your cart', 'dragon-glow' ),
			'type'     => __( 'Strictly necessary', 'dragon-glow' ),
			'duration' => __( 'Session', 'dragon-glow' ),
		),
		array(
			'name'     => 'wp_woocommerce_session_*',
			'provider' => __( 'Dragon Glow', 'dragon-glow' ),
			'purpose'  => __( 'Links your device to your cart and checkout data', 'dragon-glow' ),
			'type'     => __( 'Strictly necessary', 'dragon-glow' ),
			'duration' => __( '2 days', 'dragon-glow' ),
		),
		array(
			'name'     => 'wordpress_logged_in_*',
			'provider' => __( 'Dragon Glow', 'dragon-glow' ),
			'purpose'  => __( 'Keeps you signed in to your account', 'dragon-glow' ),
			'type'     => __( 'Strictly necessary', 'dragon-glow' ),
			'duration' => __( 'Session', 'dragon-glow' ),
		),
		array(
			'name'     => 'dg_cookie_consent',
			'provider' => __( 'Dragon Glow', 'dragon-glow' ),
			'purpose'  => __( 'Remembers your cookie choices', 'dragon-glow' ),
			'type'     => __( 'Strictly necessary', 'dragon-glow' ),
			'duration' => __( '12 months', 'dragon-glow' ),
		),
		array(
			'name'     => 'dg_currency',
			'provider' => __( 'Dragon Glow', 'dragon-glow' ),
			'purpose'  => __( 'Remembers your selected currency and region', 'dragon-glow' ),
			'type'     => __( 'Functional', 'dragon-glow' ),
			'duration' => __( '12 months', 'dragon-glow' ),
		),
		array(
			'name'     => '_ga / _ga_*',
			'provider' => __( 'Google Analytics', 'dragon-glow' ),
			'purpose'  => __( 'Measures site usage and visits', 'dragon-glow' ),
			'type'     => __( 'Analytics', 'dragon-glow' ),
			'duration' => __( 'Up to 24 months', 'dragon-glow' ),
		),
		array(
			'name'     => '_gid',
			'provider' => __( 'Google Analytics', 'dragon-glow' ),
			'purpose'  => __( 'Distinguishes users for usage reporting', 'dragon-glow' ),
			'type'     => __( 'Analytics', 'dragon-glow' ),
			'duration' => __( '24 hours', 'dragon-glow' ),
		),
		array(
			'name'     => '_fbp',
			'provider' => __( 'Meta', 'dragon-glow' ),
			'purpose'  => __( 'Measures and personalises advertising', 'dragon-glow' ),
			'type'     => __( 'Advertising', 'dragon-glow' ),
			'duration' => __( '3 months', 'dragon-glow' ),
		),
	);

	/**
	 * Lọc danh sách cookie trước khi render bảng.
	 *
	 * @param array $rows Mảng các hàng cookie.
	 */
	return (array) apply_filters( 'dg_cookies_table_data', $rows );
}

/**
 * Trả về danh sách section của trang Cookie Policy.
 *
 * Mỗi section có key:
 *   - id      (string) Slug dùng cho href #section-N.
 *   - num     (string) Số thứ tự hiển thị (vd "01", "02"…).
 *   - title   (string) Tiêu đề.
 *   - body    (string) Nội dung HTML đơn giản.
 *   - partial (string) Optional — nếu có, template include file partial này
 *                      thay vì echo `body`. Partial path relative tới
 *                      template-parts/cookie-policy/, không có extension.
 *
 * @return array<int, array{id: string, num: string, title: string, body: string, partial?: string}>
 */
function dg_cookie_policy_sections_data(): array {
	$sections = array(
		array(
			'id'    => 'section-1',
			'num'   => '01',
			'title' => __( 'What are cookies', 'dragon-glow' ),
			'body'  => sprintf(
				'<p>%s</p>',
				esc_html__( 'Cookies are small text files stored on your device when you visit a website. Similar technologies include pixels, tags, and local storage. They let a site remember your actions and preferences, keep you signed in, and understand how the site is used. Cookies set by us are &lsquo;first-party&rsquo;; cookies set by others are &lsquo;third-party&rsquo;.', 'dragon-glow' )
			),
		),
		array(
			'id'    => 'section-2',
			'num'   => '02',
			'title' => __( 'Why we use cookies', 'dragon-glow' ),
			'body'  => sprintf(
				'<p>%s</p><ul><li>%s</li><li>%s</li><li>%s</li><li>%s</li><li>%s</li></ul>',
				esc_html__( 'We use cookies to:', 'dragon-glow' ),
				esc_html__( 'Keep your cart and session working as you move through the site.', 'dragon-glow' ),
				esc_html__( 'Sign you in and keep your account secure.', 'dragon-glow' ),
				esc_html__( 'Remember your preferences, such as language and region.', 'dragon-glow' ),
				esc_html__( 'Measure and improve how the site performs, with your consent.', 'dragon-glow' ),
				esc_html__( 'Personalise content and measure advertising, with your consent.', 'dragon-glow' )
			),
		),
		array(
			'id'      => 'section-3',
			'num'     => '03',
			'title'   => __( 'Categories of cookies we use', 'dragon-glow' ),
			'body'    => '',
			'partial' => 'section-categories',
		),
		array(
			'id'    => 'section-4',
			'num'   => '04',
			'title' => __( 'Cookies on our website', 'dragon-glow' ),
			'body'  => sprintf(
				'<p>%s</p>',
				esc_html__( 'The table below lists the main cookies we use. Exact names and durations may vary as our providers update their tools.', 'dragon-glow' )
			),
			'partial' => 'section-cookies-table',
		),
		array(
			'id'    => 'section-5',
			'num'   => '05',
			'title' => __( 'Third-party cookies', 'dragon-glow' ),
			'body'  => sprintf(
				'<p>%s</p>',
				esc_html__( 'Some cookies are set by our providers — payment processors, analytics providers such as Google, and advertising platforms such as Meta. We do not control these cookies. Review each provider’s own privacy and cookie notice for details and opt-out options.', 'dragon-glow' )
			),
		),
		array(
			'id'    => 'section-6',
			'num'   => '06',
			'title' => __( 'How long cookies last', 'dragon-glow' ),
			'body'  => sprintf(
				'<p>%s</p>',
				esc_html__( 'Session cookies are deleted when you close your browser. Persistent cookies remain for the period shown in the table, or until you delete them. Strictly necessary cookies stay in place for as long as they are needed to run the site.', 'dragon-glow' )
			),
		),
		array(
			'id'      => 'section-7',
			'num'     => '07',
			'title'   => __( 'How to manage your choices', 'dragon-glow' ),
			'body'    => '',
			'partial' => 'section-management',
		),
		array(
			'id'    => 'section-8',
			'num'   => '08',
			'title' => __( 'Global Privacy Control and Do Not Sell or Share', 'dragon-glow' ),
			'body'  => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %s: link to email */
					esc_html__( 'We honour Global Privacy Control (GPC) signals where required by law. To opt out of any sale or sharing of your personal information through advertising cookies, use the &lsquo;Do Not Sell or Share My Personal Information&rsquo; link in the website footer, adjust your cookie choices, or email %s.', 'dragon-glow' ),
					'<a href="mailto:privacy@dragonglows.page.gd">privacy@dragonglows.page.gd</a>'
				)
			),
		),
		array(
			'id'    => 'section-9',
			'num'   => '09',
			'title' => __( 'Changes to this Policy', 'dragon-glow' ),
			'body'  => sprintf(
				'<p>%s</p>',
				esc_html__( 'We may update this Cookie Policy from time to time. We will revise the &lsquo;Last updated&rsquo; date above and, for material changes, give notice on the website.', 'dragon-glow' )
			),
		),
		array(
			'id'    => 'section-10',
			'num'   => '10',
			'title' => __( 'How to contact us', 'dragon-glow' ),
			'body'  => sprintf(
				'<address class="dg-cookie-address"><p><strong>%s</strong> <a href="mailto:privacy@dragonglows.page.gd">privacy@dragonglows.page.gd</a></p><p><strong>%s</strong> <a href="mailto:concierge@dragonglows.page.gd">concierge@dragonglows.page.gd</a></p><p><strong>%s</strong> Dragon Glow, Inc., 215 Mercer Street, Suite 400, New York, NY 10012, United States</p></address>',
				esc_html__( 'Privacy team:', 'dragon-glow' ),
				esc_html__( 'Customer care:', 'dragon-glow' ),
				esc_html__( 'Postal:', 'dragon-glow' )
			),
		),
	);

	/**
	 * Lọc danh sách section trước khi render.
	 *
	 * Mỗi phần tử có key: id, num, title, body (HTML), partial (optional).
	 *
	 * @param array $sections Danh sách section của Cookie Policy.
	 */
	return (array) apply_filters( 'dg_cookie_policy_sections_data', $sections );
}
