<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;


class OceanWP extends Core {
	public function is_available() {
		return defined( 'OCEANWP_THEME_DIR' );
	}

	public function pre_init() {
		add_filter( 'theme_mod_ocean_woo_multi_step_checkout', '__return_false', 1000 );
	}

	public function run() {
		add_filter( 'cartimize_blocked_script_handles', array($this, 'allow_main_js'), 10, 1 );
	}

	public function allow_main_js( $blocked_handles ) {
		$keys = array_keys( $blocked_handles, 'oceanwp-main' );

		if ( ! empty($keys) ) {
			foreach( $keys as $key ) {
				unset( $blocked_handles[ $key ] );
			}
		}

		return $blocked_handles;
	}
}