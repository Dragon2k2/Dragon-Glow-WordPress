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
    // We bind both `DOMContentLoaded` (cold load) and `pageshow`
    // (bfcache restore) so the selection reconciliation runs after
    // every kind of navigation. `event.persisted === true` means the
    // browser restored the page from its back/forward cache, in
    // which case the DOM keeps whatever checkbox state the user
    // last had — we have to re-run reconciliation to bring the DOM
    // and the server back in sync.
    // ── Form-restore guard (BEFORE any listener registration) ───────────────
    // Chrome/Edge restore form state on back/forward navigation EVEN
    // when `pageshow.persisted === false` (no bfcache). The restored
    // state is applied AFTER DOMContentLoaded and AFTER our handlers
    // run, so any explicit `cb.checked = false` we do during init can
    // be silently overwritten by the browser a tick later. The fix is
    // the standard one for this Chrome quirk: set `defaultChecked` on
    // each input at parse time. `defaultChecked` is the "initial"
    // value the form-restoration path uses; once the page is fully
    // loaded the browser compares the live `checked` value to
    // `defaultChecked` and only restores when they differ, so a
    // server-rendered unchecked input stays unchecked on back-nav.
    //
    // This block runs at script-parse time — BEFORE DOMContentLoaded
    // and BEFORE the browser applies any restored form state — which
    // is the only window where setting `defaultChecked` actually
    // matters. Adding the same line later in init() would be too late.
    (function primeNoRestoreCheckboxes() {
        var noRestore = document.querySelectorAll('[data-dg-cart-no-restore]');
        if (!noRestore.length) return;
        noRestore.forEach(function (cb) {
            try {
                cb.defaultChecked = false;
            } catch (e) {
                /* read-only on some engines — fall through */
            }
        });
    })();

    document.addEventListener('DOMContentLoaded', init);
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            init();
        }
    });

    function init() {
        bindQtySteppers();
        initParallaxBlobs();
        initBulkSelect();
        initBulkActions();
        initConfirmModal();
        initSelectionCheckout();
        // Run the DOM-vs-server reconciliation after the listeners
        // are in place so the subsequent `updateBulkBar()` call can
        // re-emit `change` events without an infinite loop.
        reconcileSelectionState();
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
        // Keep the "Checkout with selected" CTA in sync — same selection state
        // powers both UI surfaces.
        updateSelectionCta(keys);
        // Mirror the current selection to the server so /checkout/
        // always reflects the latest tick state, not a stale session
        // snapshot from a previous visit. Debounced 250ms inside the
        // helper.
        syncSelectionState(keys);
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

    /* ── "Checkout with selected" — companion CTA to the bulk bar ───────
       The cart page now exposes two checkout CTAs in the sidebar:
         - .dg-cart-checkout-cta--all        : original "Proceed to Checkout"
         - .dg-cart-checkout-cta--selected  : new "Checkout with selected"
       CSS (cart.css) hides --selected by default and shows it whenever
       the body has .dg-has-selection (set by the PHP layer when there
       are flags in the cart, primarily on /checkout/). On /cart/ we are
       purely JS-driven — every time the selection count crosses 0 ↔ ≥1
       we toggle a body class via updateSelectionCta() and let the
       stylesheet flip the visibility.
       Click flow:
         1. checkoutWithSelected() POSTs the cart-item keys via the new
            `dg_ajax_apply_cart_selection` handler.
         2. On success we show a success toast and redirect to the
            returned checkout URL — the server-side hooks in
            inc/woocommerce/cart-selection.php filter the review-order
            and totals to match.
         3. On failure we surface the server message verbatim.
       clearSelection() lets the user abandon the selection without
       leaving the cart (sends `dg_ajax_clear_cart_selection`,
       refreshes the master checkbox + body class). */

    function initSelectionCheckout() {
        var checkoutBtn = document.querySelector('[data-dg-cart-checkout-with-selected]');
        var clearBtn    = document.querySelector('[data-dg-cart-clear-selection]');
        if (!checkoutBtn && !clearBtn) {
            return;
        }
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', function () {
                if (checkoutBtn.disabled) return;
                var keys = getSelectedKeys();
                if (!keys.length) {
                    showToast(i18n.selectItems || 'Select items first.', 'info');
                    return;
                }
                checkoutWithSelected(keys, checkoutBtn);
            });
        }
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (clearBtn.disabled) return;
                clearSelection(clearBtn);
            });
        }
        // The DOM-vs-server reconciliation is performed by
        // `init()` (cold load) and the `pageshow` handler
        // (bfcache restore). Calling it here would double-fire
        // the AJAX sync on cold load.
    }

    /* Reconcile the DOM checkboxes against the server-side session.

       The server renders `[data-dg-cart-select]` checkboxes as
       unchecked, but the browser's back/forward cache (bfcache)
       can keep a previous DOM state alive when the shopper returns
       to /cart/ via the browser back button. In that case the
       checkboxes are still ticked even though the user has not
       re-ticked them in this visit — and the server still has a
       matching session selection, so /checkout/ would filter down
       to that stale list.

       This function:

       1. Resets any DOM checkboxes whose value isn't part of a
          server-known selection. We don't know the server list
          on the client, so we treat the initial DOM as untrusted
          and only preserve "checked" when the server told us a
          matching selection exists.
       2. Mirrors the (now-canonical) DOM to the server so the
          session matches what the user actually sees.
       3. Updates the CTA visibility and body class.

       Called from both `init()` (cold load) and the `pageshow`
       handler (bfcache restore). The handler is a no-op on a
       normal load because `event.persisted` is false.
    */
    function reconcileSelectionState() {
        // The server's view of the current selection. Empty array
        // means "no session selection" — the user has no ticked
        // items, and any DOM check state is by definition stale.
        var serverSelection = readServerSelection();
        if (!serverSelection || !serverSelection.length) {
            // Force every DOM checkbox back to unchecked AND strip
            // the `.is-selected` class off every row. The class is
            // what produces the visible "dấu tích" (gradient +
            // left-border) — failing to strip it leaves a row that
            // *looks* selected even though `cb.checked` is false.
            document.querySelectorAll('[data-dg-cart-select]').forEach(function (cb) {
                if (cb.checked) {
                    cb.checked = false;
                }
                var row = cb.closest('.dg-cart-row');
                if (row) row.classList.remove('is-selected');
            });
            var master = document.querySelector('[data-dg-cart-select-all]');
            if (master) {
                master.checked = false;
                master.indeterminate = false;
            }
        } else {
            // Server has a selection. Sync the DOM to it: tick
            // any checkbox whose value is in the list, untick the
            // rest. Defensive against both a stale DOM (bfcache
            // restore with wrong state) and a server that
            // returned a different set than what the user last
            // saw.
            document.querySelectorAll('[data-dg-cart-select]').forEach(function (cb) {
                var k = cb.value || cb.dataset.cartKey;
                var shouldBeChecked = k && serverSelection.indexOf(k) !== -1;
                if (cb.checked !== shouldBeChecked) {
                    cb.checked = shouldBeChecked;
                }
                var row = cb.closest('.dg-cart-row');
                if (row) row.classList.toggle('is-selected', shouldBeChecked);
            });
            // Master checkbox reflects the per-row state — defer
            // to updateMasterCheckbox() to keep the three-state
            // semantics correct.
            updateMasterCheckbox();
        }
        // After reconciliation, read the canonical DOM state and
        // push it to the server. This both fixes a stale server
        // session and re-applies the canonical list in case any
        // item disappeared from the cart since last sync.
        //
        // We push immediately (no debounce) so the user can click
        // "Proceed to checkout" right after restoring the page
        // without racing the 250ms debounce window.
        var canonical = getSelectedKeys();
        updateSelectionCta(canonical);
        syncSelectionStateNow(canonical);
    }

    function readServerSelection() {
        // The PHP layer injects the current session selection on
        // dg-main. Reading it here is the only way the JS can
        // know what the server believes is selected, since the
        // DOM is not authoritative.
        var raw = (window.dgCart && window.dgCart.selection) || (window.dgAjax && window.dgAjax.selection) || null;
        if (!raw) {
            return [];
        }
        if (Array.isArray(raw)) {
            return raw;
        }
        if (typeof raw === 'string') {
            return raw.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
        }
        return [];
    }

    function updateSelectionCta(keys) {
        var ctaAll      = document.querySelector('.dg-cart-checkout-cta--all');
        var ctaSelected = document.querySelector('.dg-cart-checkout-cta--selected');
        var hintTarget  = document.querySelector('[data-dg-cart-selected-count-hint]');
        if (!ctaAll || !ctaSelected) {
            return;
        }
        var count = (keys && keys.length) || 0;
        var hasSelection = count > 0;
        ctaAll.hidden = hasSelection;
        ctaSelected.hidden = !hasSelection;
        // Toggle body class so the global cart.css rules can also hide the
        // shimmer button on /checkout/ if a flag is in flight.
        document.body.classList.toggle('dg-has-selection', hasSelection);
        if (hasSelection && hintTarget) {
            // Use the same i18n table the cart already localized.
            var template = (i18n.selectedHintN) || '%d items selected for checkout';
            hintTarget.textContent = template.replace('%d', String(count));
        }
    }

    function checkoutWithSelected(keys, btn) {
        if (!window.dgAjax || !window.dgAjax.url) {
            showToast(i18n.bagUnavailable || 'Your bag is currently unavailable. Please try again later.', 'error');
            return;
        }
        btn.disabled = true;
        var originalLabel = btn.innerHTML;
        btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:20px;">progress_activity</span> ' + (i18n.processing || 'Processing…');

        var fd = new FormData();
        fd.append('action',         'dg_ajax_apply_cart_selection');
        fd.append('nonce',          getNonce());
        fd.append('cart_item_keys', keys.join(','));

        fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.disabled = false;
                btn.innerHTML = originalLabel;
                if (!data || !data.success) {
                    var msg = (data && data.data && data.data.message) || i18n.bagUnavailable || 'Could not start checkout.';
                    showToast(msg, 'error');
                    return;
                }
                var redirect = (data && data.data && data.data.redirect) || '/checkout/';
                // Optimistic toast so the user sees the intent succeed
                // even though the navigation takes a moment.
                showToast(data.data.message || 'Items ready for checkout.', 'success');
                window.location.href = redirect;
            })
            .catch(function (err) {
                btn.disabled = false;
                btn.innerHTML = originalLabel;
                showToast(i18n.network || 'Network error. Please try again.', 'error');
            });
    }

    function clearSelection(btn) {
        if (!window.dgAjax || !window.dgAjax.url) {
            // No AJAX available — just reset the visual state locally.
            resetSelection();
            return;
        }
        btn.disabled = true;
        var fd = new FormData();
        fd.append('action', 'dg_ajax_clear_cart_selection');
        fd.append('nonce',  getNonce());

        fetch(getAjaxUrl(), { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json() })
            .then(function (data) {
                btn.disabled = false;
                if (!data || !data.success) {
                    showToast((data && data.data && data.data.message) || i18n.network || 'Network error. Please try again.', 'error');
                    return;
                }
                resetSelection();
                showToast(data.data.message || 'Selection cleared.', 'info');
            })
            .catch(function () {
                btn.disabled = false;
                resetSelection();
            });
    }

    /* ── Reactive selection sync (server is the source of truth) ─────────
       Why this exists:
         `dg_checkout_selection` is a server-side session key — the only
         thing /checkout/ reads to decide which lines to show. If the user
         ticks then unticks on /cart/ without ever clicking "Checkout
         with selected", the session still holds the previous selection
         and /checkout/ either hides the wrong lines or renders empty.
         The cheapest fix that matches the rest of the theme is to
         mirror the UI to the server on every checkbox change.

       Two entry points:
         - `syncSelectionState(keys)` (debounced 250ms) — used for
           runtime checkbox changes so a tick/untick burst collapses
           into one request.
         - `syncSelectionStateNow(keys)` (immediate) — used for
           initial paint reconciliation on /cart/ load, where we
           cannot afford to debounce: the user may navigate to
           /checkout/ before the timer fires, and a stale session
           would still filter the wrong lines.

       Both are fire-and-forget: no loading state, no toast on
       success, errors are swallowed (the next successful sync —
       typically the click on "Checkout with selected" — will fix
       the state).
    */
    var syncSelectionTimer = null;
    var syncSelectionInFlight = null;

    function dispatchSelectionSync(keys) {
        if (!window.dgAjax || !window.dgAjax.url) {
            return;
        }
        var fd = new FormData();
        fd.append('action',         'dg_ajax_apply_cart_selection');
        fd.append('nonce',          getNonce());
        fd.append('cart_item_keys', (keys || []).join(','));
        // Abort any in-flight sync so out-of-order responses can't
        // overwrite a newer state. Older requests reject with
        // AbortError and are ignored.
        if (syncSelectionInFlight) {
            syncSelectionInFlight.abort();
        }
        var ctrl = (typeof AbortController === 'function') ? new AbortController() : null;
        syncSelectionInFlight = ctrl;
        var fetchOpts = { method: 'POST', body: fd, credentials: 'same-origin' };
        if (ctrl) { fetchOpts.signal = ctrl.signal; }
        fetch(getAjaxUrl(), fetchOpts)
            .then(function (r) { return r.json(); })
            .catch(function (err) {
                // AbortError from a newer sync call is expected and
                // can be ignored — the newer request will be the
                // source of truth.
                if (err && err.name === 'AbortError') {
                    return;
                }
                dgLog('warn', '[Dragon Glow] dispatchSelectionSync: fetch/parse failure. Inspect Network tab for the actual response.', err);
            });
    }

    function syncSelectionState(keys) {
        if (!window.dgAjax || !window.dgAjax.url) {
            return;
        }
        if (syncSelectionTimer) {
            clearTimeout(syncSelectionTimer);
        }
        syncSelectionTimer = setTimeout(function () {
            syncSelectionTimer = null;
            dispatchSelectionSync(keys);
        }, 250);
    }

    function syncSelectionStateNow(keys) {
        if (syncSelectionTimer) {
            clearTimeout(syncSelectionTimer);
            syncSelectionTimer = null;
        }
        dispatchSelectionSync(keys);
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