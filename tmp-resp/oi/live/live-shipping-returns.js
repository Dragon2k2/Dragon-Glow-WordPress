/**
 * Dragon Glow — Shipping & Returns Motion Animations
 * ES Module — Motion vanilla API (motion.dev).
 * Scope: chỉ trang [data-page="shipping-returns"].
 *
 * @package Dragon_Glow
 */
import { animate, inView } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";

(function () {
	'use strict';

	/* ── Guard: respect prefers-reduced-motion ─────────────── */
	const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ── Scope: chỉ chạy trên trang Shipping & Returns ────── */
	const root = document.querySelector('[data-page="shipping-returns"]');
	if (!root) return;

	/* ── Scroll reveal: stagger group children ─────────────── */
	function initGroupReveal() {
		const groups = root.querySelectorAll('[data-sr-group]');
		groups.forEach(function (group) {
			const children = Array.from(group.querySelectorAll(':scope > [data-sr]'));
			if (!children.length) return;

			inView(group, function () {
				if (prefersReduced) {
					children.forEach(function (c) { c.style.opacity = '1'; });
					return;
				}
				animate(
					children,
					{ opacity: [0, 1], y: [24, 0] },
					{
						duration: 0.7,
						ease: [0.2, 0, 0, 1],
						delay: children.length > 1 ? { each: 0.08 } : 0,
					}
				);
			}, { amount: 0.15 });
		});
	}

	/* ── Orphan [data-sr] nodes (outside any group) ────────── */
	function initOrphanReveal() {
		const all     = root.querySelectorAll('[data-sr]');
		const grouped = new Set(root.querySelectorAll('[data-sr-group] [data-sr]'));
		const orphans = Array.from(all).filter(function (el) { return !grouped.has(el); });

		orphans.forEach(function (el) {
			inView(el, function () {
				if (prefersReduced) { el.style.opacity = '1'; return; }
				animate(el, { opacity: [0, 1], y: [24, 0] }, {
					duration: 0.65,
					ease: [0.2, 0, 0, 1],
				});
			}, { amount: 0.2 });
		});
	}

	/* ── FAQ accordion (single-open) ───────────────────────── */
	function initAccordion() {
		const items = root.querySelectorAll('.dg-sr-faq-item');
		if (!items.length) return;

		items.forEach(function (item) {
			const trigger = item.querySelector('.dg-sr-faq-trigger');
			if (!trigger) return;

			trigger.addEventListener('click', function () {
				const isOpen = item.classList.contains('is-open');

				// Close all siblings (single-open behavior).
				items.forEach(function (other) {
					if (other !== item) {
						other.classList.remove('is-open');
						const t = other.querySelector('.dg-sr-faq-trigger');
						if (t) t.setAttribute('aria-expanded', 'false');
					}
				});

				// Toggle current.
				if (!isOpen) {
					item.classList.add('is-open');
					trigger.setAttribute('aria-expanded', 'true');
					if (!prefersReduced) {
						const panel = item.querySelector('.dg-sr-faq-panel');
						if (panel) {
							animate(panel, { opacity: [0.6, 1] }, { duration: 0.3, ease: 'easeOut' });
						}
					}
				} else {
					item.classList.remove('is-open');
					trigger.setAttribute('aria-expanded', 'false');
				}
			});

			// Keyboard support: Enter / Space toggle.
			trigger.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					trigger.click();
				}
			});
		});
	}

	/* ── Hero parallax (subtle bg shift) ───────────────────── */
	function initHeroParallax() {
		if (prefersReduced) return;
		const bg = root.querySelector('.dg-sr-hero-bg');
		const hero = root.querySelector('.dg-sr-hero');
		if (!bg || !hero) return;

		let rafId = null;
		const update = function () {
			rafId = null;
			const rect = hero.getBoundingClientRect();
			const vh = window.innerHeight || 1;
			// 0 khi hero ở giữa viewport, ±1 khi ở mép.
			const progress = (rect.top + rect.height / 2 - vh / 2) / vh;
			const shift = Math.max(-1, Math.min(1, -progress)) * 24;
			bg.style.transform = 'translate3d(0, ' + shift.toFixed(2) + 'px, 0) scale(1.04)';
		};
		const onScroll = function () {
			if (rafId === null) rafId = requestAnimationFrame(update);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		update();
	}

	/* ── Return request modal ──────────────────────────────── */
	function initReturnModal() {
		const modal    = document.getElementById( 'dg-return-modal' );
		const form     = document.getElementById( 'dg-return-form' );
		const status   = document.getElementById( 'dg-return-status' );
		const submitBtn = document.getElementById( 'dg-return-submit' );
		const triggers = root.querySelectorAll( '[data-return-trigger]' );
		const closers  = modal ? modal.querySelectorAll( '[data-rm-close]' ) : [];

		const strings = ( window.dgReturn && window.dgReturn.i18n ) || {};
		const t = function ( key, fallback ) {
			return strings[ key ] || fallback;
		};

		let lastFocus = null;

		function open() {
			lastFocus = document.activeElement;

			status.textContent = '';
			status.removeAttribute( 'data-state' );
			if ( form ) { form.reset(); }

			modal.removeAttribute( 'hidden' );
			modal.setAttribute( 'data-open', '' );
			document.body.classList.add( 'dg-rm-open' );

			const dialog  = modal.querySelector( '.dg-rm-dialog' );
			const firstEl = dialog.querySelector(
				'input:not([type="hidden"]), textarea, select, button'
			);
			if ( firstEl ) {
				firstEl.focus();
			} else {
				dialog.setAttribute( 'tabindex', '-1' );
				dialog.focus();
			}

			if ( ! prefersReduced ) {
				animate(
					modal.querySelector( '.dg-rm-overlay' ),
					{ opacity: [ 0, 1 ] },
					{ duration: 0.25, ease: 'easeOut' }
				);
				animate(
					dialog,
					{ opacity: [ 0, 1 ], y: [ 16, 0 ] },
					{ duration: 0.35, ease: EASE }
				);
			}
		}

		function close() {
			if ( ! modal.hasAttribute( 'data-open' ) ) { return; }
			modal.removeAttribute( 'data-open' );
			document.body.classList.remove( 'dg-rm-open' );

			if ( ! prefersReduced ) {
				Promise.all( [
					animate(
						modal.querySelector( '.dg-rm-overlay' ),
						{ opacity: [ 1, 0 ] },
						{ duration: 0.2, ease: 'easeIn' }
					).finished,
					animate(
						modal.querySelector( '.dg-rm-dialog' ),
						{ opacity: [ 1, 0 ], y: [ 0, 12 ] },
						{ duration: 0.25, ease: 'easeIn' }
					).finished,
				] ).then( function () {
					modal.setAttribute( 'hidden', '' );
				} );
			} else {
				modal.setAttribute( 'hidden', '' );
			}

			if ( lastFocus && typeof lastFocus.focus === 'function' ) {
				lastFocus.focus();
			}
		}

		function onKeydown( event ) {
			if ( event.key === 'Escape' ) {
				event.preventDefault();
				close();
				return;
			}
			if ( event.key === 'Tab' ) {
				trapFocus( event );
			}
		}

		function trapFocus( event ) {
			const dialog     = modal.querySelector( '.dg-rm-dialog' );
			const focusables = dialog.querySelectorAll(
				'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), ' +
				'textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
			);
			if ( ! focusables.length ) { return; }
			const first = focusables[ 0 ];
			const last  = focusables[ focusables.length - 1 ];
			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		}

		function setStatus( msg, state ) {
			status.textContent = msg || '';
			if ( state ) {
				status.setAttribute( 'data-state', state );
			} else {
				status.removeAttribute( 'data-state' );
			}
		}

		function setLoading( loading ) {
			if ( loading ) {
				submitBtn.classList.add( 'is-loading' );
				submitBtn.disabled = true;
				submitBtn.textContent = t( 'sending', 'Submitting...' );
			} else {
				submitBtn.classList.remove( 'is-loading' );
				submitBtn.disabled = false;
				submitBtn.textContent = t( 'submit', 'Submit request' );
			}
		}

		// Trigger buttons.
		triggers.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				open();
			} );
		} );

		// Close triggers.
		closers.forEach( function ( el ) {
			el.addEventListener( 'click', function () {
				close();
			} );
		} );

		// Keyboard events.
		modal.addEventListener( 'keydown', onKeydown );

		// Form submit.
		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();

			const emailField = form.querySelector( '#dg-rm-email' );
			const email      = ( emailField && emailField.value.trim() ) || '';
			if ( ! email || ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email ) ) {
				setStatus( t( 'invalid_email', 'Please enter a valid email address.' ), 'error' );
				emailField && emailField.focus();
				return;
			}

			const reasonField = form.querySelector( '#dg-rm-reason' );
			const reason      = ( reasonField && reasonField.value ) || '';
			if ( ! reason ) {
				setStatus( t( 'reason_required', 'Please select a reason for return.' ), 'error' );
				reasonField && reasonField.focus();
				return;
			}

			setLoading( true );
			setStatus( t( 'sending_status', 'Submitting your request...' ) );

			const formData = new FormData( form );
			const ajaxData = ( window.dgAjax ) || {};
			const url      = ajaxData.url || '/wp-admin/admin-ajax.php';
			const nonce    = ajaxData.nonce || '';

			const body = new FormData();
			body.append( 'action', 'dg_submit_return_request' );
			body.append( 'nonce', nonce );
			formData.forEach( function ( value, key ) {
				body.append( key, value );
			} );

			fetch( url, {
				method: 'POST',
				credentials: 'same-origin',
				body: body,
			} )
				.then( function ( res ) { return res.json(); } )
				.then( function ( payload ) {
					if ( payload && payload.success ) {
						const msg = ( payload.data && payload.data.message )
							? payload.data.message
							: t( 'success', 'Your return request has been submitted.' );
						setStatus( msg, 'success' );
						form.reset();
						setTimeout( close, 2600 );
					} else {
						const msg = ( payload && payload.data && payload.data.message )
							? payload.data.message
							: t( 'error', 'Something went wrong. Please try again.' );
						setStatus( msg, 'error' );
					}
				} )
				.catch( function () {
					setStatus( t( 'network', 'Network error. Please check your connection and try again.' ), 'error' );
				} )
				.finally( function () {
					setLoading( false );
				} );
		} );
	}

	/* ── Boot ──────────────────────────────────────────────── */
	initGroupReveal();
	initOrphanReveal();
	initAccordion();
	initHeroParallax();
	initReturnModal();

})();