<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class Flatsome extends Core {
	public function is_available() {
		return defined('UXTHEMES_API_URL');
	}

	function pre_init() {
		remove_action( 'woocommerce_checkout_after_order_review', 'wc_checkout_privacy_policy_text', 1 );
		add_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20 );
	}
	public function run_on_checkout() {
		$this->pre_init();
	}
}
