<?php
/**
 * Order cart helper.
 *
 * @package Billmate_Checkout/Classes/Helpers/Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order Cart helper class.
 */
class BCO_Order_Cart_Helper {

	/**
	 * Get the order cart handling key value.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return array
	 */
	public static function get_order_cart_handling( $order ) {
		return array(
			'withouttax' => 0,
			'taxrate'    => self::get_handling_tax_rate( $order ),
		);
	}

	/**
	 * Get the order cart shipping key value.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return array
	 */
	public static function get_order_cart_shipping( $order ) {
		return array(
			'withouttax' => self::get_shipping_without_tax( $order ),
			'taxrate'    => self::get_shipping_tax_rate( $order ),
		);
	}

	/**
	 * Get the order cart total key value.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return array
	 */
	public static function get_order_cart_total( $order ) {
		return array(
			'withouttax' => self::get_total_without_tax( $order ),
			'tax'        => self::get_total_tax( $order ),
			'rounding'   => 0,
			'withtax'    => self::get_total_with_tax( $order ),
		);
	}


	/**
	 * Get order handling without tax.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return int $handling_without_tax handling excl tax.
	 */
	public static function get_handling_without_tax( $order ) {
		return round( ( $order->get_total() - $order->get_total_tax() ) * 100 );
	}

	/**
	 * Get order handling tax rate.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return int $handling_tax_rate handling tax rate.
	 */
	public static function get_handling_tax_rate( $order ) {
		$tax_rate = ( $order->get_total_tax() > 0 ) ? $order->get_total_tax() / ( $order->get_total() - $order->get_total_tax() ) * 100 : 0;
		return round( $tax_rate );
	}

	/**
	 * Get order shipping without tax.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return int $shipping_without_tax shipping excl tax.
	 */
	public static function get_shipping_without_tax( $order ) {
		return round( $order->get_shipping_total() * 100 );
	}

	/**
	 * Get order shipping tax rate.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return int $shipping_tax_rate shipping tax rate.
	 */
	public static function get_shipping_tax_rate( $order ) {
		$tax_rate = ( $order->get_shipping_tax() > 0 ) ? $order->get_shipping_tax() / $order->get_shipping_total() * 100 : 0;
		return round( $tax_rate );
	}

	/**
	 * Get order total excluding tax.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return int $total_without_tax order total excl tax.
	 */
	public static function get_total_without_tax( $order ) {
		return round( ( $order->get_total() - $order->get_total_tax() ) * 100 );
	}

	/**
	 * Get order total tax.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return int $total_tax order total tax.
	 */
	public static function get_total_tax( $order ) {
		return round( $order->get_total_tax() * 100 );
	}

	/**
	 * Get order total inclusive tax.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return int $total_with_tax order total incl tax.
	 */
	public static function get_total_with_tax( $order ) {
		return round( $order->get_total() * 100 );
	}
}
