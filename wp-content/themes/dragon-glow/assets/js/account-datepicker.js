/**
 * Dragon Glow — Account "Date of birth" Scroll Wheel Date Picker
 *
 * Replaces the native <input type="date"> (inconsistent browser-default
 * calendar UI) with an elegant 3-column iOS-style scroll wheel
 * (Day / Month / Year). Writes the selected date into the hidden
 * #dg_date_of_birth input as YYYY-MM-DD so the existing WooCommerce save
 * handler (dg_save_account_custom_fields) keeps working unchanged.
 *
 * ES module — enqueued via wp_enqueue_script_module() only on the
 * /my-account/edit-account/ endpoint (see inc/enqueue/scripts.php).
 *
 * @package Dragon_Glow
 */

import { animate } from 'https://cdn.jsdelivr.net/npm/motion@11/+esm';

( function () {
	'use strict';

	const prefersReduced = matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	const ITEM_HEIGHT = 36;

	init();

	/**
	 * Boot every `[data-dob-wheel]` instance on the page.
	 *
	 * @return {void}
	 */
	function init() {
		document.querySelectorAll( '[data-dob-wheel]' ).forEach( initWheel );
	}

	/**
	 * Wire up one scroll-wheel date picker instance.
	 *
	 * @param {HTMLElement} root `.dg-dob-wheel` container with data-dob-* attrs.
	 * @return {void}
	 */
	function initWheel( root ) {
		const hiddenInput = document.getElementById( 'dg_date_of_birth' );
		if ( ! hiddenInput ) return;

		const dayCol   = root.querySelector( '[data-dob-col="day"]' );
		const monthCol = root.querySelector( '[data-dob-col="month"]' );
		const yearCol  = root.querySelector( '[data-dob-col="year"]' );
		if ( ! dayCol || ! monthCol || ! yearCol ) return;

		const months = getMonthLabels();
		const maxYear = parseInt( root.dataset.dobMaxYear, 10 ) || new Date().getFullYear();
		const minYear = maxYear - 110;
		const years = range( maxYear, minYear, -1 );

		const initialDay   = parseInt( root.dataset.dobDay, 10 ) || 0;
		const initialMonth = parseInt( root.dataset.dobMonth, 10 ) || 0;
		const initialYear  = parseInt( root.dataset.dobYear, 10 ) || 0;

		fillColumn( dayCol, range( 1, 31 ).map( pad2 ), initialDay > 0 ? initialDay - 1 : 14 );
		fillColumn( monthCol, months, initialMonth > 0 ? initialMonth - 1 : 0 );
		fillColumn( yearCol, years.map( String ), initialYear > 0 ? years.indexOf( initialYear ) : Math.floor( years.length / 2 ) );

		[ dayCol, monthCol, yearCol ].forEach( bindColumnInteractions );

		// Only write the hidden input if the user ever had a saved DOB —
		// avoids silently populating a date the user never chose.
		if ( initialDay > 0 && initialMonth > 0 && initialYear > 0 ) {
			syncHiddenInput();
		}

		/**
		 * Populate a wheel column with option labels, centered on
		 * `activeIndex` with no animation (initial render only).
		 *
		 * @param {HTMLElement} col         Scroll column element.
		 * @param {string[]}    labels      Display labels, in order.
		 * @param {number}      activeIndex Index to center on load.
		 * @return {void}
		 */
		function fillColumn( col, labels, activeIndex ) {
			col.innerHTML = '';
			labels.forEach( ( label ) => {
				const item = document.createElement( 'div' );
				item.className = 'dg-dob-wheel__item';
				item.textContent = label;
				item.setAttribute( 'role', 'option' );
				col.appendChild( item );
			} );
			requestAnimationFrame( () => {
				col.scrollTop = activeIndex * ITEM_HEIGHT;
				updateActiveItem( col );
			} );
		}

		/**
		 * Attach scroll + keyboard + click handlers to a wheel column.
		 *
		 * @param {HTMLElement} col Scroll column element.
		 * @return {void}
		 */
		function bindColumnInteractions( col ) {
			let scrollTimeout = null;
			let isDragging = false;
			let dragMoved = false;
			let dragStartY = 0;
			let dragStartScrollTop = 0;

			function scheduleSnap() {
				clearTimeout( scrollTimeout );
				scrollTimeout = setTimeout( () => {
					snapToNearest( col );
				}, 120 );
			}

			col.addEventListener( 'scroll', () => {
				updateActiveItem( col );
				if ( ! isDragging ) scheduleSnap();
			}, { passive: true } );

			col.addEventListener( 'keydown', ( e ) => {
				if ( e.key === 'ArrowDown' ) {
					e.preventDefault();
					col.scrollTop += ITEM_HEIGHT;
				} else if ( e.key === 'ArrowUp' ) {
					e.preventDefault();
					col.scrollTop -= ITEM_HEIGHT;
				} else {
					return;
				}
				scheduleSnap();
			} );

			// Click a visible (non-centered) item to jump straight to it.
			// Suppressed right after a drag so a drag-release doesn't also
			// fire a click on whatever item ends up under the pointer.
			col.addEventListener( 'click', ( e ) => {
				if ( dragMoved ) return;
				const item = e.target.closest( '.dg-dob-wheel__item' );
				if ( ! item ) return;
				scrollToIndex( col, Array.from( col.children ).indexOf( item ) );
			} );

			// ----- Pointer drag-to-scroll (mouse + touch + pen, unified) -----
			col.addEventListener( 'pointerdown', ( e ) => {
				isDragging = true;
				dragMoved = false;
				dragStartY = e.clientY;
				dragStartScrollTop = col.scrollTop;
				col.setPointerCapture( e.pointerId );
				col.classList.add( 'is-dragging' );
				clearTimeout( scrollTimeout );
			} );

			col.addEventListener( 'pointermove', ( e ) => {
				if ( ! isDragging ) return;
				const deltaY = e.clientY - dragStartY;
				if ( Math.abs( deltaY ) > 3 ) dragMoved = true;
				col.scrollTop = dragStartScrollTop - deltaY;
			} );

			function endDrag( e ) {
				if ( ! isDragging ) return;
				isDragging = false;
				col.classList.remove( 'is-dragging' );
				if ( col.hasPointerCapture( e.pointerId ) ) {
					col.releasePointerCapture( e.pointerId );
				}
				scheduleSnap();
				// Clear the click-suppression flag after the click event
				// (fired synchronously after pointerup) has had a chance to run.
				setTimeout( () => { dragMoved = false; }, 0 );
			}

			col.addEventListener( 'pointerup', endDrag );
			col.addEventListener( 'pointercancel', endDrag );
		}

		/**
		 * Highlight the item currently centered in the column's viewport.
		 *
		 * @param {HTMLElement} col Scroll column element.
		 * @return {void}
		 */
		function updateActiveItem( col ) {
			const centerIndex = Math.round( col.scrollTop / ITEM_HEIGHT );
			Array.from( col.children ).forEach( ( item, i ) => {
				item.classList.toggle( 'is-center', i === centerIndex );
			} );
		}

		/**
		 * Snap scroll position to the nearest item (Motion-eased) once the
		 * user stops scrolling/pressing arrow keys, then sync the hidden input.
		 *
		 * @param {HTMLElement} col Scroll column element.
		 * @return {void}
		 */
		function snapToNearest( col ) {
			const centerIndex = Math.round( col.scrollTop / ITEM_HEIGHT );
			const targetTop = centerIndex * ITEM_HEIGHT;
			if ( prefersReduced ) {
				col.scrollTop = targetTop;
				updateActiveItem( col );
				syncHiddenInput();
				return;
			}
			animate( col.scrollTop, targetTop, {
				duration: 0.25,
				easing: [ 0.4, 0, 0.2, 1 ],
				onUpdate: ( value ) => { col.scrollTop = value; },
			} ).finished.then( () => {
				updateActiveItem( col );
				syncHiddenInput();
			} );
		}

		/**
		 * Programmatically scroll a column so `index` becomes centered
		 * (used for click-to-select on a visible, non-centered item).
		 *
		 * @param {HTMLElement} col   Scroll column element.
		 * @param {number}      index Target item index.
		 * @return {void}
		 */
		function scrollToIndex( col, index ) {
			const targetTop = index * ITEM_HEIGHT;
			if ( prefersReduced ) {
				col.scrollTop = targetTop;
				updateActiveItem( col );
				syncHiddenInput();
				return;
			}
			animate( col.scrollTop, targetTop, {
				duration: 0.3,
				easing: [ 0.4, 0, 0.2, 1 ],
				onUpdate: ( value ) => { col.scrollTop = value; },
			} ).finished.then( () => {
				updateActiveItem( col );
				syncHiddenInput();
			} );
		}

		/**
		 * Read the centered value of each column and write YYYY-MM-DD into
		 * the hidden form input that WooCommerce's save handler reads.
		 *
		 * @return {void}
		 */
		function syncHiddenInput() {
			const day = getCenteredIndex( dayCol ) + 1;
			const month = getCenteredIndex( monthCol ) + 1;
			const year = years[ getCenteredIndex( yearCol ) ];
			if ( ! day || ! month || ! year ) return;

			// Clamp day to the number of days in the selected month/year
			// (e.g. Feb 30 -> Feb 28/29) so the result is always a valid date.
			const maxDay = new Date( year, month, 0 ).getDate();
			const safeDay = Math.min( day, maxDay );

			hiddenInput.value = `${ year }-${ pad2( month ) }-${ pad2( safeDay ) }`;
		}

		/**
		 * @param {HTMLElement} col Scroll column element.
		 * @return {number} Index of the item currently centered.
		 */
		function getCenteredIndex( col ) {
			return Math.round( col.scrollTop / ITEM_HEIGHT );
		}
	}

	/**
	 * @param {number} start Start value (inclusive).
	 * @param {number} end   End value (inclusive).
	 * @param {number} [step] Step increment, defaults to 1.
	 * @return {number[]} Range of integers from start to end.
	 */
	function range( start, end, step = 1 ) {
		const out = [];
		if ( step > 0 ) {
			for ( let i = start; i <= end; i += step ) out.push( i );
		} else {
			for ( let i = start; i >= end; i += step ) out.push( i );
		}
		return out;
	}

	/**
	 * @param {number} n Number to pad.
	 * @return {string} Zero-padded 2-digit string.
	 */
	function pad2( n ) {
		return String( n ).padStart( 2, '0' );
	}

	/**
	 * Resolve localized month labels via `dgDobI18n` (wp_localize_script).
	 * Falls back to English abbreviations if not localized.
	 *
	 * @return {string[]} 12 month labels, January first.
	 */
	function getMonthLabels() {
		if ( typeof window.dgDobI18n !== 'undefined' && Array.isArray( window.dgDobI18n.months ) && window.dgDobI18n.months.length === 12 ) {
			return window.dgDobI18n.months;
		}
		return [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ];
	}
}() );

