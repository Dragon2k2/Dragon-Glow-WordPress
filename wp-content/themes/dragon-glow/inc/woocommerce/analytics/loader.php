<?php
/**
 * Dragon Glow — WooCommerce Analytics (Loader)
 *
 * Thin loader for the Analytics reliability layer. Logic lives in the
 * sibling files of this folder (`inc/woocommerce/analytics/`):
 *
 *   - scheduler.php          Resolves WooCommerce's importer class + logger.
 *   - excluded-statuses.php  Keeps unpaid "On hold" (BACS) orders out of revenue.
 *   - sync-tool.php          Manual "Sync orders to Analytics" tool
 *                            (WooCommerce → Status → Tools), batched and resumable.
 *   - health-check.php       Site Health test that flags a stalled job queue.
 *   - realtime-sync.php      Shutdown fallback that imports new orders/refunds
 *                            while the queue is stalled.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/scheduler.php';
require_once __DIR__ . '/excluded-statuses.php';
require_once __DIR__ . '/sync-tool.php';
require_once __DIR__ . '/health-check.php';
require_once __DIR__ . '/realtime-sync.php';
