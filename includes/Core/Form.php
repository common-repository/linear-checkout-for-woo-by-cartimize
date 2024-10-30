<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Core;

use Cartimize\Cartimize;

class Form {

	/**
	 * @var bool
	 */
	private $debug = false;

	/**
	 * @since 1.1.5
	 * @access private
	 * @var string Is the phone enabled in the settings?
	 */

	/**
	 * Form constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		$cartimize                 = Cartimize::instance();
		if ( Cartimize::is_cartimize_page() ) {
			add_filter( 'woocommerce_shipping_fields', array( $this, 'inject_cartimize_fields' ), 200000 ); // seriously, run this last
			add_filter( 'woocommerce_billing_fields', array( $this, 'inject_cartimize_fields' ), 200000 ); // seriously, run this last
		}
	}

	function inject_cartimize_fields( $fields ){
		$allowes_types = array( 'text','password','email','tel','number','textarea','datepicker','select','inspireradio', 'inspirecheckbox', 'timepicker','colorpicker','wpdeskmultiselect','inspirecheckbox', 'file', 'time', 'radio', 'datetime-local', 'date', 'month', 'week', 'checkbox' );

		$allowes_types = apply_filters( 'cartimize_optional_field_allowed_types', $allowes_types);
		foreach ($fields as $filed_key => $field) {
			$custom_attributes = array(
				'autocorrect' => "off"
			);
			if ( isset($field['required']) && $field['required'] ) {
				$custom_attributes ['data-parsley-required']="true";
			} else {
				$custom_attributes ['data-parsley-required']="false";
			}
			$ship_or_bill_key = explode( '_', $filed_key )[0];
			if ( in_array( $ship_or_bill_key, array( 'shipping', 'billing' ) ) && empty( $field['custom_attributes']['data-parsley-group'] ) ) {
				$custom_attributes['data-parsley-group'] = $ship_or_bill_key;
			}
			$custom_attributes [ 'data-parsley-'.esc_attr($filed_key)]= "false";
			$custom_attributes['data-parsley-trigger'] = 'input keyup change focusout';
			if (isset($field['type'])) {
				switch ( $field['type']) {
					case 'datepicker':
						$custom_attributes['data-parsley-trigger'] = 'input keyup change';
						break;
					case 'wpdeskmultiselect':
					case 'select':
						$custom_attributes['data-parsley-trigger'] = 'input keyup change focusout select2:select';
						break;
					case 'textarea':
						break;
					case 'checkbox':
					case 'inspirecheckbox':
						$custom_attributes['data-parsley-trigger'] = '';
						break;
					case 'tel':
						$custom_attributes['data-parsley-validate-starting-Pattern'] = '[\s\#0-9_\-\+\/\(\)\.]';
						break;
					default:
						break;
				}
			}
			if (in_array($filed_key, array('billing_address_1', 'shipping_address_1'))) {
				$custom_attributes['data-parsley-minlength'] = 2;
			}
				
			$custom_attributes = apply_filters( 'cartimize_'.$filed_key.'_custom_attributes', $custom_attributes);
			if (isset($fields[$filed_key]['custom_attributes']) && is_array( $fields[$filed_key]['custom_attributes'] ) && !empty( $fields[$filed_key]['custom_attributes'])) {
				$custom_attributes = array_merge($fields[$filed_key]['custom_attributes'], $custom_attributes);
			}
			$fields[$filed_key]['custom_attributes'] = $custom_attributes;
		}

		return $fields;
	}
}
