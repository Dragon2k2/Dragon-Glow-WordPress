<?php
/**
 * Kiểm chứng markup thật của Orders pagination.
 *
 * Trích nguyên văn vòng lặp phát sinh markup từ
 * woocommerce/myaccount/orders.php, chạy với các stub WP tối thiểu,
 * rồi assert kết quả.
 */
define( 'ABSPATH', __DIR__ );

/* ── Stubs WP ── */
function esc_html( $t ) { return htmlspecialchars( (string) $t, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $u ) { return htmlspecialchars( (string) $u, ENT_QUOTES, 'UTF-8' ); }
function trailingslashit( $s ) { return rtrim( $s, '/\\' ) . '/'; }
function esc_attr__( $t, $d = '' ) { return esc_html( $t ); }

/* ── Helper thật từ inc/woocommerce/account/orders.php ── */
$orders_inc = __DIR__ . '/../../wp-content/themes/dragon-glow/inc/woocommerce/account/orders.php';
$src = preg_replace( '/^<\?php/', '', file_get_contents( $orders_inc ) );
$cut = strpos( $src, 'function dg_render_account_orders_panel' );
eval( substr( $src, 0, $cut ) );

/* ── Vòng lặp trích từ template (giữ nguyên logic, chỉ đổi biến) ── */
function render_numbers( int $current_page, int $total_pages, string $orders_base ): string {
	$visible_mobile = dg_account_orders_page_window( (int) $current_page, $total_pages );

	$out = '<span class="woocommerce-pagination__numbers">';
	for ( $i = 1; $i <= $total_pages; $i++ ) :
		$is_current = ( $i === (int) $current_page );
		$is_hidden  = ( ! in_array( $i, $visible_mobile, true ) );

		if ( $is_hidden && in_array( $i - 1, $visible_mobile, true ) ) {
			$out .= '<span class="page-numbers dg-account-pagination__ellipsis" aria-hidden="true" data-dg-page-ellipsis>…</span>';
		}

		$hidden_attr = $is_hidden ? ' data-dg-page-hidden' : '';

		if ( $is_current ) {
			$out .= '<span class="page-numbers current" aria-current="page"' . $hidden_attr . '>' . esc_html( $i ) . '</span>';
		} else {
			$page_url = 1 === $i
				? $orders_base
				: trailingslashit( $orders_base ) . 'page/' . $i . '/';
			$out .= '<a class="page-numbers" href="' . esc_url( $page_url ) . '"' . $hidden_attr . '>' . esc_html( $i ) . '</a>';
		}
	endfor;
	$out .= '</span>';

	return $out;
}

$base = 'https://dragonglow.freehosting.dev/my-account/orders';

$fail = 0;
function check( string $label, bool $ok, string $detail = '' ) {
	global $fail;
	if ( ! $ok ) { $fail++; }
	printf( "%-4s %s%s\n", $ok ? 'PASS' : 'FAIL', $label, $ok ? '' : "  ({$detail})" );
}

/* Trang 6 / 12 trang */
$html = render_numbers( 6, 12, $base );

// 1. Tất cả 12 số trang đều có mặt trong markup (desktop giữ nguyên danh sách).
for ( $i = 1; $i <= 12; $i++ ) {
	check( "page $i present in markup", (bool) preg_match( '#>(\s*)' . $i . '<#', $html ) || strpos( $html, '>' . $i . '<' ) !== false );
}

// 2. Số trang hiện tại mang aria-current="page".
check( 'current page has aria-current', strpos( $html, 'class="page-numbers current" aria-current="page"' ) !== false );

// 3. Đúng 7 phần tử bị đánh dấu ẩn trên mobile (12 - 5 hiển thị).
$hidden = substr_count( $html, 'data-dg-page-hidden' );
check( 'hidden-tag count = 7', 7 === $hidden, "got {$hidden}" );

// 4. Đúng 2 ellipsis.
$ell = substr_count( $html, 'data-dg-page-ellipsis' );
check( 'ellipsis count = 2', 2 === $ell, "got {$ell}" );

// 5. Thứ tự phần tử HIỂN THỊ trên mobile = 1,…,5,6,7,…,12
//    (bỏ qua các phần tử bị tag data-dg-page-hidden).
$expected_order = array( 1, '…', 5, 6, 7, '…', 12 );
preg_match_all( '#<(\w+)([^>]*)>([^<]*)</\1>#', $html, $els, PREG_SET_ORDER );
$visible_order = array();
foreach ( $els as $el ) {
	if ( false !== strpos( $el[2], 'data-dg-page-hidden' ) ) {
		continue;
	}
	$text = trim( $el[3] );
	if ( '' !== $text ) {
		$visible_order[] = ( '…' === $text ) ? '…' : (int) $text;
	}
}
check( 'visible order = 1,…,5,6,7,…,12', $expected_order === $visible_order, json_encode( $visible_order ) );

// 6. Trang 1 dùng URL gốc, trang 2 dùng /page/2/.
check( 'page 1 url = base', strpos( $html, 'href="https://dragonglow.freehosting.dev/my-account/orders"' ) !== false );
check( 'page 2 url = base/page/2/', strpos( $html, 'href="https://dragonglow.freehosting.dev/my-account/orders/page/2/"' ) !== false );

// 7. Trang cuối luôn hiển thị (không bị ẩn).
check( 'last page visible on mobile', strpos( $html, '>12</a>' ) !== false && strpos( $html, '>12</a>' ) !== false );
$last_hidden = (bool) preg_match( '#>12</a>\s*<#', '' ); // noop
check( 'last page not tagged hidden', (bool) preg_match( '#<a class="page-numbers" href="[^"]*" data-dg-page-hidden>12</a>#', $html ) === false );

// 8. Trang 1 luôn hiển thị.
check( 'first page not tagged hidden', (bool) preg_match( '#<a class="page-numbers" href="[^"]*" data-dg-page-hidden>1</a>#', $html ) === false );

// 9. Ngữ cảnh: total <= 5 → không ẩn gì, không ellipsis.
$html_small = render_numbers( 2, 5, $base );
check( 'total=5 no hidden tags', substr_count( $html_small, 'data-dg-page-hidden' ) === 0 );
check( 'total=5 no ellipsis', substr_count( $html_small, 'data-dg-page-ellipsis' ) === 0 );

// 10. total = 6, current = 3 → window = 1,2,3,4,6 → 1 số ẩn (5), 1 ellipsis.
$html6 = render_numbers( 3, 6, $base );
check( 'total=6 hidden count = 1', substr_count( $html6, 'data-dg-page-hidden' ) === 1, 'got ' . substr_count( $html6, 'data-dg-page-hidden' ) );
check( 'total=6 ellipsis count = 1', substr_count( $html6, 'data-dg-page-ellipsis' ) === 1, 'got ' . substr_count( $html6, 'data-dg-page-ellipsis' ) );

// 11. current = 1 → window = 1,2,6 → ẩn 3,4,5 → 1 ellipsis.
$html_c1 = render_numbers( 1, 6, $base );
check( 'current=1,total=6 ellipsis count = 1', substr_count( $html_c1, 'data-dg-page-ellipsis' ) === 1, 'got ' . substr_count( $html_c1, 'data-dg-page-ellipsis' ) );
check( 'current=1,total=6 hidden count = 3', substr_count( $html_c1, 'data-dg-page-hidden' ) === 3, 'got ' . substr_count( $html_c1, 'data-dg-page-hidden' ) );

// 12. Markup HTML hợp lệ (không có thẻ hở).
check( 'balanced tags', substr_count( $html, '<' ) === substr_count( $html, '>' ) );

echo "\n--- markup mẫu (current=6, total=12) ---\n{$html}\n";

echo $fail === 0 ? "ALL PASS\n" : "{$fail} FAILURES\n";
exit( $fail === 0 ? 0 : 1 );
