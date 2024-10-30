<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined('ABSPATH')){ exit; }

$cartimize_lang = array();

$cartimize_lang['invalid_request'] = esc_html__('Invalid request.', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['invalid_response'] = esc_html__('Invalid response. Please try again.', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['invalid_response_empty'] = esc_html__('Empty response received. Please try again.', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['invalid_response_format'] = esc_html__('Invalid response format. Please try again.', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['invalid_response_json_failed'] = esc_html__('Invalid response json failed. Please try again.', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['http_error'] = esc_html__('HTTP Error.', 'linear-checkout-for-woo-by-cartimize' );

//service API messages
$cartimize_lang['service__invalid_response'] = esc_html__('Invalid response. Please try again.', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['service__invalid_request'] = esc_html__('Invalid request. Please try again.', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['service__login_error'] = esc_html__('Email or password is incorrect.', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['service__expired'] = sprintf( // translators: 1: opening a tag  2: closing a tag
esc_html__('Your subscribed plan has expired. Please  %1$s renew your license %2$s', 'linear-checkout-for-woo-by-cartimize' ), '<a href="'.CARTIMIZE_MY_ACCOUNT_URL.'" target="_blank">', '</a>');
$cartimize_lang['service__limit_reached'] = sprintf( // translators: 1: opening a tag  2: closing a tag
esc_html__('You have reached the sites limit for your plan. Please  %1$s Upgrade your plan %2$s', 'linear-checkout-for-woo-by-cartimize' ), '<a href="'.CARTIMIZE_MY_ACCOUNT_URL.'" target="_blank">', '</a>');
$cartimize_lang['service__invalid_license_info'] = sprintf( // translators: 1: opening a tag  2: closing a tag
esc_html__('Invalid license information. Please %1$s contact support %2$s', 'linear-checkout-for-woo-by-cartimize' ), '<a href="mailto:help@cartimize.com?Subject=Invalid%20license%20information" target="_blank">', '</a>');
$cartimize_lang['service__server_issue'] = sprintf( // translators: 1: opening a tag  2: closing a tag
esc_html__('Temporary server issue. Please try again. If issue persists, %1$s contact support %2$s', 'linear-checkout-for-woo-by-cartimize' ), '<a href="mailto:help@cartimize.com?Subject=Invalid%20license%20information" target="_blank">', '</a>');
$cartimize_lang['service__invalid_token'] = sprintf( // translators: 1: opening a tag  2: closing a tag
esc_html__('Invalid token received. Please try again. If issue persists, %1$s contact support %2$s', 'linear-checkout-for-woo-by-cartimize' ), '<a href="mailto:help@cartimize.com?Subject=Invalid%20token%20received" target="_blank">', '</a>');

$cartimize_lang['service__existing_user_login'] = esc_html__('Sorry, that email already exists! Please login to your account', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['service__existing_user_email'] = esc_html__('Sorry, that email already exists! Please login to your account', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['service__already_have_plan'] = esc_html__('A pro plan is active for this account.', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['service__already_have_plan'] = esc_html__('A pro plan is active for this account.', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['service__trial_plan_site_count_reached'] = esc_html__('Your trial is active on another site. Please upgrade to Pro to start using Pro features.', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['service__invalid_responsetrial_plan_dev_site_count_reached'] = esc_html__('Dev site limit during active trial has been reached.', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['service__trial_plan_dev_site_count_reached'] = esc_html__('Dev site limit during active trial has been reached.', 'linear-checkout-for-woo-by-cartimize' );
$cartimize_lang['service__trial_plan_and_expired'] = esc_html__('Your 14-day trial is over.', 'linear-checkout-for-woo-by-cartimize' );
