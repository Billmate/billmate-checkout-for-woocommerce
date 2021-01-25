=== Billmate Checkout for WooCommerce ===
Contributors: Billmate, Krokedil, NiklasHogefjord
Tags: woocommerce, billmate, ecommerce, e-commerce, checkout
Requires at least: 5.0
Tested up to: 5.6
Requires PHP: 5.6
WC requires at least: 4.0.0
WC tested up to: 4.8.0
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== DESCRIPTION ==

Billmate Checkout for WooCommerce is a plugin that extends WooCommerce, allowing you to take payments via Billmate.

This plugin is relying upon the payment provider Billmate. The payment data will be sent to them as a 3rd party service through the Billmate API.

* Billmate website: https://www.billmate.se/
* Billmate API documentation: https://billmate.github.io/api-docs/
* Billmate terms and privacy policies: https://www.billmate.se/policyer/

== Installation ==
1. Upload plugin folder to to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go WooCommerce Settings –> Payment Gateways and configure your Billmate Checkout settings.
4. Read more about the configuration process in the [plugin documentation](https://docs.krokedil.com/article/361-billmate-checkout-introduction).


== Frequently Asked Questions ==
= Which countries does this payment gateway support? =
Billmate Checkout works for merchants in Sweden.

= Where can I find Billmate Checkout for WooCommerce documentation? =
For help setting up and configuring Billmate Checkout for WooCommerce please refer to our [documentation](https://docs.krokedil.com/article/361-billmate-checkout-introduction).

= Are there any specific requirements? =
* WooCommerce 4.0 or newer is required.
* PHP 5.6 or higher is required.
* A SSL Certificate is required.

== Changelog ==
= 2021.01.19    - version 1.0.0 =
* Release       - First release of new Billmate Checkout for WooCommerce

= 2020.12.11    - version 0.6.1 =
* Fix           - Changed how we listen to hashchange in checkout (from jQuery to vanilla JS). Some stores could not complete purchase due to this.

= 2020.11.26    - version 0.6.0 =
* Tweak         - Added logging for purchase_initialized JS event.
* Fix           - Improved logic for getting correct WC order ID in push callbacks from Billmate.

= 2020.11.25    - version 0.5.0 =
* Fix           - Add trailingslashit to home_url in accepturl. Avoid issues when WP is installed in sub folder.
* Fix           - Change confirm order listener from init to template_redirect. Could cause emails not being sent properly.

= 2020.11.11    - version 0.2.0 =