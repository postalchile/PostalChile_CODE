<?php
defined( 'ABSPATH' ) || exit;

class Postalchile_Shipping_Method extends WC_Shipping_Method {

    public function __construct() {

        $this->id                   = 'postalchile-shipping-method';
        $this->method_title         = esc_html__('Postal Chile', 'postalchile-woo-oficial' );
        $this->method_description   = esc_html__('Envíos WooCommerce a Chile por Postal Chile', 'postalchile-woo-oficial' );
        $this->availability         = 'including';
        $this->countries            = ['CL'];
        //$this->for_shipping_status  = 'processing';

        $this->init();
    }
    public function init() {

        $this->init_form_fields();
        $this->init_settings();

        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }
    public function init_form_fields() {

        $api = new Postalchile_API();

        $form_fields = array(
            'enabled' => array(
              'title'   => esc_html__('Activar/Desactivar', 'postalchile-woo-oficial' ),
              'type'    => 'checkbox',
              'label'   => esc_html__('Activa el método de envío Postal Chile', 'postalchile-woo-oficial'  ),
              'default' => 'no'
            ),
            'environment' => array(
              'title'   => esc_html__('Ambiente', 'postalchile-woo-oficial' ),
              'type'    => 'select',
              'options' => [
                'qa'    => 'Pruebas',
                'prod'  => 'Producción'
              ],
              'label'   => esc_html__('Selecciona el ambiente de trabajo con Postal Chile', 'postalchile-woo-oficial'  ),
              'description' => esc_html__('Para realizar pruebas de uso active el ambiente de Pruebas', 'postalchile-woo-oficial'  ),
              'default' => 'qa',
              'desc_tip'    => true,
            ),
            'title' => array(
              'title'       => esc_html__('Título', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              'description' => esc_html__('Ingresa el título para el método de envío con Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
            ),
            'description' => array(
              'title'       => esc_html__('Descripción', 'postalchile-woo-oficial' ),
              'type'        => 'textarea',
              'description' => esc_html__('Ingresa la descripción para método de envío con Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'api_key' => array(
              'title'       => esc_html__('API Key', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              'description' => esc_html__('Ingresa la API Key (api_key) proporcionada por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'api_secret' => array(
              'title'       => esc_html__('API Secret', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              'description' => esc_html__('Ingresa la contraseña (api_secret) proporcionada por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'client_id' => array(
              'title'       => esc_html__('ID Cliente', 'postalchile-woo-oficial' ),
              'type'        => 'number',
              'description' => esc_html__('Ingresa tu ID de cliente (client_id) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'usuario' => array(
              'title'       => esc_html__('Usuario', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'status_solicitar_envio' => array(
              'title'   => esc_html__('Estado para generación de OT automática', 'postalchile-woo-oficial' ),
              'type'    => 'select',
              'multiple' => true,
              'options' =>  array_merge([0=>'Desactivado'],wc_get_order_statuses()),
              'description'   => esc_html__('Selecciona el estado de la orden de WooCommerce en el que desees generar la orden de transporte con Postal Chile de forma automática', 'postalchile-woo-oficial'  ),
              'default' => 'wc-processing',
              'desc_tip'    => true,
            ),
            'status_anular_envio' => array(
              'title'   => esc_html__('Estado para anulación de OT automática', 'postalchile-woo-oficial' ),
              'type'    => 'select',
              'multiple' => true,
              'options' =>  array_merge([0=>'Desactivado'],wc_get_order_statuses()),
              'description'   => esc_html__('Selecciona el estado de la orden de WooCommerce en el que desees anular la orden de transporte con Postal Chile de forma automática', 'postalchile-woo-oficial'  ),
              'default' => 'wc-cancelled',
              'desc_tip'    => true,
            ),
            'tipo_envio' => array(
              'title'   => esc_html__('Tipo de envío', 'postalchile-woo-oficial' ),
              'type'    => 'select',
              'multiple' => true,
              'options' =>  [
                1 => 'ERE (NextDay)',
                4 => 'HOY (SameDay)'
              ],
              'description'   => esc_html__('Selecciona el tipo de envío a utilizar', 'postalchile-woo-oficial'  ),
              'default' => 1,
              'desc_tip'    => true,
            ),
            'tipo_servicio' => array(
              'title'   => esc_html__('Tipo de servicio', 'postalchile-woo-oficial' ),
              'type'    => 'select',
              'multiple' => true,
              'options' =>  [
                1 => 'Retiro',
                2 => 'Entrega',
                3 => 'Retiro y Entrega'
              ],
              'description'  => esc_html__('Selecciona el tipo de servicio a utilizar', 'postalchile-woo-oficial'  ),
              'default'      => 3,
              'desc_tip'     => true,
            ),
            'remit_rut' => array(
              'title'       => esc_html__('Rut remitente', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              'description' => esc_html__('Ingresa tu rut o el de la empresa', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'remit_nombres' => array(
              'title'       => esc_html__('Nombres remitente', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'remit_apellidos' => array(
              'title'       => esc_html__('Apellidos remitente', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'remit_dir_calle' => array(
              'title'       => esc_html__('Calle de la direccion para retiros', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'remit_dir_numero' => array(
              'title'       => esc_html__('N° de la dirección para retiros', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'remit_dir_adicional' => array(
              'title'       => esc_html__('N° de departamento, casa u oficina para retiro', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'remit_observaciones' => array(
              'title'       => esc_html__('Observaciones para el retiro', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'remit_region' => array(
              'title'       => esc_html__('Región', 'postalchile-woo-oficial' ),
              'type'        => 'select',
              'options'     => $api->regiones(),
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'remit_comuna' => array(
              'title'       => esc_html__('Comuna', 'postalchile-woo-oficial' ),
              'type'        => 'select',
              'options'     => $api->comunas(),
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'remit_fono' => array(
              'title'       => esc_html__('Teléfono de contacto', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            ),
            'remit_mail' => array(
              'title'       => esc_html__('E-mail de contacto', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true
            )
        );

        $this->form_fields = $form_fields;
    }
    public function get_settings( $name = false ) {

        $defaults = [
            'api_key'                => 'SGD',
            'api_secret'             => '5599b8449deab0b6c85be146d40a8a18',
            'client_id'              => 227,
            'usuario'                => '789-7',
            'jwt_secret'             => 'Abcdefghij123456',
            'status_solicitar_envio' => false,
            'status_anular_envio'    => false,
            'tipo_envio'             => 1,
            'tipo_servicio'          => 3,
        ];

        if(!isset($this->settings['environment']) || $this->settings['environment']=='qa') {
            foreach($defaults as $key=>$value)
                $this->settings[$key] = $value;
        }

        if(!isset($this->settings['jwt_secret']))
            $this->settings['jwt_secret'] = $defaults['jwt_secret'];

        if(isset($this->settings['status_solicitar_envio']) && $this->settings['status_solicitar_envio'])
            $this->settings['status_solicitar_envio'] = str_replace('wc-','',$this->settings['status_solicitar_envio']);

        if(isset($this->settings['status_anular_envio']) && $this->settings['status_anular_envio'])
            $this->settings['status_anular_envio'] = str_replace('wc-','',$this->settings['status_anular_envio']);

        return json_decode(json_encode($this->settings));
    }
    public function calculate_shipping( $package = array() ) {

        $settings = $this->get_settings();
        
        if(!isset($settings->enabled) || $settings->enabled!=='yes')
            return;

        $this->add_rate( array(
            'id'     => $this->id,
            'label'  => $this->settings['title'],
            'cost'   => $this->calculate_shipping_cost($package)
        ));
    }
    public function calculate_shipping_cost( $package = array() ) {

        $settings   = $this->get_settings();

        $api        = new Postalchile_API();

        $contents =  WC()->cart->get_cart_contents();

        $length = 0;
        $width  = 0;
        $height = 0;

        foreach($contents as $content) {
            $length += floatval($content['data']->get_length());
            $width  += floatval($content['data']->get_width());
            $height += floatval($content['data']->get_height());
        }

        $response = $api->cotizar_envio([
            'tipo_envio'        => 1,
            'tipo_servicio'     => 2,
            'region_origen'     => $settings->remit_region,
            'comuna_origen'     => $settings->remit_comuna,
            'region_destino'    => $package['destination']['state'],
            'comuna_destino'    => $package['destination']['city'],
            'largo'             => $length,
            'ancho'             => $width,
            'alto'              => $height,
            'peso'              => WC()->cart->get_cart_contents_weight()
        ]);

        if($response) {

            $response_data = json_decode($response);

            if(isset($response_data->retorno->codigo) && $response_data->retorno->codigo==0)
                return $response_data->valor;

        }

    }
}