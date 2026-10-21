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

	const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const i18n = window.dgRegister || { maxName: 50, messages: {}, labels: {} };
	const msg = i18n.messages;

	// Form field key -> input id (mirrors dg_register_fields() in PHP).
	const FIELD_IDS = {
		first_name: 'dg-reg-first-name',
		last_name: 'dg-reg-last-name',
		email: 'dg-reg-email',
		password: 'dg-reg-password',
		terms: 'dg-reg-terms'
	};
	const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
	const PASSWORD_RULES = [
		['lowercase', /[a-z]/],
		['uppercase', /[A-Z]/],
		['digit', /[0-9]/],
		['special', /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/]
	];

	/**
	 * Initialize all register page interactions.
	 */
	function init() {
		try {
			initPasswordStrength();
		} catch (e) {
			console.warn('Error in initPasswordStrength:', e);
		}
		
		try {
			initFormSubmission();
		} catch (e) {
			console.warn('Error in initFormSubmission:', e);
		}
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
	 * Form submission handling (client-side validation + toast notification).
	 */
	function initFormSubmission() {
		const form = document.querySelector('.dg-register-form');
		if (!form) return;

		// Client-side validation before submission: collect every problem,
		// show them all at once, and only let the POST through when clean.
		form.addEventListener('submit', function (e) {
			const errors = validateForm(form);
			if (!errors.length) return;

			e.preventDefault();
			renderErrors(form, errors);
		});

		// Clear a field's error as soon as the visitor edits it.
		form.addEventListener('input', function (e) {
			const field = fieldKeyOf(e.target);
			if (field) clearFieldError(form, field);
		});
		form.addEventListener('change', function (e) {
			const field = fieldKeyOf(e.target);
			if (field) clearFieldError(form, field);
		});

		// Summary links jump to (and focus) the offending control.
		form.parentElement.addEventListener('click', function (e) {
			const link = e.target.closest('[data-dg-error-link]');
			if (!link) return;
			const target = document.getElementById(link.getAttribute('href').slice(1));
			if (!target) return;
			e.preventDefault();
			target.focus();
			target.scrollIntoView({ behavior: prefersReduced ? 'auto' : 'smooth', block: 'nearest' });
		});

		// Server-rendered errors (after a failed POST): focus the summary only, no scroll.
		const summary = document.getElementById('dg-register-summary');
		if (summary && !summary.hasAttribute('hidden')) {
			summary.focus({ preventScroll: true });
		}

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
	 * Map an input element back to its field key (null when not validated).
	 *
	 * @param {EventTarget} el Event target.
	 * @return {string|null}
	 */
	function fieldKeyOf(el) {
		if (!el || !el.id) return null;
		return Object.keys(FIELD_IDS).find(function (key) {
			return FIELD_IDS[key] === el.id;
		}) || null;
	}

	/**
	 * Validate every field and return all problems (mirrors register-validation.php).
	 *
	 * @param {HTMLFormElement} form Register form.
	 * @return {Array<{field:string,message:string}>}
	 */
	function validateForm(form) {
		const errors = [];
		const add = function (field, message) {
			errors.push({ field: field, message: message });
		};
		const val = function (key) {
			const el = document.getElementById(FIELD_IDS[key]);
			return el ? el.value : '';
		};

		['first_name', 'last_name'].forEach(function (key) {
			const value = val(key).trim();
			if (!value) {
				add(key, msg[key + '_required']);
			} else if (value.length > i18n.maxName) {
				add(key, msg.name_too_long);
			}
		});

		const email = val('email').trim();
		if (!email) {
			add('email', msg.email_required);
		} else if (!EMAIL_RE.test(email)) {
			add('email', msg.email_invalid);
		}

		const password = val('password');
		if (!password) {
			add('password', msg.password_required);
		} else if (password.length < 12) {
			add('password', msg.password_too_short);
		} else {
			const missing = PASSWORD_RULES.filter(function (rule) {
				return !rule[1].test(password);
			}).map(function (rule) {
				return i18n.labels[rule[0]];
			});
			if (missing.length) {
				add('password', msg.password_missing.replace('%s', missing.join(', ')));
			}
		}

		const terms = document.getElementById(FIELD_IDS.terms);
		if (terms && !terms.checked) {
			add('terms', msg.terms_required);
		}

		return errors;
	}

	/**
	 * Show errors: inline message per field, aria-invalid, and the summary.
	 *
	 * @param {HTMLFormElement} form   Register form.
	 * @param {Array}           errors Output of validateForm().
	 */
	function renderErrors(form, errors) {
		const summary = document.getElementById('dg-register-summary');
		const list = summary ? summary.querySelector('.dg-register-form__summary-list') : null;

		Object.keys(FIELD_IDS).forEach(function (key) {
			clearFieldError(form, key, true);
		});
		if (list) list.textContent = '';

		const grouped = {};
		errors.forEach(function (error) {
			(grouped[error.field] = grouped[error.field] || []).push(error.message);

			if (list) {
				const li = document.createElement('li');
				const a = document.createElement('a');
				a.href = '#' + FIELD_IDS[error.field];
				a.setAttribute('data-dg-error-link', error.field);
				a.textContent = error.message;
				li.appendChild(a);
				list.appendChild(li);
			}
		});

		Object.keys(grouped).forEach(function (key) {
			const holder = form.querySelector('[data-dg-error-for="' + key + '"]');
			const input = document.getElementById(FIELD_IDS[key]);
			if (holder) {
				holder.querySelector('.dg-register-form__error-text').textContent = grouped[key].join(' ');
				holder.removeAttribute('hidden');
			}
			if (input) input.setAttribute('aria-invalid', 'true');
		});

		if (summary) summary.removeAttribute('hidden');

		const first = document.getElementById(FIELD_IDS[errors[0].field]);
		if (first) {
			first.focus({ preventScroll: true });
			first.scrollIntoView({ behavior: prefersReduced ? 'auto' : 'smooth', block: 'nearest' });
		}
	}

	/**
	 * Clear one field's error state and prune it from the summary.
	 *
	 * @param {HTMLFormElement} form   Register form.
	 * @param {string}          field  Field key.
	 * @param {boolean}         quiet  Skip summary pruning (used during a full re-render).
	 */
	function clearFieldError(form, field, quiet) {
		const holder = form.querySelector('[data-dg-error-for="' + field + '"]');
		const input = document.getElementById(FIELD_IDS[field]);
		if (holder) holder.setAttribute('hidden', '');
		if (input) input.removeAttribute('aria-invalid');
		if (quiet) return;

		const summary = document.getElementById('dg-register-summary');
		if (!summary || summary.hasAttribute('hidden')) return;
		summary.querySelectorAll('[data-dg-error-link="' + field + '"]').forEach(function (a) {
			a.parentElement.remove();
		});
		if (!summary.querySelector('li')) summary.setAttribute('hidden', '');
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
		// DOM still loading, wait for DOMContentLoaded
		document.addEventListener('DOMContentLoaded', init);
	} else {
		// DOM already loaded (interactive or complete)
		// Use setTimeout to ensure DOM is fully parsed before running
		setTimeout(init, 0);
	}
})();
