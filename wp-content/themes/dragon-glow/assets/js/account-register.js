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
	 * Enterprise-grade validation (OWASP/NIST aligned):
	 * - Weak: < 12 chars or missing required character types
	 * - Fair: 8-11 chars with all required types
	 * - Good: 12-15 chars with all required types (minimum acceptable)
	 * - Strong: 16+ chars with all required types
	 *
	 * Required character types:
	 * - Lowercase letter (a-z)
	 * - Uppercase letter (A-Z)
	 * - Digit (0-9)
	 * - Special character (!@#$%^&*()_+-=[]{}|;:,.<>?)
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
			text.textContent = text.getAttribute('data-text-unsealed') || 'Password Strength: Weak';
			text.style.color = '';
			updateRequirements(value);
			updateSubmitButton(0);
			return;
		}

		// Character type checks
		const hasLower = /[a-z]/.test(value);
		const hasUpper = /[A-Z]/.test(value);
		const hasDigit = /[0-9]/.test(value);
		const hasSpecial = /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(value);
		const hasAllTypes = hasLower && hasUpper && hasDigit && hasSpecial;

		let level = 0;
		let label = '';
		let color = '';

		// Strength algorithm: length + character diversity
		if (len < 8) {
			level = 1;
			label = 'Password Strength: Weak';
			color = '#D32F2F'; // Red
		} else if (len < 12 || !hasAllTypes) {
			// 8-11 chars OR missing required types = Fair (not acceptable for registration)
			level = 2;
			label = 'Password Strength: Fair';
			color = '#F57C00'; // Orange
		} else if (len < 16) {
			// 12-15 chars + all types = Good (minimum acceptable)
			level = 3;
			label = 'Password Strength: Good';
			color = '#1976D2'; // Blue
		} else {
			// 16+ chars + all types = Strong (recommended)
			level = 4;
			label = 'Password Strength: Strong';
			color = '#388E3C'; // Green
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

		// Update requirements checklist
		updateRequirements(value);

		// Update submit button state
		updateSubmitButton(level);
	}

	/**
	 * Update password requirements checklist visual state.
	 *
	 * @param {string} value Password value.
	 */
	function updateRequirements(value) {
		const requirements = document.querySelectorAll('[data-requirement]');
		if (!requirements.length) return;

		const checks = {
			length: value.length >= 12,
			lowercase: /[a-z]/.test(value),
			uppercase: /[A-Z]/.test(value),
			digit: /[0-9]/.test(value),
			special: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(value)
		};

		requirements.forEach(function (req) {
			const type = req.getAttribute('data-requirement');
			const icon = req.querySelector('.dg-requirement__icon');
			if (checks[type]) {
				req.classList.add('is-met');
				if (icon) icon.textContent = 'check_circle';
			} else {
				req.classList.remove('is-met');
				if (icon) icon.textContent = 'radio_button_unchecked';
			}
		});
	}

	/**
	 * Enable/disable submit button based on password strength.
	 * Only Good (level 3) or Strong (level 4) are acceptable.
	 *
	 * @param {number} level Strength level (0-4).
	 */
	function updateSubmitButton(level) {
		const submitButton = document.querySelector('.dg-register-form__submit');
		if (!submitButton) return;

		// Minimum acceptable: level 3 (Good)
		if (level >= 3) {
			submitButton.removeAttribute('disabled');
			submitButton.style.opacity = '';
			submitButton.style.cursor = '';
		} else {
			submitButton.setAttribute('disabled', 'disabled');
			submitButton.style.opacity = '0.5';
			submitButton.style.cursor = 'not-allowed';
		}
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
	 * Form submission handling (client-side validation + toast notification).
	 */
	function initFormSubmission() {
		const form = document.querySelector('.dg-register-form');
		if (!form) return;

		// Client-side validation before submission
		form.addEventListener('submit', function (e) {
			const passwordInput = document.querySelector('[data-dg-password-strength]');
			if (!passwordInput) return;

			const password = passwordInput.value;

			// Validate password strength (must be Good or Strong)
			const len = password.length;
			const hasLower = /[a-z]/.test(password);
			const hasUpper = /[A-Z]/.test(password);
			const hasDigit = /[0-9]/.test(password);
			const hasSpecial = /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password);
			const hasAllTypes = hasLower && hasUpper && hasDigit && hasSpecial;

			// Minimum: 12+ chars with all required types
			if (len < 12 || !hasAllTypes) {
				e.preventDefault();

				// Show error message
				const errorMsg = document.querySelector('.dg-register-form__password-error');
				if (errorMsg) {
					errorMsg.removeAttribute('hidden');
					errorMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });

					// Auto-hide after 5 seconds
					setTimeout(function () {
						errorMsg.setAttribute('hidden', '');
					}, 5000);
				}

				// Focus password field
				passwordInput.focus();
				return;
			}
		});

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
