<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

function cartimize_main_container_sub_header_print(){
	?>
		<div class="sub-header">
			<?php do_action( 'cartimize_inside_sub_header' )  ?>
			<div class="sec-badge">
				<div class="sec-text-static"><?php echo esc_html__( 'Secure Checkout', 'linear-checkout-for-woo-by-cartimize' ) ?></div>
					<?php echo esc_html__( '256-bit, Bank-Grade TLS Encryption', 'linear-checkout-for-woo-by-cartimize' ) ?>
			</div>
		</div>
	<?php
}

function cartimize_login_content_print(){
	$enable_enhanced_login = 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' );
	?>
	<?php if ( ! is_user_logged_in() && $enable_enhanced_login) : ?>
		<div class="login-prompt" >
			<span style="font-weight: 600;">
				<?php echo wp_kses_post(apply_filters( 'cartimize_already_have_account_text', esc_html__( 'Already have an account with us?', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
			</span>
			</br>
			<a href="#cartimize-login-prompt" rel="modal:open">
				<?php echo wp_kses_post(apply_filters( 'cartimize_login_to_account_text', esc_html__( 'Login to your account', 'linear-checkout-for-woo-by-cartimize' ) )); ?></a>&nbsp;
				<?php echo wp_kses_post(apply_filters( 'cartimize_faster_checkout_text', esc_html__( 'for a faster checkout.', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
				<?php do_action( 'cartimize_login_modal_html' ) ?>
		</div>
		<?php elseif(is_user_logged_in()): ?>
		<div class="login-prompt <?php echo is_user_logged_in()?'logged-in':''; ?> " >
			<span>
				<?php echo wp_kses_post(apply_filters( 'cartimize_welcome_back_text', esc_html__( 'Welcome back,', 'linear-checkout-for-woo-by-cartimize' ))); ?>
				<?php	do_action( 'cartimize_welcome_back_action' );
				?>
			</span>
		</div>
	<?php endif; ?>
	<?php
}

function cartimize_form_attributes( $id = false, $row = true ) {
    ?>
    name ="checkout" id="<?php echo $id ? esc_attr($id) : 'checkout'; ?>" class="woocommerce-checkout checkout<?php echo $row ? ' row' : ''; ?>" method="POST" formnovalidate="" data-parsley-focus="first" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data"
    <?php
}

function cartimize_shipping_step(){
	do_action( 'woocommerce_checkout_shipping' );
}

function cartimize_shipping_address_print(){
	cartimize_get_main()->get_template_controller()->get_active_template()->view('form-shipping.php');
}

function cartimize_email_and_create_account_print(){

	$force = apply_filters('cartimize_create_account_on_checkout', true);
	?>
	<div class="account-form woocommerce-shipping-fields">
		<?php if ( !is_user_logged_in() || (is_user_logged_in() && empty(WC()->checkout()->get_value( 'billing_email' ))) ): ?>
			
		<?php cartimize_email_field(); ?>
		<?php else: ?>

			<input type="hidden" name="billing_email" id="billing_email" value="<?php echo esc_attr(WC()->checkout()->get_value( 'billing_email' )); ?>">

		<?php endif; ?>
		
		<?php if ( ! is_user_logged_in()  && WC()->checkout()->is_registration_enabled() && ($force) ): ?>
			<?php if ( ! WC()->checkout()->is_registration_required() ) : ?>
				<p class="form-row form-type-checkbox">
					<span class="landscape-helpTxt woocommerce-input-wrapper"><label tabindex="0" id="createaccountlabel" class="checkbox">
						<input type="checkbox" tabindex="0" id="createaccount" <?php checked( ( true === WC()->checkout()->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" class="input-checkbox"> <?php echo esc_html(apply_filters( 'cartimize_create_an_account_checkbox_text', esc_html__( 'Create an account using this email', 'linear-checkout-for-woo-by-cartimize' ) )); ?></label>
					</span>
				</p>
			<?php endif ?>

			<?php if ( WC()->checkout()->get_checkout_fields( 'account' ) ): ?>
				<?php cartimize_create_account_password() ?>
			<?php endif ?>
		<?php endif ?>
		<?php do_action( 'cartimize_inside_account_form' ) ?>
		
	</div>
	<?php
}

function cartimize_email_field(){
	$billing_fields        = WC()->checkout()->get_checkout_fields( 'billing' );
	$email_field           = $billing_fields['billing_email'];

	woocommerce_form_field( 'billing_email', $email_field, WC()->checkout()->get_value( 'billing_email' ) ); 
}

function cartimize_create_account_password( $show_password = false, $id = 'account_password', $custom_attr="" ){
	?>
		<p class="form-row form-row-wide cartimize-create-account-password" <?php  if( !$show_password && !WC()->checkout()->is_registration_required()){?>  style="display: none;"  <?php }  ?> >
			<label><?php echo esc_html(apply_filters( 'cartimize_create_password_text', esc_html__( 'Create a Password', 'linear-checkout-for-woo-by-cartimize' ) )); ?><abbr class="required" title="required">*</abbr></label>
			<input type="password" <?php echo wp_kses_post($custom_attr); ?> class="garlic-auto-save" data-persist="garlic" data-storage="true" name="<?php echo esc_attr($id); ?>" id="<?php echo esc_attr($id) ?>" autocomplete="off" style="background-image: url(&quot;data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGP6zwAAAgcBApocMXEAAAAASUVORK5CYII=&quot;);"><a class="link password-toggle show-create-password"><?php echo esc_html__( 'SHOW', 'linear-checkout-for-woo-by-cartimize') ?></a>
			<a class="link password-toggle hide-create-password" style="display: none;"><?php echo esc_html__( 'HIDE', 'linear-checkout-for-woo-by-cartimize') ?></a>
			<ul class="accoutn-errors-list filled" id="parsley-id-5" aria-hidden="false" style="display: none;"><li class="parsley-required"><?php echo esc_html__( 'This value is required.', 'linear-checkout-for-woo-by-cartimize') ?></li></ul>
		</p>
	<?php
}

function cartimize_shipping_nav_button_html() {
	
	?>

	<div class="form-row button-row cartimize-step-submit">
		<!-- div class="previous-button">
			<?php cartimize_return_to_cart_link(); ?>
		</div> -->
		<?php if ( cartimize_show_shipping_tab() ) : ?>
			<?php cartimize_continue_to_shipping_button(); ?>
		<?php else : ?>
			<?php cartimize_continue_to_payment_button(); ?>
		<?php endif; ?>
	</div>

	<?php
	
}
function cartimize_shipping_method_nav_button_html() {
	
	?>

	<div class="form-row button-row">
		<!-- <div class="previous-button">
			<?php cartimize_return_to_cart_link(); ?>
		</div> -->
		<?php cartimize_continue_to_payment_button(); ?>

	</div>

	<?php
	
}

function cartimize_show_shipping_tab() {
    return apply_filters( 'cartimize_show_shipping_tab', WC()->cart->needs_shipping() ) === true;
}

function cartimize_show_customer_info_tab() {
    return apply_filters( 'cartimize_show_customer_info_tab', ( !is_user_logged_in() || ( is_user_logged_in() && WC()->cart->needs_shipping()) || ( is_user_logged_in() && empty(WC()->checkout()->get_value( 'billing_email' )) ))  ) ;
}

function cartimize_shipping_deatils_summary_html(){
	?>
		<div class="sum-content shipping_summary_sum_content">
			<div id="shipping_summary_wrapper"></div>
			<a href="javascript:;" data-tab="#cartimize-delivery-info" class="cartimize-tab-link"><?php echo wp_kses_post(apply_filters( 'cartimize_edit_text', esc_html__( 'EDIT', 'linear-checkout-for-woo-by-cartimize' ) )); ?></a>
		</div>
	<?php
}

function cartimize_shipping_methods_html(){

	if ( cartimize_show_shipping_tab()) : ?> 
		<div class="clc-step" id="cartimize-shipping-method">
			<div class ="clc-step-heading-wrapper">
				<h3 class="clc-entry-title">
					<?php echo wp_kses_post(apply_filters( 'cartimize_shipping_method_text', esc_html__( "Shipping Method", 'linear-checkout-for-woo-by-cartimize' ) )); ?>
				</h3>
				<h3 class="clc-summary">
					<?php echo wp_kses_post(apply_filters( 'cartimize_shipping_method_text', esc_html__( 'Shipping', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
				</h3>
			</div>
			<div class="clc-entry">
				<div class="form-container">
					<?php do_action( 'cartimize_before_shipping_method_list_html' ) ?>
					<div id="cartimize-shipping-method-html">
						<?php cartimize_all_shipping_method_lists_html() ?>
					</div>
					<?php do_action( 'cartimize_shipping_methods_nav_button' ) ?>
				</div>
			</div>
			<div class="clc-summary">
				<?php do_action( 'cartimize_shipping_method_summary' ) ?>
			</div>
		</div>
	<?php endif;
}

function cartimize_shipping_method_summary_html(){
	?>
		<div class="sum-content shipping_method_sum_content">
			<div id="shipping_method_summary">
				
			</div>
			<a href="javascript:;" data-tab="#cartimize-shipping-method" class="cartimize-tab-link"><?php echo wp_kses_post(apply_filters( 'cartimize_edit_text', esc_html__( 'EDIT', 'linear-checkout-for-woo-by-cartimize' ) )); ?></a>
		</div>
	<?php
}

function cartimize_payment_methods_html(){
	?>
		<div class="clc-step" id="cartimize-payment-method">
			<div class ="clc-step-heading-wrapper">
				<h3 class="clc-entry-title">
					<?php echo wp_kses_post(apply_filters( 'cartimize_payment_method_text', esc_html__( "Payment Details", 'linear-checkout-for-woo-by-cartimize' ) )); ?>
				</h3>
				<h3 class="clc-summary">
					<?php echo wp_kses_post(apply_filters( 'cartimize_payment_method_text', esc_html__( 'Payment', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
				</h3>
			</div>
			<div class="clc-entry">
				<div class="form-container payment-form">
					<div class="cartimize-alert-container cartimize-payment-method" style="display: none;">
			            <ul>
			            	
			            </ul>
					</div>
					<div id="cartimize-payment-options">
						<?php do_action( 'cartimize_payment_methods' ) ?>
					</div>
					<?php do_action( 'cartimize_payment_tab_content_billing_address' ) ?>
					<?php do_action( 'cartimize_coupon_module' ) ?>
					<?php do_action( 'cartimize_payment_tab_nav' ) ?>
				</div>
			</div>
			<div class="clc-summary">
				<?php do_action( 'cartimize_payment_method_summary' ) ?>
			</div>
		</div>
	
	<?php
}

function cartimize_payment_method_summary_html(){
	?>
		<div class="sum-content">
			<div id="payment_method_summary">
				
			</div>
			<a href="javascript:;" data-tab="#cartimize-payment-method" class="cartimize-tab-link"><?php echo wp_kses_post(apply_filters( 'cartimize_edit_text', esc_html__( 'EDIT', 'linear-checkout-for-woo-by-cartimize' ) )); ?></a>
		</div>
	<?php
}

function cartimize_multi_step_indicator_html(){
	$show_customer_info_tab   = apply_filters( 'cartimize_show_customer_information_tab', true );
	$show_shipping_method_tab = cartimize_show_shipping_tab();
	?>
	<ul id="cartimize-breadcrumb" class="etabs">
		<?php if ( $show_customer_info_tab ) : ?>
		<li class="tab" id="default-tab">
			<a href="#cartimize-delivery-info" class="cartimize-small active" id="cartimize-delivery-info-init"></a>
		</li>
		<?php endif; ?>
		<?php if ( $show_shipping_method_tab ) : ?>
		<li class="tab">
			<a href="#cartimize-shipping-method" class="cartimize-small"></a>
		</li>
		<?php endif; ?>
		<li class="tab" <?php echo ( ! $show_customer_info_tab && ! $show_shipping_method_tab ) ? 'id="default-tab"' : ''; ?>>
			<a href="#cartimize-payment-method" class="cartimize-small"></a>
		</li>
		<li class="tab">
			<a href="#cartimize-review-tab" class="cartimize-small"></a>
		</li>
	</ul>
	<?php
}

function cartimize_payment_methods( $available_gateways = false, $object = false, $show_title = true ) {
	cartimize_get_payment_methods( $available_gateways, $object, $show_title, false );
}

function cartimize_payment_tab_content_billing_address() {
	 if ( apply_filters( 'cartimize_show_display_billing_address', true ) ){
		// cartimize_billing_address_radio_group(); 
		cartimize_get_main()->get_template_controller()->get_active_template()->view('form-billing.php');
	 }
	do_action( 'cartimize_checkout_after_payment_tab_billing_address' );
}

function cartimize_coupon_module_html() {
    do_action( 'cartimize_before_coupon_module' );
    ?>
	<?php if ( wc_coupons_enabled() ) : ?>
	    <div id="cartimize-coupons" class="cartimize-module coupon">
	    	<div class="cartimize-alert-container coupon-notice-container" style="display: none;">
	            <ul id="coupon-notice-container">
	            	
	            </ul>
			</div>
	            <div class="form-row discount cartimize-promo-row cartimize-input-wrap-row mb-0">
	            	<?php cartimize_applied_coupon_html(); ?>

	                <div class="discount-apply-form" style="display: none;">
	                	<input type="text" name="cartimize-promo-code-btn" id="cartimize-promo-code" placeholder="<?php echo wp_kses_post(apply_filters( 'cartimize_enter_discount_code_text', esc_html__( "Enter a discount code", 'linear-checkout-for-woo-by-cartimize' ) )); ?>">
	                    <a class="btn" id="cartimize-promo-code-btn"><?php esc_attr_e( 'Apply', 'linear-checkout-for-woo-by-cartimize' ); ?></a>
	                </div>

	            </div>

	        <?php do_action( 'cartimize_coupon_module_end' ); ?>
	    </div>
	<?php endif; ?>
    <?php
	do_action( 'cartimize_after_coupon_module' );
}

function cartimize_applied_coupon_html(){
	?> <div id="cartimize-applied-coupon">
		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<span class="applied-discount-code coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<em><?php wc_cart_totals_coupon_label(sanitize_title( $code ), true); ?></em>
				- <?php cartimize_cart_totals_coupon_html( $coupon ); ?>
			</span>
		<?php endforeach;
	?>
	<div class="parent-coupon-wraper">
			<?php 
			 if(empty( WC()->cart->get_coupons() )): ?>
				<div class="cartimize-add-discount-wrapper">
					<span>+</span><a class="link cartimize-add-discount" tabindex="0" style="text-decoration: none;"> 
					<?php echo esc_html__( "Add a discount code", 'linear-checkout-for-woo-by-cartimize' ); ?></a>
				</div> <?php
			  else: ?>
			  	<div class="cartimize-add-another-discount-wrapper">
					<span>+</span><a class="link cartimize-add-discount" tabindex="0" style="text-decoration: none;"> 
				  	<?php echo esc_html__( "Add another discount code", 'linear-checkout-for-woo-by-cartimize' ); ?></a>
				</div> <?php  
			 endif;
			?>
		
	</div>
	</div>
	<?php
}

function cartimize_applied_coupon_in_summary_html(){
	$cart_coupons = WC()->cart->get_coupons();
	if ( count( $cart_coupons ) > 0 ) {
		?> </br> <div id="cartimize-applied-coupon-summary">
			<span><?php echo esc_html__( "Discount", 'linear-checkout-for-woo-by-cartimize' );  ?>:</span>
			<?php foreach ( $cart_coupons as $code => $coupon ) : ?>
				<span class="applied-discount-code coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
					<em><?php wc_cart_totals_coupon_label(sanitize_title( $code ), true);?></em>
					- <?php cartimize_cart_totals_coupon_html( $coupon, false ); ?>
				</span>
			<?php endforeach;
		?>
		</div>
		<?php
	}
}

/**
 * Get a coupon label.
 *
 * @param string|WC_Coupon $coupon Coupon data or code.
 * @param bool             $echo   Echo or return.
 *
 * @return string
 */
function cartimize_wc_cart_totals_coupon_label( $coupon, $echo = true ) {
	if ( is_string( $coupon ) ) {
		$coupon = new WC_Coupon( $coupon );
	}

	/* translators: %s: coupon code */
	$label = apply_filters( 'woocommerce_cart_totals_coupon_label', sprintf( esc_html__( '%s', 'woocommerce' ), $coupon->get_code() ), $coupon );

	if ( $echo ) {
		echo $label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		return $label;
	}
}

function cartimize_cart_totals_coupon_html( $coupon, $remove_html = true ) {
	if ( is_string( $coupon ) ) {
		$coupon = new WC_Coupon( $coupon );
	}

	$discount_amount_html = '';

	$amount               = WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax );
	$discount_amount_html = '-' . wc_price( $amount );

	if ( $coupon->get_free_shipping() && empty( $amount ) ) {
		$discount_amount_html = __( 'Free shipping coupon', 'woocommerce' );
	}

	$discount_amount_html = apply_filters( 'woocommerce_coupon_discount_amount_html', $discount_amount_html, $coupon );
	$coupon_html          = $discount_amount_html;
	if ( $remove_html ) {
		$coupon_html          = $coupon_html.'<a href="' . esc_url( add_query_arg( 'remove_coupon', rawurlencode( $coupon->get_code() ), defined( 'WOOCOMMERCE_CHECKOUT' ) ? wc_get_checkout_url().'#cartimize-payment-method' : wc_get_cart_url() ) ) . '" class="woocommerce-remove-coupon link" data-coupon="' . esc_attr( $coupon->get_code() ) . '">' . __( 'Remove', 'woocommerce' ) . '</a>';
	}

	echo wp_kses( apply_filters( 'woocommerce_cart_totals_coupon_html', $coupon_html, $coupon, $discount_amount_html ), array_replace_recursive( wp_kses_allowed_html( 'post' ), array( 'a' => array( 'data-coupon' => true ) ) ) ); // phpcs:ignore PHPCompatibility.PHP.NewFunctions.array_replace_recursiveFound
}

function cartimize_cart_summary_content_open_wrap() {
	?>
	<div id="order-summary-container">
	<?php
}

function cartimize_cart_summary_before_order_review() {
	?>
	<div id="cartimize-checkout-before-order-review">
        <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
    </div>
	<?php
}

function cartimize_cart_html() {
	cartimize_cart_summary_content_open_wrap();
	cartimize_cart_summary_before_order_review();
	cartimize_get_cart_html(false);
	cartimize_cart_summary_after_order_review();
	cartimize_totals_html();
	cartimize_close_div();
}

function cartimize_close_div() {
    do_action( 'cartimize_after_cart_summary' );
	?>
	</div>
	<?php
}

function cartimize_cart_summary_after_order_review() {
	?>
	<div id="cartimize-checkout-after-order-review">
        <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
    </div>
	<?php
}

function  cartimize_totals_html() {
	do_action( 'cartimize_before_cart_summary_totals_wrapper' );
	cartimize_get_totals_html(false);
	do_action( 'cartimize_after_cart_summary_totals_wrapper' );
}

function cartimize_payment_tab_nav() {
	do_action( 'woocommerce_review_order_before_submit' );
	?>

	<?php do_action( 'cartimize_above_review_tab_in_payment_step' ); ?>

	<div id="cartimize-payment-and-review-step">
		<?php do_action( 'cartimize_review_tab_in_payment_step' ); ?>
	</div>

	<?php
	do_action( 'cartimize_checkout_after_payment_method_tab_nav' );
}

function cartimize_payment_step_action_html(){
	?>
	<div id="cartimize-payment-method-action">
		<div class="form-row button-row cartimize-step-submit">
			<?php cartimize_continue_to_review_button(); ?>
			<div class="txt-light not-charged-yet" style="padding:10px 0 0;width: 100%;">
	        	<?php echo esc_html__( "You won't be charged yet", 'linear-checkout-for-woo-by-cartimize' ); ?>
	     	</div>
		</div>
	</div>
	<?php
}

function cartimize_review_step_hr(){
	?>
		<hr class="review-step-hr">
	<?php
}

function cartimize_review_tab_html(){
	?>
		<div class="clc-step" id="cartimize-review-tab">
			<?php cartimize_review_tab_inside_html(); ?>
		</div>
	
	<?php
}

function cartimize_review_tab_inside_html(){
	?>
		<div class ="clc-step-heading-wrapper">
			<h3 class="clc-entry-title">
				<?php echo wp_kses_post(apply_filters( 'cartimize_review_tab_text', esc_html__( "Review & Place Order", 'linear-checkout-for-woo-by-cartimize' ) )); ?>
			</h3>
		</div>
		<div class="clc-entry">
			<div class="form-container">
				<div class="cartimize-alert-container cartimize-review-tab" id="cartimize-alert-container-review-step" style="display: none;">
		            <ul>
		            	
		            </ul>
				</div>
				<div class="review-text">
					<?php  
						$shipping_text = '';
						if (cartimize_show_shipping_tab()) {
							$shipping_text = esc_html__(' delivery, shipping and', 'linear-checkout-for-woo-by-cartimize');
						} 
					?>
					<span class="review-details"><?php echo sprintf(/* translators: %s: $shipping_text variable */ esc_html__( 'Please review your %s payment details above and your order summary', 'linear-checkout-for-woo-by-cartimize'), $shipping_text ) ?></span>
					<span class="review-details sm"><?php echo esc_html__( 'below', 'linear-checkout-for-woo-by-cartimize') ?></span>
					<span class="review-details lg"><?php echo esc_html__( 'on the right', 'linear-checkout-for-woo-by-cartimize') ?>.</span><span class="when-ready-text"><?php echo esc_html__( 'When you are ready, click on the Place Order button.', 'linear-checkout-for-woo-by-cartimize') ?></span><br>
				</div>
				<div id="order-summary-review-tab-container" ></div>
				<div class="cartimize-alert-mobile-place-holder" style="display: none;" >
					<div class="cartimize-alert-container cartimize-review-tab" style="display: none;">
			            <ul>
			            	
			            </ul>
					</div>
				</div>
				<div class="">
		          <?php do_action( 'cartimize_checkout_before_payment_method_tab_nav' ); ?>
		          <?php cartimize_terms_and_conditions(); ?>
		          <?php cartimize_place_order(); ?>
		        </div>
			</div>
		</div>
	<?php
}

function cartimize_payment_request_buttons() {
    if ( ! has_action( 'cartimize_payment_request_buttons' ) ) {
    	return;
    }
	?>
	<fieldset class="express-co-cont cartimize-loading">
		<legend><?php esc_html_e('Express checkout', 'linear-checkout-for-woo-by-cartimize' ); ?></legend>
		<div id="cartimize-payment-request-buttons">
			<?php do_action( 'cartimize_payment_request_buttons' ); ?>
		</div>
		<?php do_action( 'cartimize_checkout_add_separator' ); ?>
	</fieldset>
	<?php
}

/**
 * Payment method tab terms and conditions
 */
function cartimize_terms_and_conditions() {
	do_action( 'cartimize_checkout_before_payment_method_terms_checkbox' );

	wc_get_template( 'checkout/terms.php' );
}

function cartimize_content_order_notes_html() {
		
		do_action( 'woocommerce_before_order_notes', WC()->checkout() ); ?>

		<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>

			<div class="order-notes">
				<?php foreach ( WC()->checkout()->get_checkout_fields( 'order' ) as $key => $field ) : 
						$field['is_hidden'] = true;
						cartimize_form_field( $key, $field, WC()->checkout()->get_value( $key ) );
					  endforeach; ?>
			</div>

		<?php endif; ?>

		<?php do_action( 'woocommerce_after_order_notes', WC()->checkout() ); ?>
	<?php
}

function cartimize_branding_html(){
	?>
		<a href="<?php echo esc_url(CARTIMIZE_SITE_URL.CARTIMIZE_HELP_TRACKING) ?>" class="back-link" target='_blank'></a>
	<?php
}

function cartimize_wc_print_notices(){
	$default_notice = wc_print_notices(true);
	if (!empty($default_notice)) : ?>
		<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">
			<?php echo wp_kses_post($default_notice) ?>
		</div>
	<?php endif;
	?>
		<div id="cartimize-alert-placeholder" style="display: none;">
			<li class="cartimize-alert">
				<a class="message"></a>
			</li>
		</div>
		<div class="woocommerce-notices-wrapper">
		</div>
	<?php
}
function cartimize_get_mobile_mini_cart(){
	?>
	<div id="cartimize-mini-cart-container" style="margin-left: auto;">
		<?php echo wp_kses_post(cartimize_get_mobile_mini_cart_html()); ?>
		<div id="order-summary-mini-cart-container"></div>
	</div>
	<?php
}

function cartimize_account_info_html(){
	?>
		<div class="account-info" style="margin-left:auto;">
			<?php if ( ! is_user_logged_in() ) : ?>
				<a href="#cartimize-login-prompt" class="login-prompt-header" rel="modal:open"><?php echo wp_kses_post(apply_filters( 'cartimize_login_text', esc_html__( 'Login', 'linear-checkout-for-woo-by-cartimize' ) )); ?></a>
			<?php else : ?>
				<span>
					<?php echo wp_kses_post(apply_filters( 'cartimize_welcome_back_text', esc_html__( 'Welcome back, ', 'linear-checkout-for-woo-by-cartimize' )));
						do_action( 'cartimize_welcome_back_action' );
					?>.
				</span>
			<?php endif; ?>
		</div>

	<?php
}

function cartimize_woo_error_html(){
	?>
		<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout" style="display: none;">
			<ul class="" role="alert">
				<li></li>
			</ul>
		</div>
	<?php
}

function cartimize_login_modal_html(){
	?>
		<div id="cartimize-login-prompt" class="modal cartimize-modal-prompt">
			  	<div  method="post" action=" " data-parsley-validate data-parsley-focus="first" data-persist="garlic">

			  		<div class="form-container">
						  <h3 style="text-align:center; margin-bottom: 20px; font-size: 20px;"><?php echo wp_kses_post(apply_filters( 'cartimize_login_to_account_text', esc_html__( 'Login to your account', 'linear-checkout-for-woo-by-cartimize' ) )); ?></h3>
						  <div class="cartimize-alert-container" style="text-align:center; display:none;">
						        <ul></ul>
						  </div>
						  <div class="login-fields__field-wrapper">
			  			<div class="form-row form">
			  				<?php cartimize_login_email_label_field(); ?>
	  					</div>
	  					<div class="form-row cartimize-login-password">
							<label tabindex=-1><?php echo esc_html__( 'Password', 'woocommerce' ) ?><abbr class="required" title="required">*</abbr><a  style="color:#222; margin-left: 10px; font-size:12px; margin-top: 2px;" target ="_blank" tabindex=-1 click="lost-password"  href="<?php echo esc_url(wp_lostpassword_url()); ?>" ><?php echo esc_html__('Lost password?', 'linear-checkout-for-woo-by-cartimize') ?></a></label>
							<input type="password" name="cartimize-password" id="cartimize-password" autocomplete="off" data-parsley-trigger="keyup change focusout" data-parsley-required="true"><a class="link password-toggle show-login-password"><?php echo esc_html__('SHOW', 'linear-checkout-for-woo-by-cartimize') ?></a>
							<a class="link password-toggle hide-login-password" style="display: none;"><?php echo esc_html__('HIDE', 'linear-checkout-for-woo-by-cartimize') ?></a>
						</div>
</div>
						<div class="form-row button-row" style="margin-bottom:0;">
							<input type="button" name="cartimize-login-btn" id="cartimize-login-btn" value="<?php echo esc_attr(apply_filters( 'cartimize_login_to_my_account_text', esc_html__( 'Login to my account', 'linear-checkout-for-woo-by-cartimize' ) )); ?>">
						</div>
			  		</div>
			  		
			  	</div>
			</div>
	<?php
}

function cartimize_login_email_label_field(){
	$input_element = '';
	?>
		<label><?php echo wp_kses_post(apply_filters( 'cartimize_login_modal_email_label', esc_html__( 'Email Address', 'woocommerce' ) )); ?><abbr class="required" title="required">*</abbr></label>
	<?php 
		$input_element = apply_filters( 'cartimize_login_modal_email_input', $input_element );
		if ( $input_element == '' ) { ?>
			<input type="email" inputmode="email" autocapitalize="off" autocorrect="off" data-parsley-email="" class="input-text garlic-auto-save" autofocus="" name="login_email" id="login_email" autocomplete="login_email email username" data-parsley-trigger="keyup change focusout" placeholder="" inputmode="email" data-parsley-required="true" data-parsley-validate-starting-pattern="((([a-zA-Z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-zA-Z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-zA-Z]|\d|-|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-zA-Z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-zA-Z]|\d|-|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-zA-Z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))" data-parsley-validation-trimcharacter="_" data-parsley-group="login-form">
		<?php 
		} else {
			echo wp_kses_post($input_element);
		}
}

function cartimize_back_to_cart_html(){
	?>
		<a class="back-to-cart" href="<?php echo esc_url(wc_get_cart_url()); ?>">&larr; <span><?php echo esc_html__( 'Back to Cart', 'linear-checkout-for-woo-by-cartimize' ) ?></span></a>
	<?php
}
function cartimize_cart_error_html(){
	?>

		<main role="main" class="container" id="cartimize">
			
			<div class="cartimize-cart-error" id="cartimize-dummy-info"> 
				<?php
					esc_html_e( 'There are some issues with the items in your cart. Please go back to the cart page and resolve these issues before checking out.', 'woocommerce' ); 
				?>
				<?php do_action( 'woocommerce_cart_has_errors' ); ?>
				</br>

				<div style="margin-top: 10px">
					<a class="button wc-backward" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Return to cart', 'woocommerce' ); ?>
						
					</a>
				</div>  
			</div>
		</main>
	<?php
}

function cartimize_diff_billing_address_extra_parent_no_shipping(){
	?>
		<input type="radio" name="bill_to_different_address" id="billing_same_as_shipping_radio" value="different_from_shipping" class="d-none"  checked="" />
	<?php
}