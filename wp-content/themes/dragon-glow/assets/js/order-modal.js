/**
 * Dragon Glow — Order Detail Modal
 * Split Timeline Modal (Modal 4) with Motion API animations
 * Vanilla JavaScript — no React, ES module for Motion integration
 *
 * Features:
 *  - Fetch order details via AJAX
 *  - Split timeline + details layout
 *  - Motion API animations (fade, slide, stagger)
 *  - Focus trap & keyboard navigation
 *  - Accessibility (ARIA, screen reader announcements)
 *  - Respects prefers-reduced-motion
 *
 * @package Dragon_Glow
 */

(function () {
	'use strict';

	const prefersReduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
	let currentModal = null;
	let focusTrap = null;
	let lastFocusedElement = null;

	/**
	 * Initialize modal triggers on page load
	 */
	function init() {
		// Target both custom action buttons and default WooCommerce view buttons
		const triggers = document.querySelectorAll('.dg-account-order__action[href*="view-order"], .woocommerce-button.view[href*="view-order"]');
		
		triggers.forEach(function (trigger) {
			// Mark as bound to avoid double-binding after AJAX refresh
			if (trigger.dataset.dgModalBound === '1') return;
			trigger.dataset.dgModalBound = '1';

			trigger.addEventListener('click', function (event) {
				event.preventDefault();
				const orderUrl = trigger.getAttribute('href');
				if (orderUrl) {
					openModal(orderUrl);
				}
			});
		});
	}

	/**
	 * Open modal and fetch order details
	 * @param {string} orderUrl - WooCommerce view-order URL
	 */
	function openModal(orderUrl) {
		// Extract order ID from URL
		const orderId = extractOrderId(orderUrl);
		if (!orderId) {
			console.error('Could not extract order ID from URL:', orderUrl);
			return;
		}

		// Store last focused element for focus restoration
		lastFocusedElement = document.activeElement;

		// Create modal container
		const modal = createModalElement();
		document.body.appendChild(modal);
		currentModal = modal;

		// Add body class to prevent scroll
		document.body.classList.add('dg-order-modal-open');

		// Show modal with loading state
		showLoadingState(modal);

		// Fetch order data
		fetchOrderData(orderId)
			.then(function (data) {
				renderOrderContent(modal, data);
				animateModalIn(modal);
			})
			.catch(function (error) {
				console.error('Failed to fetch order data:', error);
				renderErrorState(modal, error.message);
			});

		// Setup event listeners
		setupModalListeners(modal);
	}

	/**
	 * Extract order ID from WooCommerce view-order URL
	 * @param {string} url - Order URL
	 * @return {string|null} Order ID or null
	 */
	function extractOrderId(url) {
		// Match patterns: /view-order/123/ or /view-order/123
		const match = url.match(/\/view-order\/(\d+)\/?/);
		return match ? match[1] : null;
	}

	/**
	 * Create modal DOM structure
	 * @return {HTMLElement} Modal element
	 */
	function createModalElement() {
		const modal = document.createElement('div');
		modal.className = 'dg-order-modal';
		modal.setAttribute('role', 'dialog');
		modal.setAttribute('aria-modal', 'true');
		modal.setAttribute('aria-labelledby', 'dg-order-modal-title');
		modal.setAttribute('aria-hidden', 'false');

		modal.innerHTML = `
			<div class="dg-order-modal__overlay" data-modal-close></div>
			<div class="dg-order-modal__content">
				<button type="button" class="dg-order-modal__close" aria-label="Close modal" data-modal-close>
					<span class="material-symbols-outlined">close</span>
				</button>
				<div class="dg-order-modal__inner">
					<!-- Content will be injected here -->
				</div>
			</div>
		`;

		return modal;
	}

	/**
	 * Show loading skeleton in modal
	 * @param {HTMLElement} modal - Modal element
	 */
	function showLoadingState(modal) {
		const inner = modal.querySelector('.dg-order-modal__inner');
		inner.innerHTML = `
			<div class="dg-order-modal__timeline" style="opacity: 0.6;">
				<h3 class="dg-order-modal__timeline-title">Loading...</h3>
				<div style="height: 200px; display: flex; align-items: center; justify-content: center; color: var(--color-muted-slate);">
					<span class="material-symbols-outlined" style="font-size: 48px; animation: dg-spin 1s linear infinite;">progress_activity</span>
				</div>
			</div>
			<div class="dg-order-modal__details" style="opacity: 0.6;">
				<div style="height: 300px; display: flex; align-items: center; justify-content: center; color: var(--color-muted-slate);">
					<p>Fetching order details...</p>
				</div>
			</div>
		`;

		// Trigger open animation immediately
		requestAnimationFrame(function () {
			modal.classList.add('is-open');
			requestAnimationFrame(function () {
				modal.classList.add('is-visible');
			});
		});
	}

	/**
	 * Fetch order data via AJAX
	 * @param {string} orderId - Order ID
	 * @return {Promise<Object>} Order data
	 */
	function fetchOrderData(orderId) {
		if (!window.dgOrderModal || !window.dgOrderModal.ajax_url) {
			return Promise.reject(new Error('AJAX configuration missing'));
		}

		const formData = new FormData();
		formData.append('action', 'dg_get_order_details');
		formData.append('order_id', orderId);
		formData.append('nonce', window.dgOrderModal.nonce);

		return fetch(window.dgOrderModal.ajax_url, {
			method: 'POST',
			body: formData
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Network response was not ok');
				}
				return response.json();
			})
			.then(function (result) {
				if (!result.success || !result.data) {
					throw new Error(result.data && result.data.message ? result.data.message : 'Failed to load order');
				}
				return result.data;
			});
	}

	/**
	 * Render order content in modal
	 * @param {HTMLElement} modal - Modal element
	 * @param {Object} data - Order data from AJAX
	 */
	function renderOrderContent(modal, data) {
		const inner = modal.querySelector('.dg-order-modal__inner');
		
		// Update modal title for accessibility
		modal.setAttribute('aria-labelledby', 'dg-order-modal-title-' + data.order_number);

		inner.innerHTML = `
			<!-- Timeline Sidebar -->
			<div class="dg-order-modal__timeline">
				<h3 class="dg-order-modal__timeline-title">Order Timeline</h3>
				${renderTimeline(data.timeline)}
			</div>

			<!-- Details Area -->
			<div class="dg-order-modal__details">
				<header class="dg-order-modal__header">
					<h2 class="dg-order-modal__order-number" id="dg-order-modal-title-${data.order_number}">
						Order #${escapeHTML(data.order_number)}
					</h2>
					<p class="dg-order-modal__order-date">${escapeHTML(data.date)}</p>
				</header>

				<!-- Products Section -->
				<section class="dg-order-section">
					<h3 class="dg-order-section__title">Products</h3>
					<div class="dg-order-products">
						${data.items.map(renderProductItem).join('')}
					</div>
				</section>

				<!-- Info Grid -->
				<div class="dg-order-info-grid">
					<!-- Shipping Address -->
					<div class="dg-order-info-card">
						<h4 class="dg-order-info-card__title">Shipping Address</h4>
						<div class="dg-order-info-card__content">
							${renderAddress(data.shipping)}
						</div>
					</div>

					<!-- Payment Summary -->
					<div class="dg-order-info-card">
						<h4 class="dg-order-info-card__title">Payment Summary</h4>
						<div class="dg-order-info-card__content">
							${renderPaymentSummary(data.payment)}
						</div>
					</div>
				</div>
			</div>
		`;
	}

	/**
	 * Render timeline items
	 * @param {Array} timeline - Timeline data
	 * @return {string} HTML string
	 */
	function renderTimeline(timeline) {
		if (!timeline || timeline.length === 0) {
			return '<p style="color: var(--color-muted-slate); font-size: 0.9rem;">No timeline available</p>';
		}

		return timeline.map(function (item) {
			const isPending = item.status === 'pending';
			const itemClass = isPending ? 'dg-order-timeline__item dg-order-timeline__item--pending' : 'dg-order-timeline__item';
			
			return `
				<div class="${itemClass}">
					<div class="dg-order-timeline__label">${escapeHTML(item.label)}</div>
					<div class="dg-order-timeline__date">${escapeHTML(item.date)}</div>
				</div>
			`;
		}).join('');
	}

	/**
	 * Render product item
	 * @param {Object} item - Product item data
	 * @return {string} HTML string
	 */
	function renderProductItem(item) {
		return `
			<div class="dg-order-product">
				<div class="dg-order-product__info">
					<p class="dg-order-product__name">${escapeHTML(item.name)}</p>
					<p class="dg-order-product__meta">Quantity: ${escapeHTML(item.quantity)}</p>
				</div>
				<div class="dg-order-product__price">${escapeHTML(item.price)}</div>
			</div>
		`;
	}

	/**
	 * Render shipping address
	 * @param {Object} shipping - Shipping data
	 * @return {string} HTML string
	 */
	function renderAddress(shipping) {
		if (!shipping || !shipping.address_1) {
			return '<p style="color: var(--color-muted-slate);">No shipping address available</p>';
		}

		return `
			<p><strong>${escapeHTML(shipping.name || '')}</strong></p>
			<p>${escapeHTML(shipping.address_1 || '')}</p>
			${shipping.address_2 ? '<p>' + escapeHTML(shipping.address_2) + '</p>' : ''}
			<p>${escapeHTML(shipping.city || '')}</p>
			${shipping.phone ? '<p>Phone: ' + escapeHTML(shipping.phone) + '</p>' : ''}
		`;
	}

	/**
	 * Render payment summary
	 * @param {Object} payment - Payment data
	 * @return {string} HTML string
	 */
	function renderPaymentSummary(payment) {
		if (!payment) {
			return '<p style="color: var(--color-muted-slate);">Payment information unavailable</p>';
		}

		return `
			<div class="dg-order-summary">
				<div class="dg-order-summary__row">
					<span class="dg-order-summary__label">Subtotal</span>
					<span class="dg-order-summary__value">${escapeHTML(payment.subtotal || '$0.00')}</span>
				</div>
				<div class="dg-order-summary__row">
					<span class="dg-order-summary__label">Shipping</span>
					<span class="dg-order-summary__value">${escapeHTML(payment.shipping || '$0.00')}</span>
				</div>
				${payment.tax ? `
					<div class="dg-order-summary__row">
						<span class="dg-order-summary__label">Tax</span>
						<span class="dg-order-summary__value">${escapeHTML(payment.tax)}</span>
					</div>
				` : ''}
				<div class="dg-order-summary__row dg-order-summary__row--total">
					<span class="dg-order-summary__label">Total</span>
					<span class="dg-order-summary__value">${escapeHTML(payment.total || '$0.00')}</span>
				</div>
				${payment.method ? `
					<div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
						<p style="font-size: 0.85rem; color: var(--color-muted-slate);">Payment Method</p>
						<p style="font-weight: 600; margin-top: 0.25rem;">${escapeHTML(payment.method)}</p>
					</div>
				` : ''}
			</div>
		`;
	}

	/**
	 * Render error state
	 * @param {HTMLElement} modal - Modal element
	 * @param {string} message - Error message
	 */
	function renderErrorState(modal, message) {
		const inner = modal.querySelector('.dg-order-modal__inner');
		inner.innerHTML = `
			<div style="padding: 3rem 2rem; text-align: center; color: var(--color-soft-pearl);">
				<span class="material-symbols-outlined" style="font-size: 64px; color: #f44336; margin-bottom: 1rem;">error</span>
				<h2 style="font-family: 'Playfair Display', serif; font-size: 1.75rem; margin-bottom: 0.5rem;">Unable to load order</h2>
				<p style="color: var(--color-muted-slate); margin-bottom: 1.5rem;">${escapeHTML(message)}</p>
				<button type="button" class="dg-btn dg-btn--primary" data-modal-close>Close</button>
			</div>
		`;

		// Trigger animation
		requestAnimationFrame(function () {
			modal.classList.add('is-visible');
		});
	}

	/**
	 * Animate modal entrance with Motion API
	 * @param {HTMLElement} modal - Modal element
	 */
	async function animateModalIn(modal) {
		if (prefersReduced) {
			modal.classList.add('is-visible');
			setupFocusTrap(modal);
			return;
		}

		// Import Motion API dynamically
		const Motion = await loadMotionAPI();
		if (!Motion) {
			modal.classList.add('is-visible');
			setupFocusTrap(modal);
			return;
		}

		const { animate, stagger } = Motion;

		// Fade in overlay
		const overlay = modal.querySelector('.dg-order-modal__overlay');
		if (overlay) {
			animate(overlay, { opacity: [0, 1] }, { duration: 0.3, easing: 'ease-out' });
		}

		// Scale + fade in content
		const content = modal.querySelector('.dg-order-modal__content');
		if (content) {
			animate(
				content,
				{ opacity: [0, 1], scale: [0.95, 1], y: [20, 0] },
				{ duration: 0.3, easing: [0.16, 1, 0.3, 1] }
			);
		}

		// Stagger timeline items
		const timelineItems = modal.querySelectorAll('.dg-order-timeline__item');
		if (timelineItems.length > 0) {
			animate(
				timelineItems,
				{ opacity: [0, 1], x: [-10, 0] },
				{ duration: 0.4, delay: stagger(0.1, { start: 0.2 }), easing: 'ease-out' }
			);
		}

		// Stagger product items
		const productItems = modal.querySelectorAll('.dg-order-product');
		if (productItems.length > 0) {
			animate(
				productItems,
				{ opacity: [0, 1], y: [10, 0] },
				{ duration: 0.4, delay: stagger(0.08, { start: 0.3 }), easing: 'ease-out' }
			);
		}

		// Mark as visible
		modal.classList.add('is-visible');

		// Setup focus trap after animation
		setTimeout(function () {
			setupFocusTrap(modal);
		}, 300);
	}

	/**
	 * Load Motion API from CDN
	 * @return {Promise<Object|null>} Motion module or null
	 */
	async function loadMotionAPI() {
		if (window.Motion) {
			return window.Motion;
		}

		try {
			const Motion = await import('https://cdn.jsdelivr.net/npm/motion@11/+esm');
			window.Motion = Motion;
			return Motion;
		} catch (error) {
			console.warn('Failed to load Motion API:', error);
			return null;
		}
	}

	/**
	 * Setup modal event listeners
	 * @param {HTMLElement} modal - Modal element
	 */
	function setupModalListeners(modal) {
		// Close button and overlay
		const closeButtons = modal.querySelectorAll('[data-modal-close]');
		closeButtons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				closeModal(modal);
			});
		});

		// ESC key
		const escHandler = function (event) {
			if (event.key === 'Escape') {
				closeModal(modal);
			}
		};
		document.addEventListener('keydown', escHandler);
		modal.dataset.escHandler = 'bound';
	}

	/**
	 * Close modal with animation
	 * @param {HTMLElement} modal - Modal element
	 */
	async function closeModal(modal) {
		if (!modal) return;

		// Disable focus trap
		if (focusTrap) {
			focusTrap = null;
		}

		// Animate out
		if (!prefersReduced) {
			const Motion = window.Motion;
			if (Motion && Motion.animate) {
				const { animate } = Motion;

				const content = modal.querySelector('.dg-order-modal__content');
				if (content) {
					await animate(
						content,
						{ opacity: [1, 0], scale: [1, 0.95], y: [0, 20] },
						{ duration: 0.25, easing: 'ease-in' }
					).finished;
				}
			} else {
				modal.classList.remove('is-visible');
				await new Promise(resolve => setTimeout(resolve, 300));
			}
		}

		// Remove modal
		modal.classList.remove('is-visible');
		modal.classList.remove('is-open');
		
		setTimeout(function () {
			if (modal.parentNode) {
				modal.parentNode.removeChild(modal);
			}
			document.body.classList.remove('dg-order-modal-open');
			
			// Restore focus
			if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
				lastFocusedElement.focus();
			}
			lastFocusedElement = null;
			currentModal = null;
		}, 300);

		// Remove ESC handler
		if (modal.dataset.escHandler === 'bound') {
			document.removeEventListener('keydown', arguments.callee);
		}
	}

	/**
	 * Setup focus trap inside modal
	 * @param {HTMLElement} modal - Modal element
	 */
	function setupFocusTrap(modal) {
		const focusableElements = modal.querySelectorAll(
			'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
		);

		if (focusableElements.length === 0) return;

		const firstFocusable = focusableElements[0];
		const lastFocusable = focusableElements[focusableElements.length - 1];

		// Focus first element
		firstFocusable.focus();

		// Trap TAB key
		const trapHandler = function (event) {
			if (event.key !== 'Tab') return;

			if (event.shiftKey) {
				if (document.activeElement === firstFocusable) {
					event.preventDefault();
					lastFocusable.focus();
				}
			} else {
				if (document.activeElement === lastFocusable) {
					event.preventDefault();
					firstFocusable.focus();
				}
			}
		};

		modal.addEventListener('keydown', trapHandler);
		focusTrap = { modal: modal, handler: trapHandler };
	}

	/**
	 * Escape HTML to prevent XSS
	 * @param {string|number} str - String or number to escape
	 * @return {string} Escaped string
	 */
	function escapeHTML(str) {
		// Convert to string first (handles numbers, null, undefined)
		if (str == null) return '';
		const text = String(str);
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	// Re-initialize after AJAX content updates
	if (window.dgAccount) {
		window.dgAccount.onPanelLoad = window.dgAccount.onPanelLoad || [];
		window.dgAccount.onPanelLoad.push(init);
	}

	// Expose for debugging
	window.dgOrderModal = window.dgOrderModal || {};
	window.dgOrderModal.init = init;
	window.dgOrderModal.openModal = openModal;

})();
