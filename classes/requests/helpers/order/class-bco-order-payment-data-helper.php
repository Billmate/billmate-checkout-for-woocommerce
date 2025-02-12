<?php
/**
 * Payment data helper.
 *
 * @package Billmate_Checkout/Classes/Helpers/Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Payment data helper class.
 */
class BCO_Order_Payment_Data_Helper {

	/**
	 * Get the payment data key value.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return array
	 */
	public static function get_payment_data( $order ) {
		$order_id         = $order->get_id();
		$confirmation_url = add_query_arg(
			array(
				'bco_confirm' => 'yes',
				'bco_flow'    => 'pay_for_order_redirect',
				'wc_order_id' => $order_id,
			),
			$order->get_checkout_order_received_url()
		);
		$push_url         = home_url( '/wc-api/BCO_WC_Push/' );
		return array(
			'currency'    => self::get_currency( $order ),
			'language'    => self::get_language(),
			'country'     => self::get_country( $order ),
			'orderid'     => $order->get_order_number(),
			'logo'        => self::get_logo(),
			'accepturl'   => $confirmation_url,
			'cancelurl'   => $order->get_cancel_order_url_raw(),
			'callbackurl' => $push_url,
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
		// Swedish is the only supported language at the moment.
		return 'sv';
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

	/**
	 * Get logo helper function.
	 *
	 * @return string
	 */
	public static function get_logo() {
		$billmate_settings = get_option( 'woocommerce_bco_settings' );
		$logo              = ( isset( $billmate_settings['logo'] ) ) ? $billmate_settings['logo'] : '';
		return $logo;
	}
}
