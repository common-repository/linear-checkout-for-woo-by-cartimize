<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;


class Shoptimizer extends Core {
	public function is_available() {
		return defined('SHOPTIMIZER_CORE');
	}

	function run() {
		remove_filter( 'woocommerce_cart_item_name', 'shoptimizer_product_thumbnail_in_checkout', 20, 3 );
		remove_action( 'woocommerce_before_checkout_form', 'shoptimizer_cart_progress', 5 );
		remove_action( 'woocommerce_after_checkout_form', 'woocommerce_checkout_coupon_form' );
		remove_action( 'woocommerce_after_checkout_form', 'shoptimizer_coupon_wrapper_start', 5 );
		remove_action( 'woocommerce_after_checkout_form', 'shoptimizer_coupon_wrapper_end', 60 );
		remove_action( 'woocommerce_before_cart', 'shoptimizer_cart_progress' );
		remove_action( 'woocommerce_before_checkout_form', 'shoptimizer_cart_progress', 5 );
		remove_filter( 'woocommerce_checkout_cart_item_quantity', 'shoptimizer_woocommerce_checkout_cart_item_quantity', 10, 3 );
	}

	function run_on_update_checkout() {
		$this->run();
	}

	function run_immediately() {
		$this->run();
	}
}