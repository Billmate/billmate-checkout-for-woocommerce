<?php // phpcs:ignore
/**
 *
 * Test_BCO_Order_Payment_Data_Helper class
 *
 * @package category
 */
/**
 * Test_BCO_Order_Payment_Data_Helper class
 */
class Test_BCO_Order_Payment_Data_Helper extends AKrokedil_Unit_Test_Case {
	/**
	 * Test BCO_Order_Payment_Data_Helper::get_currency
	 *
	 * @return void
	 */
	public function test_get_currency() {
		$this->assertEquals( 'SEK', BCO_Order_Payment_Data_Helper::get_currency( $this->order ) );
	}

	/**
	 * Test BCO_Order_Payment_Data_Helper::get_language
	 *
	 * @return void
	 */
	public function test_get_language() {
		$this->assertEquals( 'sv', BCO_Order_Payment_Data_Helper::get_language() );
	}

	/**
	 * Test BCO_Order_Payment_Data_Helper::get_country
	 *
	 * @return void
	 */
	public function test_get_country() {
		$this->assertEquals( 'SE', BCO_Order_Payment_Data_Helper::get_country( $this->order ) );
	}

	/**
	 * Creates data for tests.
	 *
	 * @return void
	 */
	public function create() {
		global $locale;
		$locale = 'sv_SE';
		$order  = ( new Krokedil_Order() )->create();
		$order->set_currency( 'SEK' );
		$order->set_billing_country( 'SE' );
		$order->save();
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
