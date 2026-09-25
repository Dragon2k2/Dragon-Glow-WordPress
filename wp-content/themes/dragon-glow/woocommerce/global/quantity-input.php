<?php
/**
 * Dragon Glow — Quantity Input with +/- Buttons
 * Override WooCommerce global/quantity-input.php
 * Luxury glassmorphism stepper design with Material Symbols icons.
 *
 * @package Dragon_Glow
 * @version 9.0.0
 */

defined( 'ABSPATH' ) || exit;

/* translators: %s: Quantity. */
$label = ! empty( $args['product_name'] ) ? sprintf( esc_html__( '%s quantity', 'woocommerce' ), wp_strip_all_tags( $args['product_name'] ) ) : esc_html__( 'Quantity', 'woocommerce' );

// Defaults
$defaults = array(
	'input_id'     => uniqid( 'quantity_' ),
	'input_name'   => 'quantity',
	'input_value'  => '1',
	'classes'      => apply_filters( 'woocommerce_quantity_input_classes', array( 'input-text', 'qty', 'text' ), null ),
	'max_value'    => apply_filters( 'woocommerce_quantity_input_max', -1, null ),
	'min_value'    => apply_filters( 'woocommerce_quantity_input_min', 0, null ),
	'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, null ),
	'pattern'      => apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ),
	'inputmode'    => apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' ),
	'product_name' => '',
	'placeholder'  => apply_filters( 'woocommerce_quantity_input_placeholder', '', null ),
	'readonly'     => false,
	'type'         => 'number',
);

$args = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $args, $defaults ), null );

// Apply sanity to min/max args
$args['min_value'] = max( $args['min_value'], 0 );
$args['max_value'] = 0 < $args['max_value'] ? $args['max_value'] : '';

if ( '' !== $args['max_value'] && $args['max_value'] < $args['min_value'] ) {
	$args['max_value'] = $args['min_value'];
}

$classes = array_map( 'esc_attr', (array) $args['classes'] );
?>

<div class="quantity dg-quantity-stepper">
	<?php do_action( 'woocommerce_before_quantity_input_field' ); ?>
	
	<!-- Minus button -->
	<button type="button" 
	        class="dg-qty-btn dg-qty-minus" 
	        aria-label="<?php esc_attr_e( 'Decrease quantity', 'dragon-glow' ); ?>"
	        data-qty-action="minus">
		<span class="material-symbols-outlined" aria-hidden="true">remove</span>
	</button>
	
	<!-- Input field -->
	<label class="screen-reader-text" for="<?php echo esc_attr( $args['input_id'] ); ?>">
		<?php echo esc_html( $label ); ?>
	</label>
	<input
		type="<?php echo esc_attr( $args['type'] ); ?>"
		<?php echo $args['readonly'] ? 'readonly="readonly"' : ''; ?>
		id="<?php echo esc_attr( $args['input_id'] ); ?>"
		class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
		name="<?php echo esc_attr( $args['input_name'] ); ?>"
		value="<?php echo esc_attr( $args['input_value'] ); ?>"
		aria-label="<?php esc_attr_e( 'Product quantity', 'dragon-glow' ); ?>"
		min="<?php echo esc_attr( $args['min_value'] ); ?>"
		max="<?php echo esc_attr( 0 < $args['max_value'] ? $args['max_value'] : '' ); ?>"
		<?php if ( ! empty( $args['step'] ) ) : ?>
			step="<?php echo esc_attr( $args['step'] ); ?>"
		<?php endif; ?>
		placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"
		inputmode="<?php echo esc_attr( $args['inputmode'] ); ?>"
		autocomplete="off"
		<?php if ( ! empty( $args['pattern'] ) ) : ?>
			pattern="<?php echo esc_attr( $args['pattern'] ); ?>"
		<?php endif; ?>
	/>
	
	<!-- Plus button -->
	<button type="button" 
	        class="dg-qty-btn dg-qty-plus" 
	        aria-label="<?php esc_attr_e( 'Increase quantity', 'dragon-glow' ); ?>"
	        data-qty-action="plus">
		<span class="material-symbols-outlined" aria-hidden="true">add</span>
	</button>
	
	<?php do_action( 'woocommerce_after_quantity_input_field' ); ?>
</div>

