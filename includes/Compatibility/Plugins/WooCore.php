<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;
use Cartimize\Cartimize;

class WooCore extends Core{
	
	public function is_available() {
		return true; // always on, baby
	}
	
	public function pre_init() {
		// Using this instead of is_ajax() in case is_ajax() is not available
		if ( apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		$this->post_compatibility();

		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'suppress_add_to_cart_notices_during_checkout_redirect' ), 100000000 ); // run this late
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'suppress_add_to_cart_notices_during_ajax_add_to_cart' ), 100000000 ); // run this late
		add_filter( 'woocommerce_coupon_error', array( $this, 'modify_woocommerce_coupon_error' ), 10, 3 );
		add_filter( 'woocommerce_coupon_message', array( $this, 'modify_woocommerce_coupon_message' ), 10, 3 );
		add_filter( 'woocommerce_shipping_packages', array( $this, 'modify_woocommerce_shipping_packages' ), 10, 1 );
		// add_action( 'init', array($this, 'shim_post_billing_fields') ); // This one not needed for 1.1.4 because we wrote support for custom fields plugins
		add_action('woocommerce_thankyou', array($this, 'payment_complete'), 10, 1);
		add_filter( 'woocommerce_account_settings', array($this, 'customize_wc_account_settings'), 10);
		add_filter( 'cartimize_get_billing_details_address', array($this, 'woocommerce_formatted_address_replacements'), 10, 1);
		add_filter( 'cartimize_get_shipping_details_address', array($this, 'woocommerce_formatted_address_replacements'), 10, 1);
		add_filter( 'woocommerce_formatted_address_force_country_display', '__return_true', 10);
		add_filter( 'cartimize_parse_session_notifications', array($this, 'parse_notification'), 10, 1);
		add_action( 'wp_enqueue_scripts', array( $this, 'ignore_scripts' ), 11 );
		add_action( 'woocommerce_cart_shipping_total', array( $this, 'woocommerce_cart_shipping_total' ), 11, 1 );
		// add_filter( 'woocommerce_get_order_address', array( $this, 'get_shipping_phone_from_order' ), 10, 3 ); 
		add_filter( 'woocommerce_form_field', 'cartimize_woocommerce_form_field', 1000000, 3 );
		add_filter( 'woocommerce_form_field_args', 'cartimize_woocommerce_form_field_args', 1000000, 3 );
	}

	public function run() {
		add_action( 'woocommerce_checkout_shipping', 'cartimize_shipping_address_print');
		add_action( 'cartimize_checkout_before_billing_address', function() {
			do_action('woocommerce_before_checkout_billing_form', WC()->checkout() );
		} );

		add_action( 'cartimize_checkout_after_billing_address', function() {
			do_action('woocommerce_after_checkout_billing_form', WC()->checkout() );
		} );

		add_action( 'cartimize_checkout_after_shipping_address', function() {
			do_action('woocommerce_after_checkout_shipping_form', WC()->checkout() );
		} );

		add_action( 'cartimize_customer_info_tab', function() {
			do_action( 'woocommerce_checkout_before_customer_details' );
		}, 10 );

		add_action( 'cartimize_customer_info_tab', function() {
			do_action( 'woocommerce_checkout_after_customer_details' );
		}, 35 );

		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10 );

		// TODO: Make sure this is enabled
		remove_action( 'before_woocommerce_pay', 'woocommerce_output_all_notices', 10 );
		remove_action( 'woocommerce_checkout_after_order_review',         'woocommerce_checkout_payment' );
		add_filter('show_admin_bar', '__return_false');

		if ( cartimize_review_step_supported_payment() ) {
			add_action( 'cartimize_review_tab', 'cartimize_review_tab_html' );
			add_action( 'cartimize_above_review_tab_in_payment_step', 'cartimize_payment_step_action_html' );
		}else{
			add_action( 'cartimize_review_tab_in_payment_step', 'cartimize_review_tab_inside_html' );
			add_action( 'cartimize_above_review_tab_in_payment_step', 'cartimize_review_step_hr' );
		}

		remove_action( 'woocommerce_checkout_shipping', array( \WC_Checkout::instance(), 'checkout_form_shipping' ) );
	}

	function run_on_thankyou() {
		// TODO: Make sure this is enabled
	}

	public function post_compatibility() {
		if ( ! empty( $_POST ) && ! empty( $_GET['wc-ajax'] ) && $_GET['wc-ajax'] == "update_order_review" && apply_filters('cartimize_needs_post_compatibility', false ) ) {
			foreach ( $_POST as $key => $value ) {
				if ( $key !== "shipping_method" && stripos( $key, 'shipping_') !== false ) {
					if ( stripos( $key, 'address_1' ) ) {
						$key = str_ireplace( 'address_1', 'address', $key );
					}

					$_POST[ str_ireplace( 'shipping_', 's_', $key ) ] = wc_clean( wp_unslash($value));
				}
			}
		}

	}

	/**
	 * Some gateways expect the billing fields to be part of the posted data
	 * this ensure they always are when same as shipping address is selected
	 */
	public function shim_post_billing_fields() {
		if ( isset( $_POST['bill_to_different_address'] ) && $_POST['bill_to_different_address'] == 'same_as_shipping' ) {
			foreach ( $_POST as $key => $value ) {
				// If this is a shipping field, create a duplicate billing field
				if ( substr( $key, 0, 9 ) == 'shipping_' ) {
					$billing_field_key = substr_replace( $key, 'billing_', 0, 9 );
					$_POST[ $billing_field_key ] = wc_clean( wp_unslash($value));
				}
			}
		}
	}

	function suppress_add_to_cart_notices_during_ajax_add_to_cart( $fragments ) {
		if ( ! apply_filters( 'cartimize_suppress_add_to_cart_notices', true ) ) {
			return $fragments;
		}

		$checkout_url = wc_get_checkout_url();
		$redirect_url = apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url(), null );

		// If we are going to redirect to checkout, don't show message
		if ( ! empty( $_REQUEST['product_id'] ) && ! empty( $_REQUEST['wc-ajax'] ) && $_REQUEST['wc-ajax'] == 'add_to_cart' && $redirect_url == $checkout_url ) {
			$quantity   = isset( $_REQUEST['quantity'] ) ? wc_clean( wp_unslash( $_REQUEST['quantity'] )) : 1;
			$product_id = wc_clean( wp_unslash($_REQUEST['product_id']));

			$add_to_cart_notice = wc_add_to_cart_message( array( $product_id => $quantity ), true, true );

			if ( wc_has_notice( $add_to_cart_notice ) ) {
				$notices                  = wc_get_notices();
				$add_to_cart_notice_index = array_search( $add_to_cart_notice, $notices['success'] );

				unset( $notices['success'][ $add_to_cart_notice_index ] );
				wc_set_notices( $notices );
			}
		}

		// Continue on your way
		return $fragments;
	}

	function suppress_add_to_cart_notices_during_checkout_redirect( $url ) {
		if ( ! apply_filters( 'cartimize_suppress_add_to_cart_notices', true ) ) {
			return $url;
		}

		$checkout_url = wc_get_checkout_url();

		// If we are going to redirect to checkout, don't show message
		if ( ! empty( $_REQUEST['add-to-cart'] ) && ( $url === $checkout_url || is_checkout() ) ) {
			$quantity   = isset( $_REQUEST['quantity'] ) ? wc_clean( wp_unslash( $_REQUEST['quantity'] )) : 1;
			$quantity   = is_numeric( $quantity ) ? intval( $quantity ) : 1;
			$product_id = wc_clean( wp_unslash($_REQUEST['add-to-cart']));

			$add_to_cart_notice = wc_add_to_cart_message( array( $product_id => $quantity ), true, true );

			if ( wc_has_notice( $add_to_cart_notice ) ) {
				$notices                  = wc_get_notices();
				$add_to_cart_notice_index = array_search( $add_to_cart_notice, $notices['success'] );

				unset( $notices['success'][ $add_to_cart_notice_index ] );
				wc_set_notices( $notices );
			}
		}

		// Continue on your way
		return $url;
	}

	public function remove_scripts( $scripts ) {
		$scripts['wc-cart-fragments'] = 'wc-cart-fragments';

		return $scripts;
	}

	public function remove_styles( $styles ) {
		$styles['woocommerce-general'] = 'woocommerce-general';
		$styles['woocommerce-layout'] = 'woocommerce-layout';

		return $styles;
	}

	public function modify_woocommerce_coupon_error( $err, $err_code, $coupon ){
		if ( isset( $_POST['wc-ajax'] ) && $_POST['wc-ajax'] == 'cartimize_apply_coupon' ) {
			return $err.'|#coupon-notice-container';
		}

		return $err;
	}

	public function modify_woocommerce_coupon_message( $err, $err_code, $coupon ){

		if ( isset( $_POST['wc-ajax'] ) && $_POST['wc-ajax'] == 'cartimize_apply_coupon' ) {
			return $err.'|#coupon-notice-container';
		}

		return $err;
	}
	public function modify_woocommerce_shipping_packages( $packages ){
		global $cartimize;

		if ( $cartimize->get_settings_controller()->get_setting( 'shipping_methods_order' ) == 'recommend' ) {
			
			foreach ( $packages as $key => $package ) {
				$available_methods    = $package['rates'];
				uasort( $available_methods, 'cartimize_sort_shipping_available_methods_array' );
				$packages[ $key ]['rates'] = $available_methods;
			}
		}
		
		return $packages;
	}

	public function payment_complete( $order ){
		WC()->session->set( 'active_filling_step', null );
		WC()->session->set( 'cartimize_steps', null );
	}

	public static function customize_wc_account_settings($settings){

		$key = array_search('woocommerce_registration_generate_username', array_column($settings, 'id'));
		if( $key === false ||
			!isset( $settings[$key]['type'] ) ||
			$settings[$key]['type'] !== 'checkbox' ||
			!empty($settings[$key]['desc_tip'])
		 ) {
			return $settings;
		}
		$settings[$key]['desc_tip'] = sprintf(// translators: 1: opening a <p>tag 2: opening a <code>tag  3: closing a </code>tag 4: closing a </p>tag
		 esc_html__('%1$s This setting has been force-set by the %2$s Linear Checkout for WooCommerce by Cartimize%3$s plugin %4$s', 'linear-checkout-for-woo-by-cartimize'), '<p class="description" style="margin-top: -10px; margin-left: 22px; font-size: 12px;">', '<code style=" font-size: 12px;">', '</code>', '</p>' );
		return $settings;
	}

	public function modify_default_checkout_full_name_and_phone( $value, $input ){
		$saved_value = WC()->session->get( $input );
		if ( is_null( $value ) && !is_null( $saved_value ) ) {
			$value =  $saved_value;
		}

		return $value;
	}

	public function woocommerce_formatted_address_replacements( $format ){
		if ( !empty( $format['company'] ) && $format['country'] != 'CN' ) {
			$format['company'] = $format['company'].'|LCW|';
		}elseif ( !empty( $format['company'] ) && $format['country'] == 'CN' ) {
			$format['company'] = '|LCW|'.$format['company'];
		}

		return $format;
	}

	public function parse_notification( $notification ){

		$parsed_notifications = explode( '|', $notification );
		if ( $parsed_notifications == false ) {
			return false;
		}

		return $parsed_notifications;

	}

	public function ignore_scripts(){
		
		if ( Cartimize::is_cartimize_checkout() ) {
			wp_dequeue_style('bootstrap');
			wp_deregister_style('bootstrap');
			wp_dequeue_script('bootstrap');
			wp_deregister_script('bootstrap');
		}
	}

	public function woocommerce_cart_shipping_total( $total ){
		if ( $total == __( 'Free!', 'woocommerce' ) ) {
			$total = wc_price( 0 );
		}
		return $total;
	}

	public function get_shipping_phone_from_order( $fields, $address_type, \WC_Order $order ) {
		$order_id = \WC_Order_Factory::get_order_id( $order );
		if ( 'shipping' === $address_type ) {
			$shipping_phone = get_post_meta( $order_id, '_shipping_phone', true );
			if ( ! $shipping_phone ) {
				$billing_address = $order->get_address( 'billing' );
				$shipping_phone = $billing_address[ 'phone' ];
			}
			$fields[ 'phone' ] =  $shipping_phone;
		}
		return $fields;
	}
}
