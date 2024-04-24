<?php
/**
* @since             1.0.0
* @package           Postalchile_Woo_Oficial
*
* @wordpress-plugin
* Plugin Name: 		 Postal Chile
* Plugin URI: 		 https://postalchile.cl/
* Description: 		 Soporte oficial de Postal Chile para Woocommerce
* Version: 			 1.0.0
* Author: 			 SGD Media Group
* Author URI: 		 https://grupo-sgd.com/
* License URI:		 http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:		 postalchile
*/

if ( ! defined( 'WPINC' ) )
	die;

define( 'POSTALCHILE_WOO_OFICIAL_VERSION', '1.0.0' );

function activate_postalchile() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-postalchile-activator.php';
	Postalchile_Activator::activate();
}
function deactivate_postalchile() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-postalchile-deactivator.php';
	Postalchile_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_postalchile' );
register_deactivation_hook( __FILE__, 'deactivate_postalchile' );

require plugin_dir_path( __FILE__ ) . 'includes/class-postalchile.php';

$woocommerce_is_present = false;

$all_plugins = apply_filters('active_plugins', get_option('active_plugins'));

if (stripos(implode($all_plugins), 'woocommerce.php')) {

	function postalchile_include_shipping_method() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-postalchile-shipping-method.php';
	}
	add_action( 'woocommerce_shipping_init', 'postalchile_include_shipping_method' );

	function postalchile_add_shipping_method( $methods ) {
		$methods[] = 'Postalchile_Shipping_Method';
		return $methods;
	}

	add_filter( 'woocommerce_shipping_methods', 'postalchile_add_shipping_method' );
}

function run_postalchile() {

	$plugin = new Postalchile();
	$plugin->run();

}

run_postalchile();

if(!function_exists('dd')) {
    function dd( $data ) {
        print_r($data);
        exit;
    }
}