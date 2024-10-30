<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Core;
use Cartimize\Cartimize\Controllers\TemplateController;


class TemplateCore {
	private $_basepath;
	private $_baseuri;
	private $_slug;

	public function __construct( $slug ) {
		$template_dir = apply_filters('cartimize_template_dir', CARTIMIZE_TEMPLATE_PATH);
		$template_url = apply_filters('cartimize_template_url', CARTIMIZE_TEMPLATE_URL);
		if ( is_dir( trailingslashit( $template_dir ) . $slug ) ) {
			$this->_basepath = trailingslashit( $template_dir ) . $slug;
			$this->_baseuri = trailingslashit( $template_url )  . $slug;
		} else {
			// Otherwise, load the default template
			return new TemplateCore( 'default' );
		}

		$this->_slug = $slug;

	}

	public function load_functions(){
		$functions_path       = trailingslashit( $this->get_basepath() ) . 'functions.php';

		if ( file_exists( $functions_path ) ) {
			require_once $functions_path;
		}
	}

	public function get_basepath() {
		return $this->_basepath;
	}

	public function get_template_uri() {
		return $this->_baseuri;
	}

	public function get_slug() {
		return $this->_slug;
	}

	public function view( $filename, $parameters = [] ) {
		$filename_with_basepath = trailingslashit( $this->get_basepath() ) . $filename;
		$template_name = $this->get_slug();
		$template_piece_name = basename( $filename, '.php' );

		if ( file_exists( $filename_with_basepath ) ) {
			
			extract( $parameters );

			require $filename_with_basepath;
		}
	}
}