<?php
/**
 * Update Checkout request class
 *
 * @package Billmate_Checkout/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Update checkout request class
 */
class BCO_Request_Update_Checkout extends BCO_Request {

	/**
	 * Makes the request.
	 *
	 * @param string $bco_payment_number The Billmate payment number.
	 * @return array
	 */
	public function request( $bco_payment_number = null ) {
		$request_url  = $this->base_url;
		$request_args = apply_filters( 'bco_update_checkout_args', $this->get_request_args( $bco_payment_number ) );

		$response = wp_remote_request( $request_url, $request_args );
		$code     = wp_remote_retrieve_response_code( $response );

		// Log the request.
		$log = BCO_Logger::format_log( $bco_payment_number, 'POST', 'BCO update checkout', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
		BCO_Logger::log( $log );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		return $formated_response;
	}

	/**
	 * Gets the request body.
	 *
	 * @param string $bco_payment_number The Billmate payment number.
	 * @return array
	 */
	public function get_body( $bco_payment_number ) {
		$data         = $this->get_request_cart_data( $bco_payment_number );
		$request_body = array(
			'credentials' => array(
				'id'   => $this->id,
				'hash' => hash_hmac( 'sha512', wp_json_encode( $data ), $this->secret ),
				'test' => $this->test,
			),
			'data'        => $data,
			'function'    => 'updateCheckout',
		);
		return $request_body;
	}

	/**
	 * Gets the request args for the API call.
	 *
	 * @param string $bco_payment_number The Billmate payment number.
	 * @return array
	 */
	public function get_request_args( $bco_payment_number ) {
		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode( $this->get_body( $bco_payment_number ) ),
			'timeout' => apply_filters( 'bco_set_timeout', 10 ),
		);
	}

	/**
	 * Request cart data
	 *
	 * @param string $bco_payment_number The Billmate payment number.
	 * @return array $data cart data.
	 */
	public function get_request_cart_data( $bco_payment_number ) {
		$data = array(
			'Articles'    => BCO_Cart_Articles_Helper::get_articles(),
			'Cart'        =>
			array(
				'Total'    => BCO_Cart_Cart_Helper::get_total(),
				'Shipping' => BCO_Cart_Cart_Helper::get_shipping(),
			),
			'PaymentData' => array(
				'number' => $bco_payment_number,
			),
		);
		return $data;
	}
}
