<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Controllers;
use Cartimize\Core\TemplateCore;


class TemplatesController{
	private $_active_template;

	public function __construct( $active_template_slug ) {
		$this->_active_template = new TemplateCore( $active_template_slug );

		add_action( 'cartimize_load_template_assets', array( $this, 'enqueue_assets') );
		$this->get_active_template()->load_functions();
	}

	public function enqueue_assets(){
		$min = ( ! CARTIMIZE_DEV ) ? '.min' : '';
		wp_enqueue_style( 'cartimize_front_template_css', $this->get_active_template()->get_template_uri() . "/style.css", array(), CARTIMIZE_VERSION );
		wp_style_add_data( 'cartimize_front_template_css', 'rtl', 'replace' );
		wp_enqueue_script( 'wc-checkout', $this->get_active_template()->get_template_uri() . "/theme{$min}.js", array( 'jquery' ), CARTIMIZE_VERSION, true );
		
	}

	public function get_active_template() {
		return $this->_active_template;
	}
}