<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! WC()->checkout()->is_registration_enabled() && WC()->checkout()->is_registration_required() && ! is_user_logged_in() ) { ?>
	<main role="main" class="container" id="cartimize">
		<?php do_action('cartimize_checkout_main_container_start'); ?>
		<ul id="cartimize-breadcrumb" class="etabs">
			<li class="tab" id="default-tab">
				<a href="#cartimize-dummy-info" class="cartimize-small active" id="cartimize-dummy-info-init"></a>
			</li>
		</ul>
	<div class="cartimize-login-must" id="cartimize-dummy-info"> <?php
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	?>
	</br><div style="margin-top: 10px"><a href="#cartimize-login-prompt" rel="modal:open">
				<?php echo esc_html__(apply_filters( 'cartimize_login_to_account_text', __( 'Login to your account', 'linear-checkout-for-woo-by-cartimize' ) )); ?></a>&nbsp; <?php do_action( 'cartimize_login_modal_html' ) ?></div>
	 </div>  </main> <?php
	return;
}

?>
<?php do_action('cartimize_checkout_main_container_before'); ?>
<main role="main" class="container" id="cartimize">
	<?php do_action('cartimize_checkout_main_container_start'); ?>
	<?php do_action('cartimize_multi_step_indicator')  ?>
	<?php if ( ! apply_filters( 'cartimize_replace_form', false ) ) : ?>
		<?php do_action( 'woocommerce_before_checkout_form', WC()->checkout() ); ?>
		<form <?php cartimize_form_attributes( false, false ); ?>>
			<div class="clc-cols">
					<div class="col1 cartimize-loading" id="customer_details">
						<?php do_action('cartimize_main_container_sub_header')  ?>
						<?php do_action('cartimize_checkout_customer_info_tab')  ?>
						<?php do_action('cartimize_login_content')  ?>
						<div class="clc-steps" <?php if (  is_user_logged_in() ) { echo "logged-in"; } ?> >
							<?php do_action('cartimize_shipping_address')  ?>
							<?php do_action('cartimize_shipping_methods')  ?>
							<?php do_action('cartimize_payment_methods_html')  ?>
							<?php do_action('cartimize_review_tab')  ?>
						</div>
					</div>
					<div class="col2 cartimize-init-loader">
						<div id="order-summary-main-container">
							<?php do_action( 'cartimize_checkout_cart_summary' ); ?>
						</div>
					</div>
			</div>
		</form>
	<?php else : ?>
	    <?php do_action( 'cartimize_checkout_form' ); ?>
	<?php endif; ?>
	<?php do_action( 'cartimize_checkout_main_container_end' ); ?>
	<?php do_action( 'woocommerce_after_checkout_form', WC()->checkout() ); ?>
</main>
<?php do_action( 'cartimize_checkout_after_main_container', WC()->checkout() ); ?>