/**
 * Dragon Glow — Accessibility Statement
 * ES Module — Motion (motion.dev) vanilla API. KHONG React.
 *
 * Gồm: scroll spy cho sidebar nav + mobile TOC, reveal animation cho header/section.
 * Tôn trọng prefers-reduced-motion.
 *
 * @package Dragon_Glow
 */

import { animate, inView } from "https://cdn.jsdelivr.net/npm/motion@11/+esm";

( function () {
	'use strict';

	const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	const root = document.querySelector( '.dg-accessibility' );
	if ( ! root ) {
		return;
	}

	const EASE = [ 0.16, 1, 0.3, 1 ];

	initReveal();
	initScrollSpy();

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
		const navLinks = root.querySelectorAll( '.dg-accessibility-nav-link' );
		const mobileSelect = root.querySelector( '.dg-accessibility-toc-select' );

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
						mobileSelect.value = id;
					}
				}
			} );
		}, observerOptions );

		sections.forEach( function ( section ) {
			observer.observe( section );
		} );
	}
} )();