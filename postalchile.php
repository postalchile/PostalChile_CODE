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

	//add_action('admin_menu', 'register_postalchile_woocommerce_submenu_page');

	function register_postalchile_woocommerce_submenu_page() {
	    add_submenu_page( 'woocommerce', 'Postal Chile', 'Postal Chile', 'manage_options', 'postal-chile', 'postalchile_woocommerce_submenu_page' ); 
	}

	function postalchile_woocommerce_submenu_page() {
	    echo '<h3>Prueba Postal Chile</h3> <a href="'.get_admin_url().'admin.php?page=wc-settings&tab=shipping&section=postalchile-shipping-method">Ir a la configuración</a>';

        $settings   = json_decode(json_encode(get_option( 'woocommerce_postalchile-shipping-method_settings' )));

        $api        = new Postalchile_API();

        $length 	= 78;
        $width  	= 34;
        $height 	= 20;
        $weight 	= 18.1;

        $state  	= 'bio bio';//$settings->remit_region;
        $city   	= 'concepcion'; //$settings->remit_comuna;

        $send  		= [
            'tipo_envio'        => $settings->tipo_envio,
            'tipo_servicio'     => $settings->tipo_servicio,
            'region_origen'     => $settings->remit_region,
            'comuna_origen'     => $settings->remit_comuna,
            'region_destino'    => $state,
            'comuna_destino'    => $city,
            'largo'             => $length,
            'ancho'             => $width,
            'alto'              => $height,
            'peso'              => $weight
        ];

        $settings   	= $api->set_api_json_request( 'cotizar_envio', $send );
        $response 		= $api->cotizar_envio($send);
		$response_data 	= json_decode($response);

        $test_data = json_decode(json_encode([
        	[
        		'title' 	=> 'Datos enviados',
        		'content' 	=> $settings
        	],
        	[
        		'title' 	=> 'Datos recibidos',
        		'content' 	=> $response_data
        	]
        ]));

        echo '<div style="display: flex;">';

        foreach($test_data as $data) :

        	echo '<div style="padding: 1rem; background-color: #fff; width: 49%; margin: 1rem 0.5%; display: inline-block;"><table width="100%"><thead><tr><th colspan="2">'.$data->title.'</th></tr></thead><tbody>';

            foreach($data->content as $key=>$value) {
            	if(is_object($value)) {
            		foreach($value as $skey=>$svalue)
        				echo '<tr align="left"><th><b>'.$skey.':</b></th><td align="left">'.$svalue.'</td></tr>';
            	} else {

            		if($key=='request')
            			$value = '<pre>'.json_encode(json_decode($value), JSON_PRETTY_PRINT).'</pre>';

        			echo '<tr align="left"><th><b>'.$key.':</b></th><td align="left">'.$value.'</td></tr>';
            	}
            }
    		echo '</tbody></table></div>';
    	endforeach;

    	echo '</div>';
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