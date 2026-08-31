/**
 * Dragon Glow — Wishlist Toggle (lib)
 * Shared heart button handler used everywhere a product can be added to the
 * wishlist (Shop grid, single product, related products, wishlist page).
 *
 * Listens for clicks on `.dg-wishlist-toggle` and POSTs to `dg_wishlist_toggle`.
 * Updates the heart's `is-active` state optimistically and rolls back on error.
 *
 * Header badge update — **zero perceived latency**:
 *   1. On click, derive the new count locally from the badge's current text
 *      (`computeOptimisticCount()`) and paint it synchronously. The header
 *      reflects the toggle in the same frame as the click — no waiting for
 *      the network round-trip at all. Same UX pattern as `is-active`.
 *   2. When the toggle response comes back, reconcile: if the server's
 *      authoritative `count` matches what we painted, do nothing (would
 *      be visual noise to re-animate). If it differs — fix it via
 *      `paintBadgeCount()` (silent, no pulse).
 *   3. On error or network failure, re-sync from the server so stale
 *      optimistic values get corrected on the user's next interaction.
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

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

		const wasActive = btn.classList.contains('is-active');
		btn.classList.add('is-busy');
		btn.classList.toggle('is-active', !wasActive);

		// Optimistic badge update — paint the new count synchronously so the
		// header reflects the toggle immediately, without waiting for the
		// network round-trip. On response we either confirm (the value
		// matches) or correct (server's authoritative `count` wins, no
		// rollback because the user-visible state is already what they
		// expect). This is the same UX pattern the heart icon already
		// uses for its own `is-active` state.
		const optimisticCount = computeOptimisticCount(!wasActive);
		if (optimisticCount !== null) {
			applyBadgeCount(optimisticCount);
		}

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
				btn.classList.remove('is-busy');
				if (!data.success) {
					btn.classList.toggle('is-active', wasActive);
					// Roll back optimistic badge to whatever the server says.
					if (data.data && typeof data.data.count === 'number') {
						applyBadgeCount(data.data.count);
					} else if (data.data && data.data.redirect) {
						window.location.href = data.data.redirect;
						return;
					} else {
						// No authoritative count from the error path — just
						// re-sync from the server's source of truth.
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

				// Reconcile with the server's authoritative count.
				// If it matches our optimistic value — great, skip the
				// re-render so we don't re-trigger the animation. If it
				// differs (rare; e.g. concurrent toggle from another tab,
				// or stale baseline from a different user) — paint the
				// correct value silently (no pulse — user is already happy).
				if (data.data && typeof data.data.count === 'number') {
					if (typeof optimisticCount === 'number' && optimisticCount === data.data.count) {
						return; // matches, nothing to do.
					}
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

				// Last resort: separate count fetch (only when toggle response
				// somehow omitted the count — defensive).
				fetchHeaderBadge();
			})
			.catch(function () {
				btn.classList.remove('is-busy');
				btn.classList.toggle('is-active', wasActive);
				// Network error — re-sync from server to drop stale optimistics.
				fetchHeaderBadge();
			});
	});

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
