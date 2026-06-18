<?php
/**
 * Dragon Glow — Shop Pagination
 * Text-link pagination (Previous | 1 2 3 ... N | Next)
 * Matches Stitch design: shop-page1 / shop-page2
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

// Show only 5 numbered pages (1 ... current-1, current, current+1 ... last)
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
<div class="mt-24 flex flex-col items-center gap-8 reveal-on-scroll" id="dg-shop-pagination">
	<div class="flex items-center gap-8">
		<?php if ( $current > 1 ) : ?>
			<a class="flex items-center gap-2 text-outline/70 hover:text-primary transition-all duration-500 group transform hover:-translate-x-1"
			   href="<?php echo esc_url( get_pagenum_link( $current - 1 ) ); ?>">
				<span class="material-symbols-outlined text-[18px]">chevron_left</span>
				<span class="font-label-sm text-label-sm uppercase tracking-widest">
					<?php esc_html_e( 'Previous', 'dragon-glow' ); ?>
				</span>
			</a>
		<?php else : ?>
			<span class="flex items-center gap-2 text-outline/40 cursor-not-allowed">
				<span class="material-symbols-outlined text-[18px]">chevron_left</span>
				<span class="font-label-sm text-label-sm uppercase tracking-widest">
					<?php esc_html_e( 'Previous', 'dragon-glow' ); ?>
				</span>
			</span>
		<?php endif; ?>

		<div class="flex items-center gap-6">
			<?php foreach ( $pages_to_show as $page_num ) : ?>
				<?php if ( '…' === $page_num ) : ?>
					<span class="font-label-sm text-label-sm text-outline/40">…</span>
				<?php elseif ( (int) $page_num === (int) $current ) : ?>
					<a aria-current="page"
					   class="font-label-sm text-label-sm text-primary border-b border-primary pb-1"
					   href="<?php echo esc_url( get_pagenum_link( $page_num ) ); ?>">
						<?php echo esc_html( $page_num ); ?>
					</a>
				<?php else : ?>
					<a class="font-label-sm text-label-sm text-outline/70 hover:text-primary nav-link-underline transition-colors duration-300"
					   href="<?php echo esc_url( get_pagenum_link( $page_num ) ); ?>">
						<?php echo esc_html( $page_num ); ?>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>

		<?php if ( $current < $total ) : ?>
			<a class="flex items-center gap-2 text-outline/70 hover:text-primary transition-all duration-500 group transform hover:translate-x-1"
			   href="<?php echo esc_url( get_pagenum_link( $current + 1 ) ); ?>">
				<span class="font-label-sm text-label-sm uppercase tracking-widest">
					<?php esc_html_e( 'Next', 'dragon-glow' ); ?>
				</span>
				<span class="material-symbols-outlined text-[18px]">chevron_right</span>
			</a>
		<?php else : ?>
			<span class="flex items-center gap-2 text-outline/40 cursor-not-allowed">
				<span class="font-label-sm text-label-sm uppercase tracking-widest">
					<?php esc_html_e( 'Next', 'dragon-glow' ); ?>
				</span>
				<span class="material-symbols-outlined text-[18px]">chevron_right</span>
			</span>
		<?php endif; ?>
	</div>
</div>