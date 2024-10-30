<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Listable extends Core {
	function is_available() {
		return function_exists( 'listable_setup' );
	}

	function run() {
		remove_action( 'woocommerce_checkout_before_customer_details', 'woocommerce_checkout_login_form', 10 );
		remove_action( 'woocommerce_checkout_before_customer_details', 'woocommerce_checkout_coupon_form', 10 );
		remove_action( 'woocommerce_checkout_after_customer_details', 'woocommerce_checkout_payment', 20 );
	}
}