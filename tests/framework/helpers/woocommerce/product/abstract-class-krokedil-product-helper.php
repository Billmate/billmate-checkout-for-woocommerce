<?php // phpcs:ignore
/**
 * Helper product class
 */

/**
 * This is the class just for testing purpose
 *
 * @package Krokedil/tests
 */
/**
 * Abstract helper class for product.
 */
abstract class AKrokedil_WC_Product implements IKrokedil_WC_Product {
	/**
	 * Stores product data.
	 *
	 * @var array
	 */
	protected $data = array(
		'name'               => 'Default product name',
		'slug'               => '',
		'date_created'       => null,
		'date_modified'      => null,
		'status'             => false,
		'featured'           => false,
		'catalog_visibility' => 'visible',
		'description'        => '',
		'short_description'  => '',
		'sku'                => '',
		'price'              => '',
		'regular_price'      => '150',
		'sale_price'         => '100',
		'date_on_sale_from'  => null,
		'date_on_sale_to'    => null,
		'total_sales'        => '0',
		'tax_status'         => 'taxable',
		'tax_class'          => '',
		'manage_stock'       => false,
		'stock_quantity'     => null,
		'stock_status'       => 'instock',
		'backorders'         => 'no',
		'low_stock_amount'   => '',
		'sold_individually'  => false,
		'weight'             => '',
		'length'             => '',
		'width'              => '',
		'height'             => '',
		'upsell_ids'         => array(),
		'cross_sell_ids'     => array(),
		'parent_id'          => 0,
		'reviews_allowed'    => true,
		'purchase_note'      => '',
		'attributes'         => array(),
		'default_attributes' => array(),
		'menu_order'         => 0,
		'post_password'      => '',
		'virtual'            => false,
		'downloadable'       => false,
		'category_ids'       => array(),
		'tag_ids'            => array(),
		'shipping_class_id'  => 0,
		'downloads'          => array(),
		'image_id'           => '',
		'gallery_image_ids'  => array(),
		'download_limit'     => -1,
		'download_expiry'    => -1,
		'rating_counts'      => array(),
		'average_rating'     => 0,
		'review_count'       => 0,
	);

	/**
	 * AKrokedil_WC_Product_Helper constructor.
	 *
	 * @param array $data data.
	 */
	public function __construct( array $data = array() ) {
		$this->data = wp_parse_args( $data, $this->data );
	}

	/**
	 * Returns data.
	 *
	 * @return array data.
	 */
	final public function get_data() : array {
		return $this->data;
	}
}
