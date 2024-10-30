<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Gateways;

use Cartimize\Compatibility\Core;

class BraintreeForWooCommerce extends Core {
	public function is_available() {
		return defined( 'BFWC_PLUGIN_NAME' ) || defined( 'WC_BRAINTREE_PLUGIN_NAME' );
	}

	public function pre_init() {
		add_filter( 'cartimize_payment_gateway_card_support', array( $this, 'decide_card_support' ), 10, 2 );
	}

	public function run_on_checkout() {
		remove_action ( 'woocommerce_checkout_before_customer_details', 'wc_braintree_banner_checkout_template' );
		$gateways = array();
		foreach ( WC ()->payment_gateways ()->get_available_payment_gateways () as $id => $gateway ) {
			if ($gateway->supports ( 'wc_braintree_banner_checkout' ) && $gateway->banner_checkout_enabled ()) {
				$gateways[ $id ] = $gateway;
			}
		}
		if (count ( $gateways ) > 0) {
			add_action( 'cartimize_payment_request_buttons', array( $this, 'render_banner_buttons' ) );
		}
	}

	function render_banner_buttons() {
		$gateways = array();
		foreach ( WC ()->payment_gateways ()->get_available_payment_gateways () as $id => $gateway ) {
			if ($gateway->supports ( 'wc_braintree_banner_checkout' ) && $gateway->banner_checkout_enabled ()) {
				$gateways[ $id ] = $gateway;
			}
		}
		if (count ( $gateways ) > 0) {
			add_action( 'cartimize_checkout_add_separator', array( $this, 'add_separator' ), 11 );
			
			foreach($gateways as $gateway):?>
				<div class="wc-braintree-banner-gateway wc_braintree_banner_gateway_<?php echo esc_attr($gateway->id)?>">
					<?php $gateway->banner_fields()?>
				</div>
			<?php endforeach;
		}
	}

	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = [
			'class'  => 'BraintreeForWooCommerce',
			'params' => [],
		];

		return $compatibility;
	}

	function decide_card_support( $result, $gateway ){

		$not_support = array( 'braintree_paypal', 'braintree_applepay', 'braintree_googlepay' );

		if ( in_array( $gateway, $not_support ) ) {
			return false;
		}

		return $result;
	}
}