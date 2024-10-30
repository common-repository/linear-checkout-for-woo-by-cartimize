<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;

class YITHWooCommerceCheckoutManager extends Core {
	public function is_available() {
		return defined('YWCCP_VERSION');
	}

	public function run_on_checkout() {
		add_filter( 'ywccp_ship_different_address_old_behaviour', '__return_false');
	}
}