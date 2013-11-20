<?php 
	$tabs = array(
			'paid' => __('Paid'),
			'unpaid' => __('Not Paid'),
			'opaid' => __('Over Paid'),
			'upaid' => __('Under Paid'),
			'payments' => __('Payments')
			);
	
	$page_url = admin_url('admin.php?page=woocommerce-payment-status');
?>
<div class="wrap">
	<h2 class="nav-tab-wrapper">
	<?php 
		foreach($tabs as $key => $display){
			if( ( isset($_GET['tab']) && $_GET['tab'] == $key ) || ( empty($_GET['tab'] ) && $key == 'paid' ) ){
				$class = 'nav-tab-active nav-tab';
			}
			else{
				$class = 'nav-tab';
			}
			
			echo '<a class="' . $class . '" href="' . add_query_arg(array('tab' => $key), $page_url) . '">' . $display .'</a>';
		}
	?>
	</h2>
	
	<?php 
		if(isset($_GET['tab']) && $_GET['tab'] == 'unpaid'){
			include $this->get_this_directory() . 'includes/tabs/unpaid.php';
		}
		elseif (isset($_GET['tab']) && $_GET['tab'] == 'opaid'){
			include $this->get_this_directory() . 'includes/tabs/opaid.php';	
		}
		elseif (isset($_GET['tab']) && $_GET['tab'] == 'upaid'){
			include $this->get_this_directory() . 'includes/tabs/upaid.php';	
		}
		elseif (isset($_GET['tab']) && $_GET['tab'] == 'payments'){
			include $this->get_this_directory() . 'includes/tabs/payments.php';
		}
		else{
			include $this->get_this_directory() . 'includes/tabs/paid.php';
		}
	?>
	
</div>