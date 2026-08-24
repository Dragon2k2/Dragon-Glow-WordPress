<?php
/**
 * Template part — Cookie Policy / Specific Cookies Table
 *
 * Render bảng 5 cột × N hàng. Trên mobile, cột NAME sticky-left, các cột còn
 * lại cho scroll ngang. Có ARIA hint ẩn ở ≥768px để screen reader biết bảng
 * có thể scroll ngang.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$rows = dg_cookies_table_data();
if ( empty( $rows ) ) {
	return;
}

// Gom type để render badge với class modifier.
$type_modifier_map = array(
	'strictly necessary' => 'strictly',
	'analytics'          => 'analytics',
	'functional'         => 'functional',
	'advertising'        => 'advertising',
);
?>
<p class="dg-cookie-table-hint" aria-hidden="true">
	<span class="material-symbols-outlined">swipe_right</span>
	Vuốt ngang để xem thêm
</p>
<div class="dg-cookie-table-scroll" role="region" aria-label="<?php esc_attr_e( 'Cookies table', 'dragon-glow' ); ?>" tabindex="0">
	<table class="dg-cookie-table">
		<thead>
			<tr>
				<th scope="col" class="dg-cookie-table-col-name"><?php esc_html_e( 'Name', 'dragon-glow' ); ?></th>
				<th scope="col" class="dg-cookie-table-col-provider"><?php esc_html_e( 'Provider', 'dragon-glow' ); ?></th>
				<th scope="col" class="dg-cookie-table-col-purpose"><?php esc_html_e( 'Purpose', 'dragon-glow' ); ?></th>
				<th scope="col" class="dg-cookie-table-col-type"><?php esc_html_e( 'Type', 'dragon-glow' ); ?></th>
				<th scope="col" class="dg-cookie-table-col-duration"><?php esc_html_e( 'Duration', 'dragon-glow' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $rows as $row ) :
				$type_key = strtolower( trim( $row['type'] ) );
				$modifier = isset( $type_modifier_map[ $type_key ] ) ? $type_modifier_map[ $type_key ] : 'other';
				?>
				<tr>
					<th scope="row" class="dg-cookie-table-col-name"><code class="dg-cookie-table-name"><?php echo esc_html( $row['name'] ); ?></code></th>
					<td class="dg-cookie-table-col-provider"><?php echo esc_html( $row['provider'] ); ?></td>
					<td class="dg-cookie-table-col-purpose"><?php echo esc_html( $row['purpose'] ); ?></td>
					<td class="dg-cookie-table-col-type">
						<span class="dg-cookie-table-type dg-cookie-table-type--<?php echo esc_attr( $modifier ); ?>">
							<?php echo esc_html( $row['type'] ); ?>
						</span>
					</td>
					<td class="dg-cookie-table-col-duration"><?php echo esc_html( $row['duration'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>