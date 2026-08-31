<?php
/**
 * Dragon Glow — Wishlist: toolbar
 * Sticky filter + sort + bulk-actions bar. Pure markup — JS toggles the
 * `.is-active` state on the segment controls and the `[data-filter]` /
 * `[data-sort]` hidden inputs drive the JS-side list view.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="dg-wishlist-toolbar" data-sr>
	<div class="dg-wishlist-toolbar__row">

		<!-- Filter segment -->
		<div class="dg-wishlist-toolbar__segment" role="tablist" aria-label="<?php esc_attr_e( 'Filter wishlist', 'dragon-glow' ); ?>">
			<button type="button"
			        class="dg-wishlist-toolbar__seg is-active"
			        role="tab"
			        aria-selected="true"
			        data-dg-wl-filter="all">
				<span class="material-symbols-outlined" aria-hidden="true">apps</span>
				<span><?php esc_html_e( 'All items', 'dragon-glow' ); ?></span>
				<span class="dg-wishlist-toolbar__count" data-dg-wl-count="all">0</span>
			</button>
			<button type="button"
			        class="dg-wishlist-toolbar__seg"
			        role="tab"
			        aria-selected="false"
			        data-dg-wl-filter="in_stock">
				<span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
				<span><?php esc_html_e( 'In stock', 'dragon-glow' ); ?></span>
				<span class="dg-wishlist-toolbar__count" data-dg-wl-count="in_stock">0</span>
			</button>
			<button type="button"
			        class="dg-wishlist-toolbar__seg"
			        role="tab"
			        aria-selected="false"
			        data-dg-wl-filter="on_sale">
				<span class="material-symbols-outlined" aria-hidden="true">local_offer</span>
				<span><?php esc_html_e( 'On sale', 'dragon-glow' ); ?></span>
				<span class="dg-wishlist-toolbar__count" data-dg-wl-count="on_sale">0</span>
			</button>
		</div>

		<!-- Right cluster: select-all + sort dropdown -->
		<div class="dg-wishlist-toolbar__right">

			<label class="dg-wishlist-toolbar__select-all">
				<input type="checkbox"
				       class="dg-wishlist-toolbar__checkbox"
				       data-dg-wl-select-all
				       aria-label="<?php esc_attr_e( 'Select all items', 'dragon-glow' ); ?>" />
				<span class="dg-wishlist-toolbar__checkbox-mark" aria-hidden="true"></span>
				<span class="dg-wishlist-toolbar__select-all-label">
					<?php esc_html_e( 'Select', 'dragon-glow' ); ?>
					<span class="dg-wishlist-toolbar__select-count" data-dg-wl-selected-count>(0)</span>
				</span>
			</label>

			<label class="dg-wishlist-toolbar__sort" for="dg-wl-sort">
				<span class="dg-wishlist-toolbar__sort-label"><?php esc_html_e( 'Sort by', 'dragon-glow' ); ?></span>
				<select id="dg-wl-sort"
				        class="dg-wishlist-toolbar__sort-select"
				        data-dg-wl-sort>
					<option value="date"><?php esc_html_e( 'Recently saved', 'dragon-glow' ); ?></option>
					<option value="price-asc"><?php esc_html_e( 'Price: low to high', 'dragon-glow' ); ?></option>
					<option value="price-desc"><?php esc_html_e( 'Price: high to low', 'dragon-glow' ); ?></option>
					<option value="name"><?php esc_html_e( 'Name: A → Z', 'dragon-glow' ); ?></option>
				</select>
				<span class="material-symbols-outlined dg-wishlist-toolbar__sort-icon" aria-hidden="true">expand_more</span>
			</label>

		</div>
	</div>
</section>
