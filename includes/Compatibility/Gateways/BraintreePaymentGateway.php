<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Gateways;

use Cartimize\Compatibility\Core;

class BraintreePaymentGateway extends Core {
	public function is_available() {
		return class_exists( 'WC_braintree_payment_gateway' );
	}

	public function pre_init() {
		add_filter( 'cartimize_payment_gateway_card_support', array( $this, 'decide_card_support' ), 10, 2 );
	}

	function decide_card_support( $result, $gateway ){

		if ( $gateway == 'braintree' ) {
			return true;
		}

		return $result;
	}
}