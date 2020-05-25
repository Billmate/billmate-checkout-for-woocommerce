<?php // phpcs:ignore
/**
 *
 * Test_BCO_Order_Articles_Helper class
 *
 * @package category
 */
/**
 * Test_BCO_Order_Articles_Helper class
 */
class Test_BCO_Order_Articles_Helper extends AKrokedil_Unit_Test_Case {
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

	/**
	 * Test BCO_Order_Articles_Helper::get_article_number
	 *
	 * @return void
	 */
	public function test_get_article_number_with_sku() {
		$this->create_order( '25' );
		foreach ( $this->order->get_items() as $item ) {
			$product = $item->get_product();
			$product->set_sku( 'SKU123' );
			$product->save();
			$article_number = ( new BCO_Order_Articles_Helper() )->get_article_number( $item );
			$this->assertEquals( 'SKU123', $article_number );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	}

	/**
	 * Test BCO_Order_Articles_Helper::get_article_number
	 *
	 * @return void
	 */
	public function test_get_article_number_without_sku() {
		$this->create_order( '25' );
		foreach ( $this->order->get_items() as $item ) {
			$product        = $item->get_product();
			$article_number = ( new BCO_Order_Articles_Helper() )->get_article_number( $item );
			$this->assertEquals( $product->get_id(), $article_number );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	}

	/**
	 * Test BCO_Order_Articles_Helper::get_title
	 *
	 * @return void
	 */
	public function test_get_title() {
		$this->create_order( '25' );
		foreach ( $this->order->get_items() as $item ) {
			$title = ( new BCO_Order_Articles_Helper() )->get_title( $item );
			$this->assertEquals( 'Default product name', $title );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	}

	/**
	 * Test BCO_Order_Articles_Helper::get_quantity
	 *
	 * @return void
	 */
	public function test_get_quantity() {
		$this->create_order( '25' );
		foreach ( $this->order->get_items() as $item ) {
			$quantity = ( new BCO_Order_Articles_Helper() )->get_quantity( $item );
			$this->assertEquals( 1, $quantity );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	}

	/**
	 * Test BCO_Order_Articles_Helper::get_article_price
	 *
	 * @return void
	 */
	public function test_get_article_price() {
		// Create tax rates.
		$this->tax_rate_ids[] = $this->create_tax_rate( '25' );
		$this->tax_rate_ids[] = $this->create_tax_rate( '12' );
		$this->tax_rate_ids[] = $this->create_tax_rate( '6' );

		$this->create_order( '25' );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 8000, ( new BCO_Order_Articles_Helper() )->get_article_price( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12' );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 8929, ( new BCO_Order_Articles_Helper() )->get_article_price( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6' );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 9434, ( new BCO_Order_Articles_Helper() )->get_article_price( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '25', false );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 10000, ( new BCO_Order_Articles_Helper() )->get_article_price( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12', false );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 10000, ( new BCO_Order_Articles_Helper() )->get_article_price( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6', false );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 10000, ( new BCO_Order_Articles_Helper() )->get_article_price( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	}

	/*
	 public function test_get_discount() {
		$this->create_order( '25' );
		$this->create_coupon( 'testcode123', 10, 25 );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 8000, ( new BCO_Order_Articles_Helper() )->get_discount( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	} */

	/**
	 * Test BCO_Order_Articles_Helper::get_without_tax
	 *
	 * @return void
	 */
	public function test_get_without_tax() {
		$this->create_order( '25' );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 8000, ( new BCO_Order_Articles_Helper() )->get_without_tax( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12' );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 8929, ( new BCO_Order_Articles_Helper() )->get_without_tax( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6' );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 9434, ( new BCO_Order_Articles_Helper() )->get_without_tax( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '25', false );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 10000, ( new BCO_Order_Articles_Helper() )->get_without_tax( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12', false );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 10000, ( new BCO_Order_Articles_Helper() )->get_without_tax( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6', false );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 10000, ( new BCO_Order_Articles_Helper() )->get_without_tax( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	}

	/**
	 * Test BCO_Order_Articles_Helper::get_tax_rate
	 *
	 * @return void
	 */
	public function test_get_tax_rate() {
		$this->create_order( '25' );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 25, ( new BCO_Order_Articles_Helper() )->get_tax_rate( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12' );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 12, ( new BCO_Order_Articles_Helper() )->get_tax_rate( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6' );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 6, ( new BCO_Order_Articles_Helper() )->get_tax_rate( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '0' );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 0, ( new BCO_Order_Articles_Helper() )->get_tax_rate( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '25', false );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 25, ( new BCO_Order_Articles_Helper() )->get_tax_rate( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '12', false );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 12, ( new BCO_Order_Articles_Helper() )->get_tax_rate( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '6', false );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 6, ( new BCO_Order_Articles_Helper() )->get_tax_rate( $item ) );
		}
		wp_delete_post( $this->order->get_id() );
		$this->order = null;

		$this->create_order( '0', false );
		foreach ( $this->order->get_items() as $item ) {
			$this->assertEquals( 0, ( new BCO_Order_Articles_Helper() )->get_tax_rate( $item ) );
		}
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

	/*
	 public function create_coupon( $code = array(), $discount = 0, $discount_tax = 0 ) {
		$item = new WC_Order_Item_Coupon();
		$item->set_props(
			array(
				'code'         => $code,
				'discount'     => $discount,
				'discount_tax' => $discount_tax,
				'order_id'     => $this->order->get_id(),
			)
		);
		$item->save();
		$this->order->add_item( $item );
	} */
}

