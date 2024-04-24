<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Postalchile
 * @subpackage Postalchile/includes
 * @author     Postalchile
 */
class Postalchile {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Postalchile_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'POSTALCHILE_VERSION' ) ) {
            $this->version = POSTALCHILE_VERSION;
        } else {
            $this->version = '1.2.7';
        }
        $this->plugin_name = 'postalchile';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_public_hooks();
    }
    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Postalchile_Loader. Orchestrates the hooks of the plugin.
     * - Postalchile_i18n. Defines internationalization functionality.
     * - Postalchile_Admin. Defines all hooks for the admin area.
     * - Postalchile_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-postalchile-loader.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-postalchile-i18n.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-postalchile-public.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-postalchile-api.php';

        $this->loader = new Postalchile_Loader();
    }
    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Postalchile_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Postalchile_i18n();

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }
    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public  = new Postalchile_Public( $this->get_plugin_name(), $this->get_version() );

        $settings       = $plugin_public->get_settings();
        $status_envio   = $settings->status_solicitar_envio;
        $status_anula   = $settings->status_anular_envio;

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_filter( 'woocommerce_shipping_fields', $plugin_public, 'postalchile_change_city_to_dropdown', 10 );
        $this->loader->add_filter( 'woocommerce_checkout_fields', $plugin_public, 'postalchile_change_city_to_dropdown', 20 );
        $this->loader->add_filter( 'woocommerce_checkout_fields', $plugin_public, 'checkout_fields_override', 30);
        $this->loader->add_filter( 'woocommerce_default_address_fields', $plugin_public, 'reorder_fields') ;
        $this->loader->add_filter( 'woocommerce_default_address_fields' , $plugin_public, 'override_postcode_validation' );
        //$this->loader->add_action( 'woocommerce_review_order_before_cart_contents', $plugin_public, 'postalchile_validate_order', 10 );
        $this->loader->add_action( 'woocommerce_after_checkout_validation', $plugin_public, 'postalchile_validate_order', 10, 2 );
        $this->loader->add_filter( 'woocommerce_states', $plugin_public, 'get_states' );

        //$this->loader->add_action( 'woocommerce_after_shipping_rate', $plugin_public , 'action_after_shipping_rate', 10, 2 );
        $this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'custom_checkout_field_update_order_meta' );
        $this->loader->add_filter('woocommerce_checkout_get_value', $plugin_public ,'empty_checkout_get_value', 10, 2);

        if($status_envio)
            $this->loader->add_action( 'woocommerce_order_status_'.$status_envio, $plugin_public, 'solicitar_envio');

        if($status_anula)
            $this->loader->add_action( 'woocommerce_order_status_'.$status_anula, $plugin_public, 'anular_envio');
        
        $this->loader->add_action( 'woocommerce_order_actions', $plugin_public, 'woocommerce_order_actions' );
        $this->loader->add_action( 'woocommerce_order_action_solicitar_envio', $plugin_public, 'solicitar_envio' );
        $this->loader->add_action( 'woocommerce_order_action_anular_envio', $plugin_public, 'anular_envio' );

        $this->loader->add_action( 'woocommerce_order_details_after_order_table',$plugin_public,'tracking_envio' );
        $this->loader->add_action( 'woocommerce_before_order_itemmeta',$plugin_public,'admin_tracking_envio', 10, 3 );

        
        $this->loader->add_action( 'wp_ajax_obtener_regiones', $plugin_public, 'obtener_regiones_handle_ajax_request' );
        $this->loader->add_action( 'wp_ajax_nopriv_obtener_regiones', $plugin_public, 'obtener_regiones_handle_ajax_request' );

        $this->loader->add_action( 'wp_ajax_obtener_comunas_desde_region', $plugin_public, 'obtener_comunas_desde_region_handle_ajax_request' );
        $this->loader->add_action( 'wp_ajax_nopriv_obtener_comunas_desde_region', $plugin_public, 'obtener_comunas_desde_region_handle_ajax_request' );

    }
    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }
    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }
    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Postalchile_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }
    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}