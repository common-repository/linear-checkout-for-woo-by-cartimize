<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

use Cartimize\Admin\Admin;

class CartimizeException extends Exception {
	//$error is as error code like slug
	protected $error;
	public function __construct($error = '', $message = '', $code = 0, $previous_throwable = NULL){
		$this->error = $error;
		parent::__construct($message, $code, $previous_throwable);
	}
	public function getError(){
		return $this->error;
	}
	public function getFormatedError(){
		return Admin::cartimize_get_error_msg($this->error);
	}
	public function getErrorMsg(){
		$msg = $this->getMessage();
		return empty($msg) ?  $this->getFormatedError() : $msg;
	}
}