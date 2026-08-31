<?php
/**
 * Template Name: Wishlist — Dragon Glow
 * Description: Trang Wishlist cá nhân — Luminous Ethereal layout.
 *   - Hero gradient (eyebrow + greeting + 4 stat tiles + share CTA).
 *   - Sticky toolbar (filter: All / In stock / On sale + sort + select-all).
 *   - Grid sản phẩm dùng product-card-glass (mirror content-product.php) +
 *     heart toggle, select checkbox, quick-add button.
 *   - Bulk actions bar (sticky bottom on mobile, inline on desktop) cho
 *     add-selected-to-bag và remove-selected.
 *   - Empty state với illustration glass + 2 CTA.
 *   - Share modal (email + copy link).
 *
 * Routing: Khi user chưa đăng nhập → redirect về wp_login_url() với
 * redirect_to trỏ về trang này. Khi WC không active → render banner
 * fallback (giống template-shop).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

// FOUC guard — JS strip and reveal when ready.
echo '<script>document.documentElement.classList.add(\'dg-js\');</script>';

// Fallback: WooCommerce chưa bật → banner thân thiện.
if ( ! dg_is_woocommerce_active() ) :
	?>
	<main class="dg-wishlist" id="main-content">
		<div class="dg-wishlist-wrap">
			<div class="dg-wishlist-notice">
				<span class="material-symbols-outlined dg-wishlist-notice__icon" aria-hidden="true">info</span>
				<div>
					<strong><?php esc_html_e( 'WooCommerce is not active.', 'dragon-glow' ); ?></strong>
					<?php esc_html_e( 'Please enable WooCommerce to save products to your wishlist.', 'dragon-glow' ); ?>
				</div>
			</div>
		</div>
	</main>
	<?php
	get_footer();
	return;
endif;

// Fallback: guest → đẩy sang trang login, redirect_to quay lại đây.
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

// Load data + section partials.
require_once locate_template( 'template-parts/wishlist/data-wishlist.php' );

$dg_wl_items = dg_wishlist_page_data();
$dg_wl_stats = dg_wishlist_page_stats( $dg_wl_items );
?>
<main class="dg-wishlist" id="main-content"
      data-dg-wishlist-root
      data-empty="<?php echo empty( $dg_wl_items ) ? '1' : '0'; ?>"
      data-share-url="<?php echo esc_attr( home_url( '/wishlist/?shared=1' ) ); ?>">

	<div class="dg-wishlist-wrap">

		<!-- 1. Breadcrumb -->
		<?php get_template_part( 'template-parts/global/breadcrumb' ); ?>

		<!-- 2. Hero -->
		<?php
		// Scoped variables cho section-hero.
		set_query_var( 'dg_wl_items', $dg_wl_items );
		set_query_var( 'dg_wl_stats', $dg_wl_stats );
		get_template_part( 'template-parts/wishlist/section-hero' );
		?>

		<!-- 3. Toolbar (filter + sort + select-all) -->
		<?php get_template_part( 'template-parts/wishlist/section-toolbar' ); ?>

		<!-- 4. Empty state (hidden when items exist) -->
		<?php get_template_part( 'template-parts/wishlist/empty-state' ); ?>

		<!-- 5. Product grid -->
		<section class="dg-wishlist-grid-shell" data-dg-wl-grid-shell <?php echo empty( $dg_wl_items ) ? 'hidden' : ''; ?>>

			<div class="dg-wishlist-grid" data-dg-wl-grid>
				<?php foreach ( $dg_wl_items as $item ) : ?>
					<?php
					$product_id  = (int) $item['id'];
					$has_hover   = ! empty( $item['image_hover'] ) && $item['image_hover'] !== $item['image'];
					$is_simple   = ( 'simple' === $item['type'] );
					$button_text = $is_simple ? __( 'Add to bag', 'dragon-glow' ) : __( 'View options', 'dragon-glow' );
					?>
					<article class="dg-wishlist-card"
					         data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
					         data-dg-wl-card
					         data-in-stock="<?php echo $item['in_stock'] ? '1' : '0'; ?>"
					         data-on-sale="<?php echo $item['on_sale'] ? '1' : '0'; ?>"
					         data-price="<?php echo esc_attr( (string) ( $item['sale_price'] > 0 ? $item['sale_price'] : $item['regular_price'] ) ); ?>"
					         data-name="<?php echo esc_attr( $item['name'] ); ?>"
					         data-saved-at="<?php echo esc_attr( (string) time() ); ?>"
					         data-sr>

						<!-- Selection checkbox -->
						<label class="dg-wishlist-card__select" aria-label="<?php esc_attr_e( 'Select item', 'dragon-glow' ); ?>">
							<input type="checkbox"
							       class="dg-wishlist-card__checkbox"
							       data-dg-wl-select
							       value="<?php echo esc_attr( (string) $product_id ); ?>" />
							<span class="dg-wishlist-card__checkbox-mark" aria-hidden="true">
								<span class="material-symbols-outlined">check</span>
							</span>
						</label>

						<!-- Heart toggle (remove from wishlist) -->
						<button type="button"
						        class="dg-wishlist-card__heart is-active dg-wishlist-toggle"
						        data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
						        data-dg-wl-remove
						        aria-label="<?php esc_attr_e( 'Remove from wishlist', 'dragon-glow' ); ?>">
							<span class="material-symbols-outlined">favorite</span>
						</button>

						<!-- Image -->
						<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="dg-wishlist-card__media">
							<img src="<?php echo esc_url( $item['image'] ); ?>"
							     alt="<?php echo esc_attr( $item['name'] ); ?>"
							     class="dg-wishlist-card__img dg-wishlist-card__img--primary"
							     loading="lazy" />
							<?php if ( $has_hover ) : ?>
								<img src="<?php echo esc_url( $item['image_hover'] ); ?>"
								     alt="<?php echo esc_attr( $item['name'] . ' — texture' ); ?>"
								     class="dg-wishlist-card__img dg-wishlist-card__img--hover"
								     loading="lazy" />
							<?php endif; ?>

							<?php if ( ! empty( $item['badge'] ) ) : ?>
								<span class="dg-wishlist-card__badge"><?php echo esc_html( $item['badge'] ); ?></span>
							<?php endif; ?>

							<?php if ( $item['on_sale'] && $item['discount_pct'] > 0 ) : ?>
								<span class="dg-wishlist-card__sale">
									<?php
									printf(
										/* translators: %d: sale percentage. */
										esc_html__( '−%d%%', 'dragon-glow' ),
										(int) $item['discount_pct']
									);
									?>
								</span>
							<?php endif; ?>

							<?php if ( ! $item['in_stock'] ) : ?>
								<span class="dg-wishlist-card__oos">
									<span class="material-symbols-outlined" aria-hidden="true">inventory_2</span>
									<?php esc_html_e( 'Out of stock', 'dragon-glow' ); ?>
								</span>
							<?php endif; ?>
						</a>

						<!-- Body -->
						<div class="dg-wishlist-card__body">

							<?php if ( ! empty( $item['category_name'] ) && ! empty( $item['category_url'] ) ) : ?>
								<a href="<?php echo esc_url( $item['category_url'] ); ?>"
								   class="dg-wishlist-card__category">
									<?php echo esc_html( $item['category_name'] ); ?>
								</a>
							<?php endif; ?>

							<h3 class="dg-wishlist-card__title">
								<a href="<?php echo esc_url( $item['permalink'] ); ?>">
									<?php echo esc_html( $item['name'] ); ?>
								</a>
							</h3>

							<div class="dg-wishlist-card__meta">
								<span class="dg-wishlist-card__price">
									<?php echo wp_kses_post( $item['price_html'] ); ?>
								</span>
								<?php if ( $item['review_count'] > 0 ) : ?>
									<span class="dg-wishlist-card__rating" aria-label="<?php echo esc_attr( sprintf( __( 'Rated %s out of 5', 'dragon-glow' ), number_format_i18n( $item['rating'], 1 ) ) ); ?>">
										<span class="material-symbols-outlined" aria-hidden="true">star</span>
										<?php echo esc_html( number_format_i18n( $item['rating'], 1 ) ); ?>
										<span class="dg-wishlist-card__rating-count">
											<?php
											printf(
												/* translators: %d: review count. */
												esc_html__( '(%d)', 'dragon-glow' ),
												(int) $item['review_count']
											);
											?>
										</span>
									</span>
								<?php endif; ?>
							</div>

							<?php if ( $item['in_stock'] && $item['purchasable'] && $is_simple ) : ?>
								<button type="button"
								        class="dg-wishlist-card__cta wc-add-to-cart-btn"
								        data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
								        data-product-slug="<?php echo esc_attr( $item['slug'] ); ?>"
								        data-product-type="<?php echo esc_attr( $item['type'] ); ?>">
									<span class="material-symbols-outlined" aria-hidden="true">shopping_bag</span>
									<?php echo esc_html( $button_text ); ?>
								</button>
							<?php elseif ( ! $item['in_stock'] ) : ?>
								<button type="button" class="dg-wishlist-card__cta dg-wishlist-card__cta--disabled" disabled>
									<span class="material-symbols-outlined" aria-hidden="true">inventory_2</span>
									<?php esc_html_e( 'Out of stock', 'dragon-glow' ); ?>
								</button>
							<?php else : ?>
								<a href="<?php echo esc_url( $item['permalink'] ); ?>"
								   class="dg-wishlist-card__cta dg-wishlist-card__cta--ghost">
									<span class="material-symbols-outlined" aria-hidden="true">tune</span>
									<?php echo esc_html( $button_text ); ?>
								</a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>

			<!-- No-results state (shown by JS when filter has zero matches) -->
			<div class="dg-wishlist-noresult" data-dg-wl-noresult hidden>
				<span class="material-symbols-outlined" aria-hidden="true">filter_alt_off</span>
				<p><?php esc_html_e( 'No items match the current filter.', 'dragon-glow' ); ?></p>
				<button type="button" class="dg-wishlist-btn dg-wishlist-btn--ghost" data-dg-wl-reset-filter>
					<span class="material-symbols-outlined" aria-hidden="true">restart_alt</span>
					<?php esc_html_e( 'Clear filter', 'dragon-glow' ); ?>
				</button>
			</div>
		</section>

		<!-- 6. Bulk actions bar (sticky on mobile, inline on desktop) -->
		<div class="dg-wishlist-bulkbar" data-dg-wl-bulkbar aria-live="polite" aria-atomic="true">
			<div class="dg-wishlist-bulkbar__inner">
				<p class="dg-wishlist-bulkbar__count">
					<span data-dg-wl-bulk-count>0</span>
					<?php esc_html_e( 'selected', 'dragon-glow' ); ?>
				</p>
				<div class="dg-wishlist-bulkbar__actions">
					<button type="button"
					        class="dg-wishlist-btn dg-wishlist-btn--ghost"
					        data-dg-wl-bulk-remove>
						<span class="material-symbols-outlined" aria-hidden="true">delete</span>
						<?php esc_html_e( 'Remove selected', 'dragon-glow' ); ?>
					</button>
					<button type="button"
					        class="dg-wishlist-btn dg-wishlist-btn--primary"
					        data-dg-wl-bulk-add>
						<span class="material-symbols-outlined" aria-hidden="true">shopping_bag</span>
						<?php esc_html_e( 'Add selected to bag', 'dragon-glow' ); ?>
					</button>
					<button type="button"
					        class="dg-wishlist-btn dg-wishlist-btn--text"
					        data-dg-wl-clear-all>
						<span class="material-symbols-outlined" aria-hidden="true">layers_clear</span>
						<?php esc_html_e( 'Clear wishlist', 'dragon-glow' ); ?>
					</button>
				</div>
			</div>
		</div>

		<!-- 7. Share modal -->
		<?php get_template_part( 'template-parts/wishlist/share-modal' ); ?>

		<!-- 8. Toasts (shared) -->
		<div class="dg-wishlist-toasts" data-dg-wl-toasts aria-live="polite" aria-atomic="true"></div>

	</div>
</main>

<?php get_footer(); ?>
