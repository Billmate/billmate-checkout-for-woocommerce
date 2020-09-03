<?php
/**
 * Settings form fields for the gateway.
 *
 * @package Billmate_Checkout/Includes
 */

$settings = array(
	'enabled'                    => array(
		'title'   => __( 'Enable/Disable', 'billmate-checkout-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable ' . $this->method_title, 'billmate-checkout-for-woocommerce' ), // phpcs:ignore
		'default' => 'yes',
	),
	'title'                      => array(
		'title'       => __( 'Title', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'billmate-checkout-for-woocommerce' ),
		'default'     => __( $this->method_title, 'billmate-checkout-for-woocommerce' ), // phpcs:ignore
		'desc_tip'    => true,
	),
	'description'                => array(
		'title'       => __( 'Description', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'textarea',
		'default'     => __( 'Pay with Billmate via invoice, card and direct bank payments.', 'billmate-checkout-for-woocommerce' ),
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'billmate-checkout-for-woocommerce' ),
	),
	'select_another_method_text' => array(
		'title'       => __( 'Other payment method button text', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Customize the <em>Select another payment method</em> button text that is displayed in checkout if using other payment methods than Billmate Checkout. Leave blank to use the default (and translatable) text.', 'billmate-checkout-for-woocommerce' ),
		'default'     => '',
		'desc_tip'    => true,
	),
	'testmode'                   => array(
		'title'   => __( 'Testmode', 'billmate-checkout-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Billmate Checkout testmode', 'billmate-checkout-for-woocommerce' ),
		'default' => 'no',
	),
	'debug'                      => array(
		'title'       => __( 'Debug Log', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'billmate-checkout-for-woocommerce' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log ' . $this->method_title . ' events in <code>%s</code>', 'billmate-checkout-for-woocommerce' ), wc_get_log_file_path( 'billmate_checkout' ) ), // phpcs:ignore
	),
	'checkout_layout'            => array(
		'title'       => __( 'Checkout layout', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'select',
		'options'     => array(
			'one_column_checkout' => __( 'One column checkout', 'billmate-checkout-for-woocommerce' ),
			'two_column_checkout' => __( 'Two column checkout', 'billmate-checkout-for-woocommerce' ),
		),
		'description' => __( 'Select the Billmate Checkout layout.', 'billmate-checkout-for-woocommerce' ),
		'default'     => 'two_column_checkout',
		'desc_tip'    => false,
	),
	'company_view'               => array(
		'title'       => __( 'Checkout mode', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'select',
		'options'     => array(
			'false' => __( 'Consumer', 'billmate-checkout-for-woocommerce' ),
			'true'  => __( 'Business', 'billmate-checkout-for-woocommerce' ),
		),
		'description' => __( 'Select if you want the checkout to default to B2C or B2B mode.', 'billmate-checkout-for-woocommerce' ),
		'default'     => 'false',
		'desc_tip'    => false,
	),
	'show_order_notes'           => array(
		'title'   => __( 'Show order notes', 'billmate-checkout-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Show Order notes field in checkout.', 'billmate-checkout-for-woocommerce' ),
		'default' => 'yes',
	),
	'logo'                       => array(
		'title'       => __( 'Logo', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Change logotype for the payment. Enter the file name of the logo uploaded in your Billmate online account. Leave blank to use the standard logo.', 'billmate-checkout-for-woocommerce' ),
		'default'     => '',
		'desc_tip'    => true,
	),
	// SE.
	'credentials_se'             => array(
		'title' => 'API Credentials Sweden',
		'type'  => 'title',
	),
	'merchant_id_se'             => array(
		'title'    => __( 'Client ID', 'billmate-checkout-for-woocommerce' ),
		'type'     => 'text',
		'default'  => '',
		'desc_tip' => true,
	),
	'api_key_se'                 => array(
		'title'    => __( 'Client Secret', 'billmate-checkout-for-woocommerce' ),
		'type'     => 'text',
		'default'  => '',
		'desc_tip' => true,
	),
);
return apply_filters( 'bco_settings', $settings );
