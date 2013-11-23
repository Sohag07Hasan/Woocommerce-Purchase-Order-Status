<?php 

if( ! class_exists( 'WP_List_Table' ) ) {
	if(!class_exists('WP_Internal_Pointers')){
		require_once( ABSPATH . '/wp-admin/includes/template.php' );
	}
	require_once( ABSPATH . '/wp-admin/includes/class-wp-list-table.php' );
}


class WooPaymentStatusListTable extends WP_List_Table{
	
	private $per_page;
	private $total_items;
	private $current_page;
	public $db;
	private $status;
	
	function __construct($status = ''){
		$this->status = $status;
		parent::__construct();
	}
	
	
	/*preparing items must overwirte the mother function*/
	function prepare_items(){
			
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
	
		$this->_column_headers = array($columns, $hidden, $sortable);
	
		//paginations
	//	$this->_set_pagination_parameters();
	
		//every elements
		$this->items = $this->populate_table_data();
	}
	
	
	//get the column names
	function get_columns(){
		if($_REQUEST['tab'] == "opaid") {
			$columns = array(
					'cb' => '<input type="checkbox" />',
					'order' => __('Order'),
					'email' => __('Email'),
					'phone' => __('Phone'),
					'name' => __('Name'),
					'total' => __('Order Total'),
					'tax' => __('Tax'),
					'diff1' => __('Difference'),
					'payment_type' => __('Payment Type'),
					'paid_date' => __('Paid Date'),
					'paid_amount' => __('Paid Amount'),
					'paid_tax' => __('Paid Tax')

			);
		} elseif($_REQUEST['tab'] == "upaid") {
			$columns = array(
					'cb' => '<input type="checkbox" />',
					'order' => __('Order'),
					'email' => __('Email'),
					'phone' => __('Phone'),
					'name' => __('Name'),
					'total' => __('Order Total'),
					'tax' => __('Tax'),
					'diff2' => __('Difference'),
					'payment_type' => __('Payment Type'),
					'paid_date' => __('Paid Date'),
					'paid_amount' => __('Paid Amount'),
					'paid_tax' => __('Paid Tax')

			);	
		}  elseif($_REQUEST['tab'] == "paid") {
			$columns = array(
					'cb' => '<input type="checkbox" />',
					'order' => __('Order'),
					'email' => __('Email'),
					'phone' => __('Phone'),
					'name' => __('Name'),
					'total' => __('Order Total'),
					'tax' => __('Tax'),
					'payment_type' => __('Payment Type'),
					'paid_date' => __('Paid Date'),
					'paid_amount' => __('Paid Amount'),
					'paid_tax' => __('Paid Tax')

			);	
		} elseif($_REQUEST['tab'] == "payments") {
			$columns = array(
					'cb' => '<input type="checkbox" />',
					'order' => __('Order'),
					'email' => __('Email'),
					'phone' => __('Phone'),
					'name' => __('Name'),
					'total' => __('Order Total'),
					'tax' => __('Tax'),
					'payment_no' => __('Payment No'),
					'payment_type' => 'Payment Type',
					'payment_date' => 'Date',
					'paid_amount' => __('Paid Amount'),
					'paid_tax' => __('Paid Tax')

			);	
		} elseif($_REQUEST['tab'] == "unpaid") {
			$columns = array(
					'cb' => '<input type="checkbox" />',
					'order' => __('Order'),
					'email' => __('Email'),
					'phone' => __('Phone'),
					'name' => __('Name'),
					'total' => __('Order Total'),
					'tax' => __('Tax')
			);	
		} else {
			$columns = array(
					'cb' => '<input type="checkbox" />',
					'order' => __('Order'),
					'email' => __('Email'),
					'phone' => __('Phone'),
					'name' => __('Name'),
					'total' => __('Order Total'),
					'tax' => __('Tax'),
					'payment_type' => __('Payment Type'),
					'paid_date' => __('Paid Date'),
					'paid_amount' => __('Paid Amount'),
					'paid_tax' => __('Paid Tax')

			);	
		}
	
		return $columns;
	}
	
	
	//make some column sortable
	function get_sortable_columns(){
		$sortable_columns = array(
				'order' => array('order', false),
				'email' => array('email', false),
				'name' => array('name', false),
				'total' => array('total', false),
				'tax' => array('tax', false),
				'phone' => array('phone', false)
		);
	
		return $sortable_columns;
	}
	
	
	//use this function for pagination
	private function _set_pagination_parameters(){
		//$this->current_page = $this->get_pagenum(); //it comes form mother class (WP_List_Table)
		
		/*
		global $wpdb;
		
		if($this->status == 'unpaid'){
			$sql = "SELECT count($wpdb->posts.ID) FROM $wpdb->posts  INNER JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) WHERE $wpdb->posts.post_type = 'shop_order' AND ($wpdb->posts.post_status = 'publish') AND ( ($wpdb->postmeta.meta_key = '_purchase_order_payment_status' AND CAST($wpdb->postmeta.meta_value AS CHAR) != '1') )";
		}
		else{
			$sql = "SELECT count($wpdb->posts.ID) FROM $wpdb->posts  INNER JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) WHERE $wpdb->posts.post_type = 'shop_order' AND ($wpdb->posts.post_status = 'publish') AND ( ($wpdb->postmeta.meta_key = '_purchase_order_payment_status' AND CAST($wpdb->postmeta.meta_value AS CHAR) = '1') )";
		}
		
		$this->total_items = $wpdb->get_var($sql);
		*/
				
		$this->set_pagination_args( array(
				'total_items' => $this->total_items,                  //WE have to calculate the total number of items
				'per_page'    => $this->per_page,                     //WE have to determine how many items to show on a page
				'total_pages' => ceil($this->total_items/$this->per_page)   //WE have to calculate the total number of pages
		) );
	}
	
