</main><!-- #main-content -->

<?php get_template_part( 'template-parts/global/footer-main' ); ?>

<?php
// Render global sign-out modal (displayed on all pages when user is logged in).
dg_render_global_signout_modal();

wp_footer();
?>
</body>
</html>
