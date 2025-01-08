<?php // phpcs:ignore
/**
 * Plugin Name:     Qvickly Checkout for WooCommerce
 * Plugin URI:      https://github.com/Billmate/billmate-checkout-for-woocommerce
 * Description:     Provides an Qvickly Checkout gateway for WooCommerce.
 * Version:         __STABLE_TAG__
 * Author:          Billmate, Krokedil
 * Author URI:      https://billmate.se/
 * Developer:       Billmate, Krokedil
 * Developer URI:   http://krokedil.com/
 * Text Domain:     billmate-checkout-for-woocommerce
 * Domain Path:     /languages
 *
 * WC requires at least: 5.0.0
 * WC tested up to: 9.4.3
 *
 * Copyright:       © 2020-2024 Billmate in collaboration with Krokedil.
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Billmate_Checkout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use KrokedilQvicklyCheckoutDeps\Krokedil\WooCommerce\KrokedilWooCommerce;

// Define plugin constants.
define( 'BILLMATE_CHECKOUT_VERSION', '1.7.0' );
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
		 * BCO Api class. Handles communication with Qvickly.
		 *
		 * @var BCO_API|null
		 */
		public $api;

		/**
		 * BCO Logger class. Handles logging in plugin.
		 *
		 * @var BCO_Logger|null
		 */
		public $logger;

		/**
		 * BCO API callback class. Handles callbacks from Qvickly in plugin.
		 *
		 * @var BCO_API_Callbacks|null
		 */
		public $api_callbacks;

		/**
		 * The WooCommerce package from Krokedil.
		 *
		 * @var KrokedilWooCommerce
		 */
		public $krokedil = null;

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
		public function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
		}
		/**
		 * Private unserialize method to prevent unserializing of the *Singleton*
		 * instance.
		 *
		 * @return void
		 */
		public function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
		}

		/**
		 * Initiates the plugin.
		 *
		 * @return void
		 */
		public function init() {

			// Include the autoloader from composer. If it fails, we'll just return and not load the plugin. But an admin notice will show to the merchant.
			if ( ! $this->init_composer() ) {
				return;
			}

			if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
				return;
			}

			load_plugin_textdomain( 'billmate-checkout-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

			// Include the files for the plugin.
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
		 * Try to load the autoloader from Composer.
		 *
		 * @return mixed
		 */
		public function init_composer() {
			$autoloader = BILLMATE_CHECKOUT_PATH . '/dependencies/autoload.php';
			if ( ! is_readable( $autoloader ) ) {
				self::missing_autoloader();
				return false;
			}
			$autoloader_result = require $autoloader;
			if ( ! $autoloader_result ) {
				return false;
			}
			return $autoloader_result;
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
				'<a href="https://qvickly.io/kundsupport/">' . __( 'Support', 'billmate-checkout-for-woocommerce' ) . '</a>',
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
		 * Print error message if the composer autoloader is missing.
		 *
		 * @return void
		 */
		protected static function missing_autoloader() {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( // phpcs:ignore
					esc_html__( 'Your installation of Qvickly Checkout for WooCommerce is not complete. If you installed this plugin directly from Github please refer to the readme.dev.txt file in the plugin.', 'billmate-checkout-for-woocommerce' )
				);
			}
			add_action(
				'admin_notices',
				function () {
					?>
					<div class="notice notice-error">
						<p>
							<?php echo esc_html__( 'Your installation of Qvickly Checkout for WooCommerce is not complete. If you installed this plugin directly from Github please refer to the readme.dev.txt file in the plugin.', 'billmate-checkout-for-woocommerce' ); ?>
						</p>
					</div>
					<?php
				}
			);
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
