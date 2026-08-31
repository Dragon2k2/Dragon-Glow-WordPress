<?php
/**
 * Dragon Glow — Wishlist: empty state
 * Friendly onboarding panel shown when the wishlist is empty.
 * Hidden by default; JS toggles `.is-hidden` once items are added.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$shop_url = dg_is_woocommerce_active()
	? get_permalink( wc_get_page_id( 'shop' ) )
	: home_url( '/shop/' );
?>
<section class="dg-wishlist-empty" data-dg-wl-empty hidden>
	<div class="dg-wishlist-empty__inner">

		<div class="dg-wishlist-empty__illustration" aria-hidden="true">
			<div class="dg-wishlist-empty__halo"></div>
			<div class="dg-wishlist-empty__ring dg-wishlist-empty__ring--a"></div>
			<div class="dg-wishlist-empty__ring dg-wishlist-empty__ring--b"></div>
			<div class="dg-wishlist-empty__heart-wrap">
				<span class="material-symbols-outlined dg-wishlist-empty__heart">favorite</span>
			</div>
		</div>

		<h2 class="dg-wishlist-empty__title">
			<?php esc_html_e( 'Your wishlist is waiting', 'dragon-glow' ); ?>
		</h2>

		<p class="dg-wishlist-empty__text">
			<?php esc_html_e( 'Save the rituals you love so they are always a tap away. We will quietly notify you when they go on sale.', 'dragon-glow' ); ?>
		</p>

		<ul class="dg-wishlist-empty__bullets">
			<li>
				<span class="material-symbols-outlined" aria-hidden="true">savings</span>
				<span><?php esc_html_e( 'Get notified when saved items drop in price.', 'dragon-glow' ); ?></span>
			</li>
			<li>
				<span class="material-symbols-outlined" aria-hidden="true">event_available</span>
				<span><?php esc_html_e( 'See your collection across every device.', 'dragon-glow' ); ?></span>
			</li>
			<li>
				<span class="material-symbols-outlined" aria-hidden="true">ios_share</span>
				<span><?php esc_html_e( 'Share a private link with someone who cares.', 'dragon-glow' ); ?></span>
			</li>
		</ul>

		<div class="dg-wishlist-empty__actions">
			<a href="<?php echo esc_url( $shop_url ); ?>" class="dg-wishlist-btn dg-wishlist-btn--primary">
				<span class="material-symbols-outlined" aria-hidden="true">storefront</span>
				<?php esc_html_e( 'Explore the collection', 'dragon-glow' ); ?>
			</a>
			<a href="<?php echo esc_url( home_url( '/our-story/' ) ); ?>" class="dg-wishlist-btn dg-wishlist-btn--ghost">
				<span class="material-symbols-outlined" aria-hidden="true">auto_awesome</span>
				<?php esc_html_e( 'Read our story', 'dragon-glow' ); ?>
			</a>
		</div>
	</div>
</section>
