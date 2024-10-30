<?php 
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

namespace Cartimize\Compatibility;

use Cartimize\Compatibility\Plugins\CartFlows;
use Cartimize\Compatibility\Plugins\WooCore;
use Cartimize\Compatibility\Plugins\WooCommerceOrderDelivery;
use Cartimize\Compatibility\Plugins\WooCommerceSubscriptions;
use Cartimize\Compatibility\Plugins\OxygenBuilder;
use Cartimize\Compatibility\Plugins\CheckoutFieldEditor;
use Cartimize\Compatibility\Plugins\WooCommerceGermanized;
use Cartimize\Compatibility\Plugins\CreativeMail;
use Cartimize\Compatibility\Plugins\ExtraCheckoutFieldsBrazil;
use Cartimize\Compatibility\Plugins\WooCommercePointsandRewards;
use Cartimize\Compatibility\Plugins\WooCommercePriceBasedOnCountry;
use Cartimize\Compatibility\Plugins\WooDeliverySlotsJck;
use Cartimize\Compatibility\Plugins\ElementorPro;
use Cartimize\Compatibility\Plugins\FluentCRMPro;
use Cartimize\Compatibility\Plugins\UltimateMember;
use Cartimize\Compatibility\Plugins\GiftMessageWooCommerce;
use Cartimize\Compatibility\Plugins\YITHWooCommerceCheckoutManager;
use Cartimize\Compatibility\Gateways\PayPalCheckout;
use Cartimize\Compatibility\Gateways\KlarnaPayment;
use Cartimize\Compatibility\Gateways\KlarnaCheckout;
use Cartimize\Compatibility\Gateways\Stripe;
use Cartimize\Compatibility\Gateways\Square;
use Cartimize\Compatibility\Gateways\Braintree;
use Cartimize\Compatibility\Gateways\BraintreeForWooCommerce;
use Cartimize\Compatibility\Gateways\BraintreePaymentGateway;
use Cartimize\Compatibility\Gateways\InpsydePayPalPlus;
use Cartimize\Compatibility\Gateways\PayPalForWooCommerce;
use Cartimize\Compatibility\Gateways\StripeWooCommerce;
use Cartimize\Compatibility\Gateways\PayPalPlusCw;
use Cartimize\Compatibility\Themes\Shoptimizer;
use Cartimize\Compatibility\Themes\OceanWP;
use Cartimize\Compatibility\Themes\Astra;
use Cartimize\Compatibility\Themes\Atelier;
use Cartimize\Compatibility\Themes\Atik;
use Cartimize\Compatibility\Themes\Avada;
use Cartimize\Compatibility\Themes\BeaverBuilder;
use Cartimize\Compatibility\Themes\Blaszok;
use Cartimize\Compatibility\Themes\Divi;
use Cartimize\Compatibility\Themes\Electro;
use Cartimize\Compatibility\Themes\Flevr;
use Cartimize\Compatibility\Themes\GeneratePress;
use Cartimize\Compatibility\Themes\Genesis;
use Cartimize\Compatibility\Themes\Jupiter;
use Cartimize\Compatibility\Themes\JupiterX;
use Cartimize\Compatibility\Themes\Konte;
use Cartimize\Compatibility\Themes\Listable;
use Cartimize\Compatibility\Themes\Optimizer;
use Cartimize\Compatibility\Themes\Porto;
use Cartimize\Compatibility\Themes\Savoy;
use Cartimize\Compatibility\Themes\The7;
use Cartimize\Compatibility\Themes\TMOrganik;
use Cartimize\Compatibility\Themes\Verso;
use Cartimize\Compatibility\Themes\Zidane;
use Cartimize\Compatibility\Themes\WeaverXtreme;
use Cartimize\Compatibility\Themes\PeakShops;
use Cartimize\Compatibility\Themes\Greenmart;
use Cartimize\Compatibility\Themes\Flatsome;
use Cartimize\Compatibility\Themes\Kidz;
use Cartimize\Compatibility\Themes\Themify;
use Cartimize\Compatibility\Themes\TwentyTwentyTwo;
use Cartimize\Compatibility\Themes\TwentyTwentyThree;
use Cartimize\Compatibility\Themes\Woodmart;

class Compatibility{

	public function __construct() {

		new WooCore();
		
		new WooCommerceSubscriptions();
		
		new PayPalCheckout( $this );

		new Stripe();

		new KlarnaPayment();

		new KlarnaCheckout();

		new Square();

		new Braintree();

		new BraintreeForWooCommerce();

		new BraintreePaymentGateway();

		new InpsydePayPalPlus();

		new PayPalForWooCommerce( $this );

		new StripeWooCommerce();

		new PayPalPlusCw();


		//Themes
		new Shoptimizer();

		new OceanWP();

		new Astra();

		new Atelier();

		new Atik();

		new Avada();

		new BeaverBuilder();

		new Blaszok();

		new Divi();

		new Electro();

		new Flevr();

		new GeneratePress();

		new Genesis();

		new Jupiter();

		new JupiterX();

		new Konte();

		new Listable();

		new Optimizer();

		new Porto();

		new Savoy();

		new The7();

		new TMOrganik();

		new Verso();

		new Zidane();

		new WeaverXtreme();

		new PeakShops();
		
		new Greenmart();

		new Flatsome();

		new Kidz();

		new Themify();

		new Woodmart();

		// Plugins
		
		new CartFlows();

		new WooCommerceOrderDelivery();

		new OxygenBuilder();

		new CheckoutFieldEditor();

		new WooCommerceGermanized();

		new CreativeMail();

		new ExtraCheckoutFieldsBrazil();

		new WooCommercePointsandRewards();

		new WooCommercePriceBasedOnCountry();

		new WooDeliverySlotsJck();

		new ElementorPro();

		new FluentCRMPro();

		new UltimateMember();

		new GiftMessageWooCommerce();

		new TwentyTwentyTwo();

		new TwentyTwentyThree();

		new YITHWooCommerceCheckoutManager();
	}
}