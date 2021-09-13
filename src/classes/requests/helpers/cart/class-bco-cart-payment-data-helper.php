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
	 * Get the payment data key value.
	 *
	 * @return array
	 */
	public static function get_payment_data() {
		$confirmation_url = add_query_arg(
			array(
				'bco_confirm' => 'yes',
				'bco_flow'    => 'checkout_redirect',
				'wc_order_id' => 'null',
			),
			trailingslashit( home_url() )
		);
		$push_url         = home_url( '/wc-api/BCO_WC_Push/' );
		return array(
			'currency'    => self::get_currency(),
			'language'    => self::get_language(),
			'country'     => self::get_country(),
			'orderid'     => WC()->session->get( 'bco_wc_temp_order_id' ),
			'logo'        => self::get_logo(),
			'accepturl'   => $confirmation_url,
			'cancelurl'   => wc_get_checkout_url(),
			'callbackurl' => $push_url,
		);
	}

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
		$locale = substr( get_locale(), 0, 2 );

		// If the site language is Englis - let's return en.
		if ( 'en' === $locale ) {
			return 'en';
		}

		// If SEK is the selected currency  - let's use sv.
		if ( 'SEK' === get_woocommerce_currency() ) {
			return 'sv';
		}

		// Otherwise - let's use en.
		return 'en';
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
