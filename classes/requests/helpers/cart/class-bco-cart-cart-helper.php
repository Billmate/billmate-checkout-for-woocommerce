<?php
/**
 * Cart helper.
 *
 * @package Billmate_Checkout/Classes/Helpers/Cart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cart Cart helper class.
 */
class BCO_Cart_Cart_Helper {

	/**
	 * Get the cart handling key value.
	 *
	 * @return array
	 */
	public static function get_handling() {
		return array(
			'withouttax' => 0,
			'taxrate'    => self::get_handling_tax_rate(),
		);
	}

	/**
	 * Get the cart shipping key value.
	 *
	 * @return array
	 */
	public static function get_shipping() {
		return array(
			'withouttax' => self::get_shipping_without_tax(),
			'taxrate'    => self::get_shipping_tax_rate(),
		);
	}

	/**
	 * Get the cart total key value.
	 *
	 * @return array
	 */
	public static function get_total() {
		return array(
			'withouttax' => self::get_total_without_tax(),
			'tax'        => self::get_total_tax(),
			'rounding'   => 0,
			'withtax'    => self::get_total_with_tax(),
		);
	}

	/**
	 * Get cart handling without tax.
	 *
	 * @return int $handling_without_tax handling excl tax.
	 */
	public static function get_handling_without_tax() {
		return round( ( WC()->cart->total - WC()->cart->tax_total ) * 100 );
	}

	/**
	 * Get cart handling tax rate.
	 *
	 * @return int $handling_tax_rate handling tax rate.
	 */
	public static function get_handling_tax_rate() {
		$tax_rate = ( WC()->cart->tax_total > 0 ) ? WC()->cart->tax_total / ( WC()->cart->total - WC()->cart->tax_total ) * 100 : 0;
		return round( $tax_rate );
	}

	/**
	 * Get cart shipping without tax.
	 *
	 * @return int $shipping_without_tax shipping excl tax.
	 */
	public static function get_shipping_without_tax() {
		$shipping_amount = WC()->cart->shipping_total * 100;

		return round( $shipping_amount );
	}

	/**
	 * Get cart shipping tax rate.
	 *
	 * @return int $shipping_tax_rate shipping tax rate.
	 */
	public static function get_shipping_tax_rate() {
		if ( WC()->cart->shipping_tax_total > 0 ) {
			$shipping_rates = WC_Tax::get_shipping_tax_rates();
			$vat            = array_shift( $shipping_rates );
			if ( isset( $vat['rate'] ) ) {
				$shipping_tax_rate = round( $vat['rate'] );
			} else {
				$shipping_tax_rate = 0;
			}
		} else {
			$shipping_tax_rate = 0;
		}

		return round( $shipping_tax_rate );
	}

	/**
	 * Get cart total excluding tax.
	 *
	 * @return int $total_without_tax order total excl tax.
	 */
	public static function get_total_without_tax() {
		$total             = WC()->cart->total;
		$tax_total         = WC()->cart->tax_total;
		$shipping_tax      = ( WC()->cart->shipping_tax_total > 0 ) ? WC()->cart->shipping_tax_total : 0;
		$total_without_tax = $total - $tax_total - $shipping_tax;
		return round( $total_without_tax * 100 );
	}

	/**
	 * Get cart total tax.
	 *
	 * @return int $total_tax order total tax.
	 */
	public static function get_total_tax() {
		$shipping_tax = ( WC()->cart->shipping_tax_total > 0 ) ? WC()->cart->shipping_tax_total : 0;
		return round( ( WC()->cart->tax_total + $shipping_tax ) * 100 );
	}

	/**
	 * Get order total inclusive tax.
	 *
	 * @return int $total_with_tax order total incl tax.
	 */
	public static function get_total_with_tax() {
		return round( WC()->cart->total * 100 );
	}

}
