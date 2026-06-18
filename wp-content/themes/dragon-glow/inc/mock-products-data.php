<?php
/**
 * Dragon Glow — Shared Mock Product Data & Helpers
 *
 * Single source of truth for all mock product data used by:
 *   - template-shop.php  (no-WooCommerce fallback grid)
 *   - template-mock-product.php  (standalone detail page, now a thin include wrapper)
 *   - template-parts/shop/product-detail.php  (reusable detail partial)
 *
 * Keep in sync: keys must match sanitize_title( $p['name'] ) used in
 * template-shop.php $card_url generation.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

// ── Mock product data ────────────────────────────────────────────────
// Keys = sanitize_title( name ). Display "category" = human label;
// "category_slug" = lowercase value used by template-shop.php (filter URL).
$mock_products_data = array(
	'radiance-awakening-serum' => array(
		'name'          => 'Radiance Awakening Serum',
		'price'         => '$128.00',
		'tags'          => 'BRIGHTEN • HYDRATE • FIRM',
		'badge'         => 'Bestseller',
		'badge_pos'     => 'left',
		'category'      => 'Serums',
		'category_slug' => 'serums',
		'rating'        => 5.0,
		'review_count'  => 98,
		'short_desc'    => 'A transformative daily serum infused with dragon fruit enzymes and bio-active radiance particles. Deeply hydrates while unveiling an ethereal, glass-like glow.',
		'description'   => 'The Radiance Awakening Serum is formulated with a proprietary blend of Dragon Fruit extracts and stabilized Vitamin C. Works at a cellular level to brighten and refine texture.',
		'ingredients'   => 'Dragon Fruit Enzyme, Hyaluronic Acid (Triple Weight), Niacinamide (5%), Gold-Stabilized Vitamin C, Organic Aloe Leaf Juice, Rosehip Seed Oil, Vegetable Glycerin.',
		'how_to_use'    => array(
			array( 'step' => '1', 'title' => 'Cleanse', 'desc' => 'Begin with your Dragon Glow cleanser on damp skin.' ),
			array( 'step' => '2', 'title' => 'Apply',  'desc' => 'Press 3-4 drops gently into skin.' ),
			array( 'step' => '3', 'title' => 'Seal',   'desc' => 'Follow with Ritual Cream for maximum glow.' ),
		),
		'sizes'         => array( '30ml', '50ml' ),
		'img_main'      => get_template_directory_uri() . '/assets/images/shop/radiance-awakening-serum.webp',
		'img_gallery'   => array( get_template_directory_uri() . '/assets/images/shop/radiance-awakening-serum.webp' ),
		'benefits'      => array( 'Instant luminous finish', '24-hour moisture lock', 'Reduces fine lines in 4 weeks' ),
		'badges_extra'  => array( 'Bestseller', 'Vegan & Cruelty-Free' ),
	),
	'velvet-hydrating-cream' => array(
		'name'          => 'Velvet Hydrating Cream',
		'price'         => '$95.00',
		'tags'          => 'REPLENISH • SMOOTH • BARRIER',
		'badge'         => '',
		'badge_pos'     => 'left',
		'category'      => 'Moisturizers',
		'category_slug' => 'moisturizers',
		'rating'        => 4.5,
		'review_count'  => 74,
		'short_desc'    => 'Rich yet weightless, this velvet cream melts into skin to restore the moisture barrier and leave a plush, dewy finish that lasts all day.',
		'description'   => 'Velvet Hydrating Cream combines ceramides, peptides, and botanical extracts to deliver deep hydration while strengthening the skin barrier for a smooth, supple complexion.',
		'ingredients'   => 'Ceramide Complex, Bakuchiol Peptides, Shea Butter, Niacinamide, Hyaluronic Acid, Glycerin, Centella Asiatica Extract.',
		'how_to_use'    => array(
			array( 'step' => '1', 'title' => 'Cleanse', 'desc' => 'Start with a clean, toned face.' ),
			array( 'step' => '2', 'title' => 'Warm',    'desc' => 'Take a pea-sized amount and warm between fingers.' ),
			array( 'step' => '3', 'title' => 'Press',   'desc' => 'Press gently into cheeks, forehead, and chin.' ),
		),
		'sizes'         => array( '50ml', '100ml' ),
		'img_main'      => get_template_directory_uri() . '/assets/images/shop/velvet-hydrating-cream.webp',
		'img_gallery'   => array( get_template_directory_uri() . '/assets/images/shop/velvet-hydrating-cream.webp' ),
		'benefits'      => array( 'Strengthens skin barrier', '72-hour hydration', 'Visibly plumps and smooths' ),
		'badges_extra'  => array( 'Vegan & Cruelty-Free' ),
	),
	'enzyme-essence-mist' => array(
		'name'          => 'Enzyme Essence Mist',
		'price'         => '$72.00',
		'tags'          => 'REFRESH • BALANCE • GLOW',
		'badge'         => 'New',
		'badge_pos'     => 'right',
		'category'      => 'Serums',
		'category_slug' => 'serums',
		'rating'        => 5.0,
		'review_count'  => 42,
		'short_desc'    => 'A refreshing enzyme-powered mist that rebalances, brightens, and preps skin for maximum absorption of your next ritual step.',
		'description'   => 'Enzyme Essence Mist uses papaya and pineapple enzymes to gently exfoliate and brighten while a burst of moisture balances the skin\'s pH.',
		'ingredients'   => 'Papaya Enzyme, Pineapple Extract, Rose Hydrosol, Niacinamide, Panthenol, Glycerin, Hyaluronic Acid.',
		'how_to_use'    => array(
			array( 'step' => '1', 'title' => 'Shake', 'desc' => 'Shake gently before each use.' ),
			array( 'step' => '2', 'title' => 'Mist',  'desc' => 'Hold 20cm from face and mist evenly.' ),
			array( 'step' => '3', 'title' => 'Pat',   'desc' => 'Gently pat into skin or layer your serum on top.' ),
		),
		'sizes'         => array( '100ml', '200ml' ),
		'img_main'      => get_template_directory_uri() . '/assets/images/shop/enzyme-essence-mist.webp',
		'img_gallery'   => array( get_template_directory_uri() . '/assets/images/shop/enzyme-essence-mist.webp' ),
		'benefits'      => array( 'Instant refresh & glow', 'pH-balancing formula', 'Enhances serum absorption' ),
		'badges_extra'  => array( 'New', 'Vegan & Cruelty-Free' ),
	),
	'silk-oil-cleanser' => array(
		'name'          => 'Silk Oil Cleanser',
		'price'         => '$85.00',
		'tags'          => 'PURIFY • SOOTHE • RENEW',
		'badge'         => '',
		'badge_pos'     => 'left',
		'category'      => 'Cleansers',
		'category_slug' => 'cleansers',
		'rating'        => 4.0,
		'review_count'  => 56,
		'short_desc'    => 'A luxurious silk-textured oil cleanser that dissolves makeup, sunscreen, and impurities while nourishing the skin with botanical oils.',
		'description'   => 'Silk Oil Cleanser melts away even the most stubborn makeup with a blend of marula, jojoba, and squalane oils that leave skin clean, balanced, and deeply nourished.',
		'ingredients'   => 'Marula Oil, Jojoba Seed Oil, Squalane, Rosehip Oil, Vitamin E, Lavender Essential Oil, Cucumber Extract.',
		'how_to_use'    => array(
			array( 'step' => '1', 'title' => 'Dispense', 'desc' => 'Apply 2-3 pumps onto dry hands.' ),
			array( 'step' => '2', 'title' => 'Massage',  'desc' => 'Gently massage over dry face in circular motions.' ),
			array( 'step' => '3', 'title' => 'Rinse',    'desc' => 'Add water to emulsify, then rinse clean.' ),
		),
		'sizes'         => array( '150ml' ),
		'img_main'      => get_template_directory_uri() . '/assets/images/shop/silk-oil-cleanser.webp',
		'img_gallery'   => array( get_template_directory_uri() . '/assets/images/shop/silk-oil-cleanser.webp' ),
		'benefits'      => array( 'Removes makeup completely', 'Non-stripping formula', 'Leaves skin soft and supple' ),
		'badges_extra'  => array( 'Vegan & Cruelty-Free' ),
	),
	'golden-eye-repair' => array(
		'name'          => 'Golden Eye Repair',
		'price'         => '$95.00',
		'tags'          => 'RESTORE • BRIGHTEN • FIRM',
		'badge'         => '',
		'badge_pos'     => 'left',
		'category'      => 'Serums',
		'category_slug' => 'serums',
		'rating'        => 4.5,
		'review_count'  => 61,
		'short_desc'    => 'A gold-infused eye serum that targets dark circles, puffiness, and fine lines for a well-rested, luminous under-eye area.',
		'description'   => 'Golden Eye Repair combines colloidal gold, caffeine, and peptides to depuff, brighten, and firm the delicate eye area with visible results in 4 weeks.',
		'ingredients'   => 'Colloidal Gold, Caffeine, Peptide Complex, Hyaluronic Acid, Argan Oil, Vitamin K, Chamomile Extract.',
		'how_to_use'    => array(
			array( 'step' => '1', 'title' => 'Dispense', 'desc' => 'Apply a small amount to the ring finger.' ),
			array( 'step' => '2', 'title' => 'Tap',      'desc' => 'Gently tap around the orbital bone, not directly on lids.' ),
			array( 'step' => '3', 'title' => 'Absorb',   'desc' => 'Allow to absorb fully before applying makeup.' ),
		),
		'sizes'         => array( '15ml', '30ml' ),
		'img_main'      => get_template_directory_uri() . '/assets/images/shop/golden-eye-repair.webp',
		'img_gallery'   => array( get_template_directory_uri() . '/assets/images/shop/golden-eye-repair.webp' ),
		'benefits'      => array( 'Reduces dark circles', 'Depuffs in 15 minutes', 'Firms eye contour' ),
		'badges_extra'  => array( 'Dermatologist Tested' ),
	),
	'radiant-balance-toner' => array(
		'name'          => 'Radiant Balance Toner',
		'price'         => '$72.00',
		'tags'          => 'REFRESH • BALANCE • GLOW',
		'badge'         => '',
		'badge_pos'     => 'left',
		'category'      => 'Serums',
		'category_slug' => 'serums',
		'rating'        => 5.0,
		'review_count'  => 83,
		'short_desc'    => 'A weightless toner that balances skin\'s pH, minimizes pores, and preps skin for optimal serum absorption.',
		'description'   => 'Radiant Balance Toner is a lightweight, alcohol-free formula enriched with AHA/BHA blend and botanical extracts to refine, clarify, and illuminate.',
		'ingredients'   => 'AHA/BHA Complex, Witch Hazel Extract, Niacinamide, Rose Water, Glycerin, Green Tea Extract, Panthenol.',
		'how_to_use'    => array(
			array( 'step' => '1', 'title' => 'Cleanse',   'desc' => 'Use after cleansing on clean skin.' ),
			array( 'step' => '2', 'title' => 'Apply',     'desc' => 'Soak a cotton pad or pour into palms and press into skin.' ),
			array( 'step' => '3', 'title' => 'Continue',  'desc' => 'Follow with serum and moisturizer.' ),
		),
		'sizes'         => array( '150ml', '250ml' ),
		'img_main'      => get_template_directory_uri() . '/assets/images/shop/radiant-balance-toner.webp',
		'img_gallery'   => array( get_template_directory_uri() . '/assets/images/shop/radiant-balance-toner.webp' ),
		'benefits'      => array( 'Minimizes pores', 'Alcohol-free formula', 'Visibly balances tone' ),
		'badges_extra'  => array( 'Vegan & Cruelty-Free' ),
	),
	'lumired-velvet-lip-color' => array(
		'name'          => 'LumiRed Velvet Lip Color',
		'price'         => '$38.00',
		'tags'          => 'BOLD • LONG-LASTING • NOURISH',
		'badge'         => 'BESTSELLER',
		'badge_pos'     => 'left',
		'category'      => 'Lip Color',
		'category_slug' => 'lip-color',
		'rating'        => 4,
		'review_count'  => 124,
		'short_desc'    => 'A rich velvet red lip color by LumiRed that delivers intense pigment, long-lasting wear, and keeps lips soft and nourished all day.',
		'description'   => 'LumiRed Velvet Lip Color combines a deep classic red pigment with nourishing rose oil and vitamin E for a bold, comfortable wear that lasts up to 12 hours without drying or cracking.',
		'ingredients'   => 'Rose Hip Oil, Vitamin E, Shea Butter, Beeswax, Carmine Red Pigment, Jojoba Oil, Hyaluronic Acid.',
		'how_to_use'    => array(
			array( 'step' => '1', 'title' => 'Prep',   'desc' => 'Exfoliate and moisturize lips before application.' ),
			array( 'step' => '2', 'title' => 'Apply',  'desc' => 'Line lips first, then fill in with smooth strokes from center outward.' ),
			array( 'step' => '3', 'title' => 'Set',    'desc' => 'Blot with tissue and reapply for longer lasting bold color.' ),
		),
		'sizes'         => array( '3.5g', '4.5g' ),
		'img_main'      => get_template_directory_uri() . '/assets/images/shop/lumired-velvet-lip.webp',
		'img_gallery'   => array( get_template_directory_uri() . '/assets/images/shop/lumired-velvet-lip.webp' ),
		'benefits'      => array( 'Intense pigment all day', 'Nourishes & hydrates lips', 'Long-lasting up to 12 hours' ),
		'badges_extra'  => array( 'BESTSELLER', 'Vegan & Cruelty-Free' ),
	),
	'radiant-sun-shield' => array(
		'name'          => 'Radiant Sun Shield',
		'price'         => '$68.00',
		'tags'          => 'PROTECT • HYDRATE • GLOW',
		'badge'         => 'SPF 50',
		'badge_pos'     => 'left',
		'category'      => 'Sun Care',
		'category_slug' => 'sun-care',
		'rating'        => 4.5,
		'review_count'  => 112,
		'short_desc'    => 'A luxurious SPF 50 sunscreen that shields, hydrates, and adds a subtle luminous finish — your daily glow protector.',
		'description'   => 'Radiant Sun Shield is a broad-spectrum SPF 50 PA++++ formula that protects against UVA/UVB rays while delivering a dewy, glow-enhancing finish perfect for everyday wear.',
		'ingredients'   => 'Zinc Oxide, Titanium Dioxide, Hyaluronic Acid, Vitamin C, Niacinamide, Aloe Vera, Squalane.',
		'how_to_use'    => array(
			array( 'step' => '1', 'title' => 'Apply',    'desc' => 'Apply as the last step of your morning ritual.' ),
			array( 'step' => '2', 'title' => 'Cover',    'desc' => 'Use a generous amount for full SPF protection.' ),
			array( 'step' => '3', 'title' => 'Reapply',  'desc' => 'Reapply every 2 hours in direct sunlight.' ),
		),
		'sizes'         => array( '50ml' ),
		'img_main'      => get_template_directory_uri() . '/assets/images/shop/radiant-sun-shield.webp',
		'img_gallery'   => array( get_template_directory_uri() . '/assets/images/shop/radiant-sun-shield.webp' ),
		'benefits'      => array( 'Broad spectrum SPF 50 PA++++', 'No white cast formula', 'Adds radiant glow finish' ),
		'badges_extra'  => array( 'SPF 50', 'Vegan & Cruelty-Free' ),
	),
	'blushbloome-petal-blush' => array(
		'name'          => 'BlushBloome Petal Blush',
		'price'         => '$42.00',
		'tags'          => 'GLOW • FLUSH • RADIANT',
		'badge'         => 'New',
		'badge_pos'     => 'right',
		'category'      => 'Blush',
		'category_slug' => 'blush',
		'rating'        => 5.0,
		'review_count'  => 29,
		'short_desc'    => 'A silky soft blush powder by BlushBloome that delivers a natural rosy flush, buildable color, and a luminous petal-like glow on every skin tone.',
		'description'   => 'BlushBloome Petal Blush is crafted with ultra-fine pigments and skin-loving botanicals to give cheeks a fresh, healthy-looking flush that lasts all day without caking or fading.',
		'ingredients'   => 'Rose Petal Extract, Mica, Vitamin E, Jojoba Seed Oil, Silk Powder, Camellia Extract, Hyaluronic Acid.',
		'how_to_use'    => array(
			array( 'step' => '1', 'title' => 'Prep',    'desc' => 'Apply after foundation or BB cream on clean skin.' ),
			array( 'step' => '2', 'title' => 'Sweep',   'desc' => 'Smile and sweep blush brush upward along cheekbones.' ),
			array( 'step' => '3', 'title' => 'Blend',   'desc' => 'Blend edges softly for a natural sun-kissed flush.' ),
		),
		'sizes'         => array( '8g', '15g' ),
		'img_main'      => get_template_directory_uri() . '/assets/images/shop/blushbloome-petal-blush.webp',
		'img_gallery'   => array( get_template_directory_uri() . '/assets/images/shop/blushbloome-petal-blush.webp' ),
		'benefits'      => array( 'Natural rosy flush all day', 'Buildable & blendable color', 'Suits every skin tone' ),
		'badges_extra'  => array( 'New', 'Vegan & Cruelty-Free' ),
	),
	'browluxe-define-brow-mascara' => array(
		'name'          => 'BrowLuxe Define Brow Mascara',
		'price'         => '$28.00',
		'tags'          => 'DEFINE • FILL • HOLD',
		'badge'         => 'BESTSELLER',
		'badge_pos'     => 'left',
		'category'      => 'Brows',
		'category_slug' => 'brows',
		'rating'        => 4.0,
		'review_count'  => 67,
		'short_desc'    => 'A precision brow mascara by BrowLuxe that defines, fills, and tames brows with a natural feathery finish that lasts all day.',
		'description'   => 'BrowLuxe Define Brow Mascara features a micro-flex brush that coats every brow hair evenly, delivering buildable color and flexible hold without flaking or stiffness.',
		'ingredients'   => 'Aqua, Beeswax, Carnauba Wax, Panthenol, Vitamin E, Keratin Protein, Castor Oil.',
		'how_to_use'    => array(
			array( 'step' => '1', 'title' => 'Brush',  'desc' => 'Comb brows upward with spoolie before application.' ),
			array( 'step' => '2', 'title' => 'Apply',  'desc' => 'Stroke mascara wand through brows following natural hair direction.' ),
			array( 'step' => '3', 'title' => 'Define', 'desc' => 'Use tip of wand to fill sparse areas for fuller brows.' ),
		),
		'sizes'         => array( '4ml' ),
		'img_main'      => get_template_directory_uri() . '/assets/images/shop/browluxe-define-brow-mascara.webp',
		'img_gallery'   => array( get_template_directory_uri() . '/assets/images/shop/browluxe-define-brow-mascara.webp' ),
		'benefits'      => array( 'Defines & fills brows naturally', 'All-day flexible hold', 'No flaking or stiffness' ),
		'badges_extra'  => array( 'BESTSELLER', 'Vegan & Cruelty-Free' ),
	),
	'solarshield-ultra-sunscreen' => array(
		'name'          => 'SolarShield Ultra Sunscreen',
		'price'         => '$36.00',
		'tags'          => 'PROTECT • SHIELD • BRIGHTEN',
		'badge'         => 'SPF 50+',
		'badge_pos'     => 'left',
		'category'      => 'Sunscreen',
		'category_slug' => 'sunscreen',
		'rating'        => 5.0,
		'review_count'  => 145,
		'short_desc'    => 'A lightweight daily sunscreen by SolarShield with SPF 50+ that protects against UVA & UVB rays while leaving skin bright, smooth, and non-greasy.',
		'description'   => 'SolarShield Ultra Sunscreen combines broad-spectrum SPF 50+ protection with niacinamide and hyaluronic acid to shield skin from sun damage while hydrating and evening skin tone all day long.',
		'ingredients'   => 'Zinc Oxide, Titanium Dioxide, Niacinamide, Hyaluronic Acid, Vitamin C, Aloe Vera Leaf Extract, Green Tea Extract.',
		'how_to_use'    => array(
			array( 'step' => '1', 'title' => 'Apply',   'desc' => 'Apply generously as the last step of morning skincare.' ),
			array( 'step' => '2', 'title' => 'Blend',   'desc' => 'Spread evenly over face and neck until fully absorbed.' ),
			array( 'step' => '3', 'title' => 'Reapply', 'desc' => 'Reapply every 2 hours when exposed to sunlight.' ),
		),
		'sizes'         => array( '30ml', '50ml' ),
		'img_main'      => get_template_directory_uri() . '/assets/images/shop/solarshield-ultra-sunscreen.webp',
		'img_gallery'   => array( get_template_directory_uri() . '/assets/images/shop/solarshield-ultra-sunscreen.webp' ),
		'benefits'      => array( 'Broad-spectrum SPF 50+ protection', 'Lightweight & non-greasy formula', 'Brightens & evens skin tone' ),
		'badges_extra'  => array( 'SPF 50+', 'Vegan & Cruelty-Free' ),
	),
	'clearderm-acne-rescue-cream' => array(
		'name'          => 'ClearDerm Acne Rescue Cream',
		'price'         => '$32.00',
		'tags'          => 'CLEAR • HEAL • SOOTHE',
		'badge'         => 'ANTI-ACNE',
		'badge_pos'     => 'left',
		'category'      => 'Acne Care',
		'category_slug' => 'acne-care',
		'rating'        => 4.5,
		'review_count'  => 53,
		'short_desc'    => 'A targeted acne rescue cream by ClearDerm that clears breakouts, reduces redness, and soothes irritated skin without over-drying.',
		'description'   => 'ClearDerm Acne Rescue Cream combines salicylic acid, tea tree oil, and centella asiatica to effectively target acne at the source, calm inflammation, and prevent future breakouts while keeping skin balanced and hydrated.',
		'ingredients'   => 'Salicylic Acid (2%), Tea Tree Oil, Centella Asiatica, Niacinamide, Zinc PCA, Aloe Vera Leaf Extract, Hyaluronic Acid.',
		'how_to_use'    => array(
			array( 'step' => '1', 'title' => 'Cleanse', 'desc' => 'Apply to freshly cleansed and toned skin.' ),
			array( 'step' => '2', 'title' => 'Target',  'desc' => 'Dab a small amount directly onto blemishes and acne-prone areas.' ),
			array( 'step' => '3', 'title' => 'Heal',    'desc' => 'Use morning and night for best results. Follow with moisturizer.' ),
		),
		'sizes'         => array( '20ml', '50ml' ),
		'img_main'      => get_template_directory_uri() . '/assets/images/shop/clearderm-acne-rescue-cream.webp',
		'img_gallery'   => array( get_template_directory_uri() . '/assets/images/shop/clearderm-acne-rescue-cream.webp' ),
		'benefits'      => array( 'Clears breakouts fast', 'Reduces redness & inflammation', 'Prevents future acne' ),
		'badges_extra'  => array( 'ANTI-ACNE', 'Vegan & Cruelty-Free' ),
	),
);

// ── Shared helper: render 5-star rating (Material Symbols, golden color) ──
// Used by both the shop grid (via dg_render_stars fallback) and the detail page.
if ( ! function_exists( 'dg_mock_stars' ) ) :
	function dg_mock_stars( float $rating, string $size = '20px' ): string {
		$html = '<div class="flex items-center gap-0.5 dg-stars">';
		for ( $s = 1; $s <= 5; $s++ ) {
			$fill  = ( $s <= floor( $rating ) ) ? '1' : ( ( $s - 0.5 <= $rating ) ? '0.5' : '0' );
			$style = sprintf( 'color:#f1ca50;font-size:%s;font-variation-settings:\'FILL\' %s;', esc_attr( $size ), esc_attr( $fill ) );
			$html .= sprintf(
				'<span class="material-symbols-outlined" style="%s">star</span>',
				$style
			);
		}
		$html .= '</div>';
		return $html;
	}
endif;

// ── Shared helper: load detail shots from assets/images/details/{slug}/ ──
// Returns an array of URLs for shots 1–4 in priority order jpg > webp > jpeg > png.
// $slug defaults to '' so the function never throws a TypeError even if called with null.
if ( ! function_exists( 'dg_mock_detail_shots' ) ) :
	function dg_mock_detail_shots( string $slug = '' ): array {
		if ( empty( $slug ) ) {
			return array();
		}
		$detail_dir  = get_template_directory() . '/assets/images/details/' . $slug . '/';
		$detail_url  = get_template_directory_uri() . '/assets/images/details/' . $slug . '/';
		$detail_exts = array( 'jpg', 'webp', 'jpeg', 'png' );
		$shots       = array();

		for ( $n = 1; $n <= 4; $n++ ) {
			foreach ( $detail_exts as $ext ) {
				$file = $detail_dir . 'shot' . $n . '.' . $ext;
				if ( file_exists( $file ) ) {
					$shots[] = $detail_url . 'shot' . $n . '.' . $ext;
					break;
				}
			}
		}

		return $shots;
	}
endif;
