<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

use Cartimize\Cartimize;

function cartimize_form_field( $key, $args, $value = null, $return_html = false ) {

	$defaults = array(
		'type'              => 'text',
		'label'             => '',
		'description'       => '',
		'placeholder'       => '',
		'maxlength'         => false,
		'required'          => false,
		'autocomplete'      => false,
		'id'                => $key,
		'class'             => array(),
		'label_class'       => array(),
		'input_class'       => array(),
		'return'            => false,
		'options'           => array(),
		'custom_attributes' => array(),
		'validate'          => array(),
		'default'           => '',
		'autofocus'         => '',
		'priority'          => '',
		'wrap'              => '',
		'columns'           => 12,
	);

	$key_sans_type    = cartimize_strip_key_type( $key );
	$ship_or_bill_key = explode( '_', $key )[0];

	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'woocommerce_form_field_args', $args, $key, $value );

	if ( in_array( $ship_or_bill_key, array( 'shipping', 'billing' ) ) && empty( $args['custom_attributes']['data-parsley-group'] ) ) {
		$args['custom_attributes']['data-parsley-group'] = $ship_or_bill_key;
	}

	$required = '';

	// If we don't have a placeholder, use label
	if ( empty( $args['placeholder'] )  ) {
		$args['placeholder'] = $args['label'];
	}

	if ( isset( $args['placeholder_not_required'] ) ) {
		$args['placeholder'] = '';
	}
	if ( $args['required'] ) {
		$args['class'][] = 'validate-required';
	} else {
		$required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';

		// We don't need to do this for address_2 because
		// it is handled by address-i18n.js
		if ( apply_filters( 'cartimize_skip_placeholder', true, $key ) && $key_sans_type !== 'address_2' && !isset( $args['placeholder_not_required'] ) ) {
			$args['placeholder'] = $args['placeholder'] . ' (' . esc_html__( 'optional', 'woocommerce' ) . ')';
		}
	}

	if ( is_string( $args['label_class'] ) ) {
		$args['label_class'] = array( $args['label_class'] );
	}

	if ( is_null( $value ) ) {
		$value = $args['default'];
	}

	// Custom attribute handling
	$custom_attributes         = array();
	if ( !empty($args['custom_attributes']) && !isset($args['custom_attributes']['data-parsley-trigger']) ) {
		$args['custom_attributes']['data-parsley-trigger'] = 'change';
	}
	$args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );

	if ( $args['maxlength'] ) {
		$args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
	}

	if ( ! empty( $args['autocomplete'] ) ) {
		if ( 0 === stripos( $key, 'billing_' ) ) {
			$args['autocomplete'] = "billing {$args['autocomplete']}";
		} elseif ( 0 === stripos( $key, 'shipping_' ) ) {
			$args['autocomplete'] = "shipping {$args['autocomplete']}";
		}

		$args['custom_attributes']['autocomplete'] = $args['autocomplete'];
	}

	if ( true === $args['autofocus'] ) {
		$args['custom_attributes']['autofocus'] = 'autofocus';
	}

	if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
		foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
		}
	}

	if ( ! empty( $args['validate'] ) ) {
		foreach ( $args['validate'] as $validate ) {
			$args['class'][] = 'validate-' . esc_attr($validate);
		}
	}

	$field                 = '';
	$label_id              = $args['id'];
	$field_container_start = '';

	if ( isset( $args['wrap'] ) && ! empty( $args['wrap'] ) ) {
		$field_container_start = $args['wrap']->start . $args['wrap']->end;
	}

	$parsleyOut = '';

	$select2Class = '';

	if ( $args['required'] ) {
		$parsleyOut = 'data-parsley-required="true"';
	} else {
		$parsleyOut = 'data-parsley-required="false"';
    }

    $parsley_custom_validator = '';

    $parsley_custom_validator = ' data-parsley-'.esc_attr($args['id']).'="false"';
    

	if ( 'hidden' == $args['type'] ) {
		$args['start'] = false;
		$args['end']   = false;
	}

	switch ( $args['type'] ) {
		case 'country':
			$countries = 'shipping_country' === $key ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();

			$field = '<select field_key="' . esc_attr($key_sans_type) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="country_to_state ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . $parsleyOut .$parsley_custom_validator. '>' . '<option value="">' . esc_html__( 'Select a country', 'woocommerce' ) . '</option>';

			$highlighted_countries = array_flip( apply_filters( 'cartimize_highlighted_countries', array() ) );
			$count                 = 1;

			if ( ! empty( $highlighted_countries ) ) {
				$countries = array_merge( $highlighted_countries, $countries );
			}

			foreach ( $countries as $ckey => $cvalue ) {
				$field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . wp_kses_post($cvalue) . '</option>';

				if ( ! empty( $highlighted_countries ) && $count == count( $highlighted_countries ) ) {
					$field .= '<option disabled="disabled" value="---">---</option>';
				}

				$count++;
			}

			$field .= '</select>';
			$select2Class = 'select2';

			$field .= '<noscript><input type="submit" name="woocommerce_checkout_update_totals" value="' . esc_attr__( 'Update country', 'woocommerce' ) . '" /></noscript>';

			break;
		case 'state':
			/* Get Country */
			$country_key = 'billing_state' === $key ? 'billing_country' : 'shipping_country';
			$current_cc  = WC()->checkout()->get_value( $country_key );
			$states      = WC()->countries->get_states( $current_cc );

			if ( is_array( $states ) && empty( $states ) ) {

				$field_container_start = '<div class="col-lg-4 address-field" id="%1$s"><div class="cartimize-input-wrap">%2$s</div></div>';

				$field .= '<input type="hidden" field_key="' . esc_attr($key_sans_type) . '" class="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '" />';

			} elseif ( ! is_null( $current_cc ) && is_array( $states ) ) {

				$field .= '<select field_key="' . esc_attr($key_sans_type). '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . $parsleyOut . $parsley_custom_validator. ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
                    <option disabled>' . esc_html__( 'Select a state', 'woocommerce' ) . '</option>';

				foreach ( $states as $ckey => $cvalue ) {
					$field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . wp_kses_post($cvalue) . '</option>';
				}

				$field .= '</select>';
				$select2Class = 'select2';

			} else {

				$field .= '<input ' . $parsleyOut . $parsley_custom_validator. ' field_key="' . esc_attr($key_sans_type) . '" type="text" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value=""  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';

			}

			break;
		case 'textarea':
			$field .= '<textarea name="' . esc_attr( $key ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ', $custom_attributes ) . $parsleyOut . $parsley_custom_validator. '>' . esc_textarea( $value ) . '</textarea>';

			break;
		case 'checkbox':
			$field = '<label class="checkbox ' . implode( ' ', $args['label_class'] ) . '" ' . implode( ' ', $custom_attributes ) . '>
                    <input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" ' . checked( $value, 1, false ) . ' /> '
					 . wp_kses_post($args['label']) . $required . '</label>';

			break;
		case 'password':
		case 'text':
		case 'hidden':
		case 'email':
		case 'tel':
		case 'number':
			$field .= '<input type="' . esc_attr( $args['type'] ) . '" field_key="' . esc_attr($key_sans_type) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . $parsleyOut . $parsley_custom_validator. ' />';

			break;
		case 'select':
			$options = $field = '';

			if ( ! empty( $args['options'] ) ) {
				foreach ( $args['options'] as $option_key => $option_text ) {
					if ( '' === $option_key ) {
						// If we have a blank option, select2 needs a placeholder
						if ( empty( $args['placeholder'] ) ) {
							$args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'woocommerce' );
						}
						$custom_attributes[] = 'data-allow_clear="true"';
					}
					$options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_attr( $option_text ) . '</option>';
				}

				$field .= '<select field_key="' . esc_attr($key_sans_type) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ).'"'. $parsleyOut . $parsley_custom_validator. '>
                        ' . $options . '
                    </select>';
               	$select2Class = 'select2';
			}

			break;
		case 'radio':
			$label_id = current( array_keys( $args['options'] ) );

			if ( ! empty( $args['options'] ) ) {
				$count = 0;
				$input_html = '';
				foreach ( $args['options'] as $option_key => $option_text ) {
					if ( $count == 0 ) {
						$input_html = '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . $parsleyOut . $parsley_custom_validator. ' />';
					} else {
						$input_html = '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />';
					}
					$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . esc_attr(implode( ' ', $args['label_class'] )) . '">' .($input_html).wp_kses_post($option_text) . '</label>';

					$count++;
				}
			}

			break;
		default:
			$field .= '<input type="' . esc_attr( $args['type'] ) . '" field_key="' . esc_attr($key_sans_type) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . $parsleyOut . $parsley_custom_validator. ' />';
			break;
	}

	$row_wrap = '';

	if ( ! empty( $field ) ) {

		$field_html = '';
		$display_none = '';
		$container_id    = esc_attr( $args['id'] ) . '_field';

		if ( $args['label'] && 'checkbox' != $args['type'] && 'hidden' != $args['type'] && !( isset( $args['hide_label'] ) && $args['hide_label'] == true ) ) {
			$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . wp_kses_post($args['label']) . $required . '';
			if ( $args['required'] ) {
				$field_html .= '<abbr class="required" title="required">*</abbr></label>' ;
			}
			$field_html .="</label>";
		}elseif ( isset( $args['hide_label'] ) && $args['hide_label'] == 1 ) {
			$field_html .= '<label></label>' ;
		}

		$field = $field_html.$field;
		
		if ( $args['description'] ) {
			$field .= '<div class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</div>';
		}
		if ( isset( $args['is_hidden'] ) && $args['is_hidden'] == true ) {
			$display_none = 'd-none';
		}

		if ( in_array( $args['id'], array( 'shipping_postcode', 'billing_postcode', 'shipping_state', 'billing_state')  )  ) {
			$display_none = '';
		}


		$field = '<div class="form-row '.esc_attr($select2Class).' form-row '.esc_attr($display_none).' '.esc_attr($key).' '.esc_attr(implode( ' ', $args[ 'class' ] )).'  " id="'.esc_attr($container_id).'">'.$field.'</div>';
		if ( isset( $args[ 'cartimize_optional_label' ] ) ) {
			$field = '<div class="form-row collapsed-link '.esc_attr($key).'-collapsed-link ">+ <a class="link show_'.esc_attr($key).'" sourceclass='.esc_attr($key).' tabindex="0">'.wp_kses_post(apply_filters( 'cartimize_add_'.$key, $args[ 'cartimize_optional_label' ] )).'</a></div>'.$field;
		}

	}

	if ( $args['return'] || $return_html ) {
		return $field;
	} else {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $field;
	}
}

