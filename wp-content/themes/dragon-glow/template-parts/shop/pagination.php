<?php
/**
 * Dragon Glow — Shop Pagination
 * Glassmorphism luxury pills: Previous | page dots | Next
 * Style: Frosted glass, golden gradient border, 2026 premium feel
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Ưu tiên mock pagination (non-WC hardcode), sau đó WP_Query, cuối cùng $wp_query
if ( ! empty( $GLOBALS['dg_mock_pagination'] ) ) {
	$current = (int) $GLOBALS['dg_mock_pagination']['current'];
	$total   = (int) $GLOBALS['dg_mock_pagination']['total'];
} elseif ( ! empty( $GLOBALS['dg_product_query'] ) ) {
	$current = max( 1, (int) get_query_var( 'paged' ) ?: (int) get_query_var( 'page' ) );
	$total   = max( 1, (int) $GLOBALS['dg_product_query']->max_num_pages );
} else {
	$current = max( 1, (int) get_query_var( 'paged' ) ?: (int) get_query_var( 'page' ) );
	$total   = max( 1, (int) ( $GLOBALS['wp_query']->max_num_pages ?? 1 ) );
}

// Show only 5 numbered pages
$pages_to_show = array( 1 );
if ( $current > 3 ) {
	$pages_to_show[] = '…';
}
for ( $i = max( 2, $current - 1 ); $i <= min( $total - 1, $current + 1 ); $i++ ) {
	$pages_to_show[] = $i;
}
if ( $current < $total - 2 ) {
	$pages_to_show[] = '…';
}
if ( $total > 1 ) {
	$pages_to_show[] = $total;
}

$base_url = remove_query_arg( 'paged' );
?>
<div class="mt-20 flex items-center justify-center gap-4 reveal-on-scroll" id="dg-shop-pagination">

	<!-- ── Navigation row: Prev | dots | Next ─────────── -->
	<div class="flex items-center gap-4">

		<!-- PREVIOUS button -->
		<?php if ( $current > 1 ) : ?>
			<a class="dg-pager-btn dg-pager-btn--prev"
			   href="<?php echo esc_url( get_pagenum_link( $current - 1 ) ); ?>"
			   aria-label="<?php esc_attr_e( 'Previous page', 'dragon-glow' ); ?>">
				<span class="dg-pager-btn__icon material-symbols-outlined" aria-hidden="true">
					<span class="dg-pager-btn__arrow">→</span>
				</span>
				<span class="dg-pager-btn__label"><?php esc_html_e( 'Prev', 'dragon-glow' ); ?></span>
				<span class="dg-pager-btn__glow" aria-hidden="true"></span>
			</a>
		<?php else : ?>
			<span class="dg-pager-btn dg-pager-btn--prev dg-pager-btn--disabled" aria-disabled="true">
				<span class="dg-pager-btn__icon material-symbols-outlined" aria-hidden="true">
					<span class="dg-pager-btn__arrow">→</span>
				</span>
				<span class="dg-pager-btn__label"><?php esc_html_e( 'Prev', 'dragon-glow' ); ?></span>
			</span>
		<?php endif; ?>

		<!-- Page number pills -->
		<div class="flex items-center gap-2 mx-2">
			<?php foreach ( $pages_to_show as $page_num ) : ?>
				<?php if ( '…' === $page_num ) : ?>
					<span class="dg-page-ellipsis" aria-hidden="true">···</span>
				<?php elseif ( (int) $page_num === (int) $current ) : ?>
					<span class="dg-page-pill dg-page-pill--active" aria-current="page">
						<?php echo esc_html( $page_num ); ?>
					</span>
				<?php else : ?>
					<a class="dg-page-pill"
					   href="<?php echo esc_url( get_pagenum_link( $page_num ) ); ?>">
						<?php echo esc_html( $page_num ); ?>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>

		<!-- NEXT button -->
		<?php if ( $current < $total ) : ?>
			<a class="dg-pager-btn dg-pager-btn--next"
			   href="<?php echo esc_url( get_pagenum_link( $current + 1 ) ); ?>"
			   aria-label="<?php esc_attr_e( 'Next page', 'dragon-glow' ); ?>">
				<span class="dg-pager-btn__label"><?php esc_html_e( 'Next', 'dragon-glow' ); ?></span>
				<span class="dg-pager-btn__icon material-symbols-outlined" aria-hidden="true">
					<span class="dg-pager-btn__arrow">←</span>
				</span>
				<span class="dg-pager-btn__glow" aria-hidden="true"></span>
			</a>
		<?php else : ?>
			<span class="dg-pager-btn dg-pager-btn--next dg-pager-btn--disabled" aria-disabled="true">
				<span class="dg-pager-btn__label"><?php esc_html_e( 'Next', 'dragon-glow' ); ?></span>
				<span class="dg-pager-btn__icon material-symbols-outlined" aria-hidden="true">
					<span class="dg-pager-btn__arrow">←</span>
				</span>
			</span>
		<?php endif; ?>

	</div>
</div>