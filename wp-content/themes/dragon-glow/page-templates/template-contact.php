<?php
/**
 * Template Name: Contact Us — Dragon Glow
 * Description: Contact page matching original HTML design
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="pt-32 pb-24" id="main-content">
    <!-- Hero Section -->
    <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop mb-24 text-center">
        <h1 class="font-display-lg text-display-lg mb-6 text-glow">
            <?php esc_html_e( 'How can we help you glow?', 'dragon-glow' ); ?>
        </h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto leading-relaxed">
            <?php esc_html_e( 'Whether you have questions about our botanical rituals, need ingredient guidance, or want to discuss a recent order, our concierges are here to assist with illuminated care.', 'dragon-glow' ); ?>
        </p>
    </section>

    <!-- Centered Contact Form & Visual -->
    <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop grid grid-cols-1 lg:grid-cols-12 gap-16 items-start">

        <!-- Product Visual (Left Column) -->
        <div class="lg:col-span-5 order-2 lg:order-1 flex flex-col justify-center items-center">
            <div class="relative group w-full aspect-[4/5] bg-surface-container-low flex items-center justify-center overflow-hidden">
                <img alt="<?php esc_attr_e( 'Dragon Glow Essence Serum', 'dragon-glow' ); ?>"
                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                     id="contact-parallax-img"
                     src="<?php echo esc_url( get_theme_file_uri( 'assets/images/contact/contact.webp' ) ); ?>" />
                <div class="absolute inset-0 border-[0.5px] border-outline-variant/50 m-6 pointer-events-none"></div>
            </div>
            <div class="mt-12 text-center">
                <span class="font-label-md text-label-md text-primary uppercase tracking-[0.2em] mb-4 block">
                    <?php esc_html_e( 'Concierge Hours', 'dragon-glow' ); ?>
                </span>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    <?php esc_html_e( 'Monday — Friday: 9am - 6pm EST', 'dragon-glow' ); ?>
                </p>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    <?php esc_html_e( 'Saturday: 10am - 4pm EST', 'dragon-glow' ); ?>
                </p>
            </div>
        </div>

        <!-- Contact Form (Right Column) -->
        <div class="lg:col-span-7 order-1 lg:order-2">
            <div class="bg-surface-container-lowest p-10 md:p-16 border border-outline-variant/20 shadow-sm shadow-primary/5">
                <form class="space-y-12" id="dg-contact-form">
                    <?php wp_nonce_field( 'dg_contact_nonce', 'dg_nonce_field' ); ?>
                    <input type="hidden" name="action" value="dg_contact_form" />

                    <!-- First Name & Last Name -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-12">
                        <div class="relative input-focus-line group">
                            <input class="peer block w-full border-0 border-b border-outline-variant/40 bg-transparent py-4 focus:ring-0 focus:border-primary text-on-surface placeholder-transparent transition-all"
                                  id="first_name"
                                  name="first_name"
                                  placeholder=" "
                                  type="text"
                                  required />
                            <label class="absolute left-0 top-4 text-on-surface-variant/60 font-label-md uppercase tracking-widest pointer-events-none transition-all duration-300 transform -translate-y-6 scale-75 text-primary"
                                   for="first_name">
                                <?php esc_html_e( 'First Name', 'dragon-glow' ); ?>
                            </label>
                        </div>
                        <div class="relative input-focus-line group">
                            <input class="peer block w-full border-0 border-b border-outline-variant/40 bg-transparent py-4 focus:ring-0 focus:border-primary text-on-surface placeholder-transparent transition-all"
                                  id="last_name"
                                  name="last_name"
                                  placeholder=" "
                                  type="text"
                                  required />
                            <label class="absolute left-0 top-4 text-on-surface-variant/60 font-label-md uppercase tracking-widest pointer-events-none transition-all duration-300 transform -translate-y-6 scale-75 text-primary"
                                   for="last_name">
                                <?php esc_html_e( 'Last Name', 'dragon-glow' ); ?>
                            </label>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="relative input-focus-line group">
                        <input class="peer block w-full border-0 border-b border-outline-variant/40 bg-transparent py-4 focus:ring-0 focus:border-primary text-on-surface placeholder-transparent transition-all"
                              id="contact-email"
                              name="email"
                              placeholder=" "
                              type="email"
                              required />
                        <label class="absolute left-0 top-4 text-on-surface-variant/60 font-label-md uppercase tracking-widest pointer-events-none transition-all duration-300 transform -translate-y-6 scale-75 text-primary"
                               for="contact-email">
                            <?php esc_html_e( 'Email Address', 'dragon-glow' ); ?>
                        </label>
                    </div>

                    <!-- Subject Dropdown -->
                    <div class="relative input-focus-line group">
                        <select class="peer block w-full border-0 border-b border-outline-variant/40 bg-transparent py-4 focus:ring-0 focus:border-primary text-on-surface transition-all appearance-none cursor-pointer"
                                id="contact-subject"
                                name="subject"
                                required>
                            <option value="" disabled selected>
                                <?php esc_html_e( 'Select Inquiry Type', 'dragon-glow' ); ?>
                            </option>
                            <option value="orders">
                                <?php esc_html_e( 'Order Inquiry', 'dragon-glow' ); ?>
                            </option>
                            <option value="products">
                                <?php esc_html_e( 'Product Consultation', 'dragon-glow' ); ?>
                            </option>
                            <option value="wholesale">
                                <?php esc_html_e( 'Wholesale & Stockists', 'dragon-glow' ); ?>
                            </option>
                            <option value="press">
                                <?php esc_html_e( 'Press & Media', 'dragon-glow' ); ?>
                            </option>
                            <option value="other">
                                <?php esc_html_e( 'Other', 'dragon-glow' ); ?>
                            </option>
                        </select>
                        <span class="material-symbols-outlined absolute right-0 top-4 text-on-surface-variant pointer-events-none">
                            expand_more
                        </span>
                    </div>

                    <!-- Message -->
                    <div class="relative input-focus-line group">
                        <textarea class="peer block w-full border-0 border-b border-outline-variant/40 bg-transparent py-4 focus:ring-0 focus:border-primary text-on-surface placeholder-transparent transition-all resize-none"
                                 id="contact-message"
                                 name="message"
                                 placeholder=" "
                                 rows="4"
                                 required></textarea>
                        <label class="absolute left-0 top-4 text-on-surface-variant/60 font-label-md uppercase tracking-widest pointer-events-none transition-all duration-300 transform -translate-y-6 scale-75 text-primary"
                               for="contact-message">
                            <?php esc_html_e( 'Your Message', 'dragon-glow' ); ?>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-8">
                        <button type="submit"
                                id="dg-submit-btn"
                                class="w-full bg-primary text-on-primary font-label-md text-label-md uppercase tracking-[0.3em] py-6 px-12 transition-all duration-300 hover:opacity-90 hover:shadow-xl hover:shadow-primary/20 active:scale-[0.98]">
                            <?php esc_html_e( 'Send Message', 'dragon-glow' ); ?>
                        </button>
                    </div>

                    <p id="dg-contact-msg" class="mt-4 text-center hidden font-body-md"></p>
                </form>
            </div>
        </div>
    </section>

    <!-- Social & Direct Contact -->
    <section class="mt-32 max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop grid grid-cols-1 md:grid-cols-3 gap-12 text-center border-t border-outline-variant/30 pt-16">

        <!-- Email -->
        <div class="flex flex-col items-center">
            <span class="material-symbols-outlined text-primary text-3xl mb-4">mail</span>
            <h3 class="font-headline-md text-headline-md mb-2">
                <?php esc_html_e( 'Email Us', 'dragon-glow' ); ?>
            </h3>
            <p class="font-body-md text-body-md text-on-surface-variant">
                <?php esc_html_e( 'concierge@dragonglow.com', 'dragon-glow' ); ?>
            </p>
        </div>

        <!-- Phone -->
        <div class="flex flex-col items-center">
            <span class="material-symbols-outlined text-primary text-3xl mb-4">phone_iphone</span>
            <h3 class="font-headline-md text-headline-md mb-2">
                <?php esc_html_e( 'Call Us', 'dragon-glow' ); ?>
            </h3>
            <p class="font-body-md text-body-md text-on-surface-variant">
                <?php esc_html_e( '+1 (800) GLOW-NOW', 'dragon-glow' ); ?>
            </p>
        </div>

        <!-- Location -->
        <div class="flex flex-col items-center">
            <span class="material-symbols-outlined text-primary text-3xl mb-4">location_on</span>
            <h3 class="font-headline-md text-headline-md mb-2">
                <?php esc_html_e( 'Visit Us', 'dragon-glow' ); ?>
            </h3>
            <p class="font-body-md text-body-md text-on-surface-variant">
                <?php esc_html_e( '42 Ritual Way, New York, NY', 'dragon-glow' ); ?>
            </p>
        </div>
    </section>
</main>

<style>
    /* Text glow effect */
    .text-glow {
        text-shadow: 0 0 15px rgba(119, 90, 25, 0.1);
    }

    /* Input focus line animation */
    .input-focus-line {
        position: relative;
    }
    .input-focus-line::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 1px;
        background: #775a19;
        transition: all 0.4s ease;
        transform: translateX(-50%);
    }
    .input-focus-line:focus-within::after {
        width: 100%;
    }

    /* Floating label styles */
    .input-focus-line:focus-within label,
    .input-focus-line:not(:placeholder-shown) label {
        transform: translateY(-24px) scale(0.85) !important;
        color: #775a19 !important;
    }
