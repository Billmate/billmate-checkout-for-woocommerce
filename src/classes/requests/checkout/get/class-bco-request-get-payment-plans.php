<?php
/**
 * Get payment plans request class
 *
 * @package Billmate_Checkout/Classes/Get/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get payment request class
 */
class BCO_Request_Get_Payment_Plans extends BCO_Request {

	/**
	 * Makes the request.
	 *
	 * @param string $price The product price used for the payment plans calculation.
	 * @return array
	 */
	public function request( $price ) {
		$request_url  = $this->base_url;
		$request_args = apply_filters( 'bco_get_payment_plan_args', $this->get_request_args( $price ) );

		$response = wp_remote_request( $request_url, $request_args );
		$code     = wp_remote_retrieve_response_code( $response );

		// Log the request.
		$log = BCO_Logger::format_log( '', 'GET', 'BCO get payment plans', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
		BCO_Logger::log( $log );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		return $formated_response;
	}

	/**
	 * Gets the request body.
	 *
	 * @param string $price The product price used for the payment plans calculation.
	 * @return array
	 */
	public function get_body( $price ) {
		$data         = $this->get_request_data( $price );
		$request_body = array(
			'credentials' => array(
				'id'      => $this->id,
				'hash'    => hash_hmac( 'sha512', wp_json_encode( $data ), $this->secret ),
				'test'    => $this->test,
				'version' => $this->version,
				'client'  => $this->client,
			),
			'data'        => $data,
			'function'    => 'getPaymentplans',
		);
		return $request_body;
	}

	/**
	 * Gets the request args for the API call.
	 *
	 * @param string $price The product price used for the payment plans calculation.
	 * @return array
	 */
	public function get_request_args( $price ) {
		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode( $this->get_body( $price ) ),
			'timeout' => apply_filters( 'bco_set_timeout', 10 ),
		);
	}

	/**
	 * Get needed data for the request.
	 *
	 * @param string $price The product price used for the payment plans calculation.
	 * @return array
	 */
	public function get_request_data( $price ) {
		return array(
			'PaymentData' => array(
				'totalwithtax' => $price,
				'country'      => 'se',
				'currency'     => get_woocommerce_currency(),
				'language'     => 'sv',
			),
		);
	}
}
