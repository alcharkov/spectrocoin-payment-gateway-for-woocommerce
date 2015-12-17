=== Bitcoin Woo Payment Gateway ===
Contributors: Aleksandr Charkov
Tags: payment gateway, spectrocoin payment gateway, spectrocoin woocommerce payment, woocommerce payment gateway, bitcoin, btc, xbt
Requires at least: 4.4
Tested up to: 4.4
Stable tag: 0.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Spectrocoin (unofficial and experimental) WooCommerce payment gateway allows customers to make payment with Bitcoin or euros

== Description ==
Spectrocoin (unofficial and experimental) WooCommerce payment gateway allows customers to make payment with Bitcoin or euros
Spectrocoin is a trademark of Spectro Finance Ltd (https://spectrocoin.com/)
Documentation available at https://github.com/SpectroFinance/SpectroCoin-Merchant-API
Donate: 1F2JnMqW63pRkJJmsf7ra9PdMDpQL4VdVo
== Installation ==
1. Upload plugin directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` menu in WordPress
3. After activation, you can set options from `WooCommerce -> Settings -> Checkout` menu
4. Generate private and public keys
    1. Private key:
    openssl genrsa -out "private.key" 2048
    2. Public key:
    openssl rsa -in "private.key" -pubout -outform PEM -out "public.key"
5. Add private key to plugin settings and public key to Spectrocoin project account
6. Choose same receive currency for Wordpress plugin and spectrocoin.com project otherwise plugin would not work.
== Frequently Asked Questions ==
== Screenshots ==

== Changelog ==
= 0.1 =
* Initial Release

== Upgrade Notice ==
None
