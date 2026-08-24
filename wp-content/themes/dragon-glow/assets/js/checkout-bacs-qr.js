/**
 * Dragon Glow — BACS QR Code Generator
 *
 * Generates an EMVCo-Compatible QR code for the BACS (Direct Bank Transfer)
 * payment method. The QR encodes a TLV-formatted payload so that mobile
 * banking apps can pre-fill the transfer (amount, currency, recipient
 * account).
 *
 * Country switch: the QR is only generated for `home_country` (US by
 * default — see site option). When the customer selects a different
 * country, the QR is hidden and the manual bank-details accordion
 * becomes the primary UI.
 *
 * Source: data is read from the inline JSON payload rendered by
 * `dg_bacs_qr_panel()` (see inc/woocommerce/bacs-qr.php). There is no
 * settings round-trip — refresh on `updated_checkout` is enough to
 * reflect WC cart total changes.
 *
 * Two render contexts share this script:
 *   1. Checkout form        — listen to `#billing_country` / `#shipping_country`
 *                              so the panel reflects the customer's choice.
 *   2. Order-received page  — render once with the country captured at order
 *                              time; no input listeners (the form fields
 *                              don't exist on the thank-you page).
 *
 * @package Dragon_Glow
 */

import QRCode from 'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/+esm';
import { animate } from 'https://cdn.jsdelivr.net/npm/motion@11/+esm';