function cartimize_format_form_field( $fields ){

	if ( empty( $fields ) ) {
		return $fields;
	}

	$unknown_fields = $known_fields = array();
	
	foreach ($fields as $key => $item) {
		if ( ! isset( $item['cartimize_row']) ) {
			$unknown_fields[][][$key] = $item;
		}else{
			if ( isset( $item['cartimize_priority'] ) ) {
				$known_fields[ $item['cartimize_row'] ][ $item['cartimize_priority'] ][$key] = $item;
			}elseif( isset( $known_fields[ $item['cartimize_row'] ] ) && !isset( $item['cartimize_priority'] ) ){
				$known_fields[ $item['cartimize_row'] ] = array_merge($known_fields[ $item['cartimize_row'] ], array( $key => $item ));
			}else{
				$known_fields[ $item['cartimize_row'] ][][$key] = $item;
			}
		}

	}
	ksort($known_fields);
	return array_merge( $known_fields, $unknown_fields );
}

function cartimize_wc_checkout_fields_uasort_comparison( $a, $b ) {
	/*
	 * We are not guaranteed to get a priority
	 * setting. So don't compare if they don't
	 * exist.
	 */
	if ( ! isset( $a['cartimize_row'], $b['cartimize_row'] ) ) {
		return null;
	}

	return cartimize_wc_uasort_comparison( $a['cartimize_row'], $b['cartimize_row'] );
}

function cartimize_wc_uasort_comparison( $a, $b ) {
	if ( $a === $b ) {
		return 0;
	}
	return ( $a < $b ) ? -1 : 1;
}

function cartimize_strip_key_type( $key ) {
	$key_exp       = explode( '_', $key );
	return implode( '_', array_slice( $key_exp, 1, count( $key_exp ) - 1, true ) );
}

function cartimize_get_shipping_checkout_fields( $checkout ) {
	$fields = $checkout->get_checkout_fields( 'shipping' );
	foreach ( $fields as $key => $field ) {
		woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
	}
}

function cartimize_get_billing_checkout_fields( $checkout ){
	$fields = $checkout->get_checkout_fields( 'billing' );
	foreach ( $fields as $key => $field ) {
		if ( $key == 'billing_email' ) {
			continue;
		}
		woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
	}
}

function cartimize_get_shipping_details( $checkout ) {
	return WC()->countries->get_formatted_address(
		apply_filters(
			'cartimize_get_shipping_details_address', array(
				'company'    => $checkout->get_value( 'shipping_company' ),
				'address_1'  => $checkout->get_value( 'shipping_address_1' ),
				'address_2'  => $checkout->get_value( 'shipping_address_2' ),
				'city'       => $checkout->get_value( 'shipping_city' ),
				'state'      => $checkout->get_value( 'shipping_state' ),
				'postcode'   => $checkout->get_value( 'shipping_postcode' ),
				'country'    => $checkout->get_value( 'shipping_country' ),
			), $checkout
		), ', '
	);
}

function cartimize_get_billing_details( $checkout ) {
	return WC()->countries->get_formatted_address(
		apply_filters(
			'cartimize_get_billing_details_address', array(
				'company'    => $checkout->get_value( 'billing_company' ),
				'address_1'  => $checkout->get_value( 'billing_address_1' ),
				'address_2'  => $checkout->get_value( 'billing_address_2' ),
				'city'       => $checkout->get_value( 'billing_city' ),
				'state'      => $checkout->get_value( 'billing_state' ),
				'postcode'   => $checkout->get_value( 'billing_postcode' ),
				'country'    => $checkout->get_value( 'billing_country' ),
			), $checkout
		), ', '
	);
}

function cartimize_return_to_cart_link() {
	echo wp_kses_post(apply_filters( 'cartimize_return_to_cart_link', sprintf( '<button onclick="location.href="%s" type="button"> %s</a>', apply_filters( 'cartimize_return_to_cart_link_url', wc_get_cart_url() ), wp_kses_post(apply_filters( 'cartimize_return_to_cart_link_text', esc_html__( 'Back', 'linear-checkout-for-woo-by-cartimize' ) ) )) ));
}


function cartimize_continue_to_shipping_button( $return = false ) {
	$steps = cartimize_registerd_steps();
	if (cartimize_get_step_status( $steps['step1'] ) == 1 ) {
		# code...
		$button = apply_filters( 'cartimize_continue_to_shipping_button', sprintf( '<input type="button" id="cartimize-delivery-info-continue" data-tab="#'.esc_html__($steps['step2']).'" class="cartimize-primary-btn cartimize-next-tab" value="%s"></input>', wp_kses_post(apply_filters( 'cartimize_continue_to_shipping_method_label', esc_html__( 'Continue to Shipping Method', 'linear-checkout-for-woo-by-cartimize' ) )) ) );
	}else {
		$button = cartimize_save_changes_button_html( true, $steps['step1'] );
	}

	if ($return) {
		return $button;
	}
	$allowed_html = array(
						"input" => array( "type" => true, "id"=> true, "data-*" => true, "class" => true, "value"=> true)
					);

	echo wp_kses($button, $allowed_html);
}

