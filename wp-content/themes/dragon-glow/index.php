<?php
/**
 * Dragon Glow — index.php
 * Fallback template when no more specific template is found.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
    ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="mb-8">
                    <?php the_title( '<h1 class="font-headline text-headline-lg text-primary mb-4">', '</h1>' ); ?>
                </header>
                <div class="entry-content font-body text-body-lg text-on-surface">
                    <?php the_content(); ?>
                </div>
            </article>
    <?php
        endwhile;

        // Pagination
        the_posts_pagination( array(
            'mid_size'  => 2,
            'prev_text' => '<span class="material-symbols-outlined">chevron_left</span>',
            'next_text' => '<span class="material-symbols-outlined">chevron_right</span>',
        ) );

    else :
    ?>
        <div class="text-center py-24">
            <h2 class="font-headline text-headline-md text-primary mb-4">
                <?php esc_html_e( 'Nothing Found', 'dragon-glow' ); ?>
            </h2>
            <p class="text-on-surface-variant mb-8">
                <?php esc_html_e( 'It seems we can\'t find what you\'re looking for.', 'dragon-glow' ); ?>
            </p>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary">
                <?php esc_html_e( 'Return Home', 'dragon-glow' ); ?>
            </a>
        </div>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