	//populate the list table
	function populate_table_data(){
		//pagination
		$this->current_page = $this->get_pagenum(); //it comes form mother class (WP_List_Table)
		$this->per_page = -1;
		
		// If order status is blank
		/*$term_data = get_term_by( "slug", "pending", "shop_order_status", ARRAY_A);
		print_r($term_data);*/


		if( isset($_REQUEST['order_status']) && $_REQUEST['order_status'] == "" ) {
			unset($_REQUEST['order_status']);
		}

		if( isset($_REQUEST['start-date']) && $_REQUEST['start-date'] == "" ) {
			unset($_REQUEST['start-date']);
		}

		if( isset($_REQUEST['end-date']) && $_REQUEST['end-date'] == "" ) {
			unset($_REQUEST['end-date']);
		}

		if( isset($_REQUEST['order_payment_type']) && $_REQUEST['order_payment_type'] == "" ) {
			unset($_REQUEST['order_payment_type']);
		}

		// If Payment type is selected 
		$__payment_type = array();
		
		if($this->status == 'unpaid'){
			$args = $this->get_args_for_not_paid_latest();
		}
		elseif($this->status = 'payments'){
			$args = $this->get_args_for_payments();
		}
		else{
			$args = $this->get_args_for_paid();
		}
		$query = new WP_Query($args);
		
		//var_dump($query->request);
			
		
		$this->total_items = $query->found_posts;
		$this->_set_pagination_parameters();
		//var_dump($order_ids->request);
		
		$sanitized_data = array();
		global $wpdb;
		if($query){

			/*RND Experts*/
			$new_order_total = 0;
			$new_tax_total = 0;
			$total_paid_amount = 0;
			$total_paid_tax = 0;
			foreach ($query->posts as $order_id){
				$_order = new WC_Order($order_id);
				if($_order){
					


					$mylink = $wpdb->get_row("SELECT * FROM wp_followup_customer_orders WHERE order_id = ".$_order->id, ARRAY_A);
					//echo "<pre>";
					//print_r($_order);
					$odate = $_order->order_custom_fields['_order_total'][0];
					//echo $_order->id. " ".$mylink['price']." ".$odate. " ".$order_id."<br>";
					if($mylink['price'] >= $odate)
					{
						$_order->opaid = $mylink['price'] - $odate;
						if(($_order->opaid > 0) ? ($_order->opaid = "$".round($_order->opaid, 3)) : ($_order->opaid=""));
					}
					if($odate >= $mylink['price'])
					{
						$_order->upaid = $odate - $mylink['price'];
						if(($_order->upaid > 0) ? ($_order->upaid = "$".round($_order->upaid, 3)) : ($_order->upaid=""));
					}

					if($_REQUEST['tab'] == "opaid")
					{
						$paid_date = date('d-m-Y', $_order->order_custom_fields['_purchase_order_payment_date'][0]);
						if($_order->opaid != ""){

							$new_order_total = $new_order_total + $_order->order_total;
							$new_tax_total = $new_tax_total + $_order->get_total_tax();

							$paid_tax_calc = $_order->order_custom_fields['_paid_amount'][0] / 11;
							$total_paid_amount = $total_paid_amount + $_order->order_custom_fields['_paid_amount'][0];
							$total_paid_tax = $total_paid_tax + $paid_tax_calc;

							$sanitized_data[] = array(
									'ID' => $_order->id,
									'order' => '<a href="' . admin_url( 'post.php?post=' . absint( $_order->id ) . '&action=edit' ) . '"><strong>' . sprintf( __( 'Order %s', 'woocommerce' ), esc_attr( $_order->get_order_number() ) ) . '</strong></a> ',
									'email' => $_order->billing_email,
									'name' => $_order->billing_first_name . ' ' . $_order->billing_last_name,
									'phone' => $_order->billing_phone,
									'total' => $_order->get_formatted_order_total(),
									'tax' => woocommerce_price($_order->get_total_tax()),
									'diff1' => $_order->opaid,
									'payment_type' => $_order->order_custom_fields['_payment_type_'][0],
									'paid_date' => $paid_date,
									'paid_amount' => "$".$_order->order_custom_fields['_paid_amount'][0],
									'paid_tax'	=> "$".number_format( $paid_tax_calc , 2 )
									);
						}
					} elseif ($_REQUEST['tab'] == "upaid") {
						$paid_date = date('d-m-Y', $_order->order_custom_fields['_purchase_order_payment_date'][0]);
						if($_order->upaid != ""){
							$new_order_total = $new_order_total + $_order->order_total;
							$new_tax_total = $new_tax_total + $_order->get_total_tax();
							
							$paid_tax_calc = $_order->order_custom_fields['_paid_amount'][0] / 11;
							$total_paid_amount = $total_paid_amount + $_order->order_custom_fields['_paid_amount'][0];
							$total_paid_tax = $total_paid_tax + $paid_tax_calc;
							
							$sanitized_data[] = array(
									'ID' => $_order->id,
									'order' => '<a href="' . admin_url( 'post.php?post=' . absint( $_order->id ) . '&action=edit' ) . '"><strong>' . sprintf( __( 'Order %s', 'woocommerce' ), esc_attr( $_order->get_order_number() ) ) . '</strong></a> ',
									'email' => $_order->billing_email,
									'name' => $_order->billing_first_name . ' ' . $_order->billing_last_name,
									'phone' => $_order->billing_phone,
									'total' => $_order->get_formatted_order_total(),
									'tax' => woocommerce_price($_order->get_total_tax()),
									'diff2' => $_order->upaid,
									'payment_type' => $_order->order_custom_fields['_payment_type_'][0],
									'paid_date' => $paid_date,
									'paid_amount' => "$".$_order->order_custom_fields['_paid_amount'][0],
									'paid_tax'	=> "$".number_format( $paid_tax_calc , 2 )

									);
						}

					} 
					
					elseif($_REQUEST['tab'] == "payments"){ 
				
						
						global $woo_purchase_status;
						$partial_payment_info = $woo_purchase_status->get_partial_payment_info($_order->id);
						if(!$partial_payment_info){
							$partial_payment_info = array();
						}
						
						$sanitized_info = array();
												
						$partial_payments = $woo_purchase_status->get_partial_payment();
						
						if(isset($_REQUEST['start-date']) || isset($_REQUEST['end-date']) || isset($_REQUEST['payment_type'])){
							$where = ' 1=1 ';
							
							if(isset($_REQUEST['start-date']) && !empty($_REQUEST['start-date'])){
								$start_date = date('Y-m-d', strtotime($_REQUEST['start-date']));
								$where .= " and date >= '$start_date'";
							}
							if(isset($_REQUEST['end-date']) && !empty($_REQUEST['end-date'])){
								$end_date = date('Y-m-d', strtotime($_REQUEST['end-date']));
								$where .= " and date <= '$end_date'";
							}
							
							if(isset($_REQUEST['payment_type']) && !empty($_REQUEST['payment_type'])){
								$type = $_REQUEST['payment_type'];
								$where .= " and type like '$type'";
							}
							
							//var_dump($where); die();
							
							$partial_payment_info = $partial_payments->get_payments_by_condition($where);
						}
						else{
							$partial_payment_info = $partial_payments->get_payments_by('order_id', $_order->id);
						}
						
						if($partial_payment_info){
							foreach($partial_payment_info as $key => $info){
								$payment_number = $key+1 . '/' . count($partial_payment_info);
								if($key == 0){
									$sanitized_data[] = array(
										'ID' => $_order->id,
										'order' => '<a href="' . admin_url( 'post.php?post=' . absint( $_order->id ) . '&action=edit' ) . '"><strong>' . sprintf( __( 'Order %s', 'woocommerce' ), esc_attr( $_order->get_order_number() ) ) . '</strong></a> ',
										'email' => $_order->billing_email,
										'name' => $_order->billing_first_name . ' ' . $_order->billing_last_name,
										'phone' => $_order->billing_phone,
										'total' => $_order->get_formatted_order_total(),
										'tax' => woocommerce_price($_order->get_total_tax()),
										//'payment_details' => implode('<br/>', $sanitized_info),
										'payment_no' => $payment_number,
										'payment_type' => $info->type,
										'payment_date' => date('d/m/Y', strtotime($info->date)),
										'paid_tax'	=> woocommerce_price($info->amount / 11),
										'paid_amount'	=> woocommerce_price($info->amount),
									
									);
								}
								else{
									$sanitized_data[] = array(
										'ID' => '',
										'order' => '',
										'email' => '',
										'name' => '',
										'phone' => '',
										'total' => '',
										'tax' => '',
										//'payment_details' => implode('<br/>', $sanitized_info),
										'payment_no' => $payment_number,
										'payment_type' => $info->type,
										'payment_date' => date('d/m/Y', strtotime($info->date)),
										'paid_tax'	=> woocommerce_price($info->amount / 11),
										'paid_amount'	=> woocommerce_price($info->amount),
												
									);
								}
							}
						}
						else{						
							$sanitized_data[] = array(
								'ID' => $_order->id,
								'order' => '<a href="' . admin_url( 'post.php?post=' . absint( $_order->id ) . '&action=edit' ) . '"><strong>' . sprintf( __( 'Order %s', 'woocommerce' ), esc_attr( $_order->get_order_number() ) ) . '</strong></a> ',
								'email' => $_order->billing_email,
								'name' => $_order->billing_first_name . ' ' . $_order->billing_last_name,
								'phone' => $_order->billing_phone,
								'total' => $_order->get_formatted_order_total(),
								'tax' => woocommerce_price($_order->get_total_tax()),
								//'payment_details' => implode('<br/>', $sanitized_info),
								'payment_no' => 'N/A',
								'payment_type' => 'N/A',
								'payment_date' => 'N/A',
								'paid_tax'	=> woocommerce_price(0),
								'paid_amount'	=> woocommerce_price(0),
								
							);
						} 
					}
					
					else {
						
						$paid_date = date('d-m-Y', $_order->order_custom_fields['_purchase_order_payment_date'][0]);
						$difference_ptd = 0;
						$paid_tax_calc = $_order->order_custom_fields['_paid_amount'][0] / 11;
						$total_paid_amount = $total_paid_amount + $_order->order_custom_fields['_paid_amount'][0];
						$total_paid_tax = $total_paid_tax + $paid_tax_calc;
						$difference_ptd = $_order->order_total - $_order->order_custom_fields['_paid_amount'][0];
						//$difference_ptd = $difference_ptd > 0 ? "- $".$difference_ptd : "+ $".($difference_ptd * -1);

						if($difference_ptd > 0) {
							$difference_ptd = "- $".number_format($difference_ptd, 2);
						} else {
							$difference_ptd = -($difference_ptd);
							$difference_ptd = "+ $".number_format($difference_ptd, 2);
						}

						$new_order_total = $new_order_total + $_order->order_total ;
						$new_tax_total = $new_tax_total + $_order->get_total_tax();
						// echo ":".$_order->get_formatted_order_total();
						$sanitized_data[] = array(
							'ID' => $_order->id,
							'order' => '<a href="' . admin_url( 'post.php?post=' . absint( $_order->id ) . '&action=edit' ) . '"><strong>' . sprintf( __( 'Order %s', 'woocommerce' ), esc_attr( $_order->get_order_number() ) ) . '</strong></a> ',
							'email' => $_order->billing_email,
							'name' => $_order->billing_first_name . ' ' . $_order->billing_last_name,
							'phone' => $_order->billing_phone,
							'total' => $_order->get_formatted_order_total(),
							'tax' => woocommerce_price($_order->get_total_tax()),
							'payment_type' => $_order->order_custom_fields['_payment_type_'][0],
							'paid_date' => $paid_date,
							'paid_amount' => "$".$_order->order_custom_fields['_paid_amount'][0],
							'paid_tax'	=> "$".number_format( $paid_tax_calc , 2 ),
							'amount_ptd'	=> "$".$_order->order_custom_fields['_paid_amount'][0],
							'difference_ptd'	=> $difference_ptd
							);
					}
				}
			}

			
		}	
		
		wp_reset_query();
		echo "<table class='wp-list-table widefat fixed woocommerce_page_woocommerce-payment-status'>
			<thead>
				<tr>
					<th>Order Total</th>
					<th>Tax Total</th>
					<th>Total Paid Amount</th>
					<th>Total Paid Tax</th>
				</tr>
			</thead>

			<tbody>
				<tr>
					<td>$".$new_order_total."</td>
					<td>$".$new_tax_total."</td>
					<td>$".$total_paid_amount."</td>
					<td>$".number_format($total_paid_tax,2)."</td>
				</tr>
			</tbody>
		</table>";
		return $sanitized_data;
	}

	
	/**
	 * query arguments for not paid
	 * */
	function get_args_for_not_paid_latest(){
		if( isset($_REQUEST['payment_type']) && $_REQUEST['payment_type'] != "") {
			$__payment_type = array(
					'key' => '_payment_type_',
					'value' => $_REQUEST['payment_type'],
					'compare' => '='
				);
		}
		
		$args = array(
			
			'post_type' => 'shop_order',
			'posts_per_page' => $this->per_page,
			'paged' => $this->current_page,
			
			/*
			'tax_query' => array(
					array(
						'taxonomy' => 'shop_order_status',
						'field' => 'slug',
						'terms' => $_REQUEST['order_status']
					)),
			*/
			'meta_query' => array(
						 array(
								'key' => '_purchase_order_payment_status',
								'value' => '1',
								'compare' => '!='
							)


						),
			
						
									
			'fields' => 'ids',
			'post_status' => 'publish'
			
								
					
		);
		
		if(isset($_REQUEST['order_status'])){
			$args['tax_query'][] = array(
				'taxonomy' => 'shop_order_status',
				'field' => 'slug',
				'terms' => $_REQUEST['order_status']
			);
		}
		
		if(isset($_REQUEST['start-date']) || isset($_REQUEST['end-date'])){			
			add_filter( 'posts_where', array(&$this, 'posts_where') );		
		}
		
		
		// If payment type is selected
		if( !empty($__payment_type) ) array_push($args['meta_query'], $__payment_type);


		if(isset($_GET['orderby'])){
			$args = $this->get_order_by($args);
		}
		
		
		//var_dump($args); //exit;
		
		return $args;
	}
	
	
	/**
	 * query arguments for payments
	 * */
	function get_args_for_payments(){
			
		$args = array(
					
				'post_type' => 'shop_order',
				'posts_per_page' => $this->per_page,
				'paged' => $this->current_page,
				'fields' => 'ids',
				'post_status' => 'publish',
							
		);
	
		if(isset($_REQUEST['order_status'])){
			$args['tax_query'][] = array(
					'taxonomy' => 'shop_order_status',
					'field' => 'slug',
					'terms' => $_REQUEST['order_status']
			);
		}
	
		//if(isset($_REQUEST['start-date']) || isset($_REQUEST['end-date'])){
			add_filter('posts_join_request', array(&$this, 'posts_join'), 100, 2);
			add_filter( 'posts_where_request', array(&$this, 'posts_where'), 100, 2);			
			//add_filter('posts_request', array(&$this, 'posts_request'), 100, 2);
			add_filter('posts_groupby_request', array(&$this, 'posts_groupby_request'), 100, 2);
		//}
	
	
		// If payment type is selected
		if( !empty($__payment_type) ) array_push($args['meta_query'], $__payment_type);
	
	
		if(isset($_GET['orderby'])){
			$args = $this->get_order_by($args);
		}
	
	
		//var_dump($args); //exit;
	
		return $args;
	}
	
