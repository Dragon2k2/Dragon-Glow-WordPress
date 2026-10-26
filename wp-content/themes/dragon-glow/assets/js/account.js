/**
 * Dragon Glow — Account Page JS
 * Vanilla — no React, no motion/react.
 *
 * Responsibilities:
 *  - Toggle the account nav dropdown on mobile (<1024px).
 *  - Click-outside + Escape close for the nav dropdown.
 *  - Password visibility toggle (custom + reused by WC form).
 *  - Scroll reveal for [data-sr] elements (FOUC-guard; mirror of main.js).
 *  - Animated count-up for [data-count-to] (stat values).
 *  - AJAX navigation for account panels (Orders, Addresses, etc.).
 *  - History API (pushState/popstate) for browser back/forward.
 *  - Loading states with skeleton screens.
 *  - Address form save UX (disable + spinner; POST handled by WooCommerce).
 *  - "Use billing instead" branded confirm modal + busy state (POST clears shipping).
 *  - Sign-out confirmation dialog (focus trap, Esc, Motion stagger, busy state).
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	const prefersReduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
	const root = document.querySelector('.dg-account');
	
	// AJAX navigation state.
	let isLoading = false;
	let currentEndpoint = '';

	function initNavToggle() {
		if (!root) return;
		const nav = root.querySelector('.dg-account-nav');
		const toggle = root.querySelector('#dg-account-nav-toggle');
		const list = root.querySelector('#dg-account-nav-list');
		if (!nav || !toggle || !list) return;

		function setOpen(open) {
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			nav.classList.toggle('is-open', open);
		}

		// Auto-open at >=1024px (matches CSS @media breakpoint — sidebar stays
		// expanded as a vertical nav on desktop, collapses to dropdown on smaller
		// screens where visibility is only initiated by a user tap).
		function syncToViewport() {
			const desktop = window.matchMedia('(min-width: 1024px)').matches;
			if (desktop) {
				setOpen(true);
			} else {
				setOpen(false);
			}
		}
		syncToViewport();

		toggle.addEventListener('click', function (event) {
			event.stopPropagation();
			const expanded = toggle.getAttribute('aria-expanded') === 'true';
			setOpen(!expanded);
		});

		// Click outside closes the dropdown on mobile.
		document.addEventListener('click', function (event) {
			if (!nav.classList.contains('is-open')) return;
			if (window.matchMedia('(min-width: 1024px)').matches) return;
			if (nav.contains(event.target)) return;
			setOpen(false);
		});

		// Escape closes.
		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && nav.classList.contains('is-open')) {
				setOpen(false);
				toggle.focus();
			}
		});

		// Keep state in sync on resize (e.g. rotate tablet / drag to desktop).
		window.addEventListener('resize', syncToViewport, { passive: true });
	}

	function initPasswordToggle() {
		document.querySelectorAll('[data-dg-toggle-password]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				const selector = btn.getAttribute('data-dg-toggle-password');
				const input = selector ? document.querySelector(selector) : null;
				if (!input) return;

				const icon = btn.querySelector('.material-symbols-outlined');
				const showing = input.type === 'password';
				if (showing) {
					input.type = 'text';
					if (icon) icon.textContent = 'visibility_off';
					btn.setAttribute('aria-label', btn.getAttribute('data-label-show') || 'Hide password');
				} else {
					input.type = 'password';
					if (icon) icon.textContent = 'visibility';
					btn.setAttribute('aria-label', btn.getAttribute('data-label-hide') || 'Show password');
				}
			});
		});
	}

	function initScrollReveal() {
		if (!root) return;
		const targets = root.querySelectorAll('[data-sr]');

		// No-JS path already shows content; in JS path FOUC guard hides it first.
		// IntersectionObserver reveals each item once visible.
		if (!('IntersectionObserver' in window) || prefersReduced) {
			targets.forEach(function (el) {
				el.classList.add('is-visible');
			});
			return;
		}

		const observer = new IntersectionObserver(
			function (entries, obs) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-visible');
						obs.unobserve(entry.target);
					}
				});
			},
			{
				root: null,
				rootMargin: '0px 0px -60px 0px',
				threshold: 0.05,
			}
		);

		targets.forEach(function (el) {
			observer.observe(el);
		});
	}

	function animateCountTo(el) {
		const target = parseInt(el.getAttribute('data-count-to') || '0', 10);
		const duration = 900;
		if (Number.isNaN(target)) return;
		const start = performance.now();
		const startValue = 0;

		function tick(now) {
			const t = Math.min(1, (now - start) / duration);
			const eased = 1 - Math.pow(1 - t, 3); // easeOutCubic
			const current = Math.round(startValue + (target - startValue) * eased);
			el.textContent = current.toLocaleString();
			if (t < 1) {
				requestAnimationFrame(tick);
			} else {
				el.textContent = target.toLocaleString();
			}
		}

		requestAnimationFrame(tick);
	}

	function initCountUp() {
		if (!root) return;
		const counters = root.querySelectorAll('[data-count-to]');
		if (counters.length === 0) return;

		if (prefersReduced || !('IntersectionObserver' in window)) {
			counters.forEach(function (el) {
				const v = parseInt(el.getAttribute('data-count-to') || '0', 10);
				el.textContent = v.toLocaleString();
			});
			return;
		}

		const observer = new IntersectionObserver(
			function (entries, obs) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						animateCountTo(entry.target);
						obs.unobserve(entry.target);
					}
				});
			},
			{ threshold: 0.4 }
		);

		counters.forEach(function (el) {
			observer.observe(el);
		});
	}

	/**
	 * AJAX navigation — intercept nav link clicks and load content via AJAX.
	 */
	function initAjaxNavigation() {
		if (!root || !window.dgAjax) return;

		const content = root.querySelector('.dg-account__content');
		const navLinks = root.querySelectorAll('.dg-account-nav__link:not(.dg-account-nav__link--logout)');

		if (!content || navLinks.length === 0) return;

		// Extract current endpoint from URL on page load.
		currentEndpoint = getCurrentEndpointFromURL();

		navLinks.forEach(function (link) {
			link.addEventListener('click', function (event) {
				// Allow Ctrl/Cmd+Click or middle-click to open in new tab.
				if (event.ctrlKey || event.metaKey || event.button === 1) {
					return;
				}

				event.preventDefault();

				if (isLoading) return;

				const endpoint = link.getAttribute('href');
				if (!endpoint) return;

				loadPanel(endpoint, true);
			});
		});

		// Pagination links (e.g., /my-account/orders/page/2/) must do full page
		// navigation, not AJAX. AJAX strips query params, so we lose the paged
		// number. By-passing AJAX here keeps the pagination working correctly.
		const paginationLinks = root.querySelectorAll('.woocommerce-pagination a.page-numbers, .woocommerce-pagination a.woocommerce-button');
		paginationLinks.forEach(function (link) {
			link.addEventListener('click', function (event) {
				// Allow Ctrl/Cmd+Click or middle-click to open in new tab.
				if (event.ctrlKey || event.metaKey || event.button === 1) {
					return;
				}
				// Full page navigation — no AJAX, no history push.
				event.preventDefault();
				window.location.href = link.getAttribute('href');
			});
		});

		// Browser back/forward support.
		window.addEventListener('popstate', function (event) {
			if (event.state && event.state.endpoint !== undefined) {
				loadPanel(event.state.endpoint, false);
			}
		});

		// Set initial history state.
		if (window.history.state === null) {
			const currentURL = window.location.href;
			window.history.replaceState({ endpoint: currentEndpoint }, '', currentURL);
		}
	}

	/**
	 * Extract endpoint slug from current URL.
	 *
	 * @return {string} Endpoint slug (e.g., 'orders', 'edit-address', or '').
	 */
	function getCurrentEndpointFromURL() {
		const path = window.location.pathname;
		const base = '/my-account/';
		const idx = path.indexOf(base);
		if (idx === -1) return '';

		const after = path.substring(idx + base.length);
		const segment = after.split('/')[0];
		return segment || '';
	}

	/**
	 * Extract endpoint slug from URL.
	 *
	 * @param {string} url Full URL.
	 * @return {string} Endpoint slug.
	 */
	function extractEndpoint(url) {
		try {
			const urlObj = new URL(url, window.location.origin);
			const path = urlObj.pathname;
			const base = '/my-account/';
			const idx = path.indexOf(base);
			if (idx === -1) return '';

			const after = path.substring(idx + base.length);
			const segment = after.split('/')[0];
			return segment || '';
		} catch (e) {
			return '';
		}
	}

	/**
	 * Load panel content via AJAX.
	 *
	 * @param {string} url Target URL (e.g., '/my-account/orders').
	 * @param {boolean} pushHistory Whether to push new history state.
	 */
	function loadPanel(url, pushHistory) {
		if (isLoading) return;

		const endpoint = extractEndpoint(url);

		// If clicking the same endpoint, do nothing.
		// Exception: allow navigation when URL has ?paged=N or ?address=
		// (list ↔ edit address within the same endpoint).
		const urlObj = parseURL(url);
		const hasPaged = urlObj && urlObj.searchParams.has('paged');
		const hasAddress = urlObj && urlObj.searchParams.has('address');
		if (endpoint === currentEndpoint && !hasPaged && !hasAddress) return;

		isLoading = true;
		currentEndpoint = endpoint;

		const content = root.querySelector('.dg-account__content');
		if (!content) {
			isLoading = false;
			return;
		}

		// Show loading skeleton.
		showLoadingSkeleton(content);

		// Update active nav link immediately for instant feedback.
		updateActiveNav(endpoint);

		// AJAX request — pass full URL so server can read ?paged=N.
		const formData = new FormData();
		formData.append('action', 'dg_load_account_panel');
		formData.append('endpoint', endpoint);
		formData.append('nonce', window.dgAjax.nonce);
		formData.append('request_uri', url); // Pass full URL for pagination support.

		fetch(window.dgAjax.url, {
			method: 'POST',
			body: formData,
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Network response was not ok');
				}
				return response.json();
			})
			.then(function (data) {
				if (data.success && data.data && data.data.html) {
					// Inject new content and unlock pointer events.
					// showLoadingSkeleton() sets aria-busy="true" which maps to
					// pointer-events:none on .dg-account__content — must clear
					// on success or buttons stay unclickable until hard refresh.
					content.innerHTML = data.data.html;
					content.removeAttribute('aria-busy');

				// Update document title.
				if (data.data.title) {
					document.title = data.data.title + ' – ' + getBaseSiteTitle();
				}

					// Push history state.
					if (pushHistory) {
						window.history.pushState({ endpoint: endpoint }, '', url);
					}

					// Re-initialize scroll reveal and count-up for new content.
					setTimeout(function () {
						initScrollReveal();
						initCountUp();
						initPasswordToggle(); // Re-bind password toggles in new content.
						initPaginationLinks(); // Re-bind pagination links in new content.
						initAddressFormSave(); // Re-bind address save UX after AJAX inject.
						initUseBillingInstead(); // Re-bind ship-to-billing after AJAX inject.
						
						// Re-initialize order modal triggers after AJAX content load.
						if (window.dgOrderModal && typeof window.dgOrderModal.init === 'function') {
							window.dgOrderModal.init();
						}
					}, 50);

					// Scroll to top of content smoothly.
					content.scrollIntoView({ behavior: prefersReduced ? 'auto' : 'smooth', block: 'start' });

					// Announce to screen readers.
					announceToScreenReader(data.data.title || 'Content loaded');
				} else {
					throw new Error(data.data && data.data.message ? data.data.message : 'Failed to load content');
				}
		})
		.catch(function (error) {
			showErrorState(content, error.message);
			})
			.finally(function () {
				isLoading = false;
			});
	}

	/**
	 * Show loading skeleton in content area.
	 *
	 * @param {HTMLElement} container Content container.
	 */
	function showLoadingSkeleton(container) {
		container.innerHTML = '<div class="dg-account-panel dg-account-loading">' +
			'<div class="dg-account-loading__header">' +
			'<div class="dg-skeleton dg-skeleton--title"></div>' +
			'</div>' +
			'<div class="dg-account-loading__body">' +
			'<div class="dg-skeleton dg-skeleton--line"></div>' +
			'<div class="dg-skeleton dg-skeleton--line"></div>' +
			'<div class="dg-skeleton dg-skeleton--line" style="width: 60%;"></div>' +
			'</div>' +
			'</div>';
		container.setAttribute('aria-busy', 'true');
		container.setAttribute('aria-live', 'polite');
	}

	/**
	 * Show error state in content area.
	 *
	 * @param {HTMLElement} container Content container.
	 * @param {string} message Error message.
	 */
	function showErrorState(container, message) {
		container.innerHTML = '<div class="dg-account-panel dg-account-panel--center">' +
			'<span class="material-symbols-outlined dg-account-empty__icon">error</span>' +
			'<h2 class="dg-account-panel__title">Something went wrong</h2>' +
			'<p class="dg-account-empty__text">' + escapeHTML(message) + '</p>' +
			'<button type="button" class="dg-btn dg-btn--primary" onclick="window.location.reload()">Refresh page</button>' +
			'</div>';
		container.removeAttribute('aria-busy');
	}

	/**
	 * Update active class on nav links.
	 *
	 * @param {string} endpoint Current endpoint slug.
	 */
	function updateActiveNav(endpoint) {
		// "edit-address" is a sub-view of the addresses management flow —
		// treat it as "address-details" so the "Addresses" nav link stays
		// active while editing billing/shipping. Mirrors the PHP-side rule
		// in inc/woocommerce/account/dashboard.php.
		const matchEndpoint = endpoint === 'edit-address' ? 'address-details' : endpoint;

		// The logout link is excluded here: its href is a `?action=logout`
		// query string with no `/my-account/{endpoint}/` segment, so
		// extractEndpoint() resolves it to '' — the same value as the
		// Dashboard endpoint — which would otherwise mark it active too.
		const navLinks = root.querySelectorAll('.dg-account-nav__link:not(.dg-account-nav__link--logout)');
		navLinks.forEach(function (link) {
			const linkEndpoint = extractEndpoint(link.getAttribute('href') || '');
			if (linkEndpoint === matchEndpoint) {
				link.classList.add('is-active');
				link.setAttribute('aria-current', 'page');
			} else {
				link.classList.remove('is-active');
				link.removeAttribute('aria-current');
			}
		});

		// Update mobile toggle label.
		const toggle = root.querySelector('#dg-account-nav-toggle');
		const toggleLabel = root.querySelector('.dg-account-nav__toggle-label');
		if (toggle && toggleLabel) {
			const activeLink = root.querySelector('.dg-account-nav__link.is-active');
			if (activeLink) {
				const label = activeLink.querySelector('.dg-account-nav__label');
				if (label) {
					toggleLabel.textContent = 'Navigation: ' + label.textContent;
				}
			}
		}
	}

	/**
	 * Get base site title (without page title).
	 *
	 * @return {string} Base site title.
	 */
	function getBaseSiteTitle() {
		const parts = document.title.split(' – ');
		return parts.length > 1 ? parts[parts.length - 1] : 'My Dragons Glow';
	}

	/**
	 * Announce content change to screen readers.
	 *
	 * @param {string} message Message to announce.
	 */
	function announceToScreenReader(message) {
		const announcer = document.getElementById('dg-sr-announcer');
		if (announcer) {
			announcer.textContent = message;
		} else {
			const div = document.createElement('div');
			div.id = 'dg-sr-announcer';
			div.className = 'sr-only';
			div.setAttribute('role', 'status');
			div.setAttribute('aria-live', 'polite');
			div.textContent = message;
			document.body.appendChild(div);
		}
	}

	/**
	 * Escape HTML to prevent XSS.
	 *
	 * @param {string} str String to escape.
	 * @return {string} Escaped string.
	 */
	function escapeHTML(str) {
		const div = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	}

	/**
	 * Parse URL into an object (cross-browser).
	 *
	 * @param {string} url URL to parse.
	 * @return {URL|null} Parsed URL object or null on failure.
	 */
	function parseURL(url) {
		try {
			return new URL(url, window.location.origin);
		} catch (e) {
			return null;
		}
	}

	/**
	 * Address edit form — disable Save on submit to prevent double POST.
	 * Native form POST still goes to WC_Form_Handler::save_address.
	 */
	function initAddressFormSave() {
		if (!root) return;

		const forms = root.querySelectorAll('.dg-account-address-edit__form');
		forms.forEach(function (form) {
			if (form.dataset.dgAddressBound === '1') return;
			form.dataset.dgAddressBound = '1';

			form.addEventListener('submit', function () {
				const btn = form.querySelector('.dg-account-address-edit__save');
				if (!btn || btn.disabled) return;

				btn.disabled = true;
				btn.classList.add('is-saving');
				btn.setAttribute('aria-busy', 'true');

				const icon = btn.querySelector('.dg-account-address-edit__save-icon');
				const label = btn.querySelector('.dg-account-address-edit__save-label');
				if (icon) {
					icon.textContent = 'progress_activity';
					icon.classList.add('is-spinning');
				}
				if (label) {
					label.textContent = 'Saving…';
				}
			});
		});
	}

	/**
	 * "Use billing instead" — branded confirm modal, then POST clear shipping.
	 * Server handler: dg_handle_use_billing_instead() on template_redirect.
	 */
	function initUseBillingInstead() {
		if (!root) return;

		const modal = document.getElementById('dg-account-confirm');
		const forms = root.querySelectorAll('[data-dg-use-billing]');
		if (!modal || forms.length === 0) return;

		const dialog = modal.querySelector('.dg-account-confirm__dialog');
		const titleEl = modal.querySelector('#dg-account-confirm-title');
		const bodyEl = modal.querySelector('#dg-account-confirm-body');
		const okBtn = modal.querySelector('[data-dg-confirm-ok]');
		const cancelBtn = modal.querySelector('[data-dg-confirm-cancel]');
		const overlay = modal.querySelector('[data-dg-confirm-dismiss]');

		let pendingForm = null;
		let lastFocus = null;

		function setBusy(form) {
			const btn = form.querySelector('.dg-account-use-billing__btn');
			if (!btn || btn.disabled) return;
			btn.disabled = true;
			btn.classList.add('is-saving');
			btn.setAttribute('aria-busy', 'true');
			const icon = btn.querySelector('.dg-account-use-billing__icon');
			const label = btn.querySelector('.dg-account-use-billing__label');
			if (icon) {
				icon.textContent = 'progress_activity';
				icon.classList.add('is-spinning');
			}
			if (label) {
				label.textContent = 'Updating…';
			}
		}

		function closeConfirm() {
			modal.hidden = true;
			document.documentElement.classList.remove('dg-account-confirm-open');
			pendingForm = null;
			if (lastFocus && typeof lastFocus.focus === 'function') {
				lastFocus.focus();
			}
			lastFocus = null;
		}

		function openConfirm(form) {
			pendingForm = form;
			lastFocus = document.activeElement;

			if (titleEl) {
				titleEl.textContent = form.getAttribute('data-confirm-title') || 'Use billing instead?';
			}
			if (bodyEl) {
				bodyEl.textContent = form.getAttribute('data-confirm') ||
					'Remove your shipping address and deliver orders to your billing address instead?';
			}
			if (okBtn) {
				okBtn.textContent = form.getAttribute('data-confirm-ok') || 'Use billing';
			}
			if (cancelBtn) {
				cancelBtn.textContent = form.getAttribute('data-confirm-cancel') || 'Cancel';
			}

			modal.hidden = false;
			document.documentElement.classList.add('dg-account-confirm-open');
			if (dialog) {
				dialog.focus();
			} else if (okBtn) {
				okBtn.focus();
			}
		}

		forms.forEach(function (form) {
			if (form.dataset.dgUseBillingBound === '1') return;
			form.dataset.dgUseBillingBound = '1';

			form.addEventListener('submit', function (event) {
				if (form.dataset.dgConfirmed === '1') {
					setBusy(form);
					return;
				}
				event.preventDefault();
				openConfirm(form);
			});
		});

		if (modal.dataset.dgConfirmBound === '1') return;
		modal.dataset.dgConfirmBound = '1';

		function onConfirmOk() {
			if (!pendingForm) {
				closeConfirm();
				return;
			}
			const form = pendingForm;
			form.dataset.dgConfirmed = '1';
			setBusy(form);
			closeConfirm();
			form.submit();
		}

		if (okBtn) okBtn.addEventListener('click', onConfirmOk);
		if (cancelBtn) cancelBtn.addEventListener('click', closeConfirm);
		if (overlay) overlay.addEventListener('click', closeConfirm);

		document.addEventListener('keydown', function (event) {
			if (modal.hidden) return;
			if (event.key === 'Escape') {
				event.preventDefault();
				closeConfirm();
			}
		});
	}

	/**
	 * Bind pagination links after content is loaded via AJAX.
	 * This is called from loadPanel() after injecting new content.
	 */
	function initPaginationLinks() {
		if (!root) return;
		const paginationLinks = root.querySelectorAll('.woocommerce-pagination a.page-numbers, .woocommerce-pagination a.woocommerce-button');
		paginationLinks.forEach(function (link) {
			// Remove existing listener by cloning (simple way to avoid duplicates).
			const newLink = link.cloneNode(true);
			link.parentNode.replaceChild(newLink, link);
			newLink.addEventListener('click', function (event) {
				if (event.ctrlKey || event.metaKey || event.button === 1) {
					return;
				}
				event.preventDefault();
				window.location.href = newLink.getAttribute('href');
			});
		});
	}

	/**
	 * Sign-out confirmation dialog.
	 *
	 * Intercepts clicks on `[data-dg-signout]` (sidebar logout link) and shows
	 * #dg-signout. On confirm we follow the link's own href, which already
	 * carries the wp_logout_url() nonce. Without JS the link still works.
	 *
	 * Motion (CDN) is optional: it staggers the dialog content in. Overlay and
	 * dialog transitions are plain CSS (`.is-open`), so the dialog works even
	 * if the CDN is unreachable or reduced motion is on.
	 */
	function initSignOutConfirm() {
		const modal = document.getElementById('dg-signout');
		const triggers = document.querySelectorAll('[data-dg-signout]');
		if (!modal || triggers.length === 0) return;
		if (modal.dataset.dgSignoutBound === '1') return;
		modal.dataset.dgSignoutBound = '1';

		const dialog = modal.querySelector('.dg-signout__dialog');
		const overlay = modal.querySelector('[data-dg-signout-dismiss]');
		const cancelBtn = modal.querySelector('[data-dg-signout-cancel]');
		const confirmBtn = modal.querySelector('[data-dg-signout-confirm]');
		const items = modal.querySelectorAll('.dg-signout__item');
		if (!dialog || !cancelBtn || !confirmBtn) return;

		const EXIT_MS = 200;
		let activeTrigger = null;
		let closeTimer = null;
		let isBusy = false;
		let motion = null;

		// Warm up Motion in the background so the first open is already smooth.
		if (!prefersReduced) {
			import('https://cdn.jsdelivr.net/npm/motion@11/+esm')
				.then(function (mod) { motion = mod; })
				.catch(function () { motion = null; });
		}

		function playItemsIn() {
			if (prefersReduced || !motion) return;
			motion.animate(
				items,
				{ opacity: [0, 1], transform: ['translateY(12px)', 'translateY(0px)'] },
				{
					duration: 0.45,
					ease: [0.22, 1, 0.36, 1],
					delay: motion.stagger(0.05, { startDelay: 0.08 })
				}
			);
		}

		function open(trigger) {
			if (!modal.hidden) return;
			window.clearTimeout(closeTimer);
			activeTrigger = trigger;

			// Populate user info (avatar, name, email) from current logged-in user.
			// Use REST API endpoint for reliable current user data.
			fetch('/wp-json/wp/v2/users/me', {
				credentials: 'same-origin',
				headers: {
					'X-WP-Nonce': window.wpApiSettings?.nonce || ''
				}
			})
			.then(response => {
				if (!response.ok) throw new Error('Failed to fetch user data');
				return response.json();
			})
			.then(user => {
				// Populate user name.
				const nameEl = modal.querySelector('#dg-signout-user-name');
				if (nameEl && user.name) {
					nameEl.textContent = user.name;
				}

				// Populate user email.
				const emailEl = modal.querySelector('#dg-signout-user-email');
				if (emailEl && user.email) {
					emailEl.textContent = user.email;
				}

				// Populate avatar using WordPress avatar endpoint.
				const avatarEl = modal.querySelector('#dg-signout-avatar');
				if (avatarEl && user.id) {
					// Use get_avatar_url equivalent via REST meta.
					const avatarUrl = user.avatar_urls?.[96] || user.avatar_urls?.[48] || user.avatar_urls?.[24];
					if (avatarUrl) {
						const img = document.createElement('img');
						img.src = avatarUrl;
						img.alt = '';
						img.className = 'dg-signout__avatar-img';
						avatarEl.innerHTML = '';
						avatarEl.appendChild(img);
					}
				}
			})
			.catch(error => {
				console.warn('Could not fetch current user data:', error);
				// Fallback: leave placeholder content as-is
			});

			modal.hidden = false;
			document.documentElement.classList.add('dg-signout-open');
			// Force reflow so the CSS transition runs from the closed state.
			void dialog.offsetWidth;
			modal.classList.add('is-open');
			playItemsIn();

			// Default focus on the safe action.
			cancelBtn.focus();
		}

		function close() {
			if (modal.hidden || isBusy) return;
			modal.classList.remove('is-open');
			document.documentElement.classList.remove('dg-signout-open');

			closeTimer = window.setTimeout(function () {
				modal.hidden = true;
				items.forEach(function (el) { el.style.opacity = ''; el.style.transform = ''; });
				if (activeTrigger && typeof activeTrigger.focus === 'function') {
					activeTrigger.focus();
				}
				activeTrigger = null;
			}, prefersReduced ? 0 : EXIT_MS);
		}

		function setBusy() {
			isBusy = true;
			confirmBtn.classList.add('is-busy');
			confirmBtn.setAttribute('aria-busy', 'true');
			cancelBtn.disabled = true;

			const icon = confirmBtn.querySelector('.dg-signout__btn-icon');
			const label = confirmBtn.querySelector('.dg-signout__btn-label');
			if (icon) icon.textContent = 'progress_activity';
			if (label) label.textContent = modal.getAttribute('data-busy-label') || 'Signing out…';
		}

		function resetBusy() {
			isBusy = false;
			confirmBtn.classList.remove('is-busy');
			confirmBtn.removeAttribute('aria-busy');
			cancelBtn.disabled = false;
		}

		triggers.forEach(function (trigger) {
			trigger.addEventListener('click', function (event) {
				// Let Ctrl/Cmd/middle-click through (opens logout URL in a new tab).
				if (event.ctrlKey || event.metaKey || event.shiftKey || event.button === 1) return;
				event.preventDefault();
				open(trigger);
			});
		});

		confirmBtn.addEventListener('click', function () {
			if (isBusy || !activeTrigger) return;
			const href = activeTrigger.getAttribute('href');
			if (!href) return;
			setBusy();
			announceToScreenReader(modal.getAttribute('data-busy-label') || 'Signing out…');
			window.location.href = href;
		});

		cancelBtn.addEventListener('click', close);
		if (overlay) overlay.addEventListener('click', close);

		// Escape closes; Tab is trapped between the two buttons.
		document.addEventListener('keydown', function (event) {
			if (modal.hidden) return;

			if (event.key === 'Escape') {
				event.preventDefault();
				close();
				return;
			}

			if (event.key === 'Tab') {
				const focusables = [cancelBtn, confirmBtn].filter(function (el) { return !el.disabled; });
				if (focusables.length === 0) {
					event.preventDefault();
					return;
				}
				const first = focusables[0];
				const last = focusables[focusables.length - 1];
				if (event.shiftKey && (document.activeElement === first || document.activeElement === dialog)) {
					event.preventDefault();
					last.focus();
				} else if (!event.shiftKey && document.activeElement === last) {
					event.preventDefault();
					first.focus();
				}
			}
		});

		// Back/forward cache: returning to this page must not show a stuck spinner.
		window.addEventListener('pageshow', function (event) {
			if (!event.persisted) return;
			resetBusy();
			modal.classList.remove('is-open');
			modal.hidden = true;
			document.documentElement.classList.remove('dg-signout-open');
		});
	}

	function init() {
		initNavToggle();
		initPasswordToggle();
		initScrollReveal();
		initCountUp();
		initAjaxNavigation();
		initAddressFormSave();
		initUseBillingInstead();
		initSignOutConfirm();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
