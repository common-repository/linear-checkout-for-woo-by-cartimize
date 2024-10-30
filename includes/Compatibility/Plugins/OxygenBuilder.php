<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;

class OxygenBuilder extends Core {
	public function is_available() {
		return defined( 'CT_VERSION' );
	}

	public function run() {
		if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() && ! is_checkout_pay_page() ) {
			remove_action( 'wp_head', 'oxy_print_cached_css', 999999 );
		}
	}
}