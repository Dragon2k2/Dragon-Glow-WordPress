/**
 * Dragon Glow — Quick Add to Cart (Toggle)
 *
 * Handles add/remove toggle for .dg-quick-add buttons on the Shop grid.
 *
 * Behaviour:
 *   - First click  → AJAX add to cart → button stays in "Add" state permanently.
 *   - Second click → AJAX remove from cart → button reverts to "Add to Cart".
 *   - On DOMContentLoaded, fetches current cart identifiers from the server
 *     and pre-marks buttons for products already in the cart (supports both
 *     WooCommerce products and mock products).
 *
 * AJAX actions used:
 *   dg_ajax_add_to_cart              — adds product
 *   dg_ajax_remove_product_from_cart — removes by product_id or slug
 *   dg_ajax_get_cart_identifiers     — returns product_ids + slugs in current cart
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	// ── Init on DOM ready ─────────────────────────────────────────────────────

	document.addEventListener('DOMContentLoaded', function () {
		bindQuickAddButtons();
		restoreCartState();
	});

	// ── Stable identifier key per button ──────────────────────────────────────

	/**
	 * Return the stable cart-lookup key for a button:
	 *   product_id > 0  → numeric product_id (WooCommerce)
	 *   product_id === 0 → slug (mock products)
	 *
	 * @param {HTMLElement} btn
	 * @return {string}  Identifier to use for add/remove/restore.
	 */
	function getButtonKey(btn) {
		var pid = parseInt(btn.dataset.productId, 10) || 0;
		return pid > 0 ? String(pid) : (btn.dataset.productSlug || '');
	}

	// ── Bind buttons ──────────────────────────────────────────────────────────

	/**
	 * Attach click handlers to all .dg-quick-add buttons on the page.
	 *
	 * @return {void}
	 */
	function bindQuickAddButtons() {
		document.querySelectorAll('.dg-quick-add').forEach(function (btn) {
			btn.addEventListener('click', handleToggleClick);
		});
	}

	// ── Toggle handler ────────────────────────────────────────────────────────

	/**
	 * Handle a click on a quick-add button.
	 * Toggles between "Add to Cart" and "Add" states.
	 *
	 * @param {Event} e
	 * @return {void}
	 */
	function handleToggleClick(e) {
		var btn = e.currentTarget;

		// If product is already in cart → remove it
		if (btn.dataset.inCart === '1') {
			handleRemoveFromCart(btn);
		} else {
			handleAddToCart(btn);
		}
	}

	// ── Add to cart ───────────────────────────────────────────────────────────

	/**
	 * AJAX: add the product to cart, then permanently mark button as "Added".
	 *
	 * Bug fix #1 (Lỗi 1): always sends slug + size so mock products work.
	 * Uses window.DGCart.add() which POSTs product_id, slug, size, quantity.
	 *
	 * @param {HTMLElement} btn
	 * @return {void}
	 */
	function handleAddToCart(btn) {
		var productId = parseInt(btn.dataset.productId, 10) || 0;
		var slug      = btn.dataset.productSlug || '';
		var size      = btn.dataset.productSize || '';
		var quantity  = parseInt(btn.dataset.quantity, 10) || 1;

		setButtonLoading(btn, true);

		window.DGCart.add({
			productId: productId,
			slug:      slug,
			size:      size,
			quantity:  quantity,
		})
		.then(function (data) {
			if (data.success) {
				setAddedState(btn);
				window.DGCart.refreshCount();
			} else {
				var msg = (data.data && data.data.message)
					? data.data.message
					: gettext('Could not add to bag.');
				showInlineNotice(btn, msg);
				resetButton(btn);
			}
		})
		.catch(function () {
			showInlineNotice(btn, gettext('Network error.'));
			resetButton(btn);
		});
	}

	// ── Remove from cart ──────────────────────────────────────────────────────

	/**
	 * AJAX: remove the product from cart by product_id or slug, then reset button.
	 *
	 * Bug fix #2 (Lỗi 2): sends slug alongside product_id so mock removal works.
	 * Uses window.DGCart.remove() which POSTs both identifiers.
	 *
	 * @param {HTMLElement} btn
	 * @return {void}
	 */
	function handleRemoveFromCart(btn) {
		var productId = parseInt(btn.dataset.productId, 10) || 0;
		var slug      = btn.dataset.productSlug || '';

		setButtonLoading(btn, true);

		window.DGCart.remove({
			productId: productId,
			slug:      slug,
		})
		.then(function (data) {
			if (data.success) {
				resetButton(btn);
				window.DGCart.refreshCount();
			} else {
				// Remove failed — restore added state (item is still in cart)
				setAddedState(btn);
				var msg = (data.data && data.data.message)
					? data.data.message
					: gettext('Could not remove item.');
				showInlineNotice(btn, msg);
			}
		})
		.catch(function () {
			setAddedState(btn);
			showInlineNotice(btn, gettext('Network error.'));
		});
	}

	// ── Page-load cart state restore ──────────────────────────────────────────

	/**
	 * On page load, fetch which products are in the cart and mark their buttons.
	 * This handles hard refreshes and direct URL visits to the shop page.
	 *
	 * Bug fix #3 (Lỗi 3): uses dg_ajax_get_cart_identifiers which returns both
	 * product_ids (WooCommerce) and slugs (mock). A button is marked "Added" if
	 * its key matches either list.
	 *
	 * @return {void}
	 */
	function restoreCartState() {
		window.DGCart.getIdentifiers()
		.then(function (data) {
			if (!data || !data.success) { return; }

			var ids  = (data.data && data.data.product_ids) || [];
			var slugs = (data.data && data.data.slugs) || [];

			document.querySelectorAll('.dg-quick-add').forEach(function (btn) {
				var key = getButtonKey(btn);
				if (!key) { return; }

				// Check against numeric IDs (WooCommerce) or slug strings (mock)
				var isAdded = (ids.indexOf(Number(key)) !== -1) || (slugs.indexOf(key) !== -1);
				if (isAdded) {
					setAddedState(btn);
				}
			});
		})
		.catch(function () {
			// Silently fail — buttons simply start in "Add to Cart" state
		});
	}

	// ── Button state helpers ──────────────────────────────────────────────────

	/**
	 * Mark a button as permanently "Add" (product is in cart).
	 * Stores inCart flag on the element; no timeout revert.
	 *
	 * @param {HTMLElement} btn
	 * @return {void}
	 */
	function setAddedState(btn) {
		btn.disabled          = false;
		btn.dataset.inCart    = '1';

		btn.classList.add('dg-quick-add--added');

		var label = btn.querySelector('.dg-quick-add__label');
		if (label) {
			label.textContent = gettext('Added');
		}

		var icon = btn.querySelector('.material-symbols-outlined');
		if (icon) {
			// Save original icon name before overwriting
			if (!btn.dataset.savedIcon) {
				btn.dataset.savedIcon = icon.textContent;
			}
			icon.textContent = 'check';
		}
	}

	/**
	 * Reset a button to its idle "Add to Cart" state.
	 *
	 * @param {HTMLElement} btn
	 * @return {void}
	 */
	function resetButton(btn) {
		btn.disabled       = false;
		btn.dataset.inCart = '0';

		btn.classList.remove('dg-quick-add--added');

		var label = btn.querySelector('.dg-quick-add__label');
		if (label) {
			label.textContent = getOriginalLabel(btn);
		}

		var icon = btn.querySelector('.material-symbols-outlined');
		if (icon) {
			icon.textContent = btn.dataset.savedIcon || 'shopping_bag';
		}
	}

	/**
	 * Set loading state on a button (disables it and shows "...").
	 * Only the label span is modified — the icon is untouched during loading.
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

	// ── Utility helpers ─────────────────────────────────────────────────────

	/**
	 * Return the original button label stored at render time in data-original-label.
	 *
	 * @param {HTMLElement} btn
	 * @return {string}
	 */
	function getOriginalLabel(btn) {
		return btn.dataset.originalLabel || '';
	}

	/**
	 * Show a brief inline error notice below the button.
	 *
	 * @param {HTMLElement} btn
	 * @param {string}     message
	 * @return {void}
	 */
	function showInlineNotice(btn, message) {
		var wrap   = btn.closest('.dg-product-image');
		var notice = wrap ? wrap.querySelector('.dg-quick-add-notice') : null;
		if (!notice) { return; }

		notice.textContent = message;
		notice.classList.remove('hidden');

		setTimeout(function () {
			if (notice) { notice.classList.add('hidden'); }
		}, 3000);
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
