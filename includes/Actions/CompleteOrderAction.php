<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Actions;

use Cartimize\Core\ActionCore;

class CompleteOrderAction extends ActionCore {

	/**
	 * LogInAction constructor.
	 *
	 * @param $id
	 * @param $no_privilege
	 * @param $action_prefix
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $id, $no_privilege, $action_prefix ) {
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'set_cartimize_flag' ) );

		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	/**
	 * Takes in the information from the order form and hands it off to Woocommerce.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		// If the user is logged in don't try and get the user from the front end, just get it on the back before we checkout
		if ( ! isset( $_POST['billing_email'] ) || ! $_POST['billing_email'] ) {
			$current_user = wp_get_current_user();
			if ( $current_user ) {
				$_POST['billing_email'] = $current_user->user_email;
			}
		}

		if (  isset( $_POST['billing_full_name'] ) ) {
			$billing_name = wc_clean( wp_unslash( $_POST['billing_full_name'] ));
			$billing_name = cartimize_split_names( $billing_name );
			$_POST['billing_first_name']   = isset( $billing_name['first_name'] ) ? wc_clean( wp_unslash( $billing_name['first_name'] ) ) : null;
			$_POST['billing_last_name']   =  isset( $billing_name['last_name'] ) ? wc_clean( wp_unslash( $billing_name['last_name'] ) ) : null;
		}

		if (  isset( $_POST['shipping_full_name'] ) ) {
			$shipping_name = wc_clean( wp_unslash( $_POST['shipping_full_name'] ));
			$shipping_name = cartimize_split_names( $shipping_name );
			$_POST['shipping_first_name']   = isset( $shipping_name['first_name'] ) ? wc_clean( wp_unslash( $shipping_name['first_name'] ) ) : null;
			$_POST['shipping_last_name']   =  isset( $shipping_name['last_name'] ) ? wc_clean( wp_unslash( $shipping_name['last_name'] ) ) : null;
		}

		$_POST['_cartimize_checkout'] = true;

		do_action( 'cartimize_before_process_checkout' );

		WC()->checkout()->process_checkout();
		wp_die( 0 );
	}

	function set_cartimize_flag( $order_id ) {
		if ( ! empty( $_POST['_cartimize_checkout'] ) ) {
			update_post_meta( $order_id, '_cartimize_checkout', 'true' );
		}
	}
}
