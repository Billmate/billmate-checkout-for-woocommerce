<?php
/**
 * Init Checkout request class
 *
 * @package Billmate_Checkout/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Init checkout request class
 */
class BCO_Request_Init_Checkout extends BCO_Request {

	/**
	 * Makes the request.
	 *
	 * @return array
	 */
	public function request() {
		$request_url  = $this->base_url . $this->test;
		$request_args = apply_filters( 'bco_init_checkout_args', $this->get_request_args() );

		$response = wp_remote_request( $request_url, $request_args );
		$code     = wp_remote_retrieve_response_code( $response );

		// Log the request.
		$log = BCO_Logger::format_log( '', 'POST', 'BCO init checkout', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
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
		$data         = $this->get_data();
		$request_body = array(
			'credentials' => array(
				'id'   => $this->id,
				'hash' => hash_hmac( 'sha512', wp_json_encode( $data ), $this->secret ),
			),
			'data'        => $data,
			'function'    => 'initCheckout',
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
	 * Request data
	 *
	 * @return array $data data.
	 */
	public function get_data() {
		$data = array(
			'CheckoutData' =>
			array(
				'terms' => 'https://www.mystore.se/termspage',
			),
			'PaymentData'  =>
			array(
				'currency'     => 'SEK',
				'language'     => 'sv',
				'country'      => 'SE',
				'autoactivate' => '0',
				'orderid'      => 'P123456789',
				'logo'         => 'Logo2.jpg',
				'accepturl'    => 'https://www.mystore.se/completedpayment',
				'cancelurl'    => 'https://www.mystore.se/failedpayment',
				'returnmethod' => '',
				'callbackurl'  => 'https://www.mystore.se/callback.php',
			),
			'PaymentInfo'  =>
			array(
				'paymentdate'    => '2014-07-31',
				'yourreference'  => 'Purchaser X',
				'ourreference'   => 'Seller Y',
				'projectname'    => 'Project Z',
				'deliverymethod' => 'Post',
				'deliveryterms'  => 'FOB',
				'autocredit'     => 'false',
			),
			'Customer'     =>
			array(
				'nr'       => '12',
				'pno'      => '550101-1018',
				'Billing'  =>
				array(
					'firstname' => 'Testperson',
					'lastname'  => 'Approved',
					'company'   => 'Company',
					'street'    => 'Teststreet',
					'street2'   => 'Street2',
					'zip'       => '12345',
					'city'      => 'Testcity',
					'country'   => 'Sverige',
					'phone'     => '0712-345678',
					'email'     => 'test@developer.billmate.se',
				),
				'Shipping' =>
				array(
					'firstname' => 'Testperson',
					'lastname'  => 'Approved',
					'company'   => 'Company',
					'street'    => 'Teststreet',
					'street2'   => 'Shipping Street2',
					'zip'       => '12345',
					'city'      => 'Testcity',
					'country'   => 'Sverige',
					'phone'     => '0711-345678',
				),
			),
			'Articles'     =>
			array(
				0 =>
				array(
					'artnr'      => 'A123',
					'title'      => 'Article 1',
					'quantity'   => '2',
					'aprice'     => '1234',
					'discount'   => '0',
					'withouttax' => '2468',
					'taxrate'    => '25',
				),
				1 =>
				array(
					'artnr'      => 'B456',
					'title'      => 'Article 2',
					'quantity'   => '3.5',
					'aprice'     => '56780',
					'discount'   => '10',
					'withouttax' => '178857',
					'taxrate'    => '25',
				),
			),
			'Cart'         =>
			array(
				'Handling' =>
				array(
					'withouttax' => '1000',
					'taxrate'    => '25',
				),
				'Shipping' =>
				array(
					'withouttax' => '3000',
					'taxrate'    => '25',
				),
				'Total'    =>
				array(
					'withouttax' => '185325',
					'tax'        => '46331',
					'rounding'   => '44',
					'withtax'    => '231700',
				),
			),
		);
		return $data;
	}
}
