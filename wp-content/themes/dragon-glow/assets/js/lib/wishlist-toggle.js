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
 *   5. **Global request queue**: when clicking multiple items rapidly,
 *      requests are queued and processed one-by-one to prevent race conditions
 *      where out-of-order responses flip UI state incorrectly.
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

	// Global request queue: only 1 product sync at a time to prevent
	// race conditions when clicking multiple items rapidly.
	let pendingRequest = false;
	const requestQueue = [];

	// Global sequence counter: monotonically increasing ID for all requests
	// across all products. Used to discard stale responses when user clicks
	// multiple different products rapidly. Each button also has its own
	// _dgReqId for per-button deduplication.
	let globalSequence = 0;

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
			const badges = document.querySelectorAll('.dg-wishlist-count');
			const baselineCount = badges.length > 0
				? (badges[0].classList.contains('hidden') ? 0 : parseInt(badges[0].textContent || '0', 10) || 0)
				: null;

			// Increment burst version counter — used to detect when a new burst
			// starts while an old request is still pending.
			btn._dgBurstVersion = (btn._dgBurstVersion || 0) + 1;

			btn._dgBurst = {
				count: 0,
				// Capture CURRENT state (may have pending request changing it)
				initialActive: btn.classList.contains('is-active'),
				baselineCount: baselineCount,
				version: btn._dgBurstVersion, // Tag this burst with version
			};
		}
		btn._dgBurst.count++;

		// Visual feedback (in-flight). Always set on click so the dim
		// is visible immediately; cleared on resolve/reject.
		btn.classList.add('is-busy');

		// Toggle UI synchronously.
		const willBeActive = !btn.classList.contains('is-active');
		btn.classList.toggle('is-active', willBeActive);

		// Optimistic badge update — derive from burst baseline so rapid
		// clicks within the same burst don't compound on each other's
		// intermediate DOM updates.
		if (btn._dgBurst.baselineCount !== null) {
			const optimisticCount = computeOptimisticCountFromBurst(btn._dgBurst, willBeActive);
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
				// Pass CURRENT UI state, not burst.initialActive
				// (may have changed if previous request completed during burst)
				const currentActive = btn.classList.contains('is-active');
				queueSync(btn, productId, !currentActive, burst.version); // Pass burst version
			} else {
				// No real state change — clear the busy flag and stop.
				btn.classList.remove('is-busy');
			}
		}, SYNC_DELAY_MS);
	}

	/**
	 * Queue a sync request. If no request is pending, execute immediately.
	 * Otherwise, push to queue and wait for current request to finish.
	 *
	 * **Deduplication:** if the same productId is already in the queue,
	 * remove the old queued request (it's stale — user has toggled again).
	 * Only keep the LATEST intent for each product.
	 *
	 * **Pending request handling:** If the same product already has a pending
	 * request, we CANNOT cancel it (fetch API limitation). Instead, we mark
	 * the button with a higher request ID so when the old response arrives,
	 * it will be discarded as stale. The new request will queue normally.
	 *
	 * **Global sequence:** Each request gets a global sequence number to
	 * track order across all products. This prevents race conditions when
	 * clicking multiple different products rapidly.
	 *
	 * @param {HTMLElement} btn            The .dg-wishlist-toggle button.
	 * @param {number}      productId      Numeric WP product ID.
	 * @param {boolean}     initialActive  Pre-burst `is-active` state.
	 * @param {number}      burstVersion   Version tag for this burst.
	 */
	function queueSync(btn, productId, initialActive, burstVersion) {
		// Increment request ID immediately to invalidate any pending request
		// for the same button. This ensures old responses are discarded.
		const myReqId = (btn._dgReqId || 0) + 1;
		btn._dgReqId = myReqId;

		// Increment global sequence for this request (cross-product tracking).
		globalSequence++;
		const mySequence = globalSequence;

		// Remove any existing queued request for the same product (stale).
		// Keep only the current request — it represents the user's latest intent.
		for (let i = requestQueue.length - 1; i >= 0; i--) {
			if (requestQueue[i].productId === productId) {
				requestQueue.splice(i, 1);
			}
		}

		requestQueue.push({ 
			btn: btn, 
			productId: productId, 
			initialActive: initialActive, 
			burstVersion: burstVersion,
			sequence: mySequence  // Attach global sequence to this request
		});
		processQueue();
	}

	/**
	 * Process the next request in the queue if no request is pending.
	 */
	function processQueue() {
		if (pendingRequest || requestQueue.length === 0) {
			return;
		}

		const item = requestQueue.shift();
		pendingRequest = true;
		sendSync(item.btn, item.productId, item.initialActive, item.burstVersion, item.sequence);
	}

	/**
	 * Send the actual AJAX sync. Tag the request so a stale response
	 * from a superseded sync can't overwrite the UI.
	 *
	 * @param {HTMLElement} btn            The .dg-wishlist-toggle button.
	 * @param {number}      productId      Numeric WP product ID.
	 * @param {boolean}     initialActive  Pre-burst `is-active` state,
	 *                                     used to roll back on error.
	 * @param {number}      burstVersion   Version tag (not used - kept for compatibility).
	 * @param {number}      mySequence     Global sequence number for this request.
	 */
	function sendSync(btn, productId, initialActive, burstVersion, mySequence) {
		// NOTE: Request ID was already incremented in queueSync() to invalidate
		// any pending requests. We just read the current ID here.
		const myReqId = btn._dgReqId || 0;

		// Capture the UI state at the moment we send this request.
		// We'll use this to detect drift: if the UI has changed by the time
		// the response arrives, it means the user clicked again and we should
		// NOT force-apply the server state.
		const expectedActive = btn.classList.contains('is-active');

		const fd = new FormData();
		fd.append('action', 'dg_wishlist_toggle');
		fd.append('nonce', (window.dgAjax && window.dgAjax.nonce) || '');
		fd.append('product_id', productId);
		// Send the intended action (add/remove) instead of blind toggle.
		// This makes the operation idempotent and prevents race conditions
		// when requests arrive at server out of order.
		fd.append('intent', expectedActive ? 'add' : 'remove');

		fetch((window.dgAjax && window.dgAjax.url) || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			body: fd,
			credentials: 'same-origin',
		})
			.then(function (r) { return r.json(); })
			.then(function (data) {
				// STALENESS CHECK 1: Per-button Request ID
				// A newer sync for THIS BUTTON has been scheduled since this one fired.
				// Discard so it can't flip the UI back to a value the user has already moved past.
				if (btn._dgReqId !== myReqId) {
					finishRequest(btn);
					return;
				}

				// STALENESS CHECK 2: Global Sequence Number
				// A newer sync for THIS BUTTON has been applied since this one was sent.
				// This prevents out-of-order responses from overwriting newer state.
				// Example: Click I1-add (seq=1) → Click I1-remove (seq=2) → Response seq=2 applies → Response seq=1 arrives
				// Without this check, Response seq=1 would overwrite the newer seq=2 state.
				//
				// Note: undefined > number = false in JavaScript, so first response always passes.
				if (btn._dgLastAppliedSeq > mySequence) {
					finishRequest(btn);
					return;
				}

				// Mark this sequence as applied for this button
				btn._dgLastAppliedSeq = mySequence;

				btn.classList.remove('is-busy');

				if (!data.success) {
					btn.classList.toggle('is-active', initialActive);
					if (data.data && typeof data.data.count === 'number') {
						applyBadgeCount(data.data.count);
					} else if (data.data && data.data.redirect) {
						window.location.href = data.data.redirect;
						finishRequest(btn);
						return;
					} else {
						fetchHeaderBadge();
					}
					finishRequest(btn);
					return;
				}

				// STATE DRIFT DETECTION: Only reconcile UI with server if BOTH:
				// 1. This is still the latest request (no newer request has been queued)
				// 2. UI state hasn't changed since we sent this request
				// If either has changed, the user clicked again and a newer request
				// is coming — let that newer request handle the reconciliation.
				const currentActive = btn.classList.contains('is-active');
				const serverActive = !!data.data.added;
				const isLatestRequest = (btn._dgReqId === myReqId);
				const uiUnchanged = (currentActive === expectedActive);

				const noDrift = isLatestRequest && uiUnchanged;

				if (noDrift) {
					// No drift — UI is still in the state we expected when we sent
					// this request. Server and UI should match.
					if (currentActive === serverActive) {
						// Pop animation ONLY if server confirms our optimistic add.
						if (serverActive && currentActive === true) {
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
					}
				}
				// else: Drift detected (newer request queued OR UI changed) → 
				// user clicked again → don't touch UI, let newer request handle it.

				// Reconcile with the server's authoritative count. Paint
				// silently — the user already saw the optimistic update,
				// firing the badge pulse again would be visual noise.
				if (data.data && typeof data.data.count === 'number') {
					paintBadgeCount(data.data.count);
					if (window.DGWishlist && typeof window.DGWishlist.onCountChange === 'function') {
						window.DGWishlist.onCountChange(data.data.count);
					}
					finishRequest(btn);
					return;
				}

				// Slower path: defer to page-local module if it exposes one.
				if (window.DGWishlist && typeof window.DGWishlist.refreshCount === 'function') {
					window.DGWishlist.refreshCount();
					finishRequest(btn);
					return;
				}

				// Last resort: separate count fetch (only when toggle
				// response somehow omitted the count — defensive).
				fetchHeaderBadge();
				finishRequest(btn);
			})
			.catch(function () {
				if (btn._dgReqId !== myReqId) {
					finishRequest(btn);
					return;
				}
				btn.classList.remove('is-busy');
				btn.classList.toggle('is-active', initialActive);
				fetchHeaderBadge();
				finishRequest(btn);
			});
	}

	/**
	 * Mark current request as finished and process next item in queue.
	 *
	 * @param {HTMLElement} btn The button whose request just finished (optional).
	 */
	function finishRequest(btn) {
		pendingRequest = false;
		processQueue();
	}

	/**
	 * Derive the new badge count from the burst's captured baseline.
	 *
	 * This ensures rapid clicks within the same burst (< 350ms) don't
	 * compound on each other's intermediate DOM updates — we always
	 * compute relative to the state at the start of the burst.
	 *
	 * @param {Object}  burst       The burst state object.
	 * @param {boolean} willBeAdded true = user just added this product.
	 * @return {number}             New count.
	 */
	function computeOptimisticCountFromBurst(burst, willBeAdded) {
		const baseline = burst.baselineCount;
		return willBeAdded ? baseline + 1 : Math.max(0, baseline - 1);
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
