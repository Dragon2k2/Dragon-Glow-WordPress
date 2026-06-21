<?php
/**
 * Dragon Glow — Shop Product Card (Magazine Staggered)
 * Used in the 3-column "Curated Glow" grid
 * Matches Stitch design: shop-page1 / shop-page2
 *
 * @package Dragon_Glow
 */

defined('ABSPATH') || exit;

$product_id = get_the_ID();
$_product = dg_is_woocommerce_active() ? wc_get_product($product_id) : null;
if (!$_product) {
	return;
}

$product_name = $_product->get_name();
$product_price = $_product->get_price_html();
$product_link = get_permalink($product_id);
$image_id = $_product->get_image_id();
$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : wc_placeholder_img_src('large');

// Tags as feature pills (BRIGHTEN • HYDRATE • FIRM)
$tag_pills = '';
$tag_terms = get_the_terms($product_id, 'product_tag');
if (!empty($tag_terms) && !is_wp_error($tag_terms)) {
	$names = array_map(function ($t) {
		return strtoupper($t->name); }, $tag_terms);
	$tag_pills = implode(' • ', array_slice($names, 0, 3));
}

// Badges
$is_new = $_product->is_featured();
$is_on_sale = $_product->is_on_sale();
$badge_label = '';
$is_dark = false;

if ($is_on_sale) {
	$badge_label = __('Sale', 'dragon-glow');
	$is_dark = true;
} elseif ($is_new) {
	$badge_label = __('New', 'dragon-glow');
} else {
	// Pull first product_cat as a "feature" badge (e.g. Bestseller, Detox, SPF 50...)
	$cat_terms = get_the_terms($product_id, 'product_cat');
	if (!empty($cat_terms) && !is_wp_error($cat_terms)) {
		$badge_label = strtoupper($cat_terms[0]->name);
	}
}

$delay_ms = (int) get_query_var('dg_product_delay', 0);
$delay_style = $delay_ms > 0 ? sprintf('transition-delay: %dms;', $delay_ms) : '';
?>
<div class="stagger-item group product-card-hover reveal-on-scroll active dg-product-card"
	style="<?php echo esc_attr($delay_style); ?>">
	<a href="<?php echo esc_url($product_link); ?>" aria-label="<?php echo esc_attr($product_name); ?>"
		class="dg-product-stretched-link">
	</a>
	<div class="relative aspect-[3/4] overflow-hidden bg-surface-container-low mb-6 dg-product-image">
		<img alt="<?php echo esc_attr($product_name); ?>" class="w-full h-full object-cover dg-product-img"
			src="<?php echo esc_url($image_url); ?>" />

		<?php if ($badge_label): ?>
			<?php $pos_class = $is_dark ? 'absolute top-4 right-4' : 'absolute top-4 left-4'; ?>
			<div class="<?php echo esc_attr($pos_class); ?>">
				<span class="<?php echo $is_dark ? 'dg-badge-right' : 'dg-badge-left'; ?>">
					<?php echo esc_html($badge_label); ?>
				</span>
			</div>
		<?php endif; ?>

		<button class="dg-add-to-ritual dg-quick-add inline-flex items-center justify-center gap-2"
			data-product-id="<?php echo esc_attr($product_id); ?>"
			data-original-label="<?php esc_attr_e('Add to Ritual', 'dragon-glow'); ?>"
			type="button">
			<span class="material-symbols-outlined" style="font-size:16px;line-height:1;">shopping_bag</span>
			<span class="dg-quick-add__label"><?php esc_html_e('Add to Ritual', 'dragon-glow'); ?></span>
		</button>
	</div>
	<a href="<?php echo esc_url($product_link); ?>" class="text-center px-2 dg-product-info-link">
		<h3 class="dg-product-name">
			<?php echo esc_html($product_name); ?>
		</h3>
		<div class="dg-product-divider" aria-hidden="true"></div>
		<?php if ($tag_pills): ?>
			<p class="dg-product-tags"><?php echo esc_html($tag_pills); ?></p>
		<?php endif; ?>
		<p class="dg-product-price"><?php echo wp_kses_post($product_price); ?></p>
	</a>
</div>