<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Blaszok extends Core {
	public function is_available() {
		return function_exists( 'mpcth_woo_fix' );
	}

	function pre_init() {
		add_action( 'init', function() {
			remove_action( 'init', 'mpcth_woo_fix' );
		}, 1 );
	}
}
