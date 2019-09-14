<?php

if (!defined('ABSPATH')) {
    exit;
}
/**
 * Akulaku Admin Global Settings Payment
 *
 * This file is used for creating the Akulaku global configuration
 *
 * Copyright (c) OB Fitness Health
 *
 * This script is only free to the use for merchants of Akulaku. If
 * you have found this script useful a small recommendation as well as a
 * comment on merchant form would be greatly appreciated.
 *
 * @class       Akulaku_Settings
 * @package     Akulaku/Classes
 * @category    Class
 * @author      OBFIT
 * @located at  /includes/admin/
 */

class Akulaku_settings {
    public static $tab_name = 'wcobakulaku_settings';
    public static $options_prefix = 'wcobakulaku';
    public static function init() {
        $request = $_REQUEST;
        add_filter('woocommerce_settings_tabs_array', array(__CLASS__, 'add_wcobakulaku_settings_tab'), 50);
		add_action('woocommerce_settings_tabs_wcobakulaku_settings', array(__CLASS__, 'wcobakulaku_settings_page'));
		add_action('woocommerce_update_options_wcobakulaku_settings', array(__CLASS__, 'update_wcobakulaku_settings'));
		
    }

    	/**
	 * Validate the data for Akulaku global configuration
	 *
	 * $param array $request
	 * @return mixed
	 */
    public static function validate_configuration($request) {
        foreach ($request as $k => $v) {
            $key = str_replace('wcobakulaku_', '', $k);
            $options[$key] = $v;
        }
        return '';
    }

    /**
	 * Adds Akulaku Tab to WooCommerce
	 * Calls from the hook "woocommerce_settings_tabs_array"
	 *
	 * @param array $woocommerce_tab
	 * @return array $woocommerce_tab
	 */
	public static function add_wcobakulaku_settings_tab($woocommerce_tab) {
		$woocommerce_tab[self::$tab_name] = 'Akulaku ' . __('Global Configuration', 'wc-wcobakulaku');
		return $woocommerce_tab;
    }
    /**
	 * Adds setting fields for  Akulaku global configuration

	 * @param none
	 * @return void
	 */
    public static function wcobakulaku_settings_fields() {
		global $wcobakulaku_payments;
		//add_action( 'admin_footer', 'wc_wcobakulaku_custom_admin_redirect' );
		//$admin_url = admin_url( 'admin.php?page=wc-wcobakulaku-admin' );
		//$logpath = ((WOOCOMMERCE_VERSION > '2.2.0' ) ? wc_get_log_file_path( 'wcobakulakupayments' ) : "woocommerce/logs/novalnetpayments-".sanitize_file_name( wp_hash( 'novalnetpayments' )));
		$settings = apply_filters('woocommerce_' . self::$tab_name, array(
			array(
				'title' => 'Wcobakulaku ' . __('Global Configuration', 'wc-wcobakulaku'),
				'id' => self::$options_prefix . '_global_settings',
				'desc' => __('Selamat datang di pengaturan global akulaku. Untuk dapat menggunakan akulaku payment channel, mohon mengisi form di bawah ini.
                <br \>  untuk mendapatkan api dan  app Id mohon kontak  <a href="mailto:hilal@ob-fit.com">hilal@ob-fit.com</a>', 'wc-wcobakulaku'),
				'type' => 'title',
				'default' => '',
			),
			array(
				'title' => __('App Id', 'wc_wcobakulaku'),
				'desc' => '<br />' . __('masukkan kode merchant anda.', 'wc-wcobakulaku'),
				'id' => self::$options_prefix . '_appId',
				'type' => 'text',
				'default' => '',
			),
			array(
				'title' => __('API Key', 'wc_wcobakulaku'),
				'desc' => '<br />' . __(' Dapatkan API Key <a href=https://www.ob-fit.com>disini</a></small>.', 'wc-wcobakulaku'),
				'id' => self::$options_prefix . '_secKey',
				'type' => 'text',
				'css' => 'width:25em;',
				'default' => '',
			),
			array(
				'title' => __('Akulaku Endpoint', 'wc_wcobakulaku'),
				'desc' => '<br />' . __('Akulaku endpoint API. Mohon isi merchant code dan api key sebelum mengakses endpoint.', 'wc-wcobakulaku'),
				'id' => self::$options_prefix . '_endpoint',
				'type' => 'text',
				'css' => 'width:25em;',
				'default' => '',
			),
			array(
				'title' => __('Akulaku Debug', 'wc_wcobakulaku'),
				'desc' => '<br />' . sprintf(__('Akulaku Log dapat digunakan untuk melihat event, seperti notifikasi pembayaran.
                <code>%s</code> ', 'woothemes'), wc_get_log_file_path('wcobakulaku')),
				'id' => self::$options_prefix . '_debug',
				'type' => 'checkbox',
				'default' => 'no',
			),
		));
		return apply_filters('woocommerce_' . self::$tab_name, $settings);
	}
    
    

    /**
	 * Adds settings fields to the individual sections
	 * Calls from the hook "woocommerce_settings_tabs_" {tab_name}
	 *
	 * @param none
	 * @return void
	 */
    public static function wcobakulaku_settings_page() {
		woocommerce_admin_fields(self::wcobakulaku_settings_fields());
	}

	/**
	 * Updates settings fields from individual sections
	 * Calls from the hook "woocommerce_update_options_" {tab_name}
	 *
	 * @param none
	 * @return void
	 */
	public static function update_wcobakulaku_settings() {
		woocommerce_update_options(self::wcobakulaku_settings_fields());
	}





}
Akulaku_Settings::init();