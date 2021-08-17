=== Billmate Checkout for WooCommerce ===
Contributors: Billmate, Krokedil, NiklasHogefjord
Tags: woocommerce, billmate, ecommerce, e-commerce, checkout, swish, invoice, part-payment, installment, partpayment, card, mastercard, visa, trustly, swish
Requires at least: 5.0
Tested up to: 5.7.2
Requires PHP: 5.6
WC requires at least: 4.0.0
WC tested up to: 5.4.1
Stable tag: __STABLE_TAG__
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== DESCRIPTION ==
Billmate Checkout is an embedded checkout solution and includes all popular payment methods, Debit & Credicard, Swish, Invoice, Installment and direct bank transfer.

Billmate Checkout provides a optimized and simplified checkout experience which boosts your store with increased convertion and top notch user experience.
The Checkout have a speedy and low-click checkout process that also remembers the user for the next time they make a purchase. Everything you need to start recieving payments in your WooCommerce store.

=== How to Get Started ===
1. [Get a Billmate Account](https://www.billmate.se/checkout/)
2. [Install & configure the plugin](https://support.billmate.se/hc/sv/articles/360017161317)
3. Billmate approves your store, no more steps required!

=== Verified Third Party Compatible Plugins ===
On the following link you can see which plugins we know are compatible, https://support.billmate.se/hc/sv/articles/360017162677.
Please note that many more third party plugins are compatible even though they are not listed.

=== Privacy ===
This plugin is relying on the payment service provider Billmate. The payment data will be sent to Billmate as a 3rd party service through the Billmate API.
* Billmate website: https://www.billmate.se/
* Billmate API documentation: https://billmate.github.io/api-docs/
* Billmate terms and privacy policies: https://www.billmate.se/policyer/

== Installation ==
1. Upload plugin folder to to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go WooCommerce Settings –> Payment Gateways and configure your Billmate Checkout settings.
4. Read more about the configuration process in the [plugin documentation](https://support.billmate.se/hc/sv/articles/360017161317).

== Frequently Asked Questions ==
= Which countries does this payment gateway support? =
Billmate Checkout is only available for merchants in Sweden and for users in Sweden.

= Where can I find Billmate Checkout for WooCommerce documentation? =
For help setting up and configuring Billmate Checkout for WooCommerce please refer to our [documentation](https://support.billmate.se/hc/sv/sections/360004888977).

= I have a suggestion for an improvement or a feature request =
We have a portal for users to provide feedback, [https://woocommerce.portal.billmate.se/](https://woocommerce.portal.billmate.se/). If you submit your idea here you will get notified with updates on your idea.

= I have found a bug, where should I report it? =
The easiest way to report a bug is to email us at [support@billmate.se](mailto:support@billmate.se). If you however are a developer you can feel free to raise an issue on GitHub, [https://github.com/Billmate/billmate-checkout-for-woocommerce](https://github.com/Billmate/billmate-checkout-for-woocommerce).

== Changelog ==
= 2021.07.05    - version 1.3.0 =
* Tweak         - Add hook bco_callback_denied_order, to be able to automatically cancel order in Billmate if a denied order callback is triggered from Billmate.
* Fix           - Only add invoice fee to order if no transaction_id exists. Avoids multiple invoice fee lines.
* Fix           - PHP8 warning fix.

= 2021.05.19    - version 1.2.0 =
* Feature       - Add feature for disabling address update in WooCommerce checkout form. By using bco_populate_address_fields filter, Billmate address data will not override logged in Woocommerce customer data. 
* Feature       - Checkout page template: Changes in template file markup.
* Tweak         - Checkout page template: Don't move extra fields that are already inside the order review area.
* Tweak         - Changed bco_wc_before_checkout_form hook to woocommerce_before_checkout_form in checkout template file.
* Tweak         - Remove utf8_decode when printing error message returned from Billmate.
* Tweak         - Change logic for checkout layout to use body_class instead of JS.
* Tweak         - Adds go_to JS event listener. Makes it possible to redirect mobile users to Swish or Bank ID app automatically.
* Fix           - Use correct billing zip and country in address_selected event. Could be stored in both billingAddress & Customer.Billing returned from Billmate.
* Fix:          - Convert invoice fee to numeric format so even prices with decimals declared with comma (,) works.
* Fix           - Fix potential JS error in update checkout ajax response.

= 2021.04.14    - version 1.1.1 =
* Tweak         - Reloads checkout if customer address is missing during the WooCommerce order creation process.
* Fix           - Shipping address update fix. Billing zip and country could be returned both in billingAddress & Customer.Billing from Billmate.

= 2021.03.11    - version 1.1.0 =
* Feature       - Add setting for hide shipping address in Billmate Checkout.
* Fix           - Make sure the lowest monthly cost returned from Billmate is used in monthly cost display widget.

= 2021.03.04    - version 1.0.4 =
* Fix           - Avoid division by zero problem in discount calculation. Fixes compatibility issue with WPC Product Bundles for WooCommerce.

= 2021.02.23    - version 1.0.3 =
* Fix           - Adds support for Approved and Denied callback order status from Billmate when order had status Pending previously.

= 2021.02.08    - version 1.0.2 =
* Tweak         - Tweak WC checkout form submission logic. The plugin is no longer reliant on a hashchange to send purchase_complete response to Billmate.
* Tweak         - Adds stacktrace in logging.

= 2021.01.28    - version 1.0.1 =
* Tweak         - Minor improvements to language files

= 2021.01.28    - version 1.0.0 =
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
