/**
 * Dragon Glow — Shared image lightbox controller
 *
 * One controller for every image lightbox in the theme (single product
 * gallery + gift cards). The controller owns behaviour only — markup lives
 * in the page templates, styling lives in assets/css/zoom-lightbox.css.
 *
 * Markup contract (data attributes keep the controller markup-agnostic):
 *   [data-dg-zoom-root]     modal container (toggles `.is-open`)
 *   [data-dg-zoom-image]    the <img> rendered inside the modal
 *   [data-dg-zoom-counter]  "3 / 7" counter (optional)
 *   [data-dg-zoom-prev]     previous-image button (optional)
 *   [data-dg-zoom-next]     next-image button (optional)
 *   [data-dg-zoom-close]    close button (optional)
 *
 * Trigger contract: any element carrying `[data-dg-zoom-trigger]` opens the
 * lightbox. Triggers are bound through a single delegated listener on
 * `document`, so the controller keeps working when the DOM around a trigger
 * changes (e.g. the gift-card preview is relocated on mobile/tablet) and
 * when several lightboxes share one page.
 *
 * Accessibility: `aria-hidden` is mirrored on the root, focus is trapped
 * inside the modal while open, focus is restored to the trigger on close,
 * and Escape / ArrowLeft / ArrowRight are handled.
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

	// Every live instance, used to resolve which lightbox a trigger belongs to.
	var instances = [];

	/**
	 * Lightbox instance.
	 *
	 * @param {Object}   options
	 * @param {HTMLElement} options.root        Modal container (required).
	 * @param {Array}      options.items        Gallery items `{ src, alt, ... }`.
	 * @param {Function}   [options.onChange]   Called after the user navigates
	 *                                            (prev/next): `(index, item)`.
	 *                                            NOT called on open — opening
	 *                                            reflects the page's current
	 *                                            state, it does not change it.
	 * @param {Function}   [options.resolveIndex] Returns the index to open at, or -1.
	 * @param {HTMLElement} [options.scope]      Subtree holding the triggers of this
	 *                                            lightbox. Default: document.
	 */
	function ZoomLightbox(options) {
		options = options || {};

		this.root = options.root || null;
		if (!this.root) {
			return;
		}

		this.image = options.image || this.root.querySelector('[data-dg-zoom-image]');
		this.counter = options.counter || this.root.querySelector('[data-dg-zoom-counter]');
		this.prevBtn = options.prev || this.root.querySelector('[data-dg-zoom-prev]');
		this.nextBtn = options.next || this.root.querySelector('[data-dg-zoom-next]');
		this.closeBtn = options.close || this.root.querySelector('[data-dg-zoom-close]');

		this.items = options.items || [];
		this.onChange = typeof options.onChange === 'function' ? options.onChange : null;
		this.resolveIndex = typeof options.resolveIndex === 'function' ? options.resolveIndex : function () {
			return -1;
		};
		this.scope = options.scope || document;

		this.index = 0;
		this.lastFocused = null;
		this.savedOverflow = null;

		this.bindEvents();
		instances.push(this);
	}

	/* ── Events ────────────────────────────────────────────────────────────── */

	ZoomLightbox.prototype.bindEvents = function () {
		var self = this;

		// Click on the backdrop (the root itself) closes the modal.
		this.root.addEventListener('click', function (event) {
			if (event.target === self.root) {
				self.close();
			}
		});

		if (this.closeBtn) {
			this.closeBtn.addEventListener('click', function () {
				self.close();
			});
		}

		if (this.prevBtn) {
			this.prevBtn.addEventListener('click', function () {
				self.prev();
			});
		}

		if (this.nextBtn) {
			this.nextBtn.addEventListener('click', function () {
				self.next();
			});
		}
	};

	/* ── Public API ────────────────────────────────────────────────────────── */

	/**
	 * Open the lightbox.
	 *
	 * @param {number}      [index]   Explicit index. Default: resolved from the page state.
	 * @param {HTMLElement} [trigger] Element that opened the lightbox. Used to
	 *                               restore focus on close (clicking an <img>
	 *                               does not focus it, so `document.activeElement`
	 *                               would still be <body>).
	 */
	ZoomLightbox.prototype.open = function (index, trigger) {
		if (!this.items.length) {
			return;
		}

		var target = typeof index === 'number' ? index : this.resolveIndex();
		if (typeof target !== 'number' || target < 0 || target >= this.items.length) {
			target = 0;
		}
		this.index = target;

		// Opening reflects the page's current state — do not notify the
		// consumer (it would make the page "follow" a state it already has).
		this.render(false);

		this.lastFocused = trigger || document.activeElement;
		this.root.classList.add('is-open');
		this.root.setAttribute('aria-hidden', 'false');
		this.lockScroll();

		if (this.closeBtn) {
			this.closeBtn.focus();
		} else if (typeof this.root.focus === 'function') {
			this.root.focus();
		}
	};

	/**
	 * Close the lightbox and restore the page state.
	 */
	ZoomLightbox.prototype.close = function () {
		if (!this.isOpen()) {
			return;
		}

		this.root.classList.remove('is-open');
		this.root.setAttribute('aria-hidden', 'true');
		this.unlockScroll();

		if (this.lastFocused && typeof this.lastFocused.focus === 'function') {
			this.lastFocused.focus();
		}
		this.lastFocused = null;
	};

	/**
	 * @return {boolean} Whether the modal is currently open.
	 */
	ZoomLightbox.prototype.isOpen = function () {
		return this.root.classList.contains('is-open');
	};

	ZoomLightbox.prototype.next = function () {
		if (this.items.length <= 1) {
			return;
		}
		this.index = (this.index + 1) % this.items.length;
		this.render(true);
	};

	ZoomLightbox.prototype.prev = function () {
		if (this.items.length <= 1) {
			return;
		}
		this.index = (this.index - 1 + this.items.length) % this.items.length;
		this.render(true);
	};

	/* ── Internals ─────────────────────────────────────────────────────────── */

	/**
	 * Paint the current item into the modal.
	 *
	 * @param {boolean} [notify] Whether to notify the consumer via `onChange`.
	 *                            False on open — opening the lightbox reflects
	 *                            the page's current state, it does not change it.
	 *                            True on prev/next — the user navigated, so the
	 *                            page must follow.
	 */
	ZoomLightbox.prototype.render = function (notify) {
		var item = this.items[this.index];
		if (!item) {
			return;
		}

		if (this.image) {
			this.image.src = item.src;
			this.image.alt = item.alt || '';

			// Re-trigger the fade-in so switching images never jumps abruptly.
			this.image.classList.remove('is-visible');
			void this.image.offsetWidth;
			this.image.classList.add('is-visible');
		}

		var single = this.items.length <= 1;

		if (this.counter) {
			this.counter.textContent = (this.index + 1) + ' / ' + this.items.length;
			this.counter.hidden = single;
		}
		if (this.prevBtn) {
			this.prevBtn.hidden = single;
		}
		if (this.nextBtn) {
			this.nextBtn.hidden = single;
		}

		if (notify && this.onChange) {
			this.onChange(this.index, item);
		}
	};

	/**
	 * Keep Tab focus inside the modal.
	 *
	 * @param {KeyboardEvent} event
	 */
	ZoomLightbox.prototype.trapFocus = function (event) {
		var focusable = this.root.querySelectorAll(FOCUSABLE);
		if (!focusable.length) {
			return;
		}

		var first = focusable[0];
		var last = focusable[focusable.length - 1];
		var active = document.activeElement;

		if (!this.root.contains(active)) {
			event.preventDefault();
			first.focus();
			return;
		}

		if (event.shiftKey && active === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && active === last) {
			event.preventDefault();
			first.focus();
		}
	};

	ZoomLightbox.prototype.lockScroll = function () {
		this.savedOverflow = document.body.style.overflow;
		document.body.style.overflow = 'hidden';
	};

	ZoomLightbox.prototype.unlockScroll = function () {
		document.body.style.overflow = this.savedOverflow || '';
		this.savedOverflow = null;
	};

	/* ── Global (delegated) listeners ─────────────────────────────────────── */

	/**
	 * Find the lightbox that owns a given trigger.
	 *
	 * @param {HTMLElement} trigger
	 * @return {ZoomLightbox|null}
	 */
	function resolveInstance(trigger) {
		for (var i = 0; i < instances.length; i++) {
			if (instances[i].scope.contains(trigger)) {
				return instances[i];
			}
		}
		return null;
	}

	document.addEventListener('click', function (event) {
		var trigger = event.target.closest('[data-dg-zoom-trigger]');
		if (!trigger) {
			return;
		}

		var instance = resolveInstance(trigger);
		if (!instance) {
			return;
		}

		event.preventDefault();
		instance.open(undefined, trigger);
	});

	document.addEventListener('keydown', function (event) {
		// Keyboard activation for non-button triggers (e.g. an <img role="button">).
		if (event.key !== 'Enter' && event.key !== ' ' && event.key !== 'Spacebar') {
			return;
		}

		var trigger = event.target.closest('[data-dg-zoom-trigger]');
		if (!trigger || trigger.tagName === 'BUTTON') {
			return;
		}

		var instance = resolveInstance(trigger);
		if (!instance) {
			return;
		}

		event.preventDefault();
		instance.open(undefined, trigger);
	});

	document.addEventListener('keydown', function (event) {
		var open = null;
		for (var i = 0; i < instances.length; i++) {
			if (instances[i].isOpen()) {
				open = instances[i];
				break;
			}
		}
		if (!open) {
			return;
		}

		if (event.key === 'Escape' || event.key === 'Esc') {
			event.preventDefault();
			open.close();
		} else if (event.key === 'ArrowLeft') {
			event.preventDefault();
			open.prev();
		} else if (event.key === 'ArrowRight') {
			event.preventDefault();
			open.next();
		} else if (event.key === 'Tab') {
			open.trapFocus(event);
		}
	});

	window.DGZoomLightbox = {
		/**
		 * @param {Object} options See {@link ZoomLightbox}.
		 * @return {ZoomLightbox} The instance.
		 */
		create: function (options) {
			return new ZoomLightbox(options);
		}
	};
})();
