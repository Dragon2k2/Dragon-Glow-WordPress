<?php
/**
 * Dragon Glow — Active Filters
 * Renders active filter tags based on URL query params (WooCommerce)
 * or stays empty for the JS-driven mock flow.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$min_price  = isset( $_GET['min_price'] ) ? floatval( $_GET['min_price'] ) : 0;   // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$max_price  = isset( $_GET['max_price'] ) ? floatval( $_GET['max_price'] ) : 0;   // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$skin_type  = isset( $_GET['skin_type'] ) ? (array) $_GET['skin_type'] : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$orderby    = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$ingredient = isset( $_GET['ingredient'] ) ? sanitize_text_field( wp_unslash( $_GET['ingredient'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$has_filters = $min_price > 0 || $max_price > 0 || ! empty( $skin_type ) || ! empty( $orderby ) || ! empty( $ingredient );

if ( ! $has_filters ) {
	// Render an empty container for the JS-driven mock flow
	?>
	<div class="flex flex-wrap gap-3 mb-8" id="dg-active-filter-tags"></div>
	<?php
	return;
}
?>
<div class="flex flex-wrap gap-3 mb-8" id="dg-active-filter-tags">
	<?php if ( $min_price > 0 || $max_price > 0 ) : ?>
		<span class="inline-flex items-center gap-2 bg-secondary-container text-on-secondary-container px-4 py-1.5 rounded-full text-label-sm font-label-sm">
			<?php
			printf(
				esc_html__( 'Price: $%1$s - $%2$s', 'dragon-glow' ),
				esc_html( $min_price ),
				esc_html( $max_price )
			);
			?>
			<a class="material-symbols-outlined text-[16px] cursor-pointer hover:rotate-90 transition-transform"
			   href="<?php echo esc_url( remove_query_arg( array( 'min_price', 'max_price' ) ) ); ?>"
			   aria-label="<?php esc_attr_e( 'Remove price filter', 'dragon-glow' ); ?>">close</a>
		</span>
	<?php endif; ?>

	<?php if ( ! empty( $skin_type ) ) : ?>
		<?php foreach ( $skin_type as $type ) : ?>
			<span class="inline-flex items-center gap-2 bg-secondary-container text-on-secondary-container px-4 py-1.5 rounded-full text-label-sm font-label-sm">
				<?php echo esc_html( ucfirst( $type ) ); ?>
				<a class="material-symbols-outlined text-[16px] cursor-pointer hover:rotate-90 transition-transform"
				   href="<?php echo esc_url( remove_query_arg( 'skin_type' ) ); ?>"
				   aria-label="<?php esc_attr_e( 'Remove skin type filter', 'dragon-glow' ); ?>">close</a>
			</span>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( ! empty( $ingredient ) ) : ?>
		<span class="inline-flex items-center gap-2 bg-secondary-container text-on-secondary-container px-4 py-1.5 rounded-full text-label-sm font-label-sm">
			<?php echo esc_html( ucfirst( $ingredient ) ); ?>
			<a class="material-symbols-outlined text-[16px] cursor-pointer hover:rotate-90 transition-transform"
			   href="<?php echo esc_url( remove_query_arg( 'ingredient' ) ); ?>"
			   aria-label="<?php esc_attr_e( 'Remove ingredient filter', 'dragon-glow' ); ?>">close</a>
		</span>
	<?php endif; ?>

	<?php if ( ! empty( $orderby ) ) : ?>
		<span class="inline-flex items-center gap-2 bg-secondary-container text-on-secondary-container px-4 py-1.5 rounded-full text-label-sm font-label-sm">
			<?php
			$order_labels = array(
				'menu_order' => __( 'Default', 'dragon-glow' ),
				'popularity' => __( 'Popularity', 'dragon-glow' ),
				'rating'     => __( 'Rating', 'dragon-glow' ),
				'date'       => __( 'Newest', 'dragon-glow' ),
				'price'      => __( 'Price: Low to High', 'dragon-glow' ),
				'price-desc' => __( 'Price: High to Low', 'dragon-glow' ),
			);
			echo esc_html( $order_labels[ $orderby ] ?? $orderby );
			?>
			<a class="material-symbols-outlined text-[16px] cursor-pointer hover:rotate-90 transition-transform"
			   href="<?php echo esc_url( remove_query_arg( 'orderby' ) ); ?>"
			   aria-label="<?php esc_attr_e( 'Remove sort filter', 'dragon-glow' ); ?>">close</a>
		</span>
	<?php endif; ?>

	<button class="text-label-sm font-label-sm text-primary underline underline-offset-4 decoration-tertiary-container hover:text-on-surface transition-colors"
			id="dg-clear-all-filters"
			type="button">
		<?php esc_html_e( 'Clear All', 'dragon-glow' ); ?>
	</button>
</div>
