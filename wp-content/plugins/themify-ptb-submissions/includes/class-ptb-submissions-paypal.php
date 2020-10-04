<?php

class PTB_Submission_PayPal extends PTB_Submission_Payment {

    public function __construct($classname, $pluginname, $version) {
        parent::__construct($classname, $pluginname, $version);
    }

    public function form($post_type, $post_id, array $args, $success_page, $cancel_page) {
        $options = get_option($this->plugin_name . '-settings');
        if (!isset($options['paypal-email']) || !is_email($options['paypal-email'])) {
            return false;
        }
        $user = is_user_logged_in() ? wp_get_current_user() : false;
        $paypal_url = isset($options['paypal-sandbox']) && $options['paypal-sandbox'] ? '//www.sandbox.paypal.com/cgi-bin/webscr' : '//www.paypal.com/cgi-bin/webscr';
        $amount = floatval($args['amount']);
        $logo = get_header_image();
        $logo = apply_filters('ptb_submission_paypal_logo', $logo);
        ?>
        <form style="display:none;" name="ptb-paypal-form"  action="<?php echo esc_url($paypal_url); ?>" method="post">
            <input type="hidden" name="business" value="<?php echo $options['paypal-email']; ?>" />
            <input type="hidden" name="cmd" value="_xclick" />
            <input TYPE="hidden" name="charset" value="utf-8" />
            <input type="hidden" name="item_name" value="<?php _e('PTB Submission Fee', 'ptb-submission') ?>" />
            <input type="hidden" name="item_number" value="<?php echo $post_id ?>" />
        <?php if ($user): ?>
                <input type="hidden" name="userid" value="<?php echo $user->ID ?>" />
                <input type="hidden" name="email" value="<?php echo $user->user_email ?>" />
                <input type="hidden" name="first_name" value="<?php echo $user->user_firstname ?>" />
                <input type="hidden" name="last_name" value="<?php echo $user->user_lastname ?>" />
            <?php do_action('ptb_submission_paypal_user_fields', $post_id, $args); ?>
        <?php endif; ?>
            <?php if ($logo): ?>
                <input type="hidden" name="image_url" value="<?php echo $logo ?>" />
            <?php endif; ?>
            <input type="hidden" name="cbt" value="<?php esc_attr_e(get_bloginfo('name')) ?>" />
            <input type="hidden" name="amount" value="<?php echo $amount ?>"/>
            <input type="hidden" name="no_shipping" value="1"/>
            <input type="hidden" name="currency_code" value="<?php echo $options['currency'] ?>"/>
            <input type="hidden" name="cancel_return" value="<?php echo $cancel_page ?>"/>
            <input type="hidden" name="return" value="<?php echo $success_page ?>"/>
            <input type="hidden" name="notify_url" value="<?php echo admin_url('admin-ajax.php?action=ptb_submission_payment_result') ?>"/>
        </form>
        <script type="text/javascript">
            window.onload = function () {
                document.forms['ptb-paypal-form'].submit();
            };
        </script>
        <?php
    }

    private function sandbox($post_id, array $post_data, array $data) {
        if (function_exists('fsockopen')) {
            $req = 'cmd=_notify-validate';
            $get_magic_quotes_exists = function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() == 1;
            foreach ($post_data as $key => $value) {
                $value = $get_magic_quotes_exists ? urlencode(stripslashes($value)) : urlencode($value);
                $req.= "&$key=$value";
            }
            // post back to PayPal system to validate
            $status = isset($data['approve']) && $data['approve'] ? 'publish' : 'pending';
            $header.= "POST /cgi-bin/webscr HTTP/1.0\r\n";
            $header.= "Host: www.sandbox.paypal.com\r\n";
            $header.= "Content-Type: application/x-www-form-urlencoded\r\n";
            $header.= "Content-Length: " . strlen($req) . "\r\n\r\n";
            $fp = fsockopen('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
            if ($fp) {
                fputs($fp, $header . $req);
                while (!feof($fp)) {
                    $res = fgets($fp, 1024);
                    if (strcmp($res, "VERIFIED") == 0) {
                        fclose($fp);
                        return wp_update_post(array('ID' => $post_id, 'post_status' => $status));
                    } else if (strcmp($res, "INVALID") == 0) {
                        fclose($fp);
                        return false;
                    }
                }
                fclose($fp);
            }
        }
        return false;
    }

    private function live($post_id, array $post_data, array $data) {
        /*
          $plugin_url = plugin_dir_path( __FILE__ ).'logs/';
          $myfile = fopen($plugin_url."logs.txt", "a") or die("Unable to open file!");
          fwrite($myfile,'live');
         */
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $post = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2)
                $post[$keyval[0]] = $keyval[1];
        }
        // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $post['cmd'] = '_notify-validate';
        $response = wp_remote_post('https://www.paypal.com/cgi-bin/webscr', array(
            'method' => 'POST',
            'body' => $post,
            'httpversion' => '1.0',
            'sslverify' => false
        ));
        if (!is_wp_error($response)) {
            // inspect IPN validation result and act accordingly
            if (strcmp($response['body'], "VERIFIED") == 0) {
                $status = isset($data['approve']) && $data['approve'] ? 'publish' : 'pending';
                return wp_update_post(array('ID' => $post_id, 'post_status' => $status));
            } else {
                //fwrite($myfile,$response['body']);
                return FALSE;
            }
        } else {
            /*
              fwrite($myfile,'error');
              fwrite($myfile,$response->get_error_message());
             * 
             */
        }
        //fclose($myfile);
        return FALSE;
    }

    public function result(array $post_data) {

        if (isset($post_data['item_number'])) {
            $post_id = intval($post_data['item_number']);
            $post = get_post($post_id);
            if ($post && $post->post_status == 'draft') {
                $post_type = $post->post_type;
                $options = PTB_Submission::get_option();
                $template = $options->get_submission_template($post_type);
                if (!$template || !isset($template['frontend']) || !isset($template['frontend']['data'])) {
                    global $wp_query;
                    $wp_query->set_404();
                    status_header(404);
                    wp_die();
                }
                $data = $template['frontend']['data'];
                $options = get_option($this->plugin_name . '-settings');
                if (isset($post_data['receiver_email']) && $options['paypal-email'] == $post_data['receiver_email'] && floatval($data['amount']) <= floatval($post_data['mc_gross'])) {
                    if (isset($options['paypal-sandbox']) && $options['paypal-sandbox']) {
                        return $this->sandbox($post_id, $post_data, $data);
                    } else {
                        return $this->live($post_id, $post_data, $data);
                    }
                }
            }
        }
    }

    public function success(array $post_data, array $data, $post = false) {
        $lng = PTB_Utils::get_current_language_code();
        ?>
        <div class="ptb_submission_payment_success ptb_submission_paypal_success"><?php echo esc_textarea($data['m'][$lng]) ?></div>
        <?php
    }

    public function fail(array $post_data, array $data, $post = false) {
        ?>
        <div class="ptb_submission_payment_fail ptb_submission_paypal_fail"><?php _e('You refuse payment', 'ptb-submission') ?></div>
        <?php
    }

}
