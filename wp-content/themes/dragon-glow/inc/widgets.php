<?php
/**
 * Dragon Glow — Widget Areas
 * Đăng ký sidebar và footer widget areas.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register widget areas.
 *
 * @return void
 */
function dg_register_widget_areas(): void {
    // Blog Sidebar
    register_sidebar( array(
        'name'          => __( 'Blog Sidebar', 'dragon-glow' ),
        'id'            => 'sidebar-blog',
        'description'   => __( 'Widgets for blog posts sidebar.', 'dragon-glow' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s glass-card rounded-2xl p-6 mb-6">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="font-headline text-lg text-primary mb-4 border-b border-outline-variant pb-2">',
        'after_title'   => '</h3>',
    ) );

    // Footer Column 1 (Brand)
    register_sidebar( array(
        'name'          => __( 'Footer Column 1 - Brand', 'dragon-glow' ),
        'id'            => 'footer-1',
        'description'   => __( 'Brand info and social links in footer.', 'dragon-glow' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="font-label-sm text-label-sm text-primary uppercase tracking-widest mb-4">',
        'after_title'   => '</h4>',
    ) );

    // Footer Column 2 (Shop)
    register_sidebar( array(
        'name'          => __( 'Footer Column 2 - Shop', 'dragon-glow' ),
        'id'            => 'footer-2',
        'description'   => __( 'Shop links in footer.', 'dragon-glow' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="font-label-sm text-label-sm text-primary uppercase tracking-widest mb-4">',
        'after_title'   => '</h4>',
    ) );

    // Footer Column 3 (Company)
    register_sidebar( array(
        'name'          => __( 'Footer Column 3 - Company', 'dragon-glow' ),
        'id'            => 'footer-3',
        'description'   => __( 'Company links in footer.', 'dragon-glow' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="font-label-sm text-label-sm text-primary uppercase tracking-widest mb-4">',
        'after_title'   => '</h4>',
    ) );

    // Footer Column 4 (Help)
    register_sidebar( array(
        'name'          => __( 'Footer Column 4 - Help', 'dragon-glow' ),
        'id'            => 'footer-4',
        'description'   => __( 'Help and newsletter in footer.', 'dragon-glow' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="font-label-sm text-label-sm text-primary uppercase tracking-widest mb-4">',
        'after_title'   => '</h4>',
    ) );
}
add_action( 'widgets_init', 'dg_register_widget_areas' );

/**
 * Custom Widget: Dragon Glow About
 *
 * @package Dragon_Glow
 */
class DG_About_Widget extends WP_Widget {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            'dg_about_widget',
            __( 'Dragon Glow - About', 'dragon-glow' ),
            array(
                'description' => __( 'Display brand description with social links.', 'dragon-glow' ),
                'classname'   => 'dg-about-widget',
            )
        );
    }

    /**
     * Widget output.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Widget instance.
     * @return void
     */
    public function widget( $args, $instance ): void {
        echo $args['before_widget'];

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];
        }

        if ( ! empty( $instance['description'] ) ) {
            echo '<p class="text-on-surface-variant text-sm mb-4">' . esc_html( $instance['description'] ) . '</p>';
        }

        echo $args['after_widget'];
    }

    /**
     * Widget form.
     *
     * @param array $instance Widget instance.
     * @return string
     */
    public function form( $instance ): string {
        $title       = ! empty( $instance['title'] ) ? $instance['title'] : '';
        $description = ! empty( $instance['description'] ) ? $instance['description'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php esc_html_e( 'Title:', 'dragon-glow' ); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                   type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>">
                <?php esc_html_e( 'Description:', 'dragon-glow' ); ?>
            </label>
            <textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"
                      name="<?php echo esc_attr( $this->get_field_name( 'description' ) ); ?>"
                      rows="4"><?php echo esc_textarea( $description ); ?></textarea>
        </p>
        <?php
        return '';
    }

    /**
     * Update widget.
     *
     * @param array $new_instance New instance.
     * @param array $old_instance Old instance.
     * @return array
     */
    public function update( $new_instance, $old_instance ): array {
        $instance = array();
        $instance['title']       = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['description'] = ! empty( $new_instance['description'] ) ? sanitize_textarea_field( $new_instance['description'] ) : '';
        return $instance;
    }
}

/**
 * Register custom widgets.
 *
 * @return void
 */
function dg_register_custom_widgets(): void {
    register_widget( 'DG_About_Widget' );
}
add_action( 'widgets_init', 'dg_register_custom_widgets' );
