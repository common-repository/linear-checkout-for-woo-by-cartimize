<?php  do_action('woocommerce_before_checkout_shipping_form', WC()->checkout() ); ?> 
<div class="clc-step filling" id="cartimize-delivery-info">
	<div class ="clc-step-heading-wrapper">
		<?php if ( ! WC()->cart->needs_shipping() ) : ?>
			<h3 class="clc-entry-title">
			<?php echo wp_kses_post(apply_filters( 'cartimize_contact_info_heading', esc_html__( 'Contact Info', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
			</h3>
			<h3 class="clc-summary">
				<?php echo wp_kses_post( apply_filters( 'cartimize_contact_info_heading_summary', esc_html__( 'Contact Info', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
			</h3>
		<?php else : ?>
			<h3 class="clc-entry-title">
			<?php if (!is_user_logged_in()): ?>
					<?php if ( WC()->cart->needs_shipping() && !wc_ship_to_billing_address_only()) : ?>
						<?php echo wp_kses_post(apply_filters( 'cartimize_contact_info_shipping_address_heading', esc_html__( 'Contact Info & Delivery address', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
					<?php elseif ( WC()->cart->needs_shipping() && wc_ship_to_billing_address_only()) : ?>
						<?php echo wp_kses_post(apply_filters( 'cartimize_contact_info_shipping_address_heading', esc_html__( 'Contact Info, Delivery & Billing addresses', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
					<?php endif; ?>
			<?php else: ?>
					<?php if ( WC()->cart->needs_shipping() && !wc_ship_to_billing_address_only()) : ?>
						<?php echo wp_kses_post(apply_filters( 'cartimize_contact_info_shipping_address_heading', esc_html__( 'Delivery address', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
					<?php elseif ( WC()->cart->needs_shipping() && wc_ship_to_billing_address_only()) : ?>
						<?php echo wp_kses_post(apply_filters( 'cartimize_contact_info_shipping_address_heading', esc_html__( 'Delivery & Billing address', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
					<?php endif; ?>
			<?php endif; ?>
			</h3>
			<h3 class="clc-summary">
				<?php if ( WC()->cart->needs_shipping() && !wc_ship_to_billing_address_only()) : ?>
					<?php echo wp_kses_post(apply_filters( 'cartimize_contact_info_shipping_address_heading_summary', esc_html__( 'Delivery', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
				<?php elseif ( WC()->cart->needs_shipping() && wc_ship_to_billing_address_only()) : ?>
					<?php echo wp_kses_post(apply_filters( 'cartimize_contact_info_shipping_address_heading_summary', esc_html__( 'Delivery & Billing', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
				<?php endif; ?>
			</h3>
		<?php endif; ?>
	</div>
		<div class="clc-entry">
			<div class="form-container">
				<div class="cartimize-alert-container cartimize-delivery-info" style="display: none;">
					<ul>
						
					</ul>
				</div>
				<?php do_action( 'cartimize_email_and_create_account' ) ?>
				<?php if ( WC()->cart->needs_shipping() && !wc_ship_to_billing_address_only()) : ?>
					<?php do_action( 'cartimize_before_shipping_address_form_fields' ) ?>
					<div class="address-form woocommerce-shipping-fields shipping">
						<?php do_action( 'cartimize_start_shipping_address_form_fields' ) ?>
						<div class="form-row-header new-address">
							<?php echo wp_kses_post(apply_filters( 'cartimize_enter_delivery_address_text', esc_html__( 'ENTER YOUR DELIVERY ADDRESS', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
						</div>
						<div class="woocommerce-shipping-fields__field-wrapper">
							<?php cartimize_get_shipping_checkout_fields( WC()->checkout() ); ?>
						</div>
						<?php do_action( 'cartimize_end_shipping_address_form_fields' ) ?>
					</div>
					<?php do_action( 'cartimize_after_shipping_address_form_fields' ) ?>
				<?php elseif ( WC()->cart->needs_shipping() && wc_ship_to_billing_address_only()) : ?>
					<?php do_action( 'cartimize_before_billing_address_form_fields', true ) ?>
					<div class="address-form woocommerce-billing-fields">
						<?php do_action( 'cartimize_start_billing_address_form_fields' ) ?>
						<div class="form-row-header">
							<?php echo wp_kses_post(apply_filters( 'cartimize_enter_delivery_address_text', esc_html__( 'ENTER YOUR DELIVERY & BILLING ADDRESSES', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
						</div>
						<div class="woocommerce-billing-fields__field-wrapper">
							<?php cartimize_get_billing_checkout_fields( WC()->checkout() ); ?>
						</div>
						<?php do_action( 'cartimize_end_billing_address_form_fields' ) ?>
					</div>
					<?php do_action( 'cartimize_after_billing_address_form_fields' ) ?>
				<?php endif; ?>
				<?php do_action( 'cartimize_shipping_nav_button' ) ?>
			</div>
		</div>
		<div class="clc-summary">
			<?php do_action( 'cartimize_shipping_deatils_summary' ) ?>
		</div>
	</div>
<?php do_action('woocommerce_after_checkout_shipping_form', WC()->checkout() ); ?>