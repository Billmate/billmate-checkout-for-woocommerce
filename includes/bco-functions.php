<?php
/**
 * Functions file for the plugin.
 *
 * @package  Billmate_Checkout/Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Confirms and finishes the Billmate Order for processing.
 *
 * @param int    $order_id The WooCommerce Order id.
 * @param string $bco_payment_number Billmate payment number.
 * @return void
 */
function bco_confirm_billmate_order( $order_id = null, $bco_payment_number ) {
	if ( $order_id ) {
		$order          = wc_get_order( $order_id );
		$billmate_order = BCO_WC()->api->request_get_payment( $bco_payment_number );

		// Payment complete and set transaction id.
		// translators: Billmate purchase ID.
		$note = sprintf( __( 'Payment via Billmate Checkout. Purchase ID: %s', 'billmate-checkout-for-woocommerce' ), sanitize_text_field( '123456789' ) );
		$order->add_order_note( $note );
		$order->payment_complete( $bco_payment_number );
		do_action( 'bco_wc_payment_complete', $order_id, $billmate_order );
		// TODO: check if order status from billmate before we make payment complete.
	}
}


/**
 * Billmate Checkout iframe.
 *
 * @return void
 */
function bco_show_iframe() {
	$billmate_order = bco_init_checkout();
	do_action( 'bco_show_iframe', $billmate_order );
	echo '<iframe name="checkout_iframe" id="checkout" src="' . $billmate_order['data']['url'] . '" sandbox="allow-same-origin allow-scripts allow-modals allow-popups allow-forms allow-top-navigation" style="width:100%;min-height:800px;border:none;" scrolling="no"></iframe>'; // phpcs:ignore
}


/**
 * Init checkout.
 *
 * @return void
 */
function bco_init_checkout() {
	$bco_settings  = get_option( 'woocommerce_bco_settings' );
	$checkout_flow = ( isset( $bco_settings['checkout_flow'] ) ) ? $bco_settings['checkout_flow'] : 'checkout';

	if ( 'checkout' === $checkout_flow ) { // Only calculate cart when checkout flow is checkout. No cart calculation needed for Pay for Order flow.
		// Need to calculate these here, because WooCommerce hasn't done it yet.
		WC()->cart->calculate_fees();
		WC()->cart->calculate_shipping();
		WC()->cart->calculate_totals();
		// Initialize payment.
		$billmate_order = BCO_WC()->api->request_init_checkout();
		if ( ! $billmate_order ) { // TODO: handle error.
			return;
		}

		WC()->session->set( 'bco_wc_payment_number', $billmate_order['data']['number'] );
		WC()->session->set( 'bco_wc_order_id', $billmate_order['data']['orderid'] );
		return $billmate_order;

	} else { // If Checkout flow is Pay for Order then we have access to order id.
		global $wp;
		$order_id = $wp->query_vars['order-pay'];
		// Initialize payment.
		$billmate_order = BCO_WC()->api->request_init_checkout( $order_id );
		if ( ! $billmate_order ) { // TODO: handle error.
			return;
		}

		update_post_meta( $order_id, '_transaction_id', $billmate_order['data']['number'] );
		WC()->session->set( 'bco_wc_payment_number', $billmate_order['data']['number'] );
		WC()->session->set( 'bco_wc_order_id', $billmate_order['data']['orderid'] );

		// Extract the hash from the Billmate checkout url.
		$url   = $billmate_order['data']['url'];
		$parts = explode( '/', $url );
		$sum   = count( $parts );
		$hash  = ( $parts[ $sum - 1 ] == 'test' ) ? str_replace( '\\', '', $parts[ $sum - 2 ] ) : str_replace( '\\', '', $parts[ $sum - 1 ] );
		WC()->session->set( 'bco_wc_hash', $hash );

		return $billmate_order;
	}

}

/**
 * Shows select another payment method button in Billmate Checkout page.
 */
function bco_wc_show_another_gateway_button() {
	$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

	if ( count( $available_gateways ) > 1 ) {
		$settings                   = get_option( 'woocommerce_bco_settings' );
		$select_another_method_text = isset( $settings['select_another_method_text'] ) && '' !== $settings['select_another_method_text'] ? $settings['select_another_method_text'] : __( 'Select another payment method', 'klarna-checkout-for-woocommerce' );

		?>
		<p class="billmate-checkout-select-other-wrapper">
			<a class="checkout-button button" href="#" id="billmate-checkout-select-other">
				<?php echo esc_html( $select_another_method_text ); ?>
			</a>
		</p>
		<?php
	}
}


/**
 * Unsets the sessions used by the plguin.
 *
 * @return void
 */
function bco_wc_unset_sessions() {
	WC()->session->__unset( 'bco_wc_payment_number' );
	WC()->session->__unset( 'bco_wc_order_id' );
	WC()->session->__unset( 'bco_wc_hash' );
}


/**
 * Prints error message as notices.
 *
 * @param WP_Error $wp_error A WordPress error object.
 * @return void
 */
function bco_extract_error_message( $wp_error ) {
	wc_print_notice( $wp_error->get_error_message(), 'error' );
}
