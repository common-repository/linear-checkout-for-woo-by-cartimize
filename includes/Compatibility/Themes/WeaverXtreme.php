<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class WeaverXtreme extends Core {
	public function is_available() {
		return function_exists( 'weaverx_setup' );
	}

	public function run() {
		remove_action( 'wp_head', 'weaverx_wp_head_action' );
	}
}