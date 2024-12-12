<?php
/**
 * Articles helper.
 *
 * @package Billmate_Checkout/Classes/Helpers/Cart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cart Articles helper class.
 */
class BCO_Cart_Articles_Helper {

	/**
	 * Gets the articles for the order.
	 *
	 * @return array
	 */
	public static function get_articles() {
		$articles     = array();
		$cart_items   = WC()->cart->get_cart();
		$cart_fees    = WC()->cart->get_fees();
		$cart_coupons = WC()->cart->get_coupons();

		foreach ( $cart_items as $cart_item ) {
			array_push( $articles, self::get_cart_lines( $cart_item ) );
		}

		foreach ( $cart_fees as $fee ) {
			array_push( $articles, self::get_fee_lines( $fee ) );
		}

		// Smart coupons.
		foreach ( $cart_coupons as $coupon ) {
			if ( 'smart_coupon' === $coupon->get_discount_type() ) {
				array_push( $articles, self::get_smart_coupon_line( $coupon ) );
			}
		}

		return $articles;
	}

	/**
	 * Gets the formatted cart lines.
	 *
	 * @param array $cart_item cart item.
	 * @return array
	 */
	public static function get_cart_lines( $cart_item ) {
		if ( $cart_item['variation_id'] ) {
			$product = wc_get_product( $cart_item['variation_id'] );
		} else {
			$product = wc_get_product( $cart_item['product_id'] );
		}
		return array(
			'artnr'      => self::get_article_number( $product ),
			'title'      => self::get_title( $cart_item ),
			'quantity'   => self::get_quantity( $cart_item ),
			'aprice'     => self::get_article_price( $cart_item ),
			'withouttax' => self::get_without_tax( $cart_item ),
			'taxrate'    => self::get_tax_rate( $cart_item ),
			'discount'   => self::get_discount( $cart_item ),
		);
	}

	/**
	 * Gets the formatted cart lines for fees.
	 *
	 * @param array $fee cart item fee.
	 * @return array
	 */
	public static function get_fee_lines( $fee ) {
		if ( $cart_item['variation_id'] ) {
			$product = wc_get_product( $cart_item['variation_id'] );
		} else {
			$product = wc_get_product( $cart_item['product_id'] );
		}
		return array(
			'artnr'      => $fee->id,
			'title'      => $fee->name,
			'quantity'   => 1,
			'aprice'     => round( $fee->amount * 100 ),
			'withouttax' => round( $fee->amount * 100 ),
			'taxrate'    => self::get_fee_tax_rate( $fee ),
			'discount'   => 0,
		);
	}

	/**
	 * Gets the formatted cart lines for Smart coupon (gift card).
	 *
	 * @param array $coupon cart item fee.
	 * @return array
	 */
	public static function get_smart_coupon_line( $coupon ) {
		$apply_before_tax = 'yes' === get_option( 'woocommerce_smart_coupon_apply_before_tax', 'no' );
		if ( wc_tax_enabled() && $apply_before_tax ) {
			// The discount is applied directly to the cart item. Send gift card amount as zero for bookkeeping.
			$coupon_amount = 0;
		} else {
			$coupon_amount = $coupon->get_amount() * -1;
		}

		return array(
			'artnr'      => $coupon->get_id(),
			'title'      => __( 'Gift Card', 'billmate-checkout-for-woocommerce' ),
			'quantity'   => 1,
			'aprice'     => round( ( $coupon_amount ) * 100 ),
			'withouttax' => round( ( $coupon_amount ) * 100 ),
			'taxrate'    => 0,
			'discount'   => 0,
		);
	}

	/**
	 * Get cart item article number.
	 *
	 * Returns SKU or product ID.
	 *
	 * @param object $product Product object.
	 * @return string $article_number Cart item article number.
	 */
	public static function get_article_number( $product ) {
		if ( $product->get_sku() ) {
			$article_number = $product->get_sku();
		} else {
			$article_number = $product->get_id();
		}

		return substr( (string) $article_number, 0, 255 );
	}

	/**
	 * Get cart item title.
	 *
	 * @param array $cart_item Cart item.
	 * @return string $item_title Cart item title.
	 */
	public static function get_title( $cart_item ) {
		$cart_item_data = $cart_item['data'];
		$item_title     = $cart_item_data->get_name();

		return strip_tags( $item_title ); //phpcs:ignore
	}

	/**
	 * Get cart item quantity
	 *
	 * @param array $cart_item Cart item.
	 * @return int $item_quantity Cart item quantity.
	 */
	public static function get_quantity( $cart_item ) {
		return round( $cart_item['quantity'] );
	}

	/**
	 * Get cart item article price excluding tax
	 *
	 * @param array $cart_item Cart item.
	 * @return int $item_price Item price.
	 */
	public static function get_article_price( $cart_item ) {
		$item_subtotal = wc_get_price_excluding_tax( $cart_item['data'] );
		return round( $item_subtotal * 100 );
	}

	/**
	 * Get cart row total articles price excluding tax.
	 *
	 * @param array $cart_item cart item.
	 * @return int $item_price Item price excluding tax.
	 */
	public static function get_without_tax( $cart_item ) {
		$items_subtotal = $cart_item['line_total'];
		return round( $items_subtotal * 100 );
	}

	/**
	 * Get cart item article tax rate.
	 *
	 * @param array $cart_item cart item.
	 * @return int $item_tax_rate Item tax rate.
	 */
	public static function get_tax_rate( $cart_item ) {
		if ( $cart_item['line_tax'] < 0 ) {
			$tax_rate = abs( $cart_item['line_tax'] ) / abs( $cart_item['line_total'] ) * 100;
		} else {
			$tax_rate = ( 0.00 !== round( $cart_item['line_tax'], 2 ) ) ? $cart_item['line_tax'] / $cart_item['line_total'] * 100 : 0;
		}
		return round( $tax_rate );
	}

	/**
	 * Get cart item discount
	 *
	 * @param array $cart_item Cart item.
	 * @return int $discount Discount in %.
	 */
	public static function get_discount( $cart_item ) {
		$article_price = wc_get_price_excluding_tax( $cart_item['data'] );
		$line_total    = $cart_item['line_total'];

		// No discount for already 0 value articles.
		if ( 0.0 === round( $article_price, 2 ) ) {
			return 0;
		}

		if ( ( $article_price * $cart_item['quantity'] ) !== $line_total ) {
			return round( ( 1 - ( $line_total / ( $article_price * $cart_item['quantity'] ) ) ) * 100 );
		} else {
			return 0;
		}
		return round( $item_subtotal * 100 );
	}

	/**
	 * Get cart item fee tax rate.
	 *
	 * @param array $fee cart item fee.
	 * @return int $tax_rate fee tax rate.
	 */
	public static function get_fee_tax_rate( $fee ) {
		$_tax      = new WC_Tax();
		$tmp_rates = $_tax->get_rates( $fee->tax_class );
		$vat       = array_shift( $tmp_rates );

		if ( isset( $vat['rate'] ) ) {
			$fee_tax_rate = round( $vat['rate'], 2 );
		} else {
			$fee_tax_rate = 0;
		}
		return $fee_tax_rate;
	}

}
