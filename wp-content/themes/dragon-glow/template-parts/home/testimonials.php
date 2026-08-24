<?php
/**
 * Dragon Glow — Testimonials Section
 * 3 testimonial cards with customer reviews
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Testimonial data from original HTML
$testimonials = array(
    array(
        'name'    => 'Elena V.',
        'role'    => 'Verified Buyer',
        'rating'  => 5,
        'content' => __( 'The Nectarine Glow Serum has completely transformed my morning routine. I look like I\'ve slept 10 hours even when I\'ve only had 4. Pure magic!', 'dragon-glow' ),
        'avatar'  => get_theme_file_uri( 'assets/images/home/reviewer-01.webp' ),
    ),
    array(
        'name'    => 'Marcus R.',
        'role'    => 'Verified Buyer',
        'rating'  => 5,
        'content' => __( 'I have sensitive skin, and the Cloud Whipped Cream is the only thing that calms my redness instantly. The packaging is absolutely stunning too.', 'dragon-glow' ),
        'avatar'  => get_theme_file_uri( 'assets/images/home/reviewer-02.webp' ),
    ),
    array(
        'name'    => 'Sarah J.',
        'role'    => 'Verified Buyer',
        'rating'  => 5,
        'content' => __( 'Finally a luxury brand that values ethics as much as results. Dragon Glow is now my only skincare choice. My skin has never looked better.', 'dragon-glow' ),
        'avatar'  => get_theme_file_uri( 'assets/images/home/reviewer-03.webp' ),
    ),
);
?>
<section class="py-section-gap bg-gradient-to-br from-primary-container/20 to-secondary-container/20">
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center mb-16 reveal">
            <h2 class="font-headline text-headline-lg text-primary"><?php esc_html_e( 'Voices of the Glow', 'dragon-glow' ); ?></h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ( $testimonials as $index => $testimonial ) : ?>
            <div class="glass-card p-10 rounded-[2rem] reveal" style="transition-delay: <?php echo esc_attr( $index * 100 ); ?>ms;">
                <!-- Star Rating -->
                <div class="flex gap-1 text-tertiary mb-6">
                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                    <?php endfor; ?>
                </div>

                <!-- Quote -->
                <p class="font-body text-body-lg text-on-surface-variant italic mb-8 leading-relaxed">
                    "<?php echo esc_html( $testimonial['content'] ); ?>"
                </p>

                <!-- Author -->
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0">
                        <img class="w-full h-full object-cover"
                             src="<?php echo esc_url( $testimonial['avatar'] ); ?>"
                             alt="<?php echo esc_attr( $testimonial['name'] ); ?>"
                             loading="lazy" />
                    </div>
                    <div>
                        <p class="font-bold text-primary"><?php echo esc_html( $testimonial['name'] ); ?></p>
                        <p class="text-xs text-on-surface-variant"><?php echo esc_html( $testimonial['role'] ); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
