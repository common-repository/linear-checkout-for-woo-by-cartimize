<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Atelier extends Core {
	public function is_available() {
		return function_exists( 'sf_custom_styles' );
	}

	public function run() {
		$this->wp();
	}

	function wp() {
		if ( Cartimize::is_cartimize_checkout() ) {
			remove_action( 'wp_head', 'sf_custom_styles' );
		}
	}
}