<?php
/**
 * Dragon Glow — Empty State
 * Reusable empty-state block: icon circle + heading + description + CTA buttons.
 *
 * Usage:
 *   get_template_part( 'template-parts/global/empty-state', null, $args );
 *
 * $args supports the following keys (all optional with sensible defaults):
 *   - icon             (string) Material Symbol name. Default: 'shopping_bag'.
 *   - icon_size        (int)    Icon font-size in pixels. Default: 96.
 *   - circle_size      (string) Tailwind classes for the icon-circle wrapper.
 *                         Default: 'w-48 h-48' (48 units = 192 px at 4× scale).
 *   - title            (string) Heading text. Default: 'Your bag is empty'.
 *   - description      (string) Body text. Default: empty.
 *   - primary_cta      (array)  ['label' => string, 'url' => string].
 *   - secondary_cta    (array)  Optional second button; same shape as primary_cta.
 *                         When omitted the second slot is omitted from markup.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// Provide defaults compatible with get_template_part()'s built-in slug/variant/args
// mechanism: the third argument becomes `$args` inside the template part.
$defaults = array(
	'icon'          => 'shopping_bag',
	'icon_size'     => 96,
	'circle_size'   => 'w-48 h-48',
	'title'         => __( 'Your bag is empty', 'dragon-glow' ),
	'description'   => '',
	'primary_cta'  => null,
	'secondary_cta' => null,
);
$args = wp_parse_args( $args, $defaults );

// Normalise CTA arrays so templates can pass partial data.
$primary_cta = is_array( $args['primary_cta'] )
	? wp_parse_args( $args['primary_cta'], array( 'label' => '', 'url' => '#' ) )
	: null;

$secondary_cta = is_array( $args['secondary_cta'] )
	? wp_parse_args( $args['secondary_cta'], array( 'label' => '', 'url' => '#' ) )
	: null;
?>
<div class="mb-12">
	<div class="<?php echo esc_attr( $args['circle_size'] ); ?> mx-auto bg-surface-container rounded-full flex items-center justify-center mb-8">
		<span class="material-symbols-outlined text-primary" aria-hidden="true" style="font-size: <?php echo (int) $args['icon_size']; ?>px;">
			<?php echo esc_html( $args['icon'] ); ?>
		</span>
	</div>

	<?php if ( ! empty( $args['title'] ) ) : ?>
		<h1 class="font-headline text-headline-lg text-primary mb-4">
			<?php echo esc_html( $args['title'] ); ?>
		</h1>
	<?php endif; ?>

	<?php if ( ! empty( $args['description'] ) ) : ?>
		<p class="text-on-surface-variant text-body-lg max-w-md mx-auto mb-8">
			<?php echo esc_html( $args['description'] ); ?>
		</p>
	<?php endif; ?>
</div>

<?php if ( $primary_cta || $secondary_cta ) : ?>
	<div class="flex flex-col sm:flex-row gap-4 justify-center">
		<?php if ( $primary_cta && ! empty( $primary_cta['label'] ) ) : ?>
			<a href="<?php echo esc_url( $primary_cta['url'] ); ?>"
			   class="btn-primary">
				<?php echo esc_html( $primary_cta['label'] ); ?>
			</a>
		<?php endif; ?>

		<?php if ( $secondary_cta && ! empty( $secondary_cta['label'] ) ) : ?>
			<a href="<?php echo esc_url( $secondary_cta['url'] ); ?>"
			   class="btn-ghost">
				<?php echo esc_html( $secondary_cta['label'] ); ?>
			</a>
		<?php endif; ?>
	</div>
<?php endif; ?>
