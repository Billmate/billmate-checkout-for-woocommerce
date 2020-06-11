<?php // phpcs:ignore
/**
 *
 * Test_BCO_Cart_Cart_Helper class
 *
 * @package category
 */
/**
 * Test_BCO_Cart_Cart_Helper class
 */
class Test_BCO_Cart_Cart_Helper extends AKrokedil_Unit_Test_Case {


	/**
	 * WooCommerce simple product.
	 *
	 * @var WC_Product
	 */
	public $simple_product = null;

	/**
	 * Tax rate ids.
	 *
	 * @var array
	 */
	public $tax_rate_ids = array();

	/**
	 * Test BCO_Cart_Cart_Helper::get_without_tax
	 *
	 * @return void
	 */
	public function test_get_handling_without_tax() {
		// Create tax rates.
		$this->tax_rate_ids[] = $this->create_tax_rate( '25' );
		$this->tax_rate_ids[] = $this->create_tax_rate( '12' );
		$this->tax_rate_ids[] = $this->create_tax_rate( '6' );

		// With tax.
		update_option( 'woocommerce_prices_include_tax', 'yes' );
		// 25% inc tax.
		$this->setup_cart( '25' );
		$cart_items = WC()->cart->get_cart();

		foreach ( $cart_items as $cart_item ) {
			$item_price_25_inc = BCO_Cart_Cart_Helper::get_handling_without_tax( $cart_item );
		}
		WC()->cart->empty_cart();

		// 12% inc tax.
		$this->setup_cart( '12' );
		$cart_items = WC()->cart->get_cart();
		foreach ( $cart_items as $cart_item ) {
			$item_price_12_inc = BCO_Cart_Cart_Helper::get_handling_without_tax( $cart_item );
		}
		WC()->cart->empty_cart();

		// 6% inc tax.
		$this->setup_cart( '6' );
		$cart_items = WC()->cart->get_cart();
		foreach ( $cart_items as $cart_item ) {
			$item_price_6_inc = BCO_Cart_Cart_Helper::get_handling_without_tax( $cart_item );
		}
		WC()->cart->empty_cart();

		// Without tax.
		update_option( 'woocommerce_prices_include_tax', 'no' );
		// 25% exc tax.
		$this->setup_cart( '25' );
		$cart_items = WC()->cart->get_cart();
		foreach ( $cart_items as $cart_item ) {
			$item_price_25_exc = BCO_Cart_Cart_Helper::get_handling_without_tax( $cart_item );
		}
		WC()->cart->empty_cart();

		// 12% exc tax.
		$this->setup_cart( '12' );
		$cart_items = WC()->cart->get_cart();
		foreach ( $cart_items as $cart_item ) {
			$item_price_12_exc = BCO_Cart_Cart_Helper::get_handling_without_tax( $cart_item );
		}
		WC()->cart->empty_cart();

		// 6% exc tax.
		$this->setup_cart( '6' );
		$cart_items = WC()->cart->get_cart();
		foreach ( $cart_items as $cart_item ) {
			$item_price_6_exc = BCO_Cart_Cart_Helper::get_handling_without_tax( $cart_item );
		}
		WC()->cart->empty_cart();

		// Assertions.
		$this->assertEquals( 8000, $item_price_25_inc, 'get_handling_without_tax 25% inc tax' );
		$this->assertEquals( 8929, $item_price_12_inc, 'get_handling_without_tax 12% inc tax' );
		$this->assertEquals( 9434, $item_price_6_inc, 'get_handling_without_tax 6% inc tax' );
		$this->assertEquals( 10000, $item_price_25_exc, 'get_handling_without_tax 25% exc tax' );
		$this->assertEquals( 10000, $item_price_12_exc, 'get_handling_without_tax 12% exc tax' );
		$this->assertEquals( 10000, $item_price_6_exc, 'get_handling_without_tax 6% exc tax' );
	}

	/**
	 * Creates data for tests.
	 *
	 * @return void
	 */
	public function create() {
		$this->simple_product = ( new Krokedil_Simple_Product() )->create();

		// Default settings.
		update_option( 'woocommerce_calc_taxes', 'yes' );
	}

	/**
	 * Updates data for tests.
	 *
	 * @return void
	 */
	public function update() {
		return;
	}

	/**
	 * Gets data for tests.
	 *
	 * @return void
	 */
	public function view() {
		return;
	}


	/**
	 * Resets needed data for tests.
	 *
	 * @return void
	 */
	public function delete() {
		$this->simple_product->delete();
		$this->simple_product = null;
		foreach ( $this->tax_rate_ids as $tax_rate_id ) {
			WC_Tax::_delete_tax_rate( $tax_rate_id );
		}
		$this->tax_rate_ids = null;
	}


	/**
	 * Helper to create tax rates and class.
	 *
	 * @param string $rate The tax rate.
	 * @return int
	 */
	public function create_tax_rate( $rate ) {
		// Create the tax class.
		WC_Tax::create_tax_class( "${rate}percent", "${rate}percent" );

		// Set tax data.
		$tax_data = array(
			'tax_rate_country'  => '',
			'tax_rate_state'    => '',
			'tax_rate'          => $rate,
			'tax_rate_name'     => "Vat $rate",
			'tax_rate_priority' => 1,
			'tax_rate_compound' => 0,
			'tax_rate_shipping' => 1,
			'tax_rate_order'    => 1,
			'tax_rate_class'    => "${rate}percent",
		);
		return WC_Tax::_insert_tax_rate( $tax_data );
	}

	/**
	 * Helper function to get product from cart item.
	 *
	 * @param array $cart_item The WooCommerce cart item.
	 * @return WC_Product
	 */
	public function get_product( $cart_item ) {
		if ( $cart_item['variation_id'] ) {
			$product = wc_get_product( $cart_item['variation_id'] );
		} else {
			$product = wc_get_product( $cart_item['product_id'] );
		}

		return $product;
	}

	/**
	 * Sets up the cart for the test.
	 *
	 * @param string      $tax_rate The tax rate to be used.
	 * @param string|bool $shipping The shipping to be used.
	 * @return void
	 */
	public function setup_cart( $tax_rate, $shipping = false ) {
		$this->simple_product->set_tax_class( $tax_rate . 'percent' );
		$this->simple_product->save();
		WC()->cart->add_to_cart( $this->simple_product->get_id(), 1 );
		if ( false !== $shipping ) {
			WC()->session->set( 'chosen_shipping_methods', array( $shipping ) );
		}
		WC()->cart->calculate_totals();
	}
}
