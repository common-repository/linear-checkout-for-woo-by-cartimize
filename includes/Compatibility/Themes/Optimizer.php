<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Optimizer extends Core {
	public function is_available() {
		return function_exists( 'optimizer_setup' );
	}

	public function run() {
		remove_action( 'wp_footer', 'optimizer_load_js' );
	}
}