	function posts_groupby_request($group_by, $q){
		global $wpdb;
		$group_by = " {$wpdb->posts}.ID ";
		return $group_by;
	}
	
	function posts_where($where, $q){
		global $wpdb, $woo_purchase_status;
		$partial_payments = $woo_purchase_status->get_partial_payment();
		
		if(isset($_REQUEST['start-date']) && !empty($_REQUEST['start-date'])){
			$start_date = date('Y-m-d', strtotime($_REQUEST['start-date']));		
			$where .= " AND ({$partial_payments->db_table}.date >= '$start_date')";
		}
		if(isset($_REQUEST['end-date']) && !empty($_REQUEST['end-date'])){
			$end_date = date('Y-m-d', strtotime($_REQUEST['end-date']));		
			$where .= " AND ({$partial_payments->db_table}.date <= '$end_date')";
		}
		
		if(isset($_REQUEST['payment_type']) && !empty($_REQUEST['payment_type'])){
			$type = $_REQUEST['payment_type'];
			$where .= " AND ({$partial_payments->db_table}.type like '$type') ";
		}
		
		return $where;
		
	}
	
	//post join
	function posts_join($join, $q){
		global $wpdb, $woo_purchase_status;
		$partial_payments = $woo_purchase_status->get_partial_payment();
		
		if(isset($_REQUEST['start-date']) || isset($_REQUEST['end-date']) || isset($_REQUEST['payment_type'])){
			$join .= " INNER JOIN $partial_payments->db_table ON ($wpdb->posts.ID = $partial_payments->db_table.order_id) ";
		}				
		return $join;
	}
	
