<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Gateways;

use Cartimize\Compatibility\Core;

class Square extends Core {
	public function is_available() {
		return class_exists( '\\WooCommerce_Square_Loader' );
	}

	/**
	 * @param array $compatibility
	 *
	 * @return array
	 */
	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = [
			'class'  => 'Square',
			'params' => [],
		];

		return $compatibility;
	}
}
