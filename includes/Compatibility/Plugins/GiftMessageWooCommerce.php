<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;

class GiftMessageWooCommerce extends Core {
	
	public function is_available() {
		return function_exists( 'gmfw_fs' );
	}
	public function pre_init(){
		add_filter( 'cartimize_skip_placeholder', array( $this, 'woocommerce_form'), 10, 2 );
	}

	public function woocommerce_form( $value, $key ){
		if ( $key == 'gmfw_gift_message_from' ) {
			$value = false;
		}
		return $value;
	}
	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = [
			'class'  => 'GiftMessageWooCommerce',
			'params' => [],
		];

		return $compatibility;
	}
}