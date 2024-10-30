<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Zidane extends Core {
	function is_available() {
		return function_exists( 'zidane_framework' );
	}

	function run() {
		$Zidane_Framework = zidane_framework();

		remove_action( 'wp_footer', array( $Zidane_Framework, 'init_javascript' ) );
	}
}