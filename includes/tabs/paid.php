<?php 
	global $woo_purchase_status;
	$list_table = $woo_purchase_status->get_list_table('paid');
	$list_table->prepare_items();
	//$list_table->views();
?>
<div class="wrap">
	<h2> Paid Orders </h2>
	<form method="get">
		<input type="hidden" name="woocommerce-paid-status-bulk-action" value="y" />	
		<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
		<input type="hidden" name="payment-status" value="paid" />
		<?php $list_table->display(); ?>
		
	</form>
	
</div>