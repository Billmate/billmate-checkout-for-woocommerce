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
	 * @param string $bco_wc_hash The Qvickly checkout hash.
	 * @return array
	 */
	public function request( $bco_wc_hash = null ) {
		$request_url  = $this->base_url;
		$request_args = apply_filters( 'bco_get_checkout_args', $this->get_request_args( $bco_wc_hash ) );

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
	 * @param string $bco_wc_hash The Qvickly checkout hash.
	 * @return array
	 */
	public function get_body( $bco_wc_hash ) {
		$data         = $this->get_request_data( $bco_wc_hash );
		$request_body = array(
			'credentials' => array(
				'id'      => $this->id,
				'hash'    => hash_hmac( 'sha512', wp_json_encode( $data ), $this->secret ),
				'test'    => $this->test,
				'version' => $this->version,
				'client'  => $this->client,
			),
			'data'        => $data,
			'function'    => 'getCheckout',
		);
		return $request_body;
	}

	/**
	 * Gets the request args for the API call.
	 *
	 * @param string $bco_wc_hash The Qvickly checkout hash.
	 * @return array
	 */
	public function get_request_args( $bco_wc_hash ) {
		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode( $this->get_body( $bco_wc_hash ) ),
			'timeout' => apply_filters( 'bco_set_timeout', 10 ),
		);
	}

	/**
	 * Get needed data for the request.
	 *
	 * @param string $bco_wc_hash The Qvickly checkout hash.
	 * @return array
	 */
	public function get_request_data( $bco_wc_hash ) {
		return array(
			'PaymentData' => array(
				'hash' => $bco_wc_hash,
			),
		);
	}
}
