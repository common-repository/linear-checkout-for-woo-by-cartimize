<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Genesis extends Core {
	function is_available() {
		return function_exists( 'genesis_header_scripts' );
	}

	public function run() {
		remove_action( 'wp_head', 'genesis_header_scripts' );
		remove_action( 'wp_footer', 'genesis_footer_scripts' );
	}
}
