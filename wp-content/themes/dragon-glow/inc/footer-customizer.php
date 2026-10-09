<?php
/**
 * Dragon Glow — Footer Customizer Settings
 * Exposes the footer brand description via Customizer so it is no longer
 * coupled to the site tagline (Settings → General), which is used for SEO.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default footer brand description.
 *
 * @return string
 */
function dg_footer_description_default(): string {
	return __( 'Premium botanical skincare, thoughtfully formulated to reveal your natural radiance. Crafted with care for your skin and the planet.', 'dragon-glow' );
}

/**
 * Get the footer brand description.
 *
 * Reads the `dg_footer_description` theme mod and falls back to the default
 * copy when the mod is empty.
 *
 * @return string
 */
function dg_get_footer_description(): string {
	$description = trim( (string) dg_get_mod( 'footer_description', '' ) );

	return '' !== $description ? $description : dg_footer_description_default();
}

/**
 * Register footer customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 * @return void
 */
function dg_footer_customize_register( WP_Customize_Manager $wp_customize ): void {
	$wp_customize->add_section( 'dg_footer', array(
		'title'    => __( 'Footer', 'dragon-glow' ),
		'priority' => 34,
	) );

	// Uses the batched helper defined in shop-customizer.php (loaded earlier in functions.php).
	dg_register_customizer_settings( $wp_customize, 'dg_footer', array(
		'dg_footer_description' => array(
			'default' => dg_footer_description_default(),
			'label'   => __( 'Brand Description', 'dragon-glow' ),
			'type'    => 'textarea',
		),
	) );

	// One URL control per network; networks come from dg_get_social_links() (single source of truth).
	$social_settings = array();
	foreach ( dg_get_social_links() as $network => $social ) {
		$social_settings[ 'dg_' . $network . '_url' ] = array(
			'default' => $social['default'],
			/* translators: %s: social network name, e.g. Facebook. */
			'label'   => sprintf( __( '%s URL', 'dragon-glow' ), $social['label'] ),
			'type'    => 'url',
		);
	}
	dg_register_customizer_settings( $wp_customize, 'dg_footer', $social_settings );
}
add_action( 'customize_register', 'dg_footer_customize_register' );
