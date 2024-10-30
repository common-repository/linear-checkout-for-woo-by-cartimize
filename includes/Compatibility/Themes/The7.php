<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class The7 extends Core {
	function is_available() {
		return function_exists( 'presscore_enqueue_dynamic_stylesheets' );
	}

	function run() {
		remove_action( 'wp_enqueue_scripts', 'presscore_enqueue_dynamic_stylesheets', 20 );
	}
}