/**
 * Dragon Glow — Cookie Policy
 * ES Module — Motion (motion.dev) vanilla API. KHONG React.
 *
 * Gồm: scroll spy cho sidebar nav, reveal animation.
 * Tôn trọng prefers-reduced-motion.
 *
 * @package Dragon_Glow
 */

import { animate, inView } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";

( function () {
	'use strict';

	const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	const root = document.querySelector( '.dg-cookie' );
	if ( ! root ) {
		return;
	}

	const EASE = [ 0.16, 1, 0.3, 1 ];

	initReveal();
	initScrollSpy();
	initTocListbox();

	/* ── Reveal: header + sections ─────────────────────────────── */
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

		targets.forEach( function ( el ) {
			inView(
				el,
				function () {
					animate( el, { opacity: [ 0, 1 ], y: [ 22, 0 ] }, { duration: 0.6, ease: EASE } );
				},
				{ amount: 0.2 }
			);
		} );
	}

	/* ── Scroll Spy: highlight active nav link ─────────────────── */
	function initScrollSpy() {
		const sections = root.querySelectorAll( 'section[id]' );
		const navLinks = root.querySelectorAll( '.dg-cookie-nav-link' );
		const mobileSelect = root.querySelector( '.dg-cookie-toc-select' );

		if ( ! sections.length || ! navLinks.length ) {
			return;
		}

		const observerOptions = {
			root: null,
			rootMargin: '-15% 0px -65% 0px',
			threshold: 0
		};

		const observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					const id = entry.target.getAttribute( 'id' );

					// Update desktop nav
					navLinks.forEach( function ( link ) {
						link.classList.remove( 'is-active' );
						if ( link.getAttribute( 'href' ) === '#' + id ) {
							link.classList.add( 'is-active' );
						}
					} );

					// Update mobile select
					if ( mobileSelect ) {
						if ( 'value' in mobileSelect ) {
							mobileSelect.value = id;
						}
					}

					// Update mobile listbox label + selected option
					syncTocListbox( id );
				}
			} );
		}, observerOptions );

		sections.forEach( function ( section ) {
			observer.observe( section );
		} );
	}

	/* ── Mobile TOC listbox (<1024px): toggle + keyboard + sync ── */
	function initTocListbox() {
		const toc = root.querySelector( '[data-dg-toc]' );
		if ( ! toc ) {
			return;
		}
		const btn = toc.querySelector( '#dg-cookie-toc-btn' );
		const list = toc.querySelector( '#dg-cookie-toc-list' );
		if ( ! btn || ! list ) {
			return;
		}
		const label = toc.querySelector( '#dg-cookie-toc-label' );
		const items = Array.prototype.slice.call( list.querySelectorAll( '.dg-cookie-toc-item' ) );
		if ( ! items.length ) {
			return;
		}

		btn.addEventListener( 'click', function () {
			if ( isTocOpen() ) {
				closeToc( true );
			} else {
				openToc();
			}
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( isTocOpen() && ! toc.contains( event.target ) ) {
				closeToc( true );
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( ! isTocOpen() ) {
				return;
			}
			if ( event.key === 'Escape' ) {
				event.preventDefault();
				closeToc( true );
			}
		} );

		btn.addEventListener( 'keydown', function ( event ) {
			if ( event.key !== 'ArrowDown' && event.key !== 'ArrowUp' ) {
				return;
			}
			event.preventDefault();
			if ( ! isTocOpen() ) {
				openToc();
			}
			focusItem( event.key === 'ArrowDown' ? 0 : items.length - 1 );
		} );

		list.addEventListener( 'keydown', function ( event ) {
			const current = items.indexOf( document.activeElement.closest( '.dg-cookie-toc-item' ) );
			if ( event.key === 'ArrowDown' ) {
				event.preventDefault();
				focusItem( current < 0 ? 0 : ( current + 1 ) % items.length );
			} else if ( event.key === 'ArrowUp' ) {
				event.preventDefault();
				focusItem( current < 0 ? items.length - 1 : ( current - 1 + items.length ) % items.length );
			} else if ( event.key === 'Home' ) {
				event.preventDefault();
				focusItem( 0 );
			} else if ( event.key === 'End' ) {
				event.preventDefault();
				focusItem( items.length - 1 );
			}
		} );

		items.forEach( function ( item ) {
			const link = item.querySelector( '.dg-cookie-toc-link' );
			if ( ! link ) {
				return;
			}
			link.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				selectTocItem( item, true );
			} );
		} );

		function isTocOpen() {
			return btn.getAttribute( 'aria-expanded' ) === 'true';
		}

		function openToc() {
			btn.setAttribute( 'aria-expanded', 'true' );
			list.removeAttribute( 'hidden' );
			const panel = toc.querySelector( '.dg-cookie-toc-panel' );
			if ( panel ) {
				panel.removeAttribute( 'hidden' );
			}
		}

		function closeToc( focusBtn ) {
			btn.setAttribute( 'aria-expanded', 'false' );
			list.setAttribute( 'hidden', '' );
			const panel = toc.querySelector( '.dg-cookie-toc-panel' );
			if ( panel ) {
				panel.setAttribute( 'hidden', '' );
			}
			btn.removeAttribute( 'aria-activedescendant' );
			if ( focusBtn ) {
				btn.focus();
			}
		}

		function focusItem( index ) {
			const item = items[ index ];
			if ( ! item ) {
				return;
			}
			btn.setAttribute( 'aria-activedescendant', item.id );
			const link = item.querySelector( '.dg-cookie-toc-link' );
			if ( link ) {
				link.focus();
			}
		}

		function selectTocItem( item, closeAfter ) {
			const link = item.querySelector( '.dg-cookie-toc-link' );
			if ( ! link ) {
				return;
			}
			items.forEach( function ( other ) {
				other.setAttribute( 'aria-selected', other === item ? 'true' : 'false' );
			} );
			if ( label ) {
				label.textContent = link.textContent.trim();
			}
			if ( closeAfter ) {
				closeToc( true );
			}
			const target = document.getElementById( link.getAttribute( 'href' ).slice( 1 ) );
			if ( target ) {
				target.scrollIntoView( { behavior: reduce ? 'auto' : 'smooth', block: 'start' } );
			}
			window.history.replaceState( null, '', link.getAttribute( 'href' ) );
		}

		// Scroll-spy sync: chỉ cập nhật nhãn + option (không scroll, không history).
		window.dgSyncCookieToc = function ( id ) {
			const item = items.find( function ( el ) {
				const link = el.querySelector( '.dg-cookie-toc-link' );
				return link && link.getAttribute( 'href' ) === '#' + id;
			} );
			if ( ! item ) {
				return;
			}
			items.forEach( function ( other ) {
				other.setAttribute( 'aria-selected', other === item ? 'true' : 'false' );
			} );
			const link = item.querySelector( '.dg-cookie-toc-link' );
			if ( link && label && ! isTocOpen() ) {
				label.textContent = link.textContent.trim();
			}
		};
	}

	/* Scroll-spy gọi vào listbox nếu có (null-safe khi markup cũ). */
	function syncTocListbox( id ) {
		if ( typeof window.dgSyncCookieToc === 'function' ) {
			window.dgSyncCookieToc( id );
		}
	}
} )();