	function posts_request($request, $q){
		var_dump($request);
		return $request;
	}
	
	
	function get_date_param($date, $type){
		$date = explode('-', $date);
		
		if(count($date) == 3){
			return array(
				'day' => $date[0],
				'month' => $date[1],
				'year' => $date[2]
			);
		}
		else{
			return array(
				'day' => 1,
				'month' => 1,
				'year' => $type == 1 ? 2030 : 2000
			);
		}
	}
	
	function get_args_for_paid() {

		if( isset($_REQUEST['payment_type']) && $_REQUEST['payment_type'] != "") {
			$__payment_type = array(
					'key' => '_payment_type_',
					'value' => $_REQUEST['payment_type'],
					'compare' => '='
				);
		}

		// If Start date, End date and Order status are set
		if( isset($_REQUEST['start-date']) && isset($_REQUEST['end-date']) && isset($_REQUEST['order_status']) ){
			$st_dt = explode("-",$_REQUEST['start-date']);
			$ed_dt = explode("-",$_REQUEST['end-date']);
			$start = $st_dt[2]."-".$st_dt[1]."-".$st_dt[0]." 00:00:00";
			$end = $ed_dt[2]."-".$ed_dt[1]."-".$ed_dt[0]." 23:59:59";

			$args = array(
				'post_type' => 'shop_order',
				'posts_per_page' => $this->per_page,
				'paged' => $this->current_page,
				'tax_query' => array(
					array(
						'taxonomy' => 'shop_order_status',
						'field' => 'slug',
						'terms' => $_REQUEST['order_status']
					)),
				'meta_query' => array(
						array(
								'key' => '_purchase_order_payment_date',
            					'value' => array( strtotime($start) , strtotime($end)),
            					'compare' => 'BETWEEN',
            					'type' => 'UNSIGNED'

							),

						array(
								'key' => '_purchase_order_payment_status',
								'value' => '1',
								'compare' => $this->status == 'paid' ? '=' : '!='
							)


						),
				'fields' => 'ids',
				'post_status' => 'publish'
				);
		} // If only Start and End dates are selected. It will return the results for default status
		elseif ( isset( $_REQUEST['start-date'] ) && isset( $_REQUEST['end-date'] ) ){
			
			$st_dt = explode("-",$_REQUEST['start-date']);
			$ed_dt = explode("-",$_REQUEST['end-date']);
			$start = $st_dt[2]."-".$st_dt[1]."-".$st_dt[0]." 00:00:00";
			$end = $ed_dt[2]."-".$ed_dt[1]."-".$ed_dt[0]." 23:59:59";
			//print_r($strt);
			$args = array(
				'post_type' => 'shop_order',
				'posts_per_page' => $this->per_page,
				'paged' => $this->current_page,
				'meta_query' => array(
						array(
								'key' => '_purchase_order_payment_status',
								'value' => '1',
								'compare' => $this->status == 'paid' ? '=' : '!='

							),
						array(
								'key' => '_purchase_order_payment_date',
            					'value' => array( strtotime($start) , strtotime($end)),
            					'compare' => 'BETWEEN',
            					'type' => 'UNSIGNED'

							)
						),
				'fields' => 'ids',
				'post_status' => 'publish'
				);
		} // Start data and Order status are set
		elseif ( isset($_REQUEST['start-date']) && isset($_REQUEST['order_status']) ) {
			$st_dt = explode("-",$_REQUEST['start-date']);
			$start = $st_dt[2]."-".$st_dt[1]."-".$st_dt[0]." 00:00:00";
			$end = $st_dt[2]."-".$st_dt[1]."-".$st_dt[0]." 23:59:59";
			
			//print_r($strt);
			$args = array(
				'post_type' => 'shop_order',
				'posts_per_page' => $this->per_page,
				'paged' => $this->current_page,
				'tax_query' => array(
					array(
						'taxonomy' => 'shop_order_status',
						'field' => 'slug',
						'terms' => $_REQUEST['order_status']
					)),
				'meta_query' => array(
						array(
								'key' => '_purchase_order_payment_status',
								'value' => '1',
								'compare' => $this->status == 'paid' ? '=' : '!='

							),
						array(
								'key' => '_purchase_order_payment_date',
            					'value' => array( strtotime($start) , strtotime($end)),
            					'compare' => 'BETWEEN',
            					'type' => 'UNSIGNED'

							)
						),
				'fields' => 'ids',
				'post_status' => 'publish'
				);
		} // Only order status is selected
		elseif ( isset($_REQUEST['order_status']) ) {
			$args = array(
				'post_type' => 'shop_order',
				'posts_per_page' => $this->per_page,
				'paged' => $this->current_page,
				//'category_name' => $_REQUEST['order_status'],
				'tax_query' => array(
					array(
						'taxonomy' => 'shop_order_status',
						'field' => 'slug',
						'terms' => $_REQUEST['order_status']
					)),
				'meta_query' => array(
						array(
								'key' => '_purchase_order_payment_status',
								'value' => '1',
								'compare' => $this->status == 'paid' ? '=' : '!='
								)
						),
				'fields' => 'ids',
				'post_status' => 'publish'
				);
		} // Only start data is selected
		elseif( isset($_REQUEST['start-date']) ) {
			$st_dt = explode("-",$_REQUEST['start-date']);
			$start = $st_dt[2]."-".$st_dt[1]."-".$st_dt[0]." 00:00:00";
			$end = $st_dt[2]."-".$st_dt[1]."-".$st_dt[0]." 23:59:59";
			$args = array(
				'post_type' => 'shop_order',
				'posts_per_page' => $this->per_page,
				'paged' => $this->current_page,
				'meta_query' => array(
						array(
								'key' => '_purchase_order_payment_status',
								'value' => '1',
								'compare' => $this->status == 'paid' ? '=' : '!='

							),
						array(
								'key' => '_purchase_order_payment_date',
            					'value' => array( strtotime($start) , strtotime($end)),
            					'compare' => 'BETWEEN',
            					'type' => 'UNSIGNED'

							)
						),
				'fields' => 'ids',
				'post_status' => 'publish'
				);
		} // If nothing selected
		else {
			$args = array(
				'post_type' => 'shop_order',
				'posts_per_page' => $this->per_page,
				'paged' => $this->current_page,
				'meta_query' => array(
						array(
								'key' => '_purchase_order_payment_status',
								'value' => '1',
								'compare' => $this->status == 'paid' ? '=' : '!='

							)
						),
				'fields' => 'ids',
				'post_status' => 'publish'
				);
		}

		// If payment type is selected
		if( !empty($__payment_type) ) array_push($args['meta_query'], $__payment_type);


		if(isset($_GET['orderby'])){
			$args = $this->get_order_by($args);
		}

		return $args;
	}

