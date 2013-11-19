<?php 
global $woocommerce;
global $wpdb;
	$status = get_post_meta($post->ID, '_purchase_order_payment_status', true);
	$status = ($status == "1") ? 1 : 0;
	
	$time_stamp = get_post_meta($post->ID, '_purchase_order_payment_date', true);
?>

<?php if($time_stamp > 100): ?>
	
	<p>Date: <?php echo date('d-m-Y', $time_stamp); ?></p>
	
<?php endif; ?>
<input <?php if($status == 1){ echo 'checked="checked"'; } ?> type="checkbox" id="purchase_order_payment_status" name="purchase_order_payment_status" value="1" /> 
<input type="hidden" name="pops" id="pops" value="">
&nbsp; &nbsp;
<label for="purchase_order_payment_status"> Paid </label>
<p class="form-field payments">
	<label for="payment_date">Payment Date:</label>
	<input disabled="disabled" type="text" style="width:232px;" class="short" name="payment_date" id="datepicker_03" value="<?php echo date('d-m-Y', $time_stamp); ?>" placeholder="DD-MM-YYYY"> </p>
	<?php
	//javascript
	global $woo_purchase_status;
			
?>
			<script>
				
				jQuery( "#datepicker_03" ).datepicker({
					dateFormat: 'dd-mm-yy',
					showOn: "button",
					buttonImage: "<?php echo $woo_purchase_status->get_uri() ?>images/calendar.gif",
					buttonImageOnly: true
				});
				
				//jQuery( "#datepicker_03" ).datepicker();
				
			</script>
			
			<p class="form-field paid_amount">
	<label for="paid_amount">Paid Amount:</label>
	<?php
	$data = get_post_meta( $post->ID );
		$amt = get_post_meta($post->ID, '_paid_amount', true);
		?>
	<input type="text" style="width:232px;" class="short" name="paid_amt" id="paid_amount" value="<?php echo $amt; ?>" placeholder="">
			</p>
	<p class="form-field _payment_method">
	<label for="paid_amount">Payment Type:</label>
		<?php $type = get_post_meta($post->ID, '_payment_type_', true);
		?>
	<select name="_payment_type" id="_payment_type" class="first">
					<option value=""><?php _e( 'Select an option', 'woocommerce' ); ?></option>
					<option value="Credit Card" <?php if($type == 'Credit Card'){ echo 'selected="selected"'; } ?>>Credit Card</option>
					<option value="Bank Transfer" <?php if($type == 'Bank Transfer'){ echo 'selected="selected"'; } ?>>Bank Transfer</option>
					<option value="Cheque" <?php if($type == 'Cheque'){ echo 'selected="selected"'; } ?>>Cheque</option>
					<option value="Cash" <?php if($type == 'Cash'){ echo 'selected="selected"'; } ?>>Cash</option>
					<?php
						/*$chosen_method 	= ! empty( $data['_payment_method'][0] ) ? $data['_payment_method'][0] : '';
						$found_method 	= false;

						if ( $woocommerce->payment_gateways() ) {
							foreach ( $woocommerce->payment_gateways->payment_gateways() as $gateway ) {
								if ( $gateway->enabled == "yes" ) {
									echo '<option value="' . esc_attr( $gateway->id ) . '" ' . selected( $chosen_method, $gateway->id, false ) . '>' . esc_html( $gateway->get_title() ) . '</option>';
									if ( $chosen_method == $gateway->id )
										$found_method = true;
								}
							}
						}

						if ( ! $found_method && ! empty( $chosen_method ) ) {
							echo '<option value="' . esc_attr( $chosen_method ) . '" selected="selected">' . __( 'Other', 'woocommerce' ) . '</option>';
						} else {
							echo '<option value="other">' . __( 'Other', 'woocommerce' ) . '</option>';
						}*/
					?>
				</select></p>
	<?php if($status == 0){ ?>
	<script>
	$(document).ready(function(){
		$('.save_order').click(function(){
		if ($('#purchase_order_payment_status').is(':checked')) {
			$('#pops').val(1);
		}
		});
		});
		</script>
	<?php } ?>
	<script>
	$(document).ready(function(){
		$('.save_order').click(function(){
		if ($('#purchase_order_payment_status').is(':checked')) {
			//$('#pops').val(1);
			var inp = $('#datepicker_03');
			if (inp.val().length == "") {
			alert("Enter payment date");
			inp.focus();
			return false;
			}
			var inp1 = $('#paid_amount');
			if (inp1.val().length == "") {
			alert("Enter paid amount");
			inp1.focus();
			return false;
			}
			var inp2 = $('#_payment_type');
			if (inp2.val().length == "") {
			alert("Select payment type");
			inp2.focus();
			return false;
			}
			
		}
		//return false;
		});
		});
		
	</script>
	
