<?php
/**
 * Dragon Glow — Accessibility Statement data
 *
 * Cung cấp dữ liệu tĩnh cho trang Accessibility. Mỗi phần có
 * apply_filters() để theme con / plugin mở rộng mà không phải edit template.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hero — eyebrow + H1 + intro. Bản nội dung mới không kèm "Last updated".
 *
 * @return array{eyebrow: string, title: string, intro: string}
 */
function dg_accessibility_hero_data(): array {
	$data = array(
		'eyebrow' => __( 'Accessibility', 'dragon-glow' ),
		'title'   => __( 'Accessibility Statement', 'dragon-glow' ),
		'intro'   => __( 'We believe everyone should be able to browse, learn, and shop with ease. Accessibility is part of how we build and maintain this website, not an afterthought. We review and improve it over time.', 'dragon-glow' ),
	);

	/**
	 * Lọc dữ liệu hero trước khi render.
	 *
	 * @param array $data Mảng với key `eyebrow`, `title`, `intro`.
	 */
	return (array) apply_filters( 'dg_accessibility_hero_data', $data );
}

/**
 * 5 biện pháp hỗ trợ accessibility của section 3.
 *
 * @return array<int, string>
 */
function dg_accessibility_measures_data(): array {
	$measures = array(
		__( 'Consider accessibility when we design and build new pages.', 'dragon-glow' ),
		__( 'Use semantic HTML and ARIA roles to convey structure and meaning.', 'dragon-glow' ),
		__( 'Test keyboard navigation and visible focus on interactive elements.', 'dragon-glow' ),
		__( 'Respect the operating system setting for reduced motion.', 'dragon-glow' ),
		__( 'Review colour contrast and text sizing.', 'dragon-glow' ),
	);

	/**
	 * Lọc danh sách biện pháp trước khi render.
	 *
	 * @param array $measures Mảng 5 chuỗi biện pháp.
	 */
	return (array) apply_filters( 'dg_accessibility_measures_data', $measures );
}

/**
 * 7 tính năng accessibility của website (section 4).
 *
 * @return array<int, string>
 */
function dg_accessibility_features_data(): array {
	$features = array(
		__( 'Semantic HTML structure with clear headings and landmarks.', 'dragon-glow' ),
		__( 'ARIA labels and roles on menus, accordions, and interactive controls.', 'dragon-glow' ),
		__( 'Full keyboard navigation, with a visible focus outline on every interactive element.', 'dragon-glow' ),
		__( 'Reduced-motion support: when your device requests reduced motion, animations are turned off and content still appears.', 'dragon-glow' ),
		__( 'Descriptive alternative text for meaningful images.', 'dragon-glow' ),
		__( 'Screen-reader-only text that adds context where the visual layout alone is not enough.', 'dragon-glow' ),
		__( 'A responsive layout that supports zoom and reflow without loss of content.', 'dragon-glow' ),
	);

	/**
	 * Lọc danh sách tính năng trước khi render.
	 *
	 * @param array $features Mảng 7 chuỗi tính năng.
	 */
	return (array) apply_filters( 'dg_accessibility_features_data', $features );
}

/**
 * 3 hạn chế đã biết (section 5).
 *
 * @return array<int, string>
 */
function dg_accessibility_limitations_data(): array {
	$limits = array(
		__( 'A few third-party components, such as embedded payment or shipping widgets, may not fully meet the standard; these are controlled by their providers.', 'dragon-glow' ),
		__( 'Some older content and images may lack complete alternative text; we are reviewing them.', 'dragon-glow' ),
		__( 'Certain complex interactions are still being refined for assistive technology.', 'dragon-glow' ),
	);

	/**
	 * Lọc danh sách hạn chế trước khi render.
	 *
	 * @param array $limits Mảng 3 chuỗi hạn chế.
	 */
	return (array) apply_filters( 'dg_accessibility_limitations_data', $limits );
}

/**
 * Thông tin liên hệ cho section 9 "Feedback and Contact".
 *
 * Mỗi phần tử gồm label (in đậm) và value; nếu có `href` sẽ wrap value trong <a>.
 *
 * @return array<int, array{label: string, value: string, href?: string}>
 */
function dg_accessibility_contact_data(): array {
	$contact = array(
		array(
			'label' => __( 'Email:', 'dragon-glow' ),
			'value' => 'accessibility@dragonglows.page.gd',
			'href'  => 'mailto:accessibility@dragonglows.page.gd',
		),
		array(
			'label' => __( 'Customer care:', 'dragon-glow' ),
			'value' => 'concierge@dragonglows.page.gd',
			'href'  => 'mailto:concierge@dragonglows.page.gd',
		),
		array(
			'label' => __( 'Postal:', 'dragon-glow' ),
			'value' => 'Dragon Glow, Inc., 215 Mercer Street, Suite 400, New York, NY 10012, United States',
		),
	);

	/**
	 * Lọc thông tin liên hệ trước khi render.
	 *
	 * @param array $contact Mảng 3 phần tử `label`/`value` (và tuỳ chọn `href`).
	 */
	return (array) apply_filters( 'dg_accessibility_contact_data', $contact );
}

/**
 * 4 công nghệ chính phụ thuộc cho accessibility (section 7).
 *
 * @return array<int, string>
 */
function dg_accessibility_tech_data(): array {
	$tech = array(
		'HTML',
		'CSS',
		'JavaScript',
		'WAI-ARIA',
	);

	/**
	 * Lọc danh sách công nghệ trước khi render.
	 *
	 * @param array $tech Mảng 4 chuỗi công nghệ.
	 */
	return (array) apply_filters( 'dg_accessibility_tech_data', $tech );
}