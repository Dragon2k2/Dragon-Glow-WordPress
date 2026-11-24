/**
 * Dragon Glow — Product Page JS
 * Gallery image swap (cache-aware, instant), thumbnail active state,
 * tab switching, qty +/-, star rating, sticky bar, review form.
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    // ── Image Gallery Preloader ─────────────────────────────────
    // Collect all data-full URLs from thumbnails; skip the first
    // thumbnail because its image is already loaded on the page.
    (function preloadGalleryImages() {
        var thumbs = document.querySelectorAll('[data-dg-thumb]');
        if (!thumbs.length) return;

        var toPreload = [];
        for (var i = 1; i < thumbs.length; i++) {
            var src = thumbs[i].dataset.full;
            if (src) toPreload.push(src);
        }

        if (!toPreload.length) return;

        // Defer to after window.load so the critical rendering path
        // is never blocked.  Falls back to an immediate loop for
        // browsers without requestIdleCallback.
        var preload = function () {
            for (var j = 0; j < toPreload.length; j++) {
                (new Image()).src = toPreload[j];
            }
        };

        if (typeof requestIdleCallback !== 'undefined') {
            requestIdleCallback(preload, { timeout: 2000 });
        } else if (document.readyState === 'complete') {
            setTimeout(preload, 0);
        } else {
            window.addEventListener('load', function () {
                setTimeout(preload, 0);
            });
        }
    }());

    // ── Image Gallery Swap ─────────────────────────────────────
    // Strategy:
    //  1. Guard: ignore re-clicks on the same thumbnail.
    //  2. If the new image is already in browser cache (Image.complete)
    //     the swap is near-instantaneous; no setTimeout is used.
    //  3. If not cached yet, wait for the Image load event before
    //     fading in — this eliminates the blank / flicker window.
    //  4. If the user clicks a different thumbnail while a transition
    //     is still running, the pending transition is cancelled and a
    //     fresh one starts immediately.
    function dgChangeImage(btn) {
        var fullSrc   = btn.dataset.full;
        var mainImg   = document.getElementById('dg-main-image');
        if (!mainImg || !fullSrc) return;

        // Guard: clicking the already-active thumbnail does nothing.
        if (btn.classList.contains('is-active')) return;

        // Cancel any transition already in progress so fast
        // sequential clicks never leave the image at opacity 0.
        if (mainImg._dgSwapTimer) {
            clearTimeout(mainImg._dgSwapTimer);
            mainImg._dgSwapTimer = null;
        }
        if (mainImg._dgSwapLoadHandler) {
            mainImg.removeEventListener('load', mainImg._dgSwapLoadHandler);
            mainImg._dgSwapLoadHandler = null;
        }

        // ── Update thumbnail active states (immediate, no animation) ──
        var thumbnails = document.querySelectorAll('[data-dg-thumb]');
        for (var i = 0; i < thumbnails.length; i++) {
            var thumb = thumbnails[i];
            thumb.classList.remove(
                'is-active', 'border-primary', 'ring-2',
                'ring-primary-container/20', 'opacity-100'
            );
            thumb.classList.add('border-outline-variant/30', 'opacity-60');
        }
        btn.classList.add(
            'is-active', 'border-primary', 'ring-2',
            'ring-primary-container/20', 'opacity-100'
        );
        btn.classList.remove('border-outline-variant/30', 'opacity-60');

        // ── Swap the main image ───────────────────────────────────────
        var preloader = new Image();
        preloader.src = fullSrc;

        if (preloader.complete && preloader.naturalWidth > 0) {
            // Already in cache — swap + fade in immediately.
            mainImg.style.opacity = '0';
            mainImg.src = fullSrc;
            mainImg.style.opacity = '1';
        } else {
            // Not cached yet — fade to 0, wait for load, then fade in.
            mainImg.style.opacity = '0';
            mainImg._dgSwapLoadHandler = function () {
                mainImg.src = fullSrc;
                mainImg.style.opacity = '1';
                mainImg._dgSwapLoadHandler = null;
            };
            mainImg.addEventListener('load', mainImg._dgSwapLoadHandler);
        }
    }

    // Delegated listener — keeps working regardless of where the
    // thumbnails live in the DOM (no inline `onclick` handlers).
    document.addEventListener('click', function (e) {
        var thumb = e.target.closest('[data-dg-thumb]');
        if (!thumb) return;
        e.preventDefault();
        dgChangeImage(thumb);
    });

    // ── Tab Switching ─────────────────────────────────────────
    window.dgSwitchTab = function (tabId) {
        // Hide all panes
        var panes = document.querySelectorAll('.dg-tab-pane');
        panes.forEach(function (pane) {
            pane.classList.add('hidden');
        });

        // Reset all tab buttons
        var buttons = document.querySelectorAll('.dg-tab-btn');
        buttons.forEach(function (btn) {
            btn.classList.remove('text-primary', 'font-bold', 'border-b-2', 'border-tertiary-container');
            btn.classList.add('text-on-surface-variant', 'font-medium');
            btn.setAttribute('aria-selected', 'false');
        });

        // Show selected pane
        var pane = document.getElementById('tab-' + tabId);
        if (pane) {
            pane.classList.remove('hidden');
        }

        // Activate selected button
        var btn = document.querySelector('[data-tab="' + tabId + '"]');
        if (btn) {
            btn.classList.add('text-primary', 'font-bold', 'border-b-2', 'border-tertiary-container');
            btn.classList.remove('text-on-surface-variant', 'font-medium');
            btn.setAttribute('aria-selected', 'true');
        }

        // Scroll to tabs on mobile
        var tabsSection = document.getElementById('product-tabs');
        if (tabsSection && window.innerWidth < 768) {
            tabsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    // ── Size Selector ─────────────────────────────────────────
    // Single-select group rendered by woocommerce/content-single-product.php.
    // buy-now.js reads the selection from `.dg-size-btn.is-active`, so the
    // class must stay in sync with the Tailwind state classes toggled here.
    (function initSizeSelector() {
        var sizeButtons = document.querySelectorAll('.dg-size-btn');
        if (!sizeButtons.length) return;

        var activeClasses   = ['is-active', 'border-primary', 'text-primary', 'font-bold'];
        var inactiveClasses = ['border-outline-variant/30', 'text-on-surface-variant', 'hover:border-primary'];

        sizeButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.classList.contains('is-active')) return;

                sizeButtons.forEach(function (other) {
                    var isSelected = other === btn;
                    activeClasses.forEach(function (cls) {
                        other.classList.toggle(cls, isSelected);
                    });
                    inactiveClasses.forEach(function (cls) {
                        other.classList.toggle(cls, !isSelected);
                    });
                    other.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
                });
            });
        });
    }());

    // ── Quantity +/- Buttons ──────────────────────────────────
    // Supports both old (.minus/.plus) and new (.dg-qty-minus/.dg-qty-plus) class names.
    document.querySelectorAll('.quantity, .dg-quantity-stepper').forEach(function (container) {
        var input = container.querySelector('.qty');
        var minusBtn = container.querySelector('.minus, .dg-qty-minus');
        var plusBtn = container.querySelector('.plus, .dg-qty-plus');

        if (!input || !minusBtn || !plusBtn) return;

        var min = parseFloat(input.getAttribute('min')) || 1;
        var max = parseFloat(input.getAttribute('max')) || Infinity;
        var step = parseFloat(input.getAttribute('step')) || 1;

        /**
         * Update button disabled states based on current value
         */
        function updateButtonStates() {
            var currentValue = parseFloat(input.value) || min;

            // Disable minus if at minimum
            if (currentValue <= min) {
                minusBtn.disabled = true;
                minusBtn.setAttribute('aria-disabled', 'true');
            } else {
                minusBtn.disabled = false;
                minusBtn.removeAttribute('aria-disabled');
            }

            // Disable plus if at maximum
            if (max !== Infinity && currentValue >= max) {
                plusBtn.disabled = true;
                plusBtn.setAttribute('aria-disabled', 'true');
            } else {
                plusBtn.disabled = false;
                plusBtn.removeAttribute('aria-disabled');
            }
        }

        minusBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var val = parseFloat(input.value) || min;
            var newVal = Math.max(min, val - step);
            if (newVal !== val) {
                input.value = newVal;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
            updateButtonStates();
        });

        plusBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var val = parseFloat(input.value) || min;
            var newVal = max !== Infinity ? Math.min(max, val + step) : val + step;
            if (newVal !== val) {
                input.value = newVal;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
            updateButtonStates();
        });

        // Update button states when input value changes manually
        input.addEventListener('input', updateButtonStates);
        input.addEventListener('change', updateButtonStates);

        // Initial state
        updateButtonStates();
    });

    // ── Star Rating in Review Form ───────────────────────────
    var reviewStars = document.getElementById('review-stars');
    if (reviewStars) {
        var ratingInput = document.getElementById('review-rating-value');
        var buttons = reviewStars.querySelectorAll('button');

        buttons.forEach(function (btn, index) {
            btn.addEventListener('mouseenter', function () {
                highlightStars(index + 1);
            });

            btn.addEventListener('click', function () {
                if (ratingInput) {
                    ratingInput.value = index + 1;
                }
                highlightStars(index + 1);
            });
        });

        reviewStars.addEventListener('mouseleave', function () {
            var current = parseInt(ratingInput ? ratingInput.value : 5);
            highlightStars(current);
        });

        function highlightStars(count) {
            buttons.forEach(function (btn, idx) {
                var icon = btn.querySelector('.material-symbols-outlined');
                if (idx < count) {
                    icon.style.fontVariationSettings = "'FILL' 1";
                    icon.classList.remove('text-outline-variant');
                    icon.classList.add('text-tertiary');
                } else {
                    icon.style.fontVariationSettings = "'FILL' 0";
                    icon.classList.add('text-outline-variant');
                    icon.classList.remove('text-tertiary');
                }
            });
        }
    }

    // ── Sticky Product Info ───────────────────────────────────
    var productInfo = document.getElementById('sticky-add-to-bag');
    if (productInfo) {
        var lastScroll = 0;

        window.addEventListener('scroll', function () {
            var scroll = window.scrollY;

            if (scroll > 600 && scroll > lastScroll) {
                productInfo.classList.add('shadow-lg');
            } else {
                productInfo.classList.remove('shadow-lg');
            }

            lastScroll = scroll;
        }, { passive: true });
    }

    // ── Add to Cart Button (AJAX) ─────────────────────────────
    // Intercept form submit and use AJAX instead of page reload
    var cartForm = document.querySelector('form.cart');
    var addToCartBtn = document.querySelector('.single_add_to_cart_button');
    
    if (cartForm && addToCartBtn) {
        var originalText = addToCartBtn.textContent;
        var addedCartItemKey = '';
        var addedCartItemQuantity = 0;

        function showCartLoading(label) {
            var icon = document.createElement('span');
            icon.className = 'material-symbols-outlined dg-product-cart__loading-icon';
            icon.setAttribute('aria-hidden', 'true');
            icon.textContent = 'sync';
            var text = document.createElement('span');
            text.className = 'dg-product-cart__loading-label';
            text.textContent = label;
            addToCartBtn.replaceChildren(icon, text);
            addToCartBtn.classList.add('loading');
            addToCartBtn.disabled = true;
            addToCartBtn.setAttribute('aria-busy', 'true');
        }

        function reconcileCartBadge(data) {
            var count = data && data.count;
            if (typeof count === 'number' && isFinite(count) && count >= 0) {
                window.DGCartBadge.paint(count);
            } else {
                window.DGCart.refreshCount();
            }
        }

        function removeAddedItem() {
            if (addToCartBtn.classList.contains('loading')) return;

            var addedText = addToCartBtn.textContent;
            showCartLoading((window.dgProduct && window.dgProduct.i18n && window.dgProduct.i18n.removing) || 'Removing...');
            var removedQuantity = addedCartItemQuantity;
            window.DGCartBadge.bump(-removedQuantity);

            window.DGCart.removeMany({ cartItemKeys: [addedCartItemKey] })
            .then(function (data) {
                if (!data || !data.success) {
                    throw new Error(data && data.data && data.data.message || 'Could not remove item. Please try again.');
                }

                addToCartBtn.classList.remove('added');
                addToCartBtn.textContent = originalText;
                addedCartItemKey = '';
                addedCartItemQuantity = 0;
                reconcileCartBadge(data.data);
                document.body.dispatchEvent(new CustomEvent('removed_from_cart'));
            })
            .catch(function (error) {
                window.DGCartBadge.bump(removedQuantity);
                addToCartBtn.textContent = addedText;
                alert(error.message || 'Unable to remove item. Please try again.');
            })
            .finally(function () {
                addToCartBtn.classList.remove('loading');
                addToCartBtn.disabled = false;
                addToCartBtn.removeAttribute('aria-busy');
            });
        }

        // Remove the confirmed line even if form fields are currently invalid.
        addToCartBtn.addEventListener('click', function (e) {
            if (!addToCartBtn.classList.contains('added')) return;
            e.preventDefault();
            removeAddedItem();
        });

        cartForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Also handle implicit submissions from a form field while confirmed.
            if (addToCartBtn.classList.contains('added')) {
                removeAddedItem();
                return;
            }
            
            // Prevent double-submit
            if (addToCartBtn.classList.contains('loading')) return;
            
            // Show loading state
            showCartLoading((window.dgProduct && window.dgProduct.i18n && window.dgProduct.i18n.adding) || 'Adding...');
            
            // Get product data from form
            var productId = addToCartBtn.value || cartForm.querySelector('[name="add-to-cart"]').value;
            var quantity = cartForm.querySelector('[name="quantity"]') ? cartForm.querySelector('[name="quantity"]').value : 1;
            var variationId = cartForm.querySelector('[name="variation_id"]') ? cartForm.querySelector('[name="variation_id"]').value : 0;
            
            // Build AJAX payload
            var formData = new FormData();
            formData.append('action', 'dg_ajax_add_to_cart');
            formData.append('nonce', window.dgAjax.nonce);
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            
            if (variationId) {
                formData.append('variation_id', variationId);
                
                // Collect variation attributes (e.g., attribute_pa_color, attribute_pa_size)
                var attributes = {};
                var attrInputs = cartForm.querySelectorAll('[name^="attribute_"]');
                attrInputs.forEach(function (input) {
                    attributes[input.name] = input.value;
                });
                if (Object.keys(attributes).length > 0) {
                    formData.append('variation_attributes', JSON.stringify(attributes));
                }
            }
            
            // Paint immediately; reconcile with the response or roll back on failure.
            var addedQuantity = Math.max(1, parseInt(quantity, 10) || 1);
            window.DGCartBadge.bump(addedQuantity);

            // AJAX add to cart via theme endpoint
            fetch(window.dgAjax.url, {
                method: 'POST',
                body: formData
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    addedCartItemKey = data.data.cart_item_key;
                    addedCartItemQuantity = parseInt(data.data.cart_item_quantity, 10) || addedQuantity;
                    reconcileCartBadge(data.data);
                    // Trigger WooCommerce added_to_cart event
                    document.body.dispatchEvent(new CustomEvent('added_to_cart', {
                        detail: { 
                            productId: productId,
                            cart_item_key: data.data.cart_item_key
                        }
                    }));
                    
                    // Show success state
                    addToCartBtn.classList.remove('loading');
                    addToCartBtn.classList.add('added');
                    addToCartBtn.disabled = false;
                    addToCartBtn.removeAttribute('aria-busy');
                    addToCartBtn.textContent = data.data.message || (addToCartBtn.textContent.includes('Add') ? 'Added!' : 'Đã thêm!');
                    
                    // Show mini cart if exists
                    var miniCart = document.querySelector('.dg-mini-cart');
                    if (miniCart) {
                        miniCart.classList.add('is-open');
                        setTimeout(function () {
                            miniCart.classList.remove('is-open');
                        }, 3000);
                    }
                } else {
                    // Error from server
                    throw new Error(data.data.message || 'Could not add to cart.');
                }
            })
            .catch(function (error) {
                console.error('Add to cart error:', error);
                window.DGCartBadge.bump(-addedQuantity);
                
                // Restore button state
                addToCartBtn.classList.remove('loading');
                addToCartBtn.disabled = false;
                addToCartBtn.removeAttribute('aria-busy');
                addToCartBtn.textContent = originalText;
                
                // Show error message
                alert(error.message || 'Unable to add to cart. Please try again.');
            });
        });
    }

    // Buy Now requests and button states are owned by buy-now.js.

    // ── Review Form Submit ───────────────────────────────────
    var reviewForm = document.getElementById('dg-review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var formData = new FormData(reviewForm);
            formData.append('action', 'dg_submit_review');
            formData.append('nonce', dgAjax.nonce);

            var submitBtn = reviewForm.querySelector('button[type="submit"]');
            var originalText = submitBtn.textContent;
            submitBtn.textContent = '...';
            submitBtn.disabled = true;

            fetch(dgAjax.url, {
                method: 'POST',
                body: formData
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var msg = document.getElementById('dg-review-msg');
                if (msg) {
                    msg.textContent = data.data ? data.data.message : '';
                    msg.classList.remove('hidden');
                }
                if (data.success) {
                    reviewForm.reset();
                }
            })
            .finally(function () {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // ── Image Zoom Lightbox with Gallery Navigation ───────────
    // Dùng chung controller với trang Gift Cards
    // (assets/js/lib/zoom-lightbox.js → window.DGZoomLightbox).
    //
    // Trigger: ảnh chính `#dg-main-image` + nút zoom `.dg-zoom-btn`
    // (đánh dấu bằng `data-dg-zoom-trigger`, listener được delegate ở lib).
    (function initZoomLightbox() {
        var modal = document.querySelector('[data-dg-zoom-root]');
        var mainImg = document.getElementById('dg-main-image');
        if (!modal || !mainImg || !window.DGZoomLightbox) return;

        // Gallery = ảnh đầy đủ của mọi thumbnail.
        var items = [];
        var thumbs = document.querySelectorAll('[data-dg-thumb]');

        for (var i = 0; i < thumbs.length; i++) {
            var fullSrc = thumbs[i].dataset.full;
            if (!fullSrc) continue;
            var thumbImg = thumbs[i].querySelector('img');
            items.push({
                src: fullSrc,
                alt: thumbImg ? thumbImg.alt : '',
                // Giữ tham chiếu tới thumbnail để đồng bộ ảnh ngoài lightbox.
                thumb: thumbs[i]
            });
        }

        // Không có thumbnail → lightbox chỉ hiển thị ảnh chính.
        if (items.length === 0) {
            items.push({ src: mainImg.getAttribute('src') || mainImg.src, alt: mainImg.alt });
        }

        /**
         * Index của ảnh đang hiển thị ở #dg-main-image — đảm bảo mở lightbox
         * đúng ảnh user đang xem (bộ đếm & prev/next đồng bộ với gallery).
         *
         * Nguồn chuẩn là thumbnail active (state hiển thị của trang) — đọc
         * `src` của ảnh chính chỉ là fallback, vì `src` có thể chưa kịp cập
         * nhật nếu ảnh chưa load xong.
         *
         * @return {number} Index trong `items`, -1 nếu không khớp.
         */
        function resolveCurrentIndex() {
            // 1. Theo thumbnail active (state hiển thị của trang).
            for (var i = 0; i < items.length; i++) {
                if (items[i].thumb && items[i].thumb.classList.contains('is-active')) {
                    return i;
                }
            }

            // 2. Theo `src` của ảnh chính.
            var current = mainImg.getAttribute('src') || mainImg.currentSrc || mainImg.src;
            for (var j = 0; j < items.length; j++) {
                if (items[j].src === current) {
                    return j;
                }
            }

            return -1;
        }

        window.DGZoomLightbox.create({
            root: modal,
            scope: document,
            items: items,
            resolveIndex: resolveCurrentIndex,

            /**
             * Đồng bộ ảnh ngoài lightbox theo ảnh đang xem bên trong.
             *
             * Khi user chuyển ảnh trong lightbox (prev/next), ảnh chính
             * `#dg-main-image` + thumbnail active phải theo kịp — nếu không,
             * user thoát lightbox sẽ thấy ảnh khác với ảnh vừa xem.
             * Dùng lại `dgChangeImage()` để giữ đúng hành vi cache-aware
             * (fade khi ảnh chưa load) + cập nhật trạng thái thumbnail.
             *
             * @param {number} index Index hiện tại trong `items`.
             * @param {Object} item  Item của gallery.
             */
            onChange: function (index, item) {
                if (!item.thumb) return;

                // Ảnh ngoài đã đúng ảnh này → không cần swap (tránh load lại
                // không cần thiết, và tránh đổi ảnh ngoài khi lightbox mở
                // ở ảnh fallback do không khớp thumbnail nào).
                var current = mainImg.getAttribute('src') || mainImg.currentSrc || mainImg.src;
                if (current === item.src) return;

                dgChangeImage(item.thumb);
            }
        });
    })();
})();
