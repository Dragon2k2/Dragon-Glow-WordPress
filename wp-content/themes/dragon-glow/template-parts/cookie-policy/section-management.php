<?php
/**
 * Template part — Cookie Policy / How to manage your choices
 *
 * Section 7: hướng dẫn user quản lý cookie. Nút "Manage Cookies" trong section
 * này (cùng no-op placeholder như nút ở header) sẽ được wire vào consent UI
 * ở change riêng. Cả ba nơi (header button, banner modal, nút section này)
 * sẽ dùng chung một component.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;
?>
<p>You can accept or reject optional cookies through our cookie banner, and change your choice at any time with the &lsquo;Manage Cookies&rsquo; button on this page. You can also block or delete cookies in your browser settings, though blocking strictly necessary cookies may stop parts of the site from working. Withdrawing consent does not affect cookies already set before you withdrew it.</p>
<!--
	WIP: cùng placeholder với nút ở header. Consent UI sẽ được implement ở
	change riêng; khi đó cả hai nút sẽ mở cùng một modal/banner.
-->
<button class="dg-cookie-manage-btn dg-cookie-manage-btn--inline" type="button" disabled aria-disabled="true">
	Manage Cookies
</button>