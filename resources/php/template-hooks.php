<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

add_action( 'cartimize_header_content', 'cartimize_get_mobile_mini_cart' );
add_action( 'cartimize_welcome_back_action', 'cartimize_welcome_back_action_html' );
add_action( 'cartimize_checkout_main_container_start', 'cartimize_wc_print_notices', 10 );
add_action( 'cartimize_main_container_sub_header', 'cartimize_main_container_sub_header_print', 10);
add_action( 'cartimize_inside_sub_header', 'cartimize_back_to_cart_html', 10);
add_action( 'cartimize_login_content', 'cartimize_login_content_print', 10);
add_action( 'cartimize_login_modal_html', 'cartimize_login_modal_html', 10);
add_action( 'cartimize_multi_step_indicator', 'cartimize_multi_step_indicator_html', 10);

add_action( 'cartimize_checkout_customer_info_tab', 'cartimize_payment_request_buttons', 10);
add_action( 'cartimize_shipping_address', 'cartimize_shipping_step');
add_action( 'cartimize_email_and_create_account', 'cartimize_email_and_create_account_print');
add_action( 'cartimize_shipping_nav_button', 'cartimize_shipping_nav_button_html');
add_action( 'cartimize_shipping_deatils_summary', 'cartimize_shipping_deatils_summary_html');

add_action( 'cartimize_shipping_methods', 'cartimize_shipping_methods_html');
add_action( 'cartimize_shipping_methods_nav_button', 'cartimize_shipping_method_nav_button_html');
add_action( 'cartimize_shipping_method_summary', 'cartimize_shipping_method_summary_html');

add_action( 'cartimize_payment_methods_html', 'cartimize_payment_methods_html');
add_action( 'cartimize_payment_methods', 'cartimize_payment_methods');
add_action( 'cartimize_payment_tab_content_billing_address', 'cartimize_payment_tab_content_billing_address');
add_action( 'cartimize_before_cart_summary_totals_wrapper', 'cartimize_coupon_module_html');
add_action( 'cartimize_payment_method_summary', 'cartimize_payment_method_summary_html');

add_action( 'cartimize_checkout_cart_summary', 'cartimize_cart_html', 40 );
add_action( 'cartimize_content_order_notes', 'cartimize_content_order_notes_html', 30 );
add_action( 'cartimize_payment_tab_nav', 'cartimize_payment_tab_nav', 100 ); // Div close

add_action( 'cartimize_inside_subtotal_detail',  'cartimize_order_total_html_inside_subtotal' );

add_action( 'cartimize_after_footer', 'cartimize_branding_html' );

add_filter( 'cartimize_shipping_fields', 'cartimize_shipping_fields_remove_placeholder', 10, 2 );
add_filter( 'cartimize_billing_fields', 'cartimize_billing_fields_remove_placeholder', 10, 2 );
add_filter( 'cartimize_failed_login_error_message', 'cartimize_failed_login_error_message_html', 10, 2 );
add_action( 'cartimize_diff_billing_address_extra_parent', 'cartimize_diff_billing_address_extra_parent_no_shipping', 3, 1);

// add_filter("template", "cartimize_oxygen_template_name");
// add_filter("validate_current_theme", "__return_false");