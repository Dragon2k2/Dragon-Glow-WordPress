/**
 * Dragon Glow — Careers
 * ES Module — Motion (motion.dev) vanilla API. KHONG React.
 *
 * Gồm: scroll reveal cho mỗi section (Motion inView),
 *       xử lý ảnh lỗi (swap sang gradient fallback),
 *       smooth-scroll cho anchor nội trang (vd CTA hero → #open-roles).
 * Tôn trọng prefers-reduced-motion.
 *
 * @package Dragon_Glow
 */

import { animate, inView } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";

( function () {
	'use strict';

	const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	const root = document.querySelector( '.dg-careers' );
	if ( ! root ) {
		return;
	}

	const EASE = [ 0.16, 1, 0.3, 1 ];

	initReveal();
	initImageFallback();
	initSmoothScroll();
	initApplyModal();

	/* ── Reveal: từng section fade + slide up khi vào viewport ── */
	function initReveal() {
		const targets = root.querySelectorAll( '[data-sr]' );
		if ( ! targets.length ) {
			return;
		}

		if ( reduce ) {
			targets.forEach( function ( el ) {
				el.style.opacity = 1;
			} );
			return;
		}

		targets.forEach( function ( el, i ) {
			const delay = i === 0 ? 0 : Math.min( i * 0.05, 0.3 );
			inView(
				el,
				function () {
					animate(
						el,
						{ opacity: [ 0, 1 ], y: [ 24, 0 ] },
						{ duration: 0.7, ease: EASE, delay: delay }
					);
				},
				{ amount: 0.15 }
			);
		} );
	}

	/* ── Image fallback: nếu URL Stitch die, đánh class is-broken ── */
	function initImageFallback() {
		const imgs = root.querySelectorAll( 'img' );
		imgs.forEach( function ( img ) {
			img.addEventListener( 'error', function () {
				img.classList.add( 'is-broken' );
			} );
			// Nếu ảnh đã complete và có naturalWidth = 0 → cũng broken
			if ( img.complete && img.naturalWidth === 0 ) {
				img.classList.add( 'is-broken' );
			}
		} );
	}

	/* ── Smooth-scroll cho anchor nội trang (vd CTA hero → #open-roles) ──
	   - Chặn default để dùng window.scrollTo có tính offset navbar.
	   - Tôn trọng prefers-reduced-motion: nhảy tức thì nếu user yêu cầu giảm motion.
	   - Sau khi cuộn → chuyển focus tới target (preventScroll: true) để
	     keyboard/screen-reader tiếp tục đọc từ section đó.
	   - Chấp nhận cả data-scroll-target="#id" để tái sử dụng (fallback
	     anchor vẫn dùng href trên <a>); href="#" (rỗng) thì bỏ qua. */
	function initSmoothScroll() {
		const links = root.querySelectorAll( 'a[href^="#"]' );
		if ( ! links.length ) {
			return;
		}

		const nav = document.querySelector( '.glass-nav' );
		const navHeight = -64;

		links.forEach( function ( link ) {
			link.addEventListener( 'click', function ( event ) {
				const href = link.getAttribute( 'href' );
				if ( ! href || href === '#' ) {
					return;
				}

				const target = document.querySelector( href );
				if ( ! target ) {
					return;
				}

				event.preventDefault();

				const targetTop = target.getBoundingClientRect().top + window.scrollY - navHeight;
				window.scrollTo( {
					top: targetTop,
					behavior: reduce ? 'auto' : 'smooth',
				} );

				if ( ! target.hasAttribute( 'tabindex' ) ) {
					target.setAttribute( 'tabindex', '-1' );
				}
				target.focus( { preventScroll: true } );
			} );
		} );
	}

	/* ── Apply modal: open/close, focus trap, ESC, submit AJAX, Motion ── */
	function initApplyModal() {
		const modal = document.getElementById( 'dg-apply-modal' );
		if ( ! modal ) {
			return;
		}

		const dialog  = modal.querySelector( '.dg-apply-dialog' );
		const form    = modal.querySelector( '#dg-apply-form' );
		const status  = modal.querySelector( '#dg-apply-status' );
		const submit  = modal.querySelector( '#dg-apply-submit' );
		const roleInput  = modal.querySelector( '#dg-apply-role' );
		const roleLabel  = modal.querySelector( '#dg-apply-role-label' );
		const desiredRoleInput = modal.querySelector( '#dg-apply-desired-role' );
		const triggers   = root.querySelectorAll( '[data-apply-trigger]' );
		const closers    = modal.querySelectorAll( '[data-apply-close]' );

		const strings = ( window.dgCareersApply && window.dgCareersApply.i18n ) || {};
		const t = function ( key, fallback ) {
			return strings[ key ] || fallback;
		};

		let lastFocus = null;

		function open( roleName, options ) {
			lastFocus = document.activeElement;
			const allowCustomRole = !! ( options && options.allowCustomRole );

			if ( allowCustomRole ) {
				modal.classList.add( 'is-general-inquiry' );
			} else {
				modal.classList.remove( 'is-general-inquiry' );
			}

			if ( roleInput ) {
				roleInput.value = roleName || '';
			}
			if ( roleLabel ) {
				roleLabel.textContent = roleName || '';
			}
			if ( desiredRoleInput ) {
				desiredRoleInput.value = '';
			}

			status.textContent = '';
			status.removeAttribute( 'data-state' );

			modal.removeAttribute( 'hidden' );
			modal.setAttribute( 'data-open', '' );

			document.body.classList.add( 'dg-apply-open' );

			const firstFocusable = dialog.querySelector(
				'input:not([type="hidden"]):not([name="desired_role"]), textarea, select, button'
			);
			if ( firstFocusable ) {
				firstFocusable.focus();
			} else {
				dialog.setAttribute( 'tabindex', '-1' );
				dialog.focus();
			}

			if ( ! reduce ) {
				animate(
					modal.querySelector( '.dg-apply-overlay' ),
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
			if ( modal.getAttribute( 'hidden' ) !== null && ! modal.hasAttribute( 'data-open' ) ) {
				return;
			}
			modal.removeAttribute( 'data-open' );
			modal.classList.remove( 'is-general-inquiry' );
			document.body.classList.remove( 'dg-apply-open' );
			if ( desiredRoleInput ) {
				desiredRoleInput.value = '';
			}

			if ( ! reduce ) {
				Promise.all( [
					animate(
						modal.querySelector( '.dg-apply-overlay' ),
						{ opacity: [ 1, 0 ] },
						{ duration: 0.2, ease: 'easeIn' }
					).finished,
					animate(
						dialog,
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
			const focusables = dialog.querySelectorAll(
				'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
			);
			if ( ! focusables.length ) {
				return;
			}
			const first = focusables[0];
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
				submit.classList.add( 'is-loading' );
				submit.disabled = true;
				submit.textContent = t( 'sending', 'Sending...' );
			} else {
				submit.classList.remove( 'is-loading' );
				submit.disabled = false;
				submit.textContent = t( 'send', 'Send application' );
			}
		}

		// Trigger buttons (Apply per role row).
		triggers.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				const role = btn.getAttribute( 'data-role' ) || '';
				open( role, {
					allowCustomRole: btn.hasAttribute( 'data-allow-custom-role' ),
				} );
			} );
		} );

		// Closer buttons + overlay click.
		closers.forEach( function ( el ) {
			el.addEventListener( 'click', function () {
				close();
			} );
		} );

		// ESC + focus trap only when modal is open.
		modal.addEventListener( 'keydown', onKeydown );

		// AJAX submit.
		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			const desiredRole = desiredRoleInput ? desiredRoleInput.value.trim() : '';
			if ( desiredRole ) {
				roleInput.value = desiredRole;
				if ( roleLabel ) {
					roleLabel.textContent = desiredRole;
				}
			}

			if ( ! roleInput.value ) {
				setStatus( t( 'role_required', 'Please choose a role before submitting.' ), 'error' );
				return;
			}

			const consent = form.querySelector( '#dg-apply-consent' );
			if ( consent && ! consent.checked ) {
				setStatus( t( 'consent_required', 'Please confirm the privacy policy before submitting.' ), 'error' );
				return;
			}

			setLoading( true );
			setStatus( t( 'sending_status', 'Submitting your application...' ) );

			const data = new FormData( form );

			const ajaxConfig = ( window.dgAjax ) || {};
			const url = ajaxConfig.url || '/wp-admin/admin-ajax.php';
			const nonce = ajaxConfig.nonce || '';

			fetch( url, {
				method: 'POST',
				credentials: 'same-origin',
				body: ( function () {
					const fd = new FormData();
					fd.append( 'action', 'dg_submit_application' );
					fd.append( 'nonce', nonce );
					for ( const pair of data.entries() ) {
						fd.append( pair[0], pair[1] );
					}
					return fd;
				} )(),
			} )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( payload ) {
					if ( payload && payload.success ) {
						setStatus( payload.data && payload.data.message
							? payload.data.message
							: t( 'success', 'Your application has been sent.' ), 'success' );
						form.reset();
						setTimeout( function () {
							close();
						}, 2400 );
					} else {
						const msg = payload && payload.data && payload.data.message
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
} )();