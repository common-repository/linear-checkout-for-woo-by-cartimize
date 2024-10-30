<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Core;

/**
 * Class FrontEndElement
 */
class FrontEndElement{
	
	public function getFrontEndElement(){
		$placeholder = $this->get_placeholders();
		$postcode_validation_country = $this->get_woocommerce_supported_postcode_countries();
		$billing_text = $this->billing_text();
		$paymentError = $this->paymentError();
		$terms_error =  $this->terms_error();
		$parsley_errors =  $this->parsley_errors();
		$parsley_lang = $this->parsley_lang();
		return apply_filters( 'cartimize_front_end_element_text', array_merge( $placeholder, $postcode_validation_country, $billing_text, $paymentError, $terms_error, $parsley_errors, $parsley_lang ));
	}

	public function get_placeholders(){
		return array( 'address_1_placeholder' => esc_html__('Address Line 1 (required) ', 'linear-checkout-for-woo-by-cartimize'));
	}

	public function get_woocommerce_supported_postcode_countries(){
		$postcode['AT']	= array( 'pattern' => "([0-9]{4})" , 'mask' => true, 'minLength' => 4 );
		$postcode['BA'] = array( 'pattern' => "([7-8]{1})([0-9]{4})" , 'mask' => true, 'minLength' => 5 );
		$postcode['BE'] = array( 'pattern' => "([0-9]{4})" , 'mask' => true, 'minLength' => 4 );
		$postcode['BR'] = array( 'pattern' => "([0-9]{5})([-])?([0-9]{3})" , 'mask' => true, 'minLength' => 8 );
		$postcode['CH'] = array( 'pattern' => "([0-9]{4})" , 'mask' => '9999', 'minLength' => 4 );
		$postcode['DE'] = array( 'pattern' => "([0]{1}[1-9]{1}|[1-9]{1}[0-9]{1})[0-9]{3}" , 'mask' => true, 'minLength' => 5  );
		$postcode['ES'] = array( 'pattern' => "([0-9]{5})" , 'mask' => true, 'minLength' => 5 );
		$postcode['FR'] = array( 'pattern' => "([0-9]{5})" , 'mask' => true, 'minLength' => 5 );
		$postcode['IT'] = array( 'pattern' => "([0-9]{5})" , 'mask' => true, 'minLength' => 5 );
		$postcode['GB'] = array( 'pattern' => "GB" , 'mask' => false, 'minLength' => 4 );
		$postcode['HU'] = array( 'pattern' => "([0-9]{4})" , 'mask' => true, 'minLength' => 4 );
		$postcode['IN'] = array( 'pattern' => "[1-9]{1}[0-9]{2}\s{0,1}[0-9]{3}" , 'mask' => true, 'minLength' => 6 );
		$postcode['JP'] = array( 'pattern' => "([0-9]{3})([-])([0-9]{4})" , 'mask' => true, 'minLength' => 8 );
		$postcode['PT'] = array( 'pattern' => "([0-9]{4})([-])([0-9]{3})" , 'mask' => true, 'minLength' => 8 );
		$postcode['PR'] = array( 'pattern' => "([0-9]{5})(-[0-9]{4})?" , 'mask' => true, 'minLength' => 5 );
		$postcode['US'] = array( 'pattern' => "([0-9]{5})(-[0-9]{4})?" , 'mask' => true, 'minLength' => 5 );
		$postcode['CA'] = array( 'pattern' => "([abceghjklmnprstvxyABCEGHJKLMNPRSTVXY]\d[abceghjklmnprstvwxyzABCEGHJKLMNPRSTVWXYZ])([\ ])?(\d[abceghjklmnprstvwxyzABCEGHJKLMNPRSTVWXYZ]\d)" , 'mask' => true, 'minLength' => 6 );
		$postcode['PL'] = array( 'pattern' => "([0-9]{2})([-])([0-9]{3})" , 'mask' => true, 'minLength' => 6 );
		$postcode['CZ'] = array( 'pattern' => "([0-9]{3})(\s?)([0-9]{2})" , 'mask' => true, 'minLength' => 5 );
		$postcode['SK'] = array( 'pattern' => "([0-9]{3})(\s?)([0-9]{2})" , 'mask' => true, 'minLength' => 5 );
		$postcode['NL'] = array( 'pattern' => "([1-9][0-9]{3})(\s?)(?!SA|SD|SS)[A-Za-z]{2}" , 'mask' => false, 'minLength' => 5 );
		$postcode['SI'] = array( 'pattern' => "([1-9][0-9]{3})" , 'mask' => true, 'minLength' => 4  );
		$postcode['LI'] = array( 'pattern' => "(94[8-9][0-9])" , 'mask' => true, 'minLength' => 4 );
		$postcode['ZA'] = array( 'pattern' => "([0-9]{4})" , 'mask' => true, 'minLength' => 4 );

		return array( 'postcode_format' => apply_filters( 'cartimize_postcode_validation', $postcode) );
	}

