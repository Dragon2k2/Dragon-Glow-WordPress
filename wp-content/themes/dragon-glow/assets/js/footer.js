/**
 * Dragon Glow — Footer Link Active State Handler
 *
 * Manages active/inactive states for footer navigation links.
 * - On page load: sync .is-active class from server-side aria-current="page"
 * - On click: remove .is-active from all links instantly (visual feedback during loading)
 * - Preserves accessibility attribute (aria-current="page") for screen readers
 * - After page load: restore .is-active from aria-current="page" (in case JS applied stale class)
 *
 * @package Dragon_Glow
 */

( function() {
	'use strict';

	/**
	 * Initialize footer link active state handler.
	 */
	function initFooterActiveState() {
		// Get all footer links
		const footerLinks = document.querySelectorAll( '.dg-footer-link' );

		if ( ! footerLinks.length ) {
			return; // No footer links found
		}

		// On page load, sync active state based on aria-current="page"
		// (set server-side by PHP is_page() checks)
		syncActiveStateFromServerAttribute( footerLinks );

		// On click, remove .is-active instantly from all links before navigation
		// This ensures old active state disappears during page loading
		footerLinks.forEach( ( link ) => {
			link.addEventListener( 'click', ( e ) => {
				handleFooterLinkClick( e, footerLinks );
			} );
		} );

		// After new page loads, restore .is-active from aria-current="page"
		// This handles the case where click happened but aria-current is now on a new link
		window.addEventListener( 'load', () => {
			syncActiveStateFromServerAttribute( footerLinks );
		} );
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
		links.forEach( ( link ) => {
			if ( 'page' === link.getAttribute( 'aria-current' ) ) {
				link.classList.add( 'is-active' );
			} else {
				link.classList.remove( 'is-active' );
			}
		} );
	}

	/**
	 * Handle footer link click.
	 *
	 * Remove .is-active from all links, then add it to the clicked link
	 * for immediate visual feedback during page loading.
	 * Server-side aria-current will be restored after page load.
	 *
	 * @param {Event} e - Click event.
	 * @param {NodeList} links - Footer links.
	 */
	function handleFooterLinkClick( e, links ) {
		const clickedLink = e.currentTarget;

		// Remove active state from all footer links
		links.forEach( ( link ) => {
			link.classList.remove( 'is-active' );
		} );

		// Add active state to clicked link for instant visual feedback
		clickedLink.classList.add( 'is-active' );
	}

	// Initialize on DOM ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initFooterActiveState );
	} else {
		initFooterActiveState();
	}
}() );
