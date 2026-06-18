<?php
/**
 * Dragon Glow — Shop Section Header
 * "The Curated Glow" heading + filter dropdown trigger row
 * Matches Stitch design: shop-page1 / shop-page2
 *
 * The filter trigger on the right is wrapped in a `relative` container that
 * also hosts the floating filter dropdown panel (rendered via the
 * filter-sidebar template part).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$section_title = get_theme_mod( 'dg_shop_section_title', __( 'The Curated Glow', 'dragon-glow' ) );
$section_text  = get_theme_mod(
	'dg_shop_section_text',
	__( "A deliberate selection of our most transformative formulas, designed to synchronize with your skin's natural circadian rhythm.", 'dragon-glow' )
);
?>
<div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-16 gap-6 reveal-on-scroll active" id="dg-curated-header">
	<div class="max-w-xl">
		<h2 class="font-headline-lg text-headline-lg text-on-surface mb-4">
			<?php echo esc_html( $section_title ); ?>
		</h2>
		<p class="font-body-md text-body-md text-on-surface-variant">
			<?php echo esc_html( $section_text ); ?>
		</p>
	</div>

	<!-- Filter dropdown trigger (Material style) -->
	<div class="relative shrink-0 self-end">
		<button type="button"
				id="dg-shop-filter-trigger"
				class="dg-filter-trigger inline-flex items-center gap-3 cursor-pointer group"
				aria-haspopup="dialog"
				aria-expanded="false"
				aria-controls="dg-filter-dropdown">
			<span class="dg-filter-trigger-text font-label-sm text-label-sm text-outline border-b border-outline pb-1 filter-transition">
				<?php esc_html_e( 'Filter by Skin Concern', 'dragon-glow' ); ?>
			</span>
			<span class="dg-filter-trigger-icon material-symbols-outlined text-outline transition-transform duration-300 group-hover:translate-y-1">
				expand_more
			</span>
		</button>

		<!-- Dropdown panel: filter content -->
		<div id="dg-filter-dropdown"
			 class="dg-filter-dropdown custom-scrollbar"
			 role="dialog"
			 aria-modal="false"
			 aria-label="<?php esc_attr_e( 'Filter products', 'dragon-glow' ); ?>">
			<?php get_template_part( 'template-parts/shop/filter-sidebar' ); ?>
		</div>
	</div>
</div>
