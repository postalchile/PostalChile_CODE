<?php
/**
* @since             1.0.0
* @package           Postalchile_Woo_Oficial
*
* @wordpress-plugin
* Plugin Name: 		 Postal Chile
* Plugin URI: 		 https://postalchile.cl/
* Description: 		 Soporte oficial de Postal Chile para Woocommerce
* Version: 			 2.0.0
* Author: 			 SGD Media Group
* Author URI: 		 https://grupo-sgd.com/
* License URI:		 http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:		 postalchile
*/

if ( ! defined( 'WPINC' ) )
	die;

define( 'POSTALCHILE_WOO_OFICIAL_VERSION', '2.0.0' );

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

    add_action('admin_menu', 'register_postalchile_woocommerce_submenu_page');

	function register_postalchile_woocommerce_submenu_page() {
	    add_submenu_page( 'woocommerce', 'Postal Chile', 'Postal Chile', 'manage_options', 'postal-chile', 'postalchile_woocommerce_submenu_page' ); 
	}

	function postalchile_woocommerce_submenu_page() {
        include plugin_dir_path( __FILE__ ) . 'admin/templates/api-test.php';
	}

	add_filter( 'woocommerce_shipping_methods', 'postalchile_add_shipping_method' );

	function postalchile_checkout_save() {
		if (!wp_verify_nonce($_POST['postalchile_nonce'], 'postalchile_checkout_nonce')) {
		    die('¡Acceso no autorizado!');
		}
		update_option('postalchile_checkout',$_POST['postalchile_checkout'],false);
	    wp_redirect(admin_url('admin.php?page=postal-chile&tab=checkout'));
	    die();
	}

	add_action( 'admin_post_postalchile_checkout', 'postalchile_checkout_save' );

	function postalchile_default_checkout() {
		$fields = [
		    'state' => [
	            'label'         => 'Región',
	            //'placeholder'   => 'Seleccione una región',
	            'priority'      => '42'
		    ],
		    'city' => [
	            'label'         => 'Comuna',
	            //'placeholder'   => 'Seleccione una región',
	            'priority'      => '43'
		    ],
	        'address_1' => [
	            'label'         => 'Dirección',
	            'placeholder'   => 'Nombre de la calle',
	            //'priority'      => '42'
		    ],
	        'address_2' => [
	            'label'         => 'Número',
	            'placeholder'   => 'Número',
	            //'priority'      => '42'
		    ],
	        'address_3' => [
	            'label'         => 'Complemento',
	            'placeholder'   => 'N° Depto, Villa, Población, Sector, Etc',
	            'priority'      => '62'
		    ],
	        'company' => [
	            'label'         => 'Rut',
	            //'placeholder'   => 'Ingresa tu rut'
	            //'priority'      => '42'
		    ],
	        'phone' => [
	            'label'         => 'Teléfono celular',
	            'description'	=> 'Debe comenzar con 9 y contener 9 dígitos (Ej: 9XXXXXXXX)'
	            //'placeholder'   => 'N° Depto, Villa, Población, Sector, Etc',
	            //'priority'      => '42'
		    ],
		    /*
	        'field' => [
	            'label'         => 'Región',
	            'placeholder'   => 'N° Depto, Villa, Población, Sector, Etc',
	            'priority'      => '42'
		    ]
		    */
		];

		return $fields;
	}
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