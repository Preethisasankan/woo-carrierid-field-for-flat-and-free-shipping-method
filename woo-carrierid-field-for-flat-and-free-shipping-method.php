<?php
/**
 * Plugin Name:       Woocommerce carrier id for flat & free shipping methods
 * Description:       To add an extra field for carrier id on flat rate & free shipping methods and this carried ID is assigned on each processed order.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Preethi Sasankan
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       woocommerce-carrier-id
 * Domain Path:       /languages
 */
/*
Woocommerce carrier id for flat & free shipping methods is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Woocommerce carrier id for flat & free shipping methods is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Woocommerce carrier id for flat & free shipping methods.
*/
// if ( ! defined( WPINC ) ) {
// die;
// }
/**
 * This function is to define activation action
 */
function wci_init() {
	/** Add languangue text domain */
	load_plugin_textdomain( 'woocommerce-carrier-id', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

}
register_activation_hook( __FILE__, 'wci_init' );

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	/** Carrier field settings array
	 *
	 * @return array $array settings array
	 */
	function wci_carrier_id() {
		return array(
			'title'       => __( 'Carrier ID', 'woocommerce-carrier-id' ),
			'type'        => 'text',
			'placeholder' => 'Carrier ID',
		);
	}

	/** This function is carrier id custom field for flat rate Shipping method
	 *
	 * @param SettingFormField $settings
	 *
	 * @return array $update_setting_with_carrier_id settings array
	 */
	function wci_add_carrier_id_flar_rate( $settings ) {
		$carried_field                  = wci_carrier_id();
		$update_setting_with_carrier_id = array();
		// Setting have multiple arrays
		if ( is_array( $settings ) ) {
			foreach ( $settings as $settingkey => $setting ) {
                $update_setting_with_carrier_id[ $settingkey ] = $setting;
                // to place the custom field after cost
				if ( $settingkey == 'cost' ) {
					$update_setting_with_carrier_id['_carrier_id'] = $carried_field;

				}
			}
		}

		return $update_setting_with_carrier_id;
	}
	add_filter( 'woocommerce_shipping_instance_form_fields_flat_rate', 'wci_add_carrier_id_flar_rate', 10, 1 );
	/** This function is carrier id custom field for Free Shipping method
	 *
	 * @param SettingFormField $settings
	 *
	 * @return array $update_setting_with_carrier_id settings array
	 */
	function wci_add_carrier_id_free_shipping( $settings ) {
		$carried_field           = wci_carrier_id();
		$settings['_carrier_id'] = $carried_field;

		return $settings;
	}
	add_filter( 'woocommerce_shipping_instance_form_fields_free_shipping', 'wci_add_carrier_id_free_shipping', 10, 1 );
	/** This function is save the corresponding carrier id to order on processing
	 *
	 * @param OrderId $orderid
	 */
	function wci_woocommerce_order_status_processing( $orderid ) {
		$shipping_methods = wc_get_chosen_shipping_method_ids();
		$chosen_shipping  = WC()->session->get( 'chosen_shipping_methods' );
		$slug             = str_replace( ':', '_', $chosen_shipping[0] );
		if ( is_array( $shipping_methods ) && ( in_array( 'flat_rate', $shipping_methods ) || in_array( 'free_shipping', $shipping_methods ) ) ) {
				$wci_method = get_option( 'woocommerce_' . $slug . '_settings' );
				$carrier_id = $wci_method['_carrier_id'];
		}
		update_post_meta( $orderid, '_carrier_id', $carrier_id );
	}


	add_action( 'woocommerce_order_status_processing', 'wci_woocommerce_order_status_processing', 10, 1 );
}