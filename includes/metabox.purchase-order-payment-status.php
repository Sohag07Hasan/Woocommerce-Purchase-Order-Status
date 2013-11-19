<style>
		
	div.new_partial_paypment-form{
		display: none;
	}
	
	div.existing_partial_paypment-form{
		display: none;
		margin-left: 1.5em;
	}
	
</style>


<?php 
	global $woocommerce, $wpdb;
	$status = get_post_meta($post->ID, '_purchase_order_payment_status', true);
	$status = ($status == "1") ? 1 : 0;	
	$time_stamp = get_post_meta($post->ID, '_purchase_order_payment_date', true);
	
	$partial_payment_info = $this->get_partial_payment_info($post->ID);	
	$paid_amount = get_post_meta($post->ID, '_paid_amount', true);
	$paid_amount = number_format( (double) $paid_amount, 2, '.', '' );
	
	$the_order = new WC_Order($post->ID);
	$left_amount = $the_order->get_total() - $paid_amount;
	
	//var_dump($partial_payment_info);
	
?>

<div class="existing-payments">
	
	<?php if($partial_payment_info): foreach($partial_payment_info as $key => $info):	?>
		
		<div class="existing-payments-line">
			<p> <?php echo $key+1; ?> <span><?php echo $info['date']; ?>  $<?php echo $info['amount']; ?>  <?php echo $info['type']; ?></span> <a class="existing-payments-line-edit" href="#">Edit</a> </p>
			<div class="existing_partial_paypment-form">
			<input placeholder="dd/mm/yyyy" type="text" name="partial-payment-date[]" value="<?php echo $info['date']; ?>" style="width: 42%" >
			<input placeholder="amouont (USD)" type="text" name="partial-payment-amount[]" value="<?php echo $info['amount']; ?>" style="width: 42%" >
			
			<select class="partial-payment-paymenttype" name="partial-payment-paymenttype[]">
				<option value="">Select Payment Type</option>
				<option <?php selected('Credit Card', $info['type']); ?> value="Credit Card">Credit Card</option>
				<option <?php selected('Bank Transfer', $info['type']); ?> value="Bank Transfer">Bank Transfer</option>
				<option <?php selected('Cheque', $info['type']); ?> value="Cheque">Cheque</option>
				<option <?php selected('Cash', $info['type']); ?> value="Cash">Cash</option>
			</select>
			<input type="hidden" name="partial-payment-paymenttype_tracking[]" value="<?php echo $info['type']; ?>" /> 
			&nbsp;<input style="width: 20%" type="button" class="button button-secondary partial_payment_add" value="Ok" />
			
		</div>	
		</div>
		
	<?php endforeach; endif; ?>
		
</div>

<h4>TPD: $<span class="partials-total"><?php echo $paid_amount; ?></span> &nbsp; Left: $<span class="partials-left"><?php echo $left_amount; ?></span> &nbsp; <input name="purchase_order_payment_status" value="1" type="checkbox" value="y"> Paid</h4>

<input type="hidden" name="order_total_amount" value="<?php echo $the_order->get_total(); ?>"  />

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