/**
 * Dragon Glow — Cart Count Badge (shared)
 *
 * Single source of truth for painting the `.dg-cart-count` badge. The badge
 * is rendered in TWO places:
 *   - desktop: on the `shopping_bag` cart icon
 *   - mobile / tablet (< 1024px): on the drawer's "Cart" item
 *     (`.dg-mobile-menu-item--cart`), the only visible cart entry below `lg`
 *
 * The combined badge on the mobile/tablet menu toggle is NOT one of them —
 * it shows bag + wishlist together and is owned by `assets/js/lib/nav-badge.js`.
 *
 * Before this module existed, six call sites painted the badge independently
 * (`lib/cart-api.js`, `lib/cart-feedback.js`, `cart.js`, `thankyou.js`,
 * `wishlist.js` optimistic bump) and none of them animated the change. Each
 * one did `textContent = count` + `classList.toggle('hidden', count === 0)`,
 * so the badge jumped silently.
 *
 * This module keeps that exact contract (same selector, same `hidden`
 * toggle) and adds the change-detection animation:
 *   - `is-pulse`      → the count changed → subtle scale pop
 *   - `is-first-show` → the badge appeared for the first time → stronger
 *                       entrance so the user notices it
 *
 * The animation classes + keyframes live in `header-nav.css`, mirroring the
 * wishlist badge (`.dg-wishlist-count`) so both badges behave identically.
 *
 * Usage (from any module):
 *   window.DGCartBadge.paint(count);
 *   window.DGCartBadge.bump(+1 | -1);
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	var PULSE_CLASS = 'is-pulse';
	var FIRST_SHOW_CLASS = 'is-first-show';

	/**
	 * Remove any pending animation class, force a reflow, then add the given
	 * one so the CSS animation restarts cleanly even on rapid repeat changes.
	 *
	 * Both classes are always cleared first — otherwise a lingering
	 * `is-first-show` could win the cascade over a fresh `is-pulse` and the
	 * badge would replay the entrance instead of the subtle pop.
	 *
	 * @param {HTMLElement} el
	 * @param {string}      className
	 * @return {void}
	 */
	function restartAnimation(el, className) {
		el.classList.remove(PULSE_CLASS);
		el.classList.remove(FIRST_SHOW_CLASS);
		// Force reflow — reading offsetWidth is the cheapest reliable way.
		// eslint-disable-next-line no-unused-expressions
		el.offsetWidth;
		el.classList.add(className);
		// Auto-clean so the class doesn't linger (keeps the DOM tidy and lets
		// a later change trigger the animation again).
		setTimeout(function () {
			el.classList.remove(PULSE_CLASS);
			el.classList.remove(FIRST_SHOW_CLASS);
		}, 750);
	}

	/**
	 * Update the screen-reader labels that carry the cart count, so the
	 * number is announced with context instead of as a bare digit.
	 *
	 * Only the drawer's "Cart" item needs it: the badge lives INSIDE the
	 * link, so a screen reader would otherwise announce a bare digit
	 * ("Cart 3"). The anchor carries an explicit `aria-label`, which wins
	 * over descendant text per the accname spec — so the digit itself is
	 * never announced twice.
	 *
	 * The menu toggle is NOT handled here: its badge shows the combined
	 * bag + wishlist total and is owned by `assets/js/lib/nav-badge.js`.
	 *
	 * @param {number} total Current cart item count.
	 * @return {void}
	 */
	function syncAriaLabels(total) {
		var i18n = (window.dgAjax && window.dgAjax.i18n) || {};
		var items = total === 1
			? (i18n.itemSingular || '1 item in bag')
			: ((i18n.itemPlural || '%d items in bag').replace('%d', String(total)));

		// Drawer "Cart" item.
		var cartLinks = document.querySelectorAll('.dg-mobile-menu-item--cart a');
		for (var i = 0; i < cartLinks.length; i++) {
			var link = cartLinks[i];
			var base = link.getAttribute('data-dg-label') || 'Cart';
			link.setAttribute('aria-label', total > 0 ? base + ', ' + items : base);
		}
	}

	/**
	 * Paint an authoritative cart count onto every `.dg-cart-count` badge and
	 * animate the change.
	 *
	 * @param {number} count Non-negative integer item count.
	 * @return {void}
	 */
	function paint(count) {
		var total = parseInt(count, 10);
		if (!isFinite(total) || total < 0) {
			total = 0;
		}

		syncAriaLabels(total);

		// Keep the combined badge on the menu toggle in sync. The toggle
		// shows bag + wishlist together, so it needs the cart count even
		// though it does not carry the `.dg-cart-count` class.
		if (window.DGNavBadge && typeof window.DGNavBadge.setCart === 'function') {
			window.DGNavBadge.setCart(total);
		}

		var badges = document.querySelectorAll('.dg-cart-count');

		for (var i = 0; i < badges.length; i++) {
			var el = badges[i];
			var wasHidden = el.classList.contains('hidden');
			var prevCount = el.textContent;

			el.textContent = String(total);

			var willShow = total > 0;
			el.classList.toggle('hidden', !willShow);

			// Only animate a visible badge whose value actually changed.
			var valueChanged = prevCount !== String(total);
			if (!willShow || !valueChanged) {
				continue;
			}

			if (wasHidden && willShow) {
				restartAnimation(el, FIRST_SHOW_CLASS);
			} else {
				restartAnimation(el, PULSE_CLASS);
			}
		}
	}

	/**
	 * Optimistically adjust every badge by a signed delta.
	 *
	 * Used by the wishlist "add all to bag" flow, which knows the delta
	 * before the server confirms. The optimistic value is painted WITH the
	 * animation so the user gets instant feedback; the follow-up
	 * `DGCart.refreshCount()` then reconciles against the server and stays
	 * silent (the value already matches, so nothing re-animates).
	 *
	 * @param {number} by Signed integer — positive to add, negative to subtract.
	 * @return {void}
	 */
	function bump(by) {
		var delta = parseInt(by, 10) || 0;
		if (delta === 0) {
			return;
		}

		var badges = document.querySelectorAll('.dg-cart-count');
		if (!badges.length) {
			return;
		}

		var current = parseInt(badges[0].textContent, 10) || 0;
		var next = current + delta;
		if (next < 0) {
			next = 0;
		}

		paint(next);
	}

	// ── Expose on window ──────────────────────────────────────────────────────
	window.DGCartBadge = {
		paint: paint,
		bump: bump,
	};
})();
