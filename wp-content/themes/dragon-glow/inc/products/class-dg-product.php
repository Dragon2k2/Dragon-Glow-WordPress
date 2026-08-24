<?php
/**
 * Dragon Glow — Product Value Object
 *
 * Canonical data contract returned by both Mock and WooCommerce repositories.
 * Every product, regardless of source, normalises to this shape so the
 * rest of the codebase (checkout router, templates, JS) never needs to
 * branch on the product type.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

class DG_Product {

	/** @var int|string */
	private $id;

	/** @var string */
	private $slug;

	/** @var string */
	private $name;

	/** @var float */
	private $price;

	/** @var string */
	private $price_formatted;

	/** @var string */
	private $short_desc;

	/** @var string */
	private $description;

	/** @var string */
	private $category;

	/** @var string */
	private $category_slug;

	/** @var string */
	private $image_url;

	/** @var string[] */
	private $gallery_urls;

	/** @var string[] */
	private $sizes;

	/** @var float */
	private $rating;

	/** @var int */
	private $review_count;

	/** @var string */
	private $badge;

	/** @var string */
	private $badge_pos;

	/** @var string */
	private $source; // 'mock' | 'woocommerce'

	/** @var mixed */
	private $source_object; // WC_Product | array

	/**
	 * Constructor.
	 *
	 * @param array $args Normalised product data.
	 */
	public function __construct( array $args = array() ) {
		$this->id             = $args['id'] ?? 0;
		$this->slug           = $args['slug'] ?? '';
		$this->name           = $args['name'] ?? '';
		$this->price          = (float) ( $args['price'] ?? 0 );
		$this->price_formatted = $args['price_formatted'] ?? '';
		$this->short_desc    = $args['short_desc'] ?? '';
		$this->description   = $args['description'] ?? '';
		$this->category      = $args['category'] ?? '';
		$this->category_slug = $args['category_slug'] ?? '';
		$this->image_url     = $args['image_url'] ?? '';
		$this->gallery_urls  = (array) ( $args['gallery_urls'] ?? array() );
		$this->sizes         = (array) ( $args['sizes'] ?? array() );
		$this->rating         = (float) ( $args['rating'] ?? 0 );
		$this->review_count   = (int) ( $args['review_count'] ?? 0 );
		$this->badge         = $args['badge'] ?? '';
		$this->badge_pos     = $args['badge_pos'] ?? 'left';
		$this->source         = $args['source'] ?? 'mock';
		$this->source_object  = $args['source_object'] ?? null;
	}

	public function get_id(): string {
		return (string) $this->id;
	}

	public function get_slug(): string {
		return $this->slug;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_price(): float {
		return $this->price;
	}

	public function get_price_formatted(): string {
		return $this->price_formatted;
	}

	public function get_short_desc(): string {
		return $this->short_desc;
	}

	public function get_description(): string {
		return $this->description;
	}

	public function get_category(): string {
		return $this->category;
	}

	public function get_category_slug(): string {
		return $this->category_slug;
	}

	public function get_image_url(): string {
		return $this->image_url;
	}

	public function get_gallery_urls(): array {
		return $this->gallery_urls;
	}

	public function get_sizes(): array {
		return $this->sizes;
	}

	public function has_sizes(): bool {
		return ! empty( $this->sizes );
	}

	public function get_rating(): float {
		return $this->rating;
	}

	public function get_review_count(): int {
		return $this->review_count;
	}

	public function get_badge(): string {
		return $this->badge;
	}

	public function get_badge_pos(): string {
		return $this->badge_pos;
	}

	/**
	 * Product source: 'mock' or 'woocommerce'.
	 *
	 * @return string
	 */
	public function get_source(): string {
		return $this->source;
	}

	public function is_mock(): bool {
		return 'mock' === $this->source;
	}

	public function is_woocommerce(): bool {
		return 'woocommerce' === $this->source;
	}

	/**
	 * Returns the raw underlying object/array.
	 *
	 * @return mixed
	 */
	public function get_source_object() {
		return $this->source_object;
	}

	/**
	 * Serialise to array — useful for passing to templates or JSON.
	 *
	 * @return array
	 */
	public function to_array(): array {
		return get_object_vars( $this );
	}
}
