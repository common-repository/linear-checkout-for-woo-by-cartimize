<?php

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;

class ExtraCheckoutFieldsBrazil extends Core {
	public function __construct() {
		parent::__construct();
	}

	public function is_available() {
		return class_exists( '\\Extra_Checkout_Fields_For_Brazil' );
	}

	function pre_init(){
		if ($this->is_available()) {
			remove_filter( 'woocommerce_form_field_args', 'cartimize_woocommerce_form_field_args', 1000000, 3 );
			remove_filter( 'woocommerce_form_field', 'cartimize_woocommerce_form_field', 1000000, 3 );
		}
	}

	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = [
			'class'  => 'ExtraCheckoutFieldsBrazil',
			'params' => [],
		];

		return $compatibility;
	}

}
