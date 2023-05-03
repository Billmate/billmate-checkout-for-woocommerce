# Qvickly Checkout payment gateway for WooCommerce

Qvickly Checkout for WooCommerce is a plugin that extends WooCommerce, allowing you to take payments via Qvickly.

# Requirements

* You need an [agreement with Qvickly](https://qvickly.io/checkout/) to be able to use this plugin.
* WooCommerce 4.0.0 or newer is required.
* PHP 7.0 or higher is required.
* SSL Certificate is required.

# Documentation

Here is the section regarding the different parts from a documentation perspective.

## Installation

### WooCommerce Settings

* Make sure that you have enabled [pretty permalinks](https://wordpress.org/support/article/using-permalinks/) in your WordPress installation.
Otherwise callbacks from Qvickly back to your store won’t work and orders will not be updated with the correct order status/information.
* To get the order total to match between WooCommerce and Qvickly you need to configure WooCommerce to display prices with 2 decimals.
More information about displaying of prices and how it can cause rounding issues can be found in [this article](https://krokedil.com/dont-display-prices-with-0-decimals-in-woocommerce/).
* It is recommended to enable **guest checkout** (Enable guest checkout setting in WooCommerce -> Settings -> Checkout).

### Configuration

1. Go to: WooCommerce -> Settings -> Checkout -> Qvickly Checkout.
2. Enable Qvickly Checkout by checking the **Enable Qvickly Checkout** checkbox.
3. **Title** - Enter the title for the payment method displayed in the checkout and order confirmation emails.
4. **Description** - Enter the description of the payment method displayed in the checkout page.
5. **Other payment method button text** - Customize the Select another payment method button text that is displayed in the checkout if using other payment methods than Qvickly Checkout. Leave blank to use the default (and translatable) text.
6. **Testmode** - Tick the checkbox if you make purchases using a test account.
7. **Debug log** - Tick this checkbox to log events for debugging.
8. **Checkout flow** - Select how Qvickly Checkout should be integrated in WooCommerce. [More information here](https://docs.krokedil.com/article/361-billmate-checkout-introduction#h-H2_3).
9. **Qvickly checkout layout** - Select the Qvickly checkout layout.
10. **Client ID** - Client id that you receive from Qvickly.
11. **Client Secret** - Client secret that you receive from Qvickly.

## Qvickly Checkout settings

More documentation regarding the management settings can be found here

[Qvickly Checkout settings](https://support.billmate.se/hc/sv/sections/360004888977-Billmate-Checkout-WooCommerce-V2)

# Development

The following development commands are available.

## Building assets
You need to have Nodejs installed to be able to run commands below.

Install Node modules

    npm install

Build assets for development

    npx gulp watch

Build assets for production

    npx gulp
