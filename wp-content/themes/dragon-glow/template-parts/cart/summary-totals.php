<?php
/**
 * Cart subtotal, discounts, shipping and fees shared by the page and AJAX.
 *
 * @package Dragon_Glow
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="flex justify-between items-center text-on-surface-variant font-body-md text-sm">
    <span><?php esc_html_e( 'Subtotal', 'dragon-glow' ); ?></span>
    <span class="font-medium text-on-surface" id="dg-cart-subtotal" data-qty-pricing="<?php echo esc_attr( wp_json_encode( dg_cart_quantity_pricing() ) ); ?>"><?php wc_cart_totals_subtotal_html(); ?></span>
</div>
<?php foreach ( WC()->cart->get_coupons() as $coupon ) : ?>
<div class="flex justify-between items-center text-sm">
    <span class="text-on-surface-variant"><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
    <span class="text-primary font-semibold"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
</div>
<?php endforeach; ?>
<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
    <?php wc_cart_totals_shipping_html(); ?>
<?php else : ?>
<div class="flex justify-between items-center text-sm italic">
    <span class="text-on-surface-variant"><?php esc_html_e( 'Shipping', 'dragon-glow' ); ?></span>
    <span class="text-on-surface-variant opacity-70"><?php esc_html_e( 'Calculated at next step', 'dragon-glow' ); ?></span>
</div>
<?php endif; ?>
<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
<div class="flex justify-between items-center text-sm">
    <span class="text-on-surface-variant"><?php echo esc_html( $fee->name ); ?></span>
    <span><?php wc_cart_totals_fee_html( $fee ); ?></span>
</div>
<?php endforeach; ?>
