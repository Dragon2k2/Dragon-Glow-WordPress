<?php
/**
 * Dragon Glow — FAQ: Contact CTA
 * Banner cuối trang — frosted glass panel trên ảnh nền ethereal.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$contact_url = dg_faq_data()['contact_url'];
$cta_bg      = DG_URI . '/assets/images/faq/faq-cta-bg.jpg';
?>
<section class="dg-faq-cta" data-sr aria-labelledby="dg-faq-cta-heading">
	<div
		class="dg-faq-cta-bg"
		aria-hidden="true"
		style="--dg-faq-cta-bg: url('<?php echo esc_url( $cta_bg ); ?>');"
	></div>
	<div class="dg-faq-cta-panel">
		<h4 class="dg-faq-cta-title" id="dg-faq-cta-heading">
			<?php esc_html_e( 'Require Personal Assistance?', 'dragon-glow' ); ?>
		</h4>
		<p class="dg-faq-cta-text">
			<?php esc_html_e( 'A direct line, when the quiet pages cannot answer. We will respond within a day.', 'dragon-glow' ); ?>
		</p>
		<a class="dg-faq-cta-button" href="<?php echo esc_url( $contact_url ); ?>">
			<span class="dg-faq-cta-label"><?php esc_html_e( 'Contact Concierge', 'dragon-glow' ); ?></span>
			<span class="dg-faq-cta-arrow" aria-hidden="true">&rarr;</span>
		</a>
	</div>
</section>