<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Gateways;

use Cartimize\Compatibility\Core;

class PayPalPlusCw extends Core {
	public function is_available() {
		return class_exists( '\\PayPalPlusCw_Util' );
	}

	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = [
			'class'  => 'PayPalPlusCw',
			'params' => [],
		];

		return $compatibility;
	}
}