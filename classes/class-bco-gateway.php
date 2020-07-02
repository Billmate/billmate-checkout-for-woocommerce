<?php
/**
 * Gateway class file.
 *
 * @package Billmate_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Gateway class.
 */
class BCO_Gateway extends WC_Payment_Gateway {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->id                 = 'bco';
		$this->method_title       = __( 'Billmate Checkout', 'billmate-checkout-for-woocommerce' );
		$this->icon               = '';
		$this->method_description = __( 'Allows payments through ' . $this->method_title . '.', 'billmate-checkout-for-woocommerce' ); // phpcs:ignore

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->settings      = $this->get_option( 'woocommerce_bco_settings' );
		$this->enabled       = $this->get_option( 'enabled' );
		$this->title         = $this->get_option( 'title' );
		$this->description   = $this->get_option( 'description' );
		$this->debug         = $this->get_option( 'debug' );
		$this->testmode      = 'yes' === $this->get_option( 'testmode' );
		$this->checkout_flow = ( isset( $this->settings['checkout_flow'] ) ) ? $this->settings['checkout_flow'] : 'checkout';

		// Supports.
		$this->supports = array(
			'products',
			'refunds',
		);

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'billmate_thank_you' ) );
	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 *
	 * @return boolean
	 */
	public function is_available() {
		if ( 'yes' === $this->enabled ) {
			// Do checks here.
			return true;
		}
		return false;
	}

	/**
	 * Processes the WooCommerce Payment
	 *
	 * @param string $order_id The WooCommerce order ID.
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		WC()->session->set( 'bco_wc_order_id', $order_id );

		$confirmation_url = add_query_arg(
			array(
				'bco_confirm' => 'yes',
				'bco_flow'    => 'checkout',
				'wc_order_id' => $order_id,
			),
			$order->get_checkout_order_received_url()
		);
		$response         = array(
			'redirect_url' => $confirmation_url,
			'time'         => microtime(),
		);
		return array(
			'result'   => 'success',
			'redirect' => ( 'checkout' === $this->checkout_flow ) ? '#billmate-success=' . base64_encode( wp_json_encode( $response ) ) : $order->get_checkout_payment_url(), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- Base64 used to give a unique nondescript string.
		);
	}

	/**
	 * This plugin doesn't handle order management, but it allows Billmate Order Management plugin to process refunds
	 * and then return true or false.
	 *
	 * @param string $order_id The WooCommerce order ID.
	 * @param float  $amount The amount to be refunded.
	 * @param string $reason The reason given for the refund.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		return apply_filters( 'wc_billmate_checkout_process_refund', false, $order_id, $amount, $reason );
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = include BILLMATE_CHECKOUT_PATH . '/includes/bco-form-fields.php';
	}

	/**
	 * Shows the Billmate thankyou on the wc thankyou page.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return void
	 */
	public function billmate_thank_you( $order_id ) {
		if ( $order_id ) {
			$order = wc_get_order( $order_id );
			$order->add_order_note( __( 'Order finalized in thankyou page.', 'billmate-checkout-for-woocommerce' ) );

			// Clear cart.
			WC()->cart->empty_cart();

			// Unset sessions.
			bco_wc_unset_sessions();
		}
	}
}

/**
 * Add Billmate_Checkout payment gateway
 *
 * @wp_hook woocommerce_payment_gateways
 * @param  array $methods All registered payment methods.
 * @return array $methods All registered payment methods.
 */
function add_bco_method( $methods ) {
	$methods[] = 'BCO_Gateway';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_bco_method' );
