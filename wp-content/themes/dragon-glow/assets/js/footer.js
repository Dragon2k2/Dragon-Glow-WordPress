/**
 * Dragon Glow — Footer Link Active State Handler
 *
 * Manages active/inactive states for footer navigation links.
 * - Observes current page URL and sets aria-current="page" on matching link.
 * - On click, sets .is-active class for instant visual feedback (optimistic update).
 * - Preserves accessibility attribute (aria-current="page") for screen readers.
 *
 * @package Dragon_Glow
 */

( function() {
	'use strict';

	// Debug flag — enable console logging
	const DEBUG = window.dgDebug && window.dgDebug.enabled;

	/**
	 * Log debug message.
	 *
	 * @param {string} msg - Message to log.
	 * @param {*} data - Optional data to log.
	 */
	function debug( msg, data ) {
		if ( ! DEBUG ) {
			return;
		}
		console.log( '[DG Footer] ' + msg, data || '' );
	}

	/**
	 * Initialize footer link active state handler.
	 */
	function initFooterActiveState() {
		debug( 'Initializing footer link active state handler' );

		// Get all footer links
		const footerLinks = document.querySelectorAll( '.dg-footer-link' );

		debug( 'Found ' + footerLinks.length + ' footer links' );

		if ( ! footerLinks.length ) {
			debug( 'ERROR: No footer links found (.dg-footer-link)' );
			return; // No footer links found
		}

		// On page load, sync active state based on aria-current="page"
		// (set server-side by PHP is_page() checks)
		syncActiveStateFromServerAttribute( footerLinks );

		// On click, set .is-active for instant visual feedback + allow navigation
		footerLinks.forEach( ( link, idx ) => {
			debug( 'Link ' + idx + ': ' + link.href + ' | aria-current=' + link.getAttribute( 'aria-current' ) );

			link.addEventListener( 'click', ( e ) => {
				debug( 'Clicked link: ' + e.currentTarget.href );
				handleFooterLinkClick( e, footerLinks );
			} );
		} );

		debug( 'Footer link event listeners attached' );
	}

	/**
	 * Sync active state from server-side aria-current="page" attribute.
	 *
	 * The PHP template sets aria-current="page" on links to the current page.
	 * Convert this to .is-active class so CSS styles take effect.
	 *
	 * @param {NodeList} links - Footer links to check.
	 */
	function syncActiveStateFromServerAttribute( links ) {
		debug( 'Syncing active state from server attributes' );

		links.forEach( ( link, idx ) => {
			const current = link.getAttribute( 'aria-current' );

			if ( 'page' === current ) {
				debug( 'Link ' + idx + ' has aria-current="page" → adding .is-active' );
				link.classList.add( 'is-active' );

				// Verify class was added
				if ( link.classList.contains( 'is-active' ) ) {
					debug( '✅ Class .is-active added successfully' );
				} else {
					debug( '❌ ERROR: Failed to add .is-active class' );
				}

				// Check computed color
				const computed = window.getComputedStyle( link );
				debug( 'Computed color: ' + computed.color );
			} else {
				debug( 'Link ' + idx + ' does NOT have aria-current="page" → removing .is-active' );
				link.classList.remove( 'is-active' );
			}
		} );
	}

	/**
	 * Handle footer link click.
	 *
	 * Remove .is-active from all links, then add it to the clicked link.
	 * This provides instant visual feedback before page navigation.
	 *
	 * @param {Event} e - Click event.
	 * @param {NodeList} links - Footer links.
	 */
	function handleFooterLinkClick( e, links ) {
		debug( 'handleFooterLinkClick called' );

		const clickedLink = e.currentTarget;

		// Remove active state from all footer links
		links.forEach( ( link ) => {
			link.classList.remove( 'is-active' );
		} );

		debug( 'Removed .is-active from all links' );

		// Add active state to clicked link
		clickedLink.classList.add( 'is-active' );

		// Verify class was added
		if ( clickedLink.classList.contains( 'is-active' ) ) {
			debug( '✅ Class .is-active added to clicked link' );

			// Check computed color
			const computed = window.getComputedStyle( clickedLink );
			debug( 'Computed color after click: ' + computed.color );
		} else {
			debug( '❌ ERROR: Failed to add .is-active to clicked link' );
		}
	}

	// Log when script loads
	debug( 'Footer script loaded (readyState=' + document.readyState + ')' );

	// Initialize on DOM ready
	if ( document.readyState === 'loading' ) {
		debug( 'Document still loading, listening for DOMContentLoaded' );
		document.addEventListener( 'DOMContentLoaded', initFooterActiveState );
	} else {
		debug( 'Document already loaded, initializing immediately' );
		initFooterActiveState();
	}
}() );
