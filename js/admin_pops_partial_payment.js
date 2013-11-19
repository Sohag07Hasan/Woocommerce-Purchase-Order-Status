jQuery(document).ready(function($){
	
	//add new payment
	$('#add_a_new_partial_payment').bind('click', function(){
		$('div.new_partial_paypment-form').slideDown();
		
		//if cancel button is pressed
		$('#new_partial_payment_cancel').bind('click', function(){
			$('div.new_partial_paypment-form').slideUp();
		});
		
		//now if add button is pressed
		$('#new_partial_payment_add').unbind('click')
		$('#new_partial_payment_add').bind('click', function(){
			var ids = ['#partial-payment-date', '#partial-payment-amount', '#partial-payment-paymenttype'];
			var invalid_ids = new Array();
			var valid_ids = new Array();
						
			for(i=0; i<ids.length; i++){				
				if($(ids[i]).val() == ""){
					invalid_ids[i] = ids[i];
				}
				else{
					valid_ids[i] = ids[i];
				}
			}			
			
			//valid and ivalid styling and alert
			if(valid_ids.length > 0){
				$.each(valid_ids, function(index, value){ $(value).css({'border':'.1em solid #4D4D4D'}); });
			}			
			
			if(invalid_ids.length > 0){
				$.each(invalid_ids, function(index, value){ $(value).css({'border':'.1em solid #FF0000'}); });
				alert('Please check the fields');
				return false;
			}
			
			//now add new element			
			var new_element = '<div class="existing-payments-line"> <p> %line_number%: %date%  $%amount%  %payment_type% <a class="existing-payments-line-edit" href="#">Edit</a> </p> <div class="existing_partial_paypment-form"><input placeholder="dd/mm/yyyy" type="text" name="partial-payment-date[]" value="%date%" style="width: 42%" > <input placeholder="amouont (USD)" type="text" name="partial-payment-amount[]" value="%amount%" style="width: 42%" >';
			new_element += '<select name="partial-payment_paymenttype[]" style="width: 48%">';
			new_element += '<option value="">Select Payment Type</option>';
			
			var selected_payment_type = $('#partial-payment-paymenttype').val();
			var payment_types = ['Credit Card', 'Bank Transfer', 'Cheque', 'Cash'];
			$.each(payment_types, function(index, value){
				if(value == selected_payment_type){
					new_element += '<option value="'+value+'" selected="selected">'+value+'</option>';
				}
				else{
					new_element += '<option value="'+value+'">'+value+'</option>';
				}
				
			});
			
			new_element += '</select> &nbsp;<input style="width: 20%" type="button" class="button button-secondary partial_payment_add" value="Ok" /> </div>	</div>';
			
			//now replacing with original values
			new_element = new_element.replace(/%line_number%/g, $('div.existing-payments').children('div.existing-payments-line').length + 1);
			new_element = new_element.replace(/%amount%/g, $('#partial-payment-amount').val());
			new_element = new_element.replace(/%date%/g, $('#partial-payment-date').val());		
			new_element = new_element.replace(/%payment_type%/g, $('#partial-payment-paymenttype').val());
			$('div.existing-payments').append(new_element);
			
			update_price();
			
		});
		
	});
	
	
	//now modify the exising paypment
	$('a.existing-payments-line-edit').unbind('click');
	$('a.existing-payments-line-edit').live('click', function(){
		$(this).parent().siblings('div.existing_partial_paypment-form').slideDown();
	});
	
	$('.partial_payment_add').unbind('click');
	$('.partial_payment_add').live('click', function(){
		var new_date = $(this).siblings('input[name="partial-payment-date[]"]').val();
		var new_amount = $(this).siblings('input[name="partial-payment-amount[]"]').val();
		var new_type = $(this).siblings('select[name="partial-payment-paymenttype[]"]').val();
		$(this).parent().siblings('p').children('span').html( new_date + ' $' + new_amount + ' ' + new_type );
		$(this).parent().slideUp();
		update_price();
		
		return false;
	});	
		
	
	//update price
	var update_price = function(){
		//now updating the total amouont
		var new_amount = 0;
		$.each($('input[name="partial-payment-amount[]"]'), function(index, field){
			new_amount += Number($(field).val());
		});
		
		$('span.partials-total').html(new_amount);	
	};
	
});