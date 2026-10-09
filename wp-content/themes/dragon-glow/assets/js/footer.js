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

		// On click, set .is-active for instant visual feedback + allow navigation
		footerLinks.forEach( ( link ) => {
			link.addEventListener( 'click', ( e ) => {
				handleFooterLinkClick( e, footerLinks );
			} );
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
			if ( link.getAttribute( 'aria-current' ) === 'page' ) {
				link.classList.add( 'is-active' );
			} else {
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
		const clickedLink = e.currentTarget;

		// Remove active state from all footer links
		links.forEach( ( link ) => {
			link.classList.remove( 'is-active' );
		} );

		// Add active state to clicked link
		clickedLink.classList.add( 'is-active' );
	}

	// Initialize on DOM ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initFooterActiveState );
	} else {
		initFooterActiveState();
	}
}() );
