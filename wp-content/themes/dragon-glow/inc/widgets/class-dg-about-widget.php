<?php
/**
 * Dragon Glow — About Widget
 *
 * Custom WP_Widget hiển thị brand description (title + body). File name
 * trùng class name theo convention `class-dg-*.php ↔ DG_*`.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Custom Widget: Dragon Glow About
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
