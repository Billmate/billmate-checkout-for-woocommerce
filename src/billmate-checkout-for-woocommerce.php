<?php // phpcs:ignore
/**
 * Plugin Name:     Billmate Checkout for WooCommerce
 * Plugin URI:      https://github.com/Billmate/billmate-checkout-for-woocommerce
 * Description:     Provides an Billmate Checkout gateway for WooCommerce.
 * Version:         __STABLE_TAG__
 * Author:          Billmate, Krokedil
 * Author URI:      https://billmate.se/
 * Developer:       Billmate, Krokedil
 * Developer URI:   http://krokedil.com/
 * Text Domain:     billmate-checkout-for-woocommerce
 * Domain Path:     /languages
 *
 * WC requires at least: 4.0.0
 * WC tested up to: 4.8.0
 *
 * Copyright:       © 2020 Billmate in collaboration with Krokedil.
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Billmate_Checkout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'BILLMATE_CHECKOUT_VERSION', '1.0.1' );
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

			if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
				return;
			}

			load_plugin_textdomain( 'billmate-checkout-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

			$this->include_files();

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
			include_once BILLMATE_CHECKOUT_PATH . '/classes/class-bco-confirmation.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/class-bco-display-monthly-cost.php';

			// Requests.
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/class-bco-request.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/checkout/post/class-bco-request-init-checkout.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/checkout/post/class-bco-request-update-checkout.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/checkout/post/class-bco-request-update-payment.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/checkout/get/class-bco-request-get-checkout.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/checkout/get/class-bco-request-get-payment.php';
			include_once BILLMATE_CHECKOUT_PATH . '/classes/requests/checkout/get/class-bco-request-get-payment-plans.php';

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
