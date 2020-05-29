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
	 * Get the payment data key value.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return array
	 */
	public static function get_payment_data( $order ) {
		return array(
			'currency'  => self::get_currency( $order ),
			'language'  => self::get_language(),
			'country'   => self::get_country( $order ),
			'orderid'   => $order->get_id(),
			'accepturl' => $order->get_checkout_order_received_url(),
			'cancelurl' => $order->get_cancel_order_url_raw(),
		);
	}

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
