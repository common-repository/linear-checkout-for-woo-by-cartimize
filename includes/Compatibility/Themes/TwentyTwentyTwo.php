<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class TwentyTwentyTwo extends Core {
	public function is_available() {
		return true;
	}

	public function pre_init() {
		add_action( 'init', [ $this, 'remove_action' ], 10000000 );
	}

	function remove_action(){
		remove_action( 'woocommerce_checkout_before_order_review_heading', array( 'WC_Twenty_Twenty_Two', 'before_order_review' ) );
		remove_action( 'woocommerce_checkout_after_order_review', array( 'WC_Twenty_Twenty_Two', 'after_order_review' ) );
	}
}