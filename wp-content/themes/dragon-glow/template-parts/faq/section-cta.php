<?php
/**
 * Dragon Glow — FAQ: Contact CTA
 * Lối ra cuối trang khi câu trả lời không nằm ở đây.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$contact_url = dg_faq_data()['contact_url'];
?>
<aside class="dg-faq-cta" data-sr>
	<p class="dg-faq-cta-text"><?php esc_html_e( 'Still wondering?', 'dragon-glow' ); ?></p>
	<a class="dg-faq-cta-link" href="<?php echo esc_url( $contact_url ); ?>" data-magnetic>
		<span class="dg-faq-cta-label"><?php esc_html_e( 'Reach the concierge', 'dragon-glow' ); ?></span>
		<span class="dg-faq-cta-arrow" aria-hidden="true">&rarr;</span>
	</a>
</aside>
