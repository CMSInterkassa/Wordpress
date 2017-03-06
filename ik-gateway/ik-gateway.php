<?php
/*
Plugin Name: InterKassa Gateway
Plugin URI: http://www.gateon.net
Description: Платежный шлюз "Интеркасса" для сайтов на WordPress. (версия Интеркассы 2.0)
Version: 1.4
Lat Update: 5.03.2017
Author: Gateon
Author URI: http://www.gateon.net
*/
if (!defined('ABSPATH')) exit;

add_action('plugins_loaded', 'woocommerce_init', 0);

function woocommerce_init()
{

    if (!class_exists('WC_Payment_Gateway')) return;

    class WC_Gateway_Interkassa extends WC_Payment_Gateway
    {

        public function __construct()
        {

            global $woocommerce;

            $this->id = 'interkassa';
            $this->has_fields = false;
            $this->method_title = __('Интеркасса 2.0', 'woocommerce');
            $this->method_description = __('Интеркасса 2.0', 'woocommerce');
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->test_mode = $this->get_option('test_mode');
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
                'ip_begin' => '151.80.190.97',
                'ip_end'   => '151.80.190.104'
            );
            
            // Actions
            add_action('woocommerce_receipt_interkassa', array($this, 'receipt_page'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

            // Payment listener/API hook
            add_action('woocommerce_api_wc_gateway_interkassa', array($this, 'check_ipn_response'));

            // Payment listener/API hook
            add_action('woocommerce_api_wc_ik_sign', array($this, 'ajaxSign_generate'));
  
            if (!$this->is_valid_for_use()) {
                $this->enabled = false;
            }
        }



        public function admin_options()
        {

            ?>
            <h3><?php _e('ИнтерКасса 2.0', 'woocommerce'); ?></h3>

            <?php if ($this->is_valid_for_use()) { ?>

            <table class="form-table">
                <?php

                $this->generate_settings_html();
                ?>
            </table>

            <?php } else { ?>
            <div class="inline error"><p>
                    <strong><?php _e('Шлюз отключен', 'woocommerce'); ?></strong>: <?php _e('Единая Касса не поддерживает валюты Вашего магазина.', 'woocommerce'); ?>
                </p></div>
            <?php
            }
        }

        public function init_form_fields()
        {

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Включить/Отключить', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Включить', 'woocommerce'),
                    'default' => 'yes'
                ),
                'test_mode' => array(
                    'title' => __('Тестовый режим', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Включить тестовый режим', 'woocommerce'),
                    'default' => 'yes'
                ),
                'test_key' => array(
                    'title' => __('Тестовый ключ', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Введите тестовый ключ', 'woocommerce'),
                ),
                'title' => array(
                    'title' => __('Заголовок', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Заголовок, который отображается на странице оформления заказа', 'woocommerce'),
                    'default' => 'Интеркасса',
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => __('Описание', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('Описание, которое отображается в процессе выбора формы оплаты', 'woocommerce'),
                    'default' => __('Оплатить через электронную платежную систему Интеркасса', 'woocommerce'),
                ),
                'merchant_id' => array(
                    'title' => __('Индефикатор кассы', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Уникальный идентификатор кассы в системе Интеркасса.', 'woocommerce'),
                ),
                'secret' => array(
                    'title' => __('Секретный ключ', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Секретный ключ', 'woocommerce'),
                ),
                'enabledAPI' => array(
                    'title' => __('Включить API/Отключить API', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Включить API', 'woocommerce'),
                    'default' => 'no'
                ),
                'api_id' => array(
                    'title' => __('API Id', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Находится в настройках аккаунта в разделе API.', 'woocommerce'),
                ),
                'api_key' => array(
                    'title' => __('API Key', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Находится в настройках аккаунта в разделе API.', 'woocommerce'),
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
                'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))))
            );

        }

        public function receipt_page($order)
        {

            echo '<p>' . __('Спасибо за Ваш заказ, пожалуйста, нажмите кнопку ниже, чтобы заплатить.', 'woocommerce') . '</p>';
            echo $this->generate_form($order);
            if($this->enabledAPI == 'yes'){
            	echo $this->generateAPI();
            }   
        }

        public function generate_form($order_id)
        {
            global $woocommerce;

            $order = new WC_Order($order_id);
            $action_adr = "https://sci.interkassa.com/";
            $result_url = str_replace('https:', 'http:', add_query_arg('wc-api', 'wc_gateway_interkassa', home_url('/')));

            $args = array(
                'ik_am' => $order->order_total,
                'ik_cur' => get_woocommerce_currency(),
                'ik_co_id' => $this->merchant_id,
                'ik_pm_no' => $order_id,
                'ik_desc' => "#$order_id",
                'ik_ia_u'=>str_replace('https:', 'http:', add_query_arg('wc-api', 'WC_Gateway_Interkassa', home_url('/'))),
                'ik_suc_u'=>str_replace('https:', 'http:', add_query_arg('wc-api', 'WC_Gateway_Interkassa', home_url('/'))),
                'ik_fal_u'=>str_replace('https:', 'http:', add_query_arg('wc-api', 'WC_Gateway_Interkassa', home_url('/'))),
                'ik_pnd_u'=>str_replace('https:', 'http:', add_query_arg('wc-api', 'WC_Gateway_Interkassa', home_url('/')))
            );

            ksort($args, SORT_STRING);
            $args['secret'] = $this->secret;
            $signString = implode(':', $args);
            $signature = base64_encode(md5($signString, true));

            unset($args["secret"]);
            $args["ik_sign"] = $signature;
            $args_array = array();
            foreach ($args as $key => $value) {
                $args_array[] = '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
            }

            return
                '<form accept-charset="windows-1251" action="' . esc_url($action_adr) . '" method="POST" name="interkassa_form">' .
                '<input type="submit" class="button alt" id="submit_interkassa_button" value="' . __('Оплатить', 'woocommerce') . '" /> <a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __('Отказаться от оплаты & вернуться в корзину', 'woocommerce') . '</a>' . "\n" .
                implode("\n", $args_array) .
                '</form>';
        }

        public function check_ipn_response()
        {
            global $woocommerce;

            if ($_POST['ik_co_id']) {

                if(ip2long($_SERVER['REMOTE_ADDR'])<=ip2long($this->ip_stack['ip_begin']) && ip2long($_SERVER['REMOTE_ADDR'])>=ip2long($this->ip_stack['ip_end'])){
                    die('Ты мошенник! Пшел вон отсюда!');
                }

                if(isset($_POST['ik_pw_via']) && $_POST['ik_pw_via'] == 'test_interkassa_test_xts'){
                    $ik_key = $this->test_key;
                } else {
                    $ik_key = $this->secret;
                }
                
                $merchant_id = $this->merchant_id;
                $data = array();
                foreach ($_REQUEST as $key => $value) {
                    if (!preg_match('/ik_/', $key)) continue;
                    $data[$key] = $value;
                }

                $ik_sign = $data['ik_sign'];
                unset($data['ik_sign']);
                ksort($data, SORT_STRING);
                array_push($data, $ik_key);
                $signString = implode(':', $data);
                $sign = base64_encode(md5($signString, true));

                if ($sign == $ik_sign && $data['ik_co_id'] == $merchant_id) {
                    $order_id = $data['ik_pm_no'];
                    $order = new WC_Order($order_id);

                    if ($data['ik_inv_st'] == 'success') {
                        $order->payment_complete();
                        $order->add_order_note(__('Платеж успешно оплачен через Интеркассу', 'woocommerce'));
                    } else if ($data['ik_inv_st'] == 'fail') {
                        $order->update_status('failed', __('Платеж не оплачен', 'woocommerce'));
                        $order->add_order_note(__('Платеж не оплачен', 'woocommerce'));
                    }

                    $woocommerce->cart->empty_cart();
                    wp_redirect(get_permalink(woocommerce_get_page_id('thanks')));
                    exit();
                } else {
                    $order = new WC_Order($_REQUEST['ik_pm_no']);
                    wp_redirect($order->get_cancel_order_url());
                    exit();
                }

            } else {
                $woocommerce->cart->empty_cart();
                wp_redirect(home_url());
                exit;

            }


        }

        public function ajaxSign_generate(){
                header("Pragma: no-cache");
                header("Cache-Control: no-cache, must-revalidate");
                header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
                header("Content-type: text/plain");
             $sign = $this->IkSignFormation($_POST, $this->secret);
              echo $sign;
                die();
        
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

        public function generateAPI(){

			$ajax_url = str_replace('https:', 'http:', add_query_arg('wc-api', 'WC_Ik_sign', home_url('/')));
        	$image_path = plugin_dir_url('ik-gateway').'ik-gateway/paysystems/';
        	$payment_systems = $this->getIkPaymentSystems($this->merchant_id, $this->api_id, $this->api_key);
        	include 'apitpl.php';
        }
        public function getIkPaymentSystems($ik_co_id, $ik_api_id, $ik_api_key){
        $username = $ik_api_id;
        $password = $ik_api_key;
        $remote_url = 'https://api.interkassa.com/v1/paysystem-input-payway?checkoutId=' . $ik_co_id;

        // Create a stream
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "Authorization: Basic " . base64_encode("$username:$password")
            )
        );

        $context = stream_context_create($opts);
        $file = file_get_contents($remote_url, false, $context);
        $json_data = json_decode($file);

        if($json_data->status != 'error'){
        $payment_systems = array();
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
        return $payment_systems;
        }else{
            echo '<strong style="color:red;">API connection error!<br>'.$json_data->message.'</strong>';
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
?>