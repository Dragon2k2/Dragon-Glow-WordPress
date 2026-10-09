<?php
/**
 * Dragon Glow — SEO: Meta Description
 * Prints a per-URL <meta name="description"> in wp_head.
 *
 * Source priority per request:
 *  1. Singular / Shop page / Posts page: manual excerpt, then trimmed content.
 *     (Products: Short description. Pages: Excerpt box, enabled below.)
 *  2. Taxonomy archives: term description.
 *  3. Post type archives: post type description.
 *  4. Fallback: site-wide default (dg_default_meta_description()).
 * Search results and 404 pages print no description.
 *
 * Output is skipped when a dedicated SEO plugin is active, so tags never
 * duplicate. Use the `dg_meta_description` filter to override the final value.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Let editors write a per-page meta description in the Excerpt box.
 * Posts and products (Short description) already support excerpts.
 *
 * @return void
 */
function dg_seo_add_page_excerpt_support(): void {
	add_post_type_support( 'page', 'excerpt' );
}
add_action( 'init', 'dg_seo_add_page_excerpt_support' );

/**
 * Site-wide fallback meta description.
 *
 * @return string
 */
function dg_default_meta_description(): string {
	return sprintf(
		/* translators: %s: site name. */
		__( 'Shop %s premium botanical skincare. Thoughtfully formulated serums, creams and rituals to reveal your natural radiance.', 'dragon-glow' ),
		get_bloginfo( 'name' )
	);
}

/**
 * Normalise raw text into a meta description.
 *
 * Strips tags and shortcodes, decodes entities, collapses whitespace and
 * trims to ~160 characters on a word boundary (typical SERP snippet length).
 *
 * @param string $text Raw excerpt, content or term description.
 * @return string
 */
function dg_prepare_meta_description( string $text ): string {
	$max  = 160;
	$text = wp_strip_all_tags( strip_shortcodes( $text ), true );
	$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
	$text = trim( (string) preg_replace( '/\s+/u', ' ', $text ) );

	if ( mb_strlen( $text ) <= $max ) {
		return $text;
	}

	// Reserve one character for the ellipsis, then cut at the last space.
	$text = mb_substr( $text, 0, $max - 1 );
	$pos  = mb_strrpos( $text, ' ' );
	if ( false !== $pos && $pos > 100 ) {
		$text = mb_substr( $text, 0, $pos );
	}

	return rtrim( $text, " \t\n\r,;:.-" ) . "\u{2026}";
}

/**
 * Get the raw summary of a post: excerpt first, then content.
 * Returns '' for missing or password-protected posts so nothing leaks.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function dg_get_post_summary( int $post_id ): string {
	$post = $post_id > 0 ? get_post( $post_id ) : null;

	if ( ! $post || post_password_required( $post ) ) {
		return '';
	}

	return '' !== trim( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
}

/**
 * Build the meta description for the current request.
 *
 * @return string Empty string when no tag should be printed.
 */
function dg_get_meta_description(): string {
	if ( is_search() || is_404() ) {
		return '';
	}

	$raw = '';

	if ( is_singular() ) {
		$raw = dg_get_post_summary( (int) get_queried_object_id() );
	} elseif ( dg_is_woocommerce_active() && is_shop() ) {
		$raw = dg_get_post_summary( (int) wc_get_page_id( 'shop' ) );
	} elseif ( is_home() && ! is_front_page() ) {
		$raw = dg_get_post_summary( (int) get_option( 'page_for_posts' ) );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$raw = term_description();
	} elseif ( is_post_type_archive() ) {
		$raw = get_the_post_type_description();
	}

	$description = dg_prepare_meta_description( $raw );

	if ( '' === $description ) {
		$description = dg_default_meta_description();
	}

	/**
	 * Filter the meta description printed in <head>.
	 *
	 * @param string $description Final description.
	 */
	return (string) apply_filters( 'dg_meta_description', $description );
}

/**
 * Print the meta description tag.
 *
 * @return void
 */
function dg_output_meta_description(): void {
	// Yoast SEO, Rank Math, All in One SEO and SEOPress print their own tag.
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) ) {
		return;
	}

	$description = dg_get_meta_description();

	if ( '' === $description ) {
		return;
	}

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
}
add_action( 'wp_head', 'dg_output_meta_description', 1 );
