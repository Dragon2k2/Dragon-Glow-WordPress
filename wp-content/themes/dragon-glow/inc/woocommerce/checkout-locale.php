<?php
/**
 * Dragon Glow — WooCommerce: Checkout Locale & Country States
 *
 * Custom country/state data, locale field visibility rules, injection of the
 * custom states into WooCommerce's client-side JS, and locale cache clearing.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add custom states for countries that WooCommerce doesn't have data for.
 *
 * Loads state/province/region data from external data file to keep this file
 * maintainable. Covers top 50+ countries in e-commerce.
 *
 * @param array $states Existing states data from WooCommerce.
 * @return array Modified states data with custom additions.
 */
function dg_add_custom_country_states( array $states ): array {
	// Load custom states data from separate file (easier to maintain).
	require_once get_template_directory() . '/inc/data/country-states.php';

	$custom_states = dg_get_country_states_data();

	// Merge custom data with WooCommerce data.
	// Custom data takes precedence (to override incomplete WC data).
	return array_merge( $states, $custom_states );
}
add_filter( 'woocommerce_states', 'dg_add_custom_country_states', 10 );

/**
 * Force WooCommerce to recognize countries with states.
 * This filter runs when WC checks if a country has states.
 *
 * @param array  $states       Array of states for the country.
 * @param string $country_code Country code.
 * @return array States for the country.
 */
function dg_get_states_for_country( $states, $country_code ) {
	// Load custom states data.
	require_once get_template_directory() . '/inc/data/country-states.php';
	$custom_states = dg_get_country_states_data();

	// If we have custom data for this country, return it.
	if ( isset( $custom_states[ $country_code ] ) ) {
		return $custom_states[ $country_code ];
	}

	return $states;
}
add_filter( 'woocommerce_get_country_states', 'dg_get_states_for_country', 10, 2 );

/**
 * Inject custom country states into WooCommerce JavaScript data.
 * WooCommerce localizes states data before our filters run, so we need to
 * inject it manually via a separate JS file with localized data.
 *
 * @return void
 */
function dg_inject_custom_states_to_js(): void {
	if ( ! is_checkout() && ! is_account_page() ) {
		return;
	}

	// Load custom states data.
	require_once get_template_directory() . '/inc/data/country-states.php';
	$custom_states = dg_get_country_states_data();

	// Enqueue the injection script with WC country-select as dependency.
	wp_enqueue_script(
		'dg-inject-states',
		DG_URI . '/assets/js/inject-custom-states.js',
		array( 'jquery', 'wc-country-select' ),
		DG_VERSION,
		true
	);

	// Pass custom states data to JavaScript.
	wp_localize_script( 'dg-inject-states', 'dgCustomStates', $custom_states );
}
add_action( 'wp_enqueue_scripts', 'dg_inject_custom_states_to_js', 999 );

/**
 * Customize WooCommerce country locale to show/hide State field based on
 * real-world administrative divisions, regardless of WooCommerce's default data.
 *
 * @param array $locale WooCommerce locale data.
 * @return array Modified locale data.
 */
function dg_customize_locale_fields( array $locale ): array {
	// Load custom states data to know which countries HAVE states
	require_once get_template_directory() . '/inc/data/country-states.php';
	$countries_with_states = array_keys( dg_get_country_states_data() );

	// Countries WITHOUT states/provinces/regions (administrative divisions)
	// Based on real-world geography, not WooCommerce data
	$countries_without_states = array(
		'VA', // Vatican City
		'MC', // Monaco
		'SM', // San Marino
		'LI', // Liechtenstein
		'AD', // Andorra
		'MT', // Malta
		'LU', // Luxembourg
		'IS', // Iceland
		'CY', // Cyprus
		'FO', // Faroe Islands
		'GI', // Gibraltar
		'IM', // Isle of Man
		'JE', // Jersey
		'GG', // Guernsey
		'AX', // Åland Islands
		'SG', // Singapore
		'BH', // Bahrain
		'BN', // Brunei
		'KW', // Kuwait
		'MV', // Maldives
		'QA', // Qatar
		'TO', // Tonga
		'TV', // Tuvalu
		'NR', // Nauru
		'PN', // Pitcairn Islands
	);

	// First pass: Force SHOW state field for ALL countries that have states in our data
	foreach ( $countries_with_states as $country_code ) {
		if ( ! isset( $locale[ $country_code ] ) ) {
			$locale[ $country_code ] = array();
		}
		if ( ! isset( $locale[ $country_code ]['state'] ) ) {
			$locale[ $country_code ]['state'] = array();
		}
		// Force show and make optional (not hidden, not required)
		$locale[ $country_code ]['state']['hidden']   = false;
		$locale[ $country_code ]['state']['required'] = false;
	}

	// Second pass: Hide state field ONLY for countries that truly don't have states
	foreach ( $countries_without_states as $country_code ) {
		if ( ! isset( $locale[ $country_code ] ) ) {
			$locale[ $country_code ] = array();
		}
		if ( ! isset( $locale[ $country_code ]['state'] ) ) {
			$locale[ $country_code ]['state'] = array();
		}
		$locale[ $country_code ]['state']['required'] = false;
		$locale[ $country_code ]['state']['hidden']   = true;
	}

	return $locale;
}
add_filter( 'woocommerce_get_country_locale', 'dg_customize_locale_fields', 999 );

/**
 * Clear WooCommerce locale cache on theme switch or admin init.
 *
 * @return void
 */
function dg_clear_wc_locale_cache(): void {
	// Delete WC transients that cache country/locale data
	delete_transient( 'wc_countries' );
	delete_transient( 'wc_country_locale' );

	// Also clear object cache if using persistent cache
	if ( function_exists( 'wp_cache_delete' ) ) {
		wp_cache_delete( 'countries-locale', 'woocommerce' );
		wp_cache_delete( 'countries', 'woocommerce' );
	}
}
add_action( 'after_switch_theme', 'dg_clear_wc_locale_cache' );
add_action( 'admin_init', 'dg_clear_wc_locale_cache', 1 );
