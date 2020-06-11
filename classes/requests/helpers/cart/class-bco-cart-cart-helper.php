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
	 * Get cart handling without tax.
	 *
	 * @param array $cart_item Cart item.
	 * @return int $handling_without_tax handling excl tax.
	 */
	public static function get_handling_without_tax( $cart_item ) {
		$items_subtotal = $cart_item['line_total'];
		return round( $items_subtotal * 100 );
	}

	/**
	 * Get cart handling tax rate.
	 *
	 * @param array $cart_item Cart item.
	 * @return int $handling_tax_rate handling tax rate.
	 */
	public static function get_handling_tax_rate( $cart_item ) {
		$tax_rate = ( $cart_item['line_tax'] > 0 ) ? $cart_item['line_tax'] / $cart_item['line_total'] * 100 : 0;
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
				$shipping_tax_rate = round( $vat['rate'] * 100 );
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
	 * @param array $cart_item Cart item.
	 * @return int $total_without_tax order total excl tax.
	 */
	public static function get_total_without_tax( $cart_item ) {
		return round( ( $cart_item->get_total() - $cart_item->get_total_tax() ) * 100 );
	}

	/**
	 * Get cart total tax.
	 *
	 * @param array $cart_item Cart item.
	 * @return int $total_tax order total tax.
	 */
	public static function get_total_tax( $cart_item ) {
		return round( $cart_item->get_total_tax() * 100 );
	}

	/**
	 * Get order total inclusive tax.
	 *
	 * @param array $cart_item Cart item.
	 * @return int $total_with_tax order total incl tax.
	 */
	public static function get_total_with_tax( $cart_item ) {
		return round( $cart_item->get_total() * 100 );
	}

}
