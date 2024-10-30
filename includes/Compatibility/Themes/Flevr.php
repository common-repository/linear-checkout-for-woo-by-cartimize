<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Flevr extends Core {
	function is_available() {
		return function_exists( 'ci_print_fancybox_selectors' );
	}

	public function run() {
		remove_action( 'wp_footer', 'ci_print_fancybox_selectors', 20 );
		remove_filter( 'woocommerce_shipping_fields', 'remove_shipping_phone_field', 20 );
		remove_filter( 'woocommerce_billing_fields', 'remove_billing_phone_field', 20 );
	}
}
