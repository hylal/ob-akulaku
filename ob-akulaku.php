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
  add_ction( 'plugin_loaded', 'OB_akulaku_payment_gateway' );
  
