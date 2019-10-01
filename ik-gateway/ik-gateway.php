<?php
/*
Plugin Name: InterKassa Gateway
Description: Платежный шлюз "Интеркасса" для сайтов на WordPress. (версия Интеркассы 2.0)
Version: 1.10
Last Update: 25.06.2019
Author: Interkassa
Author URI: http://www.interkassa.com
*/
if (!defined('ABSPATH')) exit;

add_action('plugins_loaded', 'ik_init', 0);

function ik_init()
{
    if (!class_exists('WC_Payment_Gateway')) return;

    class WC_Gateway_Interkassa extends WC_Payment_Gateway
    {
        const ikUrlSCI = 'https://sci.interkassa.com/';
        const ikUrlAPI = 'https://api.interkassa.com/v1/';

        public function __construct()
        {
            global $woocommerce;

            $plugin_dir = basename(dirname(__FILE__));
            load_plugin_textdomain('interkassa', false, $plugin_dir);

            $this->id = 'interkassa';
            $this->has_fields = false;
            $this->method_title = __('Интеркасса 2.0', 'interkassa');
            $this->method_description = __('Интеркасса 2.0', 'interkassa');
            $this->init_form_fields();
            $this->init_settings();

            $this->icon = apply_filters('woocommerce_interkassa_icon', plugin_dir_url(__FILE__) . 'images/logo_interkassa.png');

            $this->title = $this->get_option('title');
            $this->test_mode = ($this->get_option('test_mode') == 'yes') ? 1 : 0;
            $this->test_key = $this->get_option('test_key');
            $this->description = $this->get_option('description');
            $this->merchant_id = $this->get_option('merchant_id');
            $this->secret = $this->get_option('secret');
            $this->enabledAPI = $this->get_option('enabledAPI');
            $this->api_id = $this->get_option('api_id');
            $this->api_key = $this->get_option('api_key');
            $this->language = $this->get_option('language');
            $this->paymenttime = $this->get_option('paymenttime');
            $this->payment_method = $this->get_option('payment_method');

            $this->ip_stack = array(
                '151.80.190.97',
                '35.233.69.55'//'151.80.190.104'
            );

            // Actions
            add_action('woocommerce_receipt_interkassa', array($this, 'receipt_page'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

            // Payment listener/API hook
            add_action('woocommerce_api_wc_gateway_interkassa', array($this, 'check_ipn_response'));

            // Payment listener/API hook
            add_action('woocommerce_api_wc_ik_sign', array($this, 'ajaxSign_generate'));

            // Answer from SCI/API hook
            add_action('woocommerce_api_wc_ik_api', array($this, 'getAnswerFromAPI'));

            if (!$this->is_valid_for_use()) {
                $this->enabled = false;
            }
        }

        public function admin_options()
        {
            global $woocommerce;

            ?>
            <h3><?php _e('Интеркасса 2.0', 'interkassa'); ?></h3>

            <?php if ($this->is_valid_for_use()) { ?>

            <table class="form-table">
                <?php

                $this->generate_settings_html();
                ?>
            </table>

        <?php } else { ?>
            <div class="inline error"><p>
                    <strong><?php _e('Шлюз отключен', 'interkassa'); ?></strong>: <?php _e('Единая Касса не поддерживает валюты Вашего магазина.', 'woocommerce'); ?>
                </p></div>
            <?php
        }
        }

        public function init_form_fields()
        {
            global $woocommerce;

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Вкл. / Выкл.', 'interkassa'),
                    'type' => 'checkbox',
                    'label' => __('Включить', 'interkassa'),
                    'default' => 'yes'
                ),
                'test_mode' => array(
                    'title' => __('Тестовый режим', 'interkassa'),
                    'type' => 'checkbox',
                    'label' => __('Включить тестовый режим', 'interkassa'),
                    'default' => 'yes'
                ),
                'test_key' => array(
                    'title' => __('Тестовый ключ', 'interkassa'),
                    'type' => 'text',
                    'description' => __('Введите тестовый ключ', 'interkassa'),
                ),
                'title' => array(
                    'title' => __('Заголовок', 'interkassa'),
                    'type' => 'text',
                    'description' => __('Заголовок, который отображается на странице оформления заказа', 'interkassa'),
                    'default' => __('Интеркасса', 'interkassa'),
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => __('Описание', 'interkassa'),
                    'type' => 'textarea',
                    'description' => __('Описание, которое отображается в процессе выбора формы оплаты', 'interkassa'),
                    'default' => __('Оплатить через электронную платежную систему Интеркасса', 'interkassa'),
                ),
                'merchant_id' => array(
                    'title' => __('Идентификатор кассы', 'interkassa'),
                    'type' => 'text',
                    'description' => __('Уникальный идентификатор кассы в системе Интеркасса.', 'interkassa'),
                ),
                'secret' => array(
                    'title' => __('Секретный ключ', 'interkassa'),
                    'type' => 'text',
                    'description' => __('Секретный ключ', 'interkassa'),
                ),
                'enabledAPI' => array(
                    'title' => __('Включить API/Отключить API', 'interkassa'),
                    'type' => 'checkbox',
                    'label' => __('Включить API', 'interkassa'),
                    'default' => 'no'
                ),
                'api_id' => array(
                    'title' => 'API Id',
                    'type' => 'text',
                    'description' => __('Находится в настройках аккаунта в разделе API.', 'interkassa'),
                ),
                'api_key' => array(
                    'title' => 'API Key',
                    'type' => 'text',
                    'description' => __('Находится в настройках аккаунта в разделе API.', 'interkassa'),
                ),

            );
        }

        function is_valid_for_use()
        {
            if (!in_array(get_option('woocommerce_currency'), array('RUB', 'UAH', 'USD', 'EUR'))) {
                return false;
            }

            return true;
        }

        function process_payment($order_id)
        {
            $order = new WC_Order($order_id);
            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(1)
            );

        }

        public function receipt_page($order)
        {
            echo '<p>' . __('Спасибо за Ваш заказ, пожалуйста, нажмите кнопку ниже, чтобы заплатить.', 'interkassa') . '</p>';
            echo $this->generate_form($order);
        }

        public function generate_form($order_id)
        {
            global $woocommerce;

            $order = new WC_Order($order_id);

            $FormData = [
                'ik_am' => $order->order_total,
                'ik_cur' => get_woocommerce_currency(),
                'ik_co_id' => $this->merchant_id,
                'ik_pm_no' => $order_id,
                'ik_desc' => "order $order_id",
                'ik_loc' => substr(get_locale(), 0, 2),
                'ik_ia_u' => add_query_arg('wc-api', 'WC_Gateway_Interkassa', home_url('/')),
                'ik_suc_u' => str_replace('amp;', '', $this->get_return_url($order)),
                'ik_fal_u' => str_replace('amp;', '', $order->get_cancel_order_url()),
                'ik_pnd_u' => str_replace('amp;', '', $this->get_return_url($order))
            ];
            if ($this->test_mode)
                $FormData['ik_pw_via'] = 'test_interkassa_test_xts';


            $FormData["ik_sign"] = $this->IkSignFormation($FormData, $this->secret);
            $hidden_fields = '';
            foreach ($FormData as $key => $value) {
                $hidden_fields .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . htmlspecialchars($value) . '" />';
            }

            $cancel_url = '<a class="button cancel" href="'
                . $order->get_cancel_order_url() .
                '">' . __('Отказаться от оплаты', 'interkassa') . '</a>';

            $ajax_url = add_query_arg('wc-api', 'wc_ik_sign', home_url('/'));
            $plugin_path = plugin_dir_url('ik-gateway') . 'ik-gateway/';
            $image_path = plugin_dir_url('ik-gateway') . 'ik-gateway/images/';

            include 'tpl.php';
        }

        public function check_ipn_response()
        {
            global $woocommerce;

            if ($this->checkIP() && $_SERVER['REQUEST_METHOD'] == 'POST') {

                $ik_response = $_POST;
                $order_id = (int)$ik_response['ik_pm_no'];
                $order = new WC_Order($order_id);
                if (!$order) {
                    return false;
                }
                
				if($ik_response['ik_pw_via'] == 'test_interkassa_test_xts')
                    $key = $this->test_key;
				else
                    $key = $this->secret;
				
                $ik_sign = $this->IkSignFormation($ik_response, $key);

                if ($ik_response['ik_sign'] == $ik_sign && ($ik_response['ik_co_id'] == $this->merchant_id)) {

                    if ($ik_response['ik_inv_st'] == 'success') {
                        $order->payment_complete();
                        $order->add_order_note(__('Платеж успешно оплачен через Интеркассу', 'interkassa'));
                    } elseif ($ik_response['ik_inv_st'] == 'fail') {
                        $order->update_status('failed', __('Платеж не оплачен', 'interkassa'));
                        $order->add_order_note(__('Платеж не оплачен', 'interkassa'));
                    }
                    echo 'OK';
                    header("HTTP/1.1 200 OK");
                    exit;
                } else {
                    $order = new WC_Order($ik_response['ik_pm_no']);
                    wp_redirect($order->get_cancel_order_url());
                    exit;
                }
            }
        }

        public function ajaxSign_generate()
        {
            header("Pragma: no-cache");
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
            header("Content-type: text/plain");
            $request = $_POST;

            if (isset($_POST['ik_act']) && $_POST['ik_act'] == 'process') {
                // $request['ik_sign'] = $this->IkSignFormation($request, $this->secret);
                $data = $this->getAnswerFromAPI($request);
				echo $data;
				exit;
            } else {
                $data = $this->IkSignFormation($request, $this->secret);
			}
			
			header("Content-type: plain/text");
            echo $data;
            exit;
        }

        public function getAnswerFromAPI($data)
        {
            $ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, self::ikUrlSCI);
			curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
			curl_close($ch);
			
            return $result;
        }

        public function IkSignFormation($data, $secret_key)
        {
            if (!empty($data['ik_sign'])) unset($data['ik_sign']);

            $dataSet = array();
            foreach ($data as $key => $value) {
                if (!preg_match('/ik_/', $key)) continue;
                $dataSet[$key] = $value;
            }

            ksort($dataSet, SORT_STRING);
            array_push($dataSet, $secret_key);
            $arg = implode(':', $dataSet);
            $ik_sign = base64_encode(md5($arg, true));

            return $ik_sign;
        }

        public function getIkPaymentSystems($ik_cashbox_id, $ik_api_id, $ik_api_key)
        {
            $username = $ik_api_id;
            $password = $ik_api_key;
            $remote_url = self::ikUrlAPI . 'paysystem-input-payway?checkoutId=' . $ik_cashbox_id;

            $businessAcc = $this->getIkBusinessAcc($username, $password);

            $ikHeaders = [];
            $ikHeaders[] = "Authorization: Basic " . base64_encode("$username:$password");
            if (!empty($businessAcc)) {
                $ikHeaders[] = "Ik-Api-Account-Id: " . $businessAcc;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $remote_url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $ikHeaders);
            $response = curl_exec($ch);
			curl_close($ch);
			
            $json_data = json_decode($response);

            if (empty($json_data))
                return '<strong style="color:red;">Error!!! System response empty!</strong>';

            if ($json_data->status != 'error') {
                $payment_systems = array();
                if (!empty($json_data->data)) {
                    foreach ($json_data->data as $ps => $info) {
                        $payment_system = $info->ser;
                        if (!array_key_exists($payment_system, $payment_systems)) {
                            $payment_systems[$payment_system] = array();
                            foreach ($info->name as $name) {
                                if ($name->l == 'en') {
                                    $payment_systems[$payment_system]['title'] = ucfirst($name->v);
                                }
                                $payment_systems[$payment_system]['name'][$name->l] = $name->v;
                            }
                        }
                        $payment_systems[$payment_system]['currency'][strtoupper($info->curAls)] = $info->als;
                    }
                }

                return !empty($payment_systems) ? $payment_systems : '<strong style="color:red;">API connection error or system response empty!</strong>';
            } else {
                if (!empty($json_data->message))
                    return '<strong style="color:red;">API connection error!<br>' . $json_data->message . '</strong>';
                else
                    return '<strong style="color:red;">API connection error or system response empty!</strong>';
            }
        }

        public function getIkBusinessAcc($username = '', $password = '')
        {
            $tmpLocationFile = __DIR__ . '/tmpLocalStorageBusinessAcc.ini';
            $dataBusinessAcc = function_exists('file_get_contents') ? file_get_contents($tmpLocationFile) : '{}';
            $dataBusinessAcc = json_decode($dataBusinessAcc, 1);
            $businessAcc = is_string($dataBusinessAcc['businessAcc']) ? trim($dataBusinessAcc['businessAcc']) : '';
            if (empty($businessAcc) || sha1($username . $password) !== $dataBusinessAcc['hash']) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, self::ikUrlAPI . 'account');
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic " . base64_encode("$username:$password")]);
                $response = curl_exec($curl);
				curl_close($curl);
				
                if (!empty($response['data'])) {
                    foreach ($response['data'] as $id => $data) {
                        if ($data['tp'] == 'b') {
                            $businessAcc = $id;
                            break;
                        }
                    }
                }

                if (function_exists('file_put_contents')) {
                    $updData = [
                        'businessAcc' => $businessAcc,
                        'hash' => sha1($username . $password)
                    ];
                    file_put_contents($tmpLocationFile, json_encode($updData, JSON_PRETTY_PRINT));
                }

                return $businessAcc;
            }

            return $businessAcc;
        }

        public function checkIP()
        {
            $ip_callback = ip2long($_SERVER['REMOTE_ADDR']) ? ip2long($_SERVER['REMOTE_ADDR']) : !ip2long($_SERVER['REMOTE_ADDR']);

            if ($ip_callback == ip2long($this->ip_stack[0]) || $ip_callback == ip2long($this->ip_stack[1])) {
                return true;
            } else {
                die('Ты мошенник! Пшел вон отсюда!');
                return false;
            }
        }
    }

    function woocommerce_add_interkassa_gateway($methods)
    {
        $methods[] = 'WC_Gateway_Interkassa';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_interkassa_gateway');
}