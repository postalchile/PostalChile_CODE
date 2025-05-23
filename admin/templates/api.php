<?php
if ( ! defined( 'WPINC' ) )
    die;
?>

<h2>Pruebas de conexión API</h2>
<p>Las siguientes pruebas de conexión determinarán si el plugin está correctamente configurado y que la comunicación con los distintos métodos de la API Postal Chile se encuentren funcionando corretamente, permitiendo solicitar cotizaciones, solicitar envíos y anular envíos.</p>

<?php

$settings   = json_decode(json_encode(get_option( 'woocommerce_postalchile-shipping-method_settings' )));

$api        = new Postalchile_API();

$length 	= 78;
$width  	= 34;
$height 	= 20;
$weight 	= 18.1;

$state  	= 'bio bio';
$city   	= 'concepcion';

// START TEST CONFIGURACION //

$config     = [];


$Postalchile_Shipping_Method = new Postalchile_Shipping_Method();
$fields = $Postalchile_Shipping_Method->get_form_fields();

$errors          = [];
$required_inputs = [];

// START GET API COTIZAR_ENVIO //

$send       = [
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

$api_settings   = $api->set_api_json_request( 'cotizar_envio', $send );
$response       = $api->cotizar_envio($send);
$response_data  = json_decode($response);

// END GET API COTIZAR_ENVIO //

// Verificar campos requeridos
foreach($settings as $key=>$value) :

    $field        = isset($fields[$key]) ? $fields[$key] : $settings->$key;
    $field_title  = isset($field['title']) ? '<span title="'.$key.'">'.$field['title'].'</span>' : $key;
    $field_title .= isset($field['required']) && $field['required'] ? '<span style="color:red;">*</span>' : false;

    if(isset($field['options']) && $field['options'])
        $value = isset($field['options'][$value]) ? $field['options'][$value] : $value;

    if(!$value && isset($field['default']) && $field['default'])
        $value = $field['default'];

    if(isset($field['required']) && $field['required'] && !$value) {
        $required_inputs[] = $field_title;

    }

    $config[$field_title] = $value;
endforeach;
if($required_inputs)
    $errors[] = '<li>Existen campos requeridos que no han sido completados: '.implode(', ', $required_inputs).'</li>';

$rut_validation = Postalchile_Public::validate_rut($settings->remit_rut);

// Verificar rut válido
if(isset($settings->remit_rut) && $settings->remit_rut && $rut_validation['error'])
    $errors[] = '<li>Rut no válido: '.$rut_validation['msj'].'</li>';

// Verificar si comuna corresponde a la región
if(isset($response_data->retorno->codigo) && $response_data->retorno->codigo==41)
    $errors[] = '<li>'.$response_data->retorno->mensaje.'</li>';

// Verificar si el client_id corresponde al usuario
if(isset($response_data->retorno->codigo) && $response_data->retorno->codigo==4)
    $errors[] = '<li>'.$response_data->retorno->mensaje.': Verifica que los campos "API Key", "API Secret", "ID Cliente" y "Usuario" sean válidos.<br>De lo contrario, comunícate con el soporte de Postal Chile.</li>';

// Verificar si el número de teléfono es válido
if( isset($settings->remit_fono) && $settings->remit_fono && !preg_match( "/^9[0-9]{8}$/", $settings->remit_fono ) )
    $errors[] = '<li>El número de teléfono debe comenzar con 9 y contener 9 dígitos en total</li>';

$test_data = json_decode(json_encode([
    [
        'title'     => 'Configuración del plugin <a href="'.$admin_url.'">Editar</a>',
        'content'   => $config
    ]
]));

$alert_color    = !$errors ? 'success' : 'danger';
$alert_content  = !$errors ? 'La configuración parece estar funcionando correctamente' : '<b>Existen errores en la configuración del plugin:</b><ol>'.implode('',$errors).'</ol>Por favor, ingresa a la <a href="'.$admin_url.'">configuración</a> para solucionar los errores.';
$panel_title    = 'Configuración del plugin';

include plugin_dir_path( __FILE__ ) . 'api-test-table.php';

//END TEST CONFIGURACION

// START PANEL COTIZAR ENVIO
$test_data = json_decode(json_encode([
	[
		'title' 	=> 'Datos enviados: Cotizar envío',
		'content' 	=> $api_settings
	],
	[
		'title' 	=> 'Datos recibidos: Cotizar envío',
		'content' 	=> $response_data
	]
]));

// END PANEL COTIZAR ENVIO //

$mensaje        = isset($response_data->retorno->mensaje) ? $response_data->retorno->mensaje : false;
$alert_color    = isset($response_data->retorno->codigo) && $response_data->retorno->codigo==0 ? 'success' : 'danger';
$alert_content  = isset($response_data->retorno->codigo) && $response_data->retorno->codigo==0 ? $mensaje : '<b>Error al crear:</b> '.$mensaje;
$panel_title    = 'Cotización de envío';

include plugin_dir_path( __FILE__ ) . 'api-test-table.php';

$send = [
    'remit_rut'             => $settings->remit_rut,
    'remit_nombres'         => $settings->remit_nombres,
    'remit_apellidos'       => $settings->remit_apellidos,
    'remit_dir_calle'       => $settings->remit_dir_calle,
    'remit_dir_numero'      => $settings->remit_dir_numero,
    'remit_dir_adicional'   => $settings->remit_dir_adicional,
    'remit_observaciones'   => $settings->remit_observaciones,
    'remit_region'          => $settings->remit_region,
    'remit_comuna'          => $settings->remit_comuna,
    'remit_fono'            => $settings->remit_fono,
    'remit_mail'            => $settings->remit_mail,
    'dest_rut'              => '1-9',
    'dest_nombres'          => 'Postal',
    'dest_apellidos'        => 'Chile',
    'dest_dir_calle'        => 'Martinez de Rozas',
    'dest_dir_numero'       => '3600',
    'dest_dir_adicional'    => 'Local 201',
    'dest_observaciones'    => false,
    'dest_region'           => $state,
    'dest_comuna'           => $city,
    'dest_fono'             => '999208501',
    'dest_mail'             => 'contacto@postalchile.cl',
    'tipo_envio'            => $settings->tipo_envio,
    'tipo_servicio'         => $settings->tipo_servicio,
    'largo'                 => $length,
    'ancho'                 => $width,
    'alto'                  => $height,
    'peso'                  => $weight,
    'contenido_descripcion' => "Prueba de conexión API",
    'contenido_valor'       => 55000,
    'cliente_codigo_barra'  => 'WC-TEST',
    'cliente_orden_compra'  => '1234567890',
];

$api_settings   = $api->set_api_json_request( 'solicitar_envio', $send );
$response       = $api->solicitar_envio($send);
$response_data  = json_decode($response);


$codigo_seguimiento = isset($response_data->servicio->codigo_seguimiento) && $response_data->servicio->codigo_seguimiento ? $response_data->servicio->codigo_seguimiento : 0;

$test_data = json_decode(json_encode([
    [
        'title'     => 'Datos enviados: Solicitud de envío',
        'content'   => $api_settings
    ],
    [
        'title'     => 'Datos recibidos: Solicitud de envío',
        'content'   => $response_data
    ]
]));

$mensaje        = isset($response_data->retorno->mensaje) ? $response_data->retorno->mensaje : false;
$alert_color    = isset($response_data->retorno->codigo) && $response_data->retorno->codigo==0 ? 'success' : 'danger';
$alert_content  = isset($response_data->retorno->codigo) && $response_data->retorno->codigo==0 ? $mensaje : '<b>Error al crear:</b> '.$mensaje;
$panel_title    = 'Solicitud de envío';

include plugin_dir_path( __FILE__ ) . 'api-test-table.php';

$send = [
    'codigo_seguimiento' => $codigo_seguimiento
];

$api_settings   = $api->set_api_json_request( 'anular_envio', $send );
$response       = $api->anular_envio($send);
$response_data  = json_decode($response);

$test_data = json_decode(json_encode([
    [
        'title'     => 'Datos enviados: Anulación de envío',
        'content'   => $api_settings
    ],
    [
        'title'     => 'Datos recibidos: Anulación de envío',
        'content'   => $response_data
    ]
]));

$mensaje        = isset($response_data->retorno->mensaje) ? $response_data->retorno->mensaje : false;
$alert_color    = !$codigo_seguimiento ? 'warning' : (isset($response_data->retorno->codigo) && $response_data->retorno->codigo==0 ? 'success' : 'danger');
$alert_content  = !$codigo_seguimiento ? 'Para realizar la prueba de anulación de envío primero se requiere pasar la prueba de Solicitud de envío' : (isset($response_data->retorno->codigo) && $response_data->retorno->codigo==0 ? $mensaje : '<b>Error al crear:</b> '.$mensaje);
$panel_title    = 'Anulación de envío';

include plugin_dir_path( __FILE__ ) . 'api-test-table.php';