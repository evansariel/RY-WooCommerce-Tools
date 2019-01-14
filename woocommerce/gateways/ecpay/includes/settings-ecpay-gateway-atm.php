<?php
defined('RY_WT_VERSION') OR exit('No direct script access allowed');

return [
	'enabled' => [
		'title' => __('Enable/Disable', 'woocommerce'),
		'label' => sprintf(__('Enable %s', 'ry-woocommerce-tools'), $this->method_title),
		'type' => 'checkbox',
		'default' => 'no',
	],
	'inpay' => [
		'title' => __('Inpay', 'ry-woocommerce-tools'),
		'label' => sprintf(__('Enable inpay', 'ry-woocommerce-tools'), $this->method_title),
		'type' => 'checkbox',
		'default' => 'no',
	],
	'title' => [
		'title' => __('Title', 'woocommerce'),
		'type' => 'text',
		'default' => $this->method_title,
		'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
		'desc_tip' => true,
	],
	'description' => [
		'title' => __( 'Description', 'woocommerce' ),
		'type' => 'text',
		'default' => $this->order_button_text,
		'desc_tip' => true,
		'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
	],
	'min_amount' => [
		'title' => __('Minimum order amount', 'ry-woocommerce-tools'),
		'type' => 'number',
		'default' => 0,
		'placeholder' => 0,
		'description' => __('0 to disable minimum amount limit.', 'ry-woocommerce-tools'),
		'custom_attributes' => [
			'min' => 0,
			'step' => 1
		]
	],
	'max_amount' => [
		'title' => __('Maximum order amount', 'ry-woocommerce-tools'),
		'type' => 'number',
		'default' => 0,
		'placeholder' => 0,
		'description' => __('0 to disable maximum amount limit.', 'ry-woocommerce-tools'),
		'custom_attributes' => [
			'min' => 0,
			'step' => 1
		]
	],
	'expire_date' => [
		'title' => __('Payment deadline', 'ry-woocommerce-tools'),
		'type' => 'number',
		'default' => 3,
		'placeholder' => 3,
		'description' => __('ATM allowable payment deadline from 1 day to 60 days.', 'ry-woocommerce-tools'),
		'custom_attributes' => [
			'min' => 1,
			'max' => 60,
			'step' => 1
		]
	]
];
