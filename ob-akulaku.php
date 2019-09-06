<?php
/*
Plugin Name: OB Akulaku
Plugin URI: https://www.ob-fit.com
Description: Payment Gateway untuk OB Fit dari Akulaku
Version: 0.1.0
Author: Hilaludin Wahid
Author URI: https://www.wahid.biz
*/
if ( ! defined( 'ABSPATH' ) ) 
die;


// cek WooCommerce

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
    echo "<div class='error notice'><p>Woocommerce has to be installed and active to use the the HCI Payments Gateway</b> plugin</p></div>";
  return;
}

// memulai plugins
add_action( 'plugins_loaded', 'obakulaku_payment_gateway_init', 0);
function obakulaku_payment_gateway_init()
{
    if ( !class_exists( 'WC_Payment_Gateway' ) ) 
    return;

    load_plugin_textdomain('wc-gateway-name', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
    /**Payment Gateway Class 
    */
    class WC_obakulaku_Gateway extends WC_Payment_Gateway {
        public function __construct() {
            $this->id = 'obakulaku';
            $this->icon = apply_filters( 'woocommerce_obakulaku_icon', plugins_url( 'public/image/logo-al.png', __FILE__ ) );
            $this->has_fields = true;
            $this->method_title = 'OB Akulaku';
            
            $this->init_form_fields();
            $this->init_settings();

            $this->title              = $this->settings['name'];
            $this->method_description = 'Integrasi Payment gateway ke akulaku.';

            if ( empty($this->settings['server_dest']) || $this->settings['server_dest'] == '0' || $this->settings['server_dest'] == 0 )
            {
                $this->appId  = trim($this->settings['appId_sandbox']);
                $this->secKey = trim($this->settings['secKey_sandbox']);
                $this->url    = "https://testmall.akulaku.com";
            }
            else
            {
                $this->appId  = trim($this->settings['appId']);
                $this->secKey = trim($this->settings['secKey']);
                $this->url    = "https://mall.akulaku.com";
            }
           // $pattern = "/(^a-zA-Z0-9]+)/";
           // $result  = preg_match($pattern, $this->prefixid, $matches, PREG_OFFSET_CAPTURE);
           add_action('init', array($this, 'check_obakulaku_response' ));
           add_action('valid_obakulaku_request');
           add_action('woocommerce_receipt_obakulaku', array($this, 'receipt_page'));

           if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) )
           {
             add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
           }
           else{
             add_action( 'woocommerce_update_options_payment_gateway', array( &$this, 'obakulaku_callback' ) );
           }
           add_action( 'woocommerce_api_wc_obakulaku_payment_gateway', array( &$this, 'obakulaku-callback' ) );

 			/**
			 * Initialisation form for Gateway Settings
			 */ 
        function init_form_fields()
        {
          $this->form_fields = array(
            'enabled'       => array(
              'title'       => __( 'Enable/Disable', 'woocommerce' ),
									'type'    => 'checkbox',
									'label'   => __( 'Enable OBAKULAKU Payment Gateway', 'woocommerce' ),
									'default' => 'yes'
            ),
            'server_dest'   => array(
              'title'       => __( 'Server Destination', 'woocommerce' ),
              'type'        => 'checkbox',
              'description' => __( 'Pilihan untuk status server Development atau Production', 'woocommerce'),
              'options'     => array (
                                       '0' => __( 'Development', 'woocommerce' ),
                                       '1' => __( 'Production', 'woocommerce' )
              ),
              'desc_tip' => true,
            ),
            'appId_sandbox' => array(
              'title'       => __( 'Akulaku Id Sandbox', 'woocommerce' ),
              'type'        => 'text',
              'description' => __( 'Masukan nomor id Akulaku untuk development', 'woocommerce' ),
              'default'     => '',
              'desc_tip'    => true,
            ),
            'secKey_sandbox' => array(
              'title'       => __( 'Security Key', 'woocommerce' ) ,
              'type'        => 'text',
              'description' => __( 'Masukkan security key dari akulaku untuk development', 'woocommerce' ),
              'default'     => '',
              'desc_tip'    => true,
            ),
            'appId'         => array(
              'title'       => __( 'Akulaku Id Production', 'woocommerce' ),
              'type'        => 'text',
              'description' => __( 'Masukan nomor id Akulaku Production', 'woocommerce' ),
              'default'     => '',
              'desc_tip'    => true,
            ),
            'secKey'         => array(
              'title'       => __( 'Security key Production', 'woocommerce' ),
              'type'        => 'text',
              'description' => __( 'Masukan security key Akulaku untuk Production', 'woocommerce' ),
              'default'     => '',
              'desc_tip'    => true,

            ),

          );
        } 
        //untuk admin options
        public function admin_options()
        {
          echo '<h2>'.__('Akulaku Payment gateway', 'woocommerce').'</h2>';
          echo '<p>' .__('Kerjasama OB Fit dan Akulaku', 'woocommerce').'</p>';
          echo "<h3>obakulaku Parameter</h3><br>\r\n";

          echo '<table class="form-table">';
						$this->generate_settings_html();
            echo '</table>';
        }

        
        // Generate form payments
        public function generate_dokuonecheckout_form($order_id) 
				{
					
						global $woocommerce;
						global $wpdb;
						static $basket;
		
						$order = new WC_Order($order_id);
						$counter = 0;
		
						foreach($order->get_items() as $item) 
						{
								$BASKET = $basket.$item['name'].','.$order->get_item_subtotal($item).','.$item['qty'].','.$order->get_line_subtotal($item).';';
						}
						
						$BASKET = "";
						
						// Order Items
						if( sizeof( $order->get_items() ) > 0 )
						{
								foreach( $order->get_items() as $item )
								{							
										$BASKET .= $item['name'] . "," . number_format($order->get_item_subtotal($item), 2, '.', '') . "," . $item['qty'] . "," . number_format($order->get_item_subtotal($item)*$item['qty'], 2, '.', '') . ";";
								}
						}
						
						// Shipping Fee
						if( $order->order_shipping > 0 )
						{
								$BASKET .= "Shipping Fee," . number_format($order->order_shipping, 2, '.', '') . ",1," . number_format($order->order_shipping, 2, '.', '') . ";";
						}					
						
						// Tax
						if( $order->get_total_tax() > 0 )
						{
								$BASKET .= "Tax," . $order->get_total_tax() . ",1," . $order->get_total_tax() . ";";
						}
			
						// Fees
						if ( sizeof( $order->get_fees() ) > 0 )
						{
								$fee_counter = 0;
								foreach ( $order->get_fees() as $item )
								{
										$fee_counter++;
										$BASKET .= "Fee Item," . $item['line_total'] . ",1," . $item['line_total'] . ";";																		
								}
						}
				
						$BASKET = preg_replace("/([^a-zA-Z0-9.\-,=:;&% ]+)/", " ", $BASKET);	
       
            $current_user      = wp_get_current_user();



            $appId             = trim($this->appId);
            $refNo             = $order_id;
            $totalPrice        = number_format($order->order_total, 2, '.', '');
            $userAccount       = trim($current_user->ID);
            $receiverName      = trim($order->billing_first_name . " " . $order->billing_last_name);
            $receiverPhone     = trim($order->billing_phone);
            $province          = trim($order->billing_city);
            $city              = trim($order->billing_city);
            $street            = trim($order->billing_address_1 . " " . $order->billing_address_2);
            $postcode          = trim($order->billing_postcode);
            $callbackPageUrl   = WC()->api_request_url( 'WC_obakulaku_Gateway');
            $details
            $virtualDetails
            $extraInfo
            //bikin signnya dulu nanti
            $sign              = 



            



          


       

}




