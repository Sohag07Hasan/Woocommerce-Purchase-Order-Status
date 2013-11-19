<?php 
	global $woo_purchase_status;
	$list_table = $woo_purchase_status->get_list_table('unpaid');
	$list_table->views();
	$list_table->prepare_items();
	
	$action = admin_url('admin.php?page=woocommerce-payment-status&tab=unpaid');
?>
<div class="wrap">
	<h2> Unpaid Orders </h2>
	<form method="get" action="<?php echo $action; ?>" >
		<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
		<input type="hidden" name="tab" value="unpaid" />
		<?php $list_table->display(); ?>
	
	</form>
	
</div>
