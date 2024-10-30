<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Greenmart extends Core {
	function is_available() {
		return defined('GREENMART_THEME_VERSION');
	}

	function run_immediately() {
		remove_filter( 'woocommerce_cart_item_name', 'greenmart_woocommerce_cart_item_name', 10, 3 ); 
	}
}