/**
 * Dragon Glow — Wishlist Page JS
 * ES Module — Motion (motion.dev) vanilla API. KHÔNG React.
 *
 * Responsibilities:
 *   - Reveal: stagger các phần tử data-sr (Motion inView + animate + stagger).
 *   - Stat count-up cho [data-count-to] (mirror account.js).
 *   - Filter (All / In stock / On sale) — client-side, no AJAX. Hides
 *     non-matching cards with Motion.
 *   - Bulk select: master checkbox + per-card checkboxes; floating bulk bar
 *     fades in when ≥ 1 card is selected. Bulk remove and bulk add-to-bag.
 *   - Bulk add uses one server request, preserves failed selections for retry,
 *     and reports products that need options or are unavailable.
 *   - Optimize: single-card remove (heart icon on the card) with optimistic
 *     UI + rollback on error.
 *   - Share modal: email share (POST → dg_wishlist_share) + copy-link button.
 *   - Clear wishlist (with glassmorphism confirm modal).
 *   - Toast feedback: shared toaster, auto-dismiss after 3.5s.
 *
 * Tôn trọng prefers-reduced-motion.
 *
 * @package Dragon_Glow
 */

import { animate, inView, stagger } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";

(function () {
	'use strict';

	const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
	const root = document.querySelector('.dg-wishlist');
	if (!root) {
		return;
	}

	const i18n = (window.dgWishlist && window.dgWishlist.i18n) || {};
	const grid = root.querySelector('[data-dg-wl-grid]');
	const shell = root.querySelector('[data-dg-wl-grid-shell]');
	const emptyState = root.querySelector('[data-dg-wl-empty]');
	const noResult = root.querySelector('[data-dg-wl-noresult]');
	const bulkBar = root.querySelector('[data-dg-wl-bulkbar]');
	const bulkCountEl = root.querySelector('[data-dg-wl-bulk-count]');
	const toasts = root.querySelector('[data-dg-wl-toasts]');

	const EASE = [0.22, 1, 0.36, 1];
	let bulkAddPending = false;

	// Boot. initSortDropdown runs BEFORE initReveal so the DOM is already
	// in the user's chosen order by the time the cards fade in — that
	// way the reveal animation lands on the sorted layout with no flash
	// of the un-sorted baseline.
	initSortDropdown();
	initReveal();
	initCountTo();
	initFilter();
	initSelectAll();
	initSingleRemove();
	initBulkBar();
	initShareModal();
	initClearAll();
	initConfirmModal();
	initInitialEmptyState();
	updateCounts();

	// Expose a tiny API for other modules (e.g. lib/wishlist-toggle.js)
	// to update the badge when an item is added/removed from another page.
	window.DGWishlist = {
		refreshCount,
	};

	/* ── Reveal: stagger các phần tử data-sr ───────────────────────────────── */
	function initReveal() {
		if (reduce) {
			root.querySelectorAll('[data-sr]').forEach(function (k) { k.style.opacity = '1'; });
			return;
		}
		// Hero stats stagger.
		const statsGroup = root.querySelector('.dg-wishlist-hero__stats');
		if (statsGroup) {
			const items = Array.from(statsGroup.querySelectorAll('[data-sr]'));
			if (items.length) {
				inView(statsGroup, function () {
					animate(items, { opacity: [0, 1], y: [22, 0] }, { duration: 0.6, ease: EASE, delay: stagger(0.06) });
				}, { amount: 0.2 });
			}
		}

		// Other elements.
		root.querySelectorAll('[data-sr]:not(.dg-wishlist-hero__stats [data-sr])').forEach(function (el) {
			inView(el, function () {
				animate(el, { opacity: [0, 1], y: [22, 0] }, { duration: 0.6, ease: EASE });
			}, { amount: 0.15 });
		});
	}

	/* ── Count-up ─────────────────────────────────────────────────────────── */
	function initCountTo() {
		root.querySelectorAll('[data-count-to]').forEach(function (el) {
			const target = parseInt(el.getAttribute('data-count-to') || '0', 10) || 0;
			if (reduce || target === 0) {
				el.textContent = String(target);
				return;
			}
			inView(el, function () {
				animate(0, target, {
					duration: 0.9,
					ease: 'easeOut',
					onUpdate: function (v) { el.textContent = String(Math.round(v)); },
				});
			}, { amount: 0.4 });
		});
	}

	/* ── Filter ───────────────────────────────────────────────────────────── */
	function initFilter() {
		const buttons = root.querySelectorAll('[data-dg-wl-filter]');
		if (!buttons.length) {
			return;
		}
		buttons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				const filter = btn.getAttribute('data-dg-wl-filter');
				if (!filter) return;

				buttons.forEach(function (b) {
					const isActive = b === btn;
					b.classList.toggle('is-active', isActive);
					b.setAttribute('aria-selected', isActive ? 'true' : 'false');
				});

				applyFilter(filter);
			});
		});
	}

	function applyFilter(filter) {
		if (!grid) return;
		const cards = Array.from(grid.querySelectorAll('[data-dg-wl-card]'));
		let visibleCount = 0;

		cards.forEach(function (card) {
			const inStock = card.getAttribute('data-in-stock') === '1';
			const onSale = card.getAttribute('data-on-sale') === '1';
			let show = true;
			if (filter === 'in_stock') show = inStock;
			else if (filter === 'on_sale') show = onSale;

			if (show) {
				card.style.display = '';
				card.style.opacity = '';
				card.style.removeProperty('transform');
				visibleCount++;
			} else {
				card.style.display = 'none';
			}
		});

		// Toggle no-result notice.
		if (noResult) {
			noResult.hidden = visibleCount > 0;
		}

		// Animate remaining cards in.
		if (!reduce && visibleCount > 0) {
			const visible = cards.filter(function (c) { return c.style.display !== 'none'; });
			animate(
				visible,
				{ opacity: [0.5, 1], y: [10, 0] },
				{ duration: 0.4, ease: EASE, delay: stagger(0.03) }
			);
		}

		updateMasterCheckbox();
	}

	// Reset filter button (inside no-result notice).
	if (noResult) {
		const resetBtn = noResult.querySelector('[data-dg-wl-reset-filter]');
		if (resetBtn) {
			resetBtn.addEventListener('click', function () {
				const allBtn = root.querySelector('[data-dg-wl-filter="all"]');
				if (allBtn) allBtn.click();
			});
		}
	}

	/* ── Sort dropdown — visual trigger + DOM reorder + persistence ───────
	 * Implementation contract:
	 *   - Native <select> stays the JS source-of-truth (form-submit safe).
	 *   - Hidden select value mirrors the active panel option.
	 *   - On selection the grid is reordered client-side using the
	 *     per-card data-* attributes (data-price, data-name). For the
	 *     "date" sort we reverse the existing DOM order — the user_meta
	 *     array that drives the page renders oldest-first, so reversing
	 *     gives "newest first" without needing a per-item timestamp.
	 *   - Persistence: URL ?sort=<value> (shareable, takes precedence on
	 *     load) > localStorage dg-wl-sort (per-user preference) > the
	 *     native <select> default. Writes are debounced into one URL
	 *     replaceState + one localStorage.setItem per selection.
	 *   - Keyboard model: WAI-ARIA APG listbox pattern. Arrow keys move
	 *     between options via roving tabindex; Home/End jump to ends;
	 *     Enter/Space select the focused option; Esc/Tab close and
	 *     return focus appropriately.
	 *   - A polite live region announces the new sort to screen readers
	 *     so the change isn't silent for AT users.
	 */
	function initSortDropdown() {
		const wrap    = root.querySelector('[data-dg-wl-sort-wrap]');
		const select  = root.querySelector('[data-dg-wl-sort]');
		const trigger = root.querySelector('[data-dg-wl-sort-trigger]');
		const panel   = root.querySelector('[data-dg-wl-sort-panel]');
		const iconEl  = root.querySelector('[data-dg-wl-sort-icon]');
		const curEl   = root.querySelector('[data-dg-wl-sort-current]');
		const options = Array.from(root.querySelectorAll('[data-dg-wl-sort-option]'));
		if (!wrap || !select || !trigger || !panel || !options.length) {
			return;
		}

		// Stable IDs for ARIA references + roving tabindex.
		if (!panel.id) {
			panel.id = 'dg-wl-sort-panel';
		}
		trigger.setAttribute('aria-controls', panel.id);
		options.forEach(function (opt, idx) {
			opt.id = 'dg-wl-sort-opt-' + idx;
			opt.setAttribute('tabindex', '-1');
		});

		let activeIndex = 0;

		function indexOfValue(value) {
			return options.findIndex(function (o) {
				return o.getAttribute('data-value') === value;
			});
		}

		/* ── Persistence: URL > localStorage > native <select> default ─── */
		function readInitialValue() {
			try {
				const urlSort = new URL(window.location.href).searchParams.get('sort');
				if (urlSort && indexOfValue(urlSort) >= 0) {
					return urlSort;
				}
			} catch (e) { /* silent */ }
			try {
				const stored = localStorage.getItem('dg-wl-sort');
				if (stored && indexOfValue(stored) >= 0) {
					return stored;
				}
			} catch (e) { /* silent */ }
			return select.value || 'date';
		}

		function persist(value) {
			try {
				const url = new URL(window.location.href);
				if (value === 'date') {
					url.searchParams.delete('sort'); // keep clean for the default
				} else {
					url.searchParams.set('sort', value);
				}
				window.history.replaceState({}, '', url);
			} catch (e) { /* silent */ }
			try {
				localStorage.setItem('dg-wl-sort', value);
			} catch (e) { /* silent */ }
		}

		/* ── UI sync: trigger label/icon + option is-active + native <select> ── */
		function syncUI(value) {
			const idx = indexOfValue(value);
			if (idx < 0) {
				return;
			}
			activeIndex = idx;
			options.forEach(function (opt, i) {
				const isActive = i === idx;
				opt.classList.toggle('is-active', isActive);
				opt.setAttribute('aria-selected', isActive ? 'true' : 'false');
				opt.setAttribute('tabindex', isActive ? '0' : '-1');
				if (isActive) {
					if (iconEl) {
						iconEl.textContent = opt.getAttribute('data-icon') || 'schedule';
					}
					if (curEl) {
						curEl.textContent = opt.getAttribute('data-label') || '';
					}
				}
			});
			if (select.value !== value) {
				select.value = value;
			}
			panel.setAttribute('aria-activedescendant', options[idx].id);
		}

		/* ── Reorder the grid cards according to the chosen sort key ───── */
		function applySort(value, animateIn) {
			if (!grid) {
				return;
			}
			const cards = Array.from(grid.querySelectorAll('[data-dg-wl-card]'));
			if (!cards.length) {
				return;
			}

			let sorted;
			if (value === 'date') {
				// Recently saved = newest first. Original DOM order mirrors
				// the user_meta array (oldest first, newest last), so reverse.
				sorted = cards.slice().reverse();
			} else if (value === 'price-asc') {
				sorted = cards.slice().sort(function (a, b) {
					return getPrice(a) - getPrice(b);
				});
			} else if (value === 'price-desc') {
				sorted = cards.slice().sort(function (a, b) {
					return getPrice(b) - getPrice(a);
				});
			} else if (value === 'name') {
				sorted = cards.slice().sort(function (a, b) {
					return getName(a).localeCompare(getName(b));
				});
			} else {
				return;
			}

			// Reorder DOM — appending an existing child moves it. Using a
			// fragment keeps this to a single reflow instead of N.
			const frag = document.createDocumentFragment();
			sorted.forEach(function (c) { frag.appendChild(c); });
			grid.appendChild(frag);

			if (animateIn && !reduce) {
				animate(
					sorted,
					{ opacity: [0.4, 1], y: [8, 0] },
					{ duration: 0.35, ease: EASE, delay: stagger(0.02) }
				);
			}
		}

		function getPrice(card) {
			return parseFloat(card.getAttribute('data-price') || '0') || 0;
		}
		function getName(card) {
			return (card.getAttribute('data-name') || '').toLowerCase();
		}

		/* ── Live region: announce sort change to AT users ────────────── */
		let live = root.querySelector('[data-dg-wl-sort-live]');
		if (!live) {
			live = document.createElement('div');
			live.setAttribute('data-dg-wl-sort-live', '');
			live.setAttribute('role', 'status');
			live.setAttribute('aria-live', 'polite');
			live.setAttribute('aria-atomic', 'true');
			live.className = 'dg-wishlist-sr-only';
			root.appendChild(live);
		}
		function announce(value) {
			const opt = options[indexOfValue(value)];
			if (!opt) {
				return;
			}
			const label  = opt.getAttribute('data-label') || '';
			const sub    = opt.getAttribute('data-sub') || '';
			const prefix = (i18n && i18n.sortedBy) || 'Sorted by';
			// Defer one frame so screen readers reliably pick up the change
			// even when focus is mid-transition.
			setTimeout(function () {
				live.textContent = sub
					? prefix + ' ' + label + '. ' + sub + '.'
					: prefix + ' ' + label + '.';
			}, 30);
		}

		/* ── Selection pipeline (option click / Enter / Space) ────────── */
		function selectOption(value) {
			if (indexOfValue(value) < 0) {
				return;
			}
			syncUI(value);
			persist(value);
			applySort(value, true);
			announce(value);
		}

		/* ── Open / close with focus management ──────────────────────── */
		function open() {
			panel.hidden = false;
			trigger.setAttribute('aria-expanded', 'true');
			const target = options[activeIndex] || options[0];
			if (target) {
				target.focus();
			}
		}
		function close(returnFocus) {
			panel.hidden = true;
			trigger.setAttribute('aria-expanded', 'false');
			if (returnFocus) {
				trigger.focus();
			}
		}

		/* ── Events ─────────────────────────────────────────────────── */
		trigger.addEventListener('click', function (e) {
			e.stopPropagation();
			if (panel.hidden) {
				open();
			} else {
				close(true);
			}
		});

		options.forEach(function (opt) {
			opt.addEventListener('click', function (e) {
				e.stopPropagation();
				const value = opt.getAttribute('data-value');
				if (!value) {
					return;
				}
				selectOption(value);
				close(true);
			});
		});

		// Keyboard navigation: arrow / Home / End / Enter / Space / Esc / Tab.
		panel.addEventListener('keydown', function (e) {
			if (panel.hidden) {
				return;
			}
			let next = activeIndex;
			let handled = true;
			if (e.key === 'ArrowDown') {
				next = Math.min(options.length - 1, activeIndex + 1);
			} else if (e.key === 'ArrowUp') {
				next = Math.max(0, activeIndex - 1);
			} else if (e.key === 'Home') {
				next = 0;
			} else if (e.key === 'End') {
				next = options.length - 1;
			} else if (e.key === 'Enter' || e.key === ' ') {
				const value = options[activeIndex].getAttribute('data-value');
				if (value) {
					selectOption(value);
				}
				close(true);
				return;
			} else if (e.key === 'Escape') {
				close(true);
				return;
			} else if (e.key === 'Tab') {
				// Let focus move naturally out of the panel.
				close(false);
				return;
			} else {
				handled = false;
			}
			if (handled) {
				e.preventDefault();
				if (next !== activeIndex && next >= 0) {
					activeIndex = next;
					syncUI(options[activeIndex].getAttribute('data-value'));
					options[activeIndex].focus();
				}
			}
		});

		// Click outside closes (without yanking focus — the user is going
		// somewhere else on purpose).
		document.addEventListener('click', function (e) {
			if (panel.hidden) {
				return;
			}
			if (!wrap.contains(e.target)) {
				close(false);
			}
		});

		/* ── Boot: read preference, sync UI, apply sort (no animation) ── */
		const initial = readInitialValue();
		syncUI(initial);
		applySort(initial, false); // apply on init so default = newest-first matches the trigger label
	}

	/* ── Select all + per-card checkboxes ──────────────────────────────────── */
	function initSelectAll() {
		const master = root.querySelector('[data-dg-wl-select-all]');
		const cards = root.querySelectorAll('[data-dg-wl-card]');
		if (!master || !cards.length) return;

		master.addEventListener('change', function () {
			const checked = master.checked;
			cards.forEach(function (card) {
				const cb = card.querySelector('[data-dg-wl-select]');
				if (!cb) return;
				if (cb.checked !== checked) {
					cb.checked = checked;
					card.classList.toggle('is-selected', checked);
				}
			});
			updateBulkBar();
		});

		cards.forEach(function (card) {
			const cb = card.querySelector('[data-dg-wl-select]');
			if (!cb) return;
			cb.addEventListener('change', function () {
				card.classList.toggle('is-selected', cb.checked);
				updateMasterCheckbox();
				updateBulkBar();
			});
		});
	}

	function updateMasterCheckbox() {
		const master = root.querySelector('[data-dg-wl-select-all]');
		const cards = root.querySelectorAll('[data-dg-wl-card]');
		if (!master || !cards.length) return;
		const visible = Array.from(cards).filter(function (c) { return c.style.display !== 'none'; });
		const checked = visible.filter(function (c) {
			const cb = c.querySelector('[data-dg-wl-select]');
			return cb && cb.checked;
		});
		master.checked = visible.length > 0 && checked.length === visible.length;
		master.indeterminate = checked.length > 0 && checked.length < visible.length;
	}

	/* ── Bulk bar visibility + counts ─────────────────────────────────────── */
	function initBulkBar() {
		const removeBtn = root.querySelector('[data-dg-wl-bulk-remove]');
		const addBtn = root.querySelector('[data-dg-wl-bulk-add]');

		if (addBtn) {
			addBtn.addEventListener('click', function () {
				if (bulkAddPending) {
					return;
				}
				const ids = getSelectedIds();
				if (!ids.length) {
					toast(i18n.selectItems || 'Select items to use bulk actions.', 'info');
					return;
				}
				bulkAddToBag(ids);
			});
		}

		if (removeBtn) {
			removeBtn.addEventListener('click', function () {
				const ids = getSelectedIds();
				if (!ids.length) {
					toast(i18n.selectItems || 'Select items to use bulk actions.', 'info');
					return;
				}
				askConfirm({ type: 'bulk', count: ids.length }).then(function (ok) {
					if (!ok) return;
					bulkRemove(ids);
				});
			});
		}
	}

	function updateBulkBar() {
		if (!bulkBar || !bulkCountEl) return;
		const ids = getSelectedIds();
		bulkCountEl.textContent = String(ids.length);
		bulkBar.classList.toggle('is-visible', ids.length > 0);

		const selectCountEl = root.querySelector('[data-dg-wl-selected-count]');
		if (selectCountEl) {
			selectCountEl.textContent = '(' + ids.length + ')';
		}
	}

	/** Immediately hide the bulk bar without reading DOM. Used by clear-all
	    optimistic path so the bar disappears the instant the user confirms. */
	function updateBulkBarImmediately() {
		if (!bulkBar || !bulkCountEl) return;
		bulkCountEl.textContent = '0';
		bulkBar.classList.remove('is-visible');
		const selectCountEl = root.querySelector('[data-dg-wl-selected-count]');
		if (selectCountEl) selectCountEl.textContent = '(0)';
	}

	function getSelectedIds() {
		return Array.from(root.querySelectorAll('[data-dg-wl-select]:checked')).map(function (cb) {
			return parseInt(cb.value, 10) || 0;
		}).filter(function (id) { return id > 0; });
	}

	function bulkRemove(ids) {
		const fd = new FormData();
		fd.append('action', 'dg_wishlist_remove_many');
		fd.append('nonce', dgAjax.nonce);
		fd.append('product_ids', ids.join(','));

		// Optimistic removal: remove cards IMMEDIATELY for instant feedback.
		const cards = ids.map(function (id) { return grid.querySelector('[data-product-id="' + id + '"]'); }).filter(Boolean);
		
		// Show toast immediately
		toast(
			ids.length === 1 
				? (i18n.removedSingle || '1 item removed from wishlist.')
				: (i18n.removed || ids.length + ' items removed from wishlist.'),
			'success'
		);
		
		// Remove cards from DOM immediately (no fade delay)
		cards.forEach(function (c) { c.remove(); });
		afterMutation({ removed: cards.length });

		// AJAX runs in background
		post(fd).then(function (data) {
			if (!data.success) {
				// If server fails, show error but keep the optimistic state
				// (simpler than trying to restore removed cards)
				toast((data.data && data.data.message) || 'Could not remove items from server.', 'error');
			}
		}).catch(function () {
			toast('Network error.', 'error');
		});
	}

	function bulkAddToBag(ids) {
		if (!window.DGCart || typeof window.DGCart.addMany !== 'function') {
			toast(i18n.cartUnavailable || 'Your bag is currently unavailable.', 'error');
			return;
		}

		// The template renders this CTA only for simple, in-stock, purchasable items.
		const directIds = getDirectAddableIds(ids);
		if (!directIds.length) {
			toast(i18n.noDirectItems || 'The selected items need options or are unavailable.', 'info');
			return;
		}

		bulkAddPending = true;
		setBulkActionPending(true);

		// Optimistic feedback: make the successful path feel immediate while the
		// server request continues in the background.
		clearSelectedIds(directIds);
		updateMasterCheckbox();
		updateBulkBar();
		bumpCartCountOptimistically(directIds.length);
		toast(i18n.optimisticAdded || 'Selected items added to your bag.', 'success');

		window.DGCart.addMany({ productIds: directIds })
			.then(function (data) {
				if (!data || !data.success) {
					restoreSelectedIds(directIds);
					updateMasterCheckbox();
					updateBulkBar();
					bumpCartCountOptimistically(-directIds.length);
					if (data && data.data && data.data.redirect) {
						window.location.href = data.data.redirect;
						return;
					}
					toast(
						(data && data.data && data.data.message) || i18n.addError || 'Could not add to bag.',
						'error'
					);
					return;
				}

				const result = data.data || {};
				const addedIds = normalizeIds(result.added_ids);
				const added = new Set(addedIds);
				const retryIds = directIds.filter(function (id) { return !added.has(id); });

				if (retryIds.length) {
					restoreSelectedIds(retryIds);
					updateMasterCheckbox();
					updateBulkBar();
					bumpCartCountOptimistically(-retryIds.length);
					toast(result.message || i18n.addError || 'Could not add to bag.', 'info');
				}

				if (addedIds.length && window.DGCart.refreshCount) {
					window.DGCart.refreshCount();
				}
			})
			.catch(function () {
				restoreSelectedIds(directIds);
				updateMasterCheckbox();
				updateBulkBar();
				bumpCartCountOptimistically(-directIds.length);
				toast(i18n.networkError || 'Network error. Please try again.', 'error');
			})
			.then(function () {
				bulkAddPending = false;
				setBulkActionPending(false);
			});
	}

	function getDirectAddableIds(ids) {
		return ids.filter(function (id) {
			const card = grid ? grid.querySelector('[data-product-id="' + id + '"]') : null;
			return card && card.querySelector('.wc-add-to-cart-btn');
		});
	}

	/**
	 * Lock bulk controls while the optimistic request is in flight.
	 * The button's icon and label stay unchanged; only interaction is locked.
	 *
	 * @param {boolean} pending
	 * @return {void}
	 */
	function setBulkActionPending(pending) {
		const buttons = root.querySelectorAll('[data-dg-wl-bulkbar] .dg-wishlist-btn');
		const checkboxes = root.querySelectorAll('[data-dg-wl-select], [data-dg-wl-select-all]');

		buttons.forEach(function (button) {
			button.disabled = pending;
		});
		checkboxes.forEach(function (checkbox) {
			checkbox.disabled = pending;
		});
	}

	function normalizeIds(value) {
		return Array.isArray(value)
			? value.map(function (id) { return parseInt(id, 10) || 0; }).filter(function (id) { return id > 0; })
			: [];
	}

	function clearSelectedIds(ids) {
		const selected = new Set(ids);
		root.querySelectorAll('[data-dg-wl-select]:checked').forEach(function (checkbox) {
			const id = parseInt(checkbox.value, 10) || 0;
			if (!selected.has(id)) {
				return;
			}
			checkbox.checked = false;
			const card = checkbox.closest('[data-dg-wl-card]');
			if (card) {
				card.classList.remove('is-selected');
			}
		});
	}

	function restoreSelectedIds(ids) {
		const selected = new Set(ids);
		root.querySelectorAll('[data-dg-wl-select]').forEach(function (checkbox) {
			const id = parseInt(checkbox.value, 10) || 0;
			if (!selected.has(id)) {
				return;
			}
			checkbox.checked = true;
			const card = checkbox.closest('[data-dg-wl-card]');
			if (card) {
				card.classList.add('is-selected');
			}
		});
	}

	/**
	 * Optimistically adjust the cart-count badge by the given amount.
	 *
	 * Positive values bump the count up (e.g. after a bulk-add); negative
	 * values roll back the optimistic change when the server confirms a
	 * failure. The badge lives in the header (outside the wishlist scope)
	 * so the query runs against `document`, matching `DGCart.refreshCount()`.
	 * The success handler in `bulkAddToBag()` still calls
	 * `DGCart.refreshCount()` so the server count can reconcile if
	 * partial-success drops any items.
	 *
	 * @param {number} by Signed integer — positive to add, negative to subtract.
	 * @return {void}
	 */
	function bumpCartCountOptimistically(by) {
		const delta = parseInt(by, 10) || 0;
		if (delta === 0) {
			return;
		}
		document.querySelectorAll('.dg-cart-count').forEach(function (el) {
			const current = parseInt(el.textContent, 10) || 0;
			const next = current + delta;
			el.textContent = String(next);
			el.classList.toggle('hidden', next <= 0);
		});
	}

	/* ── Single card remove (heart icon) ──────────────────────────────────── */
	function initSingleRemove() {
		if (!grid) return;
		grid.addEventListener('click', function (e) {
			const btn = e.target.closest('[data-dg-wl-remove]');
			if (!btn) return;

			// STOP PROPAGATION — prevent wishlist-toggle.js lib from also
			// handling this click. The lib checks for [data-dg-wl-remove]
			// and skips, but we stop bubbling here to be absolutely safe
			// and prevent any race condition with pending responses.
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();

			const card = btn.closest('[data-dg-wl-card]');
			if (!card) return;
			const productId = parseInt(card.getAttribute('data-product-id') || '0', 10);
			if (!productId) return;

			// Optimistic UI: remove card IMMEDIATELY + show toast.
			btn.classList.add('is-busy');
			toast(i18n.removedSingle || '1 item removed from wishlist.', 'success');
			
			// Remove card from DOM immediately (no fade delay)
			card.remove();
			afterMutation({ removed: 1 });

			// AJAX runs in background
			const fd = new FormData();
			fd.append('action', 'dg_wishlist_toggle');
			fd.append('nonce', dgAjax.nonce);
			fd.append('product_id', productId);

			post(fd).then(function (data) {
				if (!data.success) {
					// If server fails, show error but keep the optimistic state
					toast((data.data && data.data.message) || 'Could not remove from server.', 'error');
				}
			}).catch(function () {
				toast('Network error.', 'error');
			});
		});
	}

	/* ── Share modal ──────────────────────────────────────────────────────── */
	function initShareModal() {
		const modal = root.querySelector('[data-dg-wl-modal="share"]');
		if (!modal) return;
		const opens = root.querySelectorAll('[data-dg-wl-action="share"]');
		const closes = modal.querySelectorAll('[data-dg-wl-modal-close]');
		const form = modal.querySelector('[data-dg-wl-share-form]');
		const copyBtn = modal.querySelector('[data-dg-wl-share-copy]');
		const feedback = modal.querySelector('[data-dg-wl-share-feedback]');

		function open() {
			modal.hidden = false;
			modal.setAttribute('aria-hidden', 'false');
			if (feedback) { feedback.classList.remove('is-visible', 'is-error'); feedback.textContent = ''; }
			setTimeout(function () {
				const input = modal.querySelector('input[name="email"]');
				if (input) input.focus();
			}, 100);
		}
		function close() {
			modal.hidden = true;
			modal.setAttribute('aria-hidden', 'true');
		}

		opens.forEach(function (b) { b.addEventListener('click', open); });
		closes.forEach(function (b) { b.addEventListener('click', close); });

		// ESC closes.
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !modal.hidden) close();
		});

		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				const email = form.querySelector('input[name="email"]').value.trim();
				if (!email) return;

				const submitBtn = form.querySelector('button[type="submit"]');
				const originalText = submitBtn ? submitBtn.textContent : '';
				if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = '…'; }

				const fd = new FormData();
				fd.append('action', 'dg_wishlist_share');
				fd.append('nonce', dgAjax.nonce);
				fd.append('email', email);

				post(fd).then(function (data) {
					if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = originalText; }
					if (!data.success) {
						if (feedback) {
							feedback.textContent = (data.data && data.data.message) || 'Could not send.';
							feedback.classList.add('is-visible', 'is-error');
						}
						return;
					}
					if (feedback) {
						feedback.textContent = (data.data && data.data.message) || 'Wishlist shared.';
						feedback.classList.add('is-visible');
						feedback.classList.remove('is-error');
					}
					form.reset();
				}).catch(function () {
					if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = originalText; }
					if (feedback) {
						feedback.textContent = 'Network error.';
						feedback.classList.add('is-visible', 'is-error');
					}
				});
			});
		}

		if (copyBtn) {
			copyBtn.addEventListener('click', function () {
				const linkEl = modal.querySelector('[data-dg-wl-share-link]');
				const url = linkEl ? linkEl.value : '';
				if (!url) return;

				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(url).then(function () {
						showCopied(feedback, i18n.copied || 'Link copied.');
					}).catch(function () { fallbackCopy(linkEl, feedback, i18n); });
				} else {
					fallbackCopy(linkEl, feedback, i18n);
				}
			});
		}
	}

	function fallbackCopy(input, feedback, i18n) {
		if (!input) return;
		input.removeAttribute('readonly');
		input.select();
		try {
			document.execCommand('copy');
			showCopied(feedback, i18n.copied || 'Link copied.');
		} catch (e) {
			if (feedback) {
				feedback.textContent = 'Copy failed — please copy manually.';
				feedback.classList.add('is-visible', 'is-error');
			}
		}
		input.setAttribute('readonly', 'readonly');
		input.blur();
	}

	function showCopied(feedback, msg) {
		if (!feedback) return;
		feedback.textContent = msg;
		feedback.classList.add('is-visible');
		feedback.classList.remove('is-error');
		setTimeout(function () { feedback.classList.remove('is-visible'); }, 2500);
	}

	/* ── Initial empty-state sync ──────────────────────────────────────────
	   On page load, the template renders `empty-state` with `hidden`
	   unconditionally, and hides the grid-shell when there are no items.
	   On a fresh empty wishlist that means both are hidden — the user
	   sees a blank gap where the onboarding panel should be. Read the
	   server-rendered `data-empty` flag on the root and flip the empty
	   state visible so the initial render matches what the JS would
	   produce after a clear-all action. */
	function initInitialEmptyState() {
		if (!emptyState) return;
		const isEmpty = root.getAttribute('data-empty') === '1';
		emptyState.hidden = !isEmpty;
		if (isEmpty && shell) {
			shell.hidden = true;
		}
	}

	/* ── Clear all ──────────────────────────────────────────────────────────
	   Optimistic flow: as soon as the user confirms, fade the cards and
	   surface the success toast immediately. The AJAX round-trip runs in
	   parallel; on failure we reverse-animate the cards back to visible.

	   Note on the fade: the `.is-leaving` CSS rule does NOT actually fade
	   these cards because `initReveal()` sets `opacity: 1` as an inline
	   style via Motion, and inline styles win over class selectors. We
	   therefore animate with Motion directly so the fade is guaranteed
	   to take effect regardless of CSS specificity. */
	function initClearAll() {
		const btn = root.querySelector('[data-dg-wl-clear-all]');
		if (!btn) return;
		let pending = false;
		btn.addEventListener('click', function () {
			if (pending || !grid) return;
			askConfirm({ type: 'clear' }).then(function (ok) {
				if (!ok) return;

				const cards = Array.from(grid.querySelectorAll('[data-dg-wl-card]'));

				// Optimistic UI: remove cards IMMEDIATELY + show toast + update counts.
				toast(i18n.cleared || 'Your wishlist has been cleared.', 'success');
				
				// Remove cards from DOM immediately (no fade delay)
				cards.forEach(function (c) { c.remove(); });
				
				// Update stats and hide bulk bar immediately
				updateCountsImmediately(0, 0, 0);
				updateBulkBarImmediately();
				
				// Update header badge immediately
				updateWishlistBadge();
				
				// Show empty state immediately
				if (shell) shell.hidden = true;
				if (emptyState) {
					emptyState.hidden = false;
					if (!reduce) {
						animate(emptyState, { opacity: [0, 1], y: [20, 0] }, { duration: 0.5, ease: EASE });
					}
				}

				pending = true;
				btn.disabled = true;

				// AJAX runs in background
				const fd = new FormData();
				fd.append('action', 'dg_wishlist_clear');
				fd.append('nonce', dgAjax.nonce);
				post(fd).then(function (data) {
					pending = false;
					btn.disabled = false;
					if (!data.success) {
						// If server fails, show error but keep the optimistic state
						toast((data.data && data.data.message) || 'Could not clear on server.', 'error');
					}
				}).catch(function () {
					pending = false;
					btn.disabled = false;
					toast('Network error.', 'error');
				});
			});
		});
	}

	/* ── Confirm modal — glassmorphism confirm for destructive actions ──
	   askConfirm({ type, count }) opens the matching instance (bulk /
	   clear), updates the live count placeholders, focuses the cancel
	   button (safer default), and returns a Promise<boolean>. */
	let confirmResolver = null;
	let confirmModalEl = null;
	let confirmPrevFocus = null;

	function initConfirmModal() {
		const modals = document.querySelectorAll('[data-dg-wl-confirm]');
		if (!modals.length) return;
		modals.forEach(function (modal) {
			modal.addEventListener('click', function (e) {
				if (e.target.closest('[data-dg-wl-confirm-close]')) {
					closeConfirm(modal, false);
				} else if (e.target.closest('[data-dg-wl-confirm-yes]')) {
					closeConfirm(modal, true);
				}
			});
		});
		document.addEventListener('keydown', function (e) {
			if (e.key !== 'Escape') return;
			modals.forEach(function (m) {
				if (!m.hidden) closeConfirm(m, false);
			});
		});
	}

	function askConfirm(opts) {
		opts = opts || {};
		const type = opts.type || 'bulk';
		const count = opts.count || 0;
		const modal = document.querySelector('[data-dg-wl-confirm="' + type + '"]');
		if (!modal) {
			// Defensive fallback — modal markup missing. Preserve the old
			// native confirm() path so the destructive action still gates.
			return Promise.resolve(window.confirm(opts.message || ''));
		}

		modal.querySelectorAll('[data-dg-wl-confirm-count]').forEach(function (el) {
			el.textContent = String(count);
		});
		if (type === 'bulk') {
			const labelEl = modal.querySelector('[data-dg-wl-confirm-confirm-label]');
			if (labelEl) {
				labelEl.textContent = sprintf(
					i18n.confirmRemoveLabel || 'Remove %d items',
					count
				);
			}
		}

		confirmPrevFocus = document.activeElement;
		modal.hidden = false;
		modal.setAttribute('aria-hidden', 'false');
		confirmModalEl = modal;

		// Focus the safer default — Cancel. Tiny defer so the modal
		// renders before focus() runs (Firefox quirk).
		const cancelBtn = modal.querySelector('[data-dg-wl-confirm-close]');
		if (cancelBtn) {
			setTimeout(function () { cancelBtn.focus(); }, 50);
		}

		return new Promise(function (resolve) { confirmResolver = resolve; });
	}

	function closeConfirm(modal, ok) {
		if (!modal) return;
		modal.hidden = true;
		modal.setAttribute('aria-hidden', 'true');
		if (confirmModalEl === modal) confirmModalEl = null;
		if (confirmResolver) {
			confirmResolver(ok);
			confirmResolver = null;
		}
		if (confirmPrevFocus && confirmPrevFocus.focus) {
			try { confirmPrevFocus.focus({ preventScroll: true }); } catch (e) { /* noop */ }
		}
		confirmPrevFocus = null;
	}

	/* ── After any mutation: update counts + show/hide empty state ────────── */
	function afterMutation(opts) {
		updateCounts();
		updateBulkBar();
		updateWishlistBadge(); // Update header badge immediately

		const remaining = grid ? grid.querySelectorAll('[data-dg-wl-card]').length : 0;
		if (remaining === 0) {
			if (shell) shell.hidden = true;
			if (emptyState) emptyState.hidden = false;
			if (!reduce) {
				animate(
					emptyState,
					{ opacity: [0, 1], y: [20, 0] },
					{ duration: 0.5, ease: EASE }
				);
			}
		} else if (noResult) {
			noResult.hidden = true;
		}

		// Toast is now shown directly in remove functions (optimistic UI)
		// so we don't need to show it again here.
	}

	function updateCounts() {
		if (!grid) return;
		const cards = Array.from(grid.querySelectorAll('[data-dg-wl-card]'));
		const total = cards.length;
		const inStock = cards.filter(function (c) { return c.getAttribute('data-in-stock') === '1'; }).length;
		const onSale = cards.filter(function (c) { return c.getAttribute('data-on-sale') === '1'; }).length;

		const statTotal = root.querySelector('[data-dg-wl-stat="total"]');
		const statStock = root.querySelector('[data-dg-wl-stat="in_stock"]');
		const statSale = root.querySelector('[data-dg-wl-stat="on_sale"]');

		if (statTotal) {
			statTotal.setAttribute('data-count-to', String(total));
			statTotal.textContent = String(total);
		}
		if (statStock) statStock.textContent = String(inStock);
		if (statSale)  statSale.textContent  = String(onSale);

		const segTotal = root.querySelector('[data-dg-wl-count="all"]');
		const segStock = root.querySelector('[data-dg-wl-count="in_stock"]');
		const segSale  = root.querySelector('[data-dg-wl-count="on_sale"]');
		if (segTotal) segTotal.textContent = String(total);
		if (segStock) segStock.textContent = String(inStock);
		if (segSale)  segSale.textContent  = String(onSale);

		// Saved-amount stat is rendered server-side; skip updating here for now.
	}

	/** Immediately set stat + segment counters without reading DOM.
	    Used by the clear-all optimistic path to zero the counters the
	    instant the user confirms — no waiting for AJAX or DOM re-read. */
	function updateCountsImmediately(total, inStock, onSale) {
		total   = total   || 0;
		inStock = inStock || 0;
		onSale  = onSale  || 0;

		const statTotal = root.querySelector('[data-dg-wl-stat="total"]');
		const statStock = root.querySelector('[data-dg-wl-stat="in_stock"]');
		const statSale  = root.querySelector('[data-dg-wl-stat="on_sale"]');

		if (statTotal) {
			statTotal.setAttribute('data-count-to', String(total));
			statTotal.textContent = String(total);
		}
		if (statStock) statStock.textContent = String(inStock);
		if (statSale)  statSale.textContent  = String(onSale);

		const segTotal = root.querySelector('[data-dg-wl-count="all"]');
		const segStock = root.querySelector('[data-dg-wl-count="in_stock"]');
		const segSale  = root.querySelector('[data-dg-wl-count="on_sale"]');
		if (segTotal) segTotal.textContent = String(total);
		if (segStock) segStock.textContent = String(inStock);
		if (segSale)  segSale.textContent  = String(onSale);
	}

	/** Update wishlist badge in header (optimistic) — instant visual feedback
	    by reading the current grid DOM count, no AJAX wait. */
	function updateWishlistBadge() {
		if (!grid) return;
		const count = grid.querySelectorAll('[data-dg-wl-card]').length;
		document.querySelectorAll('.dg-wishlist-count').forEach(function (el) {
			el.textContent = String(count);
			el.classList.toggle('hidden', count === 0);
		});
	}

	/* ── Refresh header badge (called from other pages too) ───────────────── */
	function refreshCount() {
		if (!window.dgAjax) return Promise.resolve(0);
		const fd = new FormData();
		fd.append('action', 'dg_wishlist_count');
		fd.append('nonce', dgAjax.nonce);
		return post(fd).then(function (data) {
			if (!data || !data.success) return 0;
			const count = (data.data && data.data.count) || 0;
			document.querySelectorAll('.dg-wishlist-count').forEach(function (el) {
				el.textContent = String(count);
				el.classList.toggle('hidden', count === 0);
			});
			return count;
		}).catch(function () { return 0; });
	}

	/* ── Toasts ───────────────────────────────────────────────────────────── */
	function toast(message, type) {
		if (!toasts || !message) return;
		type = type || 'info';
		const icons = { success: 'check_circle', info: 'info', error: 'error' };
		const el = document.createElement('div');
		el.className = 'dg-wishlist-toast dg-wishlist-toast--' + type;
		el.innerHTML =
			'<span class="dg-wishlist-toast__icon"><span class="material-symbols-outlined">' + (icons[type] || 'info') + '</span></span>' +
			'<span class="dg-wishlist-toast__body">' + escapeHtml(message) + '</span>';
		toasts.appendChild(el);
		setTimeout(function () {
			el.classList.add('is-leaving');
			setTimeout(function () { el.remove(); }, 320);
		}, 3500);
	}

	/* ── Helpers ──────────────────────────────────────────────────────────── */
	function post(fd) {
		return fetch(window.dgAjax.url, {
			method: 'POST',
			body: fd,
			credentials: 'same-origin',
		}).then(function (r) { return r.json(); });
	}

	function sprintf(format) {
		var args = Array.prototype.slice.call(arguments, 1);
		var i = 0;
		return format.replace(/%d/g, function () { return args[i++]; });
	}

	function escapeHtml(s) {
		return String(s)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

})();
