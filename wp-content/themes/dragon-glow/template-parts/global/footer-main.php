<?php
/**
 * Dragon Glow — Global Footer
 * Gradient background, columns matching original HTML structure
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$shop_url = class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : '#';
$about_url = get_permalink( get_page_by_path( 'our-story' ) ) ?: '#';
$contact_url = get_permalink( get_page_by_path( 'contact' ) ) ?: '#';
?>
<footer class="bg-gradient-to-br from-[#f4c2c2] via-[#e1e1f5] to-[#e1e1f5] mt-section-gap pt-section-gap flat no-shadows">
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 flex flex-wrap justify-between gap-gutter">

        <!-- Brand Column -->
        <div class="w-full lg:w-1/3 mb-12 lg:mb-0">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
               class="font-headline text-headline-md text-primary mb-6 block font-bold hover:opacity-80 transition-opacity">
                <?php bloginfo( 'name' ); ?>
            </a>
            <p class="text-on-secondary-container mb-8 max-w-sm">
                <?php
                $description = get_bloginfo( 'description' );
                echo esc_html( $description ?: __( 'Ethereal rituals for the modern soul. Experience the transformative power of nature\'s most luminous ingredients.', 'dragon-glow' ) );
                ?>
            </p>
            <!-- Social Icons -->
            <div class="flex gap-4">
                <?php
                $socials = dg_get_social_links();
                foreach ( $socials as $social ) :
                    if ( '#' !== $social['url'] ) :
                    ?>
                    <a href="<?php echo esc_url( $social['url'] ); ?>"
                       class="w-10 h-10 rounded-full bg-white/40 flex items-center justify-center hover:bg-white transition-all text-primary"
                       rel="noopener noreferrer"
                       target="_blank"
                       aria-label="<?php echo esc_attr( $social['label'] ); ?>">
                        <span class="material-symbols-outlined text-xl"><?php echo esc_html( $social['icon'] ); ?></span>
                    </a>
                    <?php
                    endif;
                endforeach;
                ?>
            </div>
        </div>

        <!-- Shop Column -->
        <div class="w-full md:w-1/4 lg:w-1/6">
            <h5 class="font-bold text-on-primary-container mb-6"><?php esc_html_e( 'Shop', 'dragon-glow' ); ?></h5>
            <ul class="space-y-4 text-sm text-on-secondary-container">
                <li><a href="<?php echo esc_url( $shop_url ); ?>" class="hover:text-primary transition-all"><?php esc_html_e( 'All Products', 'dragon-glow' ); ?></a></li>
                <li><a href="#" class="hover:text-primary transition-all"><?php esc_html_e( 'New Arrivals', 'dragon-glow' ); ?></a></li>
                <li><a href="#" class="hover:text-primary transition-all"><?php esc_html_e( 'Best Sellers', 'dragon-glow' ); ?></a></li>
                <li><a href="#" class="hover:text-primary transition-all"><?php esc_html_e( 'Skin Quiz', 'dragon-glow' ); ?></a></li>
            </ul>
        </div>

        <!-- Company Column -->
        <div class="w-full md:w-1/4 lg:w-1/6">
            <h5 class="font-bold text-on-primary-container mb-6"><?php esc_html_e( 'Company', 'dragon-glow' ); ?></h5>
            <ul class="space-y-4 text-sm text-on-secondary-container">
                <li><a href="<?php echo esc_url( $about_url ); ?>" class="hover:text-primary transition-all"><?php esc_html_e( 'About Us', 'dragon-glow' ); ?></a></li>
                <li><a href="#" class="hover:text-primary transition-all"><?php esc_html_e( 'Ritual Journal', 'dragon-glow' ); ?></a></li>
                <li><a href="#" class="hover:text-primary transition-all"><?php esc_html_e( 'Sustainability', 'dragon-glow' ); ?></a></li>
                <li><a href="#" class="hover:text-primary transition-all"><?php esc_html_e( 'Careers', 'dragon-glow' ); ?></a></li>
            </ul>
        </div>

        <!-- Help Column -->
        <div class="w-full md:w-1/4 lg:w-1/6">
            <h5 class="font-bold text-on-primary-container mb-6"><?php esc_html_e( 'Help', 'dragon-glow' ); ?></h5>
            <ul class="space-y-4 text-sm text-on-secondary-container">
                <?php if ( function_exists( 'the_privacy_policy_link' ) ) : ?>
                    <li><?php the_privacy_policy_link( '', '' ); ?></li>
                <?php endif; ?>
                <?php
                $tos_page = get_page_by_path( 'terms-of-service' );
                if ( $tos_page ) :
                ?>
                <li><a href="<?php echo esc_url( get_permalink( $tos_page ) ); ?>" class="hover:text-primary transition-all"><?php esc_html_e( 'Terms of Service', 'dragon-glow' ); ?></a></li>
                <?php endif; ?>
                <li><a href="#" class="hover:text-primary transition-all"><?php esc_html_e( 'Shipping & Returns', 'dragon-glow' ); ?></a></li>
                <li><a href="<?php echo esc_url( $contact_url ); ?>" class="hover:text-primary transition-all"><?php esc_html_e( 'Contact Us', 'dragon-glow' ); ?></a></li>
            </ul>
        </div>
    </div>

    <!-- Bottom bar -->
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-8 border-t border-white/20 flex flex-col md:flex-row justify-between items-center text-xs text-on-secondary-container/80">
        <p>&copy; <?php dg_copyright_year(); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'dragon-glow' ); ?></p>
        <div class="flex gap-6 mt-4 md:mt-0">
            <span><?php esc_html_e( 'English (USD)', 'dragon-glow' ); ?></span>
            <span>
                <?php esc_html_e( 'Payment:', 'dragon-glow' ); ?>
                <span class="material-symbols-outlined align-middle ml-1">account_balance_wallet</span>
                <span class="material-symbols-outlined align-middle ml-1">credit_card</span>
            </span>
        </div>
    </div>
</footer>
