/**
 * Dragon Glow — Wishlist Page JS
 * ES Module — Motion (motion.dev) vanilla API. KHÔNG React.
 *
 * Responsibilities:
 *   - Reveal: stagger các phần tử data-sr (Motion inView + animate + stagger).
 *   - Stat count-up cho [data-count-to] (mirror account.js).
 *   - Filter (All / In stock / On sale) + sort (date / price-asc / price-desc
 *     / name) — client-side, no AJAX. Hides non-matching cards and reorders
 *     the remaining ones with Motion.
 *   - Bulk select: master checkbox + per-card checkboxes; floating bulk bar
 *     fades in when ≥ 1 card is selected. Bulk remove / bulk add-to-bag.
 *   - Optimize: single-card remove (heart icon on the card) with optimistic
 *     UI + rollback on error.
 *   - Share modal: email share (POST → dg_wishlist_share) + copy-link button.
 *   - Clear wishlist (with native confirm()).
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

	// Boot.
	initReveal();
	initCountTo();
	initFilter();
	initSort();
	initSelectAll();
	initSingleRemove();
	initBulkBar();
	initShareModal();
	initClearAll();
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

	/* ── Sort ─────────────────────────────────────────────────────────────── */
	function initSort() {
		const select = root.querySelector('[data-dg-wl-sort]');
		if (!select || !grid) return;

		select.addEventListener('change', function () {
			const mode = select.value;
			const cards = Array.from(grid.querySelectorAll('[data-dg-wl-card]'));

			cards.sort(function (a, b) {
				const aPrice = parseFloat(a.getAttribute('data-price') || '0');
				const bPrice = parseFloat(b.getAttribute('data-price') || '0');
				const aName = (a.getAttribute('data-name') || '').toLowerCase();
				const bName = (b.getAttribute('data-name') || '').toLowerCase();

				switch (mode) {
					case 'price-asc':  return aPrice - bPrice;
					case 'price-desc': return bPrice - aPrice;
					case 'name':       return aName.localeCompare(bName);
					case 'date':
					default:           return 0; // SSR order = save order
				}
			});

			// Reattach in new order with Motion FLIP-like animation.
			const frag = document.createDocumentFragment();
			cards.forEach(function (c) { frag.appendChild(c); });
			grid.appendChild(frag);

			if (!reduce) {
				animate(
					cards,
					{ opacity: [0.6, 1], scale: [0.98, 1] },
					{ duration: 0.3, ease: EASE, delay: stagger(0.02) }
				);
			}
		});
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

		if (removeBtn) {
			removeBtn.addEventListener('click', function () {
				const ids = getSelectedIds();
				if (!ids.length) {
					toast(i18n.selectItems || 'Select items to use bulk actions.', 'info');
					return;
				}
				if (!confirm(sprintf(i18n.confirmClear || 'Remove %d item(s) from your wishlist?', ids.length))) {
					return;
				}
				bulkRemove(ids);
			});
		}

		if (addBtn) {
			addBtn.addEventListener('click', function () {
				const ids = getSelectedIds();
				if (!ids.length) {
					toast(i18n.selectItems || 'Select items to use bulk actions.', 'info');
					return;
				}
				bulkAddToBag(ids);
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

		// Optimistic removal.
		const cards = ids.map(function (id) { return grid.querySelector('[data-product-id="' + id + '"]'); }).filter(Boolean);
		cards.forEach(function (c) { c.classList.add('is-leaving'); });

		post(fd).then(function (data) {
			if (!data.success) {
				cards.forEach(function (c) { c.classList.remove('is-leaving'); });
				toast((data.data && data.data.message) || 'Could not remove items.', 'error');
				return;
			}
			setTimeout(function () {
				cards.forEach(function (c) { c.remove(); });
				afterMutation({ removed: cards.length });
			}, 350);
		}).catch(function () {
			cards.forEach(function (c) { c.classList.remove('is-leaving'); });
			toast('Network error.', 'error');
		});
	}

	function bulkAddToBag(ids) {
		if (!window.DGCart) {
			toast('Cart is not available.', 'error');
			return;
		}

		// Sequence-add with progress feedback.
		let pending = ids.length;
		let failed = 0;
		ids.forEach(function (id) {
			const card = grid.querySelector('[data-product-id="' + id + '"]');
			const cta = card ? card.querySelector('.wc-add-to-cart-btn') : null;
			if (cta) {
				cta.setAttribute('disabled', 'disabled');
				cta.dataset.originalText = cta.textContent;
				cta.textContent = i18n.added ? '…' : '…';
			}

			window.DGCart.add({
				productId: id,
				slug: card ? (card.querySelector('.wc-add-to-cart-btn')?.dataset.productSlug || '') : '',
				quantity: 1,
			}).then(function (data) {
				if (!data.success) failed++;
				if (cta) {
					cta.textContent = data.success ? '✓' : (cta.dataset.originalText || '');
					setTimeout(function () {
						cta.removeAttribute('disabled');
						cta.textContent = cta.dataset.originalText || cta.textContent;
					}, 1200);
				}
			}).catch(function () {
				failed++;
			}).then(function () {
				pending--;
				if (pending === 0) {
					if (window.DGCart.refreshCount) window.DGCart.refreshCount();
					if (failed === 0) {
						toast('Added ' + ids.length + ' item(s) to your bag.', 'success');
					} else if (failed === ids.length) {
						toast('Could not add to bag.', 'error');
					} else {
						toast(failed + ' item(s) failed to add.', 'info');
					}
				}
			});
		});
	}

	/* ── Single card remove (heart icon) ──────────────────────────────────── */
	function initSingleRemove() {
		if (!grid) return;
		grid.addEventListener('click', function (e) {
			const btn = e.target.closest('[data-dg-wl-remove]');
			if (!btn) return;
			const card = btn.closest('[data-dg-wl-card]');
			if (!card) return;
			const productId = parseInt(card.getAttribute('data-product-id') || '0', 10);
			if (!productId) return;

			// Optimistic animation.
			btn.classList.add('is-busy');
			card.classList.add('is-leaving');

			const fd = new FormData();
			fd.append('action', 'dg_wishlist_toggle');
			fd.append('nonce', dgAjax.nonce);
			fd.append('product_id', productId);

			post(fd).then(function (data) {
				if (!data.success) {
					btn.classList.remove('is-busy');
					card.classList.remove('is-leaving');
					toast((data.data && data.data.message) || 'Could not remove.', 'error');
					return;
				}
				setTimeout(function () {
					card.remove();
					afterMutation({ removed: 1 });
				}, 320);
			}).catch(function () {
				btn.classList.remove('is-busy');
				card.classList.remove('is-leaving');
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

	/* ── Clear all ────────────────────────────────────────────────────────── */
	function initClearAll() {
		const btn = root.querySelector('[data-dg-wl-clear-all]');
		if (!btn) return;
		btn.addEventListener('click', function () {
			if (!confirm(i18n.confirmClear || 'Remove every item from your wishlist?')) {
				return;
			}
			const fd = new FormData();
			fd.append('action', 'dg_wishlist_clear');
			fd.append('nonce', dgAjax.nonce);
			post(fd).then(function (data) {
				if (!data.success) {
					toast((data.data && data.data.message) || 'Could not clear.', 'error');
					return;
				}
				// Empty out the grid.
				if (grid) {
					const cards = grid.querySelectorAll('[data-dg-wl-card]');
					cards.forEach(function (c) { c.classList.add('is-leaving'); });
					setTimeout(function () {
						cards.forEach(function (c) { c.remove(); });
						afterMutation({ removed: cards.length });
					}, 320);
				}
			}).catch(function () {
				toast('Network error.', 'error');
			});
		});
	}

	/* ── After any mutation: update counts + show/hide empty state ────────── */
	function afterMutation(opts) {
		updateCounts();
		updateBulkBar();

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

		if (opts && opts.removed) {
			toast(
				(opts.removed === 1
					? (i18n.removed || 'Removed from your wishlist.')
					: sprintf('%d items removed.', opts.removed)),
				'success'
			);
		}
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
