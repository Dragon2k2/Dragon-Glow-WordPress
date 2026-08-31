<?php
/**
 * Dragon Glow — Wishlist: hero
 * Luminous Ethereal hero with greeting, member-tier hint, and stat tiles.
 *
 * Expects the following variables in scope (set by the parent template):
 *   - array $dg_wl_items    Wishlist items from dg_wishlist_page_data().
 *   - array $dg_wl_stats    Stats from dg_wishlist_page_stats().
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$items = isset( $dg_wl_items ) ? (array) $dg_wl_items : array();
$stats = isset( $dg_wl_stats ) ? (array) $dg_wl_stats : dg_wishlist_page_stats( $items );
$user  = wp_get_current_user();
$first = trim( (string) $user->first_name );
$greet = '' !== $first ? $first : $user->display_name;
?>
<section class="dg-wishlist-hero" data-sr>
	<div class="dg-wishlist-hero__inner">

		<div class="dg-wishlist-hero__copy">
			<p class="dg-wishlist-hero__eyebrow">
				<span class="material-symbols-outlined dg-wishlist-hero__heart" aria-hidden="true">favorite</span>
				<?php esc_html_e( 'Your saved ritual', 'dragon-glow' ); ?>
			</p>
			<h1 class="dg-wishlist-hero__title">
				<?php
				if ( '' !== $greet ) {
					printf(
						/* translators: %s: customer first name. */
						esc_html__( '%s’s wishlist', 'dragon-glow' ),
						esc_html( $greet )
					);
				} else {
					esc_html_e( 'My wishlist', 'dragon-glow' );
				}
				?>
			</h1>
			<p class="dg-wishlist-hero__sub">
				<?php esc_html_e( 'Curated pieces you love, kept in one luminous place. Bring them home whenever you are ready.', 'dragon-glow' ); ?>
			</p>

			<div class="dg-wishlist-hero__actions">
				<button type="button" class="dg-wishlist-btn dg-wishlist-btn--primary" data-dg-wl-action="share">
					<span class="material-symbols-outlined" aria-hidden="true">share</span>
					<?php esc_html_e( 'Share wishlist', 'dragon-glow' ); ?>
				</button>
				<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>"
				   class="dg-wishlist-btn dg-wishlist-btn--ghost">
					<span class="material-symbols-outlined" aria-hidden="true">storefront</span>
					<?php esc_html_e( 'Continue shopping', 'dragon-glow' ); ?>
				</a>
			</div>
		</div>

		<dl class="dg-wishlist-hero__stats" data-sr-group>
			<div class="dg-wishlist-hero__stat" data-sr>
				<dt class="dg-wishlist-hero__stat-label"><?php esc_html_e( 'Saved items', 'dragon-glow' ); ?></dt>
				<dd class="dg-wishlist-hero__stat-value">
					<span class="dg-count-to" data-count-to="<?php echo esc_attr( (string) $stats['total'] ); ?>" data-dg-wl-stat="total">0</span>
				</dd>
			</div>

			<div class="dg-wishlist-hero__stat" data-sr>
				<dt class="dg-wishlist-hero__stat-label"><?php esc_html_e( 'In stock', 'dragon-glow' ); ?></dt>
				<dd class="dg-wishlist-hero__stat-value">
					<span data-dg-wl-stat="in_stock"><?php echo esc_html( (string) $stats['in_stock'] ); ?></span>
					<span class="dg-wishlist-hero__stat-suffix">
						<?php
						printf(
							/* translators: %s: total item count. */
							esc_html__( 'of %s', 'dragon-glow' ),
							esc_html( (string) $stats['total'] )
						);
						?>
					</span>
				</dd>
			</div>

			<div class="dg-wishlist-hero__stat" data-sr>
				<dt class="dg-wishlist-hero__stat-label"><?php esc_html_e( 'On sale', 'dragon-glow' ); ?></dt>
				<dd class="dg-wishlist-hero__stat-value">
					<span data-dg-wl-stat="on_sale"><?php echo esc_html( (string) $stats['on_sale'] ); ?></span>
				</dd>
			</div>

			<div class="dg-wishlist-hero__stat" data-sr>
				<dt class="dg-wishlist-hero__stat-label"><?php esc_html_e( 'You save', 'dragon-glow' ); ?></dt>
				<dd class="dg-wishlist-hero__stat-value">
					<span data-dg-wl-stat="saved_amount">
					<?php
					$save_amount = (float) $stats['saved_amount'];
					if ( $save_amount > 0 && function_exists( 'dg_format_price' ) ) {
						// dg_format_price() returns WC price HTML (with currency spans).
						// wp_kses_post() preserves that markup; esc_html() would stringify
						// it (e.g. "<span class="woocommerce-Price-amount…$0.00</span>")
						// and stretch the stat card to the right edge of the grid row.
						echo wp_kses_post( dg_format_price( $save_amount ) );
					} else {
						// No savings yet — render zero in the SAME WC HTML shape so
						// the card height stays consistent across renders. wp_kses_post
						// (not esc_html) is critical here.
						echo wp_kses_post( function_exists( 'dg_format_price' ) ? dg_format_price( 0 ) : '$0.00' );
					}
					?>
					</span>
				</dd>
			</div>
		</dl>

	</div>

	<div class="dg-wishlist-hero__decor" aria-hidden="true"></div>
</section>
