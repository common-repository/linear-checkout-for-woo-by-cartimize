<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;

class WooCommerceOrderDelivery extends Core {
	public function is_available() {
		return class_exists( '\\WC_OD_Checkout' );
	}

	public function run() {
		$WC_OD_Checkout = \WC_OD_Checkout::instance();

		remove_action( 'woocommerce_checkout_shipping', array( $WC_OD_Checkout, 'checkout_content' ), 99 );
		add_action( 'cartimize_shipping_method_tab', array( $WC_OD_Checkout, 'checkout_content' ), 16 );
	}
}