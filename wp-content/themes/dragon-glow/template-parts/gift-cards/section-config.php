<?php
/**
 * Dragon Glow — Gift Cards: Configuration / Details panel
 * Toggle Format + chip Value + form Recipient + nút Add to Bag.
 *
 * Submit flow (xử lý trong JS):
 *  - WC bật  : DGCart.add(productId, qty, {pa_format, pa_value})
 *  - WC tắt  : push mock item vào localStorage + điều hướng /cart/
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data    = dg_gift_cards_data();
$formats = $data['formats'];
$values  = $data['values'];
$form    = $data['form'];

// Khi WC bật: lấy product id thật từ slug 'gift-card'.
// Khi WC tắt: dùng mock_product_id (chỉ JS dùng).
if ( dg_is_woocommerce_active() ) {
	$product_id = function_exists( 'wc_get_product_id_by_slug' )
		? wc_get_product_id_by_slug( 'gift-card' )
		: 0;
} else {
	$product_id = $data['mock_product_id'];
}

$default_amount = $values[1]['amount']; // $100 (giống reference)
?>
<form
	class="dg-gift-config"
	data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
	data-mock-product-id="<?php echo esc_attr( (string) $data['mock_product_id'] ); ?>"
	data-wc-active="<?php echo esc_attr( dg_is_woocommerce_active() ? '1' : '0' ); ?>"
	data-default-amount="<?php echo esc_attr( (string) $default_amount ); ?>"
	novalidate
>
	<div class="dg-gift-config-head">
		<h2 class="dg-gift-config-title"><?php echo esc_html( $form['configure_title'] ); ?></h2>
		<p class="dg-gift-config-subtitle"><?php echo esc_html( $form['configure_subtitle'] ); ?></p>
	</div>

	<!-- Format Selection -->
	<fieldset class="dg-gift-field">
		<legend class="dg-gift-label"><?php echo esc_html( $form['format_label'] ); ?></legend>
		<div class="dg-gift-format-grid" role="radiogroup" aria-label="<?php echo esc_attr( $form['format_label'] ); ?>">
			<?php foreach ( $formats as $index => $format ) :
				$is_default = 0 === $index; ?>
				<button
					type="button"
					class="dg-gift-format<?php echo $is_default ? ' is-active' : ''; ?>"
					role="radio"
					aria-checked="<?php echo $is_default ? 'true' : 'false'; ?>"
					data-format="<?php echo esc_attr( $format['id'] ); ?>"
				>
					<span class="material-symbols-outlined dg-gift-format-icon" aria-hidden="true"><?php echo esc_html( $format['icon'] ); ?></span>
					<span class="dg-gift-format-label"><?php echo esc_html( $format['label'] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>
		<input type="hidden" name="format" value="<?php echo esc_attr( $formats[0]['id'] ); ?>" />
	</fieldset>

	<!-- Value Selection -->
	<fieldset class="dg-gift-field">
		<legend class="dg-gift-label"><?php echo esc_html( $form['value_label'] ); ?></legend>
		<div class="dg-gift-value-row" role="radiogroup" aria-label="<?php echo esc_attr( $form['value_label'] ); ?>">
			<?php foreach ( $values as $index => $value ) :
				$is_default = ( (int) $value['amount'] ) === $default_amount; ?>
				<button
					type="button"
					class="dg-gift-value<?php echo $is_default ? ' is-active' : ''; ?>"
					role="radio"
					aria-checked="<?php echo $is_default ? 'true' : 'false'; ?>"
					data-amount="<?php echo esc_attr( $value['amount'] ); ?>"
					data-slug="<?php echo esc_attr( $value['slug'] ); ?>"
				>
					<?php echo esc_html( dg_gift_cards_format_price( (int) $value['amount'] ) ); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<input type="hidden" name="value" value="<?php echo esc_attr( (string) $default_amount ); ?>" />
	</fieldset>

	<!-- Recipient Details -->
	<fieldset class="dg-gift-field dg-gift-field--recipient">
		<legend class="dg-gift-heading"><?php echo esc_html( $form['recipient_title'] ); ?></legend>

		<div class="dg-gift-input dg-gift-input--float">
			<input
				class="dg-gift-input-field"
				id="dg-gift-to"
				name="recipient_to"
				type="text"
				autocomplete="name"
				placeholder=" "
				required
			/>
			<label class="dg-gift-label" for="dg-gift-to"><?php echo esc_html( $form['recipient_to_label'] ); ?></label>
		</div>

		<div class="dg-gift-input dg-gift-input--float">
			<input
				class="dg-gift-input-field"
				id="dg-gift-email"
				name="recipient_email"
				type="email"
				autocomplete="email"
				placeholder=" "
				required
			/>
			<label class="dg-gift-label" for="dg-gift-email"><?php echo esc_html( $form['recipient_email_label'] ); ?></label>
		</div>

		<div class="dg-gift-input dg-gift-input--textarea">
			<label class="dg-gift-label" for="dg-gift-message"><?php echo esc_html( $form['message_label'] ); ?></label>
			<textarea
				class="dg-gift-input-field dg-gift-input-field--textarea"
				id="dg-gift-message"
				name="recipient_message"
				rows="3"
				placeholder="<?php echo esc_attr( $form['message_placeholder'] ); ?>"
			></textarea>
		</div>
	</fieldset>

	<!-- Actions -->
	<div class="dg-gift-actions">
		<button type="submit" class="dg-gift-submit">
			<span class="dg-gift-submit-label"><?php echo esc_html( $form['submit_label'] ); ?></span>
			<span class="dg-gift-submit-amount" aria-live="polite">
				<span class="dg-gift-submit-sep"><?php echo esc_html( $form['submit_separator'] ); ?></span>
				<span class="dg-gift-submit-value"><?php echo esc_html( dg_gift_cards_format_price( $default_amount ) ); ?></span>
			</span>
		</button>
	</div>

	<!-- Live region cho feedback (success / error) — JS đổ text vào đây -->
	<p class="dg-gift-feedback" role="status" aria-live="polite"></p>
</form>
