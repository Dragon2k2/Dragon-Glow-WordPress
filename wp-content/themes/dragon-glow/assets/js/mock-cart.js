/**
 * Dragon Glow — Mock Cart Page Handler
 *
 * Binds remove and quantity-stepper interactions on the Mock Cart page
 * (template-mock-cart.php) to the dg_ajax_remove_from_mock_cart and
 * dg_ajax_update_mock_cart AJAX actions.
 *
 * Does NOT handle WooCommerce cart interactions — those live in cart.js.
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', init);

	function init() {
		bindRemoveButtons();
		bindQtySteppers();
	}

	// ── Remove buttons ──────────────────────────────────────────────────────────

	function bindRemoveButtons() {
		var buttons = document.querySelectorAll('.dg-mock-remove');
		buttons.forEach(function (btn) {
			btn.addEventListener('click', handleRemoveClick);
		});
	}

	function handleRemoveClick(e) {
		var btn = e.currentTarget;
		var itemKey = btn.dataset.itemKey;

		if (!itemKey) return;

		var row = btn.closest('.dg-cart-row');
		if (row) {
			row.classList.add('is-removing');
		}

		var formData = new FormData();
		formData.append('action', 'dg_ajax_remove_from_mock_cart');
		formData.append('nonce', getNonce());
		formData.append('item_key', itemKey);

		fetch(getAjaxUrl(), {
			method: 'POST',
			body: formData,
			credentials: 'same-origin'
		})
		.then(function (r) { return r.json(); })
		.then(function (data) {
			if (data.success) {
				if (data.data && 0 === data.data.count) {
					// Cart is now empty — reload to show empty state.
					window.location.reload();
				} else {
					// Remove the row from DOM (it already has the fade animation).
					if (row) {
						setTimeout(function () {
							row.remove();
							updateOrderSummary();
						}, 420);
					}
				}
			} else {
				if (row) {
					row.classList.remove('is-removing');
				}
				var msg = (data.data && data.data.message)
					? data.data.message
					: gettext('Could not remove item.');
				showNotice(msg, 'error');
			}
		})
		.catch(function () {
			if (row) {
				row.classList.remove('is-removing');
			}
			showNotice(gettext('Network error.'), 'error');
		});
	}

	// ── Quantity steppers ───────────────────────────────────────────────────────

	function bindQtySteppers() {
		var minusBtns = document.querySelectorAll('.dg-mock-qty-minus');
		var plusBtns = document.querySelectorAll('.dg-mock-qty-plus');

		minusBtns.forEach(function (btn) {
			btn.addEventListener('click', handleQtyMinus);
		});

		plusBtns.forEach(function (btn) {
			btn.addEventListener('click', handleQtyPlus);
		});
	}

	function handleQtyMinus(e) {
		var btn = e.currentTarget;
		var itemKey = btn.dataset.itemKey;
		var row = btn.closest('.dg-cart-row');
		var display = row ? row.querySelector('.dg-qty-value') : null;
		var currentQty = display ? parseInt(display.textContent, 10) || 1 : 1;
		var newQty = Math.max(1, currentQty - 1);
		updateMockQty(itemKey, newQty, display);
	}

	function handleQtyPlus(e) {
		var btn = e.currentTarget;
		var itemKey = btn.dataset.itemKey;
		var row = btn.closest('.dg-cart-row');
		var display = row ? row.querySelector('.dg-qty-value') : null;
		var currentQty = display ? parseInt(display.textContent, 10) || 1 : 1;
		var newQty = currentQty + 1;
		updateMockQty(itemKey, newQty, display);
	}

	function updateMockQty(itemKey, quantity, display) {
		if (!itemKey) return;

		if (display) {
			display.textContent = quantity;
		}

		var formData = new FormData();
		formData.append('action', 'dg_ajax_update_mock_cart');
		formData.append('nonce', getNonce());
		formData.append('item_key', itemKey);
		formData.append('quantity', quantity);

		fetch(getAjaxUrl(), {
			method: 'POST',
			body: formData,
			credentials: 'same-origin'
		})
		.then(function (r) { return r.json(); })
		.then(function (data) {
			if (data.success) {
				if (data.data && 0 === data.data.count) {
					window.location.reload();
				} else {
					updateOrderSummary();
				}
			} else {
				var msg = (data.data && data.data.message)
					? data.data.message
					: gettext('Could not update quantity.');
				showNotice(msg, 'error');
			}
		})
		.catch(function () {
			showNotice(gettext('Network error.'), 'error');
		});
	}

	// ── Order summary ───────────────────────────────────────────────────────────

	/**
	 * Recalculate and refresh the order summary totals in the sidebar
	 * after a quantity change or item removal.
	 */
	function updateOrderSummary() {
		var rows = document.querySelectorAll('.dg-cart-row');
		var subtotal = 0;

		rows.forEach(function (row) {
			var qtyEl = row.querySelector('.dg-qty-value');
			var qty = qtyEl ? parseInt(qtyEl.textContent, 10) || 1 : 1;
			// Each row has its price element keyed by item key.
			var itemKey = row.dataset.dgItemKey;
			var priceEl = itemKey
				? document.getElementById('dg-mock-price-' + itemKey.replace(/[^a-zA-Z0-9_-]/g, '_'))
				: row.querySelector('.font-bold.text-primary.text-right');
			var priceText = priceEl ? priceEl.textContent.trim() : '';
			var price = parseFloat(priceText.replace(/[^0-9.]/g, '')) || 0;
			subtotal += price * qty;
		});

		var summarySubtotal = document.getElementById('dg-mock-subtotal');
		var summaryTotal = document.getElementById('dg-mock-total');
		if (summarySubtotal) {
			summarySubtotal.textContent = formatPrice(subtotal);
		}
		if (summaryTotal) {
			summaryTotal.textContent = formatPrice(subtotal);
		}
	}

	// ── Helpers ─────────────────────────────────────────────────────────────────

	function getAjaxUrl() {
		return (window.dgAjax && window.dgAjax.url) ? window.dgAjax.url : '/wp-admin/admin-ajax.php';
	}

	function getNonce() {
		return (window.dgAjax && window.dgAjax.nonce) ? window.dgAjax.nonce : '';
	}

	function gettext(key) {
		return (window.dgAjax && window.dgAjax.i18n && window.dgAjax.i18n[key])
			? window.dgAjax.i18n[key]
			: key;
	}

	/**
	 * Show a transient notice banner at the top of the cart content area.
	 *
	 * @param {string} message
	 * @param {string} type  'error' | 'success'
	 */
	function showNotice(message, type) {
		var container = document.querySelector('.dg-cart-row') || document.querySelector('main');
		if (!container) return;

		var notice = document.createElement('div');
		var isError = type === 'error';
		notice.className = 'mb-6 p-4 rounded-xl text-sm ' + (
			isError
				? 'bg-error-container text-on-error-container border border-error/20'
				: 'bg-primary-container/20 text-primary border border-primary/20'
		);
		notice.setAttribute('role', 'alert');
		notice.textContent = message;

		var existing = container.parentElement.querySelector('.dg-notice-banner');
		if (existing) existing.remove();

		notice.classList.add('dg-notice-banner');
		container.parentElement.insertBefore(notice, container);

		setTimeout(function () {
			notice.remove();
		}, 4000);
	}

	/**
	 * Format a number as a price string (simple fallback when wc_price unavailable).
	 *
	 * @param {number} amount
	 * @return {string}
	 */
	function formatPrice(amount) {
		// Attempt to use WooCommerce formatting if available.
		if (window.dgAjax && window.dgAjax._wcPriceFormat) {
			return window.dgAjax._wcPrice_format(amount);
		}
		return '$' + amount.toFixed(2);
	}

})();
