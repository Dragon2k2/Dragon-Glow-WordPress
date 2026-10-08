/**
 * Dragon Glow — Toast notification system (window.DGToast).
 *
 * Site-wide toast UI (Neon / Cyberpunk style, see assets/css/toast.css).
 * Shared module — any page/feature can call window.DGToast.show(...).
 *
 * Usage:
 *   window.DGToast.show({ tone: 'success', title: 'Saved', text: 'Address updated.' });
 *   window.DGToast.show({ tone: 'error', text: 'Something went wrong.' });
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	const prefersReduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
	const DEFAULT_DURATION = 5000;

	const ICONS = {
		success: 'check_circle',
		error: 'error',
		info: 'info',
	};

	let viewport = null;

	/**
	 * Lazily create (or reuse) the fixed bottom-right toast viewport.
	 *
	 * @return {HTMLElement}
	 */
	function getViewport() {
		if (viewport && document.body.contains(viewport)) return viewport;

		viewport = document.getElementById('dg-toast-viewport');
		if (!viewport) {
			viewport = document.createElement('div');
			viewport.id = 'dg-toast-viewport';
			viewport.className = 'dg-toast-viewport';
			viewport.setAttribute('aria-live', 'polite');
			viewport.setAttribute('aria-atomic', 'true');
			document.body.appendChild(viewport);
		}
		return viewport;
	}

	/**
	 * Escape text for safe innerHTML use (titles/messages are plain text,
	 * never pre-rendered HTML from the server).
	 *
	 * @param {string} str
	 * @return {string}
	 */
	function escapeHTML(str) {
		const div = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	}

	/**
	 * Remove a toast element with exit animation (skipped when reduced motion).
	 *
	 * @param {HTMLElement} el
	 */
	function dismiss(el) {
		if (!el || el.dataset.dgDismissing === '1') return;
		el.dataset.dgDismissing = '1';

		if (prefersReduced) {
			el.remove();
			return;
		}

		el.classList.add('is-leaving');
		el.addEventListener('transitionend', function () {
			el.remove();
		}, { once: true });

		// Fallback in case transitionend doesn't fire (e.g. element display changes).
		setTimeout(function () {
			if (el.isConnected) el.remove();
		}, 400);
	}

	/**
	 * Show a toast notification.
	 *
	 * @param {Object} options
	 * @param {string} [options.tone='info']   'success' | 'error' | 'info'.
	 * @param {string} [options.title]         Optional bold title line.
	 * @param {string} options.text            Message body (required).
	 * @param {number} [options.duration=5000] Auto-dismiss delay in ms. Pass 0 to disable auto-dismiss.
	 * @return {HTMLElement} The toast element (for advanced callers).
	 */
	function show(options) {
		const opts = options || {};
		const tone = ['success', 'error', 'info'].indexOf(opts.tone) !== -1 ? opts.tone : 'info';
		const text = opts.text || '';
		const title = opts.title || '';
		const duration = typeof opts.duration === 'number' ? opts.duration : DEFAULT_DURATION;
		const icon = ICONS[tone];

		const el = document.createElement('div');
		el.className = 'dg-toast';
		el.setAttribute('data-tone', tone);
		el.setAttribute('role', 'status');

		el.innerHTML =
			'<span class="dg-toast__icon material-symbols-outlined" aria-hidden="true">' + icon + '</span>' +
			'<div class="dg-toast__body">' +
			(title ? '<p class="dg-toast__title">' + escapeHTML(title) + '</p>' : '') +
			'<p class="dg-toast__text">' + escapeHTML(text) + '</p>' +
			'</div>' +
			'<button type="button" class="dg-toast__close" aria-label="Dismiss notification">' +
			'<span class="material-symbols-outlined" aria-hidden="true">close</span>' +
			'</button>';

		getViewport().appendChild(el);

		const closeBtn = el.querySelector('.dg-toast__close');
		if (closeBtn) {
			closeBtn.addEventListener('click', function () {
				dismiss(el);
			});
		}

		if (duration > 0) {
			let timer = setTimeout(function () { dismiss(el); }, duration);
			el.addEventListener('mouseenter', function () { clearTimeout(timer); });
			el.addEventListener('mouseleave', function () { timer = setTimeout(function () { dismiss(el); }, 1500); });
		}

		return el;
	}

	window.DGToast = { show: show };
})();
