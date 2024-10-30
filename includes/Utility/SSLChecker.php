<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Utility;

use Exception;

class SSLChecker{

	private $host;
	private $port;

	public function __construct($host = false, $port = 443) {

		if (empty($host)) {
			$host = $_SERVER['HTTP_HOST'];
		}
		$this->host = $host;
		$this->port = $port;
		add_filter( 'cartimize_get_ssl_signature', array( $this, 'get_ssl_signature' ));
	}

	public static function isEnabled(){
		return function_exists('openssl_x509_parse') && function_exists('stream_socket_client') && function_exists('stream_context_create');
	}

	public function get_ssl_signature(){
		if (self::isEnabled()) {
			return false;
		}

		try {

			$get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
			$read = stream_socket_client("ssl://".$this->host .":".$this->port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);

			$cert = stream_context_get_params($read);

			$certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

			if (empty($certinfo['signatureTypeSN'])) {
				return $certinfo['signatureTypeSN'];
			}
			
		} catch (Exception $e) {
			return false;
		}
	}
}