<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;

class WooCommercePriceBasedOnCountry extends Core {
	public function is_available() {
		return class_exists( '\\WCPBC_Frontend' );
	}

	public function pre_init() {
		add_action( 'wc_price_based_country_before_frontend_init', array( $this, 'maybe_set_country' ) );
	}

	function maybe_set_country() {
		if ( defined( 'WC_DOING_AJAX' ) && WC_DOING_AJAX && isset( $_GET['wc-ajax'] ) && 'update_order_review' === $_GET['wc-ajax'] ) {
			$country   = isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : false;
			$s_country = isset( $_POST['s_country'] ) ? wc_clean( wp_unslash( $_POST['s_country'] ) ) : false;

			if ( $country ) {
				wcpbc_set_prop_value( wc()->customer, 'billing_country', $country );
			}

			if ( wc_ship_to_billing_address_only() ) {
				if ( $country ) {
					WC()->customer->set_shipping_country( $country );
				}
			} else {
				if ( $s_country ) {
					WC()->customer->set_shipping_country( $s_country );
				}
			}
		}
	}
}
