<?php
/**
 * Order cart helper.
 *
 * @package Billmate_Checkout/Classes/Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order Cart helper class.
 */
class BCO_Order_Cart_Helper {
	/**
	 * Get order handling without tax.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price handling excl tax.
	 */
	public function get_handling_without_tax( $order_item ) {
		return round( $order_item->get_total() * 100 );
	}

	/**
	 * Get order handling tax rate.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price handling tax rate.
	 */
	public function get_handling_tax_rate( $order_item ) {
		$tax_rate = ( $order_item->get_total_tax() > 0 ) ? $order_item->get_total_tax() / $order_item->get_total() * 100 : 0;
		return round( $tax_rate );
	}

	/**
	 * Get order shipping without tax.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price shipping excl tax.
	 */
	public function get_shipping_without_tax( $order_item ) {
		return round( $order_item->get_total() * 100 );
	}

	/**
	 * Get order shipping tax rate.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price shipping tax rate.
	 */
	public function get_shipping_tax_rate( $order_item ) {
		$tax_rate = ( $order_item->get_total_tax() > 0 ) ? $order_item->get_total_tax() / $order_item->get_total() * 100 : 0;
		return round( $tax_rate );
	}

	/**
	 * Get order total excluding tax.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price order total excl tax.
	 */
	public function get_total_without_tax( $order_item ) {
		return round( $order_item->get_total() * 100 );
	}

	/**
	 * Get order total tax.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price order total tax.
	 */
	public function get_total_tax( $order_item ) {
		return round( $order_item->get_total_tax() * 100 );
	}

	/**
	 * Get order total inclusive tax.
	 *
	 * @param array $order_item order item.
	 * @return int $order_total order total incl tax.
	 */
	public function get_total_with_tax( $order_item ) {
		return round( ( $order_item->get_total() + $order_item->get_total_tax() ) * 100 );
	}
}
