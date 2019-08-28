<?php
/*
Plugin Name: OB Akulaku
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
    if (!defined('ASBPATH')) {
      die;
    }

// cek WooCommerce

    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
      echo "<div class='error notice'><p>Woocommerce has to be installed and active to use the the HCI Payments Gateway</b> plugin</p></div>";
    return;
  }

  // memulai plugins
  add_action( 'plugin_loaded', 'exe_OB_akulaku_payment_gateway' );
  function exe_OB_akulaku_payment_gateway() {

    // Menghubungi WooCommerce

    function add_OB_akulaku_payment_gateway( $methods ) {
      $methods[] = 'add_OB_akulaku_payment_gateway';
      return $methods;
    }

    add_filter( 'woocommerce_payment_gateways', 'add_OB_akulaku_payment_gateway' );

    if ( ! class_exist( 'WC_Payment_Gateway' ) )
    return;

    // OB akulaku class
    class OB_akulaku_payment_gateway extends WC_Payment_Gateway {
      // Constructor
      public function __construct() {
        $this->id = 'obakulaku';
        $this->icon = apply_filters( 'woocommerce_obakulaku_icon', plugins_url( 'public/image/logo-al.png', __FILE__ ) );
        $this->has_fields = true;
        $this->method_title = 'OB Akulaku';
        $this->method_description = 'Integrasi Payment gateway ke akulaku.';

        // load ke isian kolom
        $this->init_form_fields();
        // load settings
        $this->init_settings();

        // Mendapatkan data nilai settings
        $this->title		 = $this->get_option( 'title' );
        $this->description	 = $this->get_option( 'description' );
        $this->enabled		 = $this->get_option( 'enabled' );
        $this->sandbox		 = $this->get_option( 'sandbox' );
        $this->environment	 = $this->sandbox == 'no' ? 'production' : 'sandbox';
        $this->appId	 = $this->sandbox == 'no' ? $this->get_option( 'appId' ) : $this->get_option( 'sandbox_appId' );
        $this->secKey	 = $this->sandbox == 'no' ? $this->get_option( 'secKey' ) : $this->get_option( 'sandbox_secKey' );

        //Hooks
        add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
        add_action( 'admin_notices', array($his, 'checks' ) );
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
      }
      // Pilihan Admin
      public function admin_options() {
        ?>
        <h3><?php _e( 'OB Akulaku', 'woocommerce'); ?></h3>
        <p><?php _e( 'Payment Gateway untuk Akulaku', 'woocommerce'); ?></p>
        <table class="form-table">
          <?php $this->generate_settings_html(); ?>
      	        <script type="text/javascript">
      	    	jQuery('#woocommerce_obakulaku_sandbox').change(function () {
                var sandbox = jQuery('#woocommerce_obakulaku_sandbox_secKey, #woocommerce_obakulaku_sandbox_appId').closest('tr'),
                production = jQuery('#woocommerce_obakulaku_secKey, #woocommerce_obakulaku_appId').closest('tr');

      	    	    if (jQuery(this).is(':checked')) {
      	    		sandbox.show();
      	    		production.hide();
      	    	    } else {
      	    		sandbox.hide();
      	    		production.show();
      	    	    }
      	    	}).change();
      	        </script>
      	    </table> <?php
      	}
        // Fungsi ssl
      public function checks() {
      if ( $this->enabled == 'no' ) {
        return;
        }
          // PHP version
          if ( version_compare( phpversion(), '5.4.0', '<' ) ) {
            echo '<div class="error"<p>' .sprintf( __( 'OB Akulaku Error: requires PHP 5.4.0 and above. You are using version %s.', 'woocommerce' ), phpversion() ) . '</p></div>';
          }
          // Show message if enabled and FORCE SSL is disabled and WordpressHTTPS plugin is not detected
         elseif ( 'no' == get_option( 'woocommerce_force_ssl_checkout' ) && ! class_exists( 'WordPressHTTPS' ) ) {
       $greater_than_33 = version_compare( '3.3', WC_VERSION );
       $wc_settings_url = admin_url( sprintf( 'admin.php?page=wc-settings&tab=%s', $greater_than_33 ? 'advanced' : 'checkout' ) );

       echo '<div class="error"><p>' . sprintf( __( 'OB Akulaku is enabled, but the <a href="%s">Secure checkout</a> option is disabled; your checkout may not be secure! Please enable SSL and ensure your server has a valid SSL certificate - OB Akulaku will only work in sandbox mode.', 'woocommerce' ), $wc_settings_url ) . '</p></div>';
         }
     }
      // Check PG ini enabled
      public function is_available() {
       if ( 'yes' != $this->enabled ) {
         return false;
       }

       return true;
    }
    // PG Setting
    public function init_form_fieds() {
      $this->form_fields = array(
		'enabled'		 => array(
		    'title'		 => __( 'Enable/Disable', 'woocommerce' ),
		    'label'		 => __( 'Enable OB Akulaku Payment Gateway', 'woocommerce' ),
		    'type'		 => 'checkbox',
		    'description'	 => '',
		    'default'	 => 'no'
		),
    'title'			 => array(
		    'title'		 => __( 'Title', 'woocommerce' ),
		    'type'		 => 'text',
		    'description'	 => __( 'Layanan Akulaku disini.', 'woocommerce' ),
		    'default'	 => __( 'OB Akulaku', 'woocommerce' ),
		    'desc_tip'	 => true
		),
    'description'		 => array(
        'title'		 => __( 'Description', 'woocommerce' ),
        'type'		 => 'textarea',
        'description'	 => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
        'default'	 => 'Pay securely with Akulaku.',
        'desc_tip'	 => true
    ),
    'sandbox'		 => array(
        'title'		 => __( 'Sandbox', 'woocommerce' ),
        'label'		 => __( 'Enable Sandbox Mode', 'woocommerce' ),
        'type'		 => 'checkbox',
        'description'	 => __( 'Place the payment gateway in sandbox mode using sandbox API keys (real payments will not be taken).', 'woocommerce' ),
        'default'	 => 'yes'
    ),
    'sandbox_appId'	 => array(
		    'title'		 => __( 'Sandbox App ID From Akulaku', 'woocommerce' ),
		    'type'		 => 'text',
		    'description'	 => __( 'Get your API keys from your Akulaku account.', 'woocommerce' ),
		    'default'	 => '',
		    'desc_tip'	 => true
		),
    'sandbox_secKey'	 => array(
        'title'		 => __( 'Sandbox Security Key', 'woocommerce' ),
        'type'		 => 'text',
        'description'	 => __( 'Get your Security keys from your Akulaku account.', 'woocommerce' ),
        'default'	 => '',
        'desc_tip'	 => true
    ),
    'appId'		 => array(
		    'title'		 => __( 'App ID', 'woocommerce' ),
		    'type'		 => 'text',
		    'description'	 => __( 'Get your API keys from your Akulakuj account.', 'woocommerce' ),
		    'default'	 => '',
		    'desc_tip'	 => true
		),
    'secKey'		 => array(
		    'title'		 => __( 'Security Key', 'woocommerce' ),
		    'type'		 => 'text',
		    'description'	 => __( 'Get your API keys from your Akulaku account.', 'woocommerce' ),
		    'default'	 => '',
		    'desc_tip'	 => true
		),
  );
  }

  // untuk webhook CallbackOBakulaku

  public function check_OB_akulaku_payment_webhook()
  {
    //receive CallbackOBakulaku
    $decode_webhook = json_decode(@file_get_contents("php://inputs"));
    global $woocommerce;
    $order_ref
  }

  function sign($content){
    $appId = $this->appId;
    $secKey = $this->secKey;
    $content = $appId.$secKey.$content;
    $sign =  base64_encode(hash('sha512', $content, true));
    return str_replace(array('+','/','='),array('-','_',''),$sign);
}

    // Get installment info
    function wp_remote_get( $url, $args = array() ) {
      $http = _wp_http_get_object();
      return $http->get( $url, $args );
    }
