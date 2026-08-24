/**
 * Dragon Glow — Buy Now Handler
 *
 * Unified frontend handler for Buy Now buttons on both mock product pages
 * and WooCommerce product pages.
 *
 * Responsibilities:
 *   - Collect product_id / slug, selected size, and quantity.
 *   - Send a single AJAX request to dg_ajax_buy_now.
 *   - Handle redirects and error messages — the backend decides the path.
 *
 * No branching logic lives here; the backend (DG_Checkout_Router) decides
 * whether to redirect to WC checkout or the internal mock checkout page.
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	// ── Shared state (used across multiple functions) ──────────────────────────

	var qty = 1;

	// ── Init on DOM ready ─────────────────────────────────────────────────────
	document.addEventListener('DOMContentLoaded', init);

	function init() {
		bindQuantityStepper();
		bindBuyNowButtons();
		bindAddToBagButtons();
	}

	// ── Quantity stepper (shared with product-detail.php inline JS) ────────────
	function bindQuantityStepper() {
		var minusBtn = document.getElementById('dg-qty-minus');
		var plusBtn  = document.getElementById('dg-qty-plus');
		var display = document.getElementById('dg-qty-display');

		if (minusBtn) {
			minusBtn.addEventListener('click', function () {
				if (qty > 1) {
					qty--;
					if (display) { display.textContent = qty; }
				}
			});
		}

		if (plusBtn) {
			plusBtn.addEventListener('click', function () {
				qty++;
				if (display) { display.textContent = qty; }
			});
		}
	}

	// ── Buy Now buttons ──────────────────────────────────────────────────────
	function bindBuyNowButtons() {
		var buttons = document.querySelectorAll('[data-buy-now]');
		buttons.forEach(function (btn) {
			btn.addEventListener('click', handleBuyNowClick);
		});
	}

	function handleBuyNowClick(e) {
		var btn = e.currentTarget;

		// Collect product identifiers.
		var productId = parseInt(btn.dataset.productId, 10) || 0;
		var slug      = btn.dataset.productSlug || '';
		var size      = getSelectedSize();
		var quantity  = getQuantity();

		// Disable + show loading.
		setBuyNowLoading(btn, true);

		var formData = new FormData();
		formData.append('action', 'dg_ajax_buy_now');
		formData.append('nonce', getNonce());
		formData.append('product_id', productId);
		formData.append('slug', slug);
		formData.append('size', size);
		formData.append('quantity', quantity);

		fetch(getAjaxUrl(), {
			method: 'POST',
			body: formData,
			credentials: 'same-origin'
		})
		.then(function (r) { return r.json(); })
		.then(function (data) {
			if (data.success) {
				// Backend returned success — follow redirect.
				var redirectUrl = data.data && data.data.redirect;
				if (redirectUrl) {
					// Brief delay for mock checkout to persist the transient.
					window.location.href = redirectUrl;
				} else {
					// No redirect but success — reload to stay on page.
					window.location.reload();
				}
			} else {
				// Error — re-enable button and show friendly message.
				setBuyNowLoading(btn, false);
				var msg = (data.data && data.data.message)
					? data.data.message
					: gettext('Something went wrong. Please try again.');
				showBuyNowNotice(btn, msg, 'error');

				// If backend returned a redirect (e.g. "please select a size"), follow it.
				if (data.data && data.data.redirect) {
					window.location.href = data.data.redirect;
				}
			}
		})
		.catch(function () {
			setBuyNowLoading(btn, false);
			showBuyNowNotice(btn, gettext('Network error. Please check your connection and try again.'), 'error');
		});
	}

	// ── Add to Bag buttons (Quick Add / Add to Bag on mock product pages) ─────
	function bindAddToBagButtons() {
		var buttons = document.querySelectorAll('[data-add-to-bag]');
		buttons.forEach(function (btn) {
			btn.addEventListener('click', handleAddToBagClick);
		});
	}

	/**
	 * Add to Bag for mock products (slug-only) or WC products (product_id).
	 *
	 * Uses window.DGCart.add() which POSTs to dg_ajax_add_to_cart silently —
	 * no checkout redirect.  On success, redirects to the cart page if the
	 * backend returned a redirect URL.
	 *
	 * Icon preservation: the button contains a material-symbols span (always the first
	 * child) and a label text node (second child when present, or part of textContent
	 * when no span wrapper exists).  We write only to the label — never touch the icon
	 * span or call btn.textContent which would destroy the entire DOM subtree.
	 *
	 * @param {Event} e
	 */
	function handleAddToBagClick(e) {
		var btn = e.currentTarget;

		var productId = parseInt(btn.dataset.productId, 10) || 0;
		var slug      = btn.dataset.productSlug || '';
		var size      = getSelectedSize();
		var quantity  = getQuantity();

		// Capture original label text — prefer the dedicated label element when present,
		// otherwise fall back to the second child (icon-span + text-node markup).
		var labelEl = btn.querySelector('.dg-quick-add__label');
		if (!labelEl && btn.children.length > 1) {
			labelEl = btn.children[1];
		}
		var origText = labelEl
			? labelEl.textContent.trim()
			: btn.textContent.trim();

		btn.disabled = true;
		setLabel(btn, '...');

		window.DGCart.add({
			productId: productId,
			slug:      slug,
			size:      size,
			quantity:  quantity,
		})
		.then(function (data) {
			if (data.success) {
				var redirectUrl = (data.data && data.data.redirect)
					? data.data.redirect
					: '';
				if (redirectUrl) {
					window.location.href = redirectUrl;
				} else {
					var msg = gettext('Could not add to bag.');
					showBuyNowNotice(btn, msg, 'error');
					setLabel(btn, origText);
					btn.disabled = false;
				}
			} else {
				var msg = (data.data && data.data.message)
					? data.data.message
					: gettext('Could not add to bag.');
				showBuyNowNotice(btn, msg, 'error');
				setLabel(btn, origText);
				btn.disabled = false;
			}
		})
		.catch(function () {
			setLabel(btn, origText);
			btn.disabled = false;
			showBuyNowNotice(btn, gettext('Network error.'), 'error');
		});
	}

	/**
	 * Write text only to the label element inside a button — never overwrite the
	 * icon span or call btn.textContent which would nuke all child nodes.
	 *
	 * @param {HTMLElement} btn
	 * @param {string}     text
	 * @return {void}
	 */
	function setLabel(btn, text) {
		var labelEl = btn.querySelector('.dg-quick-add__label');
		if (!labelEl && btn.children.length > 1) {
			labelEl = btn.children[1];
		}
		if (labelEl) {
			labelEl.textContent = text;
		}
	}

	// ── Helpers ──────────────────────────────────────────────────────────────

	/**
	 * Get the currently selected size from .dg-size-btn.is-active elements.
	 *
	 * @return {string}
	 */
	function getSelectedSize() {
		var activeSize = document.querySelector('.dg-size-btn.is-active');
		return activeSize ? activeSize.textContent.trim() : '';
	}

	/**
	 * Get the current quantity from the qty display.
	 *
	 * @return {number}
	 */
	function getQuantity() {
		var display = document.getElementById('dg-qty-display');
		return parseInt(display ? display.textContent : '1', 10) || 1;
	}

	/**
	 * Set or clear the loading state on a Buy Now button.
	 *
	 * @param {HTMLElement} btn
	 * @param {boolean}    loading
	 */
	function setBuyNowLoading(btn, loading) {
		var icon  = btn.querySelector('.dg-buy-now-icon');
		var label = btn.querySelector('.dg-buy-now-label');

		btn.disabled = loading;

		if (loading) {
			if (icon) {
				icon.textContent = 'sync';
				icon.classList.add('animate-spin');
			}
			if (label) {
				label.textContent = gettext('Processing...');
			}
		} else {
			if (icon) {
				icon.textContent = 'shopping_cart_checkout';
				icon.classList.remove('animate-spin');
			}
			if (label) {
				label.textContent = gettext('Buy Now');
			}
		}
	}

	/**
	 * Show a notice message near the Buy Now button.
	 *
	 * @param {HTMLElement} btn
	 * @param {string}     message
	 * @param {string}     type   'error' | 'success'
	 */
	function showBuyNowNotice(btn, message, type) {
		var noticeEl = document.getElementById('dg-buy-now-notice');
		if (!noticeEl) return;

		var isError = type === 'error';
		noticeEl.textContent = message;
		noticeEl.className = 'mt-3 px-4 py-3 rounded-xl text-sm ' + (
			isError
				? 'bg-error-container/20 text-error border border-error/20'
				: 'bg-primary-container/20 text-primary border border-primary/20'
		);
		noticeEl.classList.remove('hidden');
	}

	/**
	 * Get AJAX URL from dgAjax global.
	 *
	 * @return {string}
	 */
	function getAjaxUrl() {
		return (window.dgAjax && window.dgAjax.url) ? window.dgAjax.url : '/wp-admin/admin-ajax.php';
	}

	/**
	 * Get nonce from dgAjax global.
	 *
	 * @return {string}
	 */
	function getNonce() {
		return (window.dgAjax && window.dgAjax.nonce) ? window.dgAjax.nonce : '';
	}

	/**
	 * Get a translated string from dgAjax globals.
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
