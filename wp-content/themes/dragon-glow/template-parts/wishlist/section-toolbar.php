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

			<div class="dg-wishlist-toolbar__sort" data-dg-wl-sort-wrap>
				<span class="dg-wishlist-toolbar__sort-label"><?php esc_html_e( 'Sort by', 'dragon-glow' ); ?></span>

				<select id="dg-wl-sort"
				        class="dg-wishlist-toolbar__sort-select"
				        data-dg-wl-sort
				        aria-label="<?php esc_attr_e( 'Sort wishlist items', 'dragon-glow' ); ?>">
					<option value="date"><?php esc_html_e( 'Recently saved', 'dragon-glow' ); ?></option>
					<option value="price-asc"><?php esc_html_e( 'Price: low to high', 'dragon-glow' ); ?></option>
					<option value="price-desc"><?php esc_html_e( 'Price: high to low', 'dragon-glow' ); ?></option>
					<option value="name"><?php esc_html_e( 'Name: A → Z', 'dragon-glow' ); ?></option>
				</select>

				<button type="button"
				        class="dg-wishlist-toolbar__sort-trigger"
				        data-dg-wl-sort-trigger
				        aria-haspopup="listbox"
				        aria-expanded="false">
					<span class="dg-wishlist-toolbar__sort-icon material-symbols-outlined"
					      data-dg-wl-sort-icon
					      aria-hidden="true">schedule</span>
					<span class="dg-wishlist-toolbar__sort-current" data-dg-wl-sort-current><?php esc_html_e( 'Recently saved', 'dragon-glow' ); ?></span>
					<span class="dg-wishlist-toolbar__sort-chevron material-symbols-outlined" aria-hidden="true">expand_more</span>
				</button>

				<div class="dg-wishlist-toolbar__sort-panel"
				     data-dg-wl-sort-panel
				     role="listbox"
				     aria-label="<?php esc_attr_e( 'Sort wishlist items', 'dragon-glow' ); ?>"
				     hidden>
					<button type="button"
					        class="dg-wishlist-toolbar__sort-option is-active"
					        role="option"
					        aria-selected="true"
					        data-dg-wl-sort-option
					        data-value="date"
					        data-icon="schedule"
					        data-label="<?php esc_attr_e( 'Recently saved', 'dragon-glow' ); ?>"
					        data-sub="<?php esc_attr_e( 'Newest items first', 'dragon-glow' ); ?>">
						<span class="dg-wishlist-toolbar__sort-option-icon material-symbols-outlined" aria-hidden="true">schedule</span>
						<span class="dg-wishlist-toolbar__sort-option-body">
							<span class="dg-wishlist-toolbar__sort-option-label"><?php esc_html_e( 'Recently saved', 'dragon-glow' ); ?></span>
							<span class="dg-wishlist-toolbar__sort-option-sub"><?php esc_html_e( 'Newest items first', 'dragon-glow' ); ?></span>
						</span>
						<span class="dg-wishlist-toolbar__sort-option-check material-symbols-outlined" aria-hidden="true">check</span>
					</button>
					<button type="button"
					        class="dg-wishlist-toolbar__sort-option"
					        role="option"
					        aria-selected="false"
					        data-dg-wl-sort-option
					        data-value="price-asc"
					        data-icon="arrow_upward"
					        data-label="<?php esc_attr_e( 'Price: low to high', 'dragon-glow' ); ?>"
					        data-sub="<?php esc_attr_e( 'Cheapest first', 'dragon-glow' ); ?>">
						<span class="dg-wishlist-toolbar__sort-option-icon material-symbols-outlined" aria-hidden="true">arrow_upward</span>
						<span class="dg-wishlist-toolbar__sort-option-body">
							<span class="dg-wishlist-toolbar__sort-option-label"><?php esc_html_e( 'Price: low to high', 'dragon-glow' ); ?></span>
							<span class="dg-wishlist-toolbar__sort-option-sub"><?php esc_html_e( 'Cheapest first', 'dragon-glow' ); ?></span>
						</span>
						<span class="dg-wishlist-toolbar__sort-option-check material-symbols-outlined" aria-hidden="true">check</span>
					</button>
					<button type="button"
					        class="dg-wishlist-toolbar__sort-option"
					        role="option"
					        aria-selected="false"
					        data-dg-wl-sort-option
					        data-value="price-desc"
					        data-icon="arrow_downward"
					        data-label="<?php esc_attr_e( 'Price: high to low', 'dragon-glow' ); ?>"
					        data-sub="<?php esc_attr_e( 'Premium first', 'dragon-glow' ); ?>">
						<span class="dg-wishlist-toolbar__sort-option-icon material-symbols-outlined" aria-hidden="true">arrow_downward</span>
						<span class="dg-wishlist-toolbar__sort-option-body">
							<span class="dg-wishlist-toolbar__sort-option-label"><?php esc_html_e( 'Price: high to low', 'dragon-glow' ); ?></span>
							<span class="dg-wishlist-toolbar__sort-option-sub"><?php esc_html_e( 'Premium first', 'dragon-glow' ); ?></span>
						</span>
						<span class="dg-wishlist-toolbar__sort-option-check material-symbols-outlined" aria-hidden="true">check</span>
					</button>
					<button type="button"
					        class="dg-wishlist-toolbar__sort-option"
					        role="option"
					        aria-selected="false"
					        data-dg-wl-sort-option
					        data-value="name"
					        data-icon="sort_by_alpha"
					        data-label="<?php esc_attr_e( 'Name: A → Z', 'dragon-glow' ); ?>"
					        data-sub="<?php esc_attr_e( 'Alphabetical', 'dragon-glow' ); ?>">
						<span class="dg-wishlist-toolbar__sort-option-icon material-symbols-outlined" aria-hidden="true">sort_by_alpha</span>
						<span class="dg-wishlist-toolbar__sort-option-body">
							<span class="dg-wishlist-toolbar__sort-option-label"><?php esc_html_e( 'Name: A → Z', 'dragon-glow' ); ?></span>
							<span class="dg-wishlist-toolbar__sort-option-sub"><?php esc_html_e( 'Alphabetical', 'dragon-glow' ); ?></span>
						</span>
						<span class="dg-wishlist-toolbar__sort-option-check material-symbols-outlined" aria-hidden="true">check</span>
					</button>
				</div>
			</div>

		</div>
	</div>
</section>
