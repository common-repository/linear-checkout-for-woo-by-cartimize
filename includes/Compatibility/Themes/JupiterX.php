<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;

class JupiterX extends Core {
	function is_available() {
		return function_exists( 'jupiterx_define_constants' );
	}

	function run_immediately() {
		add_action( 'woocommerce_proceed_to_checkout', array( $this, 'remove_actions'), 0 );
		add_action( 'woocommerce_review_order_after_submit', array( $this, 'remove_actions'), 0 );
	}

	function remove_actions() {
		remove_action( 'woocommerce_proceed_to_checkout', 'jupiterx_wc_continue_shopping_button', 5 );
		remove_action( 'woocommerce_review_order_after_submit', 'jupiterx_wc_continue_shopping_button' );
	}
}