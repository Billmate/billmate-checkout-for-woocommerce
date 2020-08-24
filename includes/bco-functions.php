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
 * Billmate Checkout iframe.
 *
 * @return void
 */
function bco_show_iframe() {
	$billmate_order = bco_init_checkout();
	do_action( 'bco_show_iframe', $billmate_order );
	echo '<iframe name="checkout_iframe" id="checkout" src="' . WC()->session->get( 'bco_wc_checkout_url' ) . '" sandbox="allow-same-origin allow-scripts allow-modals allow-popups allow-forms allow-top-navigation" style="width:100%;min-height:800px;border:none;" scrolling="no"></iframe>'; // phpcs:ignore
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
		if ( WC()->session->get( 'bco_wc_hash' ) ) {

			// Try to update the order, if it fails try to create new order.
			$billmate_order = BCO_WC()->api->request_update_checkout( WC()->session->get( 'bco_wc_number' ) );
			if ( ! $billmate_order ) {
				// If update order failed try to create new order.
				WC()->session->set( 'bco_wc_temp_order_id', md5( uniqid( wp_rand(), true ) ) );
				$billmate_order = BCO_WC()->api->request_init_checkout();
				if ( ! $billmate_order ) {
					// If failed then bail.
					return;
				}
				WC()->session->set( 'bco_wc_number', $billmate_order['data']['number'] );
				WC()->session->set( 'bco_wc_checkout_url', $billmate_order['data']['url'] . '?activateJsEvents=1' );
				set_checkout_hash( $billmate_order['data']['url'] );
				return $billmate_order;
			}
			WC()->session->set( 'bco_wc_checkout_url', $billmate_order['data']['url'] . '?activateJsEvents=1' );
			set_checkout_hash( $billmate_order['data']['url'] );
			return $billmate_order;

		} else {
			// Initialize payment.
			WC()->session->set( 'bco_wc_temp_order_id', md5( uniqid( wp_rand(), true ) ) );
			$billmate_order = BCO_WC()->api->request_init_checkout();
			if ( ! $billmate_order ) {
				return;
			}
			WC()->session->set( 'bco_wc_number', $billmate_order['data']['number'] );
			WC()->session->set( 'bco_wc_checkout_url', $billmate_order['data']['url'] . '?activateJsEvents=1' );
			set_checkout_hash( $billmate_order['data']['url'] );

			return $billmate_order;
		}
	} else { // If Checkout flow is Pay for Order then we have access to order id.
		global $wp;
		$order_id = $wp->query_vars['order-pay'];
		// Initialize payment.
		$billmate_order = BCO_WC()->api->request_init_checkout( $order_id );
		if ( ! $billmate_order ) {
			return;
		}

		WC()->session->set( 'bco_wc_order_id', $billmate_order['data']['orderid'] );
		WC()->session->set( 'bco_wc_checkout_url', $billmate_order['data']['url'] );
		set_checkout_hash( $billmate_order['data']['url'] );

		return $billmate_order;
	}

}

/**
 * Set checkout hash.
 *
 * @param string $url The checkout url.
 * @return void
 */
function set_checkout_hash( $url ) {
	// Extract the hash from the Billmate checkout url.
	$parts = explode( '/', $url );
	$sum   = count( $parts );
	$hash  = ( 'test' === $parts[ $sum - 1 ] ) ? str_replace( '\\', '', $parts[ $sum - 2 ] ) : str_replace( '\\', '', $parts[ $sum - 1 ] );
	// Set chekout hash as session variable.
	WC()->session->set( 'bco_wc_hash', $hash );
}

/**
 * Shows select another payment method button in Billmate Checkout page.
 */
