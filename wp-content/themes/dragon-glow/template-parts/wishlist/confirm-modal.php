<?php
/**
 * Dragon Glow — Wishlist: confirm modal
 * Glassmorphism panel (Classic Glass — variant #01) for confirming
 * destructive actions: bulk-remove (selected items) and clear-all
 * (every item). JS calls window.DGWishlist.askConfirm({ type, count })
 * to open the correct instance.
 *
 * Each instance carries a data-dg-wl-confirm attribute (`bulk` or
 * `clear`) so the JS layer can target the right panel. The count
 * placeholder is a <span> with data-dg-wl-confirm-count that JS
 * updates with the live number; the surrounding strings stay
 * translatable as full sentences.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// ── Instance 1: bulk-remove (selected items) ──────────────────────────────
?>
<div class="dg-wishlist-confirm"
     data-dg-wl-confirm="bulk"
     hidden
     aria-hidden="true"
     role="dialog"
     aria-modal="true"
     aria-labelledby="dg-wl-confirm-bulk-title">
	<div class="dg-wishlist-confirm__overlay" data-dg-wl-confirm-close></div>
	<div class="dg-wishlist-confirm__panel" role="document">

		<div class="dg-wishlist-confirm__head">
			<div class="dg-wishlist-confirm__icon" aria-hidden="true">
				<span class="material-symbols-outlined">delete_sweep</span>
			</div>
			<div class="dg-wishlist-confirm__text">
				<h2 class="dg-wishlist-confirm__title" id="dg-wl-confirm-bulk-title">
					<?php esc_html_e( 'Remove from wishlist?', 'dragon-glow' ); ?>
				</h2>
				<p class="dg-wishlist-confirm__message">
					<?php esc_html_e( 'You are about to remove', 'dragon-glow' ); ?>
					&nbsp;<span class="dg-wishlist-confirm__count" data-dg-wl-confirm-count>0</span>&nbsp;
					<?php esc_html_e( 'item(s) from your wishlist. This action cannot be undone.', 'dragon-glow' ); ?>
				</p>
			</div>
		</div>

		<div class="dg-wishlist-confirm__actions">
			<button type="button"
			        class="dg-wishlist-btn dg-wishlist-btn--ghost"
			        data-dg-wl-confirm-close>
				<span class="material-symbols-outlined" aria-hidden="true">close</span>
				<?php esc_html_e( 'Cancel', 'dragon-glow' ); ?>
			</button>
			<button type="button"
			        class="dg-wishlist-btn dg-wishlist-btn--danger"
			        data-dg-wl-confirm-yes>
				<span class="material-symbols-outlined" aria-hidden="true">delete</span>
				<span data-dg-wl-confirm-confirm-label>
					<?php esc_html_e( 'Remove items', 'dragon-glow' ); ?>
				</span>
			</button>
		</div>
	</div>
</div>

<?php
// ── Instance 2: clear-all (every item) ──────────────────────────────────
?>
<div class="dg-wishlist-confirm"
     data-dg-wl-confirm="clear"
     hidden
     aria-hidden="true"
     role="dialog"
     aria-modal="true"
     aria-labelledby="dg-wl-confirm-clear-title">
	<div class="dg-wishlist-confirm__overlay" data-dg-wl-confirm-close></div>
	<div class="dg-wishlist-confirm__panel" role="document">

		<div class="dg-wishlist-confirm__head">
			<div class="dg-wishlist-confirm__icon dg-wishlist-confirm__icon--warning" aria-hidden="true">
				<span class="material-symbols-outlined">warning</span>
			</div>
			<div class="dg-wishlist-confirm__text">
				<h2 class="dg-wishlist-confirm__title" id="dg-wl-confirm-clear-title">
					<?php esc_html_e( 'Clear your wishlist?', 'dragon-glow' ); ?>
				</h2>
				<p class="dg-wishlist-confirm__message">
					<?php esc_html_e( 'Every item in your wishlist will be permanently removed. This action cannot be undone.', 'dragon-glow' ); ?>
				</p>
			</div>
		</div>

		<div class="dg-wishlist-confirm__actions">
			<button type="button"
			        class="dg-wishlist-btn dg-wishlist-btn--ghost"
			        data-dg-wl-confirm-close>
				<span class="material-symbols-outlined" aria-hidden="true">close</span>
				<?php esc_html_e( 'Cancel', 'dragon-glow' ); ?>
			</button>
			<button type="button"
			        class="dg-wishlist-btn dg-wishlist-btn--danger"
			        data-dg-wl-confirm-yes>
				<span class="material-symbols-outlined" aria-hidden="true">layers_clear</span>
				<?php esc_html_e( 'Clear wishlist', 'dragon-glow' ); ?>
			</button>
		</div>
	</div>
</div>