	/**
	 * Order by manipulation
	 * */
	function get_order_by($args){
		switch($_GET['orderby']){
			case 'email':
				$args['meta_key'] = '_billing_email';
				$args['orderby'] = 'meta_value';
				break;
			case 'name':
				$args['meta_key'] = '_billing_first_name';
				$args['orderby'] = 'meta_value';
				break;
			case 'total':
				$args['meta_key'] = '_order_total';
				$args['orderby'] = 'meta_value_num';
				break;
			case 'tax':
				$args['meta_key'] = '_order_tax';
				$args['orderby'] = 'meta_value_num';
				break;
			default:
				$args['orderby'] = 'ID';
				$args['order'] = 'desc';
		}
		
		if(isset($_GET['order'])){
			$args['order'] = $_GET['order'];
		}
		
		return $args;
	}
	
	
	/* Utility that are mendatory   */
	
	/* checkbox for bulk action*/
	function column_cb($item) {
		if(empty($item['ID'])) return '';
		return sprintf(
			'<input type="checkbox" name="order_id[]" value="%s" />', $item['ID']
		);
	}
	
	/* default column checking and it is must */
	function column_default($item, $column_name){
		switch($column_name){
			case "ID":
			case "order":
			case "email":
			case "name":
			case "total":
			case "tax": 
			case 'phone':
			case 'diff1':
			case 'diff2':
			case 'paid_date':
			case 'payment_type':
			case 'paid_amount':
			case 'paid_tax':
			case 'amount_ptd':
			case 'difference_ptd':
			case 'payment_type':
			case 'payment_no':
			case 'payment_date':		
				return $item[$column_name];
				break;
			case 'payment_details':
				return $this->payment_details($item[$column_name]);
				break;
			default:
				var_dump($item);
					
		}
	}
	
