<?php 
	global $woo_purchase_status;
	$list_table = $woo_purchase_status->get_list_table('paid');
	$list_table->views();
	$list_table->prepare_items();
?>
<div class="wrap">
	<h2>Under Paid</h2>
	<form method="get">	
		<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
		<input type="hidden" name="tab" value="upaid" />
	<?php $list_table->display(); ?>
	
</div>