<?php
/**
 * Dragon Glow — Shop Page Customizer Settings
 * Exposes the editorial shop content (hero, philosophy, rituals) via Customizer.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register shop page customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 * @return void
 */
function dg_shop_customize_register( WP_Customize_Manager $wp_customize ): void {

	// ── Section: Shop Hero ─────────────────────────────────
	$wp_customize->add_section( 'dg_shop_hero', array(
		'title'    => __( 'Shop — Hero', 'dragon-glow' ),
		'priority' => 30,
	) );

	$settings = array(
		'dg_shop_hero_image'     => array( 'default' => '', 'label' => __( 'Hero Image', 'dragon-glow' ), 'type' => 'image' ),
		'dg_shop_hero_eyebrow'   => array( 'default' => __( 'Editorial Edition', 'dragon-glow' ), 'label' => __( 'Eyebrow Text', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_hero_title_a'   => array( 'default' => __( 'The Art of', 'dragon-glow' ), 'label' => __( 'Title Line 1', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_hero_title_b'   => array( 'default' => __( 'Luminous', 'dragon-glow' ), 'label' => __( 'Title Italic Word', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_hero_title_c'   => array( 'default' => __( 'Being', 'dragon-glow' ), 'label' => __( 'Title Line 2', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_hero_text'      => array( 'default' => __( 'Experience the definitive collection of clinical radiance. Where ancient dragon fruit enzymes meet contemporary molecular science for an unparalleled glow.', 'dragon-glow' ), 'label' => __( 'Body Text', 'dragon-glow' ), 'type' => 'textarea' ),
		'dg_shop_hero_btn1_text' => array( 'default' => __( 'Shop The Series', 'dragon-glow' ), 'label' => __( 'Primary Button Text', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_hero_btn1_url'  => array( 'default' => '#products', 'label' => __( 'Primary Button URL', 'dragon-glow' ), 'type' => 'url' ),
		'dg_shop_hero_btn2_text' => array( 'default' => __( 'Explore Philosophy', 'dragon-glow' ), 'label' => __( 'Secondary Button Text', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_hero_btn2_url'  => array( 'default' => home_url( '/our-story/' ), 'label' => __( 'Secondary Button URL', 'dragon-glow' ), 'type' => 'url' ),
	);
	dg_register_customizer_settings( $wp_customize, 'dg_shop_hero', $settings );

	// ── Section: Shop Section Header ────────────────────────
	$wp_customize->add_section( 'dg_shop_section', array(
		'title'    => __( 'Shop — Section Header', 'dragon-glow' ),
		'priority' => 31,
	) );
	dg_register_customizer_settings( $wp_customize, 'dg_shop_section', array(
		'dg_shop_section_title' => array( 'default' => __( 'The Curated Glow', 'dragon-glow' ), 'label' => __( 'Section Title', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_section_text'  => array( 'default' => __( "A deliberate selection of our most transformative formulas, designed to synchronize with your skin's natural circadian rhythm.", 'dragon-glow' ), 'label' => __( 'Section Text', 'dragon-glow' ), 'type' => 'textarea' ),
	) );

	// ── Section: Ingredient Philosophy ─────────────────────
	$wp_customize->add_section( 'dg_shop_philosophy', array(
		'title'    => __( 'Shop — Philosophy Section', 'dragon-glow' ),
		'priority' => 32,
	) );
	dg_register_customizer_settings( $wp_customize, 'dg_shop_philosophy', array(
		'dg_shop_philosophy_eyebrow'   => array( 'default' => __( 'The Essence', 'dragon-glow' ), 'label' => __( 'Eyebrow', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_philosophy_title'     => array( 'default' => __( 'Harnessing the Dragon Fruit', 'dragon-glow' ), 'label' => __( 'Title', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_philosophy_text'      => array( 'default' => __( 'Our signature Hylocereus enzyme complex is cold-pressed to preserve its vital nutrients. This "Luminous Bio-C" promotes cellular turnover without irritation, providing the foundation for a clinical-grade glow.', 'dragon-glow' ), 'label' => __( 'Paragraph', 'dragon-glow' ), 'type' => 'textarea' ),
		'dg_shop_philosophy_bullet1'   => array( 'default' => __( 'Responsibly Sourced Micro-Farms', 'dragon-glow' ), 'label' => __( 'Bullet 1', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_philosophy_bullet2'   => array( 'default' => __( 'Cold-Distilled Molecular Integrity', 'dragon-glow' ), 'label' => __( 'Bullet 2', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_philosophy_bullet3'   => array( 'default' => __( 'Dermatologist-Approved Efficacy', 'dragon-glow' ), 'label' => __( 'Bullet 3', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_philosophy_cta'       => array( 'default' => __( 'Our Ingredient Glossary', 'dragon-glow' ), 'label' => __( 'CTA Text', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_philosophy_cta_url'   => array( 'default' => home_url( '/our-story/' ), 'label' => __( 'CTA URL', 'dragon-glow' ), 'type' => 'url' ),
		'dg_shop_philosophy_image_main'=> array( 'default' => '', 'label' => __( 'Main Image', 'dragon-glow' ), 'type' => 'image' ),
		'dg_shop_philosophy_image_card'=> array( 'default' => '', 'label' => __( 'Floating Card Image', 'dragon-glow' ), 'type' => 'image' ),
	) );

	// ── Section: Brand Rituals ─────────────────────────────
	$wp_customize->add_section( 'dg_shop_rituals', array(
		'title'    => __( 'Shop — Rituals Section', 'dragon-glow' ),
		'priority' => 33,
	) );
	dg_register_customizer_settings( $wp_customize, 'dg_shop_rituals', array(
		'dg_shop_rituals_title'     => array( 'default' => __( 'A Daily Invocation', 'dragon-glow' ), 'label' => __( 'Section Title', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_rituals_text'      => array( 'default' => __( 'Beauty is not a task, it is a ritual of self-reverence. Discover our curated sequences for dawn and dusk.', 'dragon-glow' ), 'label' => __( 'Section Text', 'dragon-glow' ), 'type' => 'textarea' ),
		'dg_shop_rituals_am_eyebrow'=> array( 'default' => __( 'AM Ritual', 'dragon-glow' ), 'label' => __( 'AM Eyebrow', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_rituals_am_title'  => array( 'default' => __( 'The Dawn Awakening', 'dragon-glow' ), 'label' => __( 'AM Title', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_rituals_am_image'  => array( 'default' => '', 'label' => __( 'AM Image', 'dragon-glow' ), 'type' => 'image' ),
		'dg_shop_rituals_am_url'    => array( 'default' => home_url( '/shop/?ritual=am' ), 'label' => __( 'AM URL', 'dragon-glow' ), 'type' => 'url' ),
		'dg_shop_rituals_pm_eyebrow'=> array( 'default' => __( 'PM Ritual', 'dragon-glow' ), 'label' => __( 'PM Eyebrow', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_rituals_pm_title'  => array( 'default' => __( 'The Evening Rebirth', 'dragon-glow' ), 'label' => __( 'PM Title', 'dragon-glow' ), 'type' => 'text' ),
		'dg_shop_rituals_pm_image'  => array( 'default' => '', 'label' => __( 'PM Image', 'dragon-glow' ), 'type' => 'image' ),
		'dg_shop_rituals_pm_url'    => array( 'default' => home_url( '/shop/?ritual=pm' ), 'label' => __( 'PM URL', 'dragon-glow' ), 'type' => 'url' ),
	) );
}
add_action( 'customize_register', 'dg_shop_customize_register' );

/**
 * Register a batch of customizer settings + controls for a given section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @param string               $section_id   Section ID.
 * @param array                $settings     Map key => args(default, label, type).
 * @return void
 */
function dg_register_customizer_settings( WP_Customize_Manager $wp_customize, string $section_id, array $settings ): void {
	foreach ( $settings as $id => $args ) {
		$type     = $args['type']     ?? 'text';
		$default  = $args['default']  ?? '';
		$label    = $args['label']    ?? $id;

		$wp_customize->add_setting( $id, array(
			'default'           => $default,
			'sanitize_callback' => 'image' === $type
				? 'esc_url_raw'
				: ( 'textarea' === $type ? 'wp_kses_post' : 'sanitize_text_field' ),
			'transport'         => 'refresh',
		) );

		if ( 'image' === $type ) {
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $id, array(
				'label'   => $label,
				'section' => $section_id,
			) ) );
		} elseif ( 'textarea' === $type ) {
			$wp_customize->add_control( $id, array(
				'label'   => $label,
				'section' => $section_id,
				'type'    => 'textarea',
			) );
		} elseif ( 'url' === $type ) {
			$wp_customize->add_control( $id, array(
				'label'   => $label,
				'section' => $section_id,
				'type'    => 'url',
			) );
		} else {
			$wp_customize->add_control( $id, array(
				'label'   => $label,
				'section' => $section_id,
				'type'    => 'text',
			) );
		}
	}
}
