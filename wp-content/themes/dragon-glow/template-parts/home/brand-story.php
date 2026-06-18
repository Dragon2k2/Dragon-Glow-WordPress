<?php
/**
 * Dragon Glow — Brand Story Section
 * Philosophy + features + brand image
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$brand_image = get_theme_file_uri( 'assets/images/home/brand-image.webp' );
?>
<section class="py-section-gap relative overflow-hidden">
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">

        <!-- Brand Image -->
        <div class="relative reveal">
            <div class="absolute -top-10 -left-10 w-64 h-64 bg-primary-container/20 rounded-full blur-3xl animate-float"></div>
            <div class="relative z-10 rounded-full overflow-hidden border-4 border-white shadow-2xl aspect-square w-4/5 mx-auto">
                <img class="w-full h-full object-cover"
                     src="<?php echo esc_url( $brand_image ); ?>"
                     alt="<?php esc_attr_e( 'Dragon Glow Philosophy', 'dragon-glow' ); ?>"
                     loading="lazy" />
            </div>
            <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-secondary-container/20 rounded-full blur-3xl animate-float" style="animation-delay: -2s;"></div>
        </div>

        <!-- Content -->
        <div class="reveal">
            <span class="font-label-sm text-label-sm text-tertiary tracking-[0.2em] uppercase mb-4 block"><?php esc_html_e( 'Our Philosophy', 'dragon-glow' ); ?></span>
            <h2 class="font-headline text-headline-lg text-primary mb-8"><?php esc_html_e( 'Why Dragon Glow', 'dragon-glow' ); ?></h2>

            <div class="space-y-8">
                <!-- Feature 1 -->
                <div class="flex gap-6">
                    <div class="bg-primary-container/30 p-4 rounded-2xl h-fit flex-shrink-0">
                        <span class="material-symbols-outlined text-primary text-3xl">science</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-xl mb-2"><?php esc_html_e( 'Scientific Efficacy', 'dragon-glow' ); ?></h4>
                        <p class="text-on-surface-variant"><?php esc_html_e( 'We merge rare botanicals with clinically-proven actives to ensure your results are as powerful as they are beautiful.', 'dragon-glow' ); ?></p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="flex gap-6">
                    <div class="bg-secondary-container/30 p-4 rounded-2xl h-fit flex-shrink-0">
                        <span class="material-symbols-outlined text-secondary text-3xl">psychology_alt</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-xl mb-2"><?php esc_html_e( 'Ritualistic Care', 'dragon-glow' ); ?></h4>
                        <p class="text-on-surface-variant"><?php esc_html_e( "Skincare isn't a chore; it's a sacred ritual of self-love and presence in an ever-accelerating world.", 'dragon-glow' ); ?></p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="flex gap-6">
                    <div class="bg-tertiary-container/30 p-4 rounded-2xl h-fit flex-shrink-0">
                        <span class="material-symbols-outlined text-tertiary text-3xl">self_improvement</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-xl mb-2"><?php esc_html_e( 'Clean Conscience', 'dragon-glow' ); ?></h4>
                        <p class="text-on-surface-variant"><?php esc_html_e( 'Sustainability and ethics are at our core. 100% vegan, cruelty-free, and packaged in recycled glass.', 'dragon-glow' ); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
