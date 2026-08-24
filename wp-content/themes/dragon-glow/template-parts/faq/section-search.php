<?php
/**
 * Dragon Glow — FAQ: Search
 * Ô tìm kiếm trực tiếp (lọc câu hỏi + câu trả lời). Logic ở assets/js/faq.js.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="dg-faq-search" data-sr>
	<span class="material-symbols-outlined dg-faq-search-icon" aria-hidden="true">search</span>
	<input
		type="search"
		id="dg-faq-search"
		class="dg-faq-search-input"
		placeholder="<?php esc_attr_e( 'Search', 'dragon-glow' ); ?>"
		aria-label="<?php esc_attr_e( 'Search the questions', 'dragon-glow' ); ?>"
		aria-describedby="dg-faq-search-status"
		autocomplete="off"
	/>
	<button
		type="button"
		id="dg-faq-search-clear"
		class="dg-faq-search-clear"
		aria-label="<?php esc_attr_e( 'Clear search', 'dragon-glow' ); ?>"
		hidden
	>
		<span class="material-symbols-outlined" aria-hidden="true">close</span>
	</button>

	<?php // Thông báo số kết quả cho cả người dùng lẫn screen reader. ?>
	<p id="dg-faq-search-status" class="dg-faq-search-status" role="status" aria-live="polite"></p>
</div>