function cartimize_continue_to_payment_button( $return = false ) {
	$steps = cartimize_registerd_steps();
	if ((cartimize_get_step_status( $steps['step2'] ) == 1 && cartimize_show_shipping_tab()) || (!cartimize_show_shipping_tab() && cartimize_get_active_filing_step() == $steps['step1'])) {
		$button = apply_filters( 'cartimize_continue_to_payment_button', sprintf( '<input type="button" data-tab="#'.esc_html__($steps['step3']).'" id="cartimize-shipping-method-continue" class="cartimize-primary-btn cartimize-next-tab" value="%s">',wp_kses_post(apply_filters( 'cartimize_continue_to_payment_method_label', esc_html__( 'Continue to Payment Details', 'linear-checkout-for-woo-by-cartimize' ) ) )) );
	}else {
		$button = cartimize_save_changes_button_html( true, $steps['step2'] );
	}

	if ($return) {
		return $button;
	}

	$allowed_html = array(
							"input" => array( "type" => true, "id"=> true, "data-*" => true, "class" => true, "value"=> true)
						);

	echo wp_kses($button, $allowed_html);
}

function cartimize_continue_to_review_button( $return = false ) {
	$steps = cartimize_registerd_steps();
	if (cartimize_get_step_status($steps['step3']) == 1 || (cartimize_get_active_filing_step() == $steps['step1'] && !cartimize_show_customer_info_tab()) ) {
		$button = apply_filters( 'cartimize_continue_to_review_button', sprintf( '<input type="button" data-tab="#'.esc_html__($steps['step4']).'" id="cartimize-payment-method-continue" class="cartimize-primary-btn cartimize-next-tab" value="%s">',wp_kses_post( apply_filters( 'cartimize_continue_to_review_button_label', esc_html__( 'Continue to Review & Place Order', 'linear-checkout-for-woo-by-cartimize' ) ) )) );

	}else {
		$button = cartimize_save_changes_button_html( true, $steps['step3'] );
	}

	if ($return) {
		return $button;
	}

	$allowed_html = array(
							"input" => array( "type" => true, "id"=> true, "data-*" => true, "class" => true, "value"=> true)
						);

	echo wp_kses($button, $allowed_html);
}

function cartimize_all_shipping_method_lists_html(){
	if ( apply_filters( 'cartimize_contain_products', true ) ) :
		cartimize_shipping_method_lists_html();
	endif;
	do_action( 'cartimize_after_shipping_method_list_html' );

}

function cartimize_shipping_method_lists_html() {
	global $cartimize;
	$packages = WC()->shipping->get_packages();

	foreach ( $packages as $i => $package ) {
		$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
		$product_names = array();

		if ( sizeof( $packages ) > 1 ) {
			foreach ( $package['contents'] as $item_id => $values ) {
				$product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
			}
			$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
		}

		$available_methods    = $package['rates'];
		if ( $cartimize->get_settings_controller()->get_setting( 'shipping_methods_order' ) == 'recommend' ) {
			uasort( $available_methods, 'cartimize_sort_shipping_available_methods_array' );
		}
		$show_package_details = sizeof( $packages ) > 1;
		$package_details      = implode( ', ', $product_names );
		$package_name         = apply_filters( 'woocommerce_shipping_package_name', sprintf( _nx( 'Shipping', 'Shipping %d', ( $i + 1 ), 'shipping packages', 'woocommerce' ), ( $i + 1 ) ), $i, $package );
		$index                = $i;

		if ( count( $available_methods ) > 1 ) {
		?>
			<div class="form-row-header"><?php echo wp_kses_post(apply_filters( 'cartimize_choose_your_shipping_method_text', esc_html__( "Choose your Shipping Method", 'linear-checkout-for-woo-by-cartimize' ) )); ?></div>

		<?php 
		}
		if ( count( $available_methods ) > 0 ) : ?>
			
			<?php cartimize_before_shipping(); ?>

			<ul class="woocommerce-shipping-methods custom-radio shipping-methods ul" id="shipping_method">
				<?php
				foreach ( $available_methods as $method ) :
					ob_start();
					do_action( 'woocommerce_after_shipping_rate', $method, $index );
					$after_shipping_method = ob_get_clean();
					?>
					<li class="li">
						<?php printf( '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method"  %4$s />', esc_html($index), esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ), checked( $method->id, $chosen_method, false ) );?>

						<?php printf( '<label for="shipping_method_%1$s_%2$s">%3$s</label>', esc_html($index), esc_attr( sanitize_title( $method->id ) ), wp_kses_post(cartimize_wc_cart_totals_shipping_method_label( $method )) ); ?>
						<?php
						if ( ! empty( $after_shipping_method ) && preg_match( '/<thead|<tbody|<tfoot|<th|<tr/', $after_shipping_method ) ) :
							?>
							<table>
								<?php do_action( 'woocommerce_after_shipping_rate', $method, $index ); ?>
							</table>
                        <?php else: ?>
							<?php do_action( 'woocommerce_after_shipping_rate', $method, $index ); ?>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php cartimize_after_shipping(); ?>
		<?php else : ?>
			<div class="shipping-message">
				<?php echo wp_kses_post(apply_filters( 'woocommerce_no_shipping_available_html', '<div class="cartimize-alert cartimize-alert-error"><div class="message">' . wpautop( __( 'There are no shipping methods available. Please double check your address, or contact us if you need any help.', 'woocommerce' ) )) . '</div></div>' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $show_package_details ) : ?>
			<?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html( $package_details ) . '</small></p>'; ?>
		<?php endif; ?>
		<?php

	}
}

function cartimize_wc_cart_totals_shipping_method_label( $method ) {
	$label     = $method->get_label();
	$has_cost  = 0 < $method->cost;

	if ( WC()->cart->display_prices_including_tax() ) {
		$label .= ': ' . wc_price( $method->cost + $method->get_shipping_tax() );
		if ( $method->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
			$label .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
		}
	} else {
		$label .= ': ' . wc_price( $method->cost );
		if ( $method->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
			$label .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
		}
	}

	return apply_filters( 'woocommerce_cart_shipping_method_full_label', $label, $method );
}

function cartimize_before_shipping() {
	if ( has_action( 'woocommerce_review_order_before_shipping' ) ) :
		?>
		<table id="cartimize-before-shipping">
			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
		</table>
		<?php
	endif;
}

function cartimize_after_shipping() {
	if ( has_action( 'woocommerce_review_order_after_shipping' ) ) :
		?>
		<table id="cartimize-after-shipping">
			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
		</table>
		<?php
	endif;
}

function cartimize_sort_shipping_available_methods_array($a, $b){
	if ($a->method_id == 'free_shipping' && $b->method_id == 'free_shipping' && $a->instance_id < $b->instance_id) {
		return 0;
	}elseif($a->method_id == 'free_shipping' && $b->method_id == 'free_shipping' && $a->instance_id > $b->instance_id){
		return 1;
	}elseif($a->method_id == 'free_shipping' && $b->method_id != 'free_shipping'){
		return 0;
	}elseif($a->method_id != 'free_shipping' && $b->method_id == 'free_shipping'){
		return 1;
	}elseif($a->cost < $b->cost){
		return 0;
	}elseif($a->cost > $b->cost){
		return 1;
	}
}

function cartimize_get_payment_methods( $available_gateways = false, $object = false, $show_title = true, $return_html = true ) {
	$payment_methods_html = cartimize_get_payment_methods_html( $available_gateways );
	$available_gateways = ! $available_gateways ? WC()->payment_gateways->get_available_payment_gateways() : $available_gateways;
	$object = ! $object ? WC()->cart : $object;

	if ($return_html) {
		ob_start();
	}
	?>
	
		<?php do_action( 'cartimize_checkout_before_payment_methods' ); ?>

		<?php if ( $object->needs_payment() ) : 

				if ( !empty( $available_gateways ) && count( $available_gateways ) > 1 ) { ?>
					<div class="form-row-header">
						<span class="cartimize-small secure-notice"><?php esc_html_e( 'Choose your preferred payment method', 'linear-checkout-for-woo-by-cartimize' ); ?></span>
					</div>
				<?php }
			?>
			<div class="cartimize-payment-methods-wrap">
				<div id="payment" class="woocommerce-checkout-payment">
					<?php 
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $payment_methods_html; 
					?>
				</div>
			</div>
			</div>

		<?php else : ?>
			<div class="cartimize-no-payment-method-wrap">
				<span class="cartimize-small"><?php echo wp_kses_post(apply_filters( 'cartimize_no_payment_required_text', esc_html__( 'Your order is free. No payment is required.', 'linear-checkout-for-woo-by-cartimize' ) )); ?></span>
			</div>
		<?php endif; ?>

		<?php do_action( 'cartimize_checkout_after_payment_methods' ); ?>
		<?php
		?>
	<?php
	if ($return_html) {
		return ob_get_clean();
	}
}

