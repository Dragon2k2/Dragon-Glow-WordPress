/**
 * Dragon Glow — Quick Add to Cart
 *
 * Handles silent add-to-cart for quick-add buttons across the site:
 *   - "Add to Ritual" buttons on the Shop grid (product-card.php).
 *   - Quick-add icon on the Best Sellers carousel (best-sellers.php).
 *   - Mock-product quick-add buttons in template-shop.php.
 *
 * This module intentionally NEVER redirects — it fires the AJAX request and
 * restores the button state, regardless of outcome.  The back-end handler
 * (dg_add_to_cart_silently) never returns a redirect URL on this action.
 *
 * Responsibilities:
 *   - Prevent double-click by disabling the button during the request.
 *   - Update only the label text (via the `.dg-quick-add__label` child element)
 *     so the icon remains untouched in the DOM.
 *   - Restore the original label + re-enable the button in ALL code paths
 *     (success, error, network failure) via `.finally()`.
 *   - Call window.DGUpdateCartCount() after a successful add so the header
 *     badge reflects the new cart state without a page reload.
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	// ── Init on DOM ready ─────────────────────────────────────────────────────

	document.addEventListener('DOMContentLoaded', bindQuickAddButtons);

	// ── Core ─────────────────────────────────────────────────────────────────

	/**
	 * Find and bind all `.dg-quick-add` buttons on the page.
	 *
	 * @return {void}
	 */
	function bindQuickAddButtons() {
		var buttons = document.querySelectorAll('.dg-quick-add');
		buttons.forEach(function (btn) {
			btn.addEventListener('click', handleQuickAddClick);
		});
	}

	/**
	 * Handle a single quick-add click.
	 *
	 * @param {Event} e
	 * @return {void}
	 */
	function handleQuickAddClick(e) {
		var btn = e.currentTarget;

		var productId = parseInt(btn.dataset.productId, 10) || 0;
		var slug      = btn.dataset.productSlug || '';
		var quantity  = parseInt(btn.dataset.quantity, 10) || 1;

		// Guard: quick-add buttons should not have size selection — they add qty=1.
		// The shared quantity state from buy-now.js is intentionally ignored here.
		setButtonLoading(btn, true);

		var formData = new FormData();
		formData.append('action', 'dg_ajax_add_to_cart');
		formData.append('nonce', getNonce());
		formData.append('product_id', productId);
		formData.append('slug', slug);
		formData.append('quantity', quantity);

		fetch(getAjaxUrl(), {
			method: 'POST',
			body: formData,
			credentials: 'same-origin'
		})
		.then(function (r) { return r.json(); })
		.then(function (data) {
			// Guard: if the backend ever leaks a redirect URL on this action,
			// warn in the console rather than silently navigate away.
			if (data.data && data.data.redirect) {
				console.warn('[Dragon Glow] quick-add-to-cart received an unexpected redirect URL — this should not happen. URL:', data.data.redirect);
			}

			if (data.success) {
				showAddedFeedback(btn);
				refreshCartCount();
			} else {
				var msg = (data.data && data.data.message)
					? data.data.message
					: gettext('Could not add to bag.');
				showInlineNotice(btn, msg);
			}
		})
		.catch(function () {
			showInlineNotice(btn, gettext('Network error.'));
		})
		.finally(function () {
			resetButton(btn);
		});
	}

	// ── Button state ─────────────────────────────────────────────────────────

	/**
	 * Put a quick-add button into loading state.
	 * Only the label element is modified — the icon child is untouched.
	 *
	 * @param {HTMLElement} btn
	 * @param {boolean}    loading
	 * @return {void}
	 */
	function setButtonLoading(btn, loading) {
		btn.disabled = loading;
		var label = btn.querySelector('.dg-quick-add__label');
		if (label) {
			label.textContent = loading ? '...' : getOriginalLabel(btn);
		}
	}

	/**
	 * Reset a quick-add button to its idle state after a request completes.
	 * Called from the `.finally()` block so it runs unconditionally.
	 *
	 * @param {HTMLElement} btn
	 * @return {void}
	 */
	function resetButton(btn) {
		btn.disabled = false;
		var label = btn.querySelector('.dg-quick-add__label');
		if (label) {
			label.textContent = getOriginalLabel(btn);
		}
		btn.classList.remove('dg-quick-add--added');
	}

	/**
	 * Show brief "Added!" feedback on the label, then revert after 1.5 s.
	 *
	 * @param {HTMLElement} btn
	 * @return {void}
	 */
	function showAddedFeedback(btn) {
		var label = btn.querySelector('.dg-quick-add__label');
		if (!label) return;

		btn.classList.add('dg-quick-add--added');
		label.textContent = gettext('Added!');

		setTimeout(function () {
			if (label) {
				label.textContent = getOriginalLabel(btn);
			}
			btn.classList.remove('dg-quick-add--added');
		}, 1500);
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	/**
	 * Retrieve the original label text stored in the data attribute set at
	 * render time, or fall back to the current textContent of the label span.
	 * Using a data attribute avoids any edge case where the textContent was
	 * already modified by showAddedFeedback() when we read it for reset.
	 *
	 * @param {HTMLElement} btn
	 * @return {string}
	 */
	function getOriginalLabel(btn) {
		return btn.dataset.originalLabel || '';
	}

	/**
	 * Show a temporary inline error message below the button.
	 *
	 * @param {HTMLElement} btn
	 * @param {string}     message
	 * @return {void}
	 */
	function showInlineNotice(btn, message) {
		var notice = btn.closest('.dg-product-image').querySelector('.dg-quick-add-notice');
		if (notice) {
			notice.textContent = message;
			notice.classList.remove('hidden');
			setTimeout(function () {
				if (notice) notice.classList.add('hidden');
			}, 3000);
		}
	}

	/**
	 * Trigger a cart count refresh so the header badge reflects the new state.
	 *
	 * @return {void}
	 */
	function refreshCartCount() {
		if (typeof window.DGUpdateCartCount === 'function') {
			window.DGUpdateCartCount();
		}
	}

	/**
	 * Get AJAX URL from dgAjax global.
	 *
	 * @return {string}
	 */
	function getAjaxUrl() {
		return (window.dgAjax && window.dgAjax.url)
			? window.dgAjax.url
			: '/wp-admin/admin-ajax.php';
	}

	/**
	 * Get nonce from dgAjax global.
	 *
	 * @return {string}
	 */
	function getNonce() {
		return (window.dgAjax && window.dgAjax.nonce)
			? window.dgAjax.nonce
			: '';
	}

	/**
	 * Get a translated string from dgAjax.i18n, falling back to the raw key.
	 *
	 * @param {string} key
	 * @return {string}
	 */
	function gettext(key) {
		return (window.dgAjax && window.dgAjax.i18n && window.dgAjax.i18n[key])
			? window.dgAjax.i18n[key]
			: key;
	}

})();
