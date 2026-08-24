<?php
/**
 * Dragon Glow — Help Center: Search
 * Underline mở rộng thành Ivory container khi focus (Stitch pattern).
 * Logic filter ở assets/js/help-center.js.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$search = dg_hc_data()['search'];
?>
<section class="dg-hc-search" aria-label="<?php esc_attr_e( 'Search the help center', 'dragon-glow' ); ?>" data-sr-group>
	<div class="dg-hc-search-container" data-sr>
		<span class="material-symbols-outlined dg-hc-search-icon" aria-hidden="true">search</span>
		<input
			type="search"
			id="dg-hc-search"
			class="dg-hc-search-input"
			placeholder="<?php echo esc_attr( $search['placeholder'] ); ?>"
			aria-label="<?php echo esc_attr( $search['aria_label'] ); ?>"
			aria-describedby="dg-hc-search-status"
			autocomplete="off"
		/>
	</div>
	<p id="dg-hc-search-status" class="dg-hc-search-status" role="status" aria-live="polite"></p>
</section>