function cartimize_get_payment_methods_html( $available_gateways = false ) {
	do_action( 'cartimize_get_payment_methods_html' );

	$available_gateways = ! $available_gateways ? WC()->payment_gateways->get_available_payment_gateways() : $available_gateways;
	$current_gateway    = WC()->session->get( 'chosen_payment_method' );
	ob_start();
	$total_count = 0;
	if ( ! empty( $available_gateways ) ) { 
		$total_count = count($available_gateways);
	}
	?>
	<div class="payment_methods_container <?php echo ($total_count == 1 )? ' single_payment_gateway' : '' ?>">
	<?php
	if ( ! empty( $available_gateways ) ) { ?>
	<ul class="wc_payment_methods ul payment_ul_methods payment_methods methods cartimize-radio-reveal-group custom-radio inline vertical" style="flex: 0 0 35%;"> <?php
		$count = 0;
		foreach ( $available_gateways as $gateway ) {
			if ( apply_filters( "cartimize_show_gateway_{$gateway->id}", true ) ) : ?>
				<li class="li wc_payment_method payment_method_<?php echo esc_attr($gateway->id); ?> cartimize-radio-reveal-li <?php echo ( ( empty( $current_gateway ) && $count == 0 ) || $current_gateway === $gateway->id ) ? "active" : ''; echo ($total_count == 1 )? ' single_payment_gateway' : ''; ?>">
						<input id="payment_method_<?php echo esc_attr($gateway->id); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php echo ( ( empty( $current_gateway ) && $count == 0 ) || $current_gateway === $gateway->id ) ? "checked" : ''; ?> data-order_button_text="<?php echo wp_kses_post( apply_filters( 'cartimize_gateway_order_button_text', $gateway->order_button_text, $gateway ) ); ?>" />
						<label class="payment_method_label cartimize-radio-reveal-label" for="payment_method_<?php echo esc_attr($gateway->id); ?>">
							<span class="payment_method_title cartimize-radio-reveal-title"><?php echo ($gateway->get_title());?></span>
							<?php
								if ( apply_filters( 'cartimize_show_payment_icons_in_list', false)) {
									echo wp_kses_post($gateway->get_icon());
								}
							?>
						</label>
						<?php if ( apply_filters( "cartimize_payment_gateway_{$gateway->id}_content", $gateway->has_fields() || $gateway->get_description() ) ) : ?>
								<div class="payment-form-container payment_box payment_method_<?php echo esc_attr($gateway->id); ?> cartimize-radio-reveal-content" <?php if ( ! $gateway->chosen ) :?>style="display:none;"<?php else: ?> style="display:block;" <?php endif; ?>>
									<?php
									if ( apply_filters( "cartimize_payment_gateway_card_support", $gateway->supports( 'tokenization' ),  $gateway->id)) { ?>
										<div class="secure-info">
					                        <span><?php echo esc_html__( 'Secure Credit/Debit Card Payment', 'linear-checkout-for-woo-by-cartimize' ); ?></span><br>
					                       <?php echo sprintf(/* translators: %s: Payment gateway name */esc_html__( 'This 256-bit TLS encrypted payment is secured by %s.', 'linear-checkout-for-woo-by-cartimize' ), wp_kses_post($gateway->get_method_title())); ?>
					                     </div>
									<?php }
									if ($gateway->get_icon()) {
										?>  <div class="payment_method_icons">
										<?php echo wp_kses_post($gateway->get_icon()); ?>
										</div> <?php
									}

									$gateway->payment_fields();


									$field_html = '';

									/**
									 * Gateway Compatibility Patches
									 */
									// Expiration field fix
									$field_html = str_ireplace( 'js-sv-wc-payment-gateway-credit-card-form-expiry', 'js-sv-wc-payment-gateway-credit-card-form-expiry  wc-credit-card-form-card-expiry', $field_html );
									$field_html = str_ireplace( 'js-sv-wc-payment-gateway-credit-card-form-account-number', 'js-sv-wc-payment-gateway-credit-card-form-account-number  wc-credit-card-form-card-number', $field_html );

									// Credit Card Field Placeholders
									$field_html = str_ireplace( '•••• •••• •••• ••••', 'Card Number', $field_html );
									$field_html = str_ireplace( '&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;', 'Card Number', $field_html );

									//apply_filters( "cartimize_payment_gateway_field_html_{$gateway->id}", $field_html ); PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>
								</div>
						<?php elseif ( !$gateway->get_description() ) : ?>
								<div class="payment-form-container payment_box payment_method_<?php echo esc_attr($gateway->id); ?> cartimize-radio-reveal-content" <?php if ( ! $gateway->chosen ) :?>style="display:none;"<?php endif; ?>>
									<?php 
										echo esc_html__( 'You can proceed to the next step to complete your order.', 'linear-checkout-for-woo-by-cartimize' );
									?>
								</div>
						<?php endif; ?>
				</li>

				<?php else :
					do_action_ref_array( "cartimize_payment_gateway_list_{$gateway->id}_alternate", array( $count ) );
				endif;

				$count++;
		} ?>
	</ul>
		<?php
	} else {
		echo '<div class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . wp_kses_post(apply_filters( 'woocommerce_no_available_payment_methods_message', __( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) )) . '</div>';
	}
	
	return ob_get_clean();
}

function cartimize_get_payment_methods_html_fingerprint( $payment_methods_html, $object = false ) {
	// TODO: Move this into the compat classes with some magicness and a filter

	$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

	if ( empty( $available_gateways ) ) {
		$available_gateways = array();
	}


	return apply_filters( 'cartimize_get_payment_methods_html_fingerprint', md5( count($available_gateways) ) );
}

