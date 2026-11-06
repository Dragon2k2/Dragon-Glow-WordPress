/**
 * Dragon Glow — Nav Badge (combined counter on the menu toggle)
 *
 * Owns the combined counter pill that rides on the mobile/tablet menu
 * toggle (`#dg-mobile-menu-toggle`), below 1024px.
 *
 * Why a dedicated module:
 *   - The pill shows bag + wishlist COMBINED, so it cannot reuse the
 *     `.dg-cart-count` class — `DGCartBadge.paint()` writes the cart count
 *     into every `.dg-cart-count` element and would overwrite the total.
 *   - Two independent counters feed one pill, so the module keeps a local
 *     copy of both and recomputes the total whenever either changes.
 *
 * Public API:
 *   window.DGNavBadge.setCart(count);
 *   window.DGNavBadge.setWishlist(count);
 *   window.DGNavBadge.refresh();   → re-read both counters from the DOM
 *
 * The counts are seeded from the DOM on init: the server renders the
 * initial total, and the individual counters are recovered from the
 * already-rendered `.dg-cart-count` / `.dg-wishlist-count` badges.
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	var PULSE_CLASS = 'is-pulse';
	var FIRST_SHOW_CLASS = 'is-first-show';

	// Current value of each counter. `null` means "not known yet" — the
	// badge stays untouched until at least one counter is known, so we
	// never flash a wrong total.
	var cartCount = null;
	var wishlistCount = null;

	/**
	 * Read the numeric value of the first badge matching a selector.
	 *
	 * @param {string} selector
	 * @return {number} The count; 0 when the badge is absent or hidden.
	 */
	function readCountFromBadge(selector) {
		var badge = document.querySelector(selector);
		if (!badge) {
			return 0;
		}
		if (badge.classList.contains('hidden')) {
			return 0;
		}
		var value = parseInt(badge.textContent, 10);
		return isFinite(value) && value >= 0 ? value : 0;
	}

	/**
	 * Remove any pending animation class, force a reflow, then add the given
	 * one so the CSS animation restarts cleanly even on rapid repeat changes.
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
		// Auto-clean so the class doesn't linger on the DOM.
		setTimeout(function () {
			el.classList.remove(PULSE_CLASS);
			el.classList.remove(FIRST_SHOW_CLASS);
		}, 750);
	}

	/**
	 * Build the screen-reader label for the menu toggle.
	 *
	 * The badge lives INSIDE the button, so a screen reader would otherwise
	 * announce a bare digit ("Menu 3"). The button carries an explicit
	 * `aria-label`, which wins over descendant text per the accname spec —
	 * so the digit itself is never announced twice.
	 *
	 * @param {number} total Combined counter value.
	 * @return {void}
	 */
	function syncAriaLabel(total) {
		var toggle = document.getElementById('dg-mobile-menu-toggle');
		if (!toggle) {
			return;
		}

		var i18n = (window.dgAjax && window.dgAjax.i18n) || {};
		var items = total === 1
			? (i18n.navItemSingular || '1 item')
			: ((i18n.navItemPlural || '%d items').replace('%d', String(total)));

		var base = toggle.getAttribute('data-dg-label') || 'Menu';
		toggle.setAttribute('aria-label', total > 0 ? base + ', ' + items : base);
	}

	/**
	 * Paint the combined total onto the toggle badge.
	 *
	 * @return {void}
	 */
	function paint() {
		var badge = document.querySelector('.dg-nav-badge');
		if (!badge) {
			return;
		}

		// Fall back to the server-rendered badges when a counter has not been
		// reported yet — the DOM badges hold the truth at that point, so the
		// pill never flashes a wrong total.
		var cart = cartCount !== null ? cartCount : readCountFromBadge('.dg-cart-count');
		var wishlist = wishlistCount !== null ? wishlistCount : readCountFromBadge('.dg-wishlist-count');

		var total = cart + wishlist;
		var wasHidden = badge.classList.contains('hidden');
		var prevCount = badge.textContent;

		badge.textContent = String(total);

		var willShow = total > 0;
		badge.classList.toggle('hidden', !willShow);

		syncAriaLabel(total);

		// Only animate a visible badge whose value actually changed.
		var valueChanged = prevCount !== String(total);
		if (!willShow || !valueChanged) {
			return;
		}

		if (wasHidden && willShow) {
			restartAnimation(badge, FIRST_SHOW_CLASS);
		} else {
			restartAnimation(badge, PULSE_CLASS);
		}
	}

	/**
	 * Set the cart counter and repaint the combined badge.
	 *
	 * @param {number} count Non-negative integer item count.
	 * @return {void}
	 */
	function setCart(count) {
		var value = parseInt(count, 10);
		cartCount = isFinite(value) && value > 0 ? value : 0;
		paint();
	}

	/**
	 * Set the wishlist counter and repaint the combined badge.
	 *
	 * @param {number} count Non-negative integer item count.
	 * @return {void}
	 */
	function setWishlist(count) {
		var value = parseInt(count, 10);
		wishlistCount = isFinite(value) && value > 0 ? value : 0;
		paint();
	}

	/**
	 * Re-read both counters from the rendered badges.
	 *
	 * Used on init to recover the individual values from the server-rendered
	 * markup, and after a full page-state change that may have rewritten
	 * the badge elements (e.g. a WooCommerce fragment refresh).
	 *
	 * @return {void}
	 */
	function refresh() {
		var cart = readCountFromBadge('.dg-cart-count');
		var wishlist = readCountFromBadge('.dg-wishlist-count');

		if (cart !== null) {
			cartCount = cart;
		}
		if (wishlist !== null) {
			wishlistCount = wishlist;
		}

		paint();
	}

	// ── Expose on window ──────────────────────────────────────────────────────

	window.DGNavBadge = {
		setCart: setCart,
		setWishlist: setWishlist,
		refresh: refresh,
	};

	// ── Init ──────────────────────────────────────────────────────────────────

	function init() {
		refresh();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
