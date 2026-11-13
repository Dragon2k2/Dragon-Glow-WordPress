<?php
/**
 * CLI test — dg_account_orders_page_window().
 *
 * Chạy: php tmp-resp/account-pagination/test-window-desktop.php
 */

$src = file_get_contents( __DIR__ . '/../../wp-content/themes/dragon-glow/inc/woocommerce/account/orders.php' );

// ABSPATH guard trong file gốc sẽ exit — định nghĩa nó để nạp được snippet.
define( 'ABSPATH', __DIR__ );

// Chỉ lấy 2 hàm cần test + phần đầu file (docblock/exit guard).
$start = strpos( $src, '/**' );
$end   = strpos( $src, 'function dg_render_account_orders_panel' );

$snippet = substr( $src, $start, $end - $start );

// Stub apply_filters.
function apply_filters( $tag, $value ) {
	return $value;
}

eval( $snippet );

$fail = 0;
function check( $label, $ok, $detail = '' ) {
	global $fail;
	if ( ! $ok ) {
		$fail++;
		echo "  FAIL $label" . ( $detail ? " — $detail" : '' ) . "\n";
	} else {
		echo "  PASS $label\n";
	}
}

echo "\n=== dg_account_orders_page_window() — radius 1 (compact) ===\n";
$cases = array(
	array( 6, 12, 1, array( 1, 5, 6, 7, 12 ) ),
	array( 1, 12, 1, array( 1, 2, 12 ) ),
	array( 2, 12, 1, array( 1, 2, 3, 12 ) ),
	array( 12, 12, 1, array( 1, 11, 12 ) ),
	array( 6, 50, 1, array( 1, 5, 6, 7, 50 ) ),
	array( 3, 5, 1, array( 1, 2, 3, 4, 5 ) ),
	array( 1, 3, 1, array( 1, 2, 3 ) ),
);
foreach ( $cases as $c ) {
	list( $cur, $total, $r, $exp ) = $c;
	$got = dg_account_orders_page_window( $cur, $total, $r );
	check( "r$r cur=$cur total=$total", $got === $exp, 'got [' . implode( ',', $got ) . '] exp [' . implode( ',', $exp ) . ']' );
}

echo "\n=== dg_account_orders_page_window() — radius 2 (wide) ===\n";
$cases = array(
	array( 6, 12, 2, array( 1, 4, 5, 6, 7, 8, 12 ) ),
	array( 25, 50, 2, array( 1, 23, 24, 25, 26, 27, 50 ) ),
	array( 1, 50, 2, array( 1, 2, 3, 50 ) ),
	array( 2, 50, 2, array( 1, 2, 3, 4, 50 ) ),
	array( 50, 50, 2, array( 1, 48, 49, 50 ) ),
	array( 49, 50, 2, array( 1, 47, 48, 49, 50 ) ),
);
foreach ( $cases as $c ) {
	list( $cur, $total, $r, $exp ) = $c;
	$got = dg_account_orders_page_window( $cur, $total, $r );
	check( "r$r cur=$cur total=$total", $got === $exp, 'got [' . implode( ',', $got ) . '] exp [' . implode( ',', $exp ) . ']' );
}

echo "\n=== Biên (robustness) ===\n";
check( 'total=0 → [1]', array( 1 ) === dg_account_orders_page_window( 1, 0, 2 ) );
check( 'current > total → clamp', dg_account_orders_page_window( 99, 10, 1 ) === dg_account_orders_page_window( 10, 10, 1 ) );
check( 'current < 1 → clamp', dg_account_orders_page_window( 0, 10, 1 ) === dg_account_orders_page_window( 1, 10, 1 ) );
check( 'radius=0 → chỉ first/last/current', array( 1, 25, 50 ) === dg_account_orders_page_window( 25, 50, 0 ) );

echo "\n=== Danh sách ngắn (≤5 trang) → không collapse ===\n";
foreach ( array( 1, 2, 3, 4, 5 ) as $t ) {
	check( "total=$t → hiện đủ", range( 1, $t ) === dg_account_orders_page_window( 1, $t, 2 ), 'got [' . implode( ',', dg_account_orders_page_window( 1, $t, 2 ) ) . ']' );
}

echo "\n=== 12 trang — khớp ảnh người dùng (desktop) ===\n";
$wide12 = dg_account_orders_page_window( 1, 12, 2 );
check( 'cur=1 total=12 r=2 → 1,2,3,12', array( 1, 2, 3, 12 ) === $wide12, 'got [' . implode( ',', $wide12 ) . ']' );
check( 'cur=1 total=12 r=2 → có ellipsis', count( $wide12 ) < 12, 'count=' . count( $wide12 ) );
$mid12 = dg_account_orders_page_window( 6, 12, 2 );
check( 'cur=6 total=12 r=2 → 1,4..8,12', array( 1, 4, 5, 6, 7, 8, 12 ) === $mid12, 'got [' . implode( ',', $mid12 ) . ']' );
$last12 = dg_account_orders_page_window( 12, 12, 2 );
check( 'cur=12 total=12 r=2 → 1,10,11,12', array( 1, 10, 11, 12 ) === $last12, 'got [' . implode( ',', $last12 ) . ']' );

echo "\n" . ( 0 === $fail ? "ALL PASS" : $fail . " FAILURES" ) . "\n";
exit( 0 === $fail ? 0 : 1 );