function cartimize_get_cart_html($return_html = true) {
	$cart = WC()->cart;
	if ($return_html) {
		ob_start();
	}
	?>
		<div id="cartimize-cart">
			<h4><?php echo wp_kses_post(apply_filters('cartimize_items_summary_text', esc_html__( 'Items Summary', 'linear-checkout-for-woo-by-cartimize' ))); ?>
				<span class="order-summary-count">
					(<?php printf( /* translators: %s: cart item count */esc_html(_n( '%s item', '%s items', number_format_i18n($cart->get_cart_contents_count(), 'linear-checkout-for-woo-by-cartimize' ))), esc_html(number_format_i18n( $cart->get_cart_contents_count() ))) ; ?>)
				</span>
				<a class="close-mini-cart"></a></h4>
			<div class="order-items-scroll scroll up" style="display: none;"></div>
			<ul class="order-items">
				<?php
				do_action( 'cartimize_cart_html_table_start' );
				$cart_data = apply_filters( 'cartimize_cart_data', $cart->get_cart(), $cart );
				foreach ( $cart_data as $cart_item_key => $cart_item ) {
					if ( isset( $cart_item['data'] ) ) {
						$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
						if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
			                $item_thumb    = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'cartimize_cart_thumb' ), $cart_item, $cart_item_key );
			                $item_quantity = apply_filters( 'woocommerce_checkout_cart_item_quantity', cartimize_get_cart_edit_item_quantity_control( $cart_item, $_product, $cart_item_key ), $cart_item, $cart_item_key );
			                $item_title    = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
			                $item_url      = get_permalink( $cart_item['product_id'] );
			                $item_subtotal = apply_filters( 'woocommerce_cart_item_subtotal', $cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );

			                if ( apply_filters( 'cartimize_show_cart_item_discount', false ) ) {
			                    $item_title = $item_title . ' (' . $_product->get_price_html() . ') ';
			                }
			                ?>
			                <li class="cart-item-row cart-item-<?php echo esc_attr($cart_item_key); ?> <?php echo (isset( $_POST['undo_item']) && $cart_item_key == sanitize_text_field( wp_unslash( $_POST['undo_item'] ) ))? 'remove-undo-highlight': ''; ?>" >
				                <?php if ( $item_thumb ) : ?>
			                    <div class="prod-img">
			                        <?php echo ($item_thumb); ?>
			                    </div>
			                    <?php endif; ?>

		                        <div class="cartimize-cart-item-description meta-name-qty">
		                            <div class="prod-name">
		    		                    <?php echo wp_kses_post($item_title); ?>
		                            </div>
									<div class="cart-editing" style="display:flex; align-items: center;">
			                    		<div style="font-size: 13px; margin-right: 6px;"><?php echo wc_price($cart_item['data']->get_price()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			                            <?php if ( !has_filter( 'cartimize_edit_cart_item' ) ): ?>
			                            	x <?php echo wp_kses_post($cart_item['quantity']) ?>
			                    		<?php endif; ?>
			                         	</div>
                            		</div>
                            		<?php do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>
		                        	<?php echo wp_kses_post(cartimize_get_formatted_cart_item_data( $cart_item )); ?>
		                        </div>

			                    <div class="cartimize-cart-item-quantity d-none">
				                    <?php echo wp_kses_post($cart_item['quantity']); ?>
			                    </div>

			                    <div class="cartimize-cart-item-subtotal qty-price-remove-item">
			                    	<?php if ( has_filter( 'cartimize_edit_cart_item' ) ): ?>
			                    	<?php apply_filters( 'cartimize_edit_cart_item', $cart_item_key, $cart_item, $_product ); ?>
			                    	<?php endif; ?>
				                    <?php echo wp_kses_post($item_subtotal); ?>
				                     <?php echo wp_kses(apply_filters( 'cartimize_remove_cart_item', '', $cart_item_key ), array("a"=>array("cart-key" => true, "class"=> true))); ?>
        	                        <?php echo wp_kses_post($item_quantity); ?>
			                    </div>
			                </li>
			                <?php
			            }
					}elseif( isset( $cart_item[ 'product_id' ] ) ){
						?>
						<li>
							<div class="removed-item-undo txt-light">
								<?php
									$product = wc_get_product( $cart_item[ 'product_id' ] );
									$title =  $product->get_title();
									$link = $product->get_permalink();
									echo "<a href='".esc_url($link)."' target='_blank'>".wp_kses_post($title).'</a> '.esc_html__( 'has been removed.', 'linear-checkout-for-woo-by-cartimize' ) ;

									$removed_notice = ' <a class="restore-item link">' . esc_html__( 'Add it back', 'linear-checkout-for-woo-by-cartimize' ) . '<input type="hidden" name="undo_item" value="'.esc_attr($cart_item_key).'"></input> </a>';

									$allowed_html = array(
																"a" => array( "type" => true, "id"=> true, "data-*" => true, "class" => true, "value"=> true),
																"input" => array( "type" => true, "id"=> true, "data-*" => true, "class" => true, "value"=> true, "name"=> true),
															);

									echo wp_kses($removed_notice, $allowed_html);

								?>
							</div>
						</li>
						<?php
					}
				}

				?>
			</ul>
			<div class="order-items-scroll scroll down" style="display: none;"></div>
		</div>

	<?php
	if ($return_html) {
		return ob_get_clean();
	}
}

function cartimize_get_formatted_cart_item_data( $cart_item ) {
    $item_data = cartimize_get_formatted_cart_item_data_array( $cart_item );
    $output = '';
    $first = true;

    foreach ( $item_data as $item_key => $item_datum) {
    	if ( !empty( $item_datum['key'] ) ) {
    		$output .= '<li>'.$item_datum['key'].': ';
    	}
        if ( $first ) {
	        $output .= $item_datum[ 'value' ];
	        $first = false;
        } else {

	        $output .= $item_datum[ 'value' ]. ' </li> ';
        }
    }

    if ( $output ) {
        echo '<ul class="cartimize-cart-item-data">' . wp_kses_post($output) . '</ul>';
    }

    return '';
}

function cartimize_get_formatted_cart_item_data_array( $cart_item ) {
	$item_data = array();

	// Variation values are shown only if they are not found in the title as of 3.0.
	// This is because variation titles display the attributes.
	if ( $cart_item['data']->is_type( 'variation' ) && is_array( $cart_item['variation'] ) ) {
		foreach ( $cart_item['variation'] as $name => $value ) {
			$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

			if ( taxonomy_exists( $taxonomy ) ) {
				// If this is a term slug, get the term's nice name.
				$term = get_term_by( 'slug', $value, $taxonomy );
				if ( ! is_wp_error( $term ) && $term && $term->name ) {
					$value = $term->name;
				}
				$label = wc_attribute_label( $taxonomy );
			} else {
				// If this is a custom option slug, get the options name.
				$value = apply_filters( 'woocommerce_variation_option_name', $value, null, $taxonomy, $cart_item['data'] );
				$label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $cart_item['data'] );
			}

			// Check the nicename against the title.
			if ( '' === $value || wc_is_attribute_in_product_name( $value, $cart_item['data']->get_name() ) ) {
				continue;
			}

			$item_data[] = array(
				'key'   => $label,
				'value' => $value,
			);
		}
	}

	// Filter item data to allow 3rd parties to add more to the array.
	$item_data = apply_filters( 'woocommerce_get_item_data', $item_data, $cart_item );

	return $item_data;
}

function cartimize_get_cart_edit_item_quantity_control( $cart_item, $product, $cart_item_key ) {
     if (  Cartimize::is_cartimize_checkout() ) {
         $return = '<input type="hidden" class="cartimize-edit-item-quantity-value" name="cart[' . $cart_item_key . '][qty]" value="' . $cart_item['quantity'] . '"/>';

 	    return $return;
 	}
}

function cartimize_get_totals_html($return = true) {
	if ($return) {
		ob_start();
	}
	?>
	<div id="<?php echo esc_attr(apply_filters( 'cartimize_template_cart_el', 'cartimize-totals-list' )); ?>" >
		<div class="cartimize-module sub-total-itemized">
			<table class="cartimize-module">
				<?php do_action( 'cartimize_before_cart_summary_totals' ); ?>

				<tr class="cart-subtotal">
					<td width="50%"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></td>
					<td width="50%"><?php wc_cart_totals_subtotal_html();  ?></td>
				</tr>

				<?php if ( cartimize_show_shipping_tab() ) : ?>

					<tr class="woocommerce-shipping-totals">
						<td>
							<?php 

								if ( cartimize_estimate_shipping_and_order_total() ) {
									echo esc_html(apply_filters( 'cartimize_order_summary_estimated_shipping_text', __( 'Estimated Shipping', 'linear-checkout-for-woo-by-cartimize' ) )); 
								}else{
									echo esc_html(apply_filters( 'cartimize_order_summary_shipping_text', __( 'Shipping', 'linear-checkout-for-woo-by-cartimize' ) ));
								}
							?>
						</td>
						<td>
							<span class="processing-hide">
								<?php echo wp_kses_post(cartimize_get_shipping_total()); ?>
							</span>
						</td>
					</tr>

				<?php endif; ?>

				<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
					<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
						<td><div class="cart-discount-amount"><?php wc_cart_totals_coupon_html( $coupon ); ?></div></td>
					</tr>
				<?php endforeach; ?>

				<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
					<tr class="fee">
						<td><?php echo esc_html( $fee->name ); ?></td>
						<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
					</tr>
				<?php endforeach; ?>

				<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
					<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
						<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
							<tr class="tax-rate tax-rate-<?php echo esc_html__( $code ); ?>">
								<td><?php echo esc_html( $tax->label ); ?></td>
								<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr class="tax-total">
							<td><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></td>
							<td><?php wc_cart_totals_taxes_total_html(); ?></td>
						</tr>
					<?php endif; ?>
				<?php endif; ?>
				<?php do_action( 'cartimize_inside_subtotal_detail' ); ?>
				<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>


				<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

				<?php do_action( 'cartimize_after_cart_summary_totals' ); ?>
			</table>
			<?php do_action( 'cartimize_content_order_notes' ); ?>
		</div>
		<?php do_action( 'cartimize_outer_summary_detail' ); 

	if ($return) {
		return apply_filters( 'cartimize_totals_html', ob_get_clean() );
	}

}

