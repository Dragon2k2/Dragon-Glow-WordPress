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
        <div class="dg-contact-panel lg:col-span-7 order-1 lg:order-2">
            <div class="dg-contact-card">
                <form class="dg-contact-form" id="dg-contact-form">
                    <?php wp_nonce_field( 'dg_contact_nonce', 'dg_nonce_field' ); ?>
                    <input type="hidden" name="action" value="dg_contact_form" />

                    <!-- First Name & Last Name -->
                    <div class="dg-contact-form__row">
                        <div class="dg-contact-field">
                            <input class="dg-contact-field__control"
                                  id="first_name"
                                  name="first_name"
                                  placeholder=" "
                                  type="text"
                                  required />
                            <label class="dg-contact-field__label"
                                   for="first_name">
                                <?php esc_html_e( 'First Name', 'dragon-glow' ); ?>
                            </label>
                        </div>
                        <div class="dg-contact-field">
                            <input class="dg-contact-field__control"
                                  id="last_name"
                                  name="last_name"
                                  placeholder=" "
                                  type="text"
                                  required />
                            <label class="dg-contact-field__label"
                                   for="last_name">
                                <?php esc_html_e( 'Last Name', 'dragon-glow' ); ?>
                            </label>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="dg-contact-field">
                        <input class="dg-contact-field__control"
                              id="contact-email"
                              name="email"
                              placeholder=" "
                              type="email"
                              required />
                        <label class="dg-contact-field__label"
                               for="contact-email">
                            <?php esc_html_e( 'Email Address', 'dragon-glow' ); ?>
                        </label>
                    </div>

                    <!-- Subject Dropdown -->
                    <div class="dg-contact-field dg-contact-field--select">
                        <select class="dg-contact-field__control"
                                aria-label="<?php esc_attr_e( 'Inquiry type', 'dragon-glow' ); ?>"
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
                        <span class="material-symbols-outlined dg-contact-field__chevron" aria-hidden="true">
                            expand_more
                        </span>
                    </div>

                    <!-- Message -->
                    <div class="dg-contact-field dg-contact-field--textarea">
                        <textarea class="dg-contact-field__control"
                                 id="contact-message"
                                 name="message"
                                 placeholder=" "
                                 rows="4"
                                 maxlength="1000"
                                 required></textarea>
                        <label class="dg-contact-field__label"
                               for="contact-message">
                            <?php esc_html_e( 'Your Message', 'dragon-glow' ); ?>
                        </label>
                        <span class="dg-contact-field__count" id="dg-contact-count">0 / 1000</span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="dg-submit-btn" class="dg-contact-form__submit">
                        <?php esc_html_e( 'Send Message', 'dragon-glow' ); ?>
                    </button>

                    <p id="dg-contact-msg" class="dg-contact-form__msg" role="status" aria-live="polite" hidden></p>
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
                <?php esc_html_e( 'concierge@dragonglows.page.gd', 'dragon-glow' ); ?>
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

<?php get_footer(); ?>
