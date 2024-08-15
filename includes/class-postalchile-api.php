<?php 
defined( 'ABSPATH' ) || exit;

require_once(plugin_dir_path( __FILE__ ) . 'src/JWTExceptionWithPayloadInterface.php');
require_once(plugin_dir_path( __FILE__ ) . 'src/BeforeValidException.php');
require_once(plugin_dir_path( __FILE__ ) . 'src/CachedKeySet.php');
require_once(plugin_dir_path( __FILE__ ) . 'src/ExpiredException.php');
require_once(plugin_dir_path( __FILE__ ) . 'src/JWK.php');
require_once(plugin_dir_path( __FILE__ ) . 'src/JWT.php');
require_once(plugin_dir_path( __FILE__ ) . 'src/Key.php');
require_once(plugin_dir_path( __FILE__ ) . 'src/SignatureInvalidException.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ( ! class_exists( 'Postalchile_API' ) ) {
    class Postalchile_API {
        public function __construct() {

            $this->id                   = 'postalchile';

            $this->init();

            $this->api_base_url         = 'https://services.postalchile.cl/PCE/rest/';
            $this->settings             = get_option( 'woocommerce_postalchile-shipping-method_settings' );
        }
        public function init(){
            // Init is required by wordpress
        }
        public function get_settings( $name = false ) {

            $defaults = [
                'api_key'                => 'SGD',
                'api_secret'             => '5599b8449deab0b6c85be146d40a8a18',
                'client_id'              => 227,
                'usuario'                => '789-7',
                'jwt_secret'             => 'Abcdefghij123456'
            ];

            if(!isset($this->settings['environment']) || $this->settings['environment']=='qa') {
                foreach($defaults as $key=>$value)
                    $this->settings[$key] = $value;
            }

            if(!isset($this->settings['jwt_secret']))
                $this->settings['jwt_secret'] = $defaults['jwt_secret'];

            return json_decode(json_encode($this->settings));
        }
        private function get_json( $file_path ) {

            $file_path = trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/data/' ).$file_path;

            if ( !file_exists($file_path) )
                return [];

            ob_start();
            include $file_path;
            $data = ob_get_contents();
            ob_end_clean();

            return json_decode($data);
        }
        public function regiones() {

            $regiones = [];

            $data   = $this->get_json( 'regiones.json' );

            if(!$data)
                return $regiones;

            foreach($data as $region)
                $regiones[trim(strtolower($region->region))] = trim($region->region);

            return $regiones;
        }
        public function comunas( $region=false ) {

            $comunas = [];

            if($region && !is_numeric($region)) {

                $regiones = $this->get_json( 'regiones.json' );

                if(!$regiones)
                    $region = false;

                foreach($regiones as $region_data) {
                    if(strtolower($region_data->region)==trim(strtolower($region)))
                        $region = $region_data->id;
                }

                if(!is_numeric($region))
                    $region = false;
            }

            $data   = $this->get_json( 'comunas.json' );

            if(!$data)
                return $comunas;

            foreach($data as $comuna) {

                if($region && $comuna->region!==$region)
                    continue;

                $comunas[trim(strtolower($comuna->comuna))] = trim(ucfirst($comuna->comuna));
            }

            return $comunas;
        }
        // Endpoints
        public function solicitar_envio( $servicio ) {

            $settings = $this->get_settings();

            $servicio = wp_parse_args($servicio,[
                'remit_rut'             => false,
                'remit_nombres'         => false,
                'remit_apellidos'       => false,
                'remit_dir_calle'       => false,
                'remit_dir_numero'      => false,
                'remit_dir_adicional'   => false,
                'remit_observaciones'   => false,
                'remit_region'          => false,
                'remit_comuna'          => false,
                'remit_fono'            => false,
                'remit_mail'            => false,
                'dest_rut'              => false,
                'dest_nombres'          => false,
                'dest_apellidos'        => false,
                'dest_dir_calle'        => false,
                'dest_dir_numero'       => false,
                'dest_dir_adicional'    => false,
                'dest_observaciones'    => false,
                'dest_region'           => false,
                'dest_comuna'           => false,
                'dest_fono'             => false,
                'dest_mail'             => false,
                'tipo_envio'            => false, // 1: ERE (NextDay) / 4: caso servicio HOY (SameDay)
                'tipo_servicio'         => false, // 1: Retiro 2: Entrega 3: Retiro y Entrega 
                'largo'                 => false,
                'ancho'                 => false,
                'alto'                  => false,
                'peso'                  => false,
                'contenido_descripcion' => false,
                'contenido_valor'       => false,
                'cliente_codigo_barra'  => false,
                'cliente_orden_compra'  => false,
            ]);

            foreach($settings as $key=>$value) {
                if(isset($servicio[$key]) && !$servicio[$key])
                    $servicio[$key] = $value;
            }

            $args       = $this->set_api_json_request( 'solicitar_envio', $servicio, 'detalle_servicio');
            $response   = $this->api($args);
            //return $this->debug($args,$response);

            return $response;
        }
        public function tracking_envio( $servicio ) {

            $servicio = wp_parse_args($servicio,[
                'codigo_seguimiento'    => false,
                'cliente_codigo_barra'  => false
            ]);

            $args       = $this->set_api_json_request( 'tracking_envio', $servicio );
            $response   = $this->api($args);

            //return $this->debug($args,$response);

            return $response;
        }
        public function anular_envio( $servicio ) {

            $servicio = wp_parse_args($servicio,[
                'codigo_seguimiento'    => false
            ]);

            $args       = $this->set_api_json_request( 'anular_envio', $servicio );
            $response   = $this->api($args);

            //return $this->debug($args,$response);

            return $response;
        }
        public function cotizar_envio( $servicio ) {

            $servicio = wp_parse_args($servicio,[
                'tipo_envio'        => false,
                'tipo_servicio'     => false,
                'region_origen'     => false,
                'comuna_origen'     => false,
                'region_destino'    => false,
                'comuna_destino'    => false,
                'largo'             => false,
                'ancho'             => false,
                'alto'              => false,
                'peso'              => false
            ]);

            $args       = $this->set_api_json_request( 'cotizar_envio', $servicio );
            $response   = $this->api($args);

            //return $this->debug($args,$response);

            return $response;
        }
        // Helpers
        public function debug($args,$response) {

            $settings = $this->get_settings();

            ob_start();
            echo '<br><table class="table table-sm" width="200">';
            echo '<tr><th>Configuración:</th><td>';
            echo '<pre>'.json_encode($settings, JSON_PRETTY_PRINT).'</pre>';
            echo '</td><tr>';
            echo '<tr><th>API Endpoint:</th><td><pre>'.$this->api_base_url.$args['endpoint'].'</pre></td><tr>';
            echo '<tr><th>Request:</th><td><pre>'.json_encode(json_decode($args['request']), JSON_PRETTY_PRINT).'</pre></td><tr>';
            echo '<tr><th>Response:</th><td>';
            echo '<pre>'.json_encode(json_decode($response), JSON_PRETTY_PRINT).'</pre>';
            echo '</td><tr>';
            echo '</table>';
            $return = ob_get_contents();
            ob_end_clean();

            return $return;
        }
        public function set_api_json_request( $endpoint, $data, $data_key='servicio' ) {

            $settings = $this->get_settings();
            
            return [
                'endpoint'  => $endpoint,
                'request'   => '{
                    "encabezado": {
                        "client_id": '.$settings->client_id.',
                        "usuario": "'.$settings->usuario.'"
                    },
                    "'.$data_key.'": '.json_encode($data).'
                }'
            ];
        }
        public function api($args) {

            $args = wp_parse_args($args,[
                'token'     => false,
                'method'    => 'POST',
                'endpoint'  => false,
                'request'   => []
            ]);

            $url = $this->api_base_url.$args['endpoint'];

            if(!$args['token'])
                $args['token'] = $this->get_login_token();

            $headers = [
                "Authorization: Bearer ".$args['token'],
                'Content-Type: application/json'
            ];

            $curl = curl_init();

            switch ($args['method']) {
                case "POST":
                    curl_setopt($curl, CURLOPT_POST, 1);

                    if ($args['request'])
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $args['request']);
                    break;
                case "PUT":
                    curl_setopt($curl, CURLOPT_PUT, 1);
                    break;
                default:
                    if ($data)
                        $url = sprintf("%s?%s", $url, http_build_query($args['request']));
            }

            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, "username:password");
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($curl);

            curl_close($curl);

            //if($args['endpoint']=='solicitar_envio')
            //    dd($result);    

            return $result;
        }   
        public function get_login_token() {

            date_default_timezone_set('America/Santiago');

            $settings   = $this->get_settings();

            $data = [
                'api_key'       => $settings->api_key,
                'api_secret'    => $settings->api_secret,
                'client_id'     => $settings->client_id,
                'datetime'      => date('Y-m-d H:i:s'),
            ];

            $token = JWT::encode(
                $data,
                $settings->jwt_secret,
                'HS256'
            );

            $login = $this->api([
                'token'     => $token,
                'endpoint'  => 'login'
            ]);

            $login = json_decode($login);

            if(isset($login->token) && $login->token)
                return $login->token;
        }
    }
}
