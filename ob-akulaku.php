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

    if (!define('ASBPATH')) {
      die;
    }

// cek WooCommerce

    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
      echo "<div class='error notice'><p>Woocommerce has to be installed and active to use the the HCI Payments Gateway</b> plugin</p></div>";
    return;
  }

  // memulai plugins
  add_ction( 'plugin_loaded', 'exe_OB_akulaku_payment_gateway' );
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
        $this->icon apply_filters( ' woocommerce_obakulaku_icon', plugins_url( 'public/image/logo-al.png', __FILE__ ) );
        $this->has_fields = true;
        $this->method_title = 'OB Akulaku';
        $this->method_description = 'Integrasi Payment gateway ke akulaku.';

        // load ke isian kolom
        $this->init_form_fields();
        // load settings
        $this->init_settings():

        // Mendapatkan data nilai settings
        $this->title		 = $this->get_option( 'title' );
        $this->description	 = $this->get_option( 'description' );
        $this->enabled		 = $this->get_option( 'enabled' );
        $this->sandbox		 = $this->get_option( 'sandbox' );
        $this->environment	 = $this->sandbox == 'no' ? 'production' : 'sandbox';
        $this->appId	 = $this->sandbox == 'no' ? $this->get_option( 'appId' ) : $this->get_option( 'sandbox_appId' );
        $this->secKey	 = $this->sandbox == 'no' ? $this->get_option( 'secKey' ) : $this->get_option( 'sandbox_secKey' );
        




      }
    }
  }
