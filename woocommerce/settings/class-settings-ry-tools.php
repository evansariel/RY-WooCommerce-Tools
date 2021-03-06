<?php
defined('RY_WT_VERSION') OR exit('No direct script access allowed');

if( !class_exists('WC_Settings_RY_Tools', false) ) {

	class WC_Settings_RY_Tools extends WC_Settings_Page {
		public function __construct() {
			$this->id    = 'rytools';
			$this->label = __('RY Tools', 'ry-woocommerce-tools');

			parent::__construct();
		}

		public function get_sections() {
			$sections = [
				'' => __('Base options', 'ry-woocommerce-tools')
			];

			return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
		}

		public function output() {
			global $current_section;

			$settings = $this->get_settings($current_section);
			WC_Admin_Settings::output_fields($settings);
		}

		public function save() {
			global $current_section;

			$settings = $this->get_settings($current_section);
			WC_Admin_Settings::save_fields($settings);
		
			if( $current_section ) {
				do_action('woocommerce_update_options_' . $this->id . '_' . $current_section);
			}
		}

		public function get_settings($current_section = '') {
			$settings = [];
			if( $current_section == '' ) {
				$settings = [
					[
						'title' => __('ECPay support', 'ry-woocommerce-tools'),
						'type'  => 'title',
						'id'    => 'ecpay_support',
					],
					[
						'title'   => __('Gateway method', 'ry-woocommerce-tools'),
						'desc'    => __('Enable ECPay gateway method', 'ry-woocommerce-tools')
							. (wc_checkout_is_https() ? '' : '<br>' . __('For correct link with ECPay API, need enable secure checkout.', 'ry-woocommerce-tools')),
						'id'      => RY_WT::$option_prefix . 'enabled_ecpay_gateway',
						'type'    => 'checkbox',
						'default' => 'yes'
					],
					[
						'title'   => __('Shipping method', 'ry-woocommerce-tools'),
						'desc'    => __('Enable ECPay shipping method', 'ry-woocommerce-tools')
							. (wc_checkout_is_https() ? '' : '<br>' . __('For correct link with ECPay API, need enable secure checkout.', 'ry-woocommerce-tools')),
						'id'      => RY_WT::$option_prefix . 'enabled_ecpay_shipping',
						'type'    => 'checkbox',
						'default' => 'no',
					],
					[
						'type' => 'sectionend',
						'id' => 'ecpay_support',
					],
					[
						'title' => __('General options', 'ry-woocommerce-tools'),
						'type'  => 'title',
						'id'    => 'general_options',
					],
					[
						'title'   => __('Repay action', 'ry-woocommerce-tools'),
						'desc'    => __('Edable order to change payment', 'ry-woocommerce-tools'),
						'id'      => RY_WT::$option_prefix . 'repay_action',
						'type'    => 'checkbox',
						'default' => 'no',
					],
					[
						'title'   => __('strength password', 'ry-woocommerce-tools'),
						'desc'    => __('Edable the strength password check', 'ry-woocommerce-tools'),
						'id'      => RY_WT::$option_prefix . 'strength_password',
						'type'    => 'checkbox',
						'default' => 'yes',
					],
					[
						'type' => 'sectionend',
						'id' => 'general_options',
					],
					[
						'title' => __('Address options', 'ry-woocommerce-tools'),
						'type'  => 'title',
						'id'    => 'checkout_page_options',
					],
					[
						'title'   => __('Show Country', 'ry-woocommerce-tools'),
						'desc'    => __('Show Country select item', 'ry-woocommerce-tools'),
						'id'      => RY_WT::$option_prefix . 'show_country_select',
						'type'    => 'checkbox',
						'default' => 'no',
					],
					[
						'title'   => __('Last name first', 'ry-woocommerce-tools'),
						'desc'    => __('Show Last name before first name input item', 'ry-woocommerce-tools'),
						'id'      => RY_WT::$option_prefix . 'last_name_first',
						'type'    => 'checkbox',
						'default' => 'no',
					],
					[
						'type' => 'sectionend',
						'id' => 'checkout_page_options',
					]
				];
			}

			return apply_filters('woocommerce_get_settings_' . $this->id, $settings, $current_section);
		}
	}
}

return new WC_Settings_RY_Tools();
