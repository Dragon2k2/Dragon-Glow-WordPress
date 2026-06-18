<?php
/**
 * Dragon Glow — Shop Archive (The Collection)
 * Override: woocommerce/archive-product.php
 * Layout: glass sidebar filters (left) + product grid (right) + pagination
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<!-- Decorative Blobs -->
<div class="ethereal-blob bg-primary-container w-[500px] h-[500px] -top-64 -left-64 opacity-30 pointer-events-none"></div>
<div class="ethereal-blob bg-tertiary-container w-[400px] h-[400px] bottom-0 -right-32 opacity-30 pointer-events-none"></div>

<main class="max-w-container-max mx-auto flex min-h-screen relative" id="main-content">

    <!-- Sidebar Filters -->
    <aside class="hidden lg:block w-80 glass-sidebar sticky top-[72px] h-[calc(100vh-72px)] p-8 overflow-y-auto custom-scrollbar flex-shrink-0">
        <?php get_template_part( 'template-parts/shop/filter-sidebar' ); ?>
    </aside>

    <!-- Mobile Filter Toggle -->
    <div class="lg:hidden fixed bottom-6 right-6 z-50">
        <button type="button"
                id="dg-mobile-filter-toggle"
                class="w-14 h-14 rounded-full bg-primary text-white shadow-lg flex items-center justify-center hover:scale-105 transition-transform"
                aria-label="<?php esc_attr_e( 'Open filters', 'dragon-glow' ); ?>">
            <span class="material-symbols-outlined">tune</span>
        </button>
    </div>

    <!-- Mobile Filter Panel -->
    <div id="dg-mobile-filter-panel" class="fixed inset-0 z-[200] hidden lg:hidden">
        <div class="absolute inset-0 bg-inverse-surface/50" id="dg-filter-overlay"></div>
        <div class="absolute right-0 top-0 bottom-0 w-80 bg-surface overflow-y-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-headline text-xl text-primary"><?php esc_html_e( 'Filters', 'dragon-glow' ); ?></h3>
                <button type="button" id="dg-close-filter" class="p-2 hover:bg-surface-container rounded-full transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <?php get_template_part( 'template-parts/shop/filter-sidebar' ); ?>
        </div>
    </div>

    <!-- Product Area -->
    <div class="flex-1 px-margin-mobile md:px-margin-desktop py-12">

        <!-- Page Header -->
        <header class="mb-12">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <?php get_template_part( 'template-parts/global/breadcrumb' ); ?>
                    <h1 class="font-headline text-headline-lg text-primary">
                        <?php woocommerce_page_title(); ?>
                    </h1>
                </div>

                <!-- Sort -->
                <div class="flex items-center gap-4">
                    <span class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-widest hidden sm:block">
                        <?php esc_html_e( 'Sort By', 'dragon-glow' ); ?>
                    </span>
                    <?php woocommerce_catalog_ordering(); ?>
                </div>
            </div>

            <!-- Active Filter Tags -->
            <?php get_template_part( 'template-parts/shop/active-filters' ); ?>
        </header>

        <?php if ( woocommerce_product_loop() ) : ?>

            <?php woocommerce_product_loop_start(); ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-gutter">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php wc_get_template_part( 'content', 'product' ); ?>
                    <?php endwhile; ?>
                </div>

            <?php woocommerce_product_loop_end(); ?>

            <!-- Pagination -->
            <footer class="mt-section-gap flex flex-col items-center gap-8">
                <?php get_template_part( 'template-parts/shop/pagination' ); ?>
                <p class="text-label-sm font-label-sm text-on-surface-variant">
                    <?php
                    $total    = (int) $GLOBALS['wp_query']->found_posts;
                    $per_page = (int) $GLOBALS['wp_query']->get( 'posts_per_page' );
                    $current  = max( 1, (int) get_query_var( 'paged' ) );
                    $from     = $per_page > 0 ? ( ( $current - 1 ) * $per_page ) + 1 : 1;
                    $to       = min( $current * $per_page, $total );
                    printf(
                        esc_html__( 'Showing %1$d-%2$d of %3$d Products', 'dragon-glow' ),
                        (int) $from,
                        (int) $to,
                        (int) $total
                    );
                    ?>
                </p>
            </footer>

        <?php else : ?>
            <div class="py-24 text-center">
                <?php woocommerce_no_products_found(); ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterToggle = document.getElementById('dg-mobile-filter-toggle');
    const filterPanel = document.getElementById('dg-mobile-filter-panel');
    const filterOverlay = document.getElementById('dg-filter-overlay');
    const filterClose = document.getElementById('dg-close-filter');

    if (filterToggle && filterPanel) {
        filterToggle.addEventListener('click', function() {
            filterPanel.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    if (filterOverlay) {
        filterOverlay.addEventListener('click', function() {
            filterPanel.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }

    if (filterClose) {
        filterClose.addEventListener('click', function() {
            filterPanel.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }
});
</script>

<?php get_footer(); ?>
