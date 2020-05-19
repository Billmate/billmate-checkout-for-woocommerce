<?php
/**
 * Payment data helper.
 *
 * @package Billmate_Checkout/Classes/Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Payment data helper class.
 */
class BCO_Payment_Data_Helper {

	/**
	 * Get currency helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_currency( $order ) {
		return $order->get_currency();
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
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_country( $order ) {
		return $order->get_billing_country();
	}
}
