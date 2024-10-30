<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Actions;
use Cartimize\Core\ActionCore;

class LogInAction extends ActionCore {

	
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	public function action() {
		$info                  = array();
		$info['user_login']    = isset( $_POST['user_login'] ) ? sanitize_text_field( $_POST['user_login'] ) : '';
		$info['user_password'] = isset( $_POST['password'] ) ? sanitize_text_field( $_POST['password'] ) : '';
		$info['remember']      = true;

		$user        = wp_signon( $info, is_ssl() );
		$alt_message = esc_html__('Login error.', 'linear-checkout-for-woo-by-cartimize');

		$out = array();

		if ( is_wp_error( $user ) ) {
			$out['logged_in'] = false;
			$out['message']   = apply_filters( 'cartimize_failed_login_error_message', ( $user->get_error_message() ) ?: $alt_message, $user->get_error_code() );
		} else {
			$out['logged_in'] = true;
			$out['message']   = esc_html__('Login successful', 'linear-checkout-for-woo-by-cartimize');
		}

		$this->out( $out );
	}
}
