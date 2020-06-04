<?php
/**
 * Cancel payment request class
 *
 * @package Billmate_Checkout/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cancel payment request class
 */
class BCO_Request_Cancel_Payment extends BCO_Request {

	/**
	 * Makes the request.
	 *
	 * @param string $bco_order_number Billmate order number.
	 * @return array
	 */
	public function request( $bco_order_number ) {
		$request_url  = $this->base_url;
		$request_args = apply_filters( 'bco_cancel_payment_args', $this->get_request_args( $bco_order_number ) );

		$response = wp_remote_request( $request_url, $request_args );
		$code     = wp_remote_retrieve_response_code( $response );

		// Log the request.
		$log = BCO_Logger::format_log( $bco_order_number, 'GET', 'BCO cancel payment', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
		BCO_Logger::log( $log );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		return $formated_response;
	}

	/**
	 * Gets the request body.
	 *
	 * @param string $bco_order_number Billmate order number.
	 * @return array
	 */
	public function get_body( $bco_order_number ) {
		$data         = $this->get_request_data( $bco_order_number );
		$request_body = array(
			'credentials' => array(
				'id'   => $this->id,
				'hash' => hash_hmac( 'sha512', wp_json_encode( $data ), $this->secret ),
				'test' => $this->test,
			),
			'data'        => $data,
			'function'    => 'cancelPayment',
		);
		return $request_body;
	}

	/**
	 * Gets the request args for the API call.
	 *
	 * @param string $bco_order_number Billmate order number.
	 * @return array
	 */
	public function get_request_args( $bco_order_number ) {
		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode( $this->get_body( $bco_order_number ) ),
		);
	}

	/**
	 * Get needed data for the request.
	 *
	 * @param string $bco_order_number Billmate order number.
	 * @return array
	 */
	public function get_request_data( $bco_order_number ) {
		return array( 'number' => $bco_order_number );
	}
}
