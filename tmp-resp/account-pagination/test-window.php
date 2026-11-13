<?php
/**
 * Unit test cho dg_account_orders_page_window().
 *
 * Chạy: php tmp-resp/account-pagination/test-window.php
 */
$orders_file = __DIR__ . '/../../wp-content/themes/dragon-glow/inc/woocommerce/account/orders.php';

// Stub ABSPATH + chỉ nạp helper (file có gọi wc_get_orders nên chỉ test helper).
define( 'ABSPATH', __DIR__ );

// Nạp riêng phần helper bằng cách eval file và chặn phần render.
$src = file_get_contents( $orders_file );
// Bỏ opening tag `<?php` (eval cần code không có tag).
$src = preg_replace( '/^<\?php/', '', $src );
// Cắt file từ "function dg_render_account_orders_panel" trở đi.
$cut = strpos( $src, 'function dg_render_account_orders_panel' );
$helper_only = substr( $src, 0, $cut );
eval( $helper_only );

$cases = array(
	// [current, total, expected]
	array( 1, 1, array( 1 ) ),
	array( 1, 3, array( 1, 2, 3 ) ),
	array( 1, 7, array( 1, 2, 3, 4, 5, 6, 7 ) ),
	array( 1, 8, array( 1, 2, '…', 8 ) ),
	array( 1, 12, array( 1, 2, '…', 12 ) ),
	array( 6, 12, array( 1, '…', 5, 6, 7, '…', 12 ) ),
	array( 2, 12, array( 1, 2, 3, '…', 12 ) ),
	array( 11, 12, array( 1, '…', 10, 11, 12 ) ),
	array( 12, 12, array( 1, '…', 11, 12 ) ),
	array( 3, 12, array( 1, 2, 3, 4, '…', 12 ) ),
	array( 10, 12, array( 1, '…', 9, 10, 11, 12 ) ),
	// Out of range inputs are clamped.
	array( 0, 12, array( 1, 2, '…', 12 ) ),
	array( 99, 12, array( 1, '…', 11, 12 ) ),
	array( 5, 0, array( 1 ) ),
);

$fail = 0;
foreach ( $cases as $case ) {
	list( $current, $total, $expected ) = $case;
	$actual   = dg_account_orders_page_window( $current, $total );
	$ok       = ( $actual === $expected );
	$max_len  = count( $actual );

	if ( ! $ok ) {
		$fail++;
	}
	printf(
		"%-4s current=%-3d total=%-3d len=%-2d  %s\n",
		$ok ? 'PASS' : 'FAIL',
		$current,
		$total,
		$max_len,
		$ok ? '' : 'expected ' . json_encode( $expected ) . ' got ' . json_encode( $actual )
	);
}

// Invariant: window length must never exceed 7 for large totals (mobile fit).
foreach ( range( 1, 40 ) as $current ) {
	$w = dg_account_orders_page_window( $current, 40 );
	if ( count( $w ) > 7 ) {
		$fail++;
		printf( "FAIL current=%d total=40 len=%d exceeds 7\n", $current, count( $w ) );
	}
}

echo $fail === 0 ? "\nALL PASS\n" : "\n{$fail} FAILURES\n";
exit( $fail === 0 ? 0 : 1 );
