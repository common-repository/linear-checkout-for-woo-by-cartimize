<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Gateways;

use Cartimize\Compatibility\Core;
use Cartimize\Cartimize;

class Stripe extends Core {

	protected $stripe_request_button_height = '35';

	public function __construct() {
		parent::__construct();
	}

	function is_available() {
		return defined( 'WC_STRIPE_VERSION' ) && version_compare( WC_STRIPE_VERSION, '4.0.0' ) >= 0;
	}

	function pre_init() {
		// If this filter returns true, override the btn height settings in 2 places
		if ( apply_filters( 'cartimize_stripe_compat_override_request_btn_height', '__return_true' ) ) {
			add_filter( 'option_woocommerce_stripe_settings', array( $this, 'override_btn_height_settings_on_update' ), 10, 1 );
			add_filter( 'wc_stripe_settings', array( $this, 'filter_default_settings' ), 1 );
		}
		add_filter( 'woocommerce_payment_gateways', array( $this, 'modify_woocommerce_payment_gateways' ), 100000000, 1 );
		add_filter( 'wc_payment_gateway_form_saved_payment_methods_html', array( $this, 'modify_wc_payment_gateway_form_saved_payment_methods_html' ), 100000000, 1 );
	}

	function run() {
		// Apple Pay
		$this->add_stripe_apple_pay();
	}

	function override_btn_height_settings_on_update( $value ) {
		$value['payment_request_button_height'] = $this->stripe_request_button_height;

		return $value;
	}

	function filter_default_settings( $settings ) {
		$settings['payment_request_button_height']['default'] = $this->stripe_request_button_height;

		return $settings;
	}

	function add_stripe_apple_pay() {
		// Setup Apple Pay
		if ( class_exists( '\\WC_Stripe_Payment_Request' ) && Cartimize::is_cartimize_checkout() ) {
			$stripe_payment_request = \WC_Stripe_Payment_Request::instance();

			if ( class_exists( '\\WC_Stripe_Apple_Pay_Registration' ) ) {
				$apple_pay_reg = new \WC_Stripe_Apple_Pay_Registration();
				$stripe_enabled                 = 'yes' === $apple_pay_reg->get_option( 'enabled', 'no' );
				$payment_request_button_enabled = 'yes' === $apple_pay_reg->get_option( 'payment_request', 'yes' );

				if (
					$stripe_enabled && 
					$payment_request_button_enabled
				) {
					add_filter( 'wc_stripe_show_payment_request_on_checkout', '__return_true' );

					// Remove default stripe request placement
					remove_action( 'woocommerce_checkout_before_customer_details', array( $stripe_payment_request, 'display_payment_request_button_html' ), 1 );
					remove_action( 'woocommerce_checkout_before_customer_details', array( $stripe_payment_request, 'display_payment_request_button_separator_html' ), 2 );

					// Add our own stripe requests
					add_action( 'cartimize_payment_request_buttons', array( $stripe_payment_request, 'display_payment_request_button_html' ), 1 );
					add_action( 'cartimize_checkout_add_separator', array( $this, 'add_apple_pay_separator' ), 12 ); // This should be 12, which is after 11, which is the hook other gateways use
				}
			}
		}
	}

	function add_apple_pay_separator() {
		$this->add_separator( '', 'wc-stripe-payment-request-button-separator', 'text-align: center;' );
	}

	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = [
			'class'  => 'Stripe',
			'params' => [],
		];

		return $compatibility;
	}

	function modify_woocommerce_payment_gateways( $load_gateways ){

		if ( in_array( 'WC_Stripe_Subs_Compat' , $load_gateways )) {
			$stripe_key = array_search( 'WC_Stripe_Subs_Compat' , $load_gateways);
			unset( $load_gateways[$stripe_key] );
			array_unshift( $load_gateways, 'WC_Stripe_Subs_Compat' );
		}
		return $load_gateways;
	}

	function modify_wc_payment_gateway_form_saved_payment_methods_html( $html='' ){
		return str_ireplace( '<ul class="woocommerce-SavedPaymentMethods', '<ul class="woocommerce-SavedPaymentMethods custom-radio', $html );
	}
	
}
