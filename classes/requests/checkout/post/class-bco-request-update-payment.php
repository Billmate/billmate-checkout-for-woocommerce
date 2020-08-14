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
				'id'      => $this->id,
				'hash'    => hash_hmac( 'sha512', wp_json_encode( $data ), $this->secret ),
				'test'    => $this->test,
				'version' => $this->version,
				'client'  => $this->client,
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
			'timeout' => apply_filters( 'bco_set_timeout', 10 ),
		);
	}

	/**
	 * Get needed data for the request.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return array
	 */
	public function get_request_data( $order_id ) {
		$data = array(
			'PaymentData' => array(
				'number'  => get_post_meta( $order_id, '_billmate_transaction_id', true ),
				'method'  => get_post_meta( $order_id, '_billmate_payment_method_id', true ),
				'orderid' => $order_id,
			),
		);
		return $data;
	}
}
