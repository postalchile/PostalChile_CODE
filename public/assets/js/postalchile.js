(function( $ ) { // NOSONAR
	'use strict';
	var state_input = '.woocommerce-shipping-calculator input[type=text][name=calc_shipping_state]'; // NOSONAR
	var city_input = '.woocommerce-shipping-calculator input[type=text][name=calc_shipping_city]'; // NOSONAR
	var option_value = '<option value="'; // NOSONAR
	var cargando_comunas = '" selected="selected">Cargando comunas...</option>'; // NOSONAR
	var selected_selected = 'selected="selected"'; // NOSONAR
	var obtener_comuna_desde_region = 'action=obtener_comunas_desde_region&region='; // NOSONAR
	function transform_woo_shipping_calculator() { // NOSONAR
		
		var $state,$city,$state_parent,$city_parent,state_value,city_value,$new_state,$new_city;
		
		if ($(state_input).length) {
		 	$state = $(state_input);
		 	$city = $(city_input);
		 	$state_parent = $state.parents('p');
		 	$city_parent = $city.parents('p');
		 	
		 	state_value = $state.val();
		 	city_value = $city.val();

		 	$new_state = $('<select name="calc_shipping_state" style="width: 100%" class="wc-enhanced-select" id="calc_shipping_statex" placeholder="'+$state.attr('placeholder')+'" data-placeholder="'+$state.attr('placeholder')+'"><option value="'+state_value+'" selected="selected">Cargando Región...</option></select>'); // NOSONAR
		 	$new_city = $('<select name="calc_shipping_city" style="width: 100%" class="wc-enhanced-select" id="calc_shipping_cityx" placeholder="'+$city.attr('placeholder')+'" data-placeholder="'+$city.attr('placeholder')+'"><option value="'+city_value+'" selected="selected"> Cargando Comuna...</option></select>'); // NOSONAR


		 	$state_parent.append($new_state);
		 	$state.remove();
		 	
		 	$city_parent.append($new_city);
		 	$city.remove();
		
			$new_state.select2( { minimumResultsForSearch: 5 } );
			$new_city.select2( { minimumResultsForSearch: 5 } )

		 	jQuery.ajax({
					type: "post",
					url: woocommerce_params.ajax_url,
					dataType: 'json',
					data: "action=obtener_regiones&nonce="+woocommerce_postalchile.nonce,
					success: function(result){
						if (result.regiones) {
							var regiones_html = '';
							for(var k in result.regiones) {
								regiones_html += option_value + k + '" ' + (state_value === k ? selected_selected:'') + '>' + result.regiones[k] + '</option>';
							}
							$new_state.html(regiones_html);
						} else {
							$new_state.html('');
						}

						state_value = $new_state.val();

						jQuery.ajax({
							type: "post",
							url: woocommerce_params.ajax_url,
							dataType: 'json',
							data: obtener_comuna_desde_region+state_value+"&nonce="+woocommerce_postalchile.nonce,
							success: function(iresult){
								if (iresult.comunas) {
									var comunas_html = '';
									for(var k2 in iresult.comunas) {
										comunas_html += option_value + k2 + '" ' + (city_value === k2 ? selected_selected:'') + '>' + iresult.comunas[k2] + '</option>';
									}
									$new_city.html(comunas_html);
								} else {
									$new_city.html('');
								}
							}
						});


					}
				});

	 		$new_state.on('change', function(event) {

				state_value = $new_state.val();
				city_value = $new_city.val();
				$new_city.html(option_value+city_value+'" selected="selected"> Cargando Comuna...</option>')

				jQuery.ajax({
					type: "post",
					url: woocommerce_params.ajax_url,
					dataType: 'json',
					data: obtener_comuna_desde_region+state_value+"&nonce="+woocommerce_postalchile.nonce,
					success: function(result){
						if (result.comunas) {
							var comunas_html = [];
							for(var k in result.comunas) {
								comunas_html.push( option_value + k + '" ' + (city_value === k ? selected_selected:'') + '>' + result.comunas[k] + '</option>' );
							}
							$new_city.html(comunas_html.join(''));
						} else {
							$new_city.html('');
						}
					}
				});

	 		});
	 	// A veces solo a veces, solo cambia el county pero no el city asi que en ese caso debemos solo trabajar con el city
	 	} else if ($(city_input).length) {
			$city = $(city_input);
			$state = $('.woocommerce-shipping-calculator select[name=calc_shipping_state]');
		 	$city_parent = $city.parents('p');
		 	city_value = $city.val();		 	
		 	$new_city = $(
				 			'<select name="calc_shipping_city" style="width: 100%" class="wc-enhanced-select" id="calc_shipping_cityx" placeholder="' +
							 $city.attr('placeholder') +
							 '" data-placeholder="' +
							 $city.attr('placeholder') +
							 '"><option value="' +
							 city_value +
							 '" selected="selected"> Cargando Comuna...</option></select>'
						);
	
		 	$city_parent.append($new_city);
		 	$city.remove();

			state_value = $("#calc_shipping_state,#calc_shipping_statex").val();

			jQuery.ajax({
				type: "post",
				url: woocommerce_params.ajax_url,
				dataType: 'json',
				data: obtener_comuna_desde_region+state_value+"&nonce="+woocommerce_postalchile.nonce,
				success: function(result){
					if (result.comunas) {
						var comunas_html = '';
						for(var k in result.comunas) {
							comunas_html += option_value + k + '" ' + (city_value === k ? selected_selected:'') + '>' + result.comunas[k] + '</option>';
						}
						$new_city.html(comunas_html);
					} else {
						$new_city.html('');
					}
				}
			});

			$("#calc_shipping_state,#calc_shipping_statex").on('change', function(event) {

				state_value = $state.val();
				city_value = $new_city.val();
				$new_city.html(option_value+city_value+'" selected="selected"> Cargando Comuna...</option>')

				jQuery.ajax({
					type: "post",
					url: woocommerce_params.ajax_url,
					dataType: 'json',
					data: obtener_comuna_desde_region+state_value+"&nonce="+woocommerce_postalchile.nonce,
					success: function(result){ // NOSONAR
						if (result.comunas) {
							var comunas_html = '';
							for(var k in result.comunas) {
								comunas_html += option_value + k + '" ' + (city_value === k ? selected_selected:'') + '>' + result.comunas[k] + '</option>';
							}
							$new_city.html(comunas_html);
						} else {
							$new_city.html('');
						}
					}
				});

	 		});
	 		
	 	}
	}
	 $(function() { // NOSONAR
	 	transform_woo_shipping_calculator();

	 	$(document).on('click', '.shipping-calculator-button', function(ev) { // NOSONAR
	 		if ($(state_input).length ||
	 			$(city_input).length
	 			) {
	 			transform_woo_shipping_calculator();
	 		}
	 	})
	 	
	 	/////////////////////////////////////
		 $('form.woocommerce-checkout #billing_state').on('change', function(ev)
		 { // NOSONAR
			ev.preventDefault();
			ev.stopPropagation();

	 		var region = $(ev.currentTarget).val();
			 var $billing_city = $('form.woocommerce-checkout #billing_city');
	 		var city_value = $billing_city.val();
	 		$billing_city.html(option_value+city_value+cargando_comunas);
			 
			jQuery.ajax({
				type: "post",
				url: woocommerce_params.ajax_url,
				dataType: 'json',
				data: obtener_comuna_desde_region+region+"&nonce="+woocommerce_postalchile.nonce,
				success: function(result)
				{
					if (result.comunas) {
						var comunas_html = '';
						for(var k in result.comunas) {
							comunas_html += option_value + k + '" ' + (city_value === k ? selected_selected:'') + '>' + result.comunas[k] + '</option>';
						}
						$billing_city.html(comunas_html);
						$billing_city.trigger("change");
					} else {
						$billing_city.html('');
					}
				}
			});
		 });
		 
	 	/////////////////////////////////////
	 	$('form.woocommerce-checkout #shipping_state').on('change', function(ev) { // NOSONAR
	 		var region = $(ev.currentTarget).val();
			var $shipping_city = $('form.woocommerce-checkout #shipping_city');
	 		var city_value = $shipping_city.val();
	 		$shipping_city.html(option_value+city_value+cargando_comunas);
	 		jQuery.ajax({
					type: "post",
					url: woocommerce_params.ajax_url,
					dataType: 'json',
					data: obtener_comuna_desde_region+region+"&nonce="+woocommerce_postalchile.nonce,
					success: function(result){
						if (result.comunas) {
							var comunas_html = '';
							for(var k in result.comunas) {
								comunas_html += option_value + k + '" ' + (city_value === k ? selected_selected:'') + '>' + result.comunas[k] + '</option>';
							}
							$shipping_city.html(comunas_html);
						} else {
							$shipping_city.html('');
						}
					}
				});
	 	});
	 	/////////////////////////////////////
	 	['shipping','billing'].forEach( function(source){ // NOSONAR

		var address_fields = '.woocommerce-MyAccount-content .woocommerce-address-fields #';
	 	if ( $(address_fields + source+'_city').length ) {
	 		var $user_city = $(address_fields + source+'_city');
	 		var $user_state = $(address_fields + source+'_state');
	 		if ($user_state.get(0) && $user_state.get(0).tagName === 'SELECT' && $user_city.attr('type') === 'text') {

	 			var $user_city_parent = $user_city.parents('span');
	 			var user_city_value = $user_city.val();

	 			var $new_user_city = $('<select name="'+
				 						source+
										'_city" style="width: 100%" class="wc-enhanced-select" id="billing_city" placeholder="' +
										$user_city.attr('placeholder') +
										'" data-placeholder="' +
										$user_city.attr('placeholder') +
										'"><option value="' +
										user_city_value +
										'" selected="selected"> Cargando Comuna...</option></select>');

	 			$user_city_parent.append($new_user_city);
			 	$user_city.remove();
				$new_user_city.select2( { minimumResultsForSearch: 5 } );


				var user_state_value = $user_state.val();


				jQuery.ajax({
					type: "post",
					url: woocommerce_params.ajax_url,
					dataType: 'json',
					data: obtener_comuna_desde_region+user_state_value+"&nonce="+woocommerce_postalchile.nonce,
					success: function(result){
						if (result.comunas) {
							var comunas_html = '';
							for(var k in result.comunas) {
								comunas_html += option_value + k + '" ' + (user_city_value === k ? selected_selected:'') + '>' + result.comunas[k] + '</option>';
							}
							$new_user_city.html(comunas_html);
						} else {
							$new_user_city.html('');
						}
					}
				});

				$user_state.on('change', function(ev) {
			 		var region = $(ev.currentTarget).val();
			 		var user_city_value2 = $new_user_city.val();
			 		$new_user_city.html(option_value+user_city_value2+cargando_comunas);
			 		jQuery.ajax({
							type: "post",
							url: woocommerce_params.ajax_url,
							dataType: 'json',
							data: obtener_comuna_desde_region + region+"&nonce=" + woocommerce_params.nonce,
							success: function(result){
								if (result.comunas) {
									var comunas_html = '';
									for(var k in result.comunas) {
										comunas_html += option_value + k + '" ' + (user_city_value2 === k ? selected_selected:'') + '>' + result.comunas[k] + '</option>';
									}
									$new_user_city.html(comunas_html);
								} else {
									$new_user_city.html('');
								}
					}
						});
			 	});
	 		}	
	 	}
	 });

	 	$('a.tracking-link').on('click', function(ev) { // NOSONAR

	 		var old_text = $(ev.currentTarget).text();
	 		if (old_text === 'Cargando...') {
				return;
			}
	 		$(ev.currentTarget).text('Cargando...');
	 		jQuery.ajax({
					type: "post",
					url: woocommerce_params.ajax_url,
					dataType: 'json',
					data: "action=track_order&nonce="+woocommerce_postalchile.nonce+"&pid="+$(this).data('pid')+"&ot="+$(this).data('ot'),
					success: function(result){
						var data = {};
						$(ev.currentTarget).text(old_text);
						if (result.error) {
							alert(result.error);
							return;
						}
						if (result.response && result.response.data) {
							data = result.response;
						} else {
							data = result;
						}
						$(this).WCBackboneModal({
								template: 'wc-modal-track-order',
								variable : data
							});

						setTimeout(function(){
							if (data.data.trackingEvents.length) {
								var html = '';
								$.each(data.data.trackingEvents,function(index, item){
									html += '<tr><td>'+item.eventDate+'</td><td>'+item.eventHour+'</td><td>'+item.description+'</td><td></td></tr>'
								});
								$("#wc-chilexpress-events > tbody").html(html);
							} else {
								$("#wc-chilexpress-events > tbody > tr > td").text('No existen eventos aún para este envio.');
							}
						},500);
						
						
					}
				});
			
	 	});

	 	function updateShippingCartCalculatorLabel() {
		 	$("#shipping_method label").each(function(index, el) {
	 			if ($(el).text().indexOf('Chilexpress') > -1) {
		 			$(el)
					 	.html(
							 $(el).text()
							 .replace(
								 'Chilexpress',
								 ('<img src="' +
								 woocommerce_postalchile.base_url +
								 'imgs/logo-chilexpress.png" style="width: 120px; margin-right: 0.2em; margin-top:2px; margin-bottom:-2px;" />'))
							 );
		 		}
		 	});
	 	}

	 	$(document.body).on('change', '#shipping_method input[type=radio].shipping_method', function(ev){ 
  			for(var i = 100; i < 2000; i = i + 50) {
	  			setTimeout(function(){
	  				updateShippingCartCalculatorLabel();
	  			}, i);
  			}
		});

	 	$(document.body).on('updated_wc_div', function(ev) { 
	 		updateShippingCartCalculatorLabel();
	 	});
	 
	 	$(document.body).on('updated_checkout', function(ev) {
	 		updateShippingCartCalculatorLabel();
	 	});

	 	updateShippingCartCalculatorLabel();


 		/*$(document).on('click', '#shipping_method input:radio', function () {

 			var shipping_method = $(this).val();
 			var isCxp = shipping_method.includes('chilexpress_woo_oficial');
 			
 			setTimeout(
				function() 
			  	{
 					if(isCxp)
		 				$('.glosa-fecha-entrega').show("fast");
			  	}, 4000);
	    });

	    //Cuando carga desde 0 la pagina
	    var shipping_method = $('#shipping_method input:radio:checked').val();
	    var isCxp = shipping_method.includes('chilexpress_woo_oficial');

	    if(isCxp)
		 	$('.glosa-fecha-entrega').show("fast");
		*/
		
	 });

})( jQuery );
