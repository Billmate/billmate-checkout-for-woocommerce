<?php
/**
 * Articles helper.
 *
 * @package Billmate_Checkout/Classes/Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order Articles helper class.
 */
class BCO_Order_Articles_Helper {

	/**
	 * Get order item article number.
	 *
	 * Returns SKU or product ID.
	 *
	 * @param object $order_item Product object.
	 * @return string $article_number Order item article number.
	 */
	public static function get_article_number( $order_item ) {
		$product = $order_item->get_product();
		if ( $product->get_sku() ) {
			$article_number = $product->get_sku();
		} else {
			$article_number = $product->get_id();
		}

		return substr( (string) $article_number, 0, 64 ); // TODO: Check what the max character is here.
	}

	/**
	 * Get order item title.
	 *
	 * @param array $order_item order item.
	 * @return string $item_title order item title.
	 */
	public function get_title( $order_item ) {
		$item_title = $order_item->get_name();

		return strip_tags( $item_title ); //phpcs:ignore
	}

	/**
	 * Get order item quantity
	 *
	 * @param array $order_item order item.
	 * @return int $item_quantity order item quantity.
	 */
	public function get_quantity( $order_item ) {
		return $order_item->get_quantity();
	}

	/**
	 * Get order item article price excluding tax
	 *
	 * @param array $order_item order item.
	 * @return int $item_price Item price.
	 */
	public function get_article_price( $order_item ) {
		$item_subtotal = $order_item->get_total() * 100 / $order_item->get_quantity();
		return round( $item_subtotal );
	}

	/**
	 * Get order row total articles price excluding tax.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price Item price.
	 */
	public function get_without_tax( $order_item ) {
		return round( $order_item->get_total() * 100 );
	}

	/**
	 * Get order item article tax rate.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price Item price.
	 */
	public function get_tax_rate( $order_item ) {
		$tax_rate = ( $order_item->get_total_tax() > 0 ) ? $order_item->get_total_tax() / $order_item->get_total() * 100 : 0;
		return round( $tax_rate );
	}
}
