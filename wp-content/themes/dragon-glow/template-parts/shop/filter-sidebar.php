<?php
/**
 * Dragon Glow — Filter Content (body of the dropdown panel)
 * Shared by the desktop dropdown (in the section header) and the
 * mobile filter sheet.
 *
 * NOT a sidebar anymore — just the inner content rendered inside a
 * `position: absolute` panel anchored to the "Filter by Skin Concern"
 * trigger. Mobile usage wraps it in a fixed sheet.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Determine shop URL
$shop_url = dg_is_woocommerce_active()
	? get_permalink( wc_get_page_id( 'shop' ) )
	: home_url( '/shop/' );

// Current category (WC only)
$current_category    = null;
$current_category_id = 0;
if ( dg_is_woocommerce_active() ) {
	$current_category    = get_queried_object();
	$current_category_id = is_product_category() ? $current_category->term_id : 0;
}

// Top-level product categories (WC only)
$categories = array();
if ( dg_is_woocommerce_active() ) {
	$categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => 0,
	) );
}

// Selected values from query string.
$selected_skin_types = isset( $_GET['skin_type'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	? array_map( 'sanitize_title', (array) wp_unslash( $_GET['skin_type'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	: array();
$selected_ingredients = isset( $_GET['ingredient'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	? array_map( 'sanitize_title', (array) wp_unslash( $_GET['ingredient'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	: array();
$selected_min_price = isset( $_GET['min_price'] ) ? max( 0, (float) $_GET['min_price'] ) : 0;   // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$selected_max_price = isset( $_GET['max_price'] ) ? max( 0, (float) $_GET['max_price'] ) : 200; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$selected_rating    = isset( $_GET['rating'] ) ? max( 1, min( 5, (int) $_GET['rating'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Dynamic WooCommerce taxonomies for filter data.
$skin_types = array();
$ingredients = array();

if ( dg_is_woocommerce_active() ) {
	$skin_taxonomies = array( 'pa_skin_type', 'pa_skin_concern' );
	foreach ( $skin_taxonomies as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			)
		);
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$skin_types = $terms;
			break;
		}
	}

	$ingredient_taxonomies = array( 'pa_ingredient', 'product_tag' );
	foreach ( $ingredient_taxonomies as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			)
		);
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$ingredients = $terms;
			break;
		}
	}
}
?>
<div class="dg-filter-content space-y-8" id="dg-filter-sidebar">

	<!-- Categories -->
	<section>
		<h3 class="font-label-sm text-label-sm text-primary tracking-[0.2em] uppercase mb-4">
			<?php esc_html_e( 'Categories', 'dragon-glow' ); ?>
		</h3>
		<ul class="space-y-3">
			<?php if ( dg_is_woocommerce_active() && ! empty( $categories ) ) : ?>
				<?php foreach ( $categories as $category ) : ?>
					<?php
					$is_active    = $current_category_id === $category->term_id;
					$badge_class  = $is_active
						? 'bg-tertiary-container text-on-tertiary-container'
						: 'bg-secondary-container text-on-secondary-container';
					$li_class     = $is_active
						? 'flex items-center justify-between cursor-pointer text-primary font-semibold'
						: 'flex items-center justify-between cursor-pointer text-on-surface-variant hover:text-primary filter-transition';
					?>
					<li class="<?php echo esc_attr( $li_class ); ?>" data-category-item="<?php echo esc_attr( $category->slug ); ?>" data-category-label="<?php echo esc_attr( $category->name ); ?>">
						<span class="flex-1"><?php echo esc_html( $category->name ); ?></span>
						<span class="text-[10px] <?php echo esc_attr( $badge_class ); ?> px-2 py-0.5 rounded-full" data-badge>
							<?php echo esc_html( $category->count ); ?>
						</span>
					</li>
				<?php endforeach; ?>
			<?php else : ?>
				<?php
				$fallback_categories = array(
					array( 'name' => __( 'Cleansers', 'dragon-glow' ),     'key' => 'cleansers',      'count' => 12 ),
					array( 'name' => __( 'Serums & Oils', 'dragon-glow' ),'key' => 'serums',         'count' => 24 ),
					array( 'name' => __( 'Moisturizers', 'dragon-glow' ), 'key' => 'moisturizers',   'count' => 18 ),
					array( 'name' => __( 'Sun Protection', 'dragon-glow' ),'key' => 'sun-protection', 'count' => 8 ),
				);
				foreach ( $fallback_categories as $cat ) :
					$is_active   = $cat['key'] === 'serums';
					$li_class    = $is_active
						? 'flex items-center justify-between cursor-pointer text-primary font-semibold'
						: 'flex items-center justify-between cursor-pointer text-on-surface-variant hover:text-primary filter-transition';
					$badge_class = $is_active
						? 'text-[10px] bg-tertiary-container text-on-tertiary-container px-2 py-0.5 rounded-full'
						: 'text-[10px] bg-secondary-container text-on-secondary-container px-2 py-0.5 rounded-full';
				?>
					<li class="<?php echo esc_attr( $li_class ); ?>" data-category-item="<?php echo esc_attr( $cat['key'] ); ?>" data-category-label="<?php echo esc_attr( wp_strip_all_tags( $cat['name'] ) ); ?>">
						<span><?php echo wp_kses_post( $cat['name'] ); ?></span>
						<span class="<?php echo esc_attr( $badge_class ); ?>" data-badge><?php echo esc_html( $cat['count'] ); ?></span>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
	</section>

	<!-- Price Range -->
	<section>
		<h3 class="font-label-sm text-label-sm text-primary tracking-[0.2em] uppercase mb-4">
			<?php esc_html_e( 'Price Range', 'dragon-glow' ); ?>
		</h3>
		<input type="range"
			   id="price-range"
			   min="0"
			   max="200"
			   value="<?php echo (int) $selected_max_price; ?>"
			   step="1"
			   class="w-full h-1.5 bg-outline-variant rounded-lg appearance-none cursor-pointer accent-primary" />
		<div class="flex justify-between mt-3 text-label-sm font-label-sm text-on-surface-variant">
			<span>$<span id="price-min-label"><?php echo (int) $selected_min_price; ?></span></span>
			<span>$<span id="price-max-label"><?php echo (int) $selected_max_price; ?></span></span>
		</div>
	</section>

	<!-- Skin Type -->
	<section>
		<h3 class="font-label-sm text-label-sm text-primary tracking-[0.2em] uppercase mb-4">
			<?php esc_html_e( 'Skin Type', 'dragon-glow' ); ?>
		</h3>
		<div class="grid grid-cols-1 gap-3">
			<?php if ( ! empty( $skin_types ) ) : ?>
				<?php foreach ( $skin_types as $term ) : ?>
					<?php
					$is_checked = in_array( $term->slug, $selected_skin_types, true );
					?>
					<label class="flex items-center gap-3 cursor-pointer group">
						<input type="checkbox"
							   name="skin_type[]"
							   value="<?php echo esc_attr( $term->slug ); ?>"
							   data-skin="<?php echo esc_attr( $term->slug ); ?>"
							   data-label="<?php echo esc_attr( $term->name ); ?>"
							   class="rounded border-outline text-primary focus:ring-primary-container"
							   <?php checked( $is_checked ); ?> />
						<span class="text-on-surface-variant group-hover:text-primary transition-colors <?php echo $is_checked ? 'text-primary' : ''; ?>">
							<?php echo esc_html( $term->name ); ?>
						</span>
						<span class="ml-auto text-[10px] bg-secondary-container text-on-secondary-container px-2 py-0.5 rounded-full">
							<?php echo esc_html( (string) $term->count ); ?>
						</span>
					</label>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="text-label-sm text-on-surface-variant">
					<?php esc_html_e( 'No skin-type data available yet.', 'dragon-glow' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<!-- Ingredients -->
	<section>
		<h3 class="font-label-sm text-label-sm text-primary tracking-[0.2em] uppercase mb-4">
			<?php esc_html_e( 'Ingredients', 'dragon-glow' ); ?>
		</h3>
		<div class="flex flex-wrap gap-2">
			<?php if ( ! empty( $ingredients ) ) : ?>
				<?php foreach ( $ingredients as $term ) : ?>
					<?php
					$is_selected   = in_array( $term->slug, $selected_ingredients, true );
					$btn_class     = $is_selected
						? 'px-3 py-1 bg-primary-container text-on-primary-container rounded-full text-label-sm font-label-sm'
						: 'px-3 py-1 bg-surface-container-high rounded-full text-label-sm font-label-sm hover:bg-primary-container transition-colors';
					$raw_label     = html_entity_decode( wp_strip_all_tags( (string) $term->name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					$display_label = preg_replace( '/\s*[;；]\s*/u', ', ', $raw_label );
					$display_label = preg_replace( '/\s*,\s*/', ', ', (string) $display_label );
					$display_label = trim( (string) $display_label );
					?>
					<button
						class="<?php echo esc_attr( $btn_class ); ?>"
						type="button"
						data-ingredient="<?php echo esc_attr( $term->slug ); ?>"
						data-label="<?php echo esc_attr( $display_label ); ?>"
					>
						<?php echo esc_html( $display_label ); ?> (<?php echo esc_html( (string) $term->count ); ?>)
					</button>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="text-label-sm text-on-surface-variant">
					<?php esc_html_e( 'No ingredient data available yet.', 'dragon-glow' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<!-- Ratings -->
	<section>
		<h3 class="font-label-sm text-label-sm text-primary tracking-[0.2em] uppercase mb-4">
			<?php esc_html_e( 'Ratings', 'dragon-glow' ); ?>
		</h3>
		<div class="space-y-2">
			<?php for ( $r = 5; $r >= 1; $r-- ) : ?>
			<label class="flex items-center gap-2 cursor-pointer group">
				<input type="radio" name="rating" data-rating-filter="<?php echo (int) $r; ?>" class="text-primary focus:ring-primary-container" <?php checked( $selected_rating, $r ); ?> />
				<div class="flex" style="color: #F1CA50;">
					<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
					<span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' <?php echo $s <= $r ? 1 : 0; ?>;">star</span>
					<?php endfor; ?>
				</div>
			</label>
			<?php endfor; ?>
		</div>
	</section>

	<!-- Apply / Reset row -->
	<div class="flex items-center justify-between pt-2 border-t border-outline-variant">
		<button type="button"
				id="dg-filter-reset"
				class="text-label-sm font-label-sm text-on-surface-variant hover:text-primary transition-colors uppercase tracking-widest">
			<?php esc_html_e( 'Reset', 'dragon-glow' ); ?>
		</button>
		<button type="button"
				id="dg-filter-apply"
				class="bg-primary text-on-primary px-6 py-2 font-label-sm text-label-sm uppercase tracking-widest hover:brightness-110 transition-all">
			<?php esc_html_e( 'Apply', 'dragon-glow' ); ?>
		</button>
	</div>

</div>
