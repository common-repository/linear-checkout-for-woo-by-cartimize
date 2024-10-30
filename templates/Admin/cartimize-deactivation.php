<style>
        #lcw-deactviate-feedback-cont {
            width: 360px;
            margin: auto;
        }

        #lcw-deactviate-feedback-cont ul,
        #lcw-deactviate-feedback-cont li {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        #lcw-deactviate-feedback-cont ul {
            border: 1px solid #7e8993;
            background-color: white;
        }

        #lcw-deactviate-feedback-cont ul li:not(:last-of-type) {
            border-bottom: 1px solid #7e8993;
        }

        #lcw-deactviate-feedback-cont ul li label {
            display: block;
            position: relative;
            padding: 15px 15px 15px 45px;

        }

        #lcw-deactviate-feedback-cont ul li label:hover,
        #lcw-deactviate-feedback-cont ul li input[type=radio]:checked+label {
            background-color: #fafafa;
        }
        #lcw-deactviate-feedback-cont ul li label span.reason-title {font-size: 15px;}
        #lcw-deactviate-feedback-cont ul li input[type=radio]:checked+label span.reason-title{ font-weight:bold;}

        #lcw-deactviate-feedback-cont ul li input[type=radio]:not(:checked)+label:hover {
            cursor: pointer;
        }

        #lcw-deactviate-feedback-cont ul li label textarea {
            width: 100%;
            padding: 7px;
            font-family: inherit;
            box-sizing: border-box;
        }

        #lcw-deactviate-feedback-cont ul li label .helpTxt {
            font-size: 13px;
            margin: 7px 0 5px;
        }

        #lcw-deactviate-feedback-cont ul li label .helpTxt,
        #lcw-deactviate-feedback-cont ul li label textarea {
            display: none;
        }

        #lcw-deactviate-feedback-cont ul li input[type=radio] {
            position: absolute;
            margin: 17px;
            z-index: 1;
        }

        #lcw-deactviate-feedback-cont ul li input[type=radio]:checked+label .helpTxt,
        #lcw-deactviate-feedback-cont ul li input[type=radio]:checked+label textarea {
            display: block;
        }
        .cartimize-report-dialog { width:auto !important;}
</style>
<div id="cartimize-popu-modal" style="display: none;">
    <div id="lcw-deactviate-feedback-cont">
        <h3 style="text-align: center;line-height: 1.4em; margin-top: 20px;">If you have a moment, can you please share why you are deactivating the LCW
            plugin?</h3>
        <ul>
            <li>
            <input type="radio" class="cartimize-deactivate-radio" name="cartimize-deactivate-radio" value="issue" id="feedback_issue"><label for="feedback_issue">
                <span class="reason-title">I'm facing some issues with the plugin</span>
            <div class="helpTxt">
                <?php
                    $email = $this->plugin_instance->get_license_controller()->get_setting('email');
                    if ( $email == false ) {
                        $current_user = wp_get_current_user();
                        $email = $current_user->user_email;
                    }
                ?>
            I'm sorry about that. I'd really love an opportunity to fix your issue and help you succeed with the plugin. <br><br>Please explain your issue briefly below and we'll get back to you asap at <?php echo esc_html($email); ?></div><textarea class="cartimize-deactivate-message issue"></textarea></label></li>
            <li>
                <input type="radio" class="cartimize-deactivate-radio" name="cartimize-deactivate-radio" value="feature_request" id="feedback_feature_request"><label for="feedback_feature_request"><span class="reason-title">Features I need are
                    missing</span><div class="helpTxt">I'm sorry that the plugin is not there yet. Please let us know what
                        features are missing and we'll do our best to build them for you asap.</div>
                    <textarea class="cartimize-deactivate-message feature_request"></textarea></label></li>
            <li>
                <input type="radio" class="cartimize-deactivate-radio" name="cartimize-deactivate-radio" value="better_plugin" id="feedback_better_plugin"><label for="feedback_better_plugin"><span class="reason-title">I found a better
                    plugin</span><div class="helpTxt">Is it possible to share the plugin name? It will help us understand what
                        we are missing</div><textarea class="cartimize-deactivate-message better_plugin"></textarea></label></li>

            <li>
                <input type="radio" class="cartimize-deactivate-radio" name="cartimize-deactivate-radio" value="temp_deactivation" id="feedback_temp_deactivation"><label for="feedback_temp_deactivation"><span class="reason-title">It's a temporary
                    deactivation</span></label></li>
            <li>
                <input type="radio" class="cartimize-deactivate-radio" name="cartimize-deactivate-radio" value="no_longer_need" id="feedback_no_longer_need" /><label for="feedback_no_longer_need"><span class="reason-title">I no longer need
                    this
                    plugin</span></label></li>
            <li>
                <input type="radio" class="cartimize-deactivate-radio" name="cartimize-deactivate-radio" value="others" id="feedback_others"><label for="feedback_others"><span class="reason-title">Other</span><div
                        class="helpTxt">Can you
                        please give us more details?</div>
                    <textarea class="cartimize-deactivate-message others"></textarea></label></li>
        </ul>
        <label class="cartimize-deactivate-checkbox-label" style="display: inline-block;margin-top: 15px;display: none;">
            <input class="cartimize-deactivate-data-send" type="checkbox" checked>Share a diagnostic <a style="color: unset; text-decoration: underline;" class="cartimize-show-anonymous-data">report</a> to help improve the plugin
        </label>
        <a class="cartimize-see-data-loader" style="margin: 10px 0 5px; display: inline-block;  font-size: 12px; display: none; ">Collecting data for report..</a>
        <div class= "cartimize_deactivation_anonymous_data" style="border: 1px solid rgb(126, 137, 147); border-radius: 5px; padding: 5px; font-family: monospace; font-size: 12px; background-color: rgb(247, 247, 247); max-height: 100px; overflow: auto; margin-top: 10px;display: none;">
        </div>
        <button style="width: 100%;background-color: #003e99; color:#fff; border:0; border-radius: 5px; margin-top:20px; padding:15px 10px;cursor: pointer;" class="cartimize-send-deactivation" class="deactivate" disabled>Submit and Deactivate</button>
        <div style="text-align:center; padding-top: 10px;">
            <a style="font-size:12px; padding: 5px;" href="?cartimize-deactivate=1">I don't want to
                share. Just deactivate it.</a>
        </div>
    </div>
</div>