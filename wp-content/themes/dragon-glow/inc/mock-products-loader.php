<?php
/**
 * Dragon Glow — Mock Product Loader
 *
 * Single gateway for all mock product data and helper functions.
 * This file is bootstrap-loaded by functions.php (require_once) so the functions
 * are always available — including on AJAX requests (admin-ajax.php) where the
 * call chain originates inside a function body.
 *
 * Responsibilities:
 *   1. dg_get_mock_products_data() — loads the pure data file via `require`,
 *      caches the result in a static variable, and returns the array. Safe to
 *      call from any scope, any number of times.
 *   2. dg_mock_stars()            — renders 5-star rating HTML (Material Symbols).
 *   3. dg_mock_detail_shots()     — enumerates detail images for a product slug.
 *
 * WHY SEPARATE FROM DATA FILE:
 * inc/mock-products-data.php is a PURE DATA file: it only builds a PHP array
 * and does `return $mock_products_data;`. It must NOT be bootstrap-included
 * because it calls `get_template_directory()` at file-scope, which would fail
 * during a CLI cron context before WordPress is fully bootstrapped.
 *
 * This loader file is included via require_once in functions.php (after helpers.php),
 * so its function definitions are guaranteed to exist before any template runs.
 * The data file is only ever required through dg_get_mock_products_data(), which
 * handles the `require` + `return` pattern correctly.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'dg_get_mock_products_data' ) ) :
	/**
	 * Return the mock products data array, loading it via the pure data file.
	 *
	 * The underlying data file (inc/mock-products-data.php) ends with
	 * `return $mock_products_data;`. This function uses `require` (not
	 * `require_once`) and captures that return value. The static cache ensures
	 * the file is parsed exactly once per HTTP request, regardless of how many
	 * times or from which PHP scope (global, inside a function, or AJAX handler)
	 * this function is called.
	 *
	 * @return array<string, array> Keyed by product slug (sanitize_title of name).
	 */
	function dg_get_mock_products_data(): array {
		static $cache = null;

		if ( null !== $cache ) {
			return $cache;
		}

		$file = DG_DIR . '/inc/mock-products-data.php';
		if ( ! file_exists( $file ) ) {
			$cache = array();
			return $cache;
		}

		// `require` (not `require_once`) returns the file's return value.
		// The data file ends with `return $mock_products_data;`.
		$cache = require $file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.NotUsingAbsolutePath

		return is_array( $cache ) ? $cache : array();
	}
endif;

if ( ! function_exists( 'dg_mock_stars' ) ) :
	/**
	 * Render a 5-star rating using Material Symbols icons.
	 *
	 * Three visual states rendered with the "star" glyph only (FILL axis works
	 * correctly for "star" — "star_half" is avoided because that glyph may be
	 * missing from the loaded Material Symbols font):
	 *   - Full star  : 1 glyph "star",  FILL 1 (solid yellow).
	 *   - Half star  : overlay of 2 glyphs — base "star" FILL 0 (outlined) and
	 *                  a fill layer "star" FILL 1 clipped to the left half via
	 *                  CSS clip-path, so the result is left half solid + right
	 *                  half outline in the same #f1ca50 yellow.
	 *   - Empty star : 1 glyph "star",  FILL 0 (outline only).
	 *
	 * Half-star placement: when the fractional part of $rating is >= 0.5,
	 * the next integer position renders as a half star.
	 * Rounds toward the nearest 0.5 step:
	 *   4.7 → 4.5  (fractional part 0.7 >= 0.5, shows a half star)
	 *   3.4 → 3.0  (fractional part 0.4 <  0.5, shows an empty star)
	 *
	 * All stars share color #f1ca50 defined in main.css
	 * (.dg-stars .material-symbols-outlined). No inline color needed.
	 *
	 * @param float  $rating Rating value (0–5).
	 * @param string $size   CSS size value for --dg-star-size (e.g. '20px').
	 * @return string HTML fragment.
	 */
	function dg_mock_stars( float $rating, string $size = '20px' ): string {
		$fractional = $rating - floor( $rating );
		$use_half   = ( $fractional >= 0.5 ) ? 1 : 0;
		$floor      = (int) floor( $rating );

		$html = '<div class="dg-stars" style="display:flex;align-items:center;justify-content:center;gap:3px;margin-bottom:6px;">';
		for ( $s = 1; $s <= 5; $s++ ) {
			if ( $s <= $floor ) {
				// Full star — single glyph, FILL 1.
				$html .= sprintf(
					'<span class="material-symbols-outlined" style="--dg-star-fill:1;font-size:%s;">star</span>',
					esc_attr( $size )
				);
			} elseif ( $s === $floor + 1 && $use_half ) {
				// Half star — overlay: base outline + clipped fill layer.
				// Both spans share --dg-star-size via the parent .dg-star-half.
				// --dg-star-fill is set inline so the shared rule
				// ".dg-stars .material-symbols-outlined" (which reads the var)
				// correctly gives __fill FILL 1 and __base FILL 0.
				$html .= sprintf(
					'<span class="dg-star-half" style="--dg-star-size:%s;font-size:%s;">' .
					'<span class="material-symbols-outlined dg-star-half__fill" style="--dg-star-fill:1">star</span>' .
					'<span class="material-symbols-outlined dg-star-half__base" style="--dg-star-fill:0">star</span>' .
					'</span>',
					esc_attr( $size ),
					esc_attr( $size )
				);
			} else {
				// Empty star — single glyph, FILL 0.
				$html .= sprintf(
					'<span class="material-symbols-outlined" style="--dg-star-fill:0;font-size:%s;">star</span>',
					esc_attr( $size )
				);
			}
		}
		$html .= '</div>';
		return $html;
	}
endif;

if ( ! function_exists( 'dg_mock_detail_shots' ) ) :
	/**
	 * Return an ordered list of detail image URLs for a given product slug.
	 *
	 * Looks for files named shot1–shot4 in
	 * /assets/images/details/{$slug}/ and returns URLs in priority order
	 * jpg > webp > jpeg > png (first matching extension wins per shot).
	 *
	 * @param string $slug Product slug (must not be empty).
	 * @return array<string> List of image URLs (max 4).
	 */
	function dg_mock_detail_shots( string $slug = '' ): array {
		if ( empty( $slug ) ) {
			return array();
		}
		$detail_dir  = get_template_directory() . '/assets/images/details/' . $slug . '/';
		$detail_url  = get_template_directory_uri() . '/assets/images/details/' . $slug . '/';
		$detail_exts = array( 'jpg', 'webp', 'jpeg', 'png' );
		$shots       = array();

		for ( $n = 1; $n <= 4; $n++ ) {
			foreach ( $detail_exts as $ext ) {
				$file = $detail_dir . 'shot' . $n . '.' . $ext;
				if ( file_exists( $file ) ) {
					$shots[] = $detail_url . 'shot' . $n . '.' . $ext;
					break;
				}
			}
		}

		return $shots;
	}
endif;
