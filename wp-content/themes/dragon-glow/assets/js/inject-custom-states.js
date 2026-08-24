/**
 * Dragon Glow — Inject Custom Country States
 *
 * WooCommerce localizes states data before theme filters run,
 * so we inject custom states directly into wc_country_select_params.
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	if (typeof wc_country_select_params === 'undefined' || typeof dgCustomStates === 'undefined') {
		console.warn('Dragon Glow: Missing WC params or custom states data');
		return;
	}

	// Parse existing countries data.
	var existingCountries = JSON.parse(wc_country_select_params.countries || '{}');

	// Merge custom states.
	var customStates = dgCustomStates;
	var injectedCount = 0;

	for (var countryCode in customStates) {
		if (customStates.hasOwnProperty(countryCode)) {
			existingCountries[countryCode] = customStates[countryCode];
			injectedCount++;
		}
	}

	// Update the global params.
	wc_country_select_params.countries = JSON.stringify(existingCountries);

	// Force re-init country select fields after DOM ready.
	jQuery(document).ready(function ($) {
		// Wait for WooCommerce to init first.
		setTimeout(function () {
			// Trigger change on country fields to refresh state fields.
			var countrySelects = $('.country_to_state, .country_select');
			if (countrySelects.length) {
				countrySelects.each(function () {
					$(this).trigger('change');
				});
			}
		}, 500);
	});
})();
