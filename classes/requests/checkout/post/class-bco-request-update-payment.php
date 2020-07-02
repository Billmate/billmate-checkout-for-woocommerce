<?php
/**
 * Update payment request class
 *
 * @package Billmate_Checkout/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Update payment request class
 */
class BCO_Request_Update_Payment extends BCO_Request {

	/**
	 * Makes the request.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return array
	 */
	public function request( $order_id ) {
		$request_url  = $this->base_url;
		$request_args = apply_filters( 'bco_update_payment_args', $this->get_request_args( $order_id ) );

		$response = wp_remote_request( $request_url, $request_args );
		$code     = wp_remote_retrieve_response_code( $response );

		// Log the request.
		$log = BCO_Logger::format_log( $order_id, 'POST', 'BCO update payment', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
		BCO_Logger::log( $log );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		return $formated_response;
	}

	/**
	 * Gets the request body.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return array
	 */
	public function get_body( $order_id ) {
		$data         = $this->get_request_data( $order_id );
		$request_body = array(
			'credentials' => array(
				'id'   => $this->id,
				'hash' => hash_hmac( 'sha512', wp_json_encode( $data ), $this->secret ),
				'test' => $this->test,
			),
			'data'        => $data,
			'function'    => 'updatePayment',
		);
		return $request_body;
	}

	/**
	 * Gets the request args for the API call.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return array
	 */
	public function get_request_args( $order_id ) {
		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode( $this->get_body( $order_id ) ),
		);
	}

	/**
	 * Get needed data for the request.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return array
	 */
	public function get_request_data( $order_id ) {
		$order = wc_get_order( $order_id );
		$data  = array(
			'PaymentData' => array(
				'number'   => get_post_meta( $order_id, '_billmate_transaction_id', true ),
				'method'   => get_post_meta( $order_id, '_billmate_payment_method_id', true ),
				'country'  => BCO_Order_Payment_Data_Helper::get_country( $order ),
				'language' => BCO_Order_Payment_Data_Helper::get_language(),
				'orderid'  => $order_id,
				'currency' => BCO_Order_Payment_Data_Helper::get_currency( $order ),
			),
			'Customer'    =>
			array(
				'Billing'  => BCO_Order_Customer_Helper::get_customer_billing( $order ),
				'Shipping' => BCO_Order_Customer_Helper::get_customer_shipping( $order ),
			),
			'Articles'    => BCO_Order_Articles_Helper::get_articles( $order ),
			'Cart'        =>
			array(
				'Handling' => BCO_Order_Cart_Helper::get_order_cart_handling( $order ),
				'Shipping' => BCO_Order_Cart_Helper::get_order_cart_shipping( $order ),
				'Total'    => BCO_Order_Cart_Helper::get_order_cart_total( $order ),
			),
		);
		return $data;
	}
}