function bco_wc_show_another_gateway_button() {
	$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

	if ( count( $available_gateways ) > 1 ) {
		$settings                   = get_option( 'woocommerce_bco_settings' );
		$select_another_method_text = isset( $settings['select_another_method_text'] ) && '' !== $settings['select_another_method_text'] ? $settings['select_another_method_text'] : __( 'Select another payment method', 'billmate-checkout-for-woocommerce' );

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
 * Set Billmate Checkout payment method tile.
 *
 * @param string $order_id The WooCommerce order id.
 * @param array  $bco_order The Billmate order.
 * @return void
 */
function bco_set_payment_method_title( $order_id, $bco_order = array() ) {
	$bco_payment_method = '';
	if ( isset( $bco_order['data']['PaymentData']['method'] ) ) {
		$bco_payment_method = $bco_order['data']['PaymentData']['method'];
		update_post_meta( $order_id, '_billmate_payment_method_id', $bco_payment_method );
	}

	switch ( $bco_payment_method ) {
		case '1':
			$method_title = __( 'Billmate Invoice', 'billmate-checkout-for-woocommerce' );
			break;
		case '2':
			$method_title = __( 'Billmate Invoice', 'billmate-checkout-for-woocommerce' );
			break;
		case '4':
			$method_title = __( 'Billmate Part Payment', 'billmate-checkout-for-woocommerce' );
			break;
		case '8':
			$method_title = __( 'Billmate Card Payment', 'billmate-checkout-for-woocommerce' );
			break;
		case '16':
			$method_title = __( 'Billmate Bank Payment', 'billmate-checkout-for-woocommerce' );
			break;
		case '24':
			$method_title = __( 'Billmate Card/Bank Payment', 'billmate-checkout-for-woocommerce' );
			break;
		case '32':
			$method_title = __( 'Billmate Cash Payment', 'billmate-checkout-for-woocommerce' );
			break;
		default:
			$method_title = __( 'Billmate Checkout', 'billmate-checkout-for-woocommerce' );
	}
	$order = wc_get_order( $order_id );
	$order->set_payment_method_title( $method_title );
	$order->save();
}


/**
 * Unsets the sessions used by the plguin.
 *
 * @return void
 */
function bco_wc_unset_sessions() {
	WC()->session->__unset( 'bco_wc_order_id' );
	WC()->session->__unset( 'bco_wc_hash' );
	WC()->session->__unset( 'bco_wc_checkout_url' );
	WC()->session->__unset( 'bco_wc_number' );
	WC()->session->__unset( 'bco_wc_temp_order_id' );
}


/**
 * Prints error message as notices.
 *
 * @param WP_Error $wp_error A WordPress error object.
 * @return void
 */
function bco_extract_error_message( $wp_error ) {
	wc_print_notice( utf8_decode( $wp_error->get_error_message() ), 'error' );
}


/**
 * Confirm Billmate order.
 *
 * @param string   $order_id The WooCommerce order id.
 * @param WC_Order $order The WooCommerce order id.
 * @param array    $bco_checkout The Billmate checkout data.
 */
function bco_confirm_billmate_order( $order_id, $order, $bco_checkout ) {
	if ( is_object( $order ) && ! $order->has_status( array( 'on-hold', 'processing', 'completed' ) ) ) {
		$bco_order_number = $bco_checkout['data']['PaymentData']['order']['number'];
		update_post_meta( $order_id, '_transaction_id', $bco_order_number );

		// Make get_payment request if we have Billmate order number.
		if ( '' !== $bco_order_number ) {
			$bco_order = BCO_WC()->api->request_get_payment( $bco_order_number );
		}
		// Set payment method title.
		bco_set_payment_method_title( $order_id, $bco_order );

		// Confirm order.
		switch ( strtolower( $bco_checkout['data']['PaymentData']['order']['status'] ) ) {
			case 'pending':
				// Translators: Billmate transaction id.
				$note = sprintf( __( 'Order is PENDING APPROVAL by Billmate. Please visit Billmate Online for the latest status on this order. Billmate Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_order_number ) );
				$order->add_order_note( $note );
				$order->update_status( 'on-hold' );

				break;
			case 'created':
				// Translators: Billmate transaction id.
				$note = sprintf( __( 'Payment via Billmate Checkout. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_order_number ) );
				$order->add_order_note( $note );
				$order->payment_complete( $bco_order_number );

				do_action( 'bco_wc_payment_complete', $order_id, $bco_checkout );
				break;
			case 'paid':
				// Translators: Billmate transaction id.
				$note = sprintf( __( 'Payment via Billmate Checkout. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_order_number ) );
				$order->add_order_note( $note );
				$order->payment_complete( $bco_order_number );

				do_action( 'bco_wc_payment_complete', $order_id, $bco_checkout );
				break;
			case 'cancelled':
				break;
			case 'failed':
				break;
		}
	}

}


/**
 * Confirm Billmate redirect order.
 *
 * @param string   $order_id The WooCommerce order id.
 * @param WC_Order $order The WooCommerce order.
 * @param array    $data The content data from Billmate redirect.
 * @return void
 */
function bco_confirm_billmate_redirect_order( $order_id, $order, $data ) {
	$bco_transaction_id = get_post_meta( $order_id, '_billmate_transaction_id', true );
	update_post_meta( $order_id, '_transaction_id', $bco_transaction_id );

	if ( is_object( $order ) && ! $order->has_status( array( 'on-hold', 'processing', 'completed' ) ) ) {

		switch ( strtolower( $data['status'] ) ) {
			case 'pending':
				// Translators: Billmate transaction id.
				$note = sprintf( __( 'Order is PENDING APPROVAL by Billmate. Please visit Billmate Online for the latest status on this order. Billmate Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_transaction_id ) );
				$order->add_order_note( $note );
				$order->update_status( 'on-hold' );

				break;
			case 'created':
				// Translators: Billmate transaction id.
				$note = sprintf( __( 'Payment via Billmate Checkout. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_transaction_id ) );
				$order->add_order_note( $note );
				$order->payment_complete( $bco_transaction_id );

				do_action( 'bco_wc_payment_complete', $order_id, $data );
				break;
			case 'paid':
				// Translators: Billmate transaction id.
				$note = sprintf( __( 'Payment via Billmate Checkout. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_transaction_id ) );
				$order->add_order_note( $note );
				$order->payment_complete( $bco_transaction_id );

				do_action( 'bco_wc_payment_complete', $order_id, $data );
				break;
			case 'cancelled':
				break;
			case 'failed':
				break;
		}
	}
}
