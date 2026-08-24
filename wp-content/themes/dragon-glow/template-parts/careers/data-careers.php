<?php
/**
 * Dragon Glow — Careers data
 *
 * Cung cấp dữ liệu tĩnh cho trang Careers: hero copy, 3 "why-join-us" tile,
 * 6 benefit tile, 6 role row, 4 hire step, ảnh URL. Mỗi nguồn có
 * apply_filters() để plugin / theme con mở rộng mà không phải edit template.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hero — eyebrow, H1, intro, CTA, ảnh.
 *
 * @return array{eyebrow: string, title: string, intro: string, cta_label: string, image_url: string, image_alt: string}
 */
function dg_careers_hero_data(): array {
	$data = array(
		'eyebrow'   => __( 'Careers', 'dragon-glow' ),
		'title'     => __( 'Build the Ritual with Us', 'dragon-glow' ),
		'intro'     => __( 'We make skincare with care. We are looking for people who do the same.', 'dragon-glow' ),
		'cta_label' => __( 'See open roles', 'dragon-glow' ),
		// Ảnh hero nội bộ (real WebP, 37 KB, 553x739). Fallback gray qua CSS khi file die.
		'image_url' => DG_URI . '/assets/images/careers/hero.webp',
		'image_alt' => __( 'A focused, professional team member working diligently in a serene, brightly lit minimalist design studio in New York. The light is soft and natural, emphasizing a clean, modern aesthetic with subtle warm tones.', 'dragon-glow' ),
	);

	return (array) apply_filters( 'dg_careers_hero_data', $data );
}

/**
 * Mission quote — section 2 (full-width quote).
 *
 * @return string
 */
function dg_careers_mission_data(): string {
	$text = __( '"We blend ancient heritage with clinical precision."', 'dragon-glow' );
	return (string) apply_filters( 'dg_careers_mission_data', $text );
}

/**
 * 3 "Why join us" tile (section 3).
 *
 * Mỗi phần tử gồm icon_url, icon_alt, title, body.
 *
 * @return array<int, array{icon_url: string, icon_alt: string, title: string, body: string}>
 */
function dg_careers_why_join_data(): array {
	$data = array(
		array(
			'icon_url' => DG_URI . '/assets/images/careers/scientific-efficacy.webp',
			'icon_alt' => __( 'Scientific Efficacy icon: a laboratory flask with botanical leaves, gold thin-line style on white.', 'dragon-glow' ),
			'title'    => __( 'Scientific Efficacy', 'dragon-glow' ),
			'body'     => __( 'We merge rare botanicals with proven actives.', 'dragon-glow' ),
		),
		array(
			'icon_url' => DG_URI . '/assets/images/careers/ritualistic-care.webp',
			'icon_alt' => __( 'Ritualistic Care icon: hands gently cradling a small plant sprout, gold thin-line style on white.', 'dragon-glow' ),
			'title'    => __( 'Ritualistic Care', 'dragon-glow' ),
			'body'     => __( 'We treat the work as a craft, not a task.', 'dragon-glow' ),
		),
		array(
			'icon_url' => DG_URI . '/assets/images/careers/clean-conscience.webp',
			'icon_alt' => __( 'Clean Conscience icon: a hand lifting a young tree within a circle, gold thin-line style on white.', 'dragon-glow' ),
			'title'    => __( 'Clean Conscience', 'dragon-glow' ),
			'body'     => __( 'Sustainability and ethics sit at our core.', 'dragon-glow' ),
		),
	);

	return (array) apply_filters( 'dg_careers_why_join_data', $data );
}

/**
 * 6 "What we offer" tile (section 5).
 *
 * @return array<int, string>
 */
function dg_careers_benefits_data(): array {
	$data = array(
		__( 'Health, dental, and vision', 'dragon-glow' ),
		__( 'Paid time to rest', 'dragon-glow' ),
		__( 'Parental leave', 'dragon-glow' ),
		__( 'Product, every season', 'dragon-glow' ),
		__( 'A studio in New York, and remote roles', 'dragon-glow' ),
		__( 'Time and budget to learn', 'dragon-glow' ),
	);
	return (array) apply_filters( 'dg_careers_benefits_data', $data );
}

/**
 * 6 Open role (section 6).
 *
 * Mỗi phần tử gồm title, team, location, type.
 *
 * @return array<int, array{title: string, team: string, location: string, type: string}>
 */
