<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;
use Cartimize\Cartimize;

class Themify extends Core {
	public function is_available() {
		return class_exists( 'Themify_Enqueue_Assets' );
	}

	public function run_on_checkout() {
        if (Cartimize::is_cartimize_checkout()) {
            remove_action('wp',array('Themify_Enqueue_Assets','lazy_init'),1);
        }
	}
}
