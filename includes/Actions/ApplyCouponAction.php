<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */
namespace Cartimize\Actions;
use Cartimize\Core\ActionCore;

class ApplyCouponAction extends ActionCore {

	/**
	 * ApplyCouponAction constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param $id
	 */
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	/**
	 * Applies the coupon discount and returns the new totals
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		ob_start();
		if ( ! empty( $_POST['coupon_code'] ) ) {

			WC()->cart->add_discount( wc_format_coupon_code( wp_unslash( $_POST['coupon_code'] ) ) );
			
			WC()->cart->calculate_totals();

			$discount_amounts = $fragments = array();

			//  We set it true to here so when the HTML is generated the target is correct
			wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

			$html = ob_get_clean();

			ob_start();
			cartimize_applied_coupon_html();
			$coupon_html = ob_get_contents();
			ob_clean();
			ob_end_clean();

			$notices  = cartimize_get_session_notices();

			$fragments = array(
					'#cartimize-applied-coupon' => $coupon_html,
				);

			$chosen_shipping_methods_labels = array();

			$packages = WC()->shipping->get_packages();

			foreach ( $packages as $i => $package ) {
				$chosen_method = isset( WC()->session->get( 'chosen_shipping_methods' )[ $i ] ) ? WC()->session->get( 'chosen_shipping_methods' )[ $i ] : false;

				if ( $chosen_method ) {
					$shipping_label = '';
					$available_methods    = $package['rates'];
					$shipping_label .= '<span class="txt-light">'.$available_methods[ $chosen_method ]->get_label().'</span>: '.wc_price($available_methods[ $chosen_method ]->get_cost());
					$chosen_shipping_methods_labels[] = $shipping_label;
				}
			}

			$chosen_shipping_methods_labels = apply_filters( 'cartimize_chosen_shipping_methods_labels', $chosen_shipping_methods_labels );
			
			$fragments['#shipping_method_summary'] = '<div class="shipping-details-content" id="shipping_method_summary">' . join( ', ', $chosen_shipping_methods_labels ) . '</div>';

			if ( WC()->cart->needs_shipping() ) {
				ob_start();
				cartimize_all_shipping_method_lists_html( );
				$shipping_html = ob_get_contents();
				ob_clean();
				ob_end_clean();
				$fragments['#cartimize-shipping-method-html'] = "<div id='cartimize-shipping-method-html'>".$shipping_html."</div>";
			}



			$response = array(
				'new_totals'    => array(
					'new_subtotal'       => WC()->cart->get_cart_subtotal(),
					'new_shipping_total' => WC()->cart->get_cart_shipping_total(),
					'new_taxes_total'    => WC()->cart->get_cart_tax(),
					'new_total'          => WC()->cart->get_total(),
				),
				'needs_payment' => WC()->cart->needs_payment(),
				'fees'          => $this->prep_fees(),
				'fragments' => $fragments,
				'notices'                 => $notices

			);

			if ( !isset( $notices['error'] ) ) {
				$response['code'] = sanitize_text_field( $_POST['coupon_code'] );
			}

		} else {
			$response = apply_filters( 'cartimize_apply_coupon_response_error', array(
				'message' => array( 'error' => [ esc_html__('Please provide a coupon code', 'linear-checkout-for-woo-by-cartimize') ] ),
			) );
			$html = ob_get_clean();
		}
		$response['html'] = $html;
		$response = apply_filters( 'cartimize_apply_coupon_response', $response );

		$this->out(
			$response
		);
	}

	function prep_fees() {
		$fees = [];

		foreach ( WC()->cart->get_fees() as $fee ) {
			$out         = (object) [];
			$out->name   = $fee->name;
			$out->amount = ( 'excl' == WC()->cart->tax_display_cart ) ? wc_price( $fee->total ) : wc_price( $fee->total + $fee->tax );
			$fees[]      = $out;
		}

		return $fees;
	}
}
