<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class GeneratePress extends Core {
	function is_available() {
		return defined( 'GENERATE_VERSION' );
	}

	public function run() {
		$this->remove_gp_scripts();
	}

	function remove_gp_scripts() {
		remove_action( 'wp_enqueue_scripts', 'generatepress_wc_scripts', 100 );
	}
}
