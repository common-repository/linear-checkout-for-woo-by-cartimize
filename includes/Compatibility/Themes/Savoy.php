<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Savoy extends Core {
	function is_available() {
		return defined( 'NM_THEME_DIR' );
	}

	function run() {
		remove_filter( 'woocommerce_checkout_required_field_notice', 'nm_checkout_required_field_notice' );
	}
}