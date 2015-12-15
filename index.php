<?php
/**
 * Plugin Name: Spectrocoin Payment Gateway for WooCommerce
 * Plugin URI: http://github.com/alcharkov/spectrocoin-payment-gateway-for-woocommerce
 * Description: Spectrocoin Payment Gateway for WooCommerce
 * Version: 0.1
 * Author: Aleksandr Charkov.
 * Author URI: http://github.com/alcharkov
 * Requires at least: 4.4
 * Tested up to: 4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function spectrocoin_woocomerce_settings($links) {
    $settings_link = '<a href="'.admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_gateway_spectrocoin').'">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'spectrocoin_woocomerce_settings');

add_action('plugins_loaded', 'init_spectrocoin_wc_payment_gateway');

function init_spectrocoin_wc_payment_gateway() {
    // Don't do anything if WooCommerce is not installed
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    define('SC_API_URL', 'https://spectrocoin.com/api/merchant/1');

    define('PLUGIN_DIR', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)) . '/');

    require_once(dirname(__FILE__) . '/SCMerchantClient/SCMerchantClient.php');

    class WC_Gateway_Spectrocoin extends WC_Payment_Gateway {
        public static $log_enabled = true;
        public static $log = 0;

	function __construct() {
	    $this->id = 'spectrocoin';
	    $this->has_fields = false;
	    $this->method_title = __('Spectrocoin', 'woocommerce');
	    $this->method_description = 'Allows bitcoins and euros';

	    $this->icon = apply_filters('woocommerce_paypal_icon', PLUGIN_DIR . 'logo.png');

	    $this->init_form_fields();
	    $this->init_settings();

	    $this->title = $this->get_option('title');
	    $this->project_id = $this->get_option('project_id');
	    $this->merchant_id = $this->get_option('merchant_id');
	    $this->receive_currency = $this->get_option('receive_currency');
	    $this->private_key = $this->get_option('private_key');
	    $this->enabled = $this->get_option('enabled');
	    
    	    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
	    //?wc-api=wc_gateway_spectrocoin
	    add_action('woocommerce_api_wc_gateway_spectrocoin', array($this, 'check_spectrocoin_callback'));

	}

	function init_form_fields() {
	    $this->form_fields = array(
		'title' => array(
		    'title' => __('Title', 'woocommerce'),
		    'type' => ('text')
		),
		'project_id' => array(
		    'title' => __('Project ID', 'woocommerce'),
		    'type' => ('text')
		),
		'merchant_id' => array(
		    'title' => __('Merchant ID', 'woocommerce'),
		    'type' => ('text')
		),
		'receive_currency' => array(
		    'title' => __('Receive currency', 'woocommerce'),
		    'type' => ('select'),
		    'options' => array(
			'eur' => __('EUR', 'woocommerce'),
			'btc' => __('BTC', 'woocommerce')
		    )
		),
		'private_key' => array(
		    'title' => __('Private key', 'woocommerce'),
		    'type' => ('textarea')
		),
		'enabled' => array(
		    'title' => __('Enabled', 'woocommerce'),
		    'type' => ('select'),
		    'options' => array(
			'yes' => __('Yes', 'woocommerce'),
			'no' => __('No', 'woocommerce')
		    )
		)
	    );
	}

	function check_spectrocoin_callback() {
	    global $woocommerce;
	    $ipn = $_REQUEST;

	    // Exit now if the $_POST was empty.
	    if (empty($ipn)) {
		echo 'Invalid request!'; return;
	    }

	    $scMerchantClient = new SCMerchantClient(
		SC_API_URL,
		$this->get_option('merchant_id'),
		$this->get_option('project_id'),
		$this->get_option('private_key')
	    );

	    $callback = $scMerchantClient->parseCreateOrderCallback($ipn);

	    if ($callback != null && $scMerchantClient->validateCreateOrderCallback($callback)){
		switch ($callback->getStatus()) {
		    case OrderStatusEnum::$New:
		    case OrderStatusEnum::$Pending:
			break;
		    case OrderStatusEnum::$Expired:
		    case OrderStatusEnum::$Failed:
			break;
		    case OrderStatusEnum::$Test:
		    case OrderStatusEnum::$Paid:
			$order_number = (int) $ipn['invoice_id'];
			$order = new WC_Order(absint($order_number));
			$order->add_order_note(__('Callback payment completed', 'woocomerce'));
                        $order->payment_complete();
			$order->reduce_order_stock();
			break;
		    default:
			echo 'Unknown order status: '.$callback->getStatus();
			break;
		}
		$woocommerce->cart->empty_cart();

		echo '*ok*';
	    } else {
		echo 'Invalid callback!';
	    }
	    
            exit;
	}
	
	function process_payment($order_id) {
	    global $woocommerce;
	    
	    $order = new WC_Order($order_id);
            $order_number = $order->get_order_number();
	    
	    $receiveCurrency = strtoupper(trim($this->get_option('receive_currency')));
            $currency = $order->get_order_currency();
	    $amount = $order->get_total();

	    if ($currency != $receiveCurrency) {
		$receiveAmount = $this->unitConversion($amount, $currency, $receiveCurrency);
	    } else {
		$receiveAmount = $amount;
	    }

	    if (!$receiveAmount || $receiveAmount < 0) {
		echo 'Spectrocoin is not fully configured. Please select different payment';
		exit;
	    }
	    
	    $scMerchantClient = new SCMerchantClient(
		SC_API_URL,
		$this->get_option('merchant_id'),
		$this->get_option('project_id'),
		$this->get_option('private_key')
	    );

	    set_query_var('invoice_id', $order_number);
	    $callbackUrl = add_query_arg( array('wc-api' => 'WC_Gateway_Spectrocoin', 'invoice_id' => $order_number), home_url( '/' ) );
	    $createOrderRequest = new CreateOrderRequest(
		null,
		0,
		$receiveAmount,
		'',
		'en',
		$callbackUrl,
		$order->get_checkout_order_received_url(),
		$woocommerce->cart->get_checkout_url()
	    );

	    $createOrderResponse = $scMerchantClient->createOrder($createOrderRequest);
	    if ($createOrderResponse instanceof ApiError) {
		$this->log('Error occurred: ');
		$this->log($createOrderResponse->getCode());
		$this->log($createOrderResponse->getMessage());
	    } else if ($createOrderResponse instanceof CreateOrderResponse) {
		     return array(
			 'result' => 'success',
			 'redirect' => $createOrderResponse->getRedirectUrl()
		     );
	    }
	    return ;
	    
	}

	 public static function log($message) {
            if (self::$log_enabled) {
                if (empty(self::$log)) {
                    self::$log = new WC_Logger();
                }
                self::$log->add('spectrocoin', $message);
            }
	 }

	function unitConversion($amount, $currencyFrom, $currencyTo) {
	    $currencyFrom = strtoupper($currencyFrom);
	    $currencyTo = strtoupper($currencyTo);
	    $url = "http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%20in%20%28%22{$currencyTo}{$currencyFrom}%22%20%29&env=store://datatables.org/alltableswithkeys&format=json";
	    $content = file_get_contents($url);
	    if ($content) {
		$obj = json_decode($content);
		if (!isset($obj->error) && isset($obj->query->results->rate->Rate)) {
		    $rate = $obj->query->results->rate->Rate;
		    return ($amount * 1.0) / $rate;
		}
	    }
	}
        
    }
}

function add_spectrocoin_payment_gateway($methods) {
    $methods[] = 'WC_Gateway_Spectrocoin';
    return $methods;
} 

add_filter('woocommerce_payment_gateways', 'add_spectrocoin_payment_gateway');

?>
