<?php
defined('RY_WT_VERSION') OR exit('No direct script access allowed');

final class RY_WT_update {
	public static function update() {
		global $wpdb;

		$now_version = RY_WT::get_option('version');

		if( $now_version === FALSE ) {
			$now_version = '0.0.0';
		}
		if( $now_version == RY_WT_VERSION ) {
			return;
		}

		if( version_compare($now_version, '0.0.7', '<' ) ) {
			if( !wp_next_scheduled('ry_check_ntp_time') ) {
				RY_WT::update_option('ntp_time_error', false);
				wp_schedule_event(time(), 'daily', 'ry_check_ntp_time');
			}
			RY_WT::update_option('version', '0.0.7');
		}

		if( version_compare($now_version, '0.0.18', '<' ) ) {
			RY_WT::update_option('last_name_first', RY_WT::get_option('name_merged'));
			RY_WT::delete_option('one_row_address');
			RY_WT::delete_option('name_merged');
			RY_WT::update_option('version', '0.0.18');
		}

		if( version_compare($now_version, '0.0.23', '<' ) ) {
			@set_time_limit(300);
			$meta_rows = $wpdb->get_results(
				"SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ('_ecpay_atm_ExpireDate', '_ecpay_barcode_ExpireDate', '_ecpay_cvs_ExpireDate')"
			);

			foreach ( $meta_rows as $meta_row ) {
				if( strpos($meta_row->meta_value, 'T') === FALSE ) {
					$time = new DateTime($meta_row->meta_value, new DateTimeZone('Asia/Taipei'));
					update_metadata_by_mid('post', $meta_row->meta_id, $time->format(DATE_ATOM));
				}
			}

			$meta_rows = $wpdb->get_results(
				"SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_shipping_cvs_info'"
			);
			foreach ( $meta_rows as $meta_row ) {
				$cvs_info_list = maybe_unserialize($meta_row->meta_value);
				if( is_array($cvs_info_list) ) {
					foreach( $cvs_info_list as $key => $item ) {
						if( strpos($item['edit'], 'T') === FALSE ) {
							$time = new DateTime($item['edit'], new DateTimeZone('Asia/Taipei'));
							$cvs_info_list[$key]['edit'] = $time->format(DATE_ATOM);
						}
						if( strpos($item['create'], 'T') === FALSE ) {
							$time = new DateTime($item['create'], new DateTimeZone('Asia/Taipei'));
							$cvs_info_list[$key]['create'] = $time->format(DATE_ATOM);
						}

						update_metadata_by_mid('post', $meta_row->meta_id, $cvs_info_list);
					}
				}
			}

			RY_WT::update_option('version', '0.0.23');
		}

		if( version_compare($now_version, '0.0.24', '<' ) ) {
			@set_time_limit(300);
			$meta_rows = $wpdb->get_results(
				"SELECT meta_id, post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_shipping_cvs_store_ID'"
			);
			foreach ( $meta_rows as $meta_row ) {
				$cvs_store_address = get_metadata('post', $meta_row->post_id, '_shipping_cvs_store_address', true);
				if( empty($cvs_store_address) ) {
					$shipping_address_1 = get_metadata('post', $meta_row->post_id, '_shipping_address_1', true);
					update_metadata('post', $meta_row->post_id, '_shipping_cvs_store_address', $shipping_address_1);
				}

				$cvs_info_list = get_metadata('post', $meta_row->post_id, '_shipping_cvs_info', true);
				if( is_array($cvs_info_list) ) {
					foreach( $cvs_info_list as $key => $item ) {
						$cvs_info_list[$key]['store_ID'] = $meta_row->meta_value;
					}
					update_metadata('post', $meta_row->post_id, '_shipping_cvs_info', $cvs_info_list);
				}

				update_metadata('post', $meta_row->post_id, '_shipping_company', '');
				update_metadata('post', $meta_row->post_id, '_shipping_address_2', '');
				update_metadata('post', $meta_row->post_id, '_shipping_city', '');
				update_metadata('post', $meta_row->post_id, '_shipping_state', '');
				update_metadata('post', $meta_row->post_id, '_shipping_postcode', '');
			}

			RY_WT::update_option('version', '0.0.24');
		}

		if( version_compare($now_version, '0.0.31', '<' ) ) {
			if( 'billing_only' === get_option('woocommerce_ship_to_destination') ) {
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
			}

			RY_WT::update_option('version', '0.0.31');
		}

		if( version_compare($now_version, '1.0.13.1', '<' ) ) {
			RY_WT::update_option('version', '1.0.13.1');
		}
	}
}
