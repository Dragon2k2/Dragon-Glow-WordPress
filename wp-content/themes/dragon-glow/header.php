<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php bloginfo( 'description' ); ?>">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Decorative Blobs (fixed, z-index -1) -->
<div class="ethereal-blob bg-[#f4c2c2] w-[500px] h-[500px] -top-64 -left-64 animate-pulse pointer-events-none"></div>
<div class="ethereal-blob bg-[#e1e1f5] w-[400px] h-[400px] bottom-0 -right-32 pointer-events-none"></div>

<!-- Skip to content link for accessibility -->
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:bg-primary focus:text-white focus:px-4 focus:py-2 focus:rounded">
    <?php esc_html_e( 'Skip to content', 'dragon-glow' ); ?>
</a>

<?php get_template_part( 'template-parts/global/header-nav' ); ?>

<main id="main-content">
