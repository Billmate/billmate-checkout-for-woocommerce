<?php
/**
 * Main request class
 *
 * @package Billmate_Checkout/Classes/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main request class
 */
class BCO_Request {
	/**
	 * The request enviroment.
	 *
	 * @var $enviroment
	 */
	public $enviroment;

    public $billmate_settings;
    public $testmode;
    public $test;
    public $base_url;
    public $id;
    public $secret;
    public $checkout_flow;
    public $company_view;
    public $hide_shipping_address;
    public $version;
    public $client;

    /**
	 * Class constructor.
	 */
	public function __construct() {
		$this->set_environment_variables();
	}

	/**
	 * Returns headers.
	 *
	 * @return array
	 */
	public function get_headers() {
		return array(
			'Content-Type' => 'application/json',
		);
	}

	/**
	 * Sets the environment.
	 *
	 * @return void
	 */
	public function set_environment_variables() {
		$this->billmate_settings     = get_option( 'woocommerce_bco_settings' );
		$this->testmode              = $this->billmate_settings['testmode'];
		$this->test                  = ( 'yes' === $this->testmode ) ? 'true' : 'false';
		$this->base_url              = BILLMATE_CHECKOUT_ENV;
		$this->id                    = $this->billmate_settings['merchant_id_se'];
		$this->secret                = $this->billmate_settings['api_key_se'];
		$this->checkout_flow         = ( isset( $this->billmate_settings['checkout_flow'] ) ) ? $this->billmate_settings['checkout_flow'] : 'checkout';
		$this->company_view          = ( isset( $this->billmate_settings['company_view'] ) ) ? $this->billmate_settings['company_view'] : 'false';
		$this->hide_shipping_address = ( 'yes' === $this->billmate_settings['hide_shipping_address'] ) ? 'true' : 'false';
		$this->version               = '2.2.2';
		$this->client                = 'WooCommerce_v2:' . BILLMATE_CHECKOUT_VERSION;

	}

	/**
	 * Checks response for any error.
	 *
	 * @param object $response The response.
	 * @param array  $request_args The request args.
	 * @param string $request_url The request URL.
	 * @return object|array
	 */
	public function process_response( $response, $request_args = array(), $request_url = '' ) {
		// Check if response is a WP_Error, and return it back if it is.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = 'URL: ' . $request_url . ' - ' . wp_json_encode( $request_args );
		// Check the status code, if its not between 200 and 299 then its an error.
		if ( wp_remote_retrieve_response_code( $response ) < 200 || wp_remote_retrieve_response_code( $response ) > 299 ) {
			$error_message = '';
			// Get the error messages.
			if ( null !== $response['response'] ) {
				$bco_error_code    = isset( $response['response']['code'] ) ? $response['response']['code'] . ' ' : '';
				$bco_error_message = isset( $response['response']['message'] ) ? $response['response']['message'] . ' ' : '';
				$error_message     = $bco_error_code . $bco_error_message;
			}

			if ( null !== json_decode( $response['body'], true ) ) {
				$errors = json_decode( $response['body'], true );
				foreach ( $errors as $error => $bco_error_messages ) {
					foreach ( $bco_error_messages as $bco_error_message ) {
						$error_message .= $bco_error_message . ' ';
					}
				}
			}
			return new WP_Error( wp_remote_retrieve_response_code( $response ), $error_message, $data );
		}

		// If the response body has code, its an error. Request itself went OK.
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $response_body['code'] ) ) {
			$code          = $response_body['code'];
			$error_message = $response_body['message'];
			return new WP_Error( $code, $error_message, $data );
		}
		return $response_body;
	}
}
