/**
 * Dragon Glow — FAQ
 * ES Module — Motion (motion.dev) vanilla API. KHÔNG React.
 *
 * Gồm: reveal + stagger, accordion single-open (animate height),
 * category filter (sidebar), live search (lọc + ẩn group rỗng), magnetic CTA.
 * Tôn trọng prefers-reduced-motion.
 *
 * @package Dragon_Glow
 */

import { animate, inView, stagger } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";

( function () {
	'use strict';

	const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	const root = document.querySelector( '.dg-faq' );
	if ( ! root ) {
		return;
	}

	const EASE = [ 0.16, 1, 0.3, 1 ];

	initReveal();
	initAccordion();
	initCategories();
	initSearch();

	/* ── Reveal: stagger các phần tử data-sr ──────────────────────────────── */
	function initReveal() {
		root.querySelectorAll( '[data-sr-group]' ).forEach( function ( group ) {
			const kids = Array.from( group.querySelectorAll( ':scope > [data-sr]' ) );
			if ( ! kids.length ) {
				return;
			}
			if ( reduce ) {
				kids.forEach( function ( k ) { k.style.opacity = 1; } );
				return;
			}
			inView( group, function () {
				animate( kids, { opacity: [ 0, 1 ], y: [ 22, 0 ] }, { duration: 0.6, ease: EASE, delay: stagger( 0.06 ) } );
			}, { amount: 0.2 } );
		} );

		root.querySelectorAll( '[data-sr]:not([data-sr-group] [data-sr])' ).forEach( function ( el ) {
			if ( reduce ) {
				el.style.opacity = 1;
				return;
			}
			inView( el, function () {
				animate( el, { opacity: [ 0, 1 ], y: [ 22, 0 ] }, { duration: 0.6, ease: EASE } );
			}, { amount: 0.15 } );
		} );
	}

	/* ── Accordion: chỉ một card open tại một thời điểm ────────────────────── */
	function initAccordion() {
		root.querySelectorAll( '.dg-faq-trigger' ).forEach( function ( trigger ) {
			const card = trigger.closest( '.dg-faq-card' );
			const panel = document.getElementById( trigger.getAttribute( 'aria-controls' ) );
			if ( ! card || ! panel ) {
				return;
			}
			trigger.addEventListener( 'click', function () {
				const isOpen = trigger.getAttribute( 'aria-expanded' ) === 'true';
				if ( isOpen ) {
					closeCard( trigger, card, panel );
				} else {
					// Đóng tất cả card khác (single-open).
					closeAll();
					openCard( trigger, card, panel );
				}
			} );
		} );
	}

	function closeAll() {
		root.querySelectorAll( '.dg-faq-card.is-open' ).forEach( function ( card ) {
			const trig = card.querySelector( '.dg-faq-trigger' );
			const panel = card.querySelector( '.dg-faq-panel' );
			if ( trig && panel ) {
				closeCard( trig, card, panel );
			}
		} );
	}

	function openCard( trigger, card, panel ) {
		trigger.setAttribute( 'aria-expanded', 'true' );
		card.classList.add( 'is-open' );
		panel.hidden = false;
		if ( reduce ) {
			return;
		}
		const target = panel.scrollHeight;
		animate( panel, { height: [ '0px', target + 'px' ], opacity: [ 0, 1 ] }, { duration: 0.4, ease: EASE } )
			.then( function () { panel.style.height = 'auto'; } );
	}

	function closeCard( trigger, card, panel ) {
		trigger.setAttribute( 'aria-expanded', 'false' );
		card.classList.remove( 'is-open' );
		if ( reduce ) {
			panel.hidden = true;
			return;
		}
		const current = panel.scrollHeight;
		animate( panel, { height: [ current + 'px', '0px' ], opacity: [ 1, 0 ] }, { duration: 0.28, ease: EASE } )
			.then( function () {
				panel.hidden = true;
				panel.style.height = '';
				panel.style.opacity = '';
			} );
	}

	/* ── Category filter (sidebar) ─────────────────────────────────────────── */
	function initCategories() {
		const buttons = Array.from( root.querySelectorAll( '[data-faq-category]' ) );
		if ( ! buttons.length ) {
			return;
		}
		buttons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				const id = btn.getAttribute( 'data-faq-category' );
				// active state
				buttons.forEach( function ( b ) {
					const on = b === btn;
					b.classList.toggle( 'is-active', on );
					b.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
				} );
				// lọc groups: nhóm khớp hiện, còn lại ẩn
				filterByCategory( id );
				// scroll lên đầu accordion nếu user ở xa
				const list = root.querySelector( '#dg-faq-list' );
				if ( list ) {
					const top = list.getBoundingClientRect().top + window.scrollY - 100;
					window.scrollTo( { top: Math.max( 0, top ), behavior: reduce ? 'auto' : 'smooth' } );
				}
			} );
		} );
	}

	function filterByCategory( id ) {
		const groups = Array.from( root.querySelectorAll( '[data-faq-group]' ) );
		groups.forEach( function ( g ) {
			const match = g.getAttribute( 'data-faq-group' ) === id;
			g.hidden = ! match;
			// Bỏ qua trạng thái open ở các card trong group bị ẩn (clean state).
			if ( ! match ) {
				g.querySelectorAll( '.dg-faq-card.is-open' ).forEach( function ( card ) {
					const trig = card.querySelector( '.dg-faq-trigger' );
					const panel = card.querySelector( '.dg-faq-panel' );
					if ( trig && panel ) {
						trig.setAttribute( 'aria-expanded', 'false' );
						card.classList.remove( 'is-open' );
						panel.hidden = true;
						panel.style.height = '';
						panel.style.opacity = '';
					}
				} );
			}
		} );
	}

	/* ── Live search: lọc item + ẩn group rỗng + empty-state ───────────────── */
	function initSearch() {
		const input = document.getElementById( 'dg-faq-search' );
		const clear = document.getElementById( 'dg-faq-search-clear' );
		const status = document.getElementById( 'dg-faq-search-status' );
		const empty = document.getElementById( 'dg-faq-empty' );
		if ( ! input ) {
			return;
		}

		const items = Array.from( root.querySelectorAll( '[data-faq-item]' ) );
		const groups = Array.from( root.querySelectorAll( '[data-faq-group]' ) );
		// Cache text (câu hỏi + câu trả lời) một lần để lọc nhanh.
		const haystacks = items.map( function ( it ) {
			return normalise( it.textContent || '' );
		} );

		let timer;

		function run() {
			const q = normalise( input.value.trim() );
			const hasQuery = q.length > 0;
			clear.hidden = ! hasQuery;

			let visible = 0;
			items.forEach( function ( it, i ) {
				const match = ! hasQuery || haystacks[ i ].indexOf( q ) !== -1;
				it.hidden = ! match;
				if ( match ) {
					visible++;
				}
			} );

			groups.forEach( function ( g ) {
				const any = g.querySelector( '[data-faq-item]:not([hidden])' ) !== null;
				g.hidden = ! any;
			} );

			if ( empty ) {
				empty.hidden = visible > 0;
			}
			if ( status ) {
				status.textContent = hasQuery ? visible + ( visible === 1 ? ' result' : ' results' ) : '';
			}
		}

		input.addEventListener( 'input', function () {
			window.clearTimeout( timer );
			timer = window.setTimeout( run, 120 );
		} );
		if ( clear ) {
			clear.addEventListener( 'click', function () {
				input.value = '';
				input.focus();
				run();
			} );
		}
	}

	function normalise( str ) {
		return str.toLowerCase().replace( /[‘’]/g, "'" );
	}
} )();