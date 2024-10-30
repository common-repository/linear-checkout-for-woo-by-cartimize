<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Actions;
use Cartimize\Core\ActionCore;

class UpdatePaymentMethodAction extends ActionCore {

	/**
	 * LogInAction constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param $id
	 */
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	/**
	 * Logs in the user based on the information passed. If information is incorrect it returns an error message
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		$payment_method = empty( $_POST['payment_method'] ) ? '' : wc_clean( wp_unslash($_POST['payment_method'] ));
		WC()->session->set( 'chosen_payment_method', $payment_method);

		cartimizeSetActiveStep( cartimize_get_active_filing_step() );
		
		$fragments['#cartimize-delivery-info-continue'] = cartimize_continue_to_shipping_button( true );
		$fragments['#cartimize-shipping-method-continue'] = cartimize_continue_to_payment_button( true );
		$fragments['#cartimize-payment-method-continue'] = cartimize_continue_to_review_button( true );

		$this->out( array(
			'payment_method' => WC()->session->get( 'chosen_payment_method' ),
			'active_filling_step'	  => cartimize_get_active_filing_step(),
			'all_step_status'      => cartime_get_all_step_status(),
			'fragments'               => $fragments 
		) );
	}
}