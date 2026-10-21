/**
 * Dragon Glow — Account Sign-in
 *
 * Client-side validation for the signed-out sign-in form on /my-account/.
 * Mirrors inc/woocommerce/account/login-validation.php: every problem is
 * collected and shown at once (summary + inline message per field), and the
 * POST only goes through when the form is clean. Server-side validation stays
 * the source of truth; this only saves a round-trip.
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const i18n = window.dgLogin || { messages: {} };
	const msg = i18n.messages;

	// Form field key -> input id (mirrors dg_login_fields() in PHP, minus `credentials`).
	const FIELD_IDS = {
		username: 'dg-login-username',
		password: 'dg-login-password'
	};

	/**
	 * Initialize all sign-in form interactions.
	 */
	function init() {
		const form = document.querySelector('.dg-login__form');
		if (!form) return;

		initValidation(form);
		initServerErrors();
	}

	/**
	 * Wire submit validation, live error clearing and summary links.
	 *
	 * @param {HTMLFormElement} form Sign-in form.
	 */
	function initValidation(form) {
		form.addEventListener('submit', function (e) {
			const errors = validateForm();
			if (errors.length) {
				e.preventDefault();
				renderErrors(form, errors);
				return;
			}

			// Valid: block double submits while the request is in flight.
			const submit = form.querySelector('.dg-login__submit');
			if (submit) {
				window.DGLoading.start(submit);
			}
		});

		// Clear a field's error as soon as the visitor edits it. Bad
		// credentials flag both controls, so editing either clears both.
		form.addEventListener('input', function (e) {
			const field = fieldKeyOf(e.target);
			if (!field) return;
			clearFieldError(form, field);
			clearCredentialsError(form);
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

		// Browsers restore the page from bfcache with the busy state still set.
		window.addEventListener('pageshow', function (e) {
			if (!e.persisted) return;
			const submit = form.querySelector('.dg-login__submit');
			if (submit) {
				window.DGLoading.stop(submit);
			}
		});
	}

	/**
	 * Server-rendered errors (after a failed POST). The summary is a
	 * role="alert" region, so screen readers announce it without focus. When
	 * the first error belongs to a field (e.g. correct account, wrong
	 * password) the cursor goes straight to that field so the visitor can
	 * retype immediately; form-level errors focus the summary instead.
	 */
	function initServerErrors() {
		const summary = document.getElementById('dg-login-summary');
		if (!summary || summary.hasAttribute('hidden')) return;

		const link = summary.querySelector('[data-dg-error-link]');
		const field = link ? document.getElementById(link.getAttribute('href').slice(1)) : null;
		const focusTarget = field || summary;

		focusTarget.focus({ preventScroll: true });
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
	 * Validate every field and return all problems (mirrors login-validation.php).
	 *
	 * @return {Array<{field:string,message:string}>}
	 */
	function validateForm() {
		const errors = [];
		const username = document.getElementById(FIELD_IDS.username);
		const password = document.getElementById(FIELD_IDS.password);

		if (username && !username.value.trim()) {
			errors.push({ field: 'username', message: msg.username_required });
		}
		if (password && !password.value) {
			errors.push({ field: 'password', message: msg.password_required });
		}

		return errors;
	}

	/**
	 * Show errors: inline message per field, aria-invalid, and the summary.
	 *
	 * @param {HTMLFormElement} form   Sign-in form.
	 * @param {Array}           errors Output of validateForm().
	 */
	function renderErrors(form, errors) {
		const summary = document.getElementById('dg-login-summary');
		const list = summary ? summary.querySelector('.dg-login__summary-list') : null;

		Object.keys(FIELD_IDS).forEach(function (key) {
			clearFieldError(form, key, true);
		});
		if (list) list.textContent = '';

		errors.forEach(function (error) {
			const holder = form.querySelector('[data-dg-error-for="' + error.field + '"]');
			const input = document.getElementById(FIELD_IDS[error.field]);

			if (holder) {
				holder.querySelector('.dg-login__error-text').textContent = error.message;
				holder.removeAttribute('hidden');
			}
			if (input) input.setAttribute('aria-invalid', 'true');

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
	 * @param {HTMLFormElement} form  Sign-in form.
	 * @param {string}          field Field key.
	 * @param {boolean}         quiet Skip summary pruning (used during a full re-render).
	 */
	function clearFieldError(form, field, quiet) {
		const holder = form.querySelector('[data-dg-error-for="' + field + '"]');
		const input = document.getElementById(FIELD_IDS[field]);
		if (holder) holder.setAttribute('hidden', '');
		if (input) input.removeAttribute('aria-invalid');
		if (quiet) return;

		pruneSummary('[data-dg-error-link="' + field + '"]');
	}

	/**
	 * Clear the server-side "bad credentials" error (flags both controls).
	 *
	 * @param {HTMLFormElement} form Sign-in form.
	 */
	function clearCredentialsError(form) {
		if (!document.querySelector('[data-dg-error-link="credentials"]')) return;

		Object.keys(FIELD_IDS).forEach(function (key) {
			clearFieldError(form, key, true);
		});
		pruneSummary('[data-dg-error-link="credentials"]');
	}

	/**
	 * Remove summary items matching a selector; hide the summary when empty.
	 *
	 * @param {string} selector Selector of the summary links to remove.
	 */
	function pruneSummary(selector) {
		const summary = document.getElementById('dg-login-summary');
		if (!summary || summary.hasAttribute('hidden')) return;

		summary.querySelectorAll(selector).forEach(function (a) {
			a.parentElement.remove();
		});
		if (!summary.querySelector('li')) summary.setAttribute('hidden', '');
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
