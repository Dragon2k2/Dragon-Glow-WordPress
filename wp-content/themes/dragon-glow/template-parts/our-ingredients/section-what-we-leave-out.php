<?php
/**
 * Dragon Glow — Our Ingredients: What We Leave Out
 * 2-col layout: heading left, 3 items right with border-left.
 * Mirrors stitch: "What we leave out."
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$data      = dg_our_ingredients_data();
$leave_out = $data['leave_out'];
$items     = $leave_out['items'];
?>
<section class="dg-oi-leave-out" aria-label="What we leave out">
	<div class="dg-oi-leave-out-inner">

		<!-- Heading: left col -->
		<div class="dg-oi-leave-out-heading" data-sr>
			<h2 class="dg-oi-leave-out-headline"><?php echo esc_html( $leave_out['headline'] ); ?></h2>
		</div>

		<!-- Items: right col with 3 items -->
		<div class="dg-oi-leave-out-items">
			<?php foreach ( $items as $item ) : ?>
			<div class="dg-oi-leave-out-item" data-sr>
				<h3 class="dg-oi-leave-out-item-text"><?php echo esc_html( $item ); ?></h3>
			</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