function cartimize_order_total_html(){
	?>
		<div class="order-total">
			<table>
				<tr>
					<td>
						<?php
							if ( cartimize_estimate_shipping_and_order_total() ) {
								echo esc_html(apply_filters( 'cartimize_order_summary_estimated_order_total_text', __( 'Estimated Order Total', 'linear-checkout-for-woo-by-cartimize' ) ));
							}else{
								echo esc_html(apply_filters( 'cartimize_order_summary_order_total_text', __( 'Total', 'woocommerce' ) ));
							}
					 	?>
					 </td>
					<td><span class="woocommerce-Price-amount-order-total"><span class="amount-inner"><?php wc_cart_totals_order_total_html(); ?></span></span></td>
				</tr>
			</table>
		</div>
	</div>
	<?php
}

function cartimize_order_total_html_inside_subtotal(){
	?>		<tr class="spacer"><td colspan="2"></td><td>
			<tr class="order-total simple-product">
				<td class="label">
					<?php
						if ( cartimize_estimate_shipping_and_order_total() ) {
							echo esc_html(apply_filters( 'cartimize_order_summary_estimated_order_total_text', __( 'Estimated Order Total', 'linear-checkout-for-woo-by-cartimize' ) ));
						}else{
							echo esc_html(apply_filters( 'cartimize_order_summary_order_total_text', __( 'Total', 'woocommerce' ) ));
						}
				 	?>
				 </td>
				<td class="price"><?php wc_cart_totals_order_total_html(); ?></td>
			</tr>
	<?php
}

function cartimize_get_shipping_total() {
	$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
	$shipping_methods        = cartimize_get_all_package_shipping_methods();
	$new_shipping_total      = esc_html__( 'Not Calculated', 'linear-checkout-for-woo-by-cartimize' );

	if ( WC()->customer->has_calculated_shipping() && is_array( $chosen_shipping_methods ) && $chosen_shipping_methods[0] !== false ) {
		$new_shipping_total = WC()->cart->get_cart_shipping_total();
	} elseif ( WC()->customer->has_calculated_shipping() && is_array( $chosen_shipping_methods ) && $chosen_shipping_methods[0] === false ) {
		$new_shipping_total = esc_html__( 'Not Available', 'linear-checkout-for-woo-by-cartimize' );
	} elseif ( ! WC()->customer->has_calculated_shipping() && ( is_array( $chosen_shipping_methods ) && $chosen_shipping_methods[0] === false ) && count( $shipping_methods ) == 0 ) {
		$new_shipping_total = esc_html__( 'Not Calculated', 'linear-checkout-for-woo-by-cartimize' );
	} elseif ( count( $shipping_methods ) > 0 ) {
		$new_shipping_total = WC()->cart->get_cart_shipping_total();
	}

	return $new_shipping_total;
}

function cartimize_get_all_package_shipping_methods() {
	$packages    = WC()->shipping->get_packages();
	$all_methods = [];

	foreach ( $packages as $i => $package ) {
		$available_methods = $package['rates'];

		if ( count( $available_methods ) > 0 ) {
			foreach ( $available_methods as $available_method ) {
				$all_methods[ $available_method->id ] = $available_method;
			}
		}
	}

	return $all_methods;
}

function cartimize_customer_info_tab() {
	// Before Customer Info Tab
	do_action( 'cartimize_checkout_before_customer_info_tab' );

	/*
	 * Customer Info Content
	 */
	do_action( 'cartimize_customer_info_tab' );

	// After Customer Info Tab
	do_action( 'cartimize_checkout_after_customer_info_tab' );
}

function cartimize_shipping_method_tab() {
	// Before Shipping Method Tab
	do_action( 'cartimize_checkout_before_shipping_method_tab' );

	/**
	 * Shipping Method Content
	 */
	do_action( 'cartimize_shipping_method_tab' );

	// After Shipping Method Tab
	do_action( 'cartimize_checkout_after_shipping_method_tab' );
}


function cartime_get_all_step_status(){
	$steps = WC()->session->get( 'cartimize_steps' );

	if ( !empty( $steps ) ) {
		return $steps;
	}
	// 1 -> unfilled, 2 -> filling, 3 -> filled
	$steps = array(
				'cartimize-delivery-info' => 1,
				'cartimize-shipping-method' =>1,
				'cartimize-payment-method' =>1,
				'cartimize-review-tab' =>1
				);
	WC()->session->set( 'cartimize_steps', $steps );
	return $steps;
}

function cartimize_get_step_status( $step ){

	$all_steps_status = cartime_get_all_step_status();

	if ( isset( $all_steps_status[ $step ] ) ) {
		return $all_steps_status[ $step ];
	}
}

function cartimize_change_step_status( $step , $status = 3 ){
	$all_steps_status = cartime_get_all_step_status();
	$previous_step = cartimize_get_previous_step( $step );
	if ( isset( $all_steps_status[ $previous_step ] ) ) {
		$all_steps_status[ $previous_step ] = $status;
		WC()->session->set( 'cartimize_steps', $all_steps_status );
	}
}

function cartimize_get_previous_step( $step ){
	$previous_steps = array(
				'cartimize-shipping-method' => 'cartimize-delivery-info',
				'cartimize-payment-method' => 'cartimize-shipping-method',
				'cartimize-review-tab' => 'cartimize-payment-method'
				);
	if ( $step == 'cartimize-payment-method' && !cartimize_show_shipping_tab() ) {
		return 'cartimize-delivery-info';
	}
	if ( isset( $previous_steps[ $step ] ) ) {
		return $previous_steps[ $step ];
	}

	return $step;
}

function cartimize_get_active_filing_step(){
	$active_filling_step = WC()->session->get( 'active_filling_step' );

	if ( $active_filling_step == 'cartimize-delivery-info' && !cartimize_show_customer_info_tab() ) {
		return 'cartimize-payment-method';
	}
	
	if ( !empty( $active_filling_step ) ) {
		return $active_filling_step;
	}

	return 'cartimize-delivery-info';

}

function cartimize_next_filing_step(){
	$active_filling_step = cartimize_get_active_filing_step();

	switch ( $active_filling_step ) {
		case 'cartimize-delivery-info':
			if ( cartimize_show_shipping_tab() ) {
				return 'cartimize-shipping-method';
			}else{
				return 'cartimize-payment-method';	
			}
		case 'cartimize-shipping-method':
			return 'cartimize-payment-method';
		case 'cartimize-payment-method':
			return 'cartimize-review-tab';
		default:
			return 'cartimize-review-tab';
	}
}

function cartimizeSetActiveStep( $step ){

	if ( $step == "cartimize-review-tab" && cartimize_review_step_supported_payment() == false ) {
		$step = 'cartimize-payment-method';
	}
	WC()->session->set( 'active_filling_step', $step );
}

function cartimize_review_step_supported_payment(){

	$supported_payments = $available_gateways_keys = array();
	if ( defined( 'CARTIMIZE_REVIEW_STEP_SUPPORTED_PAYMENT' ) ) {
		$supported_payments = explode(',', str_replace(' ', '',CARTIMIZE_REVIEW_STEP_SUPPORTED_PAYMENT));
	}

	$available_gateways =  WC()->payment_gateways->get_available_payment_gateways();

	if ( !empty( $available_gateways ) ) {
		$available_gateways_keys = array_keys( $available_gateways );
	}

	$review_step_payments = apply_filters( 'cartimize_review_step_supported_payments', $supported_payments );
	$diff = array_diff( $available_gateways_keys, $review_step_payments );

	if ( empty( $diff ) ) {
		return true;
	}

	return false;
}

