<?php
/**
 * Payment data helper.
 *
 * @package Billmate_Checkout/Classes/Helpers/Cart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Payment data helper class.
 */
class BCO_Cart_Payment_Data_Helper {

	/**
	 * Get currency helper function.
	 *
	 * @return string
	 */
	public static function get_currency() {
		return get_woocommerce_currency();
	}

	/**
	 * Get language helper function.
	 *
	 * @return string
	 */
	public static function get_language() {
		$iso_code = explode( '_', get_locale() );
		if ( in_array( $iso_code[0], array( 'sv', 'da', 'no', 'en' ), true ) ) {
			$lang = $iso_code[0];
		} else {
			$lang = 'en';
		}

		return $lang;
	}

	/**
	 * Get country helper function.
	 *
	 * @return string
	 */
	public static function get_country() {
		// Try to use customer country if available.
		if ( ! empty( WC()->customer->get_billing_country() ) && strlen( WC()->customer->get_billing_country() ) === 2 ) {
			return WC()->customer->get_billing_country( 'edit' );
		}

		$base_location = wc_get_base_location();
		$country       = $base_location['country'];

		return $country;
	}
}
