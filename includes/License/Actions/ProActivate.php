<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */
namespace Cartimize\License\Actions;
use Cartimize\Core\ActionCore;

class ProActivate extends ActionCore{
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	public function action() {
		$response = array();
		try{
			$result = false;
			if ( isset($_POST['action']) && $_POST['action'] == 'pro_activate'  ) {
				$this->activate_pro();
				$activate = activate_plugin(CARTIMIZE_PRO_SLUG);
				if ( is_wp_error( $activate ) ) {
					throw new \CartimizeException('pro_activation_failed');
				}else{
					$result = true;	
				}

			}else{

				throw new \CartimizeException('invalid_request');
			}
			$response['status'] = $result ? 'success' : 'error';
		}
		catch(\CartimizeException $e){
			$error = $e->getError();
			$error_msg = $e->getErrorMsg();
		
			$response['status'] = 'error';
			$response['error_msg'] = $error_msg;
			$response['error_code'] = $error;
		}

		$this->out( $response );
	}

	public function activate_pro(){
		try{
			global $cartimize;
			if ( is_object( $cartimize ) ) {
				$response_data = $cartimize->get_license_instance()->activate_pro();
				if ( $response_data == true ) {
					return true;
				}
			}
		}catch(\CartimizeException $e){
			return false;
		}

		return false;
	}
}