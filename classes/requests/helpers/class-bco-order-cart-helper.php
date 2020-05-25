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
	 * @return int $item_price Item price.
	 */
	public function get_handling_without_tax( $order_item ) {
		return round( $order_item->get_total() * 100 );
	}

	/**
	 * Get order handling tax rate.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price Item price.
	 */
	public function get_handling_tax_rate( $order_item ) {
		$tax_rate = ( $order_item->get_total_tax() > 0 ) ? $order_item->get_total_tax() / $order_item->get_total() * 100 : 0;
		return round( $tax_rate );
	}



	/**
	 * Get order shipping without tax.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price Item price.
	 */
	public function get_shipping_without_tax( $order_item ) {
		return round( $order_item->get_total() * 100 );
	}

	/**
	 * Get order shipping tax rate.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price Item price.
	 */
	public function get_shipping_tax_rate( $order_item ) {
		$tax_rate = ( $order_item->get_total_tax() > 0 ) ? $order_item->get_total_tax() / $order_item->get_total() * 100 : 0;
		return round( $tax_rate );
	}



	/**
	 * Get order total excluding tax.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price Item price.
	 */
	public function get_total_without_tax( $order_item ) {
		return round( $order_item->get_total() * 100 );
	}

	/**
	 * Get order total tax.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price Item price.
	 */
	public function get_total_tax( $order_item ) {
		return round( $order_item->get_total_tax() * 100 );
	}

	public function get_total_rounding( $order_item ) {
		$total = $order_item->get_total() + $order_item->get_total_tax();
		echo PHP_EOL . var_export( $total, true );
		$round_total = round( $total );
		echo PHP_EOL . var_export( $round_total, true );
		$rounding = $round_total - $total;
		echo PHP_EOL . var_export( round( $rounding * 100 ), true );
		return $rounding;
	}

	public function get_total_with_tax( $order_item ) {
		return round( ( $order_item->get_total() + $order_item->get_total_tax() ) * 100 );
	}
}
