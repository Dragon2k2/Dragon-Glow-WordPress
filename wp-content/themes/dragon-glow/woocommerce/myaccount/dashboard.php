<?php
/**
 * Dragon Glow — Account Dashboard
 * Override: woocommerce/myaccount/dashboard.php
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$customer = wp_get_current_user();
?>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
    <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>

    <div class="mb-8">
        <h1 class="font-headline text-headline-lg text-primary mb-2">
            <?php
            printf(
                esc_html__( 'Hello, %s!', 'dragon-glow' ),
                esc_html( $customer->display_name )
            );
            ?>
        </h1>
        <p class="text-on-surface-variant">
            <?php esc_html_e( 'Welcome back to your luminous skincare journey.', 'dragon-glow' ); ?>
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Quick Links -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Recent Orders -->
            <div class="glass-card rounded-3xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-headline text-xl text-primary">
                        <?php esc_html_e( 'Recent Orders', 'dragon-glow' ); ?>
                    </h2>
                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="text-primary hover:underline text-sm font-medium">
                        <?php esc_html_e( 'View All', 'dragon-glow' ); ?>
                    </a>
                </div>

                <?php
                $orders = wc_get_orders( array(
                    'customer' => get_current_user_id(),
                    'limit'    => 3,
                    'orderby'  => 'date',
                    'order'    => 'DESC',
                ) );

                if ( $orders ) :
                ?>
                <div class="space-y-4">
                    <?php foreach ( $orders as $order ) : ?>
                    <div class="flex items-center justify-between p-4 bg-surface rounded-xl">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary">receipt_long</span>
                            </div>
                            <div>
                                <p class="font-medium">
                                    <?php printf( esc_html__( 'Order #%s', 'dragon-glow' ), esc_html( $order->get_order_number() ) ); ?>
                                </p>
                                <p class="text-sm text-on-surface-variant">
                                    <?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-primary"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></p>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium <?php echo 'completed' === $order->get_status() ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                <div class="text-center py-8">
                    <p class="text-on-surface-variant mb-4">
                        <?php esc_html_e( 'You haven\'t placed any orders yet.', 'dragon-glow' ); ?>
                    </p>
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn-primary inline-block">
                        <?php esc_html_e( 'Start Shopping', 'dragon-glow' ); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Wishlist Preview -->
            <?php
            $wishlist = dg_get_wishlist();
            if ( ! empty( $wishlist ) ) :
            ?>
            <div class="glass-card rounded-3xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-headline text-xl text-primary">
                        <?php esc_html_e( 'Your Wishlist', 'dragon-glow' ); ?>
                    </h2>
                    <a href="<?php echo esc_url( get_permalink( get_option( 'dg_wishlist_page_id' ) ) ); ?>" class="text-primary hover:underline text-sm font-medium">
                        <?php esc_html_e( 'View All', 'dragon-glow' ); ?>
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <?php
                    $wishlist_products = array_slice( $wishlist, 0, 3 );
                    foreach ( $wishlist_products as $product_id ) :
                        $product = wc_get_product( $product_id );
                        if ( ! $product ) {
                            continue;
                        }
                    ?>
                    <a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" class="group">
                        <div class="aspect-square rounded-xl overflow-hidden mb-2">
                            <?php echo get_the_post_thumbnail( $product_id, 'medium', array( 'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform' ) ); ?>
                        </div>
                        <p class="text-sm font-medium truncate"><?php echo esc_html( $product->get_name() ); ?></p>
                        <p class="text-sm text-primary font-bold"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Account Details -->
            <div class="glass-card rounded-3xl p-6">
                <h2 class="font-headline text-xl text-primary mb-6">
                    <?php esc_html_e( 'Account Details', 'dragon-glow' ); ?>
                </h2>

                <div class="space-y-4">
                    <div>
                        <p class="text-label-sm text-on-surface-variant"><?php esc_html_e( 'Name', 'dragon-glow' ); ?></p>
                        <p class="font-medium"><?php echo esc_html( $customer->display_name ); ?></p>
                    </div>
                    <div>
                        <p class="text-label-sm text-on-surface-variant"><?php esc_html_e( 'Email', 'dragon-glow' ); ?></p>
                        <p class="font-medium"><?php echo esc_html( $customer->user_email ); ?></p>
                    </div>
                </div>

                <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>" class="btn-ghost w-full mt-6 text-center">
                    <?php esc_html_e( 'Edit Profile', 'dragon-glow' ); ?>
                </a>
            </div>

            <!-- Quick Links -->
            <div class="glass-card rounded-3xl p-6">
                <h2 class="font-headline text-xl text-primary mb-6">
                    <?php esc_html_e( 'Quick Links', 'dragon-glow' ); ?>
                </h2>

                <nav class="space-y-2">
                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="flex items-center gap-3 p-3 rounded-xl hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-primary">receipt_long</span>
                        <?php esc_html_e( 'My Orders', 'dragon-glow' ); ?>
                    </a>
                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>" class="flex items-center gap-3 p-3 rounded-xl hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-primary">home</span>
                        <?php esc_html_e( 'Addresses', 'dragon-glow' ); ?>
                    </a>
                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>" class="flex items-center gap-3 p-3 rounded-xl hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-primary">person</span>
                        <?php esc_html_e( 'Account Details', 'dragon-glow' ); ?>
                    </a>
                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'customer-logout' ) ); ?>" class="flex items-center gap-3 p-3 rounded-xl hover:bg-surface-container transition-colors text-error">
                        <span class="material-symbols-outlined">logout</span>
                        <?php esc_html_e( 'Sign Out', 'dragon-glow' ); ?>
                    </a>
                </nav>
            </div>
        </div>
    </div>
</div>
