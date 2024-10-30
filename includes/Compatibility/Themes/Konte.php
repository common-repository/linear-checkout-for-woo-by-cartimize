<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Konte extends Core {
	function is_available() {
		return function_exists( 'konte_content_width' );
	}

	function run() {
		remove_action( 'woocommerce_before_checkout_form', 'Konte_WooCommerce_Template_Checkout::checkout_login_form', 10 );
	}
}