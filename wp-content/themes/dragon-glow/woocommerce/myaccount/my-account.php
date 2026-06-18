<?php
/**
 * Dragon Glow — My Account
 * Override: woocommerce/myaccount/my-account.php
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_account_navigation' );
?>

<div class="woocommerce-MyAccount-content max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
    <?php
    /**
     * My Account content.
     * @hooked woocommerce_account_content()
     */
    do_action( 'woocommerce_account_content' );
    ?>
</div>
