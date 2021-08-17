<?php
/**
 * Customer helper.
 *
 * @package Billmate_Checkout/Classes/Helpers/Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Customer helper class.
 */
class BCO_Order_Customer_Helper {

	/**
	 * Get the customer billing key value.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return array
	 */
	public static function get_customer_billing( $order ) {
		return array(
			'firstname' => self::get_billing_first_name( $order ),
			'lastname'  => self::get_billing_last_name( $order ),
			'company'   => self::get_billing_company( $order ),
			'street'    => self::get_billing_address_1( $order ),
			'street2'   => self::get_billing_address_2( $order ),
			'zip'       => self::get_billing_postcode( $order ),
			'city'      => self::get_billing_city( $order ),
			'country'   => self::get_billing_country( $order ),
			'phone'     => self::get_billing_phone( $order ),
			'email'     => self::get_billing_email( $order ),
		);
	}

	/**
	 * Get the customer shipping key value.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return array
	 */
	public static function get_customer_shipping( $order ) {
		return array(
			'firstname' => ! empty( self::get_shipping_first_name( $order ) ) ? self::get_shipping_first_name( $order ) : self::get_billing_first_name( $order ),
			'lastname'  => ! empty( self::get_shipping_last_name( $order ) ) ? self::get_shipping_last_name( $order ) : self::get_billing_last_name( $order ),
			'company'   => ! empty( self::get_shipping_company( $order ) ) ? self::get_shipping_company( $order ) : self::get_billing_company( $order ),
			'street'    => ! empty( self::get_shipping_address_1( $order ) ) ? self::get_shipping_address_1( $order ) : self::get_billing_address_1( $order ),
			'street2'   => ! empty( self::get_shipping_address_2( $order ) ) ? self::get_shipping_address_2( $order ) : self::get_billing_address_2( $order ),
			'zip'       => ! empty( self::get_shipping_postcode( $order ) ) ? self::get_shipping_postcode( $order ) : self::get_billing_postcode( $order ),
			'city'      => ! empty( self::get_shipping_city( $order ) ) ? self::get_shipping_city( $order ) : self::get_billing_city( $order ),
			'country'   => ! empty( self::get_shipping_country( $order ) ) ? self::get_shipping_country( $order ) : self::get_billing_country( $order ),
		);
	}

	/**
	 * Get billing first name helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_billing_first_name( $order ) {
		return $order->get_billing_first_name();
	}

	/**
	 * Get billing last name helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_billing_last_name( $order ) {
		return $order->get_billing_last_name();
	}

	/**
	 * Get billing company helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_billing_company( $order ) {
		return $order->get_billing_company();
	}

	/**
	 * Get billing address 1 helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_billing_address_1( $order ) {
		return $order->get_billing_address_1();
	}

	/**
	 * Get billing address 2 helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_billing_address_2( $order ) {
		return $order->get_billing_address_2();
	}

	/**
	 * Get billing postcode helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_billing_postcode( $order ) {
		return $order->get_billing_postcode();
	}

	/**
	 * Get billing city helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_billing_city( $order ) {
		return $order->get_billing_city();
	}

	/**
	 * Get billing country helper function.
	 *
	 * @param WC_order $order WooCommerce order.
	 * @return string
	 */
	public static function get_billing_country( $order ) {
		return $order->get_billing_country();
	}

	/**
	 * Get billing phone helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_billing_phone( $order ) {
		return $order->get_billing_phone();
	}

	/**
	 * Get billing email helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_billing_email( $order ) {
		return $order->get_billing_email();
	}



	/**
	 * Get shipping first name helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_shipping_first_name( $order ) {
		return $order->get_shipping_first_name();
	}

	/**
	 * Get shipping last name helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_shipping_last_name( $order ) {
		return $order->get_shipping_last_name();
	}

	/**
	 * Get shipping company helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_shipping_company( $order ) {
		return $order->get_shipping_company();
	}

	/**
	 * Get shipping address 1 helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_shipping_address_1( $order ) {
		return $order->get_shipping_address_1();
	}

	/**
	 * Get shipping address 2 helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_shipping_address_2( $order ) {
		return $order->get_shipping_address_2();
	}

	/**
	 * Get shipping postcode helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_shipping_postcode( $order ) {
		return $order->get_shipping_postcode();
	}

	/**
	 * Get shipping city helper function.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return string
	 */
	public static function get_shipping_city( $order ) {
		return $order->get_shipping_city();
	}

	/**
	 * Get shipping country helper function.
	 *
	 * @param WC_order $order WooCommerce order.
	 * @return string
	 */
	public static function get_shipping_country( $order ) {
		return $order->get_shipping_country();
	}
}
