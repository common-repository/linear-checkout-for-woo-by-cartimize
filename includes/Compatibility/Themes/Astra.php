<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;
use Cartimize\Cartimize;

class Astra extends Core {
	public function is_available() {
		return defined( 'ASTRA_THEME_VERSION' );
	}

	public function run() {
		$this->remove_astra_scripts();
	}

	public function remove_scripts( $scripts ) {
		$scripts['astra-addon-js'] = 'astra-addon-js';

		return $scripts;
	}

	public function remove_astra_scripts() {
		if ( Cartimize::is_cartimize_checkout() ) {
			remove_all_actions( 'astra_get_js_files' );
		}
	}
}