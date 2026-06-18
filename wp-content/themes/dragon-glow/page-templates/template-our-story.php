<?php
/**
 * Template Name: Our Story — Dragon Glow
 * Description: Our Story page matching original HTML design
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main>
    <!-- Hero Section -->
    <section class="relative h-screen w-full flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0">
            <img alt="<?php esc_attr_e( 'Serene Botanical Garden', 'dragon-glow' ); ?>"
                 class="w-full h-full object-cover"
                 src="<?php echo esc_url( get_theme_file_uri( 'assets/images/our-story/our-story1.webp' ) ); ?>" />
            <div class="absolute inset-0 bg-black/20"></div>
        </div>
        <div class="relative z-10 text-center px-margin-mobile reveal-on-scroll">
            <p class="text-[#d4af37] mb-6 tracking-[0.3em] uppercase text-[12px] font-semibold leading-none">
                <?php esc_html_e( 'Established in Wisdom', 'dragon-glow' ); ?>
            </p>
            <h1 class="font-serif text-white text-4xl md:text-6xl lg:text-7xl mb-8 max-w-4xl mx-auto leading-tight font-semibold tracking-tight">
                <?php esc_html_e( 'The Essence of Radiance', 'dragon-glow' ); ?>
            </h1>
            <p class="text-white/90 max-w-2xl mx-auto mb-10 text-lg leading-relaxed">
                <?php esc_html_e( 'Blending ancient heritage with clinical precision to unveil your skin\'s natural luminous potential.', 'dragon-glow' ); ?>
            </p>
            <a href="#philosophy" class="inline-block bg-[#d4af37] hover:bg-[#735c00] text-[#554300] hover:text-white px-10 py-4 uppercase tracking-[0.2em] transition-all duration-500 text-sm font-semibold leading-none">
                <?php esc_html_e( 'Watch Our Story', 'dragon-glow' ); ?>
            </a>
        </div>
        <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2">
        </div>
    </section>

    <!-- Our Philosophy -->
    <section id="philosophy" class="py-[120px] px-5 md:px-16 max-w-[1280px] mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-20 items-center">
            <div class="reveal-on-scroll">
                <p class="text-[#735c00] mb-6 uppercase tracking-[0.2em] text-[12px] font-semibold leading-none">
                    <?php esc_html_e( 'Our Philosophy', 'dragon-glow' ); ?>
                </p>
                <h2 class="font-serif text-[#735c00] text-3xl md:text-4xl mb-8 leading-tight">
                    <?php esc_html_e( 'Illumination through ancient botanical science.', 'dragon-glow' ); ?>
                </h2>
                <div class="space-y-6 text-[#4d4635] text-base leading-relaxed">
                    <p><?php esc_html_e( 'At Dragon Glow, we believe radiance is more than a surface quality—it is the outward manifestation of inner health and ancestral resilience. Our journey began in the secluded high-altitude gardens where rare botanicals have thrived for centuries.', 'dragon-glow' ); ?></p>
                    <p><?php esc_html_e( 'We combine these "luminous icons" of the plant world with modern clinical delivery systems, ensuring that every drop honors the heritage of the past while meeting the rigorous demands of today\'s skin science.', 'dragon-glow' ); ?></p>
                </div>
            </div>
            <div class="relative reveal-on-scroll">
                <div class="bg-[#f4f3f1] aspect-[4/5] flex items-center justify-center p-12 text-center shadow-xl">
                    <div>
                        <span class="material-symbols-outlined text-[#d4af37] text-5xl mb-6">auto_awesome</span>
                        <h3 class="font-serif text-[#735c00] text-2xl mb-4 leading-tight">
                            <?php esc_html_e( 'Luminous Heritage', 'dragon-glow' ); ?>
                        </h3>
                        <p class="text-[#4d4635] text-base italic leading-relaxed">
                            "<?php esc_html_e( 'The skin reflects the light we cultivate within. We simply provide the tools to let it shine.', 'dragon-glow' ); ?>"
                        </p>
                    </div>
                </div>
                <div class="absolute -bottom-6 -right-6 w-full h-full border border-[#d4af37]/20 -z-10"></div>
            </div>
        </div>
    </section>

    <!-- The Alchemy -->
    <section class="bg-[#ffffff] py-[120px] px-5 md:px-16">
        <div class="max-w-[1280px] mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="reveal-on-scroll lg:order-2">
                    <p class="text-[#735c00] mb-4 uppercase tracking-[0.2em] text-[12px] font-semibold leading-none">
                        <?php esc_html_e( 'The Alchemy', 'dragon-glow' ); ?>
                    </p>
                    <h2 class="font-serif text-[#735c00] text-3xl md:text-4xl mb-12 leading-tight">
                        <?php esc_html_e( 'Pure. Potent. Proven.', 'dragon-glow' ); ?>
                    </h2>
                    <div class="space-y-10">
                        <div class="flex gap-6 group">
                            <span class="font-serif text-[#d4af37]/30 text-5xl transition-colors group-hover:text-[#d4af37] leading-none">01</span>
                            <div>
                                <h4 class="font-serif text-[#735c00] text-xl mb-2 leading-tight">
                                    <?php esc_html_e( 'Wild-Harvested Jasmine', 'dragon-glow' ); ?>
                                </h4>
                                <p class="text-[#4d4635] text-base leading-relaxed">
                                    <?php esc_html_e( 'Hand-picked at dawn to preserve the delicate essential oils that stimulate cellular renewal and calm inflammation.', 'dragon-glow' ); ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-6 group">
                            <span class="font-serif text-[#d4af37]/30 text-5xl transition-colors group-hover:text-[#d4af37] leading-none">02</span>
                            <div>
                                <h4 class="font-serif text-[#735c00] text-xl mb-2 leading-tight">
                                    <?php esc_html_e( 'Golden Nectar Honey', 'dragon-glow' ); ?>
                                </h4>
                                <p class="text-[#4d4635] text-base leading-relaxed">
                                    <?php esc_html_e( 'A natural humectant that draws moisture deep into the dermis, providing a plump, glass-like finish.', 'dragon-glow' ); ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-6 group">
                            <span class="font-serif text-[#d4af37]/30 text-5xl transition-colors group-hover:text-[#d4af37] leading-none">03</span>
                            <div>
                                <h4 class="font-serif text-[#735c00] text-xl mb-2 leading-tight">
                                    <?php esc_html_e( 'High-Altitude Green Tea', 'dragon-glow' ); ?>
                                </h4>
                                <p class="text-[#4d4635] text-base leading-relaxed">
                                    <?php esc_html_e( 'Loaded with polyphenols that shield the skin from modern environmental oxidative stress.', 'dragon-glow' ); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="reveal-on-scroll lg:order-1">
                    <img alt="<?php esc_attr_e( 'Premium Ingredients', 'dragon-glow' ); ?>"
                         class="w-full h-auto rounded-sm shadow-2xl grayscale-[0.3] hover:grayscale-0 transition-all duration-700"
                         src="<?php echo esc_url( get_theme_file_uri( 'assets/images/our-story/our-story2.png' ) ); ?>" />
                </div>
            </div>
        </div>
    </section>

    <!-- Our Commitment -->
    <section class="py-[120px] px-5 md:px-16 max-w-[1280px] mx-auto">
        <div class="text-center mb-16 reveal-on-scroll">
            <p class="text-[#735c00] mb-4 uppercase tracking-[0.2em] text-[12px] font-semibold leading-none">
                <?php esc_html_e( 'Our Commitment', 'dragon-glow' ); ?>
            </p>
            <h2 class="font-serif text-[#735c00] text-3xl md:text-4xl leading-tight">
                <?php esc_html_e( 'Defining High-End Clean Beauty', 'dragon-glow' ); ?>
            </h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Bento Item 1 -->
            <div class="md:col-span-2 bg-white p-12 flex flex-col justify-center border border-[#e3e2e0] shadow-sm hover:shadow-md transition-shadow reveal-on-scroll">
                <h3 class="font-serif text-[#735c00] text-xl mb-6 leading-tight">
                    <?php esc_html_e( 'Sustainably Sourced', 'dragon-glow' ); ?>
                </h3>
                <p class="text-[#4d4635] mb-8 max-w-lg text-base leading-relaxed">
                    <?php esc_html_e( 'We partner exclusively with family-owned botanical farms that practice regenerative agriculture, ensuring the earth remains as radiant as your skin.', 'dragon-glow' ); ?>
                </p>
                <div class="flex gap-4">
                    <span class="material-symbols-outlined text-[#d4af37]">eco</span>
                    <span class="material-symbols-outlined text-[#d4af37]">psychology_alt</span>
                    <span class="material-symbols-outlined text-[#d4af37]">potted_plant</span>
                </div>
            </div>
            <!-- Bento Item 2 -->
            <div class="bg-[#705a00] p-12 flex flex-col justify-center text-white reveal-on-scroll">
                <h3 class="font-serif text-xl mb-6 leading-tight">
                    <?php esc_html_e( 'Clinical Trust', 'dragon-glow' ); ?>
                </h3>
                <p class="text-white/80 text-base leading-relaxed">
                    <?php esc_html_e( 'Every formula undergoes three phases of dermatological testing to guarantee safety for sensitive complexions.', 'dragon-glow' ); ?>
                </p>
            </div>
            <!-- Bento Item 3 -->
            <div class="bg-[#f4f3f1] p-12 flex flex-col justify-center reveal-on-scroll">
                <h3 class="font-serif text-[#735c00] text-xl mb-6 leading-tight">
                    <?php esc_html_e( 'Cruelty-Free', 'dragon-glow' ); ?>
                </h3>
                <p class="text-[#4d4635] text-base leading-relaxed">
                    <?php esc_html_e( 'Beauty should never come at a cost to others. We are proudly certified vegan and cruelty-free.', 'dragon-glow' ); ?>
                </p>
            </div>
            <!-- Bento Item 4 -->
            <div class="md:col-span-2 bg-[#fdfcf9] p-12 flex flex-col justify-center border border-[#e3e2e0] reveal-on-scroll">
                <h3 class="font-serif text-[#735c00] text-xl mb-6 leading-tight">
                    <?php esc_html_e( 'Minimalist Luxury', 'dragon-glow' ); ?>
                </h3>
                <p class="text-[#4d4635] max-w-lg text-base leading-relaxed">
                    <?php esc_html_e( 'We eliminate unnecessary fillers to prioritize high concentrations of active ingredients, resulting in more potent rituals and less waste.', 'dragon-glow' ); ?>
                </p>
            </div>
        </div>
    </section>
</main>

<script>
(function() {
    // Reveal elements on scroll
    var observerOptions = {
        threshold: 0.15,
        rootMargin: '0px'
    };

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal-on-scroll').forEach(function(el) {
        observer.observe(el);
    });

    // Newsletter form submission
    var form = document.getElementById('dg-newsletter-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            var btn = form.querySelector('button[type="submit"]');
            var originalText = btn.textContent;
            btn.textContent = '<?php esc_html_e( 'Subscribing...', 'dragon-glow' ); ?>';
            btn.disabled = true;

            var formData = new FormData(form);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                var msg = document.getElementById('dg-newsletter-msg');
                msg.classList.remove('hidden', 'text-red-600', 'text-[#735c00]');

                if (data.success) {
                    msg.classList.add('text-[#735c00]');
                    msg.textContent = data.data.message || '<?php esc_html_e( 'Thank you for subscribing!', 'dragon-glow' ); ?>';
                    form.reset();

                    setTimeout(function() {
                        msg.classList.add('hidden');
                    }, 5000);
                } else {
                    msg.classList.add('text-red-600');
                    msg.textContent = data.data.message || '<?php esc_html_e( 'Something went wrong.', 'dragon-glow' ); ?>';
                }

                btn.textContent = originalText;
                btn.disabled = false;
            })
            .catch(function(error) {
                console.error('Error:', error);
                var msg = document.getElementById('dg-newsletter-msg');
                msg.classList.remove('hidden', 'text-[#735c00]');
                msg.classList.add('text-red-600');
                msg.textContent = '<?php esc_html_e( 'Something went wrong.', 'dragon-glow' ); ?>';
                btn.textContent = originalText;
                btn.disabled = false;
            });
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            var targetId = this.getAttribute('href');
            if (targetId && targetId !== '#') {
                e.preventDefault();
                var target = document.querySelector(targetId);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
})();
</script>

<?php get_footer(); ?>
