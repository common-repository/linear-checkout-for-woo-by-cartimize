<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Verso extends Core {
	public function is_available() {
		return function_exists( 'verso_scripts' );
	}

	public function run() {
		add_filter( 'verso_filter_theme_style_url', '__return_empty_string', 100 );

		// Hide search form
		remove_action('wp_footer', 'verso_render_footer', 10);
	}
}