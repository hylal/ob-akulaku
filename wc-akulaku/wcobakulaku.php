<?php

/*
Plugin Name: OBFIT WC Akulaku
Plugin URI: https://www.ob-fit.com
Description: Payment Gateway untuk OB Fit dari Akulaku
Version: 0.1.0
Author: Hilaludin Wahid
Author URI: https://www.wahid.biz
License: GPL-3.0+
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
WC requires at least: 3.0
WC tested up to: 3.7
 */
//Bismillahirrahmaanirrahim

if (!defined('ABSPATH')) {
    die;
}

add_action('plugins_loaded', 'woocommerce_wcobakulaku_init', 0);

function woocommerce_wcobakulaku_init() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    // ini untuk konfigurasi umumnya
    
    include_once dirname(__FILE__) . '/includes/admin/class-wcobakulaku-admin-settings.php';
    if (!class_exists('OBakulaku_Payment_Gateway')) {
        
        /**
         * disini untuk abstract class
         * parent class untuk penambahan barangkali ada yang masuk lagi lainnya
         */
        Abstract class OBakulaku_Payment_Gateway extends WC_Payment_Gateway {

         /** @var bool apakah fungsi logging itu aktif */
        public static $log_enabled = false;
        /** @var WC_Logger untuk instansi logger */
        public static $log = false;

        public function __construct() {

            // plugin id
            $this->id = $this->sub_id;

            // payment method
            $this->payment_method = '';

            // kalau trueberarti menggunakan layanan ini
            $this->has_fields = false;

            // set konfigurasi global
            // redirect URL
            $this->redirect_url = WC()->api_request_url('WC_Gateway_' . $this->id);

            // load pengaturan
            $this->init_form_fields();
            $this->init_settings();

            // Menentukan user set variabel
            $this->title = $this->settings['title'];
            $this->enabled = $this->settings['enabled'];
            $this->description = $this->settings['description'];

            // atur variabel dari konfigurasi Global
            $this->secKey = get_option('wcobakulaku_seckey');
            $this->appId = get_option('wcobakulaku_appId');
            self::$log_enabled = get_option('wcobakulaku_debug');

            // remove trailing slash and add one for our need
            $this->endpoint = rtrim(get_option('wcobakulaku_endpoint'), '/');

            self::$log_enabled = get_option('wcobakulaku_debug') == 'yes' ? true : false;

            // actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));

            // Payment listener/API Hook
            add_action('woocommerce_api_wc_gateway_' . $this->id, array($this, 'check_wcobakulaku_response'));
        }

/**
 * set fields untuk setiap payment gateway
 * @return void
 */
            function init_form_fields(){
                $this->form_fields = array(
                    'enabled' => array(
						'title' => __('Enable/Disable', 'woothemes'),
						'label' => __('Enable WC OB Akulaku', 'woothemes'),
						'type' => 'checkbox', 'description' => '',
						'default' => 'no',
					),
					'title' => array(
						'title' => __('Title', 'woothemes'), 'type' =>
						'text', 'description' => __('', 'woothemes'),
						'default' => __('Pembayaran Akulaku', 'woothemes'),
					),
					'description' => array(
						'title' => __('Description', 'woothemes'),
						'type' => 'textarea', 'description' => __('',
							'woothemes'), 'default' => 'Sistem pembayaran menggunakan Akulaku.',
					),

                );
            }

            public function admin_options() {
                echo '<table class="form-table">';
                $this->generate_settings_html();
                echo '</table>';
            }


            /**
			 * @param $order_id
			 * @return null
			 */

            function process_payment($order_id) {
                $order = new WC_Order($order_id);

                $this->log('Generating paymnet form for order ' . $order->get_order_number() . '. Notify URL ' . $this->redirect_url);

                //endpoint for inquiry
                $url = $this->endpoint . '/api/json/public/openpay/new.do';
                      
                               
                //merchant user
                $current_user = $order->billing_first_name . " " . $order->billing_last_name;
                                //record current user who made a transaction for merchant info
                $userstreet = $order->billing_address_1 . $order->billing_address_2;

                //Details produk disini
                $order = wc_get_order( $order_id );
				$order_data = $order->get_items();
				//build order items 
				$produk_items = [];
				$items_counter = 0;
				$total_cost = 0;
				foreach ($order_data as $order_key => $order_value):
				// get image 
				$product = $order_value->get_product();
				if ($product->get_image_id()) {
					$image_src = wp_get_attachment_image_src($product->get_image_id());
					$image_url = $image_src[0];
					} else {
                    $image_url = '';
					}
					// adding category product as item
				$kategori_id = $product->get_id();
				//$type = wc_get_product_category_list($kategori_id);
                //vendor name
                $vendorname = "OB Fitness Health";
                $vendorid = "OBFIT01";
                // items add to cart
				$produk_items[$items_counter] = [
							"skuId" => $order_value->get_id(),
                            "skuName" => $product->get_sku(),
                            "unitPrice" => $order_value->get_total()/$order_value->get_quantity(), 
                            "qty" => $order_value->get_quantity(), // Get the item quantity
                            "imageUrl" => $image_url,
                            "vendorName" => $vendorname,
                            "vendorId" => $vendorid
                        ];
					
						$total_cost += $order_value->get_total();
						$items_counter++;
                endforeach;
                

                $produkdetails[] = ([
                            "skuId" => $order_value->get_id(),
                            "skuName" => $product->get_sku(),
                            "unitPrice" => $order_value->get_total()/$order_value->get_quantity(), 
                            "qty" => $order_value->get_quantity(), // Get the item quantity
                            "imageUrl" => $image_url,
                            "vendorName" => $vendorname,
                            "vendorId" => $vendorid
                ]);

               
                

				/*
				if (is_user_logged_in()) {
					$current_user = wp_get_current_user()->user_login;
				} else {
					$current_user = "GUEST";
				}
                */		
                                             
                /**Generate signature */
            
        
                $contents = array(
                    $order_id,
                    intval($order->order_total),
                    $order->billing_email,
                    $current_user,
                    $order->billing_phone,
                    $order->billing_state,
                    $order->billing_city,
                    $userstreet,
                    $order->billing_postcode,
                    $this->redirect_url,
                    json_encode($produkdetails),
                    '',
                    '{}',
                    );

                  
                    

            
                    //$contentJoined = array();
                    $contentjoined = implode($contents);

                    //$appId = wc_clean(stripslashes($_REQUEST['appId']));
                
                    //$appId = $this->appId;
                
                    //$security = $this->secKey;

                $content = $this->appId . $this->secKey . $contentjoined;
                $sign = base64_encode(hash('sha512', $content, true));
                $sign = str_replace(array('+','/','='),array('-','_',''),$sign);

               //var_dump($content);
               //die;

                
                $params = array(
                    'appId' => $this->appId,
                    'refNo' => $order_id,
                    'totalPrice' => intval($order->order_total),
                    'userAccount' => $order->billing_email,
                    'receiverName' => $current_user,
                    'receiverPhone' => $order->billing_phone,
                    'province' => $order->billing_state,
                    'city' => $order->billing_city,
                    'street' => $userstreet,
                    'postcode' => $order->billing_postcode,
                    'callbackPageUrl' => $this->redirect_url,
                    'details' => json_encode($produkdetails),
                    'virtualDetails' => '',
                    'extraInfo' => '{}',
                    'sign' => $sign,
                );
                //lagi
                $paramsJoined = array();
                foreach($params as $param => $value) {
                    $paramsJoined[] = "$param=$value";
                }
                $infopesanan = implode('&', $paramsJoined);
                
               
                
        
                //$params = http_build_query($params);
                //$paramds = str_replace(array('+','/','='),array('-','_',''),$sign);
                //echo http_build_query($data) . "\n";

                $headers = array('Content-Type' => 'application/x-www-form-urlencoded');

                //show request dari permintaannya
                $this->log("create a request for inquiry");
                $this->log(var_export($params, true));

                // send this payload to authorize 4 processing
                $response = wp_remote_post($url, array(
                        'method' => 'POST', 
                        'headers' => $headers,
                        'body' => $infopesanan,                  
                )
                );
                 
 
                
                // retrive the bodys response if no errors found
                $response_body = wp_remote_retrieve_body($response);
                $response_code = wp_remote_retrieve_response_code($response);

                // var_dump($response_body);
                //die;

                if (is_wp_error($response)) {
                    throw new Exception(__('maaf saat ini sedang ada kendala untuk menghubungi server Akulaku.', 'wcobakulaku'));                
                }

                if (empty($response_body)) {
                    throw new Exception(__('Tidak ada respon dari server Akulaku', 'wcobakulaku'));
                }

                // ubah respon server menjadi teks terbaca
                $resp = json_decode($response_body);
               
               //var_dump($resp);
               //die;

                //log respon dari server
                $this->log('response body: ' . $response_body);
                $this->log('response code: ' . $response_code);
                $this->log($url);

                $signPembayaran = $this->appId . $this->secKey . $order_id;

                $signcode = base64_encode(hash('sha512', $signPembayaran, true));
                $signcode = str_replace(array('+','/','='),array('-','_',''),$signcode);


                $pembayaranUrl = $this->endpoint . "/v2/openPay.html?appId=" . $this->appId . "&refNo=" . $order_id . "&sign=" . $signcode . "&lang=id";

                // Uji code untuk mengetahui bisa atau tidak. 1 or 4
                // artinya transaksi telah berhasil
                if ($response_code == '200') {

                    // simpan kode referensi ini
                    $this->log('Permohonan berhasil untuk order id ' . $order->get_order_number() . ' dengan no refensi ' . $order->order_id);
                    if($this->payment_method == 'AL')
                    {
                        wc()->cart->empty_cart();
                    }

                    //redirect ke Thank You Page pastiin lagi paymentUrl-nya
                    return array(
                        'result' => 'success', 
                        'redirect' => $pembayaranUrl,
                    );
                } else {
                    $this->log('Permohonan Anda Gagal dengan order Id' . $order->get_order_number());
                    //Transaksi tidak berhasil tambahkan peringatan ke keranjang

                    if ($response_code == "400") {
                        wc_add_notice($resp->Message, 'error');
                        // tambah catatan ke order utk noRef
                        $order->add_order_note( 'Error:' . $resp->Message);
                    }
                    else{
                        wc_add_notice("error processing payment", 'error');
                        //tambahkan note 
                        $order->add_order_note( 'Error : error processing payment.');
                    }
                    return;
                }
            }

           // var_dump($response);
           // die;

            /**
             * @return null
             */

            function check_wcobakulaku_response() {
                $this->log("masuk ke check wcobakulaku respon.");
                    //resultcode diganti appId
                if (empty($_REQUEST['appId']) || empty($_REQUEST['refNo']) || empty($_REQUEST['sign'] )) {
                    throw new Exception(__('wrong query string please contact admin.', 'wcobakulaku'));
                    return;
                }

                if (!empty($_REQUEST['status']) && $_REQUEST['status'] == 'notify') {
					$this->notify_response();
					exit;
                }
                
                $appId = wc_clean(stripslashes($_REQUEST['appId']));
                $refNo = wc_clean(stripslashes($_REQUEST['refNo']));
                $sign = wc_clean(stripslashes($_REQUEST['sign']));
                $status = wc_clean(stripslashes($_REQUEST['status']));


                $order = new WC_Order($order_id);

                if ($status == '100' && $this->validate_transaction($appId, $refNo, $sign)) {
                    $order->add_order_note(__('Pembayaran telah dilakukan melalui Akulaku dengan id ' . $refNo, 'woocommerce'));
                    $this->log("Pembayaran dengan order ID " . $refNo . " telah berhasil.");
                }else {
					$order->add_order_note('Pembayaran dengan Akulaku tidak berhasil');
					$this->log("Pembayaran dengan order ID " . $refNo . " gagal.");
					//$order->update_status( 'on-hold', __( 'pembayaran gagal mohon contact administrator ', 'woocommerce'));
					//$order->reduce_order_stock();
					//WC()->cart->empty_cart();
				}

				exit;
            }

            function notify_response() {

                //log request from Server
                $this->log(var_export($_REQUEST, true));

                if (empty($_REQUEST['status']) || empty($_REQUEST['refNo'])) {
                    throw new Exception(__('wrong query string please contact admin.', 'wcobakulaku'));
                    return false;
                }

                $order_id = wc_clean(stripslashes($_REQUEST['refNo']));
                $order = new WC_Order($order_id);
                
                if ($_REQUEST['status'] == '100') {
					wc_add_notice('pembayaran dengan akulaku telah berhasil.');
                            return wp_redirect($order->get_checkout_order_received_url());
				}else if ($_REQUEST['status'] == '1') {
					wc_add_notice('pembayaran dengan akulaku sedang diproses.');
                            return wp_redirect($order->get_checkout_order_received_url());
				} else {
					wc_add_notice('pembayaran dengan akulaku gagal.', 'error');
                            return wp_redirect($order->get_checkout_payment_url(false));
				}
				//return wp_redirect($this->get_return_url(new WC_Order($order_id)));

            }

           

            /**
			 * function to generate log for debugging
			 * to activate loggin please set debug to true in admin configuration
			 * @param type $message
			 * @return type
			 */
            public static function log($message) {
                if (self::$log_enabled) {
                    if (empty(self::$log)){
                        self::$log = new WC_Logger();
                    }
                    self::$log->add('wcobakulaku', $message);
                }
            }
        }
    }
    /**
	 *
	 * @param type $methods
	 * set duitku gateway that uses Duitku Payment Gateway
	 * @return type
	 */

    function add_wcobakulaku_gateway($methods) {
        $methods[] = 'WC_Gateway_OB_Akulaku';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_wcobakulaku_gateway');

    foreach (glob(dirname(__FILE__) . '/includes/daftarpg/*.php') as $filename) {
        include_once $filename;
    }
}
