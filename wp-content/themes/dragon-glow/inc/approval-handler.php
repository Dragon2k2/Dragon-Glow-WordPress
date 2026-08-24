<?php
/**
 * Dragon Glow — Careers Approval Handler (loader)
 *
 * Email-only flow (Cách A):
 *  Khách submit form → HR nhận email có 2 link (?dg_action=approve|reject&token=…).
 *  Khi HR click 1 trong 2 link, trình duyệt mở trang webform trên site:
 *    - Approve → form nhập ngày/giờ/địa điểm → gửi email lịch phỏng vấn cho khách.
 *    - Reject  → form nhập lý do (optional) → gửi email từ chối cho khách.
 *
 *  Token lưu trong WordPress transient (14 ngày, dùng 1 lần). Sau khi HR xử
 *  lý xong, transient bị xoá để chống click lại.
 *
 *  Modules dưới `inc/careers/`:
 *    - approval-route.php     Routing + token consume/finalise.
 *    - approval-decision.php  Validate POST + send email via Brevo / wp_mail.
 *    - approval-emails.php    Build HTML email body (logic).
 *    - approval-ics.php       Build VCALENDAR string.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

require_once DG_DIR . '/inc/careers/approval-route.php';
require_once DG_DIR . '/inc/careers/approval-emails.php';
require_once DG_DIR . '/inc/careers/approval-ics.php';
require_once DG_DIR . '/inc/careers/approval-decision.php';
