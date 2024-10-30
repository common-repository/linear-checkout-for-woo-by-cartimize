<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Actions;
use Cartimize\Core\ActionCore;

class UpdateCheckoutAction extends ActionCore {

	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	public function action() {
		\WC_Checkout::instance();
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		do_action( 'woocommerce_checkout_update_order_review', isset( $_POST['post_data'] ) ? wp_unslash( $_POST['post_data'] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		do_action( 'cartimize_checkout_update_order_review' );
		
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
		$posted_shipping_methods = isset( $_POST['shipping_method'] ) ? wc_clean( wp_unslash( $_POST['shipping_method'] ) ) : array();

		if ( is_array( $posted_shipping_methods ) ) {
			foreach ( $posted_shipping_methods as $i => $value ) {
				$chosen_shipping_methods[ $i ] = $value;
			}
		}

		WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
		$redirect = apply_filters( 'cartimize_update_checkout_redirect', false );

		$payment_method = empty( $_POST['payment_method'] ) ? '' : wc_clean( wp_unslash( $_POST['payment_method'] ));
		WC()->session->set( 'chosen_payment_method', $payment_method );

		if ( isset( $_POST['update_active_filling_step'] ) ) {
			$update_active_filling_step = wc_clean( wp_slash( $_POST['update_active_filling_step'] ) );
			$update_active_filling_step = str_replace( '#', '', $update_active_filling_step );
			cartimizeSetActiveStep( $update_active_filling_step );
			cartimize_change_step_status( $update_active_filling_step, 3 );
		}else{
			cartimizeSetActiveStep( cartimize_get_active_filing_step() ); 
		}
		if ( isset( $_POST['billing_full_name'] ) ) {
			$billing_name = wc_clean( wp_unslash( $_POST['billing_full_name'] ));
			$billing_name = cartimize_split_names( $billing_name );
		}

		WC()->customer->set_props(
			array(
				'billing_full_name'   => isset( $_POST['billing_full_name'] ) ? wc_clean( wp_unslash( $_POST['billing_full_name'] ) ) : null,
				'billing_first_name'   => isset( $billing_name['first_name'] ) ? wc_clean( wp_unslash( $billing_name['first_name'] ) ) : null,
				'billing_last_name'   => isset( $billing_name['last_name'] ) ? wc_clean( wp_unslash( $billing_name['last_name'] ) ) : null,
				'billing_country'   => isset( $_POST['billing_country'] ) ? wc_clean( wp_unslash( $_POST['billing_country'] ) ) : null,
				'billing_state'     => isset( $_POST['billing_state'] ) ? wc_clean( wp_unslash( $_POST['billing_state'] ) ) : null,
				'billing_postcode'  => isset( $_POST['billing_postcode'] ) ? wc_clean( wp_unslash( $_POST['billing_postcode'] ) ) : null,
				'billing_city'      => isset( $_POST['billing_city'] ) ? wc_clean( wp_unslash( $_POST['billing_city'] ) ) : null,
				'billing_address_1' => isset( $_POST['billing_address_1'] ) ? wc_clean( wp_unslash( $_POST['billing_address_1'] ) ) : null,
				'billing_address_2' => isset( $_POST['billing_address_2'] ) ? wc_clean( wp_unslash( $_POST['billing_address_2'] ) ) : null,
			)
		);

		if ( isset( $_POST['billing_phone'] ) ) {
			WC()->session->set( 'billing_phone' , wc_clean( wp_unslash( $_POST['billing_phone'] ) ));
		}

		if ( isset( $_POST['billing_full_name'] ) ) {
			WC()->session->set( 'billing_full_name' , wc_clean( wp_unslash( $_POST['billing_full_name'] ) ));
		}


		if ( wc_ship_to_billing_address_only() || ! WC()->cart->needs_shipping() ) {
			if ( isset( $_POST['billing_full_name'] ) ) {
				$billing_name = wc_clean( wp_unslash( $_POST['billing_full_name'] ));
				$billing_name = cartimize_split_names( $billing_name );
			}
			WC()->customer->set_props(
				array(
					'shipping_full_name'   => isset( $_POST['shipping_full_name'] ) ? wc_clean( wp_unslash( $_POST['shipping_full_name'] ) ) : null,
					'shipping_first_name'   => isset( $billing_name['first_name'] ) ? wc_clean( wp_unslash( $billing_name['first_name'] ) ) : null,
					'shipping_last_name'   => isset( $billing_name['last_name'] ) ? wc_clean( wp_unslash( $billing_name['last_name'] ) ) : null,
					'shipping_country'   => isset( $_POST['billing_country'] ) ? wc_clean( wp_unslash( $_POST['billing_country'] ) ) : null,
					'shipping_state'     => isset( $_POST['billing_state'] ) ? wc_clean( wp_unslash( $_POST['billing_state'] ) ) : null,
					'shipping_postcode'  => isset( $_POST['billing_postcode'] ) ? wc_clean( wp_unslash( $_POST['billing_postcode'] ) ) : null,
					'shipping_city'      => isset( $_POST['billing_city'] ) ? wc_clean( wp_unslash( $_POST['billing_city'] ) ) : null,
					'shipping_address_1' => isset( $_POST['billing_address_1'] ) ? wc_clean( wp_unslash( $_POST['billing_address_1'] ) ) : null,
					'shipping_address_2' => isset( $_POST['billing_address_2'] ) ? wc_clean( wp_unslash( $_POST['billing_address_2'] ) ) : null,
				)
			);
			
			if ( isset( $_POST['billing_full_name'] ) ) {
				WC()->session->set( 'shipping_full_name' , wc_clean( wp_unslash( $_POST['billing_full_name'] ) ));
			}
		} else {
			if ( isset( $_POST['shipping_full_name'] ) ) {
				$shipping_name = wc_clean( wp_unslash( $_POST['shipping_full_name'] ));
				$shipping_name = cartimize_split_names( $shipping_name );
			}
			WC()->customer->set_props(
				array(
					'shipping_full_name'   => isset( $_POST['shipping_full_name'] ) ? wc_clean( wp_unslash( $_POST['shipping_full_name'] ) ) : null,
					'shipping_first_name'   => isset( $shipping_name['first_name'] ) ? wc_clean( wp_unslash( $shipping_name['first_name'] ) ) : null,
					'shipping_last_name'   => isset( $shipping_name['last_name'] ) ? wc_clean( wp_unslash( $shipping_name['last_name'] ) ) : null,
					'shipping_country'   => isset( $_POST['shipping_country'] ) ? wc_clean( wp_unslash( $_POST['shipping_country'] ) ) : null,
					'shipping_state'     => isset( $_POST['shipping_state'] ) ? wc_clean( wp_unslash( $_POST['shipping_state'] ) ) : null,
					'shipping_postcode'  => isset( $_POST['shipping_postcode'] ) ? wc_clean( wp_unslash( $_POST['shipping_postcode'] ) ) : null,
					'shipping_city'      => isset( $_POST['shipping_city'] ) ? wc_clean( wp_unslash( $_POST['shipping_city'] ) ) : null,
					'shipping_address_1' => isset( $_POST['shipping_address_1'] ) ? wc_clean( wp_unslash( $_POST['shipping_address_1'] ) ) : null,
					'shipping_address_2' => isset( $_POST['shipping_address_2'] ) ? wc_clean( wp_unslash( $_POST['shipping_address_2'] ) ) : null,
				)
			);
			if ( isset( $_POST['shipping_phone'] ) ) {
				WC()->session->set( 'shipping_phone' , wc_clean( wp_unslash( $_POST['shipping_phone'] ) ));
			}

			if ( isset( $_POST['shipping_full_name'] ) ) {
				WC()->session->set( 'shipping_full_name' , wc_clean( wp_unslash( $_POST['shipping_full_name'] ) ));
			}
		}

		if ( isset( $_POST['has_full_address'] ) && wc_string_to_bool( wc_clean( wp_unslash( $_POST['has_full_address'] ) ) ) ) {
			WC()->customer->set_calculated_shipping( true );
		} else {
			WC()->customer->set_calculated_shipping( false );
		}

		WC()->customer->save();

		// Calculate shipping before totals. This will ensure any shipping methods that affect things like taxes are chosen prior to final totals being calculated. Ref: #22708.
		WC()->cart->calculate_shipping();
		WC()->cart->calculate_totals();

		unset( WC()->session->refresh_totals, WC()->session->reload_checkout );

		$payment_methods_html = apply_filters( 'cartimize_update_payment_methods', cartimize_get_payment_methods( false, false, true ) );

		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
		$current_gateway    = WC()->session->get( 'chosen_payment_method' );
		$payment_method_summary = '';

		if ( !empty($current_gateway) && !empty($available_gateways) &&  !empty($available_gateways[$current_gateway] )) {
			$selected_payment_title = $available_gateways[$current_gateway]->get_title();
			$payment_method_summary = '<div class="selected_payment_title">'.$selected_payment_title.'</div></br>';
		}

		if( apply_filters( 'cartimize_show_payment_summary_address', true ) ){
			$payment_method_summary .= cartimize_get_formated_billing_fields_summary();
		}


		ob_start();
		cartimize_applied_coupon_in_summary_html();
		$payment_method_summary .= ob_get_contents();
		ob_clean();
		do_action( 'woocommerce_check_cart_items' );

		$notices  = cartimize_get_session_notices();

		$chosen_shipping_methods_labels = array();

		$packages = WC()->shipping->get_packages();

		foreach ( $packages as $i => $package ) {
			$chosen_method = isset( WC()->session->get( 'chosen_shipping_methods' )[ $i ] ) ? WC()->session->get( 'chosen_shipping_methods' )[ $i ] : false;
			$available_methods    = $package['rates'];
			if ( $chosen_method && isset( $available_methods[ $chosen_method ] )) {
				$method = $available_methods[ $chosen_method ];
				$label     = '<span class="txt-light">'.$method->get_label().':</span>';
				if ( WC()->cart->display_prices_including_tax() ) {
					$label .= wc_price( $method->get_cost() + $method->get_shipping_tax() );
					if ( $method->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
						$label .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
					}
				} else {
					$label .= wc_price( $method->cost );
					if ( $method->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
						$label .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
					}
				}
				$shipping_label = '';
				$shipping_label .= '<span class="txt-light">'.$available_methods[ $chosen_method ]->get_label().'</span>: '.wc_price($available_methods[ $chosen_method ]->get_cost());
				$chosen_shipping_methods_labels[] = $label;
			}
			
		}

		$chosen_shipping_methods_labels = apply_filters( 'cartimize_chosen_shipping_methods_labels', $chosen_shipping_methods_labels );

		ob_start();
		cartimize_applied_coupon_html();
		$coupon_html = ob_get_contents();
		ob_clean();

		$fragments = array(
						'#shipping_summary_wrapper'        => cartimize_get_formated_shipping_fields_summary(),
						'#payment_method_summary'         => '<div id="payment_method_summary">'.$payment_method_summary.'</div>',
						'#shipping_method_summary' => '<div class="shipping-details-content" id="shipping_method_summary">' . join( ', ', $chosen_shipping_methods_labels ) . '</div>',
						'#cartimize-totals-list'                 => cartimize_get_totals_html(),
						'#cartimize-cart'                        => cartimize_get_cart_html(),
						"#cartimize-mini-cart"                   => cartimize_get_mobile_mini_cart_html(),
						'#cartimize-applied-coupon' => $coupon_html,
						'#cartimize-place-order'                       => cartimize_get_place_order(),
						'#cartimize-payment-options' => "<div id='cartimize-payment-options'>".$payment_methods_html."</div>",

						
					);
		if ( WC()->cart->needs_shipping() ) {
			ob_start();
			cartimize_all_shipping_method_lists_html( );
			$shipping_html = ob_get_contents();
			ob_clean();
			ob_end_clean();
			$fragments['#cartimize-shipping-method-html'] = "<div id='cartimize-shipping-method-html'>".$shipping_html."</div>";
		
		}
		if ( !empty( $_POST['update_active_filling_step'] ) ) {
			$fragments['#cartimize-delivery-info-continue'] = cartimize_continue_to_shipping_button( true );
			$fragments['#cartimize-shipping-method-continue'] = cartimize_continue_to_payment_button( true );
			$fragments['#cartimize-payment-method-continue'] = cartimize_continue_to_review_button( true );
		}
		$cartimize_get_payment_methods_html_fingerprint = false;

		$this->out(
			array(
				'needs_payment'           => WC()->cart->needs_payment(),
				'cartimize_get_payment_methods_html_fingerprint' => $cartimize_get_payment_methods_html_fingerprint,
				'fragments'               => apply_filters( 'woocommerce_update_order_review_fragments', $fragments ),
				'redirect'                => $redirect,
				'notices'                 => $notices,
				'active_filling_step'	  => cartimize_get_active_filing_step(),
				'all_step_status'      => cartime_get_all_step_status(),
				"initialize_value" => apply_filters( 'cartimize_woocommerce_update_order_review_initialize_value', array() ),
				"step_submit"            => isset( $_POST['step_submit'] ) ? true : false
			)
		);
	}
}