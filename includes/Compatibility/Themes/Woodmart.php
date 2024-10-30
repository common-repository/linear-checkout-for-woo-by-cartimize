<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Themes;

use Cartimize\Compatibility\Core;
use Cartimize\Cartimize;

class Woodmart extends Core {
	function is_available() {

        $is_true = defined('WOODMART_SLUG');
     

        return $is_true;
	}

    public function pre_init() {
		add_action( 'cartimize_cart_html_table_start', [ $this, 'remove_action' ], 10000000 );
	}

	public function remove_action() {
        if (Cartimize::is_cartimize_checkout()) {
            // remove_filter('wp_get_attachment_image_attributes', 'woodmart_lazy_attributes', 10, 3);
        }
	}
}