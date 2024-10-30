<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */
namespace Cartimize\License\Actions;
use Cartimize\Core\ActionCore;

class ProInstall extends ActionCore{
	public function __construct( $id, $no_privilege, $action_prefix ) {
		parent::__construct( $id, $no_privilege, $action_prefix );
	}

	public function action() {
		$response = array();
		try{
			$result = false;
			if ( isset($_POST['action']) && $_POST['action'] == 'pro_install'  ) {
				global $wp_filesystem, $cartimize;

				if (!$cartimize->get_license_instance()->is_server_writable()) {
				    throw new \CartimizeException('failed_please_add_ftp_install_remote_file');
				}

				$install_plugin_result = $cartimize->get_license_instance()->install_plugin();
				$result = 1;
				$response = $cartimize->get_license_instance()->is_pro_install_or_active();

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