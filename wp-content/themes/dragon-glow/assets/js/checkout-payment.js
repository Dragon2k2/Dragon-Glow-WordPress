/**
 * Dragon Glow — Checkout Payment Methods Interactions
 *
 * Smooth expand/collapse animations for payment method descriptions
 * using Motion API (vanilla JS). Respects `prefers-reduced-motion`.
 *
 * @package Dragon_Glow
 */

import { animate } from 'https://cdn.jsdelivr.net/npm/motion@11/+esm';

(function () {
	'use strict';

	const prefersReduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

	/**
	 * Animate payment description expand/collapse
	 *
	 * @param {HTMLElement} desc - The .dg-payment-description element
	 * @param {boolean} show - true to expand, false to collapse
	 */
	function animateDescription(desc, show) {
		if (!desc) return;

		if (prefersReduced) {
			// Instant toggle for reduced motion
			desc.style.maxHeight = show ? 'none' : '0';
			desc.style.opacity = show ? '1' : '0';
			desc.style.display = show ? 'block' : 'none';
			return;
		}

		if (show) {
			// Measure natural height
			desc.style.display = 'block';
			desc.style.maxHeight = 'none';
			const naturalHeight = desc.scrollHeight;
			desc.style.maxHeight = '0';

			// Animate expand
			animate(
				desc,
				{
					maxHeight: [0, naturalHeight + 'px'],
					opacity: [0, 1]
				},
				{
					duration: 0.4,
					easing: [0.4, 0, 0.2, 1]
				}
			).finished.then(() => {
				desc.style.maxHeight = 'none';
			});
		} else {
			// Animate collapse
			const currentHeight = desc.scrollHeight;
			desc.style.maxHeight = currentHeight + 'px';

			animate(
				desc,
				{
					maxHeight: [currentHeight + 'px', 0],
					opacity: [1, 0]
				},
				{
					duration: 0.3,
					easing: [0.4, 0, 0.2, 1]
				}
			).finished.then(() => {
				desc.style.display = 'none';
			});
		}
	}

	/**
	 * Handle payment method selection change
	 */
	function handlePaymentSelection() {
		const radios = document.querySelectorAll('.dg-payment-radio');

		radios.forEach(function (radio) {
			radio.addEventListener('change', function () {
				// Collapse all descriptions
				document.querySelectorAll('.dg-payment-description').forEach(function (desc) {
					animateDescription(desc, false);
				});

				// Expand selected method's description
				if (this.checked) {
					const card = this.closest('.dg-payment-method');
					const desc = card ? card.querySelector('.dg-payment-description') : null;
					if (desc) {
						animateDescription(desc, true);
					}

					// Update place order button text if gateway specifies custom text
					const orderButtonText = this.getAttribute('data-order_button_text');
					const placeOrderBtn = document.getElementById('place_order');
					if (orderButtonText && placeOrderBtn) {
						const btnTextSpan = placeOrderBtn.querySelector('span:last-child');
						if (btnTextSpan) {
							btnTextSpan.textContent = orderButtonText;
						}
					}
				}
			});
		});

		// Show initially selected method's description
		const selectedRadio = document.querySelector('.dg-payment-radio:checked');
		if (selectedRadio) {
			const card = selectedRadio.closest('.dg-payment-method');
			const desc = card ? card.querySelector('.dg-payment-description') : null;
			if (desc) {
				if (prefersReduced) {
					desc.style.display = 'block';
					desc.style.maxHeight = 'none';
					desc.style.opacity = '1';
				} else {
					// Small delay to ensure DOM is ready
					setTimeout(() => animateDescription(desc, true), 100);
				}
			}
		}
	}

	/**
	 * Add hover effect for payment cards (subtle scale)
	 */
	function addHoverEffects() {
		if (prefersReduced) return;

		const cards = document.querySelectorAll('.dg-payment-card');
		cards.forEach(function (card) {
			card.addEventListener('mouseenter', function () {
				const icon = this.querySelector('.dg-payment-icon');
				if (icon && !this.closest('.dg-payment-method').querySelector('.dg-payment-radio:checked')) {
					animate(icon, { 
						scale: 1.08,
						rotate: 3
					}, { 
						duration: 0.3,
						easing: [0.34, 1.56, 0.64, 1]
					});
				}
			});

			card.addEventListener('mouseleave', function () {
				const icon = this.querySelector('.dg-payment-icon');
				if (icon && !this.closest('.dg-payment-method').querySelector('.dg-payment-radio:checked')) {
					animate(icon, { 
						scale: 1,
						rotate: 0
					}, { 
						duration: 0.25,
						easing: [0.4, 0, 0.2, 1]
					});
				}
			});
		});
	}

	/**
	 * Add ripple effect on card click
	 */
	function addRippleEffect() {
		if (prefersReduced) return;

		const labels = document.querySelectorAll('.dg-payment-label');
		labels.forEach(function (label) {
			label.addEventListener('click', function (e) {
				const card = this.querySelector('.dg-payment-card');
				if (!card) return;

				// Create ripple element
				const ripple = document.createElement('span');
				ripple.style.cssText = `
					position: absolute;
					border-radius: 50%;
					background: rgba(255, 193, 7, 0.4);
					width: 20px;
					height: 20px;
					pointer-events: none;
					z-index: 1;
				`;

				const rect = card.getBoundingClientRect();
				const x = e.clientX - rect.left;
				const y = e.clientY - rect.top;

				ripple.style.left = x + 'px';
				ripple.style.top = y + 'px';
				card.appendChild(ripple);

				// Animate ripple
				animate(ripple, {
					width: [20, Math.max(rect.width, rect.height) * 2],
					height: [20, Math.max(rect.width, rect.height) * 2],
					opacity: [0.6, 0]
				}, {
					duration: 0.6,
					easing: [0.4, 0, 0.2, 1]
				}).finished.then(() => {
					ripple.remove();
				});
			});
		});
	}

	/**
	 * Re-bind after WooCommerce AJAX updates
	 */
	function bindWooCommerceEvents() {
		if (typeof jQuery === 'undefined') return;

		jQuery(document.body).on('updated_checkout payment_method_selected', function () {
			handlePaymentSelection();
			addHoverEffects();
			addRippleEffect();
		});
	}

	/**
	 * Initialize on DOM ready
	 */
	function init() {
		handlePaymentSelection();
		addHoverEffects();
		addRippleEffect();
		bindWooCommerceEvents();
	}

	// Wait for DOM
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
