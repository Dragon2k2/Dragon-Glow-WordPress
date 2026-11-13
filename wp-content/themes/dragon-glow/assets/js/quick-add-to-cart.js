/**
 * Dragon Glow â€” Quick Add to Cart (optimistic toggle)
 *
 * Handles the add/remove toggle for `.dg-quick-add` buttons (Shop grid,
 * home best-sellers carousel, related products).
 *
 * â”€â”€ Why this is optimistic â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
 * The previous implementation waited for the server before showing anything:
 *
 *   click â†’ disable + "..." â†’ RTT(add) â†’ setAddedState â†’ RTT(get count)
 *
 * Two round-trips plus a visible "waiting" state â€” that is what made the
 * button feel slow. The current implementation flips the UI immediately and
 * lets the network settle in the background:
 *
 *   click â†’ paint "Added" + bump the badge        (synchronous, 0 ms)
 *         â†’ RTT(add) in the background
 *              â†’ success: reconcile against the authoritative `count` that
 *                         now travels back inside the add/remove payload
 *              â†’ failure: roll the button AND the badge back + notify
 *
 * The authoritative count is returned inside the add/remove response, so the
 * second round-trip (`dg_ajax_get_cart_count`) is no longer needed.
 *
 * Concurrency model:
 *   Every in-flight operation contributes a signed delta to the badge. The
 *   badge is only repainted from the server count once EVERY operation has
 *   settled â€” painting earlier would flash a stale number when several
 *   products are added at once.
 *
 * AJAX actions used:
 *   dg_ajax_add_to_cart              â€” adds product
 *   dg_ajax_remove_product_from_cart â€” removes by product_id or slug
 *   dg_ajax_get_cart_identifiers     â€” returns product_ids + slugs in current cart
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	/**
	 * Number of cart operations still in flight.
	 *
	 * While this is > 0 the badge keeps its optimistic value; the
	 * authoritative count is applied once the last operation settles.
	 *
	 * @type {number}
	 */
	var pendingOps = 0;

	/**
	 * True when at least two operations overlapped.
	 *
	 * Concurrent requests make the `count` returned by each response
	 * unreliable on its own: the server processes them in its own order, so
	 * the response that arrives LAST in the browser may describe an EARLIER
	 * server state. When this flag is set, the module reconciles with one
	 * background `refreshCount()` instead of trusting a single response.
	 *
	 * @type {boolean}
	 */
	var hadConcurrency = false;

	// â”€â”€ Init on DOM ready â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

	document.addEventListener('DOMContentLoaded', function () {
		bindQuickAddButtons();
		restoreCartState();
	});

	// â”€â”€ Stable identifier key per button â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

	/**
	 * Return the stable cart-lookup key for a button:
	 *   product_id > 0  â†’ numeric product_id (WooCommerce)
	 *   product_id === 0 â†’ slug (mock products)
	 *
	 * @param {HTMLElement} btn
	 * @return {string}  Identifier to use for add/remove/restore.
	 */
	function getButtonKey(btn) {
		var pid = parseInt(btn.dataset.productId, 10) || 0;
		return pid > 0 ? String(pid) : (btn.dataset.productSlug || '');
	}

	// â”€â”€ Bind buttons â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

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

	// â”€â”€ Toggle handler â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

	/**
	 * Handle a click on a quick-add button.
	 * Toggles between the "Add to Cart" and "Added" states.
	 *
	 * @param {Event} e
	 * @return {void}
	 */
	function handleToggleClick(e) {
		var btn = e.currentTarget;

		// One request per button at a time â€” a second click while the first
		// is still in flight would double-count the badge and race the two
		// server responses.
		if (btn.dataset.dgBusy === '1') {
			return;
		}

		if (btn.dataset.inCart === '1') {
			handleRemoveFromCart(btn);
		} else {
			handleAddToCart(btn);
		}
	}

	// â”€â”€ Add to cart â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

	/**
	 * Optimistic add: paint the "Added" state and bump the badge immediately,
	 * then reconcile with the server in the background.
	 *
	 * Bug fix #1 (Lá»—i 1): always sends slug + size so mock products work.
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

		// â”€â”€ Optimistic paint (synchronous, before any network call) â”€â”€
		btn.dataset.dgBusy = '1';
		setAddedState(btn);
		beginOperation(1);

		window.DGCart.add({
			productId: productId,
			slug:      slug,
			size:      size,
			quantity:  quantity,
		})
		.then(function (data) {
			btn.dataset.dgBusy = '';

			if (!data || !data.success) {
				// Server refused the add â†’ undo the optimistic paint.
				endOperation(1, false);
				resetButton(btn);
				showError(btn, (data && data.data && data.data.message)
					? data.data.message
					: gettext('Could not add to bag.'));
				return;
			}

			// Success â€” the server tells us the real count, so we can
			// reconcile without a second round-trip.
			endOperation(1, true, data.data ? data.data.count : null);
		})
		.catch(function () {
			btn.dataset.dgBusy = '';
			endOperation(1, false);
			resetButton(btn);
			showError(btn, gettext('Network error.'));
		});
	}

	// â”€â”€ Remove from cart â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

	/**
	 * Optimistic remove: flip the button back and drop the badge count right
	 * away, then reconcile with the server in the background.
	 *
	 * Bug fix #2 (Lá»—i 2): sends slug alongside product_id so mock removal works.
	 * Uses window.DGCart.remove() which POSTs both identifiers.
	 *
	 * @param {HTMLElement} btn
	 * @return {void}
	 */
	function handleRemoveFromCart(btn) {
		var productId = parseInt(btn.dataset.productId, 10) || 0;
		var slug      = btn.dataset.productSlug || '';

		// â”€â”€ Optimistic paint (synchronous, before any network call) â”€â”€
		btn.dataset.dgBusy = '1';
		resetButton(btn);
		beginOperation(-1);

		window.DGCart.remove({
			productId: productId,
			slug:      slug,
		})
		.then(function (data) {
			btn.dataset.dgBusy = '';

			if (!data || !data.success) {
				// Removal failed â€” the item is still in the cart, so put
				// the button and the badge back.
				endOperation(-1, false);
				setAddedState(btn);
				showError(btn, (data && data.data && data.data.message)
					? data.data.message
					: gettext('Could not remove item.'));
				return;
			}

			endOperation(-1, true, data.data ? data.data.count : null);
		})
		.catch(function () {
			btn.dataset.dgBusy = '';
			endOperation(-1, false);
			setAddedState(btn);
			showError(btn, gettext('Network error.'));
		});
	}

	// â”€â”€ Optimistic badge bookkeeping â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

	/**
	 * Start an optimistic operation: bump the badge immediately.
	 *
	 * @param {number} delta Signed integer â€” +1 to add, -1 to remove.
	 * @return {void}
	 */
	function beginOperation(delta) {
		if (pendingOps > 0) {
			hadConcurrency = true;
		}
		pendingOps += 1;
		bumpBadge(delta);
	}

	/**
	 * Settle an optimistic operation.
	 *
	 * @param {number}      delta  The same delta passed to beginOperation().
	 * @param {boolean}     ok     Whether the server accepted the operation.
	 * @param {number|null} count  Authoritative cart count (success only).
	 * @return {void}
	 */
	function endOperation(delta, ok, count) {
		pendingOps = Math.max(0, pendingOps - 1);

		if (!ok) {
			// Roll the badge back â€” the server never applied the change.
			bumpBadge(-delta);
		}

		if (pendingOps > 0) {
			// Still waiting for other operations â€” the badge keeps its
			// optimistic value until everything settled.
			return;
		}

		if (hadConcurrency) {
			// Overlapping requests: the per-response `count` cannot be
			// trusted (the server may have processed them in a different
			// order than the browser received them). Reconcile once with
			// the authoritative count instead.
			hadConcurrency = false;
			if (typeof window.DGCart.refreshCount === 'function') {
				window.DGCart.refreshCount();
			}
			return;
		}

		if (ok) {
			var value = parseInt(count, 10);
			if (isFinite(value) && value >= 0) {
				paintBadge(value);
			}
		}
	}

	/**
	 * Adjust the cart badge by a signed delta.
	 *
	 * @param {number} delta Signed integer.
	 * @return {void}
	 */
	function bumpBadge(delta) {
		if (!window.DGCartBadge || typeof window.DGCartBadge.bump !== 'function') {
			return;
		}
		window.DGCartBadge.bump(delta);
	}

	/**
	 * Paint an authoritative cart count onto the badge.
	 *
	 * @param {number} count
	 * @return {void}
	 */
	function paintBadge(count) {
		if (!window.DGCartBadge || typeof window.DGCartBadge.paint !== 'function') {
			return;
		}
		window.DGCartBadge.paint(count);
	}

	// â”€â”€ Page-load cart state restore â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

	/**
	 * Restore the "already in cart" state on page load.
	 *
	 * The state is rendered server-side (`data-in-cart` on the button), so
	 * there is NO extra round-trip here â€” the button already shows the
	 * correct state in the very first paint. The previous implementation
	 * fetched the cart identifiers over AJAX, which cost a full round-trip
	 * (~2s on this host) and made the buttons visibly jump from
	 * "Add to Cart" to "Added" after the page had already rendered.
	 *
	 * The AJAX path is still kept as a fallback for markup that predates
	 * the server-side attribute (e.g. cached fragments).
	 *
	 * @return {void}
	 */
	function restoreCartState() {
		var buttons = document.querySelectorAll('.dg-quick-add');
		var needsFetch = false;

		buttons.forEach(function (btn) {
			if (btn.dataset.inCart === undefined) {
				needsFetch = true;
				return;
			}
			if (btn.dataset.inCart === '1') {
				// `silent` â€” restoring the state must not replay the click
				// feedback animation.
				setAddedState(btn, true);
			}
		});

		if (!needsFetch) {
			return;
		}

		// Fallback: markup without the server-rendered attribute.
		window.DGCart.getIdentifiers()
		.then(function (data) {
			if (!data || !data.success) { return; }

			var ids   = (data.data && data.data.product_ids) || [];
			var slugs = (data.data && data.data.slugs) || [];

			buttons.forEach(function (btn) {
				var key = getButtonKey(btn);
				if (!key) { return; }

				// Check against numeric IDs (WooCommerce) or slug strings (mock)
				var isAdded = (ids.indexOf(Number(key)) !== -1) || (slugs.indexOf(key) !== -1);
				if (isAdded) {
					setAddedState(btn, true);
				}
			});
		})
		.catch(function () {
			// Silently fail â€” buttons simply keep their server-rendered state
		});
	}

	// â”€â”€ Button state helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

	/**
	 * Mark a button as permanently "Add" (product is in cart).
	 * Stores inCart flag on the element; no timeout revert.
	 *
	 * @param {HTMLElement} btn
	 * @param {boolean}     silent Skip the pop animation (state restore).
	 * @return {void}
	 */
	function setAddedState(btn, silent) {
		btn.disabled       = false;
		btn.dataset.inCart = '1';

		btn.classList.add('dg-quick-add--added');
		// One-shot pop animation â€” retriggered on every state change so
		// repeated add/remove cycles keep the feedback alive.
		if (!silent) {
			restartPopAnimation(btn);
		}

		var label = btn.querySelector('.dg-quick-add__label');
		if (label) {
			label.textContent = gettext('Added');
		}

		var icon = btn.querySelector('.material-symbols-outlined');
		if (icon) {
			// Save original icon name before overwriting.
			// Guard against capturing 'check': the server renders the
			// checkmark already for products that are in the cart, and
			// capturing it would make the reset paint a checkmark on an
			// "Add to Cart" button.
			if (!btn.dataset.savedIcon && icon.textContent.trim() !== 'check') {
				btn.dataset.savedIcon = icon.textContent;
			}
			icon.textContent = 'check';
		}

		syncAriaLabel(btn, true);
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
		restartPopAnimation(btn);

		var label = btn.querySelector('.dg-quick-add__label');
		if (label) {
			label.textContent = getOriginalLabel(btn);
		}

		var icon = btn.querySelector('.material-symbols-outlined');
		if (icon) {
			icon.textContent = btn.dataset.savedIcon || 'shopping_bag';
		}

		syncAriaLabel(btn, false);
	}

	/**
	 * Keep the button's accessible name in sync with its visible state.
	 *
	 * Some variants (home best-sellers) render the label as `sr-only` text
	 * plus an explicit `aria-label` on the button. Updating only the visible
	 * label would leave screen readers announcing the stale "Add to cart".
	 *
	 * @param {HTMLElement} btn
	 * @param {boolean}     isAdded
	 * @return {void}
	 */
	function syncAriaLabel(btn, isAdded) {
		var label = btn.querySelector('.dg-quick-add__label');
		if (!label || !label.classList.contains('sr-only')) {
			// The visible label IS the accessible name â€” nothing to sync.
			return;
		}
		btn.setAttribute('aria-label', isAdded ? gettext('Added') : getOriginalLabel(btn));
	}

	/**
	 * Restart the one-shot pop animation on a button.
	 *
	 * The class is removed, a reflow is forced, then it is re-added so the
	 * animation replays on every state change instead of only the first one.
	 *
	 * @param {HTMLElement} btn
	 * @return {void}
	 */
	function restartPopAnimation(btn) {
		btn.classList.remove('dg-quick-add--pop');
		// Force reflow â€” reading offsetWidth is the cheapest reliable way.
		// eslint-disable-next-line no-unused-expressions
		btn.offsetWidth;
		btn.classList.add('dg-quick-add--pop');
	}

	// â”€â”€ Utility helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

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
	 * Report an error to the user.
	 *
	 * Prefers the inline notice element when the template renders one;
	 * otherwise falls back to the site-wide toast system (`window.DGToast`)
	 * so the message is visible wherever the button sits in the layout.
	 *
	 * @param {HTMLElement} btn
	 * @param {string}     message
	 * @return {void}
	 */
	function showError(btn, message) {
		var wrap   = btn.closest('.dg-product-image');
		var notice = wrap ? wrap.querySelector('.dg-quick-add-notice') : null;

		if (notice) {
			notice.textContent = message;
			notice.classList.remove('hidden');

			setTimeout(function () {
				notice.classList.add('hidden');
			}, 3000);
			return;
		}

		if (window.DGToast && typeof window.DGToast.show === 'function') {
			window.DGToast.show({ tone: 'error', text: message });
		}
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
