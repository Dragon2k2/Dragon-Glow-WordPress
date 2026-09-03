/**
 * Dragon Glow — Wishlist Toggle (lib)
 * Shared heart button handler used everywhere a product can be added to the
 * wishlist (Shop grid, single product, related products, wishlist page).
 *
 * Listens for clicks on `.dg-wishlist-toggle` and POSTs to `dg_wishlist_toggle`.
 *
 * Click feel — **always responsive, never blocked**:
 *   1. The heart toggles its `is-active` state synchronously on every click —
 *      no waiting on the network round-trip. Same for the header badge
 *      (`computeOptimisticCount()` / `applyBadgeCount()`).
 *   2. Clicks within a 350 ms burst are coalesced. Only the *last* click in
 *      the burst schedules an actual AJAX call. If that final burst has an
 *      odd click count the state genuinely changed (toggle), so we sync. If
 *      it's even the user clicked back to where they started — no-op, no
 *      server round-trip.
 *   3. When the sync response lands we tag-check: if a newer sync has been
 *      scheduled in the meantime, the stale response is discarded. This
 *      protects against an out-of-order response flipping the UI back to
 *      a value the user has already moved past.
 *   4. On error or network failure we roll back to the burst's recorded
 *      initial state and re-sync the header badge from the server.
 *
 * Heart sits at z-index 20 above `.dg-product-stretched-link` (z-index 1),
 * so clicks never fall through to the card link — see woocommerce.css.
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	// Coalesce rapid clicks into a single AJAX sync. Tuned for "feels
	// instant" on a human double-click / triple-click burst while still
	// short enough that a deliberate pause + click feels responsive.
	const SYNC_DELAY_MS = 350;

	document.addEventListener('click', function (e) {
		const btn = e.target.closest('.dg-wishlist-toggle');
		if (!btn) return;

		e.preventDefault();
		e.stopPropagation();

		// If the parent card is already handling removal (wishlist page),
		// we skip and let that handler run.
		if (btn.hasAttribute('data-dg-wl-remove')) {
			return;
		}

		const productId = parseInt(btn.dataset.productId || '0', 10);
		if (!productId) return;

		onHeartClick(btn, productId);
	});

	/**
	 * Handle a heart click. Toggles UI synchronously and (debounced)
	 * schedules a server sync.
	 *
	 * @param {HTMLElement} btn        The .dg-wishlist-toggle button.
	 * @param {number}      productId  Numeric WP product ID.
	 */
	function onHeartClick(btn, productId) {
		// First click of a rapid burst — capture the initial state so we
		// know what to roll back to on error, and start counting.
		if (btn._dgBurst === undefined) {
			btn._dgBurst = {
				count: 0,
				initialActive: btn.classList.contains('is-active'),
			};
		}
		btn._dgBurst.count++;

		// Visual feedback (in-flight). Always set on click so the dim
		// is visible immediately; cleared on resolve/reject.
		btn.classList.add('is-busy');

		// Toggle UI synchronously.
		const willBeActive = !btn.classList.contains('is-active');
		btn.classList.toggle('is-active', willBeActive);

		// Optimistic badge update — paint the new count synchronously so
		// the header reflects the toggle immediately, without waiting for
		// the network round-trip.
		const optimisticCount = computeOptimisticCount(willBeActive);
		if (optimisticCount !== null) {
			applyBadgeCount(optimisticCount);
		}

		// Debounce: only the *last* click in a rapid burst schedules a
		// sync. If that final count is odd the user ended on a different
		// state than they started (toggle); if even they're back where
		// they started (no-op, no round-trip needed).
		clearTimeout(btn._dgTimer);
		btn._dgTimer = setTimeout(function () {
			const burst = btn._dgBurst;
			delete btn._dgBurst;
			btn._dgTimer = null;

			if (burst.count % 2 === 1) {
				sendSync(btn, productId, burst.initialActive);
			} else {
				// No real state change — clear the busy flag and stop.
				btn.classList.remove('is-busy');
			}
		}, SYNC_DELAY_MS);
	}

	/**
	 * Send the actual AJAX sync. Tag the request so a stale response
	 * from a superseded sync can't overwrite the UI.
	 *
	 * @param {HTMLElement} btn            The .dg-wishlist-toggle button.
	 * @param {number}      productId      Numeric WP product ID.
	 * @param {boolean}     initialActive  Pre-burst `is-active` state,
	 *                                     used to roll back on error.
	 */
	function sendSync(btn, productId, initialActive) {
		const myReqId = (btn._dgReqId || 0) + 1;
		btn._dgReqId = myReqId;

		const fd = new FormData();
		fd.append('action', 'dg_wishlist_toggle');
		fd.append('nonce', (window.dgAjax && window.dgAjax.nonce) || '');
		fd.append('product_id', productId);

		fetch((window.dgAjax && window.dgAjax.url) || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			body: fd,
			credentials: 'same-origin',
		})
			.then(function (r) { return r.json(); })
			.then(function (data) {
				// Stale response — a newer sync has been scheduled since
				// this one fired. Discard so it can't flip the UI back to
				// a value the user has already moved past.
				if (btn._dgReqId !== myReqId) return;

				btn.classList.remove('is-busy');

				if (!data.success) {
					btn.classList.toggle('is-active', initialActive);
					if (data.data && typeof data.data.count === 'number') {
						applyBadgeCount(data.data.count);
					} else if (data.data && data.data.redirect) {
						window.location.href = data.data.redirect;
						return;
					} else {
						fetchHeaderBadge();
					}
					console.warn('[DG Wishlist] toggle failed:', data.data);
					return;
				}

				btn.classList.toggle('is-active', !!data.data.added);

				// Pop animation if we just added.
				if (data.data.added) {
					const icon = btn.querySelector('.material-symbols-outlined');
					if (icon && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
						icon.animate(
							[
								{ transform: 'scale(1)' },
								{ transform: 'scale(1.35)', offset: 0.4 },
								{ transform: 'scale(1)' },
							],
							{ duration: 450, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' }
						);
					}
				}

				// Reconcile with the server's authoritative count. Paint
				// silently — the user already saw the optimistic update,
				// firing the badge pulse again would be visual noise.
				if (data.data && typeof data.data.count === 'number') {
					paintBadgeCount(data.data.count);
					if (window.DGWishlist && typeof window.DGWishlist.onCountChange === 'function') {
						window.DGWishlist.onCountChange(data.data.count);
					}
					return;
				}

				// Slower path: defer to page-local module if it exposes one.
				if (window.DGWishlist && typeof window.DGWishlist.refreshCount === 'function') {
					window.DGWishlist.refreshCount();
					return;
				}

				// Last resort: separate count fetch (only when toggle
				// response somehow omitted the count — defensive).
				fetchHeaderBadge();
			})
			.catch(function () {
				if (btn._dgReqId !== myReqId) return;
				btn.classList.remove('is-busy');
				btn.classList.toggle('is-active', initialActive);
				fetchHeaderBadge();
			});
	}

	/**
	 * Derive the new badge count locally from the current badge value.
	 *
	 * Returns null if we have no baseline to work from — in that case the
	 * caller should fall back to the server response and skip the optimistic
	 * update entirely (we won't fake a count we have no basis for).
	 *
	 * Mirrors `dg_get_wishlist_count()` semantics: count of saved items,
	 * 0 = empty, hidden when 0.
	 *
	 * @param {boolean} willBeAdded  true = user just added this product.
	 * @return {number|null}         New count, or null if no baseline.
	 */
	function computeOptimisticCount(willBeAdded) {
		const badges = document.querySelectorAll('.dg-wishlist-count');
		if (!badges.length) return null;

		// Read from the first badge — there should only ever be one in the
		// page (the header nav), but the helper was built to handle many in
		// case a mobile drawer or sticky variant lands in the future.
		const el = badges[0];
		const wasHidden = el.classList.contains('hidden');
		const baseline = parseInt(el.textContent || '0', 10) || 0;

		// If the baseline badge was hidden we treated it as "0 saved".
		// From there, adding bumps to 1, removing stays at 0.
		const effectiveBaseline = wasHidden ? 0 : baseline;
		return willBeAdded ? effectiveBaseline + 1 : Math.max(0, effectiveBaseline - 1);
	}

	/**
	 * Paint the badge value AND trigger the change-detection animation.
	 *
	 * @param {number} count Authoritative count from the server.
	 */
	function applyBadgeCount(count) {
		const n = Number(count) || 0;
		document.querySelectorAll('.dg-wishlist-count').forEach(function (el) {
			const wasHidden = el.classList.contains('hidden');
			const prevCount = el.textContent;

			el.textContent = String(n);
			const willShow = n > 0;
			el.classList.toggle('hidden', !willShow);

			const valueChanged = prevCount !== String(n);
			if (!willShow || !valueChanged) {
				return;
			}

			if (wasHidden && willShow) {
				restartAnimation(el, 'is-first-show');
			} else {
				restartAnimation(el, 'is-pulse');
			}
		});
	}

	/**
	 * Paint the badge value WITHOUT triggering the change-detection
	 * animation. Used for reconciliation: the user already saw the updated
	 * badge when we painted optimistically; firing the animation a second
	 * time on server confirm would be visual noise.
	 */
	function paintBadgeCount(count) {
		const n = Number(count) || 0;
		document.querySelectorAll('.dg-wishlist-count').forEach(function (el) {
			el.textContent = String(n);
			el.classList.toggle('hidden', n === 0);
		});
	}

	/**
	 * Remove the animation class, force a reflow, then re-add it so the
	 * CSS animation restarts even on rapid repeat clicks.
	 */
	function restartAnimation(el, className) {
		el.classList.remove(className);
		// Force reflow — reading offsetWidth is the cheapest reliable way.
		// eslint-disable-next-line no-unused-expressions
		el.offsetWidth;
		el.classList.add(className);
		// Auto-clean so the class doesn't stay on indefinitely (helps
		// future animation triggers + keeps DOM tidy).
		setTimeout(function () { el.classList.remove(className); }, 750);
	}

	/**
	 * Defensive fallback: separate fetch of the wishlist count. Only runs
	 * when the toggle response did not include a `count` field.
	 */
	function fetchHeaderBadge() {
		if (!window.dgAjax) return;
		const fd = new FormData();
		fd.append('action', 'dg_wishlist_count');
		fd.append('nonce', window.dgAjax.nonce);
		fetch(window.dgAjax.url, {
			method: 'POST',
			body: fd,
			credentials: 'same-origin',
		})
			.then(function (r) { return r.json(); })
			.then(function (data) {
				if (!data || !data.success) return;
				applyBadgeCount((data.data && data.data.count) || 0);
			})
			.catch(function () { /* silent */ });
	}
})();
