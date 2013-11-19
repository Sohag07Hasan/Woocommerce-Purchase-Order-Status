<style>
		
	div.new_partial_paypment-form{
		display: none;
	}
	
	div.existing_partial_paypment-form{
		display: none;
	}
	
</style>


<?php 
	global $woocommerce, $wpdb;
	$status = get_post_meta($post->ID, '_purchase_order_payment_status', true);
	$status = ($status == "1") ? 1 : 0;	
	$time_stamp = get_post_meta($post->ID, '_purchase_order_payment_date', true);
?>

<div class="existing-payments">
	<div class="existing-payments-line">
		<p> 1:  12/12/2013  $600.23 Bank Transer <a class="existing-payments-line-edit" href="#">Edit</a></p>
		
		<div class="existing_partial_paypment-form">
			<input placeholder="dd/mm/yyyy" type="text" name="partial-payment-date[]" value="" style="width: 48%" >
			<input placeholder="amouont (USD)" type="text" name="partial-payment-amount[]" value="600.23" style="width: 48%" >
			
			<select style="width: 48%" name="partial-payment-paymenttype[]">
				<option value="">Select Payment Type</option>
				<option value="Credit Card">Credit Card</option>
				<option value="Bank Transfer">Bank Transfer</option>
				<option value="Cheque">Cheque</option>
				<option value="Cash">Cash</option>
			</select> &nbsp;
			<input name="partial_payment_add" style="width: 20%" type="button" class="button button-secondary" value="Ok" />
			<input name="partial_payment_cancel" style="width: 24%" type="button" class="button button-secondary" value="Cancel" /> 
		</div>				
	</div>
		
</div>

<h4>TPD: $<span class="partials-total">700.46</span> &nbsp; Left: $<span class="partials-left">50.00</span> &nbsp; <input type="checkbox" value="y"> Paid</h4>

<p><input id="add_a_new_partial_payment" type="button" class="button button-primary" value="Add Payment"></p>

<div class="new_partial_paypment-form">
	<input placeholder="dd/mm/yyyy" type="text" id="partial-payment-date" value="" style="width: 48%" >
	<input placeholder="amouont (USD)" type="text" id="partial-payment-amount" value="" style="width: 48%" >
	
	<select id="partial-payment-paymenttype" style="width: 48%" name="partial-payment-paymenttype">
		<option value=""><?php _e( 'Select Payment Type', 'woocommerce' ); ?></option>
		<option value="Credit Card">Credit Card</option>
		<option value="Bank Transfer">Bank Transfer</option>
		<option value="Cheque">Cheque</option>
		<option value="Cash">Cash</option>
	</select> &nbsp;
	<input id="new_partial_payment_add" style="width: 20%" type="button" class="button button-secondary" value="Add" />
	<input id="new_partial_payment_cancel" style="width: 24%" type="button" class="button button-secondary" value="Cancel" /> 
</div>