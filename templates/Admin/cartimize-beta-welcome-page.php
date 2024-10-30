<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined('ABSPATH')){ exit; }

?>
<div class="cartimize_admin">
	
	<div class="acc-login-form">
		<h2 style="text-align:center;margin: 0 0 30px;"><?php echo esc_html__( 'Welcome to Linear Checkout for WooCommerce', 'linear-checkout-for-woo-by-cartimize' ); ?></h2>
		<div class="create-content" style="text-align: left; border: 1px dashed #ccc; border-radius: 5px; background-color: #f9f9f9; margin-bottom: 20px; padding: 10px;">
		<div>You can be an integral part of the plugin's growth by choosing to share non-sensitive usage data and non-personally identifiable performance data  solely for the purpose of improving the plugin.<br><br> We'll never share this information with anyone or use it for purposes other than improving the plugin. Pinky swear!</div></div>
		<input type="hidden" id="join_beta" name="join_beta" value="1">
		<input type="hidden" id="force_send" name="force-send" value="1">
		<input type="submit" value="Sure, I'll help!" name="accept-stats" class="button-primary create-content" id="cartimize_join_beta_btn" style="display: block; width: 100%; padding: 5px; font-size: 16px; margin-top: 10px;">
		<a type="submit" name="service_create_account" onclick="document.getElementById('join_beta').value='0';document.getElementById('force_send').value='0';document.getElementById('mainform').submit()" value="no_beta" style="padding: 10px; display: inline-block; margin: 10px 114px 0; cursor: pointer;">No, thanks.</a>
	</div>
</div>