function cartimize_save_changes_button_html( $return = false, $id = '' ){
	$active_filling_step = cartimize_get_active_filing_step();
	$update_active_filling_step = '';
	if ( $id == $active_filling_step ) {
		$step = cartimize_registerd_steps();
		$currentKey = array_keys($step, $id);
		$active_filling_step = cartimize_next_element($step, $currentKey[0]);
		$update_active_filling_step = 'update_active_filling_step';
	}

	$save_changes = apply_filters( 'cartimize_save_changes_button', sprintf( '<input type="button" id="'.$id.'-continue" data-tab="#'.$active_filling_step.'" class="cartimize-primary-btn cartimize_save_changes cartimize-next-tab '.$update_active_filling_step.'" value="%s">', wp_kses_post(apply_filters( 'cartimize_save_changes_button_label', esc_html__( 'Save Changes', 'linear-checkout-for-woo-by-cartimize' ) ) ) ));

	if ( $return ) {
		return $save_changes;
	}

	$allowed_html = array(
								"input" => array( "type" => true, "id"=> true, "data-*" => true, "class" => true, "value"=> true)
							);

	echo wp_kses($save_changes, $allowed_html);
}

function cartimize_estimate_shipping_and_order_total(){

	$active_filling_step = cartimize_get_active_filing_step();
	if ( in_array( $active_filling_step, array( 'cartimize-delivery-info', 'cartimize-shipping-method' ) ) ) {
		return true;
	}

	return false;
}

function cartimize_shipping_fields_remove_placeholder( $field, $key ){
	
	if ( !in_array($key, apply_filters( 'cartimize_shipping_fields_remove_placeholder', array( 'shipping_address_2', 'shipping_state') ) )) {
		$field['placeholder_not_required'] = true;
	}

	return $field;
}

function cartimize_billing_fields_remove_placeholder( $field, $key ){
	
	if ( !in_array($key, apply_filters( 'cartimize_billing_fields_remove_placeholder', array( 'billing_address_2', 'billing_state') ) )) {
		$field['placeholder_not_required'] = true;
	}

	return $field;
}

function cartimize_get_mobile_mini_cart_html(){
	$cart = WC()->cart;

	ob_start();

	$cart_data = $cart->get_cart();

	?>
	<div class="order-summary-hdr" id="cartimize-mini-cart">
		<div>
			<?php printf( esc_html(_n( '%s item', '%s items', esc_html(number_format_i18n($cart->get_cart_contents_count(), 'linear-checkout-for-woo-by-cartimize' )))), esc_html(number_format_i18n( $cart->get_cart_contents_count() )) ) ?>
			<?php wc_cart_totals_order_total_html(); ?>
		</div>
	</div>
	<?php

	return apply_filters( 'cartimize_mobile_mini_cart_html', ob_get_clean() );
}

function cartimize_welcome_back_action_html(){
	$user_id = get_current_user_id();
	if ( $user_id !=0 ) {
		$user_info = get_userdata( $user_id );
		$first_name = $user_info->first_name;
		$user_email = $user_info->user_email;
		$last_name = !empty($user_info->last_name)? $user_info->last_name: '';
		if ( !empty( $first_name )) {
			echo esc_html__($first_name.' '.$last_name). '<span class="cartimize-user-account-email"> ('.$user_email.') </span>';
		}else{
			echo esc_html__($user_info->user_login). '<span class="cartimize-user-account-email"> ('.$user_email.') </span>';
		}
	}
	
}

function cartimize_get_place_order( $order_button_text = false, $return_html = true ) {
	if ($return_html) {
		ob_start();
	}

	$order_button_text = ! $order_button_text ? apply_filters( 'woocommerce_order_button_text', esc_html__( 'Place order securely', 'linear-checkout-for-woo-by-cartimize' ) ) : $order_button_text;
	?>
	<div class="<?php echo wp_kses_post(join( ' ', apply_filters( 'cartimize_place_order_button_container_classes', array( 'place-order' ) ) ) ); ?>" id="cartimize-place-order">
        <?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="cartimize-primary-btn cartimize-next-tab validate" name="woocommerce_checkout_place_order" id="place_order" formnovalidate="formnovalidate" value="' . esc_html( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // @codingStandardsIgnoreLine ?>

		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>
	<?php
	if ( ! is_ajax() ) {
		do_action( 'woocommerce_review_order_after_payment' );
	}

	if ($return_html) {
		return ob_get_clean();
	}
}

function cartimize_place_order( $order_button_text = false ) {
	cartimize_get_place_order( $order_button_text, false );
}

function cartimize_split_names( $name ){
	$split_names = array();
	$parts = explode(" ", $name);
	if(count($parts) > 1) {
	    $split_names['last_name'] = array_pop($parts);
	    $split_names['first_name'] = implode(" ", $parts);
	}
	else
	{
	    $split_names['first_name'] = $name;
	    $split_names['last_name'] = " ";
	}

	return $split_names;
}

function cartimize_failed_login_error_message_html( $error_msg, $error_code ){
	if ( $error_code == 'incorrect_password' ) {
		return '<a class="message">'.esc_html__('The password you entered is incorrect', 'linear-checkout-for-woo-by-cartimize').'.<br>'.esc_html__( 'Please check your password or', 'linear-checkout-for-woo-by-cartimize').'  <a target="_blank" style="text-decoration:underline !important;" href="'.wp_lostpassword_url().'">'.esc_html__( 'reset your password', 'linear-checkout-for-woo-by-cartimize').'</a>.</a>';
	}

	return $error_msg;
}

function cartimize_get_session_notices(){
	$all_notices  = WC()->session->get( 'wc_notices', array() );

	// Filter out empty messages
	foreach( $all_notices as $key => $notice ) {
		if ( empty( array_filter( $notice ) ) ) {
			unset( $all_notices[ $key ] );
		}
	}

	$notice_types = apply_filters( 'woocommerce_notice_types', array( 'error', 'success', 'notice' ) );
	$notices      = [];

	foreach ( $notice_types as $notice_type ) {
		if ( wc_notice_count( $notice_type ) > 0 && isset( $all_notices[ $notice_type ] ) ) {
			$notices[ $notice_type ] = [];

			// In WooCommerce 3.9+, messages can be an array with two properties:
			// - notice
			// - data
			foreach ( $all_notices[ $notice_type ] as $notice ) {
				$notification = apply_filters( 'cartimize_parse_session_notifications' ,$notice['notice'] );
				if ( is_array( $notification ) && isset( $notification[1]) && isset($notification[0]) ) {
					$notices[ $notice_type ][ $notification[1] ] = $notification[0];
				}else{
					$notices[ $notice_type ][] = isset( $notice['notice'] ) ? $notice['notice'] : $notice;
				}
			}
		}
	}

	wc_clear_notices();

	return $notices;
}
function cartimize_disable_theme_load( $stylesheet_dir ) {

	// disable theme entirely for now
	return "fake";
}

function cartimize_oxygen_template_name($template) {
	return "cartimize-is-not-a-theme";
}

if ( ! function_exists( 'is_lcw_checkout' ) ) {

	/**
	 * Is_checkout - Returns true when viewing the checkout page.
	 *
	 * @return bool
	 */
	function is_lcw_checkout() {
		global $post, $wp, $wp_query;
		$current_post_id = $wp_query->post->ID;
		$page_id = wc_get_page_id( 'checkout' );
		$post_type = get_post_type( $page_id );

		$q= new WP_Query();

		return ( $page_id && $post_type == 'page' ) || wp_post_content_has_shortcode( 'woocommerce_checkout' ) || apply_filters( 'woocommerce_is_checkout', false ) || defined( 'WOOCOMMERCE_CHECKOUT' );
	}
}

function cartimize_post_content_has_shortcode( $tag = '' ) {
	global $post;

	return is_singular() && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $tag );
}

function cartimize_registerd_steps(){
	$steps = array(
			        "step1"=> "cartimize-delivery-info",
			        "step2"=> "cartimize-shipping-method",
			        "step3"=> "cartimize-payment-method",
			        "step4"=> "cartimize-review-tab"
			);
	return apply_filters( 'cartimize_registerd_steps', $steps );
}

function cartimize_next_element(array $array, $currentKey)
{
    if (!isset($array[$currentKey])) {
        return false;
    }
    $nextElement = false;
    foreach ($array as $key => $item) {
        $nextElement = next($array);
        if ($key == $currentKey) {
            break;
        }
    }

    return $nextElement;
}

function cartimize_pro_badge(){
	?>
		<div class="pro-feature-badge">
			<span class="dashicons dashicons-star-filled"></span>
		</div>
	<?php
}

