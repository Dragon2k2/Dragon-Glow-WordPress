<?php
/**
 * Dragon Glow — Wishlist: share modal
 * Hidden by default; JS opens it via [data-dg-wl-modal-open] and closes
 * via [data-dg-wl-modal-close] / Escape / overlay click.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$share_url = home_url( '/wishlist/?shared=1' );
?>
<div class="dg-wishlist-modal" data-dg-wl-modal="share" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="dg-wl-share-title">
	<div class="dg-wishlist-modal__overlay" data-dg-wl-modal-close></div>
	<div class="dg-wishlist-modal__panel" role="document">

		<header class="dg-wishlist-modal__head">
			<div class="dg-wishlist-modal__icon" aria-hidden="true">
				<span class="material-symbols-outlined">share</span>
			</div>
			<div>
				<h2 class="dg-wishlist-modal__title" id="dg-wl-share-title">
					<?php esc_html_e( 'Share my wishlist', 'dragon-glow' ); ?>
				</h2>
				<p class="dg-wishlist-modal__sub">
					<?php esc_html_e( 'Send a private link to your saved pieces. We will email a single-use URL that opens this wishlist.', 'dragon-glow' ); ?>
				</p>
			</div>
			<button type="button"
			        class="dg-wishlist-modal__close"
			        data-dg-wl-modal-close
			        aria-label="<?php esc_attr_e( 'Close', 'dragon-glow' ); ?>">
				<span class="material-symbols-outlined" aria-hidden="true">close</span>
			</button>
		</header>

		<form class="dg-wishlist-modal__form" data-dg-wl-share-form>
			<label class="dg-wishlist-modal__field">
				<span class="dg-wishlist-modal__field-label"><?php esc_html_e( "Friend's email", 'dragon-glow' ); ?></span>
				<input type="email"
				       name="email"
				       required
				       autocomplete="email"
				       placeholder="friend@example.com"
				       class="dg-wishlist-modal__input" />
			</label>

			<button type="submit" class="dg-wishlist-btn dg-wishlist-btn--primary dg-wishlist-modal__submit">
				<span class="material-symbols-outlined" aria-hidden="true">send</span>
				<?php esc_html_e( 'Send link', 'dragon-glow' ); ?>
			</button>
		</form>

		<div class="dg-wishlist-modal__divider">
			<span><?php esc_html_e( 'Or share yourself', 'dragon-glow' ); ?></span>
		</div>

		<div class="dg-wishlist-modal__copy">
			<input type="text"
			       readonly
			       value="<?php echo esc_url( $share_url ); ?>"
			       class="dg-wishlist-modal__input"
			       data-dg-wl-share-link
			       aria-label="<?php esc_attr_e( 'Wishlist share link', 'dragon-glow' ); ?>" />
			<button type="button"
			        class="dg-wishlist-btn dg-wishlist-btn--ghost"
			        data-dg-wl-share-copy>
				<span class="material-symbols-outlined" aria-hidden="true">content_copy</span>
				<?php esc_html_e( 'Copy link', 'dragon-glow' ); ?>
			</button>
		</div>

		<div class="dg-wishlist-modal__feedback" data-dg-wl-share-feedback role="status" aria-live="polite"></div>
	</div>
</div>
