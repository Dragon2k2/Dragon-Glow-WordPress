<?php
/**
 * Template Name: FAQ — Dragon Glow
 * Description: Frequently asked questions with accordion and search
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();

set_query_var( 'dg_hero_title',    esc_html__( 'Frequently Asked Questions', 'dragon-glow' ) );
set_query_var( 'dg_hero_subtitle', esc_html__( 'Have a question? Browse our answers below, or reach our concierge team for personalised guidance.', 'dragon-glow' ) );
get_template_part( 'template-parts/global/page-hero' );

// ── FAQ data (single source of truth — edit here to update questions) ──────
$faq_groups = array(
    'orders_shipping' => array(
        'label' => esc_html__( 'Orders & Shipping', 'dragon-glow' ),
        'items' => array(
            array(
                'question' => esc_html__( 'How long will it take for my order to arrive?', 'dragon-glow' ),
                'answer'   => esc_html__( 'Standard shipping within the US takes 5–7 business days. Express shipping (2–3 business days) and overnight delivery are available at checkout for an additional fee. International orders typically arrive within 10–21 business days depending on the destination and customs processing.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'Do you ship internationally?', 'dragon-glow' ),
                'answer'   => esc_html__( 'Yes, we ship to over 60 countries worldwide. All international orders are customs-cleared before delivery. Import duties and taxes are the responsibility of the recipient and are calculated at checkout.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'How can I track my order?', 'dragon-glow' ),
                'answer'   => esc_html__( 'Once your order ships, you will receive a confirmation email with a tracking number. You can also use our Track Your Order page to check the status using your order ID and the email address used at checkout.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'Can I change or cancel my order after it\'s placed?', 'dragon-glow' ),
                'answer'   => esc_html__( 'We begin processing orders within 1–2 hours of placement. If you need to make a change or cancellation, please contact our concierge team immediately at concierge@dragonglow.com or use the Contact page. We will do our best to accommodate your request before it ships.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'What if my package arrives damaged?', 'dragon-glow' ),
                'answer'   => esc_html__( 'We take great care in packaging, but if your order arrives damaged, please contact us within 48 hours of delivery with photos of the damaged items and packaging. We will arrange a replacement or full refund as quickly as possible.', 'dragon-glow' ),
            ),
        ),
    ),
    'products' => array(
        'label' => esc_html__( 'Products & Ingredients', 'dragon-glow' ),
        'items' => array(
            array(
                'question' => esc_html__( 'Are your products suitable for sensitive skin?', 'dragon-glow' ),
                'answer'   => esc_html__( 'Our formulations are designed with all skin types in mind, including sensitive skin. We use high concentrations of botanical actives and avoid common irritants such as synthetic fragrances, parabens, and sulphates. Each product page lists the full ingredient list (INCI). If you have specific concerns, we recommend a patch test or consulting our concierge team.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'Are your products vegan and cruelty-free?', 'dragon-glow' ),
                'answer'   => esc_html__( 'Yes. All Dragon Glow products are 100% vegan and cruelty-free. We do not test on animals at any stage of development, and none of our ingredients are derived from animals.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'How should I store my products?', 'dragon-glow' ),
                'answer'   => esc_html__( 'Store your products in a cool, dry place away from direct sunlight and heat sources. Our airless pump bottles help protect actives from oxidation. Once opened, we recommend using the product within 12 months and keeping the cap tightly closed.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'Where are your products made?', 'dragon-glow' ),
                'answer'   => esc_html__( 'All Dragon Glow formulations are designed in our New York studio and manufactured in a certified GMP facility in the United States using globally sourced botanical ingredients.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'Do you offer samples before purchasing?', 'dragon-glow' ),
                'answer'   => esc_html__( 'We occasionally offer sample sets with our seasonal launches. Sign up for our newsletter to be the first to know about new releases and sample availability. Full-size products can be returned within 30 days if they are not right for you.', 'dragon-glow' ),
            ),
        ),
    ),
    'returns' => array(
        'label' => esc_html__( 'Returns & Refunds', 'dragon-glow' ),
        'items' => array(
            array(
                'question' => esc_html__( 'What is your return policy?', 'dragon-glow' ),
                'answer'   => esc_html__( 'We accept returns of unopened and unused products within 30 days of delivery. Sealed products that have been opened or used may be eligible for a partial refund or store credit at our discretion. Final-sale items and gift items (store credit only) are non-returnable. Please visit our Shipping & Returns page for the full process.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'How long does a refund take?', 'dragon-glow' ),
                'answer'   => esc_html__( 'Once we receive your return and confirm it meets our policy requirements, refunds are processed within 5–7 business days to your original payment method. You will receive a confirmation email when the refund has been issued. Depending on your bank, it may take an additional 3–5 business days for the funds to appear in your account.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'Do you offer exchanges?', 'dragon-glow' ),
                'answer'   => esc_html__( 'We do not offer direct product exchanges. If you would like a different product, please return the original item for a refund and place a new order for the desired product.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'Who pays for return shipping?', 'dragon-glow' ),
                'answer'   => esc_html__( 'For standard returns, we provide a prepaid, carbon-neutral return shipping label at no cost to you. If you are returning an item due to our error (e.g., wrong item or damaged product), we will cover all return shipping costs.', 'dragon-glow' ),
            ),
        ),
    ),
    'account' => array(
        'label' => esc_html__( 'Account', 'dragon-glow' ),
        'items' => array(
            array(
                'question' => esc_html__( 'Do I need an account to place an order?', 'dragon-glow' ),
                'answer'   => esc_html__( 'No, you can check out as a guest. However, creating an account allows you to save your favourites, track orders, and access exclusive member benefits and early access to new launches.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'How do I reset my password?', 'dragon-glow' ),
                'answer'   => esc_html__( 'On the login page, click "Forgot your password?" and enter your email address. You will receive a password reset link within a few minutes. If you do not see it, please check your spam folder or contact our concierge team.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'How is my personal information protected?', 'dragon-glow' ),
                'answer'   => esc_html__( 'We take data protection seriously. All personal information is encrypted using SSL and stored in compliance with applicable privacy laws. We never sell your data to third parties. For full details, please review our Privacy Policy.', 'dragon-glow' ),
            ),
            array(
                'question' => esc_html__( 'Can I delete my account?', 'dragon-glow' ),
                'answer'   => esc_html__( 'Yes. Contact our concierge team at concierge@dragonglow.com and we will delete your account and associated data within 30 days in accordance with your applicable privacy rights.', 'dragon-glow' ),
            ),
        ),
    ),
);
?>

<main class="pb-24" id="main-content">

    <?php
    // ── Search bar ─────────────────────────────────────────────────────────────
    ?>
    <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop mb-16">
        <div class="dg-faq-search relative max-w-xl mx-auto">
            <span class="material-symbols-outlined dg-search-icon absolute left-5 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">search</span>
            <input
                type="search"
                id="dg-faq-search"
                class="w-full border border-outline-variant/40 bg-surface-container-lowest py-4 pl-14 pr-8 font-body-md text-body-md text-on-surface placeholder-on-surface-variant/50 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/30 transition-all"
                placeholder="<?php esc_attr_e( 'Search for answers...', 'dragon-glow' ); ?>"
                aria-label="<?php esc_attr_e( 'Search frequently asked questions', 'dragon-glow' ); ?>"
            />
            <button
                type="button"
                id="dg-faq-search-clear"
                class="dg-search-clear absolute right-5 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface transition-colors hidden"
                aria-label="<?php esc_attr_e( 'Clear search', 'dragon-glow' ); ?>"
            >
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
        <p id="dg-faq-search-count" class="dg-no-results-msg text-center font-body-md text-body-md text-on-surface-variant mt-6 hidden">
            <?php esc_html_e( 'No results found for your search. Try different keywords or contact our concierge.', 'dragon-glow' ); ?>
        </p>
    </section>

    <?php
    // ── Accordion groups ───────────────────────────────────────────────────────
    ?>
    <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop" aria-label="<?php esc_attr_e( 'FAQ categories', 'dragon-glow' ); ?>">
        <div id="dg-faq-accordion" class="space-y-16">
            <?php foreach ( $faq_groups as $group_id => $group ) : ?>
            <div class="dg-faq-group" data-group="<?php echo esc_attr( $group_id ); ?>">
                <h2 class="dg-faq-group-title font-headline text-headline-lg text-on-surface mb-8 pb-4 border-b border-outline-variant/40">
                    <?php echo esc_html( $group['label'] ); ?>
                </h2>

                <ul class="dg-faq-list space-y-0" role="list">
                    <?php foreach ( $group['items'] as $idx => $item ) : ?>
                    <?php
                    $item_id = 'faq-' . $group_id . '-' . $idx;
                    ?>
                    <li class="dg-faq-item border-b border-outline-variant/20 last:border-b-0">
                        <button
                            class="dg-faq-trigger w-full text-left flex items-start gap-4 py-6 pr-4 font-body-md text-body-md text-on-surface hover:text-primary transition-colors group"
                            aria-expanded="false"
                            aria-controls="<?php echo esc_attr( $item_id . '-panel' ); ?>"
                            id="<?php echo esc_attr( $item_id . '-trigger' ); ?>"
                        >
                            <span class="dg-faq-icon material-symbols-outlined text-on-surface-variant group-hover:text-primary mt-[2px] transition-transform duration-300 flex-shrink-0" aria-hidden="true">
                                add
                            </span>
                            <span class="dg-faq-question-text flex-1 text-left"><?php echo esc_html( $item['question'] ); ?></span>
                        </button>
                        <div
                            class="dg-faq-panel overflow-hidden"
                            id="<?php echo esc_attr( $item_id . '-panel' ); ?>"
                            role="region"
                            aria-labelledby="<?php echo esc_attr( $item_id . '-trigger' ); ?>"
                            hidden
                        >
                            <div class="dg-faq-answer pb-6 pl-10 pr-4 font-body-md text-body-md text-on-surface-variant leading-relaxed">
                                <?php echo esc_html( $item['answer'] ); ?>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>

        <?php
        // ── No results state ──────────────────────────────────────────────────
        ?>
        <div id="dg-faq-empty" class="dg-no-results-content text-center py-20 hidden">
            <span class="material-symbols-outlined text-primary/40 text-5xl mb-6 block">search_off</span>
            <h3 class="font-headline text-headline-md text-on-surface mb-3">
                <?php esc_html_e( 'No answers found', 'dragon-glow' ); ?>
            </h3>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-md mx-auto mb-8">
                <?php esc_html_e( 'We couldn\'t find an answer matching your search. Our concierge team is happy to help.', 'dragon-glow' ); ?>
            </p>
            <?php
            $contact_page = get_page_by_path( 'contact' );
            $contact_url  = $contact_page ? get_permalink( $contact_page->ID ) : '#';
            ?>
            <a href="<?php echo esc_url( $contact_url ); ?>"
               class="inline-block bg-primary text-on-primary font-label-md text-label-md uppercase tracking-[0.25em] py-4 px-10 hover:opacity-90 transition-opacity">
                <?php esc_html_e( 'Contact Concierge', 'dragon-glow' ); ?>
            </a>
        </div>
    </section>

    <?php
    // ── Contact CTA ───────────────────────────────────────────────────────────
    ?>
    <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop mt-20 pt-16 border-t border-outline-variant/30 text-center">
        <p class="font-body-md text-body-md text-on-surface-variant mb-6">
            <?php esc_html_e( 'Still have questions? Our concierge team is here for you.', 'dragon-glow' ); ?>
        </p>
        <?php
        $contact_page = get_page_by_path( 'contact' );
        $contact_url  = $contact_page ? get_permalink( $contact_page->ID ) : '#';
        ?>
        <a href="<?php echo esc_url( $contact_url ); ?>"
           class="inline-flex items-center gap-2 bg-surface-container-lowest border border-outline-variant/40 text-primary font-label-md text-label-md uppercase tracking-[0.25em] py-4 px-10 hover:bg-surface-container-low hover:border-primary/40 transition-all">
            <?php esc_html_e( 'Contact Us', 'dragon-glow' ); ?>
            <span class="material-symbols-outlined text-xl" aria-hidden="true">arrow_forward</span>
        </a>
    </section>

</main>

<?php get_footer(); ?>
