<?php
/**
 * Dragon Glow — Breadcrumb
 * Simple breadcrumb navigation
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$wc = class_exists( 'WooCommerce' );
?>
<nav class="flex items-center gap-2 text-label-sm font-label-sm text-on-surface-variant mb-8 flex-wrap" aria-label="Breadcrumb">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
       class="hover:text-primary transition-colors"
       aria-label="<?php esc_attr_e( 'Home', 'dragon-glow' ); ?>">
        <span class="material-symbols-outlined text-base">home</span>
    </a>

    <?php if ( is_home() && ! is_front_page() ) : ?>
        <span class="text-outline">/</span>
        <span class="text-primary font-bold"><?php single_post_title(); ?></span>

    <?php elseif ( is_front_page() ) : ?>
        <!-- Don't show breadcrumb on front page -->

    <?php elseif ( $wc && is_shop() ) : ?>
        <span class="text-outline">/</span>
        <span class="text-primary font-bold"><?php esc_html_e( 'Shop', 'dragon-glow' ); ?></span>

    <?php elseif ( $wc && is_product_category() ) : ?>
        <span class="text-outline">/</span>
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="hover:text-primary">
            <?php esc_html_e( 'Shop', 'dragon-glow' ); ?>
        </a>
        <span class="text-outline">/</span>
        <span class="text-primary font-bold"><?php single_cat_title(); ?></span>

    <?php elseif ( $wc && is_product() ) : ?>
        <span class="text-outline">/</span>
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="hover:text-primary">
            <?php esc_html_e( 'Shop', 'dragon-glow' ); ?>
        </a>
        <span class="text-outline">/</span>
        <?php
        $primary_category = dg_get_product_primary_category( get_the_ID() );
        if ( $primary_category ) :
        ?>
            <a href="<?php echo esc_url( get_term_link( $primary_category ) ); ?>" class="hover:text-primary">
                <?php echo esc_html( $primary_category->name ); ?>
            </a>
            <span class="text-outline">/</span>
        <?php endif; ?>
        <span class="text-primary font-bold"><?php the_title(); ?></span>

    <?php elseif ( is_page() ) : ?>
        <?php
        global $post;
        if ( $post->post_parent ) :
            $ancestors = get_post_ancestors( $post->ID );
            $ancestors = array_reverse( $ancestors );
            foreach ( $ancestors as $ancestor ) :
        ?>
                <span class="text-outline">/</span>
                <a href="<?php echo esc_url( get_permalink( $ancestor ) ); ?>" class="hover:text-primary">
                    <?php echo esc_html( get_the_title( $ancestor ) ); ?>
                </a>
        <?php
            endforeach;
        endif;
        ?>
        <span class="text-outline">/</span>
        <span class="text-primary font-bold"><?php the_title(); ?></span>

    <?php elseif ( $wc && is_cart() ) : ?>
        <span class="text-outline">/</span>
        <span class="text-primary font-bold"><?php esc_html_e( 'Your Bag', 'dragon-glow' ); ?></span>

    <?php elseif ( $wc && is_checkout() ) : ?>
        <span class="text-outline">/</span>
        <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="hover:text-primary">
            <?php esc_html_e( 'Your Bag', 'dragon-glow' ); ?>
        </a>
        <span class="text-outline">/</span>
        <span class="text-primary font-bold"><?php esc_html_e( 'Checkout', 'dragon-glow' ); ?></span>

    <?php elseif ( $wc && is_account_page() ) : ?>
        <span class="text-outline">/</span>
        <span class="text-primary font-bold"><?php esc_html_e( 'My Account', 'dragon-glow' ); ?></span>

    <?php elseif ( is_single() ) : ?>
        <span class="text-outline">/</span>
        <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="hover:text-primary">
            <?php esc_html_e( 'Journal', 'dragon-glow' ); ?>
        </a>
        <span class="text-outline">/</span>
        <span class="text-primary font-bold"><?php the_title(); ?></span>

    <?php elseif ( is_category() || is_tag() || is_date() || is_author() ) : ?>
        <span class="text-outline">/</span>
        <?php the_archive_title( '<span class="text-primary font-bold">', '</span>' ); ?>

    <?php elseif ( is_search() ) : ?>
        <span class="text-outline">/</span>
        <span class="text-primary font-bold">
            <?php
            printf(
                esc_html__( 'Search: %s', 'dragon-glow' ),
                '<span class="text-on-surface-variant">' . get_search_query() . '</span>'
            );
            ?>
        </span>

    <?php elseif ( is_404() ) : ?>
        <span class="text-outline">/</span>
        <span class="text-primary font-bold"><?php esc_html_e( 'Page Not Found', 'dragon-glow' ); ?></span>

    <?php endif; ?>
</nav>
