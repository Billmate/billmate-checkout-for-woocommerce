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
 * BCO_API class.
 *
 * Class that has functions for the Billmate communication.
 */
class BCO_API {

	/**
	 * Init Billmate Checkout.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return mixed
	 */
	public function request_init_checkout( $order_id = null ) {
		$request  = new BCO_Request_Init_Checkout();
		$response = $request->request( $order_id );

		return $this->check_for_api_error( $response );
	}

	/**
	 * Get Billmate Checkout.
	 *
	 * @param string $bco_wc_hash The Billmate checkout hash.
	 * @return mixed
	 */
	public function request_get_checkout( $bco_wc_hash = null ) {
		$request  = new BCO_Request_Get_Checkout();
		$response = $request->request( $bco_wc_hash );

		return $this->check_for_api_error( $response );
	}

	/**
	 * Update Billmate Checkout.
	 *
	 * @param string $bco_payment_number The Billmate payment number.
	 * @param string $order_id The WooCommerce order ID.
	 * @return mixed
	 */
	public function request_update_checkout( $bco_payment_number = null, $order_id = null ) {
		$request  = new BCO_Request_Update_Checkout();
		$response = $request->request( $bco_payment_number, $order_id );

		return $this->check_for_api_error( $response );
	}

	/**
	 * Get Billmate Payment.
	 *
	 * @param string $bco_transaction_id The Billmate transaction id.
	 * @return mixed
	 */
	public function request_get_payment( $bco_transaction_id = null ) {
		$request  = new BCO_Request_Get_Payment();
		$response = $request->request( $bco_transaction_id );

		return $this->check_for_api_error( $response );
	}

	/**
	 * Update Billmate Payment.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return mixed
	 */
	public function request_update_payment( $order_id = null ) {
		$request  = new BCO_Request_Update_Payment();
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
