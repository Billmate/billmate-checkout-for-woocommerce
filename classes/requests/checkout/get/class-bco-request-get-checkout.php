<?php
/**
 * Get checkout request class
 *
 * @package Billmate_Checkout/Classes/Get/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get checkout request class
 */
class BCO_Request_Get_Checkout extends BCO_Request {

	/**
	 * Makes the request.
	 *
	 * @return array
	 */
	public function request() {
		$request_url  = $this->base_url;
		$request_args = apply_filters( 'bco_get_checkout_args', $this->get_request_args() );

		$response = wp_remote_request( $request_url, $request_args );
		$code     = wp_remote_retrieve_response_code( $response );

		// Log the request.
		$log = BCO_Logger::format_log( '', 'GET', 'BCO get checkout', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
		BCO_Logger::log( $log );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		return $formated_response;
	}

	/**
	 * Gets the request body.
	 *
	 * @return array
	 */
	public function get_body() {
		$data         = $this->get_request_data();
		$request_body = array(
			'credentials' => array(
				'id'   => $this->id,
				'hash' => hash_hmac( 'sha512', wp_json_encode( $data ), $this->secret ),
				'test' => $this->test,
			),
			'data'        => $data,
			'function'    => 'getCheckout',
		);
		return $request_body;
	}

	/**
	 * Gets the request args for the API call.
	 *
	 * @return array
	 */
	public function get_request_args() {
		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode( $this->get_body() ),
		);
	}

	/**
	 * Get needed data for the request.
	 *
	 * @return array
	 */
	public function get_request_data() {
		return array(
			'PaymentData' => array(
				'hash' => WC()->session->get( 'bco_wc_hash' ),
			),
		);
	}
}
