<?php
/**
 * Dragon Glow — Shop Archive (The Collection)
 * WooCommerce override: woocommerce/archive-product.php
 * Layout: hero + section header + magazine grid + philosophy + rituals
 * Matches template-shop.php (mock) structure.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- 1. Immersive hero banner -->
<?php get_template_part( 'template-parts/shop/hero' ); ?>

<!-- 2. Curated Glow section: header + product grid + pagination -->
<section class="py-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto" id="products">

	<?php get_template_part( 'template-parts/shop/section-header' ); ?>

	<!-- Active filter tags (driven by URL params) -->
	<?php get_template_part( 'template-parts/shop/active-filters' ); ?>

	<?php
		// ── Product loop ──────────────────────────────────────────
		$has_products    = have_posts();
		$found_posts     = (int) $GLOBALS['wp_query']->found_posts;
		$is_filtered_out = ! $has_products && $found_posts > 0;
		$is_empty_db    = ! $has_products && $found_posts === 0;
	?>

	<?php if ( $has_products ) : ?>

		<!-- Magazine staggered grid: 1/2/3 columns -->
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-gutter gap-y-32 dg-shop-grid" id="dg-product-grid">
			<?php
			$delay = 0;
			while ( have_posts() ) :
				the_post();
				set_query_var( 'dg_product_delay', $delay );
				get_template_part( 'template-parts/shop/product-card' );
				$delay += 100;
			endwhile;
			?>
		</div>

		<?php
		// ── Pagination ──────────────────────────────────────────
		get_template_part( 'template-parts/shop/pagination' );
		?>

	<?php elseif ( $is_filtered_out ) : ?>
		<!-- Filtered out: products exist but nothing matched -->
		<div class="text-center py-24">
			<div class="w-32 h-32 mx-auto bg-surface-container rounded-full flex items-center justify-center mb-6">
				<span class="material-symbols-outlined text-primary" style="font-size: 64px;">search_off</span>
			</div>
			<h2 class="font-headline text-headline-md text-primary mb-4">
				<?php esc_html_e( 'No products found', 'dragon-glow' ); ?>
			</h2>
			<p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-8">
				<?php esc_html_e( 'We could not find any products matching your current filters. Try adjusting your selection.', 'dragon-glow' ); ?>
			</p>
			<a class="btn-luxury bg-primary text-on-primary px-10 py-4 font-label-sm text-label-sm uppercase tracking-widest inline-block"
			   href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">
				<?php esc_html_e( 'Clear Filters', 'dragon-glow' ); ?>
			</a>
		</div>

	<?php else : ?>
		<!-- Database is empty -->
		<div class="text-center py-24">
			<div class="w-32 h-32 mx-auto bg-surface-container rounded-full flex items-center justify-center mb-6">
				<span class="material-symbols-outlined text-primary" style="font-size: 64px;">search</span>
			</div>
			<h2 class="font-headline text-headline-md text-primary mb-4">
				<?php esc_html_e( 'No products yet', 'dragon-glow' ); ?>
			</h2>
			<p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-8">
				<?php esc_html_e( 'Our collection is growing. New rituals arrive every season — check back soon for luminous additions.', 'dragon-glow' ); ?>
			</p>
			<a class="btn-luxury bg-primary text-on-primary px-10 py-4 font-label-sm text-label-sm uppercase tracking-widest inline-block"
			   href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Back to Home', 'dragon-glow' ); ?>
			</a>
		</div>
	<?php endif; ?>

</section>

<!-- 3. Mobile filter sheet -->
<div id="dg-mobile-filter-panel" class="fixed inset-0 z-[200] hidden">
	<div class="absolute inset-0 bg-inverse-surface/50" id="dg-filter-overlay"></div>
	<div class="absolute right-0 top-0 bottom-0 w-80 bg-surface overflow-y-auto p-6">
		<div class="flex justify-between items-center mb-6">
			<h3 class="font-headline text-xl text-primary"><?php esc_html_e( 'Filters', 'dragon-glow' ); ?></h3>
			<button type="button" id="dg-close-filter" class="p-2 hover:bg-surface-container rounded-full transition-colors">
				<span class="material-symbols-outlined">close</span>
			</button>
		</div>
		<?php get_template_part( 'template-parts/shop/filter-sidebar' ); ?>
	</div>
</div>

<!-- 4. Ingredient philosophy section -->
<?php get_template_part( 'template-parts/shop/philosophy' ); ?>

<!-- 5. Brand rituals section (AM / PM) -->
<?php get_template_part( 'template-parts/shop/rituals' ); ?>

<style>
	:root {
		--luxury-bezier: cubic-bezier(0.16, 1, 0.3, 1);
	}
	.material-symbols-outlined {
		font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
	}
</style>

<script>
(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {

		// ── Hero parallax ──────────────────────────────────────
		var heroImg = document.getElementById('dg-shop-hero-img');
		if (heroImg) {
			window.addEventListener('scroll', function() {
				var scrolled = window.pageYOffset;
				heroImg.style.transform = 'translateY(' + (scrolled * 0.15) + 'px) scale(1.05)';
			}, { passive: true });
		}

		// ── Reveal on scroll (luxury fade-up) ─────────────────
		if ('IntersectionObserver' in window) {
			var observer = new IntersectionObserver(function(entries) {
				entries.forEach(function(entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('active');
						observer.unobserve(entry.target);
					}
				});
			}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
			document.querySelectorAll('.reveal-on-scroll').forEach(function(el) {
				observer.observe(el);
			});
		} else {
			document.querySelectorAll('.reveal-on-scroll').forEach(function(el) {
				el.classList.add('active');
			});
		}

		// ── Filter dropdown (Material style) ───────────────────
		var filterTrigger = document.getElementById('dg-shop-filter-trigger');
		var filterDropdown = document.getElementById('dg-filter-dropdown');
		var filterClose = document.getElementById('dg-close-filter');

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
			filterTrigger.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				toggleFilterDropdown();
			});
		}
		var applyBtn = document.getElementById('dg-filter-apply');
		if (applyBtn) {
			applyBtn.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				applyFilters();
			});
		}
		var resetBtn = document.getElementById('dg-filter-reset');
		if (resetBtn) {
			resetBtn.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				clearAllFilters(true);
			});
		}

		// Close on outside click
		document.addEventListener('click', function(e) {
			if (!filterDropdown || !filterDropdown.classList.contains('is-open')) return;
			var inside = filterDropdown.contains(e.target);
			var onTrigger = filterTrigger && filterTrigger.contains(e.target);
			if (!inside && !onTrigger) {
				closeFilterDropdown();
			}
		});

		// Close on ESC
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' || e.keyCode === 27) {
				closeFilterDropdown();
			}
		});

		// ── Mobile filter sheet ────────────────────────────────
		var mobileToggle  = document.getElementById('dg-mobile-filter-toggle');
		var mobilePanel   = document.getElementById('dg-mobile-filter-panel');
		var mobileOverlay = document.getElementById('dg-filter-overlay');
		var mobileClose   = document.getElementById('dg-close-filter');

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
		if (mobileToggle)  mobileToggle.addEventListener('click', openMobileFilter);
		if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobileFilter);
		if (mobileClose)   mobileClose.addEventListener('click', closeMobileFilter);

		// ── Active filter tags ────────────────────────────────
		var activeFilters = {
			category: null,
			skins: [],
			ingredients: [],
			minRating: 0
		};

		// Init activeFilters from URL (so reload restores selected state).
		(function initFiltersFromURL() {
			var params = new URLSearchParams(window.location.search);
			params.forEach(function(value, key) {
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
			priceSlider.addEventListener('input', function() {
				var val = priceSlider.value;
				if (priceMaxLabel) priceMaxLabel.textContent = val;
				if (priceMinLabel) priceMinLabel.textContent = '0';
			});
		}

		// Category pill: click sets activeFilters.category (no link navigation).
		document.querySelectorAll('[data-category-item]').forEach(function(item) {
			item.addEventListener('click', function() {
				var cat = this.dataset.categoryItem;
				document.querySelectorAll('[data-category-item]').forEach(function(el) {
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
		document.querySelectorAll('[data-rating-filter]').forEach(function(radio) {
			radio.addEventListener('change', function() {
				activeFilters.minRating = this.checked ? parseInt(this.dataset.ratingFilter, 10) : 0;
				updateActiveTags();
			});
		});

		// Ingredient pill toggle.
		document.querySelectorAll('[data-ingredient]').forEach(function(btn) {
			btn.addEventListener('click', function() {
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
		document.querySelectorAll('[data-skin]').forEach(function(checkbox) {
			checkbox.addEventListener('change', function() {
				var skin = this.dataset.skin;
				var label = this.closest('label') && this.closest('label').querySelector('span');
				if (this.checked) {
					if (activeFilters.skins.indexOf(skin) === -1) activeFilters.skins.push(skin);
					if (label) label.classList.add('text-primary');
				} else {
					activeFilters.skins = activeFilters.skins.filter(function(s) { return s !== skin; });
					if (label) label.classList.remove('text-primary');
				}
				updateActiveTags();
			});
			if (checkbox.checked) {
				var label = checkbox.closest('label') && checkbox.closest('label').querySelector('span');
				if (label) label.classList.add('text-primary');
			}
		});

		// Sync UI from activeFilters on page load (restored from URL).
		// Category
		if (activeFilters.category) {
			document.querySelectorAll('[data-category-item]').forEach(function(el) {
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
			var catNames = {
				'serums': 'Serums & Oils',
				'cleansers': 'Cleansers',
				'moisturizers': 'Moisturizers',
				'sun-protection': 'Sun Protection'
			};
			if (activeFilters.category) {
				tags.push({ label: catNames[activeFilters.category] || activeFilters.category, key: 'category', value: activeFilters.category });
			}
			var skinNames = { 'dry':'Dry Skin', 'oily':'Oily Skin', 'sensitive':'Sensitive Skin', 'combination':'Combination' };
			activeFilters.skins.forEach(function(s) {
				tags.push({ label: skinNames[s] || s, key: 'skin', value: s });
			});
			var ingNames = { 'vitamin-c':'Vitamin C', 'retinol':'Retinol', 'hyaluronic':'Hyaluronic Acid', 'niacinamide':'Niacinamide' };
			activeFilters.ingredients.forEach(function(i) {
				tags.push({ label: ingNames[i] || i, key: 'ingredient', value: i });
			});
			if (activeFilters.minRating > 0) {
				tags.push({ label: activeFilters.minRating + '\u2605', key: 'rating', value: activeFilters.minRating });
			}
			if (priceSlider) {
				var maxVal = parseInt(priceSlider.value, 10);
				if (maxVal < 200) {
					tags.push({ label: '$0 - $' + maxVal, key: 'price', value: maxVal });
				}
			}
			if (tags.length === 0) return;

			tags.forEach(function(tag) {
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
			clearBtn.addEventListener('click', function() { clearAllFilters(true); });
			container.appendChild(clearBtn);

			container.querySelectorAll('[data-remove-key]').forEach(function(icon) {
				icon.addEventListener('click', function() {
					removeFilter(this.dataset.removeKey, this.dataset.removeValue);
				});
			});
		}

		function removeFilter(key, value) {
			if (key === 'category') {
				activeFilters.category = null;
				document.querySelectorAll('[data-category-item]').forEach(function(el) {
					el.classList.remove('text-primary', 'font-semibold');
					el.classList.add('text-on-surface-variant');
					var badge = el.querySelector('[data-badge]');
					if (badge) badge.className = 'text-[10px] bg-secondary-container px-2 py-0.5 rounded-full';
				});
			} else if (key === 'skin') {
				activeFilters.skins = activeFilters.skins.filter(function(s) { return s !== value; });
				var cb = document.querySelector('[data-skin="' + value + '"]');
				if (cb) {
					cb.checked = false;
					var label = cb.closest('label') && cb.closest('label').querySelector('span');
					if (label) label.classList.remove('text-primary');
				}
			} else if (key === 'ingredient') {
				activeFilters.ingredients = activeFilters.ingredients.filter(function(i) { return i !== value; });
				var btn = document.querySelector('[data-ingredient="' + value + '"]');
				if (btn) btn.className = 'px-3 py-1 bg-surface-container-high rounded-full text-label-sm font-label-sm hover:bg-primary-container transition-colors';
			} else if (key === 'rating') {
				activeFilters.minRating = 0;
				document.querySelectorAll('[data-rating-filter]').forEach(function(r) { r.checked = false; });
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
			document.querySelectorAll('[data-category-item]').forEach(function(el) {
				el.classList.remove('text-primary', 'font-semibold');
				el.classList.add('text-on-surface-variant');
				var badge = el.querySelector('[data-badge]');
				if (badge) badge.className = 'text-[10px] bg-secondary-container px-2 py-0.5 rounded-full';
			});
			document.querySelectorAll('[data-skin]').forEach(function(cb) {
				cb.checked = false;
				var label = cb.closest('label') && cb.closest('label').querySelector('span');
				if (label) label.classList.remove('text-primary');
			});
			document.querySelectorAll('[data-ingredient]').forEach(function(btn) {
				btn.className = 'px-3 py-1 bg-surface-container-high rounded-full text-label-sm font-label-sm hover:bg-primary-container transition-colors';
			});
			document.querySelectorAll('[data-rating-filter]').forEach(function(r) { r.checked = false; });
			if (priceSlider) {
				priceSlider.value = 200;
				if (priceMaxLabel) priceMaxLabel.textContent = '200';
			}
			if (navigate) {
				var url = new URL(window.location.href);
				['skin_type','ingredient','rating','min_price','max_price','product_cat','paged','page'].forEach(function(p) {
					url.searchParams.delete(p);
				});
				window.location.assign(url.toString());
				return;
			}
			applyFilters();
		}

		// Build a URL from the current activeFilters + price slider, then
		// navigate so the server can re-query the products.
		function applyFilters() {
			var url = new URL(window.location.href);
			['skin_type','ingredient','rating','min_price','max_price','product_cat','paged','page'].forEach(function(p) {
				url.searchParams.delete(p);
			});
			if (activeFilters.category) {
				url.searchParams.set('product_cat', activeFilters.category);
			}
			activeFilters.skins.forEach(function(slug) {
				url.searchParams.append('skin_type[]', slug);
			});
			activeFilters.ingredients.forEach(function(slug) {
				url.searchParams.append('ingredient[]', slug);
			});
			if (activeFilters.minRating > 0) {
				url.searchParams.set('rating', String(activeFilters.minRating));
			}
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

		updateActiveTags();
	});
})();
</script>

<?php get_footer();
