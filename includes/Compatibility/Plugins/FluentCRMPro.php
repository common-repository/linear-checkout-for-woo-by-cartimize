<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;
use FluentCampaign\App\Services\Integrations\WooCommerce\WooInit;

class FluentCRMPro extends Core {
	public function is_available() {
		return defined( 'FLUENTCAMPAIGN_DIR_FILE' );
	}

	public function run() {
		if ( !WC()->cart->needs_shipping() ) {
			add_action( 'woocommerce_checkout_terms_and_conditions', [ 'FluentCampaign\App\Services\Integrations\WooCommerce\WooInit', 'addSubscribeBox' ], 10000000 );
		}else{
			add_action( 'cartimize_email_and_create_account', [ 'FluentCampaign\App\Services\Integrations\WooCommerce\WooInit', 'addSubscribeBox' ], 10000000 );
		}
	}
    
}