function cartimize_remove_by_plugin_class($tag, $class_name, $functionName, $isAction = false, $priority = 10) {
    if (!class_exists($class_name)) {
        return null;
    }

    global $wp_filter;

    if (empty($wp_filter[$tag][$priority])) {
        return null;
    }

    foreach ($wp_filter[$tag][$priority] as $callable) {
        if (empty($callable['function']) || !is_array($callable['function']) || count($callable['function']) < 2) {
            continue;
        }

        if (!is_a($callable['function'][0], $class_name)) {
            continue;
        }

        if ($callable['function'][1] !== $functionName) {
            continue;
        }

        if ($isAction) {
            remove_action($tag, $callable['function'], $priority);
        } else {
            remove_filter($tag, $callable['function'], $priority);
        }

        return $callable['function'];
    }

    return null;
}

function cartimize_woocommerce_form_field( $field, $key, $args ){

	if ( $args['required'] == false && isset( $args[ 'cartimize_optional_label' ] )) {
		
	$field = '<div class="form-row collapsed-link '.esc_attr($key).'-collapsed-link ">+ <a class="link show_'.esc_attr($key).'" sourceclass='.esc_attr($key).' tabindex="0">'.wp_kses_post($args[ 'cartimize_optional_label' ]).'</a></div>'.$field;
		
	}
	return $field;
}

function cartimize_woocommerce_form_field_args( $args, $key ){

	$allowes_types = array( 'text','password','email','tel','number','textarea','datepicker','select','inspireradio', 'inspirecheckbox', 'timepicker','colorpicker','wpdeskmultiselect','inspirecheckbox', 'file', 'time', 'radio', 'datetime-local', 'date', 'month', 'week', 'checkbox', 'url', "multiselect", "datetime_local", "checkboxgroup" );

	$allowes_types = apply_filters( 'cartimize_optional_field_allowed_types', $allowes_types);

	if ( isset($args['type'])) {
		$args['class'][] = 'form-type-'.$args['type'];
		if ( in_array( $args['type'], $allowes_types ) ) {
			if ( $args['required'] == false && !empty($args['label'])) {
				$args['is_hidden'] = true;
				if ( !isset($args['input_class']) && !is_array($args['input_class']) ) {
					$args['input_class'] = array();
				}
				$args['input_class'][]= 'garlic-auto-save';
				$args['class'][]= 'd-none';
				$args['class'][]= $key;
				$optional_label = esc_html__('Add', 'linear-checkout-for-woo-by-cartimize');
				$optional_label .= ' '.esc_html($args['label']);
				$args['cartimize_optional_label'] = apply_filters( 'cartimize_optional_collapse_label_text', $optional_label, $args, $key);
				
			}
			
		}
	}

	return $args;
}

function cartimize_get_formated_shipping_fields_summary(){
	$standard_fields = array('billing_email', 'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode', 'shipping_country', 'shipping_phone');
	$billing_email = WC()->checkout()->get_value( 'billing_email' );
	$shipping_address = cartimize_get_shipping_details( WC()->checkout() );
	$shipping_fullname = WC()->checkout()->get_value( 'shipping_full_name' );
	if ( empty( $shipping_fullname ) ) {
		$shipping_fullname = cartimize_get_shipping_full_name();
	}
	$checkout_fields = WC()->checkout()->get_checkout_fields();
	$additional_fields = array();
	if ( isset( $checkout_fields['shipping'] ) ) {
		$additional_fields = cartimize_get_additional_checkout_field( $checkout_fields['shipping'], $standard_fields );
	}
	$HTML = '<div id="shipping_summary_wrapper">';
	if ( !empty( $billing_email ) ) {
		$HTML .= '<div id="shipping_summary_email" class="shipping-details-content" style="padding-bottom: 5px; display: inline-block;">' . $billing_email . '</div>';
	}
	if ( !empty( $shipping_fullname ) ) {
		$HTML .= '<div id="shipping_summary_fullname" class="shipping-details-content">' . $shipping_fullname . '</div>';
	}
	if ( !empty( $shipping_address ) && WC()->cart->needs_shipping() ) {
		$HTML .= '<div id="shipping_summary_address" class="shipping-details-content">' . str_replace( array("|LCW|,", ", |LCW|"), ',</br>', $shipping_address). '</div>';
	}

	if ( WC()->checkout()->get_value( 'shipping_phone' ) ) {
		$HTML .= '<div id="shipping_phone_number" class="shipping-details-content"></br>' . WC()->checkout()->get_value( 'shipping_phone' ) . '</div>';
	}

	if ( !empty( $additional_fields ) ) {
		$HTML .= '<br><div class="shipping-details-content"><p class="">' . implode( '<br />', $additional_fields ) . '</p></div>';
	}

	$HTML .= '</div>';
	
	return $HTML;
	
}

function cartimize_get_formated_billing_fields_summary(){
	$standard_fields = array('billing_email', 'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_country', 'billing_phone');
	$billing_address = cartimize_get_billing_details( WC()->checkout() );
	$billing_fullname = WC()->checkout()->get_value( 'billing_full_name' );
	if ( empty( $billing_fullname ) ) {
		$billing_fullname = cartimize_get_billing_full_name();
	}
	$checkout_fields = WC()->checkout()->get_checkout_fields();
	$additional_fields = array();
	if ( isset( $checkout_fields['billing'] ) ) {
		$additional_fields = cartimize_get_additional_checkout_field( $checkout_fields['billing'], $standard_fields );
	}
	$HTML = '<div id="billing_summary_wrapper">';
	
	if ( !empty( $billing_fullname ) ) {
		$HTML .= '<div id="billing_summary_fullname" class="billing-details-content">' . $billing_fullname . '</div>';
	}
	if ( !empty( $billing_address ) ) {
		$HTML .= '<div id="billing_summary_address" class="billing-details-content">' . str_replace( array("|LCW|,", ", |LCW|"), ',</br>', $billing_address). '</div>';
	}

	if ( WC()->checkout()->get_value( 'billing_phone' ) ) {
		$HTML .= '<div id="billing_phone_number" class="billing-details-content"></br>' . WC()->checkout()->get_value( 'billing_phone' ) . '</div>';
	}

	if ( !empty( $additional_fields ) ) {
		$HTML .= '<br><div class="billing-details-content"><p class="">' . implode( '<br />', $additional_fields ) . '</p></div>';
	}

	$HTML .= '</div>';
	
	return $HTML;
	
}

function cartimize_get_additional_checkout_field( $checkout_fields, $standard_fields ){
	$additional_fields = array();
	foreach ($checkout_fields  as $key => $value) {
		if ( !in_array( $key, $standard_fields ) && (!empty( WC()->checkout()->get_value( $key ) ) && !empty($value['label'])) ) {
			$field_value =  WC()->checkout()->get_value( $key ) ;
			if ( is_array( $field_value ) ) {
				$field_value = implode( ',', $field_value );
			}elseif(basename( wp_normalize_path($field_value)) !== $field_value ) {
				$field_value = basename( wp_normalize_path($field_value));
			}
			$additional_fields[] = sprintf(
				'<span>%1$s</span>: %2$s',
				esc_html( $value['label'] ),
				$field_value
			);	
		}
	}

	return $additional_fields;
}

function cartimize_get_shipping_full_name(){
	$full_name = "";
	$first_name = WC()->checkout()->get_value( 'shipping_first_name' );
	$last_name = WC()->checkout()->get_value( 'shipping_last_name' );

	if ( !empty( $first_name ) ) {
		$full_name = $first_name;
	}

	if ( !empty( $last_name ) ) {
		$full_name .= ' '.$last_name;
	}

	return $full_name;
}

function cartimize_get_billing_full_name(){
	$full_name = "";
	$first_name = WC()->checkout()->get_value( 'billing_first_name' );
	$last_name = WC()->checkout()->get_value( 'billing_last_name' );
	
	if ( !empty( $first_name ) ) {
		$full_name = $first_name;
	}

	if ( !empty( $last_name ) ) {
		$full_name .= ' '.$last_name;
	}

	return $full_name;
}