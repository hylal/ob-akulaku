<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Akulaku
 *
 * This gateway is used for processing Akulaku online payment
 *
 * Copyright (c) 
 *
 * This script is only free to the use for merchants of OB Fit. If
 * you have found this script useful a small recommendation as well as a
 * comment on merchant form would be greatly appreciated.
 *
 * @class       WC_Gateway_OB_Akulaku
 * @extends     OBakulaku_Payment_Gateway
 * @package     Wcobakulaku/Classes/Payment
 * @author      Hilaludin Wahid
 * @located at  /includes/daftarpg
 */

class WC_Gateway_OB_Akulaku extends OBakulaku_Payment_Gateway {
    var $sub_id = 'ob_akulaku';
    public function __construct() {
        parent::__construct();
        $this->method_title = 'Akulaku';
        $this->payment_method = 'V1';
        //payment logo
        $this->icon = plugins_url('assets/logoakulaku.png', dirname(__FILE__) ); 
    }
}

?>