<?php
if ( ! defined( 'WPINC' ) )
    die;

$admin_url = get_admin_url().'admin.php?page=wc-settings&tab=shipping&section=postalchile-shipping-method';
$tab       = isset($_GET['tab']) ? $_GET['tab'] : 'api';
?>
<header class="postalchile-admin-header">
    <img src="<?php echo plugin_dir_url( __DIR__ ) . 'assets/images/logo.png'; ?>" width="200" height="44" alt="Postal Chile">
    <a class="menu-item <?php echo $tab=='api' ? 'selected' : false; ?>" href="admin.php?page=postal-chile&tab=api">Pruebas de conexión</a>
    <a class="menu-item <?php echo $tab=='checkout' ? 'selected' : false; ?>" href="admin.php?page=postal-chile&tab=checkout">Configuración Checkout</a>
    <div class="ml-auto">
        <a class="button button-primary ml-auto" href="<?php echo plugin_dir_url(__DIR__) . '../Manual de instalación WooCommerce.pdf'; ?>" target="_blank">Ver documentación</a>
        <a class="button button-primary ml-auto" href="<?php echo $admin_url; ?>">Ir a la configuración</a>
    </div>
</header>
<div class="wrap">
    <?php
    include plugin_dir_path( __FILE__ ) . $tab . '.php';
    ?>
</div>
<style>
    .postbox {
        margin-bottom: 0 !important;
        margin-top: -1px !important;
    }
    .postbox-header {
        padding: 0 1rem;
    }
    .postbox .inside {
        padding: 1rem !important;
        margin: 0 !important;
    }
    .ml-auto {
        margin-left: auto !important;
    }
    .wrap {
        max-width: 100%;
        width: 1000px;
        margin: 10px auto;
        padding-right: 10px;
        box-sizing: border-box;
    }
    .postalchile-admin-header {
        background-color: #fff;
        padding: 0 1rem;
        display: flex;
        align-items: center;
        margin-left: -1.5rem;
    }
    .postalchile-admin-header a.menu-item {
        text-decoration: none;
        color: #1d2327;
        padding: 1.5rem 1rem;
        margin-bottom: -2px;
        border-bottom: 2px solid transparent;
    }
    .postalchile-admin-header a.menu-item.selected {
        border-bottom: 2px solid #004077;
    }
    .postalchile-admin-header a.menu-item:hover {
        border-bottom: 2px solid #004077;
    }
    .postalchile-alert {
        border: 1px solid;
        padding: .75rem;
        border-radius: .2rem;
        display: block;
        width: 100%;
        box-sizing: border-box;
        margin-bottom: 1rem;
    }
    .postalchile-alert-success {
        background-color: rgb(76 175 80 / 10%);
        border-color: #4CAF50;
        color: #4CAF50;
    }
    .postalchile-alert-danger {
        background-color: rgb(244 67 54 / 10%);
        border-color: #F44336;
        color: #F44336;
    }
    .postalchile-alert-warning {
        background-color: rgb(255 193 7 / 10%);
        border-color: #ffc107;
        color: #FFC107;
    }
    .collapse {
        display: none;
    }
    .collapse.show {
        display:  block;
    }
    .row {
        display: flex;
        margin:10px -10px;
    }
    .col-4 {
        max-width: 25%;
        flex: 0 0 25%;
        padding: 0 5px;
    }
    input {
        padding: 5px;
        border-radius: 0;
        border: 1px solid #dedede;
        width: 100%;
    }

    label {
        font-weight: bold;
        font-size: 12px;
        display: block;
        margin-bottom: 5px;
        text-transform: capitalize;
    }
    @media(max-width: 800px) {

        .row {
            flex-wrap: wrap;
        }

    }
</style>
<script>
    jQuery( ".health-check-accordion-trigger" ).on( "click", function() {
        jQuery( this ).parent().next().toggleClass('show');
    });
</script>