<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Divi extends Core {
	public function is_available() {
		return function_exists( 'et_maybe_add_scroll_to_anchor_fix' );
	}

	function run() {
		remove_action( 'wp_head', 'et_maybe_add_scroll_to_anchor_fix', 9 );
	}
}
