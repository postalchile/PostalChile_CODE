<?php
defined( 'ABSPATH' ) || exit;

class Postalchile_Shipping_Method extends WC_Shipping_Method {

    public function __construct() {

        $this->id                   = 'postalchile-shipping-method';
        $this->method_title         = esc_html__('Envío con Postal Chile', 'postalchile-woo-oficial' );
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

    public function get_form_fields() {

        $api = new Postalchile_API();

        $form_fields = array(
            'enabled' => array(
              'title'   => esc_html__('Activar plugin', 'postalchile-woo-oficial' ),
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
              'desc_tip'    => true
            ),
            'title' => array(
              'title'       => esc_html__('Título', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              'description' => esc_html__('Ingresa el título para el método de envío con Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('Envío con Postal Chile', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'default'     => $this->method_title,
              'placeholder' => $this->method_title
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
              'desc_tip'    => true,
              'required'    => true
            ),
            'api_secret' => array(
              'title'       => esc_html__('API Secret', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              'description' => esc_html__('Ingresa la contraseña (api_secret) proporcionada por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'required'    => true
            ),
            'client_id' => array(
              'title'       => esc_html__('ID Cliente', 'postalchile-woo-oficial' ),
              'type'        => 'number',
              'description' => esc_html__('Ingresa tu ID de cliente (client_id) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'required'    => true
            ),
            'usuario' => array(
              'title'       => esc_html__('Usuario', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'required'    => true
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
              'desc_tip'    => true,
              'required'    => true
            ),
            'remit_nombres' => array(
              'title'       => esc_html__('Nombres remitente', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'required'    => true
            ),
            'remit_apellidos' => array(
              'title'       => esc_html__('Apellidos remitente', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'required'    => true
            ),
            'remit_dir_calle' => array(
              'title'       => esc_html__('Calle de la direccion para retiros', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'required'    => true
            ),
            'remit_dir_numero' => array(
              'title'       => esc_html__('N° de la dirección para retiros', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'required'    => true
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
              'default'     => esc_html__('metropolitana', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'required'    => true
            ),
            'remit_comuna' => array(
              'title'       => esc_html__('Comuna', 'postalchile-woo-oficial' ),
              'type'        => 'select',
              'options'     => $api->comunas(),
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('quinta normal', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'required'    => true
            ),
            'remit_fono' => array(
              'title'       => esc_html__('Teléfono de contacto', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'required'    => true
            ),
            'remit_mail' => array(
              'title'       => esc_html__('E-mail de contacto', 'postalchile-woo-oficial' ),
              'type'        => 'text',
              //'description' => esc_html__('Ingresa tu código de usuario (usuario) proporcionado por Postal Chile', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'required'    => true
            ),
            'default_length' => array(
              'title'       => esc_html__('Longitud (predeterminada)', 'postalchile-woo-oficial' ),
              'type'        => 'number',
              'description' => esc_html__('Define la longitud en centímetros (cm) predeterminada en caso de que los productos no tengan configurada su dimensión', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'placeholder' => 1,
              'default'     => 1
            ),
            'default_width' => array(
              'title'       => esc_html__('Ancho (predeterminado)', 'postalchile-woo-oficial' ),
              'type'        => 'number',
              'description' => esc_html__('Define el ancho en centímetros (cm) predeterminado en caso de que los productos no tengan configurada su dimensión', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'placeholder' => 1,
              'default'     => 1
            ),
            'default_height' => array(
              'title'       => esc_html__('Alto (predeterminado)', 'postalchile-woo-oficial' ),
              'type'        => 'number',
              'description' => esc_html__('Define el alto en centímetros (cm) predeterminado en caso de que los productos no tengan configurada su dimensión', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'placeholder' => 1,
              'default'     => 1
            ),
            'default_weight' => array(
              'title'       => esc_html__('Peso (predeterminado)', 'postalchile-woo-oficial' ),
              'type'        => 'number',
              'description' => esc_html__('Define el peso en kilos (kg) predeterminado en caso de que los productos no tengan configurada su dimensión', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'placeholder' => 1,
              'default'     => 1
            ),
            'standar_height' => array(
              'title'       => esc_html__('Caja estándar - Alto', 'postalchile-woo-oficial' ),
              'type'        => 'number',
              'description' => esc_html__('Define el alto en centímetros (cm) de una caja estándar', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'placeholder' => 50,
              'default'     => 50
            ),
            'standar_width' => array(
              'title'       => esc_html__('Caja estándar - Ancho', 'postalchile-woo-oficial' ),
              'type'        => 'number',
              'description' => esc_html__('Define el ancho en centímetros (cm) de una caja estándar', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'placeholder' => 40,
              'default'     => 40
            ),
            'standar_length' => array(
              'title'       => esc_html__('Caja estándar - Longitud', 'postalchile-woo-oficial' ),
              'type'        => 'number',
              'description' => esc_html__('Define la longitud en centímetros (cm) de una caja estándar', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'placeholder' => 20,
              'default'     => 20
            ),
            'max_weight' => array(
              'title'       => esc_html__('Peso máximo legar a transportar (KG)', 'postalchile-woo-oficial' ),
              'type'        => 'number',
              'description' => esc_html__('Define el peso máximo legal a transportar', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'placeholder' => 25,
              'default'     => 25
            ),
            'margin' => array(
              'title'       => esc_html__('Margen de tolerancia en el cálculo (%)', 'postalchile-woo-oficial' ),
              'type'        => 'number',
              'description' => esc_html__('Define el margen de tolerancia en el cálculo de bultos', 'postalchile-woo-oficial'  ),
              'default'     => esc_html__('', 'postalchile-woo-oficial' ),
              'desc_tip'    => true,
              'placeholder' => 10,
              'default'     => 10
            ),
        );
        
        return $form_fields;

    }
    public function init_form_fields() {

        //get_height() * $content['data']->get_width() * $content['data']->get_length

        $this->form_fields = $this->get_form_fields();
    }
    public function get_settings( $name = false ) {

        $defaults = [
            'title'                  => $this->method_title,
            'api_key'                => 'SGD',
            'api_secret'             => '5599b8449deab0b6c85be146d40a8a18',
            'client_id'              => 227,
            'usuario'                => '789-7',
            'jwt_secret'             => 'Abcdefghij123456',
            'status_solicitar_envio' => false,
            'status_anular_envio'    => false,
            'tipo_envio'             => 1,
            'tipo_servicio'          => 3,
            'default_length'         => 1,
            'default_width'          => 1,
            'default_height'         => 1,
            'default_weight'         => 1
        ];

        if(!isset($this->settings['environment']) || $this->settings['environment']=='qa') {
            foreach($defaults as $key=>$value)
                $this->settings[$key] = $value;
        }

        $required_fields = ['title','default_length','default_width','default_height','default_weight'];

        foreach($required_fields as $field) {
          if(!$this->settings[$field])
            $this->settings[$field] = $defaults[$field];
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

        $shipping_data = $this->calculate_shipping_cost($package);

        $this->add_rate( array(
            'id'     => $this->id,
            'label'  => isset($shipping_data->label) && $shipping_data->label ? $shipping_data->label : $this->settings['title'],
            'cost'   => $shipping_data->cost
        ));
    }

    public function get_content_dimensions( $content ) {

        $settings   = $this->get_settings();

        $height     = floatval($content['data']->get_height());
        $width      = floatval($content['data']->get_width());
        $length     = floatval($content['data']->get_length());
        $weight     = floatval($content['data']->get_weight());
        $quantity   = floatval($content['quantity']);

        if(!$height && $settings->default_height)
          $height = floatval($settings->default_height);

        if(!$width && $settings->default_width)
          $width  = floatval($settings->default_width);

        if(!$length && $settings->default_length)
          $length = floatval($settings->default_length);

        if(!$weight && $settings->default_weight)
          $weight = floatval($settings->default_weight);

        $total          = floatval($height * $width * $length);
        $margin         = floatval($settings->margin / 100);
        $total_weight   = $quantity*$weight;
        $total_lt       = $quantity*($total/1000);


        $dimensions = (object)[
          'total'               => $total,
          'quantity'            => $quantity,
          'height'              => $height,
          'width'               => $width,
          'length'              => $length,
          'weight'              => $weight,
          'weight_volume'       => $total/4000,
          'cm3'                 => $total,
          'lt'                  => $total/1000,
          'total_weight_volume' => $quantity*($total/4000),
          'total_weight'        => $total_weight,
          'total_lt'            => $total_lt,
          'margin'              => $margin,
          'final_weight'        => $total_weight+($total_weight*$margin),
          'final_lt'            => $total_lt+($total_lt*$margin)
        ];

        return $dimensions;

    }

    public function calculate_shipping_cost_old( $package = array() ) {

        $settings   = $this->get_settings();

        $api        = new Postalchile_API();

        $contents =  WC()->cart->get_cart_contents();

        $length = 0;
        $width  = 0;
        $height = 0;

        $biggest_dimension  = 0;
        $_weight            = 0;
        $weight             = WC()->cart->get_cart_contents_weight();

        foreach($contents as $content) {

          $_dimensions = $this->get_content_dimensions($content);
          $dimensions  = $_dimensions->total;

          if(!$weight)
            $_weight += floatval($settings->default_weight);

          if($dimensions > $biggest_dimension) {
              $biggest_dimension  = $dimensions;

              $length = $_dimensions->length;
              $width  = $_dimensions->width;
              $height = $_dimensions->height;
          }
        }

        $response = $api->cotizar_envio([
            'tipo_envio'        => $settings->tipo_envio,
            'tipo_servicio'     => $settings->tipo_servicio,
            'region_origen'     => $settings->remit_region,
            'comuna_origen'     => $settings->remit_comuna,
            'region_destino'    => $package['destination']['state'],
            'comuna_destino'    => $package['destination']['city'],
            'largo'             => $length,
            'ancho'             => $width,
            'alto'              => $height,
            'peso'              => $weight ? $weight : $_weight
        ]);

        if($response) {

            $response_data = json_decode($response);

            if(isset($response_data->retorno->codigo) && $response_data->retorno->codigo==0) {

              $return = (object)[
                'cost' => $response_data->valor
              ];

            } else {

              $error = current_user_can('manage_woocommerce') ? ' | Detalles del error: '.json_encode($response_data, JSON_PRETTY_PRINT) : false;

              $return = (object)[
                'label' => $this->settings['title'].' - '.$response_data->retorno->mensaje.$error,
                'cost'  => $response_data->valor,
                'error' => $response_data->retorno->mensaje
              ];

            }
            
            return $return;

        }
    }

    public function calculate_shipping_cost( $package = array() ) {

        $settings   = $this->get_settings();

        $api        = new Postalchile_API();

        $contents =  WC()->cart->get_cart_contents();

        $extra_data = false;

        $length = 0;
        $width  = 0;
        $height = 0;

        $biggest_dimension  = 0;
        $_weight            = 0;
        $weight             = WC()->cart->get_cart_contents_weight();
        $standar_cm3        = $settings->standar_height*$settings->standar_width*$settings->standar_length;

        $totals = [
          'weight'      => 0,
          'lt'          => 0,
          'standar_cm3' => $standar_cm3,
          'standar_lt'  => $standar_cm3/1000,
          'max_weight'  => $settings->max_weight
        ];

        foreach($contents as $index=>$content) {

            $_dimensions    = $this->get_content_dimensions($content);
            $dimensions     = $_dimensions->total;

            $totals['weight']   += $_dimensions->final_weight;
            $totals['lt']       += $_dimensions->final_lt;

            if(!$weight)
                $_weight += floatval($settings->default_weight);

            if($dimensions > $biggest_dimension) {
                $biggest_dimension  = $dimensions;
                $length = $_dimensions->length;
                $width  = $_dimensions->width;
                $height = $_dimensions->height;
            }
        }

        $weight     = $weight ? $weight : $_weight;
        $box_volume = ceil($totals['lt']/$totals['standar_lt']);
        $box_weight = ceil($totals['weight']/$settings->max_weight);
        $boxes_unit = $box_volume > $box_weight ? 'Lt.' : 'Kg.';
        $boxes_qty  = $box_volume > $box_weight ? $totals['standar_lt'] : ($weight > $settings->max_weight ? $settings->max_weight : $weight);

        $totals['box_volume'] = $box_volume;
        $totals['box_weight'] = $box_weight;
        $totals['boxes']      = max($box_volume, $box_weight);
        $totals['boxes_unit'] = $boxes_unit;
        $totals['boxes_qty']  = $boxes_qty;

        $single_box  = $totals['boxes'] > 1 ? true : false;
        $extra_data  = $totals['boxes'].' '.($single_box ? 'bultos' : 'bulto').' de '.$totals['boxes_qty'].' '.$totals['boxes_unit'];
        $total_price = true;

        if(!$total_price)
          $extra_data .= $single_box ? ' - Precio por bulto' : false;

        $response_args = [
            'tipo_envio'        => $settings->tipo_envio,
            'tipo_servicio'     => $settings->tipo_servicio,
            'region_origen'     => $settings->remit_region,
            'comuna_origen'     => $settings->remit_comuna,
            'region_destino'    => $package['destination']['state'],
            'comuna_destino'    => $package['destination']['city'],
            'largo'             => $single_box ? $settings->standar_length : $length,
            'ancho'             => $single_box ? $settings->standar_width : $width,
            'alto'              => $single_box ? $settings->standar_height : $height,
            'peso'              => $single_box ? ($weight/$totals['boxes']) : $weight
        ];

        $response = $api->cotizar_envio($response_args);

        if($response) {

            $response_data = json_decode($response);

            $cost = isset($response_data->valor) ? ($total_price ? $response_data->valor*$totals['boxes'] : $response_data->valor) : false;

            if(isset($response_data->retorno->codigo) && $response_data->retorno->codigo==0) {

              $return = (object)[
                'label' => $this->settings['title'].': '.$extra_data,
                'cost'  => $cost
              ];

            } else {

              $error = current_user_can('manage_woocommerce') ? ' | Detalles del error: '.json_encode($response_data, JSON_PRETTY_PRINT) : false;

              $return = (object)[
                'label' => $this->settings['title'].': '.$extra_data.' - '.$response_data->retorno->mensaje.$error,
                'cost'  => $cost,
                'error' => $response_data->retorno->mensaje
              ];

            }
            
            return $return;

        }

    }
}