	public function billing_text(){
		return array( 'billing_label_mobile' => esc_html__( 'Billing', 'linear-checkout-for-woo-by-cartimize' ));
	}

	public function paymentError(){

		$payment_error = array();

		$payment_error['paymentError']['stripeRefreshValidation'] =  esc_html__('Sorry, but we had to clear your credit card details as a security measure. We take the security of your personal data seriously. Please re-enter your credit card details to proceed.', 'linear-checkout-for-woo-by-cartimize');
		$payment_error['paymentError']['stripeEmptyFormStepSubmit'] =  esc_html__('Please fill the card details', 'linear-checkout-for-woo-by-cartimize');

		return  $payment_error;
	}

	public function terms_error(){
		return array( 'terms_error' => esc_html__( 'Please read and accept the terms and conditions to proceed with your order.', 'linear-checkout-for-woo-by-cartimize' ));
	}

	public function parsley_errors(){
		$parsley_errors = array();

		$parsley_errors['email'] = esc_html__( 'Please enter your email address.', 'linear-checkout-for-woo-by-cartimize' );
		$parsley_errors['shipping_full_name'] = esc_html__( 'Please enter your Full Name. It looks like you have entered only your first name or last name.', 'linear-checkout-for-woo-by-cartimize' );
		$parsley_errors['billing_full_name'] = esc_html__( 'Please enter your Full Name. It looks like you have entered only your first name or last name.', 'linear-checkout-for-woo-by-cartimize' );
		$parsley_errors['shipping_postcode'] = esc_html__( 'This value is required.', 'linear-checkout-for-woo-by-cartimize' );
		$parsley_errors['shipping_address_1'] = esc_html__( 'This value is required.', 'linear-checkout-for-woo-by-cartimize' );
		$parsley_errors['billing_address_1'] = esc_html__( 'This value is required.', 'linear-checkout-for-woo-by-cartimize' );

		return apply_filters( 'cartimize_front_end_parsley_errors', array( 'parsley_errors' => $parsley_errors));
	}

	public function parsley_lang(){
		$lang = array();
		$lang['defaultMessage'] = esc_html__( 'This value seems to be invalid.', 'linear-checkout-for-woo-by-cartimize' );
		$lang['type']['email'] = esc_html__( 'This value should be a valid email.', 'linear-checkout-for-woo-by-cartimize' );
		$lang['type']['number'] = esc_html__( 'This value should be a valid number.', 'linear-checkout-for-woo-by-cartimize' );
		$lang['type']['integer'] = esc_html__( 'This value should be a valid integer.', 'linear-checkout-for-woo-by-cartimize' );
		$lang['notblank'] = esc_html__( 'This value should not be blank.', 'linear-checkout-for-woo-by-cartimize' );
		$lang['required'] = esc_html__( 'This value is required.', 'linear-checkout-for-woo-by-cartimize' );
		$lang['pattern'] = esc_html__( 'This value seems to be invalid.', 'linear-checkout-for-woo-by-cartimize' );

		return array('parsley_lang' => $lang);
	}
}