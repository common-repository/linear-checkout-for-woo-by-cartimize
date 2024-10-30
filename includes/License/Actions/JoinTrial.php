<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */
namespace Cartimize\License\Actions;
use Cartimize\Core\ActionCore;
use Cartimize\Admin\Auth;

class JoinTrial extends ActionCore{
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	public function action() {
		$response = array();
		try{
			$result = false;
			if ( isset($_POST['action']) && $_POST['action'] == 'join_trial'  ) {
				global $cartimize;
				$auth_instance = new Auth($cartimize);
				$data = $config = array();
				$config['email'] = $cartimize->get_license_controller()->get_setting('email');
				$config['site_token'] = $cartimize->get_license_controller()->get_setting('site_token');
				$data['user_id'] = $cartimize->get_license_controller()->get_setting('user_id');
				$response_data = $auth_instance->do_service_request($config, 'join_trial', $data);
				if( !isset($response_data['status']) || !isset($response_data['message']) ){
					throw new \CartimizeException('service__invalid_response');
				}

				if( $response_data['status'] === 'success'){

					$result = $auth_instance->save_creds_info($response_data);
					
					$response = $cartimize->get_license_instance()->is_pro_install_or_active();

				}elseif( $response_data['status'] === 'error' && $response_data['message'] ){
					throw new \CartimizeException('service__'.$response_data['message']); 
				}
				else{
					throw new \CartimizeException('service__invalid_response');
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
}