<?php

defined( 'ABSPATH' ) || exit;

do_action( 'cartimize_checkout_before_billing_address' );
$ship_to_different_address = WC()->session->get( 'ship_to_different_address');

?> <div class="cartimize-billing-form-container <?php if( apply_filters( 'cartimize_hide_billing_address', false ) ) echo 'toggle'; ?>" > <?php
	if ( ! apply_filters( 'cartimize_force_display_billing_address', false ) && WC()->cart->needs_shipping_address() ) :
		?>
		<div class="form-row-header new-address">
			<?php echo wp_kses_post(apply_filters( 'cartimize_billing_address_heading', esc_html__( 'Billing address', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
		</div>
		<div id="cartimize-shipping-same-billing" class="form-row cartimize-module cartimize-accordion">
			<div class="custom-radio ul cartimize-radio-reveal-group inline">
				<div class="cartimize-radio-reveal-li cartimize-no-reveal li">
					<input type="radio" name="bill_to_different_address" id="billing_same_as_shipping_radio" value="same_as_shipping" class="garlic-auto-save" checked=""/>
					<label class="cartimize-radio-reveal-label" for="billing_same_as_shipping_radio">
						<span class="cartimize-radio-reveal-title"><?php esc_html_e( 'Same as Delivery Address', 'linear-checkout-for-woo-by-cartimize' ); ?></span>
					</label>
					<?php do_action( 'cartimize_after_same_as_shipping_address_label' ); ?>
				</div>
				<?php do_action( 'cartimize_same_as_shipping_address_extra',  $ship_to_different_address); ?>
				<div class="cartimize-radio-reveal-li li">
					<input type="radio" <?php checked( apply_filters( 'cartimize_ship_to_different_address_checked', $ship_to_different_address == 1 ? 1 : 0 ), 1 ); ?> name="bill_to_different_address" id="shipping_dif_from_billing_radio" value="different_from_shipping" class="garlic-auto-save" />
					<label class="cartimize-radio-reveal-label" for="shipping_dif_from_billing_radio">
						<span class="cartimize-radio-reveal-title"><?php esc_html_e( 'Enter a different address', 'linear-checkout-for-woo-by-cartimize' ); ?></span>
					</label>
				</div>
				<?php do_action( 'cartimize_diff_billing_address_extra',  $ship_to_different_address); ?>
			</div>
		</div>
        <?php do_action( 'cartimize_before_billing_address_form_fields' ) ?>
        <div id="cartimize-billing-fields-container" class="cartimize-radio-reveal-content address-form billing woocommerce-billing-fields d-none">
            <?php do_action( 'cartimize_start_billing_address_form_fields' ) ?>
            <div class="woocommerce-billing-fields__field-wrapper">
                <?php do_action( 'cartimize_before_reveal_billing_fields' ); ?>
                <?php cartimize_get_billing_checkout_fields( WC()->checkout() ); ?>
            </div>
            <?php do_action( 'cartimize_end_billing_address_form_fields' ) ?>
        </div>
        <?php do_action( 'cartimize_after_billing_address_form_fields' ) ?>
	<?php elseif(  WC()->cart->needs_shipping() && wc_ship_to_billing_address_only() ) : ?>
	<?php else : ?>
			<div class="form-row-header new-address">
				<?php echo wp_kses_post(apply_filters( 'cartimize_billing_address_heading', esc_html__( 'Billing address', 'linear-checkout-for-woo-by-cartimize' ) )); ?>
			</div>
		<?php do_action( 'cartimize_diff_billing_address_extra_parent',  $ship_to_different_address); ?>
		<?php do_action( 'cartimize_before_billing_address_form_fields' ) ?>
		<div id="cartimize-billing-fields-container" class="address-form woocommerce-billing-fields billing" >
            <?php do_action( 'cartimize_start_billing_address_form_fields' ) ?>
            <div class="woocommerce-billing-fields__field-wrapper">
                <?php cartimize_get_billing_checkout_fields( WC()->checkout() ); ?>
            </div>
            <?php do_action( 'cartimize_end_shipping_address_form_fields' ) ?>
		</div>
        <?php do_action( 'cartimize_after_billing_address_form_fields' ) ?>
	<?php endif; ?>

    <div id="ship-to-different-address" class="cartimize-force-hidden">
	    <input id="ship-to-different-address-checkbox" type="checkbox" name="ship_to_different_address" value="<?php echo WC()->cart->needs_shipping_address() ? 1 : 0; ?>" checked="" />
    </div>

</div>

	<?php
	do_action( 'cartimize_checkout_after_billing_address' );
