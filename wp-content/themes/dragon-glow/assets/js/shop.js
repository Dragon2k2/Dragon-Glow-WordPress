/**
 * Dragon Glow — Shop Page JS
 * Reveal-on-scroll, hero parallax, filter dropdown (Material), mobile filter
 * sheet, active filter tags, URL-driven filter state, client-side card hide.
 *
 * @package Dragon_Glow
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        // ── Reveal on scroll (luxury fade-up) ─────────────────
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        entry.target.style.transitionDelay = '0ms';
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

            document.querySelectorAll('.reveal-on-scroll').forEach(function (el) {
                observer.observe(el);
            });
        } else {
            document.querySelectorAll('.reveal-on-scroll').forEach(function (el) {
                el.classList.add('active');
            });
        }

        // ── Hero parallax ─────────────────────────────────────
        var heroImg = document.getElementById('dg-shop-hero-img');
        if (heroImg) {
            window.addEventListener('scroll', function () {
                var scrolled = window.pageYOffset;
                heroImg.style.transform = 'translateY(' + (scrolled * 0.15) + 'px) scale(1.05)';
            }, { passive: true });
        }

        // ── Filter dropdown (Material style) ─────────────────
        var filterTrigger = document.getElementById('dg-shop-filter-trigger');
        var filterDropdown = document.getElementById('dg-filter-dropdown');
        var applyBtn = document.getElementById('dg-filter-apply');
        var resetBtn = document.getElementById('dg-filter-reset');

        function openFilterDropdown() {
            if (!filterDropdown || !filterTrigger) return;
            filterDropdown.classList.add('is-open');
            filterTrigger.classList.add('is-open');
            filterTrigger.setAttribute('aria-expanded', 'true');
        }
        function closeFilterDropdown() {
            if (!filterDropdown || !filterTrigger) return;
            filterDropdown.classList.remove('is-open');
            filterTrigger.classList.remove('is-open');
            filterTrigger.setAttribute('aria-expanded', 'false');
        }
        function toggleFilterDropdown() {
            if (!filterDropdown) return;
            if (filterDropdown.classList.contains('is-open')) {
                closeFilterDropdown();
            } else {
                openFilterDropdown();
            }
        }

        if (filterTrigger) {
            filterTrigger.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleFilterDropdown();
            });
        }
        if (applyBtn) {
            applyBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                applyFilters();
            });
        }
        if (resetBtn) {
            resetBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                clearAllFilters(true);
            });
        }

        // Close on outside click.
        document.addEventListener('click', function (e) {
            if (!filterDropdown || !filterDropdown.classList.contains('is-open')) return;
            var inside = filterDropdown.contains(e.target);
            var onTrigger = filterTrigger && filterTrigger.contains(e.target);
            if (!inside && !onTrigger) closeFilterDropdown();
        });

        // Close on ESC.
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) closeFilterDropdown();
        });

        // ── Mobile filter sheet ──────────────────────────────
        var mobileToggle = document.getElementById('dg-mobile-filter-toggle');
        var mobilePanel = document.getElementById('dg-mobile-filter-panel');
        var mobileOverlay = document.getElementById('dg-filter-overlay');
        var mobileClose = document.getElementById('dg-close-filter');

        function openMobileFilter() {
            if (!mobilePanel) return;
            mobilePanel.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeMobileFilter() {
            if (!mobilePanel) return;
            mobilePanel.classList.add('hidden');
            document.body.style.overflow = '';
        }
        if (mobileToggle) mobileToggle.addEventListener('click', openMobileFilter);
        if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobileFilter);
        if (mobileClose) mobileClose.addEventListener('click', closeMobileFilter);

        // ── Active filter state ──────────────────────────────
        var activeFilters = {
            category: null,
            skins: [],
            ingredients: [],
            minRating: 0
        };

        // Init activeFilters from URL (so reload restores selected state).
        (function initFiltersFromURL() {
            var params = new URLSearchParams(window.location.search);
            params.forEach(function (value, key) {
                if (key === 'skin_type[]') activeFilters.skins.push(value);
                if (key === 'ingredient[]') activeFilters.ingredients.push(value);
                if (key === 'rating') activeFilters.minRating = parseInt(value, 10) || 0;
                if (key === 'product_cat') activeFilters.category = value;
            });
        })();

        // Price slider: update the displayed max label in real time.
        var priceSlider = document.getElementById('price-range');
        var priceMaxLabel = document.getElementById('price-max-label');
        var priceMinLabel = document.getElementById('price-min-label');
        if (priceSlider && priceMaxLabel) {
            priceSlider.addEventListener('input', function () {
                var val = priceSlider.value;
                if (priceMaxLabel) priceMaxLabel.textContent = val;
                if (priceMinLabel) priceMinLabel.textContent = '0';
            });
        }

        // Category pill: click updates activeFilters and navigates via applyFilters.
        document.querySelectorAll('[data-category-item]').forEach(function (item) {
            item.addEventListener('click', function () {
                var cat = this.dataset.categoryItem;
                document.querySelectorAll('[data-category-item]').forEach(function (el) {
                    var isActive = el.dataset.categoryItem === cat;
                    el.classList.toggle('text-primary', isActive);
                    el.classList.toggle('font-semibold', isActive);
                    el.classList.toggle('text-on-surface-variant', !isActive);
                    var badge = el.querySelector('[data-badge]');
                    if (badge) {
                        badge.className = isActive
                            ? 'text-[10px] bg-tertiary-container text-on-tertiary-container px-2 py-0.5 rounded-full'
                            : 'text-[10px] bg-secondary-container px-2 py-0.5 rounded-full';
                    }
                });
                activeFilters.category = cat;
                updateActiveTags();
            });
        });

        // Rating: update activeFilters on change.
        document.querySelectorAll('[data-rating-filter]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                activeFilters.minRating = this.checked ? parseInt(this.dataset.ratingFilter, 10) : 0;
                updateActiveTags();
            });
        });

        // Ingredient pill toggle.
        function normalizeIngredientLabel(text) {
            if (!text) return '';
            return text
                .replace(/&#59;/gi, ';')
                .replace(/[;；]+/g, ', ')
                .replace(/\s*,\s*/g, ', ')
                .replace(/\s+/g, ' ')
                .trim();
        }

        document.querySelectorAll('[data-ingredient]').forEach(function (btn) {
            var rawLabel = btn.getAttribute('data-label') || btn.textContent || '';
            var normalizedLabel = normalizeIngredientLabel(rawLabel.replace(/\(\d+\)\s*$/, ''));
            if (normalizedLabel) {
                btn.setAttribute('data-label', normalizedLabel);
                var countMatch = (btn.textContent || '').match(/\((\d+)\)\s*$/);
                btn.textContent = countMatch ? (normalizedLabel + ' (' + countMatch[1] + ')') : normalizedLabel;
            }

            btn.addEventListener('click', function () {
                var ing = this.dataset.ingredient;
                var idx = activeFilters.ingredients.indexOf(ing);
                if (idx > -1) {
                    activeFilters.ingredients.splice(idx, 1);
                    this.className = 'px-3 py-1 bg-surface-container-high rounded-full text-label-sm font-label-sm hover:bg-primary-container transition-colors';
                } else {
                    activeFilters.ingredients.push(ing);
                    this.className = 'px-3 py-1 bg-primary-container text-on-primary-container rounded-full text-label-sm font-label-sm';
                }
                updateActiveTags();
            });
        });

        // Skin type checkbox.
        document.querySelectorAll('[data-skin]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                var skin = this.dataset.skin;
                var label = this.closest('label').querySelector('span');
                if (this.checked) {
                    if (activeFilters.skins.indexOf(skin) === -1) activeFilters.skins.push(skin);
                    if (label) label.classList.add('text-primary');
                } else {
                    activeFilters.skins = activeFilters.skins.filter(function (s) { return s !== skin; });
                    if (label) label.classList.remove('text-primary');
                }
                updateActiveTags();
            });
            if (checkbox.checked) {
                var label = checkbox.closest('label').querySelector('span');
                if (label) label.classList.add('text-primary');
            }
        });

        // Sync UI from activeFilters on page load (restored from URL).
        if (activeFilters.category) {
            document.querySelectorAll('[data-category-item]').forEach(function (el) {
                var isActive = el.dataset.categoryItem === activeFilters.category;
                el.classList.toggle('text-primary', isActive);
                el.classList.toggle('font-semibold', isActive);
                el.classList.toggle('text-on-surface-variant', !isActive);
                var badge = el.querySelector('[data-badge]');
                if (badge) {
                    badge.className = isActive
                        ? 'text-[10px] bg-tertiary-container text-on-tertiary-container px-2 py-0.5 rounded-full'
                        : 'text-[10px] bg-secondary-container px-2 py-0.5 rounded-full';
                }
            });
        }

        // Render active filter tags.
        function updateActiveTags() {
            var container = document.getElementById('dg-active-filter-tags');
            if (!container) return;
            container.innerHTML = '';

            var tags = [];
            function getCategoryLabel(value) {
                var node = document.querySelector('[data-category-item="' + value + '"]');
                return node ? (node.getAttribute('data-category-label') || value) : value;
            }
            function getSkinLabel(value) {
                var node = document.querySelector('[data-skin="' + value + '"]');
                if (!node) return value;
                return node.getAttribute('data-label') || value;
            }
            function getIngredientLabel(value) {
                var node = document.querySelector('[data-ingredient="' + value + '"]');
                if (!node) return value;
                return node.getAttribute('data-label') || value;
            }
            if (activeFilters.category) {
                tags.push({ label: getCategoryLabel(activeFilters.category), key: 'category', value: activeFilters.category });
            }
            activeFilters.skins.forEach(function (s) {
                tags.push({ label: getSkinLabel(s), key: 'skin', value: s });
            });
            activeFilters.ingredients.forEach(function (i) {
                tags.push({ label: getIngredientLabel(i), key: 'ingredient', value: i });
            });
            if (activeFilters.minRating > 0) {
                tags.push({ label: activeFilters.minRating + '★ & Up', key: 'rating', value: activeFilters.minRating });
            }
            if (priceSlider) {
                var maxVal = parseInt(priceSlider.value, 10);
                if (maxVal < 200) tags.push({ label: '$0 - $' + maxVal, key: 'price', value: maxVal });
            }
            if (tags.length === 0) return;

            tags.forEach(function (tag) {
                var span = document.createElement('span');
                span.className = 'inline-flex items-center gap-2 bg-secondary-container text-on-secondary-container px-4 py-1.5 rounded-full text-label-sm font-label-sm';
                span.innerHTML = tag.label +
                    '<span class="material-symbols-outlined text-[16px] cursor-pointer hover:rotate-90 transition-transform" ' +
                    'data-remove-key="' + tag.key + '" data-remove-value="' + tag.value + '">close</span>';
                container.appendChild(span);
            });

            var clearBtn = document.createElement('button');
            clearBtn.className = 'text-label-sm font-label-sm text-primary underline underline-offset-4 decoration-tertiary-container hover:text-on-surface transition-colors';
            clearBtn.textContent = 'Clear All';
            clearBtn.addEventListener('click', function () { clearAllFilters(true); });
            container.appendChild(clearBtn);

            container.querySelectorAll('[data-remove-key]').forEach(function (icon) {
                icon.addEventListener('click', function () {
                    removeFilter(this.dataset.removeKey, this.dataset.removeValue);
                });
            });
        }

        function removeFilter(key, value) {
            if (key === 'category') {
                activeFilters.category = null;
                document.querySelectorAll('[data-category-item]').forEach(function (el) {
                    el.classList.remove('text-primary', 'font-semibold');
                    el.classList.add('text-on-surface-variant');
                    var badge = el.querySelector('[data-badge]');
                    if (badge) badge.className = 'text-[10px] bg-secondary-container px-2 py-0.5 rounded-full';
                });
            } else if (key === 'skin') {
                activeFilters.skins = activeFilters.skins.filter(function (s) { return s !== value; });
                var cb = document.querySelector('[data-skin="' + value + '"]');
                if (cb) {
                    cb.checked = false;
                    var label = cb.closest('label') && cb.closest('label').querySelector('span');
                    if (label) label.classList.remove('text-primary');
                }
            } else if (key === 'ingredient') {
                activeFilters.ingredients = activeFilters.ingredients.filter(function (i) { return i !== value; });
                var btn = document.querySelector('[data-ingredient="' + value + '"]');
                if (btn) btn.className = 'px-3 py-1 bg-surface-container-high rounded-full text-label-sm font-label-sm hover:bg-primary-container transition-colors';
            } else if (key === 'rating') {
                activeFilters.minRating = 0;
                document.querySelectorAll('[data-rating-filter]').forEach(function (r) { r.checked = false; });
            } else if (key === 'price') {
                if (priceSlider) {
                    priceSlider.value = 200;
                    if (priceMaxLabel) priceMaxLabel.textContent = '200';
                }
            }
            applyFilters();
        }

        function clearAllFilters(navigate) {
            activeFilters.category = null;
            activeFilters.skins = [];
            activeFilters.ingredients = [];
            activeFilters.minRating = 0;
            document.querySelectorAll('[data-category-item]').forEach(function (el) {
                el.classList.remove('text-primary', 'font-semibold');
                el.classList.add('text-on-surface-variant');
                var badge = el.querySelector('[data-badge]');
                if (badge) badge.className = 'text-[10px] bg-secondary-container px-2 py-0.5 rounded-full';
            });
            document.querySelectorAll('[data-skin]').forEach(function (cb) {
                cb.checked = false;
                var label = cb.closest('label') && cb.closest('label').querySelector('span');
                if (label) label.classList.remove('text-primary');
            });
            document.querySelectorAll('[data-ingredient]').forEach(function (btn) {
                btn.className = 'px-3 py-1 bg-surface-container-high rounded-full text-label-sm font-label-sm hover:bg-primary-container transition-colors';
            });
            document.querySelectorAll('[data-rating-filter]').forEach(function (r) { r.checked = false; });
            if (priceSlider) {
                priceSlider.value = 200;
                if (priceMaxLabel) priceMaxLabel.textContent = '200';
            }
            if (navigate) {
                var url = new URL(window.location.href);
                ['skin_type', 'ingredient', 'rating', 'min_price', 'max_price', 'product_cat', 'paged', 'page'].forEach(function (p) {
                    url.searchParams.delete(p);
                });
                window.location.assign(url.toString());
                return;
            }
            applyFilters();
        }

        // Build a URL from the current activeFilters + price slider, then
        // navigate so the filter survives back/refresh/share.
        function applyFilters() {
            var url = new URL(window.location.href);
            ['skin_type', 'ingredient', 'rating', 'min_price', 'max_price', 'product_cat', 'paged', 'page'].forEach(function (p) {
                url.searchParams.delete(p);
            });
            if (activeFilters.category) url.searchParams.set('product_cat', activeFilters.category);
            activeFilters.skins.forEach(function (slug) {
                url.searchParams.append('skin_type[]', slug);
            });
            activeFilters.ingredients.forEach(function (slug) {
                url.searchParams.append('ingredient[]', slug);
            });
            if (activeFilters.minRating > 0) url.searchParams.set('rating', String(activeFilters.minRating));
            if (priceSlider) {
                var maxVal = parseInt(priceSlider.value, 10);
                if (maxVal < 200) {
                    url.searchParams.set('min_price', '0');
                    url.searchParams.set('max_price', String(maxVal));
                }
            }

            closeFilterDropdown();
            window.location.assign(url.toString());
        }

        // Show/hide product cards based on activeFilters (read from URL on load).
        function filterProducts() {
            document.querySelectorAll('[data-category]').forEach(function (card) {
                var catMatch = !activeFilters.category || card.dataset.category === activeFilters.category;
                var ratingMatch = activeFilters.minRating === 0 || parseInt(card.dataset.rating, 10) >= activeFilters.minRating;
                card.style.display = (catMatch && ratingMatch) ? '' : 'none';
            });
        }
        filterProducts();
        updateActiveTags();
    });
})();
