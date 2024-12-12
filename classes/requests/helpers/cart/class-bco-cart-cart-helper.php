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
			'withouttax' => self::get_handling_without_tax(),
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
			'withouttax' => self::get_total_without_tax() + self::get_handling_without_tax(),
			'tax'        => self::get_total_tax() + self::get_handling_tax(),
			'rounding'   => 0,
			'withtax'    => self::get_total_with_tax() + self::get_handling_without_tax() + self::get_handling_tax(),
		);
	}

	/**
	 * Get cart handling without tax.
	 *
	 * @return int $handling_without_tax handling excl tax.
	 */
	public static function get_handling_without_tax() {
		$billmate_settings = get_option( 'woocommerce_bco_settings' );
		$invoice_fee       = isset( $billmate_settings['invoice_fee'] ) ? str_replace( ',', '.', $billmate_settings['invoice_fee'] ) : '';
		if ( ! empty( $invoice_fee ) && is_numeric( $invoice_fee ) ) {
			$handling_without_tax = round( $invoice_fee * 100 );
		} else {
			$handling_without_tax = 0;
		}
		return $handling_without_tax;
	}

	/**
	 * Get cart handling tax rate.
	 *
	 * @return int $handling_tax_rate handling tax rate.
	 */
	public static function get_handling_tax_rate() {
		$billmate_settings = get_option( 'woocommerce_bco_settings' );
		$invoice_fee       = isset( $billmate_settings['invoice_fee'] ) ? str_replace( ',', '.', $billmate_settings['invoice_fee'] ) : '';

		if ( method_exists( WC()->cart, 'get_customer' ) && true === WC()->cart->get_customer()->get_is_vat_exempt() ) {
			$is_vat_exempt = true;
		} else {
			$is_vat_exempt = false;
		}

		if ( ! empty( $invoice_fee ) && is_numeric( $invoice_fee ) && ! $is_vat_exempt ) {
			$handling_tax_rate = 0;
			$invoice_fee_tax   = $billmate_settings['invoice_fee_tax'];
			$tax_rates         = WC_Tax::get_rates_for_tax_class( $invoice_fee_tax );
			foreach ( $tax_rates as $tax_rate ) {
				if ( 'SE' === $tax_rate->tax_rate_country ) {
					// If we find a SE tax rate, use that tax rate and break.
					$handling_tax_rate = round( $tax_rate->tax_rate );
					break;
				} elseif ( '' === $tax_rate->tax_rate_country || '*' === $tax_rate->tax_rate_country ) {
					// If we find a generic tax_rate, set that for now but do not break incase we find a swedish specific tax rate.
					$handling_tax_rate = round( $tax_rate->tax_rate );
				}
			}
		} else {
			$handling_tax_rate = 0;
		}

		return round( $handling_tax_rate );
	}

	/**
	 * Get tax amount for invoice fee.
	 *
	 * @return int $handling_tax handling excl tax.
	 */
	public static function get_handling_tax() {
		$billmate_settings = get_option( 'woocommerce_bco_settings' );
		$invoice_fee       = isset( $billmate_settings['invoice_fee'] ) ? str_replace( ',', '.', $billmate_settings['invoice_fee'] ) : '';
		if ( ! empty( $invoice_fee ) && is_numeric( $invoice_fee ) ) {
			if ( 0 === self::get_handling_tax_rate() ) {
				$handling_tax = 0;
			} else {
				$handling_tax = ( $invoice_fee * ( ( self::get_handling_tax_rate() / 100 ) + 1 ) ) - $invoice_fee;
				$handling_tax = round( $handling_tax * 100 );
			}
		} else {
			$handling_tax = 0;
		}
		return $handling_tax;
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
