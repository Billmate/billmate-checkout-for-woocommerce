<?php // phpcs:ignore
/**
 * Plugin Name:     Billmate Checkout for WooCommerce
 * Plugin URI:      http://krokedil.com/
 * Description:     Provides an Billmate Checkout gateway for WooCommerce.
 * Version:         1.0.0
 * Author:          Krokedil
 * Author URI:      http://krokedil.com/
 * Developer:       Krokedil
 * Developer URI:   http://krokedil.com/
 * Text Domain:     billmate-checkout-for-woocommerce
 * Domain Path:     /languages
 *
 * WC requires at least: 3.0
 * WC tested up to: 4.1.0
 *
 * Copyright:       © 2016-2020 Krokedil.
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Billmate_Checkout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'BILLMATE_CHECKOUT_VERSION', '1.0.0' );
define( 'BILLMATE_CHECKOUT_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'BILLMATE_CHECKOUT_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'BILLMATE_CHECKOUT_ENV', 'https://api.billmate.se' );

if ( ! class_exists( 'Billmate_Checkout_For_WooCommerce' ) ) {

	/**
	 * Main class for the plugin.
	 */
	class Billmate_Checkout_For_WooCommerce {
		/**
		 * The reference the *Singleton* instance of this class.
		 *
		 * @var $instance
		 */
		protected static $instance;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			// Initiate the plugin.
			add_action( 'plugins_loaded', array( $this, 'init' ) );
			add_action( 'wp_head', array( $this, 'confirm_order' ) );
		}

		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return self::$instance The *Singleton* instance.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Private clone method to prevent cloning of the instance of the
		 * *Singleton* instance.
		 *
		 * @return void
		 */
		private function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
		}
		/**
		 * Private unserialize method to prevent unserializing of the *Singleton*
		 * instance.
		 *
		 * @return void
		 */
		private function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
		}

		/**
		 * Initiates the plugin.
		 *
		 * @return void
		 */
		public function init() {
			load_plugin_textdomain( 'billmate-checkout-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

			$this->include_files();

			// Load scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

			// Set class variables.
			$this->api           = new BCO_API();
			$this->logger        = new BCO_Logger();
			$this->api_callbacks = new BCO_API_Callbacks();

			do_action( 'bco_initiated' );
		}

		/**
		 * Includes the files for the plugin
		 *
		 * @return void
		 */
		public function include_files() {
			// Classes.
			include_once BILLMATE_CHECKOUT_PATH . '/classes/class-bco-ajax.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/class-bco-api.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/class-bco-gateway.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/class-bco-logger.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/class-bco-templates.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/class-bco-api-callbacks.php';

			// Requests.
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/class-bco-request.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/checkout/post/class-bco-request-init-checkout.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/checkout/post/class-bco-request-update-checkout.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/checkout/post/class-bco-request-update-payment.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/checkout/get/class-bco-request-get-checkout.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/checkout/get/class-bco-request-get-payment.php';

			// Request Helpers.
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/helpers/order/class-bco-order-articles-helper.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/helpers/order/class-bco-order-cart-helper.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/helpers/order/class-bco-order-customer-helper.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/helpers/order/class-bco-order-payment-data-helper.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/helpers/cart/class-bco-cart-articles-helper.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/helpers/cart/class-bco-cart-cart-helper.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/helpers/cart/class-bco-cart-customer-helper.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/helpers/cart/class-bco-cart-payment-data-helper.php';

			// Includes.
			include_once BILLMATE_CHECKOUT_PATH . '/includes/bco-functions.php';

		}

		/**
		 * Confirm Billmate order.
		 *
		 * @return void
		 */
		public function confirm_order() {
			if ( isset( $_GET['bco_confirm'] ) && isset( $_GET['wc_order_id'] ) && isset( $_GET['bco_flow']) ) { // phpcs:ignore
				$bco_flow = filter_input( INPUT_GET, 'bco_flow', FILTER_SANITIZE_STRING );

				if ( 'pay_for_order_redirect' === $bco_flow ) {
					$order_id = isset( $_GET['wc_order_id'] ) ? sanitize_text_field( wp_unslash( $_GET['wc_order_id'] ) ) : ''; // phpcs:ignore
					$order    = wc_get_order( $order_id );

					$raw_data = file_get_contents( 'php://input' );
					parse_str( urldecode( $raw_data ), $result );
					$data = json_decode( $result['data'], true );
					update_post_meta( $order_id, '_billmate_transaction_id', $data['number'] );

					// Get Checkout and set payment method title.
					$bco_checkout = BCO_WC()->api->request_get_checkout( WC()->session->get( 'bco_wc_hash' ) );
					bco_set_payment_method_title( $order_id, $bco_checkout );

					bco_confirm_billmate_redirect_order( $order_id, $order, $data ); // Confirm.
					return;

				} elseif ( 'checkout_redirect' === $bco_flow ) {
					$order_id = WC()->session->get( 'bco_wc_order_id' );
					$order    = wc_get_order( $order_id );

					$raw_data = file_get_contents( 'php://input' );
					parse_str( urldecode( $raw_data ), $result );
					$data = json_decode( $result['data'], true );
					update_post_meta( $order_id, '_billmate_transaction_id', $data['number'] );

					// Get Checkout and set payment method title.
					$bco_checkout = BCO_WC()->api->request_get_checkout( WC()->session->get( 'bco_wc_hash' ) );
					bco_set_payment_method_title( $order_id, $bco_checkout );

					BCO_WC()->api->request_update_payment( $order_id ); // Update order id in Billmate.
					bco_confirm_billmate_redirect_order( $order_id, $order, $data ); // Confirm.
					header( 'Location:' . $order->get_checkout_order_received_url() ); // Redirect.
					exit;

				} elseif ( 'checkout' === $bco_flow ) {
					$order_id = isset( $_GET['wc_order_id'] ) ? sanitize_text_field( wp_unslash( $_GET['wc_order_id'] ) ) : ''; // phpcs:ignore
					$order    = wc_get_order( $order_id );

					$bco_checkout = BCO_WC()->api->request_get_checkout( WC()->session->get( 'bco_wc_hash' ) );
					if ( false !== $bco_checkout ) {
						update_post_meta( $order_id, '_billmate_transaction_id', $bco_checkout['data']['PaymentData']['order']['number'] );
						BCO_WC()->api->request_update_payment( $order_id ); // Update order id in Billmate.
						bco_confirm_billmate_order( $order_id, $order, $bco_checkout ); // Confirm order.
					}

					return;
				}
			}
		}

		/**
		 * Adds plugin action links
		 *
		 * @param array $links Plugin action link before filtering.
		 *
		 * @return array Filtered links.
		 */
		public function plugin_action_links( $links ) {
			$setting_link = $this->get_setting_link();
			$plugin_links = array(
				'<a href="' . $setting_link . '">' . __( 'Settings', 'billmate-checkout-for-woocommerce' ) . '</a>',
				'<a href="http://krokedil.se/">' . __( 'Support', 'billmate-checkout-for-woocommerce' ) . '</a>',
			);
			return array_merge( $plugin_links, $links );
		}

		/**
		 * Get setting link.
		 *
		 * @return string Setting link
		 */
		public function get_setting_link() {
			$section_slug = 'bco';
			return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $section_slug );
		}

		/**
		 * Loads the needed scripts for Billmate_Checkout.
		 */
		public function load_scripts() {
			if ( is_checkout() ) {

				// Checkout script.
				wp_register_script(
					'bco_wc',
					BILLMATE_CHECKOUT_URL . '/assets/js/bco-checkout.js',
					array( 'jquery' ),
					BILLMATE_CHECKOUT_VERSION,
					true
				);

				$standard_woo_checkout_fields = array( 'billing_first_name', 'billing_last_name', 'billing_address_1', 'billing_address_2', 'billing_postcode', 'billing_city', 'billing_phone', 'billing_email', 'billing_state', 'billing_country', 'billing_company', 'shipping_first_name', 'shipping_last_name', 'shipping_address_1', 'shipping_address_2', 'shipping_postcode', 'shipping_city', 'shipping_state', 'shipping_country', 'shipping_company', 'terms', 'terms-field', 'account_username', 'account_password', '_wp_http_referer' );
				$bco_settings                 = get_option( 'woocommerce_bco_settings' );
				$checkout_flow                = ( isset( $bco_settings['checkout_flow'] ) ) ? $bco_settings['checkout_flow'] : 'checkout';
				$checkout_layout              = ( isset( $bco_settings['checkout_layout'] ) ) ? $bco_settings['checkout_layout'] : 'two_column_checkout';

				$params = array(
					'ajax_url'                             => admin_url( 'admin-ajax.php' ),
					'select_another_method_text'           => __( 'Select another payment method', 'billmate-checkout-for-woocommerce' ),
					'standard_woo_checkout_fields'         => $standard_woo_checkout_fields,
					'checkout_flow'                        => $checkout_flow,
					'checkout_layout'                      => $checkout_layout,
					'update_checkout_url'                  => WC_AJAX::get_endpoint( 'bco_wc_update_checkout' ),
					'update_checkout_nonce'                => wp_create_nonce( 'bco_wc_update_checkout' ),
					'change_payment_method_url'            => WC_AJAX::get_endpoint( 'bco_wc_change_payment_method' ),
					'change_payment_method_nonce'          => wp_create_nonce( 'bco_wc_change_payment_method' ),
					'get_checkout_url'                     => WC_AJAX::get_endpoint( 'bco_wc_get_checkout' ),
					'get_checkout_nonce'                   => wp_create_nonce( 'bco_wc_get_checkout' ),
					'iframe_shipping_address_change_url'   => WC_AJAX::get_endpoint( 'bco_wc_iframe_shipping_address_change' ),
					'iframe_shipping_address_change_nonce' => wp_create_nonce( 'bco_wc_iframe_shipping_address_change' ),
					'checkout_success_url'                 => WC_AJAX::get_endpoint( 'bco_wc_checkout_success' ),
					'checkout_success_nonce'               => wp_create_nonce( 'bco_wc_checkout_success' ),
				);

				wp_localize_script(
					'bco_wc',
					'bco_wc_params',
					$params
				);
				wp_enqueue_script( 'bco_wc' );

				wp_register_style(
					'bco',
					BILLMATE_CHECKOUT_URL . '/assets/css/bco-style.css',
					array(),
					BILLMATE_CHECKOUT_VERSION
				);
				wp_enqueue_style( 'bco' );
			}
		}
	}
	Billmate_Checkout_For_WooCommerce::get_instance();

	/**
	 * Main instance Billmate_Checkout_For_WooCommerce.
	 *
	 * Returns the main instance of Billmate_Checkout_For_WooCommerce.
	 *
	 * @return Billmate_Checkout_For_WooCommerce
	 */
	function BCO_WC() { // phpcs:ignore
		return Billmate_Checkout_For_WooCommerce::get_instance();
	}
}
