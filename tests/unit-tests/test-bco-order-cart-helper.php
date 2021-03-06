<?php // phpcs:ignore
/**
 *
 * Test_BCO_Order_Cart_Helper class
 *
 * @package category
 */
/**
 * Test_BCO_Order_Cart_Helper class
 */
class Test_BCO_Order_Cart_Helper extends AKrokedil_Unit_Test_Case {
	/**
	 * Tax rate ids.
	 *
	 * @var array
	 */
	public $tax_rate_ids = array();
	/**
	 * Tax rate ids.
	 *
	 * @var array
	 */
	public $tax_classes = array();
	/**
	 * Orders.
	 *
	 * @var array
	 */
	public $orders = array();

	// Handling.
	/**
	 * Test BCO_Order_Cart_Helper::get_handling_without_tax
	 *
	 * @return void
	 */
	public function test_get_handling_without_tax() {
		$this->create_order( '25' );
		$this->assertEquals( 8000, ( new BCO_Order_Cart_Helper() )->get_handling_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12' );
		$this->assertEquals( 8929, ( new BCO_Order_Cart_Helper() )->get_handling_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6' );
		$this->assertEquals( 9434, ( new BCO_Order_Cart_Helper() )->get_handling_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '25', false );
		$this->assertEquals( 10000, ( new BCO_Order_Cart_Helper() )->get_handling_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12', false );
		$this->assertEquals( 10000, ( new BCO_Order_Cart_Helper() )->get_handling_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6', false );
		$this->assertEquals( 10000, ( new BCO_Order_Cart_Helper() )->get_handling_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	}

	/**
	 * Test BCO_Order_Cart_Helper::get_handling_tax_rate
	 *
	 * @return void
	 */
	public function test_get_handling_tax_rate() {
		$this->create_order( '25' );
		$this->assertEquals( 25, ( new BCO_Order_Cart_Helper() )->get_handling_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12' );
		$this->assertEquals( 12, ( new BCO_Order_Cart_Helper() )->get_handling_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6' );
		$this->assertEquals( 6, ( new BCO_Order_Cart_Helper() )->get_handling_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '0' );
		$this->assertEquals( 0, ( new BCO_Order_Cart_Helper() )->get_handling_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '25', false );
		$this->assertEquals( 25, ( new BCO_Order_Cart_Helper() )->get_handling_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12', false );
		$this->assertEquals( 12, ( new BCO_Order_Cart_Helper() )->get_handling_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6', false );
		$this->assertEquals( 6, ( new BCO_Order_Cart_Helper() )->get_handling_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '0', false );
		$this->assertEquals( 0, ( new BCO_Order_Cart_Helper() )->get_handling_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	}

	// Shipping.
	/**
	 * Test BCO_Order_Cart_Helper::get_shipping_without_tax
	 *
	 * @return void
	 */
	public function test_get_shipping_without_tax() {
		$this->create_order( '25' );
		$this->create_shipping( 25 );
		$this->assertEquals( 4000, ( new BCO_Order_Cart_Helper() )->get_shipping_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12' );
		$this->create_shipping( 12 );
		$this->assertEquals( 4000, ( new BCO_Order_Cart_Helper() )->get_shipping_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6' );
		$this->create_shipping( 6 );
		$this->assertEquals( 4000, ( new BCO_Order_Cart_Helper() )->get_shipping_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '25', false );
		$this->create_shipping();
		$this->assertEquals( 4000, ( new BCO_Order_Cart_Helper() )->get_shipping_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12', false );
		$this->create_shipping();
		$this->assertEquals( 4000, ( new BCO_Order_Cart_Helper() )->get_shipping_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6', false );
		$this->create_shipping();
		$this->assertEquals( 4000, ( new BCO_Order_Cart_Helper() )->get_shipping_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	}

	/**
	 * Test BCO_Order_Cart_Helper::get_shipping_tax_rate
	 *
	 * @return void
	 */
	public function test_get_shipping_tax_rate() {
		$this->create_order( '25' );
		$this->create_shipping( 25 );
		$this->assertEquals( 25, ( new BCO_Order_Cart_Helper() )->get_shipping_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12' );
		$this->create_shipping( 12 );
		$this->assertEquals( 12, ( new BCO_Order_Cart_Helper() )->get_shipping_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6' );
		$this->create_shipping( 6 );
		$this->assertEquals( 6, ( new BCO_Order_Cart_Helper() )->get_shipping_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '25', false );
		$this->create_shipping();
		$this->assertEquals( 25, ( new BCO_Order_Cart_Helper() )->get_shipping_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12', false );
		$this->create_shipping();
		$this->assertEquals( 12, ( new BCO_Order_Cart_Helper() )->get_shipping_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6', false );
		$this->create_shipping();
		$this->assertEquals( 6, ( new BCO_Order_Cart_Helper() )->get_shipping_tax_rate( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

	}

	// Total.
	/**
	 * Test BCO_Order_Cart_Helper::get_total_without_tax
	 *
	 * @return void
	 */
	public function test_get_total_without_tax() {
		$this->create_order( '25' );
		$this->assertEquals( 8000, ( new BCO_Order_Cart_Helper() )->get_total_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12' );
		$this->assertEquals( 8929, ( new BCO_Order_Cart_Helper() )->get_total_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6' );
		$this->assertEquals( 9434, ( new BCO_Order_Cart_Helper() )->get_total_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '25', false );
		$this->assertEquals( 10000, ( new BCO_Order_Cart_Helper() )->get_total_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12', false );
		$this->assertEquals( 10000, ( new BCO_Order_Cart_Helper() )->get_total_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6', false );
		$this->assertEquals( 10000, ( new BCO_Order_Cart_Helper() )->get_total_without_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	}

	/**
	 * Test BCO_Order_Cart_Helper::get_total_tax
	 *
	 * @return void
	 */
	public function test_get_total_tax() {
		$this->create_order( '25' );
		$this->assertEquals( 2000, ( new BCO_Order_Cart_Helper() )->get_total_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12' );
		$this->assertEquals( 1071, ( new BCO_Order_Cart_Helper() )->get_total_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6' );
		$this->assertEquals( 566, ( new BCO_Order_Cart_Helper() )->get_total_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '0' );
		$this->assertEquals( 0, ( new BCO_Order_Cart_Helper() )->get_total_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '25', false );
		$this->assertEquals( 2500, ( new BCO_Order_Cart_Helper() )->get_total_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12', false );
		$this->assertEquals( 1200, ( new BCO_Order_Cart_Helper() )->get_total_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6', false );
		$this->assertEquals( 600, ( new BCO_Order_Cart_Helper() )->get_total_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '0', false );
		$this->assertEquals( 0, ( new BCO_Order_Cart_Helper() )->get_total_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	}

	/**
	 * Test BCO_Order_Cart_Helper::get_total_with_tax
	 *
	 * @return void
	 */
	public function test_get_total_with_tax() {
		$this->create_order( '25' );
		$this->assertEquals( 10000, ( new BCO_Order_Cart_Helper() )->get_total_with_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12' );
		$this->assertEquals( 10000, ( new BCO_Order_Cart_Helper() )->get_total_with_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6' );
		$this->assertEquals( 10000, ( new BCO_Order_Cart_Helper() )->get_total_with_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '0' );
		$this->assertEquals( 10000, ( new BCO_Order_Cart_Helper() )->get_total_with_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '25', false );
		$this->assertEquals( 12500, ( new BCO_Order_Cart_Helper() )->get_total_with_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12', false );
		$this->assertEquals( 11200, ( new BCO_Order_Cart_Helper() )->get_total_with_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6', false );
		$this->assertEquals( 10600, ( new BCO_Order_Cart_Helper() )->get_total_with_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '0', false );
		$this->assertEquals( 10000, ( new BCO_Order_Cart_Helper() )->get_total_with_tax( $this->order ) );
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	}

	/**
	 * Creates data for tests.
	 *
	 * @return void
	 */
	public function create() {
		update_option( 'woocommerce_calc_taxes', 'yes' );
		update_option( 'woocommerce_prices_include_tax', 'yes' );

		// Create tax rates.
		$this->tax_rate_ids[] = $this->create_tax_rate( '25' );
		$this->tax_rate_ids[] = $this->create_tax_rate( '12' );
		$this->tax_rate_ids[] = $this->create_tax_rate( '6' );
		$this->product        = ( new Krokedil_Simple_Product() )->create();
	}

	/**
	 * Updates data for tests.
	 *
	 * @return void
	 */
	public function update() {
	}

	/**
	 * Gets data for tests.
	 *
	 * @return void
	 */
	public function view() {
	}


	/**
	 * Resets needed data for tests.
	 *
	 * @return void
	 */
	public function delete() {
		global $wpdb;
		$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'woocommerce_tax_rates' );// phpcs:ignore
		$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'wc_tax_rate_classes' );// phpcs:ignore
		$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'woocommerce_order_items' );// phpcs:ignore
		$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'woocommerce_order_itemmeta' );// phpcs:ignore
		$this->order        = null;
		$this->product      = null;
		$this->tax_rate_ids = array();
	}

	/**
	 * Create order.
	 *
	 * @param string  $tax_rate tax rate.
	 * @param boolean $inc_tax inclusive tax.
	 * @return void
	 */
	public function create_order( $tax_rate, $inc_tax = true ) {
		$this->product->set_tax_class( $tax_rate . 'percent' );
		$this->product->save();
		if ( $inc_tax ) {
			update_option( 'woocommerce_prices_include_tax', 'yes' );
		} else {
			update_option( 'woocommerce_prices_include_tax', 'no' );
		}

		$order = wc_create_order();
		$order->add_product( $this->product );
		$order->calculate_totals();
		$order->save();
		$this->order = $order;
	}


	/**
	 * Create shipping.
	 *
	 * @param int $tax total tax amount.
	 * @return void
	 */
	public function create_shipping( $tax = 0 ) {
		$data                = array(
			'total' => 40,
		);
		$order_item_shipping = ( new Krokedil_Order_Item_Shipping( $data ) )->get_order_items_shipping();
		$order_item_shipping->set_props( array( 'shipping_tax' => $tax ) );

		$this->order->add_item( $order_item_shipping );
		$this->order->calculate_totals();
		$this->order->save();
	}

	/**
	 * Helper to create tax rates and class.
	 *
	 * @param string $rate The tax rate.
	 * @return int
	 */
	public function create_tax_rate( $rate ) {
		// Create the tax class.

		$this->tax_classes[] = WC_Tax::create_tax_class( "${rate}percent", "${rate}percent" );

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

}