	function payment_details($payment_details){
		if(count($payment_details) > 0){
			foreach($payment_details as $key => $info){
				echo '<p class="payment_info">';
				echo $key + 1;
				echo ': ' . woocommerce_price($info['amount']) . ' ';
				echo $info['date'] . '<br/>' . $info['type'] ;
				echo '</p>';
			}
		}
		else{
			echo 'No partial paypment';
		}
	}
	
	//bulk actions initialization
	function get_bulk_actions() {
		$actions = array(
				'print'    => 'Print'
		);
		return $actions;
	}
	
	
	//extra navigation 
	function extra_tablenav($which){
		global $woo_purchase_status;
		if($which == 'top'){
			echo '<div class="alignleft actions">';
			echo '<input value="'.$_GET['start-date'].'" id="datepicker_01" type="text" placeholder="Start date" name="start-date" > &nbsp;';
			echo '<input value="'.$_GET['end-date'].'" id="datepicker_02" type="text" placeholder="End date" name="end-date" > &nbsp;';
			
			// Display the payment type if the tab is not UNPAID
			if( $_GET['tab'] != 'unpaid' ) {
	       		echo "<select id=\"payment_type\" name=\"payment_type\" class=\"chosen_select\">";
				echo "	<option value=\"\">Select Payment Type</option>";
				echo "	<option ".selected('Credit Card', $_REQUEST['payment_type'])." value=\"Credit Card\">Credit Card</option>";
				echo "	<option ".selected('Bank Transfer', $_REQUEST['payment_type'])." value=\"Bank Transfer\">Bank Transfer</option>";
				echo " 	<option ".selected('Cheque', $_REQUEST['payment_type'])." value=\"Cheque\">Cheque</option>";
				echo "	<option ".selected('Cash', $_REQUEST['payment_type'])." value=\"Cash\">Cash</option>";
				echo "</select>";
			}

			echo "<select id=\"order_status\" name=\"order_status\" class=\"chosen_select\">";
			$statuses = (array) get_terms( 'shop_order_status', array( 'hide_empty' => 0, 'orderby' => 'id' ) );
			echo "	<option value=\"\">Select an order status</option>";
			foreach ( $statuses as $status ) {
				echo '<option ' . selected($status->slug, $_REQUEST['order_status'], false) . ' value="' . esc_attr( $status->slug ) . '">' . esc_html__( $status->name, 'woocommerce' ) . '</option>';
			}
			echo "</select>";

			

			echo '<input type="submit" class="button" name="date-filter" value="Filter">';
			echo '</div>';
			//javascript
			
?>
			</form>
			<script>
				

				jQuery(document).ready(function(){

					jQuery("#doaction").click(function(){
						if( jQuery("#doaction").prev().val() == "print" ) {
							printDiv();
							return false;
						}
						
					});

				});

				jQuery( "#datepicker_01" ).datepicker({
					dateFormat: 'dd-mm-yy',
					showOn: "button",
					buttonImage: "<?php echo $woo_purchase_status->get_uri() ?>images/calendar.gif",
					buttonImageOnly: true
				});

				jQuery( "#datepicker_02" ).datepicker({
					dateFormat: 'dd-mm-yy',
					showOn: "button",
					buttonImage: "<?php echo $woo_purchase_status->get_uri() ?>images/calendar.gif",
					buttonImageOnly: true
				});
				

				function printDiv() {
		            //Get the HTML of div
		           // return;
		            var oldPage = document.body.innerHTML;

		            jQuery("#adminmenuback").remove();
		            jQuery(':checkbox').parent().remove();jQuery(':checkbox').remove();
		            jQuery('#adminmenu').remove();
					jQuery('.nav-tab-wrapper').remove();
					jQuery('.updated').remove();
					jQuery('#setting-error-tgmpa').remove();
					jQuery('.update-nag').remove();
					jQuery('.tablenav').remove();
					jQuery(document).find('a').attr('href','javascipt:void(0)');
					jQuery(document).find('a').css("color","#555")
					jQuery('#wpcontent').css("margin-left", 0);
					jQuery('#wpfooter').css("margin-left", 0);
					jQuery(document).find('td').removeProp('class');jQuery(document).find('th').removeProp('class');
					jQuery(document).find('td').css('font-size','11px');jQuery(document).find('th').removeProp('font-size','11px');
					jQuery("#order").css("width","115px");
					jQuery("#email").css("width","250px");
					jQuery("#phone").css("width","125px");
					jQuery("#name").css("width","105px");
					jQuery("#total").css("width","105px");
					jQuery("#tax").css("width","105px");
					jQuery("#payment_type").css("width","125px");
					jQuery("#paid_date").css("width","125px");
					jQuery("#paid_amount").css("width","80px");
					jQuery("#paid_tax").css("width","80px");
					jQuery("#diff2").css("width","80px");
					jQuery("#diff1").css("width","80px");

					jQuery(".manage-column").css("font-size", "9px");

					var wrap = jQuery(".wrap").html();
		            var divElements = jQuery(".wp-list-table").html();
		            //Get the HTML of whole page
		            var head = jQuery(document).find('head').html();
		            //Reset the page's HTML with div's HTML only
		            document.body.innerHTML = 
		              "<html><head>"+head+"<style type='text/css' media='print' >@media print{@page {size: landscape} a {color: #999} .widefat td {font-size: 8px !important; vertical-align: top; padding:0px !important;} } .widefat td { font-size: 9px;padding: 0px !important;vertical-align: top;}</style></head><body><div class='wrap'>" + wrap + "</div></body>";

		            //Print Page
		           window.print();

		            //Restore orignal HTML
		           document.body.innerHTML = oldPage;

		          
		        }
			</script>

<?php 
			
		}
	}
	
}



?>
