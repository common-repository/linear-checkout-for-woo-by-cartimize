<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined('ABSPATH')){ exit; }

if(isset($_GET['redirect_to']) && !empty($_GET['redirect_to'])){
	$redirect_to = wc_clean( wp_unslash($_GET['redirect_to']));
}
else{
	global $cartimize_admin;
	$redirect_url = $cartimize_admin->get_login_redirect_url();
}

?>
<div class="cartimize_admin">
	
	<div class="acc-login-form create-form">
	<ul class="cartimize-tabs">
	  <li class="cartimize-radio-li">
	  	<input type="radio" name="account" id="create-account" value="create" class="create-account" checked="checked">
	  	<label for="create-account"><?php echo esc_html__( 'Create New Account', 'linear-checkout-for-woo-by-cartimize' ); ?></label>
	  </li>
	  <li class="cartimize-radio-li">
	  	<input type="radio" name="account" id="login-account" value="login" class="login-account">
	  	<label for="login-account"><?php echo esc_html__( 'Login To Your Account', 'linear-checkout-for-woo-by-cartimize' ); ?></label>
	  </li>
	</ul>
		<h2 class="create-content" style="text-align:center; margin-top: 30px;"><?php echo esc_html__( 'Create your Cartimize.com account', 'linear-checkout-for-woo-by-cartimize' ); ?></h2>
		<h2 class="login-content" style="text-align:center; margin-top: 30px;display: none;"><?php echo esc_html__( 'Login to your Cartimize.com account', 'linear-checkout-for-woo-by-cartimize' ); ?></h2>
		
		<div class="pad">
			<div id="cartimize_service_login_btn_result"></div>
			<fieldset>
				<label for="cartimize_service_email" style="margin-bottom: 3px; font-size: 12px; font-weight: bold;"><?php echo esc_html__( 'EMAIL ADDRESS (required)', 'linear-checkout-for-woo-by-cartimize' ); ?></label> 
				<input type="email" autofocus id="cartimize_service_email" style="width: 100%; padding: 5px 10px; margin: 0;" placeholder="name@company.com">
				<label class="create-content" style="margin-bottom: 3px; margin-top: 20px; font-size: 12px; font-weight: bold;"><?php echo esc_html__( 'PASSWORD', 'linear-checkout-for-woo-by-cartimize' ); ?></label> 
				<div class="create-content" style="font-style: italic;">A secure password will be generated and sent to your email.</div>
			</fieldset>

			<fieldset class="login-content" style="display: none;">
				<label style="margin-bottom: 3px; margin-top: 20px; font-size: 12px; font-weight: bold;" for="cartimize_service_password" style=""><?php echo esc_html__( 'PASSWORD', 'linear-checkout-for-woo-by-cartimize' ); ?><a style="padding-top: 0; font-weight: normal; text-decoration: none;" href="<?php echo esc_url(CARTIMIZE_SITE_LOST_PASS_URL); ?>" target="_blank" style="">Forgot password?</a></label> 
				<input type="password" id="cartimize_service_password">
				
			</fieldset>
		</div>
			<input type="button" value="Create my account" name="service_create_account" class="button-primary create-content" id="cartimize_service_create_btn" style="display: block; width: 100%; padding: 5px; font-size: 16px; margin-top: 10px;">
			<input type="button" value="Login to my account" name="service_login" class="button-primary login-content" id="cartimize_service_login_btn" style="display: none; width: 100%; padding: 5px; font-size: 16px;">
	</div>
	<?php
		if ( empty($_GET['require_signup'])) { ?>
			<div style="text-align: center; padding: 20px 0;" id="cartimize_skip_signup"><a style="cursor: pointer;" href="<?php echo esc_url(CARTIMIZE_SETTINGS_PAGE_URL); ?>&skip_signup=1" >Continue without creating an account.</a></div>
		<?php }
	?>
</div>

<script type="text/javascript">
var cartimize_redirect_after_login = '<?php echo ($redirect_url); ?>';
</script>