/**
 * Dragon Glow — FAQ
 * ES Module — Motion (motion.dev) vanilla API. KHÔNG React.
 *
 * Gồm: scroll-progress, reveal + stagger, accordion (animate height),
 * live search (lọc + đánh số lại qua CSS counter), magnetic CTA.
 * Tôn trọng prefers-reduced-motion.
 *
 * @package Dragon_Glow
 */

import { animate, inView, scroll, stagger } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";

( function () {
	'use strict';

	const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	const root = document.querySelector( '.dg-faq' );
	if ( ! root ) {
		return;
	}

	const EASE = [ 0.16, 1, 0.3, 1 ];

	initProgress();
	initReveal();
	initAccordion();
	initSearch();
	initMagnetic();

	/* ── Scroll-linked progress ─────────────────────────────────────────────── */
	function initProgress() {
		const fill = root.querySelector( '.dg-faq-progress-fill' );
		if ( ! fill ) {
			return;
		}
		if ( reduce ) {
			fill.style.transform = 'scaleX(1)';
			return;
		}
		scroll(
			animate( fill, { scaleX: [ 0, 1 ] }, { ease: 'linear' } ),
			{ target: document.documentElement }
		);
	}

	/* ── Reveal: stagger nhóm con + reveal khối đơn ─────────────────────────── */
	function initReveal() {
		// Khối có data-sr-group: stagger các con data-sr (vd hero).
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

		// Khối data-sr đứng riêng (search, từng group, cta).
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

	/* ── Accordion ──────────────────────────────────────────────────────────── */
	function initAccordion() {
		root.querySelectorAll( '.dg-faq-trigger' ).forEach( function ( trigger ) {
			const panel = document.getElementById( trigger.getAttribute( 'aria-controls' ) );
			if ( ! panel ) {
				return;
			}
			trigger.addEventListener( 'click', function () {
				const isOpen = trigger.getAttribute( 'aria-expanded' ) === 'true';
				if ( isOpen ) {
					closePanel( trigger, panel );
				} else {
					openPanel( trigger, panel );
				}
			} );
		} );
	}

	function openPanel( trigger, panel ) {
		trigger.setAttribute( 'aria-expanded', 'true' );
		panel.hidden = false;
		if ( reduce ) {
			return;
		}
		const target = panel.scrollHeight;
		animate( panel, { height: [ '0px', target + 'px' ], opacity: [ 0, 1 ] }, { duration: 0.4, ease: EASE } )
			.then( function () { panel.style.height = 'auto'; } );
	}

	function closePanel( trigger, panel ) {
		trigger.setAttribute( 'aria-expanded', 'false' );
		if ( reduce ) {
			panel.hidden = true;
			return;
		}
		const current = panel.scrollHeight;
		// Exit nhanh hơn enter cho cảm giác phản hồi.
		animate( panel, { height: [ current + 'px', '0px' ], opacity: [ 1, 0 ] }, { duration: 0.28, ease: EASE } )
			.then( function () {
				panel.hidden = true;
				panel.style.height = '';
				panel.style.opacity = '';
			} );
	}

	/* ── Live search ────────────────────────────────────────────────────────── */
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
				// Khi đang search, buộc hiện nhóm khớp (kể cả nhóm chưa kịp reveal).
				if ( hasQuery && any ) {
					g.style.opacity = '1';
					g.style.transform = 'none';
				}
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
		clear.addEventListener( 'click', function () {
			input.value = '';
			input.focus();
			run();
		} );
	}

	function normalise( str ) {
		return str.toLowerCase().replace( /[‘’]/g, "'" );
	}

	/* ── Magnetic CTA (chỉ desktop, không reduced-motion) ───────────────────── */
	function initMagnetic() {
		if ( reduce || ! window.matchMedia( '(hover: hover)' ).matches ) {
			return;
		}
		root.querySelectorAll( '[data-magnetic]' ).forEach( function ( el ) {
			el.addEventListener( 'pointermove', function ( e ) {
				const r = el.getBoundingClientRect();
				const dx = ( e.clientX - ( r.left + r.width / 2 ) ) / ( r.width / 2 );
				const dy = ( e.clientY - ( r.top + r.height / 2 ) ) / ( r.height / 2 );
				animate( el, { x: dx * 6, y: dy * 6 }, { duration: 0.3, ease: 'easeOut' } );
			} );
			el.addEventListener( 'pointerleave', function () {
				animate( el, { x: 0, y: 0 }, { type: 'spring', stiffness: 200, damping: 15 } );
			} );
		} );
	}
} )();
