<?php // phpcs:ignore
/**
 *
 * Test_BCO_Cart_Payment_Data_Helper class
 *
 * @package category
 */
/**
 * Test_BCO_Cart_Payment_Data_Helper class
 */
class Test_BCO_Cart_Payment_Data_Helper extends AKrokedil_Unit_Test_Case {
	/**
	 * Test BCO_Cart_Payment_Data_Helper::get_currency
	 *
	 * @return void
	 */
	public function test_get_currency() {
		$this->assertEquals( 'SEK', BCO_Cart_Payment_Data_Helper::get_currency() );
	}

	/**
	 * Test BCO_Cart_Payment_Data_Helper::get_language
	 *
	 * @return void
	 */
	public function test_get_language() {
		$this->assertEquals( 'sv', BCO_Cart_Payment_Data_Helper::get_language() );
	}

	/**
	 * Test BCO_Cart_Payment_Data_Helper::get_country
	 *
	 * @return void
	 */
	public function test_get_country() {
		$this->assertEquals( 'SE', BCO_Cart_Payment_Data_Helper::get_country() );
	}

	/**
	 * Creates data for tests.
	 *
	 * @return void
	 */
	public function create() {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
		add_filter(
			'locale',
			static function ( $locale ) {
				$locale = 'sv_SE';

				return $locale;
			},
			10
		);
		update_option( 'woocommerce_currency', 'SEK' );
		WC()->customer->set_billing_country( 'SE' );
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
		WC()->cart->empty_cart();
	}
}