</style>

<script>
(function() {
    // Contact form submission with states
    var form = document.getElementById('dg-contact-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            var btn = document.getElementById('dg-submit-btn');
            var originalText = btn.innerHTML;
            var msg = document.getElementById('dg-contact-msg');

            // Loading state
            btn.innerHTML = '<?php esc_html_e( 'Transmitting...', 'dragon-glow' ); ?>';
            btn.disabled = true;
            btn.classList.add('opacity-50');

            var formData = new FormData(form);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                // Simulate delay for UX
                setTimeout(function() {
                    if (data.success) {
                        // Success state
                        btn.innerHTML = '<?php esc_html_e( 'Message Received', 'dragon-glow' ); ?>';
                        btn.classList.remove('opacity-50', 'bg-primary');
                        btn.classList.add('bg-secondary', 'text-on-secondary');

                        msg.classList.remove('hidden', 'text-error');
                        msg.classList.add('text-tertiary');
                        msg.textContent = data.data.message || '<?php esc_html_e( 'Thank you! We\'ll be in touch soon.', 'dragon-glow' ); ?>';

                        // Reset after 3 seconds
                        setTimeout(function() {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                            btn.classList.remove('opacity-50', 'bg-secondary', 'text-on-secondary');
                            btn.classList.add('bg-primary', 'text-on-primary');
                            msg.classList.add('hidden');
                            form.reset();
                        }, 3000);
                    } else {
                        // Error state
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        btn.classList.remove('opacity-50');

                        msg.classList.remove('hidden', 'text-tertiary');
                        msg.classList.add('text-error');
                        msg.textContent = data.data || '<?php esc_html_e( 'Something went wrong. Please try again.', 'dragon-glow' ); ?>';
                    }
                }, 1500);
            })
            .catch(function(error) {
                console.error('Error:', error);
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.classList.remove('opacity-50');

                msg.classList.remove('hidden', 'text-tertiary');
                msg.classList.add('text-error');
                msg.textContent = '<?php esc_html_e( 'Something went wrong. Please try again.', 'dragon-glow' ); ?>';
            });
        });
    }

    // Parallax effect on scroll
    var parallaxImg = document.getElementById('contact-parallax-img');
    if (parallaxImg) {
        window.addEventListener('scroll', function() {
            var speed = 0.05;
            var yPos = -(window.pageYOffset * speed);
            parallaxImg.style.transform = 'translateY(' + yPos + 'px) scale(1.05)';
        });
    }
})();
</script>

<?php get_footer(); ?>
