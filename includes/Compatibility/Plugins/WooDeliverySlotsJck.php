<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility\Plugins;

use Cartimize\Compatibility\Core;

class WooDeliverySlotsJck extends Core {
	public function is_available() {
		return class_exists('\jckWooDeliverySlots');
	}

	public function pre_init() {
		$this->change_setting();
	}

	public function change_setting(){
		$settings = get_option('jckwds_settings');

		if( isset($settings ['general_setup_position']) && $settings ['general_setup_position'] != 'cartimize_before_shipping_method_list_html' ){
			$settings['general_setup_position'] = 'cartimize_before_shipping_method_list_html';
			update_option('jckwds_settings', $settings); 
		}
	}

	function typescript_class_and_params( $compatibility ) {
		$compatibility[] = [
			'class'  => 'WooDeliverySlotsJck',
			'params' => [],
		];

		return $compatibility;
	}
}