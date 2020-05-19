<?php // phpcs:ignore
/**
 *
 * Test_BCO_Customer_Helper class
 *
 * @package category
 */
/**
 * Test_BCO_Customer_Helper class
 */
class Test_BCO_Customer_Helper extends AKrokedil_Unit_Test_Case {
	/**
	 * Test BCO_Customer_Helper::get_billing_first_name
	 *
	 * @return void
	 */
	public function test_get_billing_first_name() {
		$this->assertEquals( 'Billingfirstname', BCO_Customer_Helper::get_billing_first_name( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_billing_last_name
	 *
	 * @return void
	 */
	public function test_get_billing_last_name() {
		$this->assertEquals( 'Billinglastname', BCO_Customer_Helper::get_billing_last_name( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_billing_company
	 *
	 * @return void
	 */
	public function test_get_billing_company() {
		$this->assertEquals( 'Billingcompany', BCO_Customer_Helper::get_billing_company( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_billing_company
	 *
	 * @return void
	 */
	public function test_get_billing_address_1() {
		$this->assertEquals( 'Billingaddress 1', BCO_Customer_Helper::get_billing_address_1( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_billing_address_2
	 *
	 * @return void
	 */
	public function test_get_billing_address2() {
		$this->assertEquals( 'Billingaddress 2', BCO_Customer_Helper::get_billing_address_2( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_billing_postcode
	 *
	 * @return void
	 */
	public function test_get_billing_postcode() {
		$this->assertEquals( '12345', BCO_Customer_Helper::get_billing_postcode( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_billing_city
	 *
	 * @return void
	 */
	public function test_get_billing_city() {
		$this->assertEquals( 'Billingcity', BCO_Customer_Helper::get_billing_city( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_billing_country
	 *
	 * @return void
	 */
	public function test_get_billing_country() {
		$this->assertEquals( 'Sweden', BCO_Customer_Helper::get_billing_country( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_billing_phone
	 *
	 * @return void
	 */
	public function test_get_billing_phone() {
		$this->assertEquals( '0701234567', BCO_Customer_Helper::get_billing_phone( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_billing_email
	 *
	 * @return void
	 */
	public function test_get_billing_email() {
		$this->assertEquals( 'test@krokedil.com', BCO_Customer_Helper::get_billing_email( $this->order ) );
	}


	/**
	 * Test BCO_Customer_Helper::get_shipping_first_name
	 *
	 * @return void
	 */
	public function test_get_shipping_first_name() {
		$this->assertEquals( 'Shippingfirstname', BCO_Customer_Helper::get_shipping_first_name( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_shipping_last_name
	 *
	 * @return void
	 */
	public function test_get_shipping_last_name() {
		$this->assertEquals( 'Shippinglastname', BCO_Customer_Helper::get_shipping_last_name( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_shipping_company
	 *
	 * @return void
	 */
	public function test_get_shipping_company() {
		$this->assertEquals( 'Shippingcompany', BCO_Customer_Helper::get_shipping_company( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_shipping_company
	 *
	 * @return void
	 */
	public function test_get_shipping_address_1() {
		$this->assertEquals( 'Shippingaddress 1', BCO_Customer_Helper::get_shipping_address_1( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_shipping_address_2
	 *
	 * @return void
	 */
	public function test_get_shipping_address2() {
		$this->assertEquals( 'Shippingaddress 2', BCO_Customer_Helper::get_shipping_address_2( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_shipping_postcode
	 *
	 * @return void
	 */
	public function test_get_shipping_postcode() {
		$this->assertEquals( '54321', BCO_Customer_Helper::get_shipping_postcode( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_shipping_city
	 *
	 * @return void
	 */
	public function test_get_shipping_city() {
		$this->assertEquals( 'Shippingcity', BCO_Customer_Helper::get_shipping_city( $this->order ) );
	}
	/**
	 * Test BCO_Customer_Helper::get_shipping_country
	 *
	 * @return void
	 */
	public function test_get_shipping_country() {
		$this->assertEquals( 'Sweden', BCO_Customer_Helper::get_shipping_country( $this->order ) );
	}


	/**
	 * Creates data for tests.
	 *
	 * @return void
	 */
	public function create() {
		$data  = [
			'billing'  => [
				'first_name' => 'Billingfirstname',
				'last_name'  => 'Billinglastname',
				'company'    => 'Billingcompany',
				'address_1'  => 'Billingaddress 1',
				'address_2'  => 'Billingaddress 2',
				'postcode'   => '12345',
				'city'       => 'Billingcity',
				'country'    => 'SE',
				'phone'      => '0701234567',
				'email'      => 'test@krokedil.com',
			],
			'shipping' => [
				'first_name' => 'Shippingfirstname',
				'last_name'  => 'Shippinglastname',
				'company'    => 'Shippingcompany',
				'address_1'  => 'Shippingaddress 1',
				'address_2'  => 'Shippingaddress 2',
				'postcode'   => '54321',
				'city'       => 'Shippingcity',
				'country'    => 'SE',
			],
		];
		$order = ( new Krokedil_Order() )->create();
		foreach ( $data as $address => $array ) {
			$order->set_address( $array, $address );
		}
		$this->order = $order;
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
		wp_delete_post( $this->order->get_id() );
		$this->order = null;
	}

}
