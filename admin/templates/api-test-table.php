<?php
if ( ! defined( 'WPINC' ) )
    die;

?>
<div class="postbox" style="width: 100%; height: 100%;">
    <h3 class="health-check-accordion-heading">
        <button aria-expanded="false" class="health-check-accordion-trigger" aria-controls="health-check-accordion-block-wp-core" type="button">
            <span class="title"><?php echo $panel_title; ?></span>
            <span class="badge postalchile-alert-<?php echo $alert_color; ?>"><?php echo $alert_color=='success' ? 'OK' : ($alert_color=='warning' ? 'Pendiente' : 'Error'); ?></span>
            <span class="icon"></span>
        </button>
    </h3>

    <div class="collapse inside">

        <div class="postalchile-alert postalchile-alert-<?php echo $alert_color; ?>">
            <?php echo $alert_content; ?>
        </div>

        <?php

        echo '<div class="row">';

        if($test_data) :
            foreach($test_data as $data) :
                if($data->content) :
                    ?>
                    <div class="" style="width: 100%; padding: 0 10px; display: inline-block;box-sizing: content-box;overflow-x: auto;">
                        <?php
                        echo '<table class="widefat striped health-check-table" width="100%"><tbody>';
                
                        foreach($data->content as $key=>$value) {
                            if(is_object($value)) {
                                foreach($value as $skey=>$svalue)
                                    echo '<tr align="left"><th><b>'.$skey.':</b></th><td align="left">'.$svalue.'</td></tr>';
                            } else {

                                if($key=='request') {

                                    if(!$value || $value=='null' || json_encode(json_decode($value), JSON_PRETTY_PRINT)=='null')
                                        $value = '<b style="color: red">Error: No se ha especificado el ID Cliente y/o el Usuario para el ambiente de Producción. Por favor, verifique la configuración.</b><br><code>'.$value.'</code>';
                                    else
                                        $value = '<pre>'.json_encode(json_decode($value), JSON_PRETTY_PRINT).'</pre>';
                                }

                                echo '<tr align="left"><th><b>'.$key.':</b></th><td align="left">'.$value.'</td></tr>';
                            }
                        }

                        echo '</tbody></table>';
                        ?>
                    </div>
                    <?php
                endif;
            endforeach;
        endif;

        echo '</div>';
        ?>
    </div>
</div>