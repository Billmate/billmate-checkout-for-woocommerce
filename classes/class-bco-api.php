<?php
/**
 * API Class file.
 *
 * @package Billmate_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACO_API class.
 *
 * Class that has functions for the Billmate communication.
 */
class BCO_API {

	/**
	 * Init Billmate Checkout.
	 *
	 * @return mixed
	 */
	public function request_init_checkout( $order_id = '' ) {
		$request  = new BCO_Request_Init_Checkout();
		$response = $request->request( $order_id );

		return $this->check_for_api_error( $response );
	}

	/**
	 * Checks for WP Errors and returns either the response as array or a false.
	 *
	 * @param array $response The response from the request.
	 * @return mixed
	 */
	private function check_for_api_error( $response ) {
		if ( is_wp_error( $response ) ) {
			bco_extract_error_message( $response );
			return false;
		}
		return $response;
	}
}
