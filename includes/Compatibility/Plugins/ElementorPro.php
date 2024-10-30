<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;
use ElementorPro\Modules\Woocommerce\Module;

class ElementorPro extends Core {
	public function is_available() {
		return defined( 'ELEMENTOR_PRO_VERSION' );
	}

	public function pre_init() {
		add_action( 'init', [ $this, 'remove_action' ], 10000000 );
	}

	function remove_action(){
		cartimize_remove_by_plugin_class( 'woocommerce_checkout_update_order_review', '\ElementorPro\Modules\Woocommerce\Module', 'load_widget_before_wc_ajax', true);
		cartimize_remove_by_plugin_class( 'woocommerce_before_calculate_totals', '\ElementorPro\Modules\Woocommerce\Module', 'load_widget_before_wc_ajax', true);
	}
}