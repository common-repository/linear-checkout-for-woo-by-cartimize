<?php
/**
 * Linear Checkout for WooCommerce by Cartimize
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined('ABSPATH')){ exit; }

   if( $this->get_site_token() == false ){
      wp_redirect(CARTIMIZE_LOGIN_PAGE_URL_MUST_SIGNUP);
   }
?>
<div class="wrap">
<h1 class="wp-heading-inline">
Linear Checkout for WooCommerce - Pro</h1>
<div id="cartimize-pro-onboarding-page" class="cartimize-admin-loading">
   <div id="tabs" style="margin-top:10px; max-width:1220px;">
      <ul>
         <li><a href="#tabs-1">Live cart editing in checkout page</a></li>
         <li><a href="#tabs-2">Google Address auto-complete</a></li>
         <li><a href="#tabs-3">Saved Addresses for logged-in users</a></li>
         <li><a href="#tabs-4">A better ‘Thank you’ page</a></li>
         <li><a href="#tabs-5">Delayed account creation</a></li>
         <li><a href="#tabs-6">Hide or make billing address optional for virtual products</a></li>
         <li><a href="#tabs-7">Remove Cartimize branding</a></li>
         <li><a href="#tabs-8">Checkout form customizer</a></li>
         <li><a href="#tabs-9">Cart order bump</a></li>
         <li><a href="#tabs-10">Auto-Fill City and State from ZIP</a></li>
         <li><a href="#tabs-11">Link-based account creation and login</a></li>
         <li><a href="#tabs-12">Display positive reviews of products in cart</a></li>
         <li><a href="#tabs-13">Login with social profiles</a></li>
      </ul>
      <div id="tabs-1">
         <div class="d-flex">
            <div class="feature-desc">
               <h2>Live cart editing in checkout page</h2>
               <p>Allow your customers to change quantities, remove and re-add products directly on the checkout
                  page without any page
                  reloads. Bid goodbye to the hideous default WooCommerce cart page.
               </p>
            </div>
            <div class="feature-img"><img src="<?php echo esc_url(CARTIMIZE_PLUGIN_URL.'assets/images/cart-editing.gif'); ?>"></div>
         </div>
      </div>

      <div id="tabs-2">
         <div class="d-flex">
            <div class="feature-desc">
               <h2>Google Address autocomplete</h2>
               <p>By empowering your customers to autocomplete their address, you can -
               <ul>
                  <li>Reduce address entry errors</li>
                  <li>Decrease the number of steps in the checkout process</li>
                  <li>Simplify the address entry experience on mobile devices</li>
                  <li>Significantly reduce total time required for a customer to place an order</li>
                  <li>Decrease the length of the check-out process by up to 64%</li>
                  <li>Reduce cart abandonment by 3-5%</li>
                  <li>Reduce post-order delivery headaches</li>
               </ul>
               </p>
            </div>
            <div class="feature-img"><img src="<?php echo esc_url(CARTIMIZE_PLUGIN_URL.'assets/images/google-autocomplete02.gif'); ?>"></div>
         </div>
      </div>

      <div id="tabs-3">
         <div class="d-flex">
            <div class="feature-desc">
               <h2>Saved Addresses for logged-in users</h2>
               <p>80% of people use the same address most of the time. Logged-in users can add multiple shipping
                  and billing addresses during checkout, or pick from
                  their previously saved addresses for both delivery and billing.<br><br>
                  Your customers can choose a default address
                  for shipping and billing, and it will automatically be selected during checkout.
               </p>
            </div>
            <div class="feature-img"><img src="<?php echo esc_url(CARTIMIZE_PLUGIN_URL.'assets/images/saved-addresses03.gif'); ?>"></div>
         </div>
      </div>

      <div id="tabs-4">
         <div class="d-flex">
            <div class="feature-desc">
               <h2>A better Order confirmation (Thank you) page</h2>
               <p>The order confirmation (thank you) page is the final leg in the checkout process. The first
                  job of the confirmation step is to let all users know that their order has been successfully
                  completed. It’s important that this information is communicated clearly to users to avoid them
                  looking for further tasks that have to be completed before the order is placed. When this isn’t
                  communicated clearly, users would wonder if they needed to do something else to finalize the
                  order.<br><br>
                  Another key aspect of the confirmation step is to clearly prioritize the information, making the
                  order confirmation information sufficiently prominent. This includes not collapsing all the
                  order information or plastering the page with auxiliary options that are much more dominant than
                  the actual order information.
               </p>
            </div>
            <div class="feature-img"><img src="<?php echo esc_url(CARTIMIZE_PLUGIN_URL.'assets/images/better-thank-you-page.jpg'); ?>"></div>
         </div>
      </div>

      <div id="tabs-5">
         <div class="d-flex">
            <div class="feature-desc">
               <h2>Delayed account creation</h2>
               <p>While users should always be allowed to complete the checkout process as a guest, having an optional account creation option is often appreciated by users. This begs the question of at which point during the checkout flow is it best to ask for the optional account creation.<br><br>
Based on previous checkout usability tests, it’s clear that delaying the optional account creation option performs the best. In fact, it’s often best delayed until the order is finalized; hence presenting the optional account creation option on the order confirmation page works best. This concept is called ‘Delayed Account Creation’.
               </p>
            </div>
            <div class="feature-img"><img src="<?php echo esc_url(CARTIMIZE_PLUGIN_URL.'assets/images/delayed-account-creation.jpg'); ?>"></div>
         </div>
      </div>

      <div id="tabs-6">
         <div class="d-flex">
            <div class="feature-desc">
               <h2>Hide or make billing address optional for virtual and downloadable products</h2>
               <p>For stores that do not need to get a billing address from their users, it is best to hide the form altogether or make it optional.
               </p>
            </div>
            <div class="feature-img"><img src="<?php echo esc_url(CARTIMIZE_PLUGIN_URL.'assets/images/disable-make-optional-billing-address.jpg'); ?>"></div>
         </div>
      </div>


      <div id="tabs-7">
         <div class="d-flex">
            <div class="feature-desc">
               <h2>Remove Cartimize branding</h2>
               <p>Remove the 'Checkout Powered by Cartimize' branding badge and make the checkout your own.</p>
            </div>
            <div class="feature-img"><img src="<?php echo esc_url(CARTIMIZE_PLUGIN_URL.'assets/images/remove-cartimize-branding.jpg'); ?>"></div>
         </div>
      </div>



      <div class="section-cta">
         <div style="text-align: center;">
            <?php echo wp_kses_post($this->button_html('onboard'));?>
         </div>
         <a class="skip-action" href="<?php echo esc_url(CARTIMIZE_SETTINGS_PAGE_URL); ?>">I'LL DO THIS LATER</a>
      </div>
   </div>
</div>
</div>