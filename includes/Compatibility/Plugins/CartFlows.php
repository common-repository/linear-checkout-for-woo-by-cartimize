<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;

class CartFlows extends Core {
	public function is_available() {
		return defined( 'CARTFLOWS_FILE' );
	}

	public function run() {
		global $post;

		// Maybe prevent Cartimize template from being loaded
		if ( _is_wcf_checkout_type() ) {
			$checkout_id = $post->ID;
		} else {
			$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
		}

		$use_cartimize_template = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-lcw-use-template' );
		if ( $use_cartimize_template == 'no' ) {
			wc_maybe_define_constant( 'CARTIMIZE_BYPASS_TEMPLATE', true );
		}
	}

	public function admin_init() {
		add_action( 'cartflows_checkout_style_tab_content', array( $this, 'admin_setting' ), 10, 1 );
		add_filter( 'cartflows_checkout_meta_options', array( $this, 'admin_add_option' ) );
	}

	function admin_add_option( $options ) {
		$options['wcf-lcw-use-template'] = array(
			'default'  => '',
			'sanitize' => 'FILTER_DEFAULT',
		);

		return $options;
	}

	function admin_setting( $options ) {
		echo wcf()->meta->get_checkbox_field(// PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'label' => 'LCW',
				'name'  => 'wcf-lcw-use-template',
				'value' => $options['wcf-lcw-use-template'],// PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'after' => esc_html__('Use Linear Checkout for WooCommerce Template', 'linear-checkout-for-woo-by-cartimize'),
			)
		); 
	}
}
