<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */
namespace Cartimize\License\Actions;
use Cartimize\Core\ActionCore;
use Cartimize\Admin\Auth;

class CheckNowAction extends ActionCore{
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	public function action() {
		try{
			$result = false;
			if ( isset($_POST['action']) && $_POST['action'] == 'check_now'  ) {
				global $cartimize;
				$response_data = $cartimize->get_license_instance()->check_now();
				if( !isset($response_data['status']) || !isset($response_data['message']) ){
					throw new \CartimizeException('service__invalid_response');
				}
				if( $response_data['status'] === 'success'){
					$response['refresh'] = true;
				}elseif( $response_data['status'] === 'error' && $response_data['message'] ){
					throw new \CartimizeException('service__'.$response_data['message']); 
				}
				else{
					throw new \CartimizeException('service__invalid_response');
				}

			}else{
				throw new \CartimizeException('service__invalid_response');
				$response['error_msg'] = '';
			}
			$response['status'] = $result ? 'success' : 'error';
		}
		catch(\CartimizeException $e){
			$error = $e->getError();
			$error_msg = $e->getErrorMsg();
		
			$response = array();
			$response['status'] = 'error';
			$response['error_msg'] = $error_msg;
			$response['error_code'] = $error;
		}

		$this->out( $response );
	}
}