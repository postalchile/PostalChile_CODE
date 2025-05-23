<?php
if ( ! defined( 'WPINC' ) )
    die;

$options = get_option('postalchile_checkout',postalchile_default_checkout());

$fields = [
    'state' => [
        'title' => 'Región',
        'description' => 'Campo para la selección de región de envío'
    ],
    'city' => [
        'title' => 'Comuna',
        'description' => 'Campo para la selección de región de envío'
    ],
    'address_1' => [
        'title' => 'Dirección: Nombre de la calle',
        'description' => 'Campo para la selección de región de envío'
    ],
    'address_2' => [
        'title' => 'Dirección: Número',
        'description' => 'Campo para la selección de región de envío',
    ],
    'address_3' => [
        'title' => 'Dirección: N° Depto, Villa, Población, Sector, Etc',
        'description' => 'Campo para la selección de región de envío',
    ],
    'company' => [
        'title' => 'Rut',
        'description' => 'Campo para la selección de región de envío'
    ],
    'phone' => [
        'title' => 'Teléfono celular',
        'description' => 'Campo para la selección de región de envío'
    ],
    /*
    'postal_code' => [
        'title' => 'Código Postal',
        'description' => 'Campo para la selección de región de envío'
    ]
    */
];

?>

<h2>Configuración campos Checkout</h2>
<p>La siguiente configuración te permitirá modificar y/o reordenar los campos del checkout utilizados por Postal Chile.</p>
<form method="POST" action="<?php echo admin_url( 'admin-post.php' ); ?>">
    <input type="hidden" name="action" value="postalchile_checkout">
    <?php wp_nonce_field( 'postalchile_checkout_nonce' , 'postalchile_nonce'); ?>
    <div class="postbox" style="width: 100%; height: 100%;">
        <?php
        foreach($fields as $key=>$field) :

            $inputs = $options[$key];

            ?>
            <h3 class="health-check-accordion-heading">
                <button aria-expanded="false" class="health-check-accordion-trigger" aria-controls="health-check-accordion-block-wp-core" type="button">
                    <span class="title"><?php echo $field['title']; ?> (<?php echo $key; ?>)</span>
                    <span class="icon"></span>
                </button>
            </h3>
            <div class="collapse inside">
                <p><?php echo $field['description']; ?></p>
                <div class="row">
                    <?php
                    foreach($inputs as $subkey=>$val) :
                        ?>
                        <div class="col-4">
                            <label for=""><?php echo $subkey; ?></label>
                            <input name="postalchile_checkout[<?php echo $key; ?>][<?php echo $subkey; ?>]" class="" value="<?php echo $val; ?>">
                        </div>
                        <?php
                    endforeach;
                    ?>
                </div>
            </div>
            <?php
        endforeach;
        ?>
    </div>
    <br>
    <button class="button button-primary">Guardar cambios</button>
    <p><b>Nota:</b> Ésta configuración permite editar exclusivamente las intervenciones realizadas por el plugin dentro del checkout de WooCommerce.</p>
    <p>Si estás utilizando algún plugin para editar el checkout y/o deseas realizar una modificación adicional te recomendamos utilizar el filtro de WooCommerce "woocommerce_checkout_fields". Cono más información en la <a href="https://developer.woocommerce.com/docs/customizing-checkout-fields-using-actions-and-filters/#overriding-core-fields" target="_blank">Documentación Oficial de WooCommerce</a></p> 
</form>