<?php
/**
 * Dragon Glow — Product Tabs
 * Description / Ingredients / How to Use / Reviews tabs
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product ) {
    return;
}

// Get product description
$description = $product->get_description();

// Get custom meta fields (if using ACF or custom fields)
$ingredients  = get_post_meta( get_the_ID(), '_dg_ingredients', true );
$how_to_use   = get_post_meta( get_the_ID(), '_dg_how_to_use', true );

// Get reviews
$comments = get_comments( array(
    'post_id' => get_the_ID(),
    'status'  => 'approve',
    'number'  => 5,
) );
$review_count = get_comments( array(
    'post_id' => get_the_ID(),
    'status'  => 'approve',
    'count'   => true,
) );
?>
<section class="mt-section-gap" id="product-tabs">
    <!-- Tab Navigation -->
    <div class="border-b border-outline-variant mb-8">
        <nav class="flex gap-8 -mb-px overflow-x-auto custom-scrollbar" role="tablist">
            <button type="button"
                    role="tab"
                    aria-selected="true"
                    aria-controls="tab-description"
                    data-tab="description"
                    class="dg-tab-btn text-primary font-bold border-b-2 border-tertiary-container pb-4 whitespace-nowrap transition-colors">
                <?php esc_html_e( 'Description', 'dragon-glow' ); ?>
            </button>

            <?php if ( $ingredients ) : ?>
            <button type="button"
                    role="tab"
                    aria-selected="false"
                    aria-controls="tab-ingredients"
                    data-tab="ingredients"
                    class="dg-tab-btn text-on-surface-variant font-medium pb-4 whitespace-nowrap transition-colors hover:text-primary">
                <?php esc_html_e( 'Ingredients', 'dragon-glow' ); ?>
            </button>
            <?php endif; ?>

            <?php if ( $how_to_use ) : ?>
            <button type="button"
                    role="tab"
                    aria-selected="false"
                    aria-controls="tab-how-to-use"
                    data-tab="how-to-use"
                    class="dg-tab-btn text-on-surface-variant font-medium pb-4 whitespace-nowrap transition-colors hover:text-primary">
                <?php esc_html_e( 'How to Use', 'dragon-glow' ); ?>
            </button>
            <?php endif; ?>

            <button type="button"
                    role="tab"
                    aria-selected="false"
                    aria-controls="tab-reviews"
                    data-tab="reviews"
                    class="dg-tab-btn text-on-surface-variant font-medium pb-4 whitespace-nowrap transition-colors hover:text-primary">
                <?php
                printf(
                    esc_html__( 'Reviews (%d)', 'dragon-glow' ),
                    esc_html( $review_count )
                );
                ?>
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="space-y-12">
        <!-- Description Tab -->
        <div id="tab-description" class="dg-tab-pane" role="tabpanel">
            <?php if ( $description ) : ?>
                <div class="prose prose-lg max-w-none text-on-surface-variant">
                    <?php echo wp_kses_post( wpautop( $description ) ); ?>
                </div>
            <?php else : ?>
                <p class="text-on-surface-variant">
                    <?php esc_html_e( 'No description available for this product.', 'dragon-glow' ); ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Ingredients Tab -->
        <?php if ( $ingredients ) : ?>
        <div id="tab-ingredients" class="dg-tab-pane hidden" role="tabpanel">
            <div class="bg-surface-container-low rounded-2xl p-8">
                <h3 class="font-headline text-xl text-primary mb-4">
                    <?php esc_html_e( 'Key Ingredients', 'dragon-glow' ); ?>
                </h3>
                <div class="prose prose-sm max-w-none text-on-surface-variant">
                    <?php echo wp_kses_post( wpautop( $ingredients ) ); ?>
                </div>

                <div class="mt-6 pt-6 border-t border-outline-variant">
                    <p class="text-label-sm text-on-surface-variant flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-sm">eco</span>
                        <?php esc_html_e( '100% Vegan & Cruelty-Free', 'dragon-glow' ); ?>
                    </p>
                    <p class="text-label-sm text-on-surface-variant mt-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-sm">science</span>
                        <?php esc_html_e( 'Dermatologist Tested', 'dragon-glow' ); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- How to Use Tab -->
        <?php if ( $how_to_use ) : ?>
        <div id="tab-how-to-use" class="dg-tab-pane hidden" role="tabpanel">
            <div class="bg-surface-container-low rounded-2xl p-8">
                <h3 class="font-headline text-xl text-primary mb-4">
                    <?php esc_html_e( 'How to Use', 'dragon-glow' ); ?>
                </h3>
                <div class="prose prose-sm max-w-none text-on-surface-variant">
                    <?php echo wp_kses_post( wpautop( $how_to_use ) ); ?>
                </div>

                <!-- Usage tips -->
                <div class="mt-8 pt-6 border-t border-outline-variant">
                    <h4 class="font-headline text-lg text-primary mb-4">
                        <?php esc_html_e( 'Pro Tips', 'dragon-glow' ); ?>
                    </h4>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-tertiary text-sm mt-1">lightbulb</span>
                            <span class="text-on-surface-variant"><?php esc_html_e( 'For best results, apply to clean, dry skin morning and evening.', 'dragon-glow' ); ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-tertiary text-sm mt-1">lightbulb</span>
                            <span class="text-on-surface-variant"><?php esc_html_e( 'Allow 1-2 minutes between layering products for optimal absorption.', 'dragon-glow' ); ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-tertiary text-sm mt-1">lightbulb</span>
                            <span class="text-on-surface-variant"><?php esc_html_e( 'Don\'t forget SPF during your morning routine.', 'dragon-glow' ); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Reviews Tab -->
        <div id="tab-reviews" class="dg-tab-pane hidden" role="tabpanel">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Review Summary -->
                <div class="glass-card rounded-2xl p-8 text-center">
                    <div class="text-6xl font-headline text-primary mb-4">
                        <?php echo number_format( (float) $product->get_average_rating(), 1 ); ?>
                    </div>

                    <?php dg_star_rating( (float) $product->get_average_rating() ); ?>

                    <p class="text-on-surface-variant mt-4">
                        <?php
                        printf(
                            esc_html( _n( 'Based on %d review', 'Based on %d reviews', $review_count, 'dragon-glow' ) ),
                            esc_html( $review_count )
                        );
                        ?>
                    </p>

                    <?php if ( $review_count > 0 ) : ?>
                    <a href="#reviews-list" class="btn-primary mt-6 inline-block">
                        <?php esc_html_e( 'Read Reviews', 'dragon-glow' ); ?>
                    </a>
                    <?php else : ?>
                    <p class="text-on-surface-variant mt-6 italic">
                        <?php esc_html_e( 'Be the first to review this product!', 'dragon-glow' ); ?>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Write Review -->
                <div>
                    <h3 class="font-headline text-xl text-primary mb-6">
                        <?php esc_html_e( 'Share Your Experience', 'dragon-glow' ); ?>
                    </h3>

                    <?php if ( is_user_logged_in() ) : ?>
                        <form id="dg-review-form" class="space-y-6">
                            <input type="hidden" name="action" value="dg_submit_review" />
                            <?php wp_nonce_field( 'dg_review_nonce', 'dg_review_nonce' ); ?>

                            <div>
                                <label for="review-rating" class="block text-label-sm text-on-surface-variant mb-2">
                                    <?php esc_html_e( 'Your Rating', 'dragon-glow' ); ?>
                                </label>
                                <div class="flex gap-2" id="review-stars">
                                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                        <button type="button" class="text-2xl text-outline-variant hover:text-tertiary transition-colors" data-rating="<?php echo esc_attr( $i ); ?>">
                                            <span class="material-symbols-outlined" style="--dg-star-fill:0">star</span>
                                        </button>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="review-rating-value" value="5" />
                            </div>

                            <div>
                                <label for="review-title" class="block text-label-sm text-on-surface-variant mb-2">
                                    <?php esc_html_e( 'Review Title', 'dragon-glow' ); ?>
                                </label>
                                <input type="text"
                                       id="review-title"
                                       name="title"
                                       required
                                       class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none" />
                            </div>

                            <div>
                                <label for="review-text" class="block text-label-sm text-on-surface-variant mb-2">
                                    <?php esc_html_e( 'Your Review', 'dragon-glow' ); ?>
                                </label>
                                <textarea id="review-text"
                                          name="text"
                                          rows="5"
                                          required
                                          class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none resize-none"></textarea>
                            </div>

                            <button type="submit" class="btn-primary w-full">
                                <?php esc_html_e( 'Submit Review', 'dragon-glow' ); ?>
                            </button>
                        </form>
                    <?php else : ?>
                        <p class="text-on-surface-variant">
                            <?php
                            printf(
                                '<a href="%s" class="text-primary hover:underline">%s</a> %s',
                                esc_url( wc_get_page_permalink( 'myaccount' ) ),
                                esc_html__( 'Login', 'dragon-glow' ),
                                esc_html__( 'to leave a review.', 'dragon-glow' )
                            );
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reviews List -->
            <?php if ( ! empty( $comments ) ) : ?>
            <div class="mt-12 pt-8 border-t border-outline-variant" id="reviews-list">
                <h3 class="font-headline text-xl text-primary mb-8">
                    <?php esc_html_e( 'Customer Reviews', 'dragon-glow' ); ?>
                </h3>

                <div class="space-y-8">
                    <?php foreach ( $comments as $comment ) : ?>
                        <article class="glass-card rounded-2xl p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-primary font-bold">
                                    <?php echo esc_html( strtoupper( substr( get_comment_author( $comment ), 0, 1 ) ) ); ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-on-surface"><?php echo esc_html( get_comment_author( $comment ) ); ?></h4>
                                    <div class="flex items-center gap-2">
                                        <?php
                                        $rating = get_comment_meta( $comment->comment_ID, 'rating', true );
                                        if ( $rating ) {
                                            dg_star_rating( (float) $rating );
                                        }
                                        ?>
                                        <span class="text-label-sm text-on-surface-variant">
                                            <?php echo esc_html( human_time_diff( get_comment_date( 'U', $comment ), current_time( 'timestamp' ) ) . ' ago' ); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $title = get_comment_meta( $comment->comment_ID, 'title', true );
                            if ( $title ) :
                            ?>
                                <h5 class="font-bold text-primary mb-2"><?php echo esc_html( $title ); ?></h5>
                            <?php endif; ?>

                            <p class="text-on-surface-variant">
                                <?php echo esc_html( get_comment_text( $comment ) ); ?>
                            </p>

                            <?php if ( get_comment_meta( $comment->comment_ID, 'verified', true ) ) : ?>
                            <p class="text-label-sm text-tertiary mt-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">verified</span>
                                <?php esc_html_e( 'Verified Purchase', 'dragon-glow' ); ?>
                            </p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    document.querySelectorAll('.dg-tab-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            window.dgSwitchTab ? window.dgSwitchTab(tabId) : null;
        });
    });

    // Star rating in review form
    const stars = document.querySelectorAll('#review-stars button');
    const ratingInput = document.getElementById('review-rating-value');

    stars.forEach(function(star) {
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            updateStars(rating);
        });

        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            updateStars(rating);
        });
    });

    function updateStars(rating) {
        stars.forEach(function(star, index) {
            const icon = star.querySelector('.material-symbols-outlined');
            if (index < rating) {
                icon.style.setProperty('--dg-star-fill', '1');
                icon.classList.remove('text-outline-variant');
                icon.classList.add('text-tertiary');
            } else {
                icon.style.setProperty('--dg-star-fill', '0');
                icon.classList.add('text-outline-variant');
                icon.classList.remove('text-tertiary');
            }
        });
    }
});
</script>
