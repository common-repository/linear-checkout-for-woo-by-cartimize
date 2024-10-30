<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class PeakShops extends Core {
	public function is_available() {
		return function_exists( 'thb_checkout_after_order_review' );
	}

	public function run() {
		remove_action( 'woocommerce_checkout_after_order_review', 'thb_checkout_after_order_review', 30 );
	}
}