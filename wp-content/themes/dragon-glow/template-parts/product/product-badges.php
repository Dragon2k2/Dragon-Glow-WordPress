<?php
/**
 * Dragon Glow — Product Badges
 * Render badges from product attributes
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product ) {
    return;
}

$badges = array();

// Featured/New badge
if ( $product->is_featured() ) {
    $badges[] = array(
        'class' => 'badge-new',
        'text'  => __( 'New', 'dragon-glow' ),
    );
}

// Bestseller badge
if ( $product->get_attribute( 'bestseller' ) ) {
    $badges[] = array(
        'class' => 'badge-bestseller',
        'text'  => __( 'Bestseller', 'dragon-glow' ),
    );
}

// Sale badge
if ( $product->is_on_sale() ) {
    $badges[] = array(
        'class' => 'badge-new',
        'text'  => __( 'Sale', 'dragon-glow' ),
    );
}

// Vegan badge
if ( $product->get_attribute( 'vegan' ) ) {
    $badges[] = array(
        'class' => 'badge-vegan',
        'text'  => __( 'Vegan', 'dragon-glow' ),
    );
}

// Limited edition badge
if ( $product->get_attribute( 'limited_edition' ) ) {
    $badges[] = array(
        'class' => 'badge-limited',
        'text'  => __( 'Limited Edition', 'dragon-glow' ),
    );
}

if ( empty( $badges ) ) {
    return;
}
?>
<div class="flex flex-wrap gap-2 mb-4">
    <?php foreach ( $badges as $badge ) : ?>
        <span class="<?php echo esc_attr( $badge['class'] ); ?>">
            <?php echo esc_html( $badge['text'] ); ?>
        </span>
    <?php endforeach; ?>
</div>
