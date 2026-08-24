<?php
/**
 * Dragon Glow — Helpers: Utilities
 * Generic string + customizer helpers.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Truncate text with ellipsis.
 *
 * @param string $text   Text to truncate.
 * @param int    $length Maximum length.
 * @return string
 */
function dg_truncate( string $text, int $length = 100 ): string {
    if ( strlen( $text ) <= $length ) {
        return $text;
    }

    return substr( $text, 0, $length ) . '&hellip;';
}

/**
 * Get theme customizer settings.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Default value.
 * @return mixed
 */
function dg_get_mod( string $key, $default = null ) {
    return get_theme_mod( 'dg_' . $key, $default );
}

/**
 * Ensure a WordPress page exists with the given slug + title + template.
 *
 * Idempotent: if a published page with the given slug (or the given template)
 * already exists, its `_wp_page_template` meta is refreshed and the existing ID
 * is returned. Otherwise a new page is inserted and returned.
 *
 * @param string $slug     Page slug.
 * @param string $title    Page title (used when creating).
 * @param string $template Page template file (relative to theme root, e.g.
 *                         `template-our-story.php`). Pass '' for default.
 * @return int Page ID (0 on failure).
 */
function dg_ensure_page( string $slug, string $title, string $template = '' ): int {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		if ( '' !== $template ) {
			update_post_meta( $existing->ID, '_wp_page_template', $template );
		}
		return (int) $existing->ID;
	}

	$postarr = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => '',
	);
	if ( '' !== $template ) {
		$postarr['meta_input'] = array( '_wp_page_template' => $template );
	}

	$id = wp_insert_post( $postarr, true );
	if ( is_wp_error( $id ) ) {
		error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			sprintf( '[Dragon Glow] dg_ensure_page("%s") failed: %s', $slug, $id->get_error_message() )
		);
		return 0;
	}
	return (int) $id;
}