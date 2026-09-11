/**
 * Dragon Glow — Cart Page JS
 *
 * Cart page interactions:
 *   - Per-row bulk-select checkboxes + master "select all" toggle the
 *     floating bulk action bar (visible whenever ≥ 1 row is checked).
 *   - Quantity steppers use the existing happy path.
 *   - Bulk remove: confirm modal → optimistic UI → DGCart.removeMany →
 *     reload to refresh totals. Rollback on failure.
 *   - Clear cart: confirm modal → optimistic UI → DGCart.clear →
 *     reload to refresh totals + show the empty cart state. Rollback
 *     on failure.
 *   - Parallax blobs (unchanged).
 *
 * The AJAX, optimistic-UI, modal, and toast primitives live in this
 * file so the page works without external modules. For non-UI cart
 * operations (add, refresh count, …) we delegate to window.DGCart
 * (assets/js/lib/cart-api.js) — single source of truth.
 *
 * Dependencies:
 *   - window.dgAjax  (url + nonce) — localized on dg-main.
 *   - window.DGCart  (add, removeMany, clear, refreshCount) — dg-cart-api.
 *
 * Tôn trọng prefers-reduced-motion.
 *
 * @package Dragon_Glow
 */
(function () {
    'use strict';

    // ── Debug gate ────────────────────────────────────────────────────────────
    // window.dgDebug.enabled is localized on dg-main (see inc/enqueue/scripts.php).
    // In production it is false (WP_DEBUG off) so warn/info/debug stay quiet;
    // only console.error passes through. When WP_DEBUG is true the full set
    // is emitted so developers can triage without re-instrumenting.
    //
    // Always-on errors stay as console.error at the call site to keep the
    // happy path zero-cost in production.
    var DG_DEBUG = !!(window.dgDebug && window.dgDebug.enabled);
    function dgLog(level, msg, extra) {
        if (level === 'error') return console.error(msg, extra);
        if (!DG_DEBUG) return;
        if (level === 'warn')  return console.warn(msg, extra);
        if (level === 'info')  return console.info(msg, extra);
        if (level === 'debug') return console.debug(msg, extra);
    }

    if (typeof console !== 'undefined' && console.info) {
        dgLog('info', '%c[Dragon Glow] cart.js loaded', 'color:#735c00;font-weight:bold;');
    }

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    // Prefer the page-specific i18n (`dgCart`), fall back to the global
    // (`dgAjax`) so strings stay translatable per surface.
    var i18n = ((window.dgCart && window.dgCart.i18n) || (window.dgAjax && window.dgAjax.i18n) || {});

    // ── Defensive fallback for stale cart-api.js cache ─────────────────────
    // Why this exists:
    //   We hit a real-world case where the browser (or a CDN / hosting
    //   cache in front of WordPress) served an older cart-api.js under
    //   the new ?ver=… URL. Hard reloads did not always clear it. The
    //   stale build pre-dates the bulk-remove API, so window.DGCart was
    //   missing `removeMany` and `clear` — the cart page then showed a
    //   generic "Your bag is currently unavailable" toast on every bulk
    //   action, with no other clue to the developer.
    //
    // What this does:
    //   If the fresh cart-api.js is already on the page, the polyfills
    //   below are skipped — zero behavioural change. Otherwise they
    //   install a self-contained implementation of the two missing
    //   methods so the cart page keeps working while the cache is fixed.
    //
    // Self-contained means no shared code with cart-api.js — duplicating
    // a small POST helper is cheaper than coupling the cart page to a
    // cache that may not have invalidated yet.
    (function ensureBulkApi() {
        var hasDGCart = typeof window.DGCart === 'object' && window.DGCart !== null;
        var hasRemoveMany = hasDGCart && typeof window.DGCart.removeMany === 'function';
        var hasClear      = hasDGCart && typeof window.DGCart.clear      === 'function';
        if (hasRemoveMany && hasClear) {
            return; // Fresh cart-api.js — nothing to polyfill.
        }

        if (typeof console !== 'undefined' && console.warn) {
            dgLog(
                'warn',
                '[Dragon Glow] cart.js: cart-api.js cache is stale — installing inline fallback for',
                { removeMany: !hasRemoveMany, clear: !hasClear },
                'Purge server/CDn cache to silence this warning.'
            );
        }

        if (!hasDGCart) {
            window.DGCart = {};
        }

        // Minimal POST helper — same shape as cart-api.js#post() but kept
        // inline so it works even if the cached module didn't load.
        function fallbackPost(action, extras) {
            var url   = (window.dgAjax && window.dgAjax.url)   || '/wp-admin/admin-ajax.php';
            var nonce = (window.dgAjax && window.dgAjax.nonce) || '';
            var fd    = new FormData();
            fd.append('action', action);
            fd.append('nonce',  nonce);
            if (extras) {
                Object.keys(extras).forEach(function (key) {
                    var value = extras[key];
                    if (value !== undefined && value !== null) {
                        fd.append(key, value);
                    }
                });
            }
            return fetch(url, {
                method:      'POST',
                body:        fd,
                credentials: 'same-origin'
            }).then(function (r) { return r.json(); });
        }

        if (!hasRemoveMany) {
            window.DGCart.removeMany = function (opts) {
                opts = opts || {};
                var keys = Array.isArray(opts.cartItemKeys)
                    ? opts.cartItemKeys.filter(function (k) { return typeof k === 'string' && k.length > 0; })
                    : [];
                return fallbackPost('dg_ajax_remove_cart_items', {
                    cart_item_keys: keys.join(','),
                });
            };
        }

        if (!hasClear) {
            window.DGCart.clear = function () {
                return fallbackPost('dg_ajax_clear_cart', {});
            };
        }
    })();

    // ── DOM ready ─────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', init);

    function init() {
        bindQtySteppers();
        initParallaxBlobs();
        initBulkSelect();
        initBulkActions();
        initConfirmModal();
    }

    // ── Bulk select (visual only — pure state for the bulkbar) ──────────────
    // Per-row checkboxes + master "select all". Toggles the .is-selected
    // outline on each row and the floating bulk action bar's visibility.

    function initBulkSelect() {
        var master = document.querySelector('[data-dg-cart-select-all]');
        var rows   = document.querySelectorAll('[data-dg-cart-select]');
        if (!master && !rows.length) {
            return;
        }

        if (master) {
            master.addEventListener('change', function () {
                var checked = master.checked;
                rows.forEach(function (cb) {
                    if (cb.checked !== checked) {
                        cb.checked = checked;
                    }
                    var row = cb.closest('.dg-cart-row');
                    if (row) row.classList.toggle('is-selected', checked);
                });
                updateBulkBar();
            });
        }

        rows.forEach(function (cb) {
            cb.addEventListener('change', function () {
                var row = cb.closest('.dg-cart-row');
                if (row) row.classList.toggle('is-selected', cb.checked);
                updateMasterCheckbox();
                updateBulkBar();
            });
        });
    }

    function updateMasterCheckbox() {
        var master = document.querySelector('[data-dg-cart-select-all]');
        var cbs    = document.querySelectorAll('[data-dg-cart-select]');
        if (!master || !cbs.length) return;
        var total = cbs.length;
        var checked = 0;
        cbs.forEach(function (cb) { if (cb.checked) checked++; });
        master.checked  = total > 0 && checked === total;
        master.indeterminate = checked > 0 && checked < total;
    }

    function getSelectedKeys() {
        var keys = [];
        document.querySelectorAll('[data-dg-cart-select]:checked').forEach(function (cb) {
            var k = cb.value || cb.dataset.cartKey;
            if (k) keys.push(k);
        });
        return keys;
    }

    function updateBulkBar() {
        var bar = document.querySelector('[data-dg-cart-bulkbar]');
        if (!bar) return;
        var countEl = document.querySelector('[data-dg-cart-bulk-count]');
        var keys    = getSelectedKeys();
        if (countEl) countEl.textContent = String(keys.length);
        bar.classList.toggle('is-visible', keys.length > 0);
    }

    function resetSelection() {
        document.querySelectorAll('[data-dg-cart-select]:checked').forEach(function (cb) {
            cb.checked = false;
            var row = cb.closest('.dg-cart-row');
            if (row) row.classList.remove('is-selected');
        });
        updateMasterCheckbox();
        updateBulkBar();
    }

    // ── Bulk actions (Remove selected + Clear cart) ─────────────────────────

    function initBulkActions() {
        var removeBtn = document.querySelector('[data-dg-cart-bulk-remove]');
        var clearBtn  = document.querySelector('[data-dg-cart-clear-all]');
        if (!removeBtn && !clearBtn) return;

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                if (removeBtn.disabled) return;
                var keys = getSelectedKeys();
                if (!keys.length) {
                    showToast(i18n.selectItems || 'Select items first.', 'info');
                    return;
                }
                askConfirm({ type: 'bulk', count: keys.length }).then(function (ok) {
                    if (!ok) return;
                    bulkRemove(keys);
                });
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (clearBtn.disabled) return;
                askConfirm({ type: 'clear' }).then(function (ok) {
                    if (!ok) return;
                    clearCart();
                });
            });
        }
    }

    /**
     * Bulk remove selected cart items.
     *
     * Flow:
     *   1. Lock all bulk controls (prevent double-click).
     *   2. Optimistic UI: show toast + fade-out animation + reset selection IMMEDIATELY.
     *   3. POST to dg_ajax_remove_cart_items in parallel.
     *   4a. On success (cart still has items): remove DOM after fade, reload.
     *   4b. On success (cart now empty): show empty state IMMEDIATELY, skip reload.
     *   5. On failure: show error toast, reload to restore correct state.
     */
    function bulkRemove(keys) {
        if (!window.DGCart || typeof window.DGCart.removeMany !== 'function') {
            dgLog(
                'warn',
                '[Dragon Glow] bulkRemove: window.DGCart.removeMany is unavailable.',
                { hasDGCart: typeof window.DGCart, keys: window.DGCart && Object.keys(window.DGCart) }
            );
            showToast(i18n.bagUnavailable || 'Your bag is currently unavailable. Please try again later.', 'error');
            return;
        }

        var rows = keys
            .map(function (k) {
                return document.querySelector('.dg-cart-row[data-cart-key="' + cssEscape(k) + '"]');
            })
            .filter(function (row) { return row; });

        if (!rows.length) {
            var stillChecked = document.querySelectorAll('[data-dg-cart-select]:checked').length;
            if (stillChecked === 0) {
                showToast(i18n.selectItems || 'Select items first.', 'info');
            } else {
                dgLog(
                    'warn',
                    '[Dragon Glow] bulkRemove: ' + stillChecked + ' checkbox(es) ticked but no matching rows found. ...',
                    { requestedKeys: keys }
                );
                showToast(i18n.bagUnavailable || 'Your bag is currently unavailable. Please try again later.', 'error');
            }
            return;
        }

        var totalRows = document.querySelectorAll('.dg-cart-row').length;
        var wouldEmpty = rows.length >= totalRows;

        setBusy(true);

        // Optimistic UI: show success toast + fade-out IMMEDIATELY for instant feedback.
        var count = keys.length;
        var optimisticMsg = sprintf(
            count === 1 
                ? (i18n.removedSingle || '%d item removed from your bag.')
                : (i18n.removed || '%d items removed from your bag.'),
            count
        );
        showToast(optimisticMsg, 'success');

        // Update cart count badge IMMEDIATELY (optimistic).
        updateCartCountBadge(totalRows - rows.length);

        rows.forEach(function (row) { row.classList.add('is-removing'); });
        resetSelection();

        var fadeMs = reduce ? 0 : 480;

        // Special case: removing last item(s) → show empty state IMMEDIATELY.
        if (wouldEmpty) {
            rows.forEach(function (row) { row.remove(); });
            swapToEmptyState();
        }

        window.DGCart.removeMany({ cartItemKeys: keys })
            .then(function (response) {
                if (response === 0 || response === '0' || response === '' || response === null) {
                    dgLog('warn', '[Dragon Glow] bulkRemove: server returned empty/unknown response. Check that the dg_ajax_remove_cart_items action is registered and the nonce is valid.');
                    showToast(i18n.bagUnavailable || 'Your bag is currently unavailable. Please try again later.', 'error');
                    if (!wouldEmpty) {
                        setTimeout(function () { window.location.reload(); }, fadeMs);
                    }
                    return;
                }

                var data = response && response.data;
                if (!response || !response.success) {
                    var msg = (data && data.message) || i18n.bagUnavailable || 'Could not remove items.';
                    showToast(msg, 'error');
                    if (!wouldEmpty) {
                        setTimeout(function () { window.location.reload(); }, fadeMs);
                    }
                    return;
                }

                // SUCCESS: server confirmed (toast already shown optimistically).

                // Update header cart count if available in response.
                if (data.cart_count !== undefined && window.DGCart && window.DGCart.refreshCount) {
                    window.DGCart.refreshCount();
                }

                // If cart still has items → reload after fade to update totals.
                // If cart is empty → already showed empty state, no reload needed.
                if (!wouldEmpty) {
                    setTimeout(function () {
                        rows.forEach(function (row) { row.remove(); });
                        window.location.reload();
                    }, fadeMs);
                }
            })
            .catch(function (err) {
                dgLog('warn', '[Dragon Glow] bulkRemove: fetch/parse failure. Inspect Network tab for the actual response.', err);
                showToast(i18n.network || 'Network error. Please try again.', 'error');
                // Reload after fade to restore correct state (only if cart not empty).
                if (!wouldEmpty) {
                    setTimeout(function () { window.location.reload(); }, fadeMs);
                }
            });
    }

    /**
     * Clear the entire cart.
     *
     * Flow:
     *   1. Lock all bulk controls (prevent double-click).
     *   2. Optimistic UI: show toast + remove rows + show empty state IMMEDIATELY.
     *   3. POST to dg_ajax_clear_cart in parallel.
     *   4. On success: update cart count, no reload needed.
     *   5. On failure: show error toast (state already changed, keep it simple).
     */
    function clearCart() {
        if (!window.DGCart || typeof window.DGCart.clear !== 'function') {
            dgLog(
                'warn',
                '[Dragon Glow] clearCart: window.DGCart.clear is unavailable.',
                { hasDGCart: typeof window.DGCart, keys: window.DGCart && Object.keys(window.DGCart) }
            );
            showToast(i18n.bagUnavailable || 'Your bag is currently unavailable. Please try again later.', 'error');
            return;
        }

        var rows = Array.prototype.slice.call(document.querySelectorAll('.dg-cart-row'));
        if (!rows.length) {
            swapToEmptyState();
            showToast(i18n.alreadyEmpty || 'Your bag is already empty.', 'info');
            return;
        }

        setBusy(true);

        // Optimistic UI: show success toast + remove rows + show empty state IMMEDIATELY.
        showToast(i18n.cleared || 'Your bag has been cleared.', 'success');
        
        // Update cart count badge to 0 IMMEDIATELY (optimistic).
        updateCartCountBadge(0);
        
        rows.forEach(function (row) { row.remove(); });
        resetSelection();
        swapToEmptyState();

        window.DGCart.clear()
            .then(function (response) {
                if (response === 0 || response === '0' || response === '' || response === null) {
                    dgLog('warn', '[Dragon Glow] clearCart: server returned empty/unknown response. Check that the dg_ajax_clear_cart action is registered.');
                    showToast(i18n.bagUnavailable || 'Your bag is currently unavailable. Please try again later.', 'error');
                    return;
                }

                var data = response && response.data;
                if (!response || !response.success) {
                    var msg = (data && data.message) || i18n.bagUnavailable || 'Could not clear your bag.';
                    showToast(msg, 'error');
                    return;
                }

                // SUCCESS: server confirmed (toast already shown optimistically).

                // Update header cart count if available.
                if (data.cart_count !== undefined && window.DGCart && window.DGCart.refreshCount) {
                    window.DGCart.refreshCount();
                }
            })
            .catch(function (err) {
                dgLog('warn', '[Dragon Glow] clearCart: fetch/parse failure. Inspect Network tab for the actual response.', err);
                showToast(i18n.network || 'Network error. Please try again.', 'error');
            });
    }

    /**
     * Lock or unlock every interactive element on the cart page that
     * could mutate cart state. Prevents double-submits and stops the
     * user from selecting more rows mid-flight.
     */
    function setBusy(busy) {
        var bar = document.querySelector('[data-dg-cart-bulkbar]');
        if (!bar) return;
        bar.querySelectorAll('button').forEach(function (b) { b.disabled = busy; });
        document.querySelectorAll('[data-dg-cart-select], [data-dg-cart-select-all]').forEach(function (cb) {
            cb.disabled = busy;
        });
        document.querySelectorAll('.dg-qty-stepper-btn').forEach(function (b) {
            b.disabled = busy || (parseInt(b.closest('.dg-qty-stepper').dataset.qty, 10) <= 1 && b.classList.contains('dg-qty-decrease'));
        });
    }

    /**
     * Hide the cart view + bulk bar; show the empty-cart state.
     * Idempotent — safe to call from both optimistic UI and rollback paths.
     */
    function swapToEmptyState() {
        var view   = document.getElementById('dg-cart-view');
        var bar    = document.querySelector('[data-dg-cart-bulkbar]');
        var empty  = document.getElementById('dg-empty-cart-view');
        if (view) view.style.display = 'none';
        if (bar)  bar.classList.remove('is-visible');
        if (empty) {
            empty.classList.add('is-visible');
            empty.classList.add('dg-empty-cart-clone');
        }
    }

    // ── Confirm modal — Promise-based, mirrors the wishlist pattern ────────
    // askConfirm({type, count}) opens the matching instance, returns
    // a Promise<boolean>. ESC and overlay-click resolve to false.
    // Focus management: Cancel button (safer default) on open; restore
    // previous focus on close.

    var confirmResolver = null;
    var confirmModalEl  = null;
    var confirmPrevFocus = null;

    function initConfirmModal() {
        var modals = document.querySelectorAll('[data-dg-cart-confirm]');
        if (!modals.length) return;

        modals.forEach(function (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target.closest('[data-dg-cart-confirm-close]')) {
                    closeConfirm(modal, false);
                } else if (e.target.closest('[data-dg-cart-confirm-yes]')) {
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
        var type  = opts.type || 'bulk';
        var count = opts.count || 0;
        var modal = document.querySelector('[data-dg-cart-confirm="' + type + '"]');
        if (!modal) {
            // Defensive fallback — modal markup missing. Preserve the
            // old native confirm() path so the destructive action still
            // gates without crashing.
            return Promise.resolve(window.confirm(opts.message || ''));
        }

        // Update live count placeholders inside the modal so the user
        // sees the exact number they're about to remove.
        modal.querySelectorAll('[data-dg-cart-confirm-count]').forEach(function (el) {
            el.textContent = String(count);
        });
        if (type === 'bulk') {
            var labelEl = modal.querySelector('[data-dg-cart-confirm-confirm-label]');
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
        var cancelBtn = modal.querySelector('[data-dg-cart-confirm-close]');
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
        if (confirmPrevFocus && typeof confirmPrevFocus.focus === 'function') {
            try {
                confirmPrevFocus.focus({ preventScroll: true });
            } catch (e) {
                /* noop — older browsers */
            }
        }
        confirmPrevFocus = null;
    }

    // ── Quantity steppers (unchanged happy path) ───────────────────────────

    function bindQtySteppers() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.dg-qty-stepper-btn');
            if (!btn) return;

            var stepper = btn.closest('.dg-qty-stepper');
            if (!stepper) return;

            var cartKey = stepper.dataset.cartKey;
            var current = parseInt(stepper.dataset.qty, 10) || 1;
            var newQty  = btn.classList.contains('dg-qty-decrease') ? current - 1 : current + 1;

            if (newQty < 1) { return; }  // Decrement to 0 disabled — manage removal server-side.

            updateQty(stepper, cartKey, newQty);
        });
    }

    /**
     * Optimistically update displayed qty, then AJAX update.
     * On success: reload page so order summary totals are accurate.
     */
    function updateQty(stepper, cartKey, newQty) {
        // Optimistic UI: update display immediately
        stepper.dataset.qty = newQty;
        var display = stepper.querySelector('.dg-qty-value');
        if (display) { display.textContent = newQty; }

        // Disable stepper during request
        stepper.querySelectorAll('.dg-qty-stepper-btn').forEach(function (b) {
            b.disabled = true;
        });

        var formData = new FormData();
        formData.append('action',        'dg_ajax_update_cart');
        formData.append('nonce',         getNonce());
        formData.append('cart_item_key', cartKey);
        formData.append('quantity',      newQty);

        fetch(getAjaxUrl(), {
            method: 'POST',
            body:   formData,
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                // Reload to refresh totals sidebar (qty update changes
                // both line subtotal and the order summary).
                window.location.reload();
            } else {
                stepper.querySelectorAll('.dg-qty-stepper-btn').forEach(function (b) {
                    b.disabled = false;
                });
                dgLog('warn', '[Dragon Glow] Qty update failed');
            }
        })
        .catch(function () {
            stepper.querySelectorAll('.dg-qty-stepper-btn').forEach(function (b) {
                b.disabled = false;
            });
        });
    }

    // ── Toasts (shared layer, mirrors wishlist + signup patterns) ─────────
    // aria-live="polite" on the container announces additions without
    // interrupting the user; auto-dismiss after 3.5s.

    function showToast(message, type) {
        var stack = document.getElementById('dg-cart-toasts');
        if (!stack || !message) return;
        type = type || 'info';
        var icons = { success: 'check_circle', info: 'info', error: 'error' };

        var el = document.createElement('div');
        el.className = 'dg-cart-toast dg-cart-toast--' + type;
        el.setAttribute('role', type === 'error' ? 'alert' : 'status');
        el.innerHTML =
            '<span class="dg-cart-toast__icon" aria-hidden="true">' +
                '<span class="material-symbols-outlined">' + (icons[type] || 'info') + '</span>' +
            '</span>' +
            '<span class="dg-cart-toast__body">' + escapeHtml(message) + '</span>';
        stack.appendChild(el);

        setTimeout(function () {
            el.classList.add('is-leaving');
            setTimeout(function () { el.remove(); }, 320);
        }, 3500);
    }

    // ── Parallax blobs (unchanged) ─────────────────────────────────────────

    function initParallaxBlobs() {
        var blob = document.querySelector('.js-cart-blob');
        if (!blob || reduce) { return; }

        window.addEventListener('mousemove', function (e) {
            var x = e.clientX / window.innerWidth;
            var y = e.clientY / window.innerHeight;
            blob.style.transform = 'translate(' + (x * 28) + 'px, ' + (y * 28) + 'px)';
        }, { passive: true });
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    function getAjaxUrl() {
        return (window.dgAjax && window.dgAjax.url) ? window.dgAjax.url : '/wp-admin/admin-ajax.php';
    }

    function getNonce() {
        return (window.dgAjax && window.dgAjax.nonce) ? window.dgAjax.nonce : '';
    }

    /**
     * Update cart count badge in header (optimistic UI).
     * 
     * @param {number} newCount - The new cart item count.
     */
    function updateCartCountBadge(newCount) {
        var badges = document.querySelectorAll('.dg-cart-count');
        badges.forEach(function (badge) {
            badge.textContent = String(newCount);
            if (newCount === 0) {
                badge.classList.add('hidden');
            } else {
                badge.classList.remove('hidden');
            }
        });
    }

    /**
     * Minimal printf-style formatter — supports the `%d` token used in
     * the i18n strings. Avoids pulling in sprintf.js for one-liners.
     */
    function sprintf(format) {
        var args = Array.prototype.slice.call(arguments, 1);
        var i = 0;
        return String(format).replace(/%d/g, function () {
            return String(args[i++] || 0);
        });
    }

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /**
     * Defensive CSS.escape polyfill — older Safari versions don't ship
     * it. WC cart keys are URL-safe hashes so we mostly need to handle
     * `.` and `:` (used in some variation_id keys). Falls back to a
     * raw lookup if escape() is missing entirely.
     */
    function cssEscape(value) {
        if (typeof window.CSS !== 'undefined' && typeof window.CSS.escape === 'function') {
            return window.CSS.escape(value);
        }
        return String(value).replace(/([!"#$%&'()*+,./:;<=>?@\[\\\]^`{|}~])/g, '\\$1');
    }

})();