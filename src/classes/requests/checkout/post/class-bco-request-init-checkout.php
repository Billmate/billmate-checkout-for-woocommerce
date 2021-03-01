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
	 * @param string $order_id WooCommerce order id.
	 * @return array
	 */
	public function request( $order_id = null ) {
		$request_url  = $this->base_url;
		$request_args = apply_filters( 'bco_init_checkout_args', $this->get_request_args( $order_id ) );

		$response = wp_remote_request( $request_url, $request_args );
		$code     = wp_remote_retrieve_response_code( $response );

		// Log the request.
		$log = BCO_Logger::format_log( $order_id, 'POST', 'BCO init checkout', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
		BCO_Logger::log( $log );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		return $formated_response;
	}

	/**
	 * Gets the request body.
	 *
	 * @param string $order_id WooCommerce order id.
	 * @return array
	 */
	public function get_body( $order_id ) {
		$data = ( 'checkout' === $this->checkout_flow ) ? $this->get_request_cart_data() : $this->get_request_order_data( $order_id );

		$request_body = array(
			'credentials' => array(
				'id'      => $this->id,
				'hash'    => hash_hmac( 'sha512', wp_json_encode( $data ), $this->secret ),
				'test'    => $this->test,
				'version' => $this->version,
				'client'  => $this->client,
			),
			'data'        => $data,
			'function'    => 'initCheckout',
		);
		return $request_body;
	}

	/**
	 * Gets the request args for the API call.
	 *
	 * @param string $order_id WooCommerce order id.
	 * @return array
	 */
	public function get_request_args( $order_id ) {
		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode( $this->get_body( $order_id ) ),
			'timeout' => apply_filters( 'bco_set_timeout', 10 ),
		);
	}

	/**
	 * Request order data
	 *
	 * @param string $order_id WooCommerce order id.
	 * @return array $data order data.
	 */
	public function get_request_order_data( $order_id ) {
		$order = wc_get_order( $order_id );
		$data  = array(
			'CheckoutData' =>
			array(
				'terms'               => get_permalink( wc_get_page_id( 'terms' ) ),
				'companyView'         => $this->company_view,
				'hideShippingAddress' => $this->hide_shipping_address,
			),
			'PaymentData'  => BCO_Order_Payment_Data_Helper::get_payment_data( $order ),
			'Customer'     =>
			array(
				'Billing'  => BCO_Order_Customer_Helper::get_customer_billing( $order ),
				'Shipping' => BCO_Order_Customer_Helper::get_customer_shipping( $order ),
			),
			'Articles'     => BCO_Order_Articles_Helper::get_articles( $order ),
			'Cart'         =>
			array(
				'Handling' => BCO_Order_Cart_Helper::get_order_cart_handling( $order ),
				'Shipping' => BCO_Order_Cart_Helper::get_order_cart_shipping( $order ),
				'Total'    => BCO_Order_Cart_Helper::get_order_cart_total( $order ),
			),
		);
		if ( ! empty( wc_privacy_policy_page_id() ) ) {
			$data['CheckoutData']['privacyPolicy'] = get_permalink( wc_privacy_policy_page_id() );
		}
		return $data;
	}


	/**
	 * Request cart data
	 *
	 * @return array $data cart data.
	 */
	public function get_request_cart_data() {
		$data = array(
			'CheckoutData' =>
			array(
				'terms'               => get_permalink( wc_get_page_id( 'terms' ) ),
				'companyView'         => $this->company_view,
				'hideShippingAddress' => $this->hide_shipping_address,
			),
			'PaymentData'  => BCO_Cart_Payment_Data_Helper::get_payment_data(),
			'Articles'     => BCO_Cart_Articles_Helper::get_articles(),
			'Cart'         =>
			array(
				'Handling' => BCO_Cart_Cart_Helper::get_handling(),
				'Shipping' => BCO_Cart_Cart_Helper::get_shipping(),
				'Total'    => BCO_Cart_Cart_Helper::get_total(),
			),
		);
		if ( ! empty( wc_privacy_policy_page_id() ) ) {
			$data['CheckoutData']['privacyPolicy'] = get_permalink( wc_privacy_policy_page_id() );
		}
		return $data;
	}
}