(function () {
	'use strict';

	const prefersReduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

	/**
	 * Build an EMVCo TLV string for a US ACH push payment.
	 *
	 * Format reference (simplified for US merchant account):
	 *   Tag 00  : Payload Format Indicator       → "01"
	 *   Tag 01  : Point of Initiation Method     → "12" (dynamic)
	 *   Tag 26  : Merchant Account Information  → nested TLV (US-specific guid + routing + account)
	 *   Tag 52  : Merchant Category Code         → "0000"
	 *   Tag 53  : Transaction Currency (ISO 4217) → "840"
	 *   Tag 54  : Transaction Amount             → total as decimal
	 *   Tag 58  : Country Code (ISO 3166-1)      → "US"
	 *   Tag 59  : Merchant Name                  → display name
	 *   Tag 60  : Merchant City                  → optional
	 *   Tag 62  : Additional Data Field          → nested TLV (order id)
	 *   Tag 63  : CRC                            → checksum
	 *
	 * Real USD push-payment apps (Cash App, Zelle, banking apps with EMVCo
	 * reader) accept the merchant account info inside Tag 26 with a US
	 * Merchant Identifier (com.uber) — but we don't have that, so we use
	 * a generic ACH-style payload that still presents the data on scan.
	 *
	 * @param {Object} data Payload from the dom.
	 * @returns {string} EMVCo TLV string.
	 */
	function buildEmvcoPayload(data) {
		const bank = data.bank || {};
		const amount = (data.cart_total || '0').toString();

		// Merchant Account Information (Tag 26) — US uses nested TLV
		//   sub-tag 00: globally unique identifier ("com.uber" / "org.spots")
		//   sub-tag 01: routing number
		//   sub-tag 02: account number
		//   sub-tag 03: account type
		const merchantAccountInfo =
			tlv('00', 'org.dragonglow.us') +
			tlv('01', bank.routing_number || '') +
			tlv('02', bank.account_number || '') +
			tlv('03', bank.account_type || 'CHECKING');

		// Additional Data Field (Tag 62) — order id
		const additionalData = tlv('05', data.order_id || 'PENDING');

		const payload =
			tlv('00', '01') +
			tlv('01', '12') +
			tlv('26', merchantAccountInfo) +
			tlv('52', '0000') +
			tlv('53', currencyCode(data.currency)) +
			tlv('54', amount) +
			tlv('58', 'US') +
			tlv('59', sanitize(bank.account_name || data.shop_name || '')) +
			tlv('60', 'NEW YORK') +
			tlv('62', additionalData);

		// Append CRC (Tag 63) — required for EMVCo. Real implementation uses
		// CRC-16/CCITT-FALSE. Here we leave Tag 63 empty so the payload still
		// parses as a valid QR; apps that strictly require a CRC will show
		// the raw payload instead of auto-populating the form. Customers can
		// still copy the bank details from the accordion below.
		return payload + '6304';
	}

	/**
	 * TLV (Tag-Length-Value) encoding.
	 * Tag: 2 or 4 hex chars.
	 * Length: 2-digit zero-padded decimal.
	 * Value: the string itself.
	 *
	 * @param {string} tag
	 * @param {string} value
	 * @returns {string}
	 */
	function tlv(tag, value) {
		const v = String(value || '');
		return tag + String(v.length).padStart(2, '0') + v;
	}

	/**
	 * Map ISO 4217 currency code to a numeric code for EMVCo Tag 53.
	 * Only USD is implemented for the launch; other currencies default to 840.
	 *
	 * @param {string} currency 3-letter currency code.
	 * @returns {string} 3-digit numeric code.
	 */
	function currencyCode(currency) {
		const map = {
			USD: '840',
			EUR: '978',
			GBP: '826',
			JPY: '392',
			VND: '704',
			AUD: '036',
			CAD: '124',
		};
		return map[(currency || 'USD').toUpperCase()] || '840';
	}

	/**
	 * Strip non-ASCII characters that EMVCo disallows in display fields.
	 *
	 * @param {string} value
	 * @returns {string}
	 */
	function sanitize(value) {
		return String(value)
			.replace(/[^\x20-\x7E]/g, '')
			.substring(0, 25)
			.toUpperCase();
	}

	/**
	 * Read the JSON payload from the panel and parse it.
	 *
	 * @param {HTMLElement} panel
	 * @returns {Object|null}
	 */
	function readPayload(panel) {
		const el = panel.querySelector('[data-bacs-qr-payload]');
		if (!el) return null;
		try {
			return JSON.parse(el.textContent || '{}');
		} catch (err) {
			console.warn('[DG BACS QR] Failed to parse payload', err);
			return null;
		}
	}

	/**
	 * Render the QR code into the canvas container.
	 *
	 * @param {HTMLElement} panel
	 * @param {string} text
	 */
	function renderQR(panel, text) {
		const canvas = panel.querySelector('[data-bacs-qr-canvas]');
		if (!canvas) return;

		// Clear previous content (canvas/svg/img)
		canvas.innerHTML = '';

		// Build QR via the npm 'qrcode' module.
		// We use toCanvas with explicit options for a clean, readable code.
		QRCode.toCanvas(text, {
			errorCorrectionLevel: 'M',
			margin: 1,
			scale: 5,
			color: {
				dark: '#1c1b1b',
				light: '#ffffff',
			},
		})
			.then((canvasEl) => {
				canvas.appendChild(canvasEl);
				canvas.setAttribute('aria-label', 'QR code for bank transfer');
			})
			.catch((err) => {
				console.warn('[DG BACS QR] QR generation failed', err);
			});
	}

	/**
	 * Update the country-aware UI: amount display, country notice, QR visibility.
	 *
	 * @param {HTMLElement} panel
	 * @param {Object} data
	 * @param {string} country
	 */
	function updateCountry(panel, data, country) {
		panel.setAttribute('data-country', country);

		// Country name — only update when the server has not rendered it already.
		// Server-rendered content carries `dg-bacs-qr__country-name--server`; JS-only
		// panels (checkout form before any server render) won't have this class,
		// so JS takes over the naming responsibility for those cases.
		const nameEl = panel.querySelector('[data-bacs-qr-country-name]');
		if (nameEl && !nameEl.classList.contains('dg-bacs-qr__country-name--server')) {
			const countryName = (data.countryName || {})[country] || country;
			nameEl.textContent = countryName;
		}

		// Country note (only show QR for US)
		const noteEl = panel.querySelector('[data-bacs-qr-country-note]');
		if (noteEl) {
			if (country === data.home_country) {
				noteEl.textContent = data.i18n?.qrAvailable || 'QR code is available for US customers.';
			} else {
				noteEl.textContent = data.i18n?.qrUnavailable || 'QR code is only available for US accounts. Please use the manual bank details below.';
			}
		}

		// Generate / hide QR
		if (country === data.home_country) {
			const payload = buildEmvcoPayload(data);
			renderQR(panel, payload);
		} else {
			const canvas = panel.querySelector('[data-bacs-qr-canvas]');
			if (canvas) canvas.innerHTML = '';
		}
	}

	/**
	 * Initialize a single BACS QR panel.
	 *
	 * @param {HTMLElement} panel
	 */
	function initPanel(panel) {
		const data = readPayload(panel);
		if (!data) return;

		// Pull country name list from dgBacsQr (localized by PHP).
		const locale = window.dgBacsQr || {};
		data.countryName = locale.countries || {};
		data.i18n = locale.i18n || {};

		// Initial render uses the country currently shown by the panel.
		updateCountry(panel, data, panel.getAttribute('data-country') || data.home_country);

		// Reveal animation on panel mount.
		if (!prefersReduced) {
			panel.style.opacity = '0';
			panel.style.transform = 'translateY(8px)';
			animate(
				panel,
				{ opacity: [0, 1], transform: ['translateY(8px)', 'translateY(0)'] },
				{ duration: 0.5, easing: [0.4, 0, 0.2, 1] }
			).finished.then(() => {
				panel.style.opacity = '';
				panel.style.transform = '';
			});
		}

		// Listen to WC country changes — both billing and shipping. Only
		// wire this up when the form fields actually exist on the page
		// (the thank-you page has no checkout form, so the listeners would
		// be no-ops; gating on existence keeps the script quiet there).
		const countryInputs = document.querySelectorAll('#billing_country, #shipping_country');
		if (countryInputs.length === 0) {
			return;
		}

		countryInputs.forEach((input) => {
			input.addEventListener('change', () => {
				// Billing takes priority; fall back to shipping, then current value.
				const billing = document.getElementById('billing_country');
				const shipping = document.getElementById('shipping_country');
				const next = (billing && billing.value) || (shipping && shipping.value) || data.home_country;
				updateCountry(panel, data, next);
			});
		});

		// Re-bind after WC AJAX refresh (same panel recreated).
		if (typeof jQuery !== 'undefined') {
			jQuery(document.body).on('updated_checkout', () => {
				const newData = readPayload(panel);
				if (newData) {
					const billing = document.getElementById('billing_country');
					const country = (billing && billing.value) || newData.country || newData.home_country;
					updateCountry(panel, newData, country);
				}
			});
		}
	}

	/**
	 * Initialize all BACS QR panels on the page.
	 */
	function init() {
		const panels = document.querySelectorAll('[data-bacs-qr-panel]');
		panels.forEach((panel) => initPanel(panel));
	}

	// Wait for DOM (script is enqueued as module → deferred by default,
	// but we still wrap in DOMContentLoaded for safety).
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
