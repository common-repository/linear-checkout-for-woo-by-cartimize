<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;
use Cartimize\Cartimize;

class UltimateMember extends Core {
	public function is_available() {
        return defined( 'ultimatemember_version');
	}

	public function pre_init() {
        add_action( 'wp_enqueue_scripts',  array( $this, 'wp_enqueue_scripts' ), 100000 );
	}
    function wp_enqueue_scripts( ) {
        if (Cartimize::is_cartimize_checkout()) {
		    wp_deregister_style( 'um_styles');
        }
	}
}
