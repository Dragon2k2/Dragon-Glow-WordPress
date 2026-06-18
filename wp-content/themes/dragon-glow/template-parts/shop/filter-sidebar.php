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
$shop_url = class_exists( 'WooCommerce' )
	? get_permalink( wc_get_page_id( 'shop' ) )
	: home_url( '/shop/' );

// Current category (WC only)
$current_category    = null;
$current_category_id = 0;
if ( class_exists( 'WooCommerce' ) ) {
	$current_category    = get_queried_object();
	$current_category_id = is_product_category() ? $current_category->term_id : 0;
}

// Top-level product categories (WC only)
$categories = array();
if ( class_exists( 'WooCommerce' ) ) {
	$categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => 0,
	) );
}

// Skin types
$skin_types = array(
	'oily'        => __( 'Oily', 'dragon-glow' ),
	'dry'         => __( 'Dry', 'dragon-glow' ),
	'sensitive'   => __( 'Sensitive', 'dragon-glow' ),
	'combination' => __( 'Combination', 'dragon-glow' ),
);
?>
<div class="dg-filter-content space-y-8" id="dg-filter-sidebar">

	<!-- Categories -->
	<section>
		<h3 class="font-label-sm text-label-sm text-primary tracking-[0.2em] uppercase mb-4">
			<?php esc_html_e( 'Categories', 'dragon-glow' ); ?>
		</h3>
		<ul class="space-y-3">
			<?php if ( class_exists( 'WooCommerce' ) && ! empty( $categories ) ) : ?>
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
					<li class="<?php echo esc_attr( $li_class ); ?>" data-category-item="<?php echo esc_attr( $category->slug ); ?>">
						<a class="flex-1" href="<?php echo esc_url( get_term_link( $category ) ); ?>">
							<?php echo esc_html( $category->name ); ?>
						</a>
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
					<li class="<?php echo esc_attr( $li_class ); ?>" data-category-item="<?php echo esc_attr( $cat['key'] ); ?>">
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
			   value="120"
			   class="w-full h-1.5 bg-outline-variant rounded-lg appearance-none cursor-pointer accent-primary" />
		<div class="flex justify-between mt-3 text-label-sm font-label-sm text-on-surface-variant">
			<span>$0</span>
			<span>$200</span>
		</div>
	</section>

	<!-- Skin Type -->
	<section>
		<h3 class="font-label-sm text-label-sm text-primary tracking-[0.2em] uppercase mb-4">
			<?php esc_html_e( 'Skin Type', 'dragon-glow' ); ?>
		</h3>
		<div class="grid grid-cols-1 gap-3">
			<?php foreach ( $skin_types as $key => $label ) : ?>
				<?php $is_checked = ( 'dry' === $key ); ?>
				<label class="flex items-center gap-3 cursor-pointer group">
					<input type="checkbox"
						   name="skin_type[]"
						   value="<?php echo esc_attr( $key ); ?>"
						   data-skin="<?php echo esc_attr( $key ); ?>"
						   class="rounded border-outline text-primary focus:ring-primary-container"
						   <?php checked( $is_checked ); ?> />
					<span class="text-on-surface-variant group-hover:text-primary transition-colors <?php echo $is_checked ? 'text-primary' : ''; ?>">
						<?php echo esc_html( $label ); ?>
					</span>
				</label>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- Ingredients -->
	<section>
		<h3 class="font-label-sm text-label-sm text-primary tracking-[0.2em] uppercase mb-4">
			<?php esc_html_e( 'Ingredients', 'dragon-glow' ); ?>
		</h3>
		<div class="flex flex-wrap gap-2">
			<button class="px-3 py-1 bg-surface-container-high rounded-full text-label-sm font-label-sm hover:bg-primary-container transition-colors" type="button" data-ingredient="vitamin-c">
				<?php esc_html_e( 'Vitamin C', 'dragon-glow' ); ?>
			</button>
			<button class="px-3 py-1 bg-primary-container text-on-primary-container rounded-full text-label-sm font-label-sm" type="button" data-ingredient="retinol">
				<?php esc_html_e( 'Retinol', 'dragon-glow' ); ?>
			</button>
			<button class="px-3 py-1 bg-surface-container-high rounded-full text-label-sm font-label-sm hover:bg-primary-container transition-colors" type="button" data-ingredient="hyaluronic">
				<?php esc_html_e( 'Hyaluronic Acid', 'dragon-glow' ); ?>
			</button>
			<button class="px-3 py-1 bg-surface-container-high rounded-full text-label-sm font-label-sm hover:bg-primary-container transition-colors" type="button" data-ingredient="niacinamide">
				<?php esc_html_e( 'Niacinamide', 'dragon-glow' ); ?>
			</button>
		</div>
	</section>

	<!-- Ratings -->
	<section>
		<h3 class="font-label-sm text-label-sm text-primary tracking-[0.2em] uppercase mb-4">
			<?php esc_html_e( 'Ratings', 'dragon-glow' ); ?>
		</h3>
		<div class="space-y-2">
			<label class="flex items-center gap-2 cursor-pointer group">
				<input type="radio" name="rating" data-rating-filter="4" class="text-primary focus:ring-primary-container" />
				<div class="flex text-tertiary">
					<span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
					<span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
					<span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
					<span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
					<span class="material-symbols-outlined text-sm">star</span>
				</div>
				<span class="text-label-sm text-on-surface-variant">&amp; Up</span>
			</label>
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
				id="dg-filter-close"
				class="bg-primary text-on-primary px-6 py-2 font-label-sm text-label-sm uppercase tracking-widest hover:brightness-110 transition-all">
			<?php esc_html_e( 'Apply', 'dragon-glow' ); ?>
		</button>
	</div>

</div>
