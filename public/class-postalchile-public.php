<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @since      1.0.0
 *
 * @package    Postalchile
 * @subpackage Postalchile/public
 * @author     Postalchile
 */
class Postalchile_Public {

	const SHIPPING_KEY = 'shipping';
	const BILLING_KEY = 'billing';
	const PRIORITY_KEY = 'priority';
	const REQUIRED_KEY = 'required';
	const ADDRESS_1_KEY = 'address_1';
	const ADDRESS_2_KEY = 'address_2';
	const SHIPPING_ADDRESS_3_KEY = 'shipping_address_3';
	const BILLING_ADDRESS_3_KEY = 'billing_address_3';
	const LABEL_KEY = 'label';
	const PLACEHOLDER_KEY = 'placeholder';

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name 	= $plugin_name;
		$this->version 		= $version;
	}
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/postalchile.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/css/postalchile.css', array(), $this->version, 'all' );

		wp_localize_script( $this->plugin_name, 'woocommerce_postalchile', array(
	        'base_url'	=> plugin_dir_url( __FILE__ ),
	        'nonce'  	=> wp_create_nonce( 'cwo-clxp-ajax-nonce' )
    	) );
	}
	public function postalchile_change_city_to_dropdown( $fields ) {	

		$api 		= new Postalchile_API();
		$state 		= WC()->checkout->get_value('billing_state');

		if (isset($fields[self::SHIPPING_KEY])){

			$options 	= $api->comunas( $state );
			$city_args = wp_parse_args( array(
				'type' => 'select',
				'options' => $options,
				'input_class' => array(
					'wc-enhanced-select',
				)
			), $fields[self::SHIPPING_KEY]['shipping_city'] );
			$fields['shipping_state'][self::PRIORITY_KEY] = '65';
			$fields[self::SHIPPING_KEY]['shipping_city'] = $city_args;
			$fields[self::BILLING_KEY]['billing_city'] = $city_args; // Also change for billing field

			wc_enqueue_js( "
			jQuery( ':input.wc-enhanced-select' ).filter( ':not(.enhanced)' ).each( function() {
				var select2_args = { minimumResultsForSearch: 5 };
				jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
			});" );

		}
		return $fields;
	}
	public function checkout_fields_override( $fields ) {

		unset($fields[self::BILLING_KEY]['billing_postcode']);
		unset($fields[self::SHIPPING_KEY]['shipping_postcode']);
		
		$base_field =  array(
			'label'     	=> __('Complemento', 'woocommerce'),
			'placeholder'   => _x('N° Depto, Villa, Población, Sector, Etc', self::PLACEHOLDER_KEY, 'woocommerce'),
			'required'  	=> false,
			'class'     	=> array('form-row-wide'),
			'clear'     	=> true,
			'priority'  	=> 62
		);
		$fields[self::SHIPPING_KEY][self::SHIPPING_ADDRESS_3_KEY] = $base_field;
		$fields[self::BILLING_KEY][self::BILLING_ADDRESS_3_KEY] = $base_field;
		$fields[self::SHIPPING_KEY][self::SHIPPING_ADDRESS_3_KEY] = $base_field;
		$fields[self::BILLING_KEY][self::BILLING_ADDRESS_3_KEY] = $base_field;

		$fields['billing']['billing_company']['label'] = 'Rut';
		$fields['billing']['billing_phone']['label'] = 'Teléfono celular';
		$fields['billing']['billing_phone']['description'] = 'Debe comenzar con 9 y contener 9 dígitos (Ej: 9XXXXXXXX)';
		
		return $fields;
	}
	public function custom_checkout_field_update_order_meta($order_id) {
		if ( ! empty( $_POST[self::BILLING_ADDRESS_3_KEY] ) ) {
			update_post_meta( $order_id, self::BILLING_ADDRESS_3_KEY, sanitize_text_field( $_POST[self::BILLING_ADDRESS_3_KEY] ) );
		}
		if ( ! empty( $_POST[self::SHIPPING_ADDRESS_3_KEY] ) ) {
			update_post_meta( $order_id, self::SHIPPING_ADDRESS_3_KEY, sanitize_text_field( $_POST[self::SHIPPING_ADDRESS_3_KEY] ) );
		}
	}
	public function reorder_fields($fields) {

		$fields[self::ADDRESS_1_KEY][self::LABEL_KEY] = 'Dirección';
		$fields[self::ADDRESS_1_KEY][self::PLACEHOLDER_KEY] = 'Nombre de la calle';
		$fields[self::ADDRESS_1_KEY][self::REQUIRED_KEY] = true;
		$fields[self::ADDRESS_2_KEY][self::LABEL_KEY] = 'N&uacute;mero';
		$fields[self::ADDRESS_2_KEY][self::PLACEHOLDER_KEY] = 'Número';
		$fields[self::ADDRESS_2_KEY][self::REQUIRED_KEY] = true;
		$fields['city'][self::LABEL_KEY] = 'Comuna';
		$fields['state'][self::PRIORITY_KEY] = 42;
		$fields['city'][self::PRIORITY_KEY] = 43;
		$fields['email'][self::PRIORITY_KEY] = 22;
		return $fields;
	}
	public function override_postcode_validation( $address_fields ) {	
		unset($address_fields['postcode']);
		return $address_fields;
	}
	public function postalchile_validate_order( $fields, $errors ) {

		$billing_company = $this->validate_rut( $fields[ 'billing_company' ] );

	    if ( $fields[ 'billing_company' ] && $billing_company['error'] ) {
	        $errors->add( 'validation', $billing_company['msj'] );
	    }

		$shipping_company = $this->validate_rut( $fields[ 'shipping_company' ] );

	    if ( $fields[ 'shipping_company' ] && $shipping_company['error'] ) {
	        $errors->add( 'validation', $shipping_company['msj'] );
	    }

		if( preg_match( "/^9[0-9]{8}$/", $fields[ 'billing_phone' ] ) ){
		} else {
			$errors->add( 'validation', 'El número de teléfono debe comenzar con 9 y contener 9 dígitos en total');
		}

	}
 	public function validate_rut($rut) {

        // Verifica que no esté vacio y que el string sea de tamaño mayor a 3 carácteres(1-9)        
        if ((empty($rut)) || strlen($rut) < 3) {
            return array('error' => true, 'msj' => 'RUT vacío o con menos de 3 caracteres.');
        }

        // Quitar los últimos 2 valores (el guión y el dígito verificador) y luego verificar que sólo sea
        // numérico
        $parteNumerica = str_replace(substr($rut, -2, 2), '', $rut);

        if (!preg_match("/^[0-9]*$/", $parteNumerica)) {
            return array('error' => true, 'msj' => 'La parte numérica del RUT sólo debe contener números.');
        }

        $guionYVerificador = substr($rut, -2, 2);
        // Verifica que el guion y dígito verificador tengan un largo de 2.
        if (strlen($guionYVerificador) != 2) {
            return array('error' => true, 'msj' => 'Error en el largo del dígito verificador.');
        }

        // obliga a que el dígito verificador tenga la forma -[0-9] o -[kK]
        if (!preg_match('/(^[-]{1}+[0-9kK]).{0}$/', $guionYVerificador)) {
            return array('error' => true, 'msj' => 'El dígito verificador no cuenta con el patrón requerido');
        }

        // Valida que sólo sean números, excepto el último dígito que pueda ser k
        if (!preg_match("/^[0-9.]+[-]?+[0-9kK]{1}/", $rut)) {
            return array('error' => true, 'msj' => 'Error al digitar el RUT');
        }

        $rutV = preg_replace('/[\.\-]/i', '', $rut);
        $dv = substr($rutV, -1);
        $numero = substr($rutV, 0, strlen($rutV) - 1);
        $i = 2;
        $suma = 0;
        foreach (array_reverse(str_split($numero)) as $v) {
            if ($i == 8) {
                $i = 2;
            }
            $suma += $v * $i;
            ++$i;
        }
        $dvr = 11 - ($suma % 11);
        if ($dvr == 11) {
            $dvr = 0;
        }
        if ($dvr == 10) {
            $dvr = 'K';
        }
        if ($dvr == strtoupper($dv)) {
            return array('error' => false, 'msj' => 'RUT ingresado correctamente.');
        } else {
            return array('error' => true, 'msj' => 'El RUT ingresado no es válido.');
        }
    }
	static function get_states( $states ) {

		$api = new Postalchile_API();
		$states['CL'] = $api->regiones();

		return $states;
	}
	public function empty_checkout_get_value( $valor, $input ){
	    $valor = '';
	    return $valor;
	}
	public function get_settings() {

		$options = get_option( 'woocommerce_postalchile-shipping-method_settings' );

        $defaults = [
            'status_solicitar_envio' => false,
            'status_anular_envio'    => false,
            'tipo_envio'             => 1,
            'tipo_servicio'          => 3
        ];

        $settings = wp_parse_args($options,$defaults);

        if(isset($settings['status_solicitar_envio']) && $settings['status_solicitar_envio'])
            $settings['status_solicitar_envio'] = str_replace('wc-','',$settings['status_solicitar_envio']);

        if(isset($settings['status_anular_envio']) && $settings['status_anular_envio'])
            $settings['status_anular_envio'] = str_replace('wc-','',$settings['status_anular_envio']);

        return json_decode(json_encode($settings));
	}
	public function solicitar_envio( $order_id ) {
		
		$order 		= is_numeric($order_id) ? wc_get_order( $order_id ) : $order_id;
		$order_id 	= is_numeric($order_id) ? $order_id : $order->get_id();

		$method_id 	= reset( $order->get_shipping_methods() )->get_method_id();

		if($method_id!=='postalchile-shipping-method')
			return;

		$api   		= new Postalchile_API();

		$codigo_seguimiento = $order->get_meta('postalchile_codigo_seguimiento');

		if($codigo_seguimiento)
			return;

		$weight = 0;
		$lenght = 0;
		$width  = 0;
		$height = 0;
		$desc 	= [];

		foreach ( $order->get_items() as $item_id => $item ) {

			$product = $item->get_product();

			$desc[] = $item->get_name();

			$weight += (float) $product->get_weight() * $item->get_quantity();
			$lenght += (float) $product->get_length() * $item->get_quantity();
			$width  += (float) $product->get_width() * $item->get_quantity();
			$height += (float) $product->get_height() * $item->get_quantity();
		}

		$weight = wc_get_weight( $weight, 'kg' );

		$data = [
            'dest_rut'              => $order->get_shipping_company(), // Formatear rut
            'dest_nombres'          => $order->get_shipping_first_name(),
            'dest_apellidos'        => $order->get_shipping_last_name(),
            'dest_dir_calle'        => $order->get_shipping_address_1(),
            'dest_dir_numero'       => $order->get_shipping_address_2(),
            'dest_dir_adicional'    => get_post_meta( $order_id, 'billing_address_3', true ), // Pendiente campo shipping_addres_3
            'dest_observaciones'    => $order->get_customer_note(),
            'dest_region'           => $order->get_shipping_state(),
            'dest_comuna'           => $order->get_shipping_city(),
            'dest_fono'             => $order->get_billing_phone(), // Formatear: 9xxxxxxxx
            'dest_mail'             => $order->get_billing_email(),
            'largo'                 => $lenght,
            'ancho'                 => $width,
            'alto'                  => $height,
            'peso'                  => $weight,
            'contenido_descripcion' => implode(',', $desc),
            'contenido_valor'       => $order->get_total(),
            'cliente_codigo_barra'  => 'WC-'.$order_id,
            'cliente_orden_compra'  => $order_id,
        ];

        $response = $api->solicitar_envio( $data );

        if($response) {

        	$response_data = json_decode($response);

        	if($response_data->retorno->codigo==0 && $response_data->servicio->codigo_seguimiento) {

        		$order->add_order_note( 'El N° de seguimiento de tu envío es '.$response_data->servicio->codigo_seguimiento, 1 );

			    $order->update_meta_data( 'postalchile_codigo_seguimiento', $response_data->servicio->codigo_seguimiento );
			    $order->update_meta_data( 'postalchile_valor', $response_data->servicio->valor );
			    $order->update_meta_data( 'postalchile_gestion_url_etiqueta1', $response_data->servicio->gestion_url_etiqueta1 );
			    $order->update_meta_data( 'postalchile_gestion_url_etiqueta2', $response_data->servicio->gestion_url_etiqueta2 );
			    $order->update_meta_data( 'postalchile_gestion_url_nomina', $response_data->servicio->gestion_url_nomina );

			    $order->save();

        	} else {
        		$order->add_order_note( 'Error al crear envío: '.$response_data->retorno->mensaje );
        	}

        } else {
        	// For debug only
        	// $order->add_order_note( $response );
        }
	}
	public function anular_envio($order_id) {

		$api   		= new Postalchile_API();

		$order 		= is_numeric($order_id) ? wc_get_order( $order_id ) : $order_id;
		$order_id 	= is_numeric($order_id) ? $order_id : $order->get_id();

		$codigo_seguimiento = $order->get_meta('postalchile_codigo_seguimiento');

		if(!$codigo_seguimiento)
			return;

        $response = $api->anular_envio( ['codigo_seguimiento'=>$codigo_seguimiento] );

        if($response) {

        	$response_data = json_decode($response);

        	if($response_data->retorno->codigo==0 && $response_data->retorno->mensaje) {

        		$order->add_order_note( 'Tu envío N° '.$codigo_seguimiento.' ha sido anulado', 1 );

			    $order->update_meta_data( 'postalchile_codigo_seguimiento', false );
			    $order->update_meta_data( 'postalchile_valor', false );
			    $order->update_meta_data( 'postalchile_gestion_url_etiqueta1', false );
			    $order->update_meta_data( 'postalchile_gestion_url_etiqueta2', false );
			    $order->update_meta_data( 'postalchile_gestion_url_nomina', false );

			    $order->save();

        	} else {
        		$order->add_order_note( 'Error al anular el envío: '.$response_data->retorno->mensaje );
        	}

        } else {
        	// For debug only
        	// $order->add_order_note( $response );
        }
	}
	public function tracking_envio( $order_id ) {

		$api   		= new Postalchile_API();

		$order 		= is_numeric($order_id) ? wc_get_order( $order_id ) : $order_id;
		$order_id 	= is_numeric($order_id) ? $order_id : $order->get_id();

		$codigo_seguimiento = $order->get_meta('postalchile_codigo_seguimiento');

		if(!$codigo_seguimiento)
			return;

        $response = $api->tracking_envio( [
            'codigo_seguimiento'    => $codigo_seguimiento,
            //'cliente_codigo_barra'  => $codigo_seguimiento
        ] );

        $status = 'En preparación';

        if($response) {

        	$response_data  = json_decode($response);
			
        	if(isset($response_data->estado_actual->gestion_actual) && $response_data->estado_actual->gestion_actual) {

        		$status = $response_data->estado_actual->gestion_actual;

        	} else {

        	}

        } else {
        	// For debug only
        	// $order->add_order_note( $response );
        }

        echo '<div class="wc-block-order-confirmation-order-note">Enviado por Postal Chile<ul><li>Estado del envío: <b>'.$status.'</b></li><li>Código de seguimiento: <b>'.$codigo_seguimiento.'</b></li></ul><p>Puedes confirmar el estado del pedido ingresando a <a href="https://www.postalchile.cl/tra_index.htm#" target="_blank">https://www.postalchile.cl/tra_index.htm#</a></div>';
	}
	public function woocommerce_order_actions( $actions ){

	    $actions['solicitar_envio'] = __('Solicitar envío con Postal Chile', 'postalchile');
	    $actions['anular_envio'] = __('Anular envío con Postal Chile', 'postalchile');

	    return $actions;
	}
	public function admin_tracking_envio( $item_id, $item, $_product ){
		if(method_exists($item, 'get_method_id')) {

			$method_name = $item->get_method_id();

			if($method_name=='postalchile-shipping-method') {

				$order_id 		= $item->get_order_id();
				$order 			= wc_get_order( $order_id );

				$codigo_seguimiento = $order->get_meta('postalchile_codigo_seguimiento');

				if(!$codigo_seguimiento) {
					echo 'Solicitud de envío no creada';
					return;
				}

				$api = new Postalchile_API();

		        $response = $api->tracking_envio( [
		            'codigo_seguimiento'    => $codigo_seguimiento,
		            //'cliente_codigo_barra'  => $codigo_seguimiento
		        ] );

		        $status = 'Pendiente';

		        if($response) {

		        	$response_data  = json_decode($response);
					
		        	if(isset($response_data->estado_actual->gestion_actual) && $response_data->estado_actual->gestion_actual) {

		        		$status = $response_data->estado_actual->gestion_actual;

		        	} else {

		        	}

		        } else {
		        	// For debug only
		        	// $order->add_order_note( $response );
		        }

		        echo '<a href="'.$order->get_meta('postalchile_gestion_url_etiqueta1').'" target="_blank"><b>Etiqueta 1</b></a> | ';
		        echo '<a href="'.$order->get_meta('postalchile_gestion_url_etiqueta2').'" target="_blank"><b>Etiqueta 2</b></a> | ';
		        echo '<a href="'.$order->get_meta('postalchile_gestion_url_nomina').'" target="_blank"><b>Nómina</b></a>';

		        echo '<ul>';
		        echo '<li>Código de seguimiento: <b>'.$codigo_seguimiento.'</b></li>';
		        echo '<li>Valor del envío: <b>'.wc_price($order->get_meta('postalchile_valor')).'</b></li>';
		        echo '<li>Estado del envío: <b>'.$status.'</b></li>';
		        echo '</ul>';
		        echo '<p>Conoce el historial completo ingresando a <a href="https://www.postalchile.cl/tra_index.htm#" target="_blank">https://www.postalchile.cl/tra_index.htm#</a>';

			}
		}
	}
	public function obtener_regiones_handle_ajax_request() {
		
		$response	= array();
		$response['message'] = "Successfull Request";
		$regiones = $this->get_states();
		$response['regiones'] = $regiones;

    	echo json_encode($response);
    	exit;
	}

	public function obtener_comunas_desde_region_handle_ajax_request() {
 		$region	= isset($_POST['region'])? sanitize_text_field($_POST['region']):"";
		$response	= array();
		$response['message'] = "Successfull Request";

		$api 		= new Postalchile_API();
		$comunas 	= $api->comunas( $region );
		$response['comunas'] = $comunas;

    	echo json_encode($response);
    	exit;
	}
}
