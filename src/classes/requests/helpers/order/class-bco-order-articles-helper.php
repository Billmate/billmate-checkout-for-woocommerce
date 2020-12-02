<?php
/**
 * Articles helper.
 *
 * @package Billmate_Checkout/Classes/Helpers/Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order Articles helper class.
 */
class BCO_Order_Articles_Helper {

	/**
	 * Gets the articles for the order.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return array
	 */
	public static function get_articles( $order ) {
		$articles = array();

		foreach ( $order->get_items() as $item ) {
			array_push( $articles, self::get_order_lines( $item ) );
		}
		foreach ( $order->get_fees() as $fee ) {
			array_push( $articles, self::get_order_lines( $fee ) );
		}

		return $articles;
	}

	/**
	 * Gets the formated order lines.
	 *
	 * @param WC_Order_Item_Product $order_item The WooCommerce order line item.
	 * @return array
	 */
	public static function get_order_lines( $order_item ) {
		$order_id = $order_item->get_order_id();
		$order    = wc_get_order( $order_id );
		return array(
			'artnr'      => self::get_article_number( $order_item ),
			'title'      => self::get_title( $order_item ),
			'quantity'   => self::get_quantity( $order_item ),
			'aprice'     => self::get_article_price( $order_item ),
			'withouttax' => self::get_without_tax( $order_item ),
			'taxrate'    => self::get_tax_rate( $order_item ),
		);
	}

	/**
	 * Get order item article number.
	 *
	 * Returns SKU or product ID.
	 *
	 * @param object $order_item Product object.
	 * @return string $article_number Order item article number.
	 */
	public static function get_article_number( $order_item ) {
		if ( 'fee' === $order_item->get_type() ) {
			$article_number = $order_item->get_id();
		} else {
			$product = $order_item->get_product();
			if ( $product->get_sku() ) {
				$article_number = $product->get_sku();
			} else {
				$article_number = $product->get_id();
			}
		}

		return substr( (string) $article_number, 0, 255 );
	}

	/**
	 * Get order item title.
	 *
	 * @param array $order_item order item.
	 * @return string $item_title order item title.
	 */
	public static function get_title( $order_item ) {
		$item_title = $order_item->get_name();

		return strip_tags( $item_title ); //phpcs:ignore
	}

	/**
	 * Get order item quantity
	 *
	 * @param array $order_item order item.
	 * @return int $item_quantity order item quantity.
	 */
	public static function get_quantity( $order_item ) {
		return $order_item->get_quantity();
	}

	/**
	 * Get order item article price excluding tax
	 *
	 * @param array $order_item order item.
	 * @return int $item_price Item price.
	 */
	public static function get_article_price( $order_item ) {
		$item_subtotal = $order_item->get_total() * 100 / $order_item->get_quantity();
		return round( $item_subtotal );
	}

	/**
	 * Get order row total articles price excluding tax.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price Item price.
	 */
	public static function get_without_tax( $order_item ) {
		return round( $order_item->get_total() * 100 );
	}

	/**
	 * Get order item article tax rate.
	 *
	 * @param array $order_item order item.
	 * @return int $item_price Item price.
	 */
	public static function get_tax_rate( $order_item ) {
		$tax_rate = ( $order_item->get_total_tax() > 0 ) ? $order_item->get_total_tax() / $order_item->get_total() * 100 : 0;
		return round( $tax_rate );
	}
}
