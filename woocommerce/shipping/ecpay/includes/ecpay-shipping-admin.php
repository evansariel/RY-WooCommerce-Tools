<?php
defined('RY_WT_VERSION') OR exit('No direct script access allowed');

class RY_ECPay_Shipping_admin {
	public static function init() {
		include_once(RY_WT_PLUGIN_DIR . 'woocommerce/admin/meta-boxes/ecpay-shipping-meta-box.php');

		add_filter('woocommerce_shipping_address_map_url_parts', [__CLASS__, 'fix_cvs_map_address']);

		add_filter('woocommerce_admin_order_actions', [__CLASS__, 'add_admin_order_actions'], 10, 2);
		add_filter('woocommerce_admin_shipping_fields', [__CLASS__, 'set_cvs_shipping_fields']);
		add_action('woocommerce_shipping_zone_method_status_toggled', [__CLASS__, 'check_can_enable'], 10, 4);
		add_action('woocommerce_update_options_shipping_options', [__CLASS__, 'check_ship_destination']);
		add_filter('woocommerce_order_actions', [__CLASS__, 'add_order_actions']);
		add_action('woocommerce_order_action_get_new_cvs_no', ['RY_ECPay_Shipping_Api', 'get_cvs_code']);
		add_action('woocommerce_order_action_get_new_cvs_no_cod', ['RY_ECPay_Shipping_Api', 'get_cvs_code_cod']);
		add_action('woocommerce_order_action_send_at_cvs_email', ['RY_ECPay_Shipping', 'send_at_cvs_email']);
		add_action('wp_ajax_RY_ECPay_Shipping_print', ['RY_ECPay_Shipping_Api', 'print_info']);

		add_action('admin_enqueue_scripts', [__CLASS__, 'add_scripts']);
		add_action('add_meta_boxes', ['RY_ECPay_Shipping_Meta_Box', 'add_meta_box'], 40, 2);
	}

	public static function fix_cvs_map_address($address) {
		if( isset($address['cvs_address']) ) {
			$address = [
				$address['cvs_address']
			];
		}
		return $address;
	}

	public static function add_admin_order_actions($actions, $object) {
		if( $object->has_status(['ry-at-cvs']) ) {
			$actions['complete'] = [
				'url' => wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id=' . $object->get_id()), 'woocommerce-mark-order-status'),
				'name' => __('Complete', 'woocommerce'),
				'action' => 'complete',
			];
		}

		return $actions;
	}

	public static function set_cvs_shipping_fields($shipping_fields) {
		global $theorder;

		$shipping_method = false;
		if( !empty($theorder) ) {
			$items_shipping = $theorder->get_items('shipping');
			if( count($items_shipping) ) {
				$items_shipping = array_shift($items_shipping);
				$shipping_method = RY_ECPay_Shipping::get_order_support_shipping($items_shipping);
			}
			if( $shipping_method !== false ) {
				unset($shipping_fields['company']);
				unset($shipping_fields['address_1']);
				unset($shipping_fields['address_2']);
				unset($shipping_fields['city']);
				unset($shipping_fields['postcode']);
				unset($shipping_fields['country']);
				unset($shipping_fields['state']);
				$shipping_fields['cvs_store_ID'] = [
					'label' => __('Store ID', 'ry-woocommerce-tools'),
					'show' => false
				];
				$shipping_fields['cvs_store_name'] = [
					'label' => __('Store Name', 'ry-woocommerce-tools'),
					'show' => false
				];
				$shipping_fields['cvs_store_address'] = [
					'label' => __('Store Address', 'ry-woocommerce-tools'),
					'show' => false
				];
				$shipping_fields['cvs_store_telephone'] = [
					'label' => __('Store Telephone', 'ry-woocommerce-tools'),
					'show' => false
				];
				$shipping_fields['phone'] = [
					'label' => __('Phone', 'woocommerce')
				];
			}
		}
		return $shipping_fields;
	}

	public static function check_can_enable($instance_id, $method_id, $zone_id, $is_enabled) {
		if( array_key_exists($method_id, RY_ECPay_Shipping::$support_methods) ) {
			if( $is_enabled == 1 ) {
				if( 'billing_only' === get_option('woocommerce_ship_to_destination') ) {
					global $wpdb;

					$wpdb->update(
						$wpdb->prefix . 'woocommerce_shipping_zone_methods',
						[
							'is_enabled' => 0
						],
						[
							'instance_id' => absint($instance_id)
						]
					);
				}
			}
		}
	}

	public static function check_ship_destination() {
		global $wpdb;
		if( 'billing_only' === get_option('woocommerce_ship_to_destination') ) {
			add_action('woocommerce_sections_shipping', [__CLASS__, 'shipping_destination_notice']);

			RY_WT::update_option('ecpay_shipping_cvs_type', 'disable');
			foreach( ['ry_ecpay_shipping_cvs_711', 'ry_ecpay_shipping_cvs_hilife', 'ry_ecpay_shipping_cvs_family'] as $method_id ) {
				$wpdb->update(
					$wpdb->prefix . 'woocommerce_shipping_zone_methods',
					[
						'is_enabled' => 0
					],
					[
						'method_id' => $method_id,
					]
				);
			}
		} else {
			if( RY_WT::get_option('ecpay_shipping_cvs_type') == 'disable' ) {
				RY_WT::update_option('ecpay_shipping_cvs_type', 'C2C');
			}
		}
	}

	public static function shipping_destination_notice() {
		echo '<div id="message" class="error inline"><p><strong>' . esc_html(__('All cvs shipping methods set to disable.', 'ry-woocommerce-tools')) . '</strong></p></div>';
	}

	public static function add_order_actions($order_actions) {
		global $theorder;
		if( !is_object($theorder) ) {
			$theorder = wc_get_order($post->ID);
		}

		foreach( $theorder->get_items('shipping') as $item_id => $item ) {
			if( RY_ECPay_Shipping::get_order_support_shipping($item) !== false ) {
				$order_actions['get_new_cvs_no'] = __('Get new CVS payment no', 'ry-woocommerce-tools');
				if( $theorder->get_payment_method() == 'cod' ) {
					$order_actions['get_new_cvs_no_cod'] = __('Get new CVS payment no with cod', 'ry-woocommerce-tools');
				}
				if( $theorder->has_status(['ry-at-cvs']) ) {
					$order_actions['send_at_cvs_email'] = __('Resend at cvs notification', 'ry-woocommerce-tools');
				}
			}
		}
		return $order_actions;
	}

	public static function add_scripts() {
		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if( in_array($screen_id, ['shop_order', 'edit-shop_order']) ) {
			wp_enqueue_style('ry-shipping-admin-style', RY_WT_PLUGIN_URL . 'style/admin/ry_shipping.css', [], RY_WT_VERSION);
			wp_enqueue_script('ry-ecpay-shipping-admin', RY_WT_PLUGIN_URL . 'style/js/admin/ry_ecpay_shipping.js', ['jquery'], RY_WT_VERSION);
		}
	}
}