function dg_careers_roles_data(): array {
	$data = array(
		array(
			'title'    => __( 'Formulation Chemist', 'dragon-glow' ),
			'team'     => __( 'Product', 'dragon-glow' ),
			'location' => __( 'New York', 'dragon-glow' ),
			'type'     => __( 'Full-time', 'dragon-glow' ),
		),
		array(
			'title'    => __( 'Sustainability Lead', 'dragon-glow' ),
			'team'     => __( 'Operations', 'dragon-glow' ),
			'location' => __( 'New York', 'dragon-glow' ),
			'type'     => __( 'Full-time', 'dragon-glow' ),
		),
		array(
			'title'    => __( 'E-commerce Developer', 'dragon-glow' ),
			'team'     => __( 'Technology', 'dragon-glow' ),
			'location' => __( 'Remote', 'dragon-glow' ),
			'type'     => __( 'Full-time', 'dragon-glow' ),
		),
		array(
			'title'    => __( 'Brand Copywriter', 'dragon-glow' ),
			'team'     => __( 'Marketing', 'dragon-glow' ),
			'location' => __( 'New York / Remote', 'dragon-glow' ),
			'type'     => __( 'Full-time', 'dragon-glow' ),
		),
		array(
			'title'    => __( 'Customer Concierge', 'dragon-glow' ),
			'team'     => __( 'Customer Care', 'dragon-glow' ),
			'location' => __( 'Remote', 'dragon-glow' ),
			'type'     => __( 'Full-time', 'dragon-glow' ),
		),
		array(
			'title'    => __( 'Retail Partnerships Manager', 'dragon-glow' ),
			'team'     => __( 'Sales', 'dragon-glow' ),
			'location' => __( 'New York', 'dragon-glow' ),
			'type'     => __( 'Full-time', 'dragon-glow' ),
		),
	);

	return (array) apply_filters( 'dg_careers_roles_data', $data );
}

/**
 * 4 "How we hire" step (section 7).
 *
 * Mỗi phần tử gồm step (label caps), title, body.
 *
 * @return array<int, array{step: string, title: string, body: string}>
 */
function dg_careers_hire_steps_data(): array {
	$data = array(
		array(
			'step'  => 'STEP 01',
			'title' => __( 'Apply', 'dragon-glow' ),
			'body'  => __( 'Send your work and a short note.', 'dragon-glow' ),
		),
		array(
			'step'  => 'STEP 02',
			'title' => __( 'Talk', 'dragon-glow' ),
			'body'  => __( 'A first call to meet.', 'dragon-glow' ),
		),
		array(
			'step'  => 'STEP 03',
			'title' => __( 'Meet the team', 'dragon-glow' ),
			'body'  => __( 'A deeper conversation, in person or online.', 'dragon-glow' ),
		),
		array(
			'step'  => 'STEP 04',
			'title' => __( 'Offer', 'dragon-glow' ),
			'body'  => __( 'We move quickly once we know.', 'dragon-glow' ),
		),
	);

	return (array) apply_filters( 'dg_careers_hire_steps_data', $data );
}

/**
 * Life Here image (section 4) — dùng URL Stitch, fallback gray qua CSS.
 *
 * @return array{image_url: string, image_alt: string}
 */
function dg_careers_life_image_data(): array {
	$data = array(
		// Ảnh nội bộ (WebP, lazy). Fallback gray qua CSS khi file die.
		'image_url' => DG_URI . '/assets/images/careers/how-we-work.webp',
		'image_alt' => __( 'A collaborative moment among a small, focused team in a serene, minimalist office environment. Natural light streams in, highlighting a clean, sophisticated workspace designed for thoughtful work.', 'dragon-glow' ),
	);

	return (array) apply_filters( 'dg_careers_life_image_data', $data );
}

/**
 * 3 "How we work" bullet (section 4).
 *
 * @return array<int, string>
 */
function dg_careers_how_we_work_data(): array {
	$data = array(
		__( 'Small teams. Clear hands.', 'dragon-glow' ),
		__( 'We make decisions close to the craft.', 'dragon-glow' ),
		__( 'We leave room to think.', 'dragon-glow' ),
	);
	return (array) apply_filters( 'dg_careers_how_we_work_data', $data );
}

/**
 * Closing CTA (section 9) — body + CTA + email label.
 *
 * @return array{body: string, cta_label: string, email: string, default_role: string}
 */
function dg_careers_closing_data(): array {
	$data = array(
		'body'      => __( 'Do not see your role? We still want to hear from you.', 'dragon-glow' ),
		'cta_label' => __( 'Write to us', 'dragon-glow' ),
		'email'     => 'careers@dragonglows.page.gd',
		'default_role' => __( 'General Application', 'dragon-glow' ),
	);
	return (array) apply_filters( 'dg_careers_closing_data', $data );
}