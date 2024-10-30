<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Kidz extends Core {
	function is_available() {
		return function_exists( 'ideapark_scripts' );
	}

	function run() {
		remove_action( 'wp_enqueue_scripts', 'ideapark_scripts', 99 );
		remove_action( 'wp_head', 'ideapark_sprite_loader' );
	}
}