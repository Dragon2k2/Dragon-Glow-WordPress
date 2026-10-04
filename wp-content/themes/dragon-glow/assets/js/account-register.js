/**
 * Dragon Glow — Account Register Page
 *
 * Password strength indicator, password toggle visibility, and form interactions
 * for /my-account/register/ endpoint (Luminous Radiance design).
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	/**
	 * Initialize all register page interactions.
	 */
	function init() {
		initPasswordStrength();
		initPasswordToggle();
		initFormSubmission();
		initCustomDropdown();
	}

	/**
	 * Password strength indicator (matches reference design exactly).
	 */
	function initPasswordStrength() {
		const passwordInput = document.querySelector('[data-dg-password-strength]');
		if (!passwordInput) return;

		const bars = document.querySelectorAll('[data-strength-bar]');
		const text = document.querySelector('[data-strength-text]');

		if (!bars.length || !text) return;

		passwordInput.addEventListener('input', function () {
			const value = this.value;
			updateStrength(value, bars, text);
		});
	}

	/**
	 * Update password strength bars and text.
	 *
	 * Strength levels (matching reference exactly):
	 * - Unsealed: no input (0 length)
	 * - Frail: < 6 characters (1 bar, red)
	 * - Tempered: 6-8 characters (2 bars, gold)
	 * - Formidable: 9-11 characters (3 bars, darker gold)
	 * - Imperial Sovereign: 12+ characters (4 bars, green)
	 *
	 * @param {string} value Password value.
	 * @param {NodeList} bars Strength bar elements.
	 * @param {HTMLElement} text Strength text element.
	 */
	function updateStrength(value, bars, text) {
		// Reset all bars
		bars.forEach(function (bar) {
			bar.removeAttribute('data-strength');
		});

		const len = value.length;

		if (len === 0) {
			text.textContent = text.getAttribute('data-text-unsealed') || 'Cipher Strength: Unsealed';
			text.style.color = '';
			return;
		}

		let level = 0;
		let label = '';
		let color = '';

		if (len < 6) {
			level = 1;
			label = 'Cipher Strength: Frail';
			color = '#BA1A1A';
		} else if (len < 9) {
			level = 2;
			label = 'Cipher Strength: Tempered';
			color = '#715509';
		} else if (len < 12) {
			level = 3;
			label = 'Cipher Strength: Formidable';
			color = '#715509';
		} else {
			level = 4;
			label = 'Cipher Strength: Imperial Sovereign';
			color = '#2E632B';
		}

		// Update bars
		for (let i = 0; i < level; i++) {
			const bar = bars[i];
			if (bar) {
				if (level === 1) {
					bar.setAttribute('data-strength', 'weak');
				} else if (level === 2) {
					bar.setAttribute('data-strength', 'fair');
				} else if (level === 3) {
					bar.setAttribute('data-strength', 'good');
				} else {
					bar.setAttribute('data-strength', 'strong');
				}
			}
		}

		// Update text
		text.textContent = label;
		text.style.color = color;
	}

	/**
	 * Password visibility toggle (reuse from account-auth pattern).
	 */
	function initPasswordToggle() {
		const toggleButtons = document.querySelectorAll('[data-dg-toggle-password]');

		toggleButtons.forEach(function (button) {
			button.addEventListener('click', function () {
				const targetSelector = this.getAttribute('data-dg-toggle-password');
				const input = document.querySelector(targetSelector);
				const icon = this.querySelector('.material-symbols-outlined');

				if (!input || !icon) return;

				if (input.type === 'password') {
					input.type = 'text';
					icon.textContent = 'visibility_off';
					this.setAttribute('aria-label', this.getAttribute('data-label-show') || 'Hide password');
				} else {
					input.type = 'password';
					icon.textContent = 'visibility';
					this.setAttribute('aria-label', this.getAttribute('data-label-hide') || 'Show password');
				}
			});
		});
	}

	/**
	 * Form submission handling (show toast notification on success).
	 */
	function initFormSubmission() {
		const form = document.querySelector('.dg-register-form');
		if (!form) return;

		// WooCommerce handles actual submission — this is just for UX enhancement.
		// If registration succeeds, WC redirects to My Account dashboard.
		// We show toast only for simulated/demo purposes here (not triggered in real flow).

		// Check for success message from WooCommerce
		const notices = document.querySelectorAll('.woocommerce-message, .woocommerce-info');
		if (notices.length > 0) {
			const toast = document.getElementById('dg-register-toast');
			const toastMsg = document.getElementById('dg-register-toast-msg');

			if (toast && toastMsg) {
				const firstNotice = notices[0];
				const message = firstNotice.textContent.trim();
				if (message) {
					toastMsg.textContent = message;
				}

				toast.removeAttribute('hidden');

				setTimeout(function () {
					toast.setAttribute('hidden', '');
				}, 3400);
			}
		}
	}

	/**
	 * Custom dropdown ("Primary Skin Aspiration") — button trigger + listbox panel.
	 *
	 * The native <select> stays in the DOM (visually hidden) so the form still
	 * submits `skin_aspiration` correctly if this script fails to load. This
	 * function only drives the visible UI and keeps both in sync.
	 */
	function initCustomDropdown() {
		const dropdown = document.querySelector('[data-dg-dropdown]');
		if (!dropdown) return;

		const select = dropdown.querySelector('.dg-dropdown__native-select');
		const trigger = dropdown.querySelector('.dg-dropdown__trigger');
		const valueEl = dropdown.querySelector('.dg-dropdown__value');
		const panel = dropdown.querySelector('.dg-dropdown__panel');
		const options = Array.from(dropdown.querySelectorAll('.dg-dropdown__option'));

		if (!select || !trigger || !valueEl || !panel || !options.length) return;

		let activeIndex = options.findIndex(function (opt) {
			return opt.classList.contains('is-selected');
		});
		if (activeIndex < 0) activeIndex = 0;

		function openPanel() {
			panel.removeAttribute('hidden');
			dropdown.classList.add('is-open');
			trigger.setAttribute('aria-expanded', 'true');
			focusOption(activeIndex);
		}

		function closePanel() {
			panel.setAttribute('hidden', '');
			dropdown.classList.remove('is-open');
			trigger.setAttribute('aria-expanded', 'false');
		}

		function isOpen() {
			return dropdown.classList.contains('is-open');
		}

		function focusOption(index) {
			options.forEach(function (opt, i) {
				opt.classList.toggle('is-active', i === index);
			});
			const target = options[index];
			if (target && typeof target.scrollIntoView === 'function') {
				target.scrollIntoView({ block: 'nearest' });
			}
		}

		function selectOption(index) {
			const option = options[index];
			if (!option) return;

			options.forEach(function (opt) {
				opt.classList.remove('is-selected');
				opt.setAttribute('aria-selected', 'false');
			});
			option.classList.add('is-selected');
			option.setAttribute('aria-selected', 'true');

			valueEl.textContent = option.querySelector('span').textContent;
			select.value = option.getAttribute('data-value');
			activeIndex = index;
		}

		trigger.addEventListener('click', function (e) {
			e.stopPropagation();
			if (isOpen()) {
				closePanel();
			} else {
				openPanel();
			}
		});

		options.forEach(function (option, index) {
			option.addEventListener('click', function () {
				selectOption(index);
				closePanel();
				trigger.focus();
			});
			option.addEventListener('mouseenter', function () {
				activeIndex = index;
				focusOption(index);
			});
		});

		trigger.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
				e.preventDefault();
				if (!isOpen()) {
					openPanel();
					return;
				}
				const delta = e.key === 'ArrowDown' ? 1 : -1;
				activeIndex = (activeIndex + delta + options.length) % options.length;
				focusOption(activeIndex);
			} else if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				if (isOpen()) {
					selectOption(activeIndex);
					closePanel();
				} else {
					openPanel();
				}
			} else if (e.key === 'Escape') {
				if (isOpen()) {
					e.preventDefault();
					closePanel();
				}
			} else if (e.key === 'Home' && isOpen()) {
				e.preventDefault();
				activeIndex = 0;
				focusOption(activeIndex);
			} else if (e.key === 'End' && isOpen()) {
				e.preventDefault();
				activeIndex = options.length - 1;
				focusOption(activeIndex);
			}
		});

		document.addEventListener('click', function (e) {
			if (isOpen() && !dropdown.contains(e.target)) {
				closePanel();
			}
		});
	}

	/**
	 * Show toast notification (helper for future use).
	 *
	 * @param {string} message Message to display.
	 */
	function showToast(message) {
		const toast = document.getElementById('dg-register-toast');
		const toastMsg = document.getElementById('dg-register-toast-msg');

		if (!toast || !toastMsg) return;

		toastMsg.textContent = message;
		toast.removeAttribute('hidden');

		setTimeout(function () {
			toast.setAttribute('hidden', '');
		}, 3400);
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
