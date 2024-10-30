<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;

class CreativeMail extends Core {
	public function is_available() {
		return defined( 'CHECKOUT_CONSENT_CHECKBOX_ID' );
	}

	public function run_immediately() {

		remove_filter('woocommerce_after_order_notes', array('CreativeMail\Managers\EmailManager', 'add_checkout_field'));
	}
}