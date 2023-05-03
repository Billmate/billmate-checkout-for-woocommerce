<?php
/**
 * Get payment request class
 *
 * @package Billmate_Checkout/Classes/Get/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get payment request class
 */
class BCO_Request_Get_Payment extends BCO_Request {

	/**
	 * Makes the request.
	 *
	 * @param string $bco_transaction_id Qvickly transaction id.
	 * @return array
	 */
	public function request( $bco_transaction_id ) {
		$request_url  = $this->base_url;
		$request_args = apply_filters( 'bco_get_payment_args', $this->get_request_args( $bco_transaction_id ) );

		$response = wp_remote_request( $request_url, $request_args );
		$code     = wp_remote_retrieve_response_code( $response );

		// Log the request.
		$log = BCO_Logger::format_log( $bco_transaction_id, 'GET', 'BCO get payment', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
		BCO_Logger::log( $log );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		return $formated_response;
	}

	/**
	 * Gets the request body.
	 *
	 * @param string $bco_transaction_id Qvickly transaction id.
	 * @return array
	 */
	public function get_body( $bco_transaction_id ) {
		$data         = $this->get_request_data( $bco_transaction_id );
		$request_body = array(
			'credentials' => array(
				'id'      => $this->id,
				'hash'    => hash_hmac( 'sha512', wp_json_encode( $data ), $this->secret ),
				'test'    => $this->test,
				'version' => $this->version,
				'client'  => $this->client,
			),
			'data'        => $data,
			'function'    => 'getPaymentinfo',
		);
		return $request_body;
	}

	/**
	 * Gets the request args for the API call.
	 *
	 * @param string $bco_transaction_id Qvickly transaction id.
	 * @return array
	 */
	public function get_request_args( $bco_transaction_id ) {
		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode( $this->get_body( $bco_transaction_id ) ),
			'timeout' => apply_filters( 'bco_set_timeout', 10 ),
		);
	}

	/**
	 * Get needed data for the request.
	 *
	 * @param string $bco_transaction_id Qvickly transaction id.
	 * @return array
	 */
	public function get_request_data( $bco_transaction_id ) {
		return array( 'number' => $bco_transaction_id );
	}
}
