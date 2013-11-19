<?php
/*
 * Plugin Name: Woocommerce Purchase Order Payment Status
 * Author: Mahibul Hasan Sohag
 * Author Uri: http://sohag07hasan.elance.com
 * */

class WooPurchaseOrderPaymentStatus{
	
	function __construct(){
		//add a metabox in order details page
		add_action( 'add_meta_boxes', array(&$this, 'woocommerce_meta_boxes' ));
		
		//save the payment status for purchase order
		add_action('woocommerce_process_shop_order_meta', array(&$this, 'woocommerce_process_shop_order_meta'), 100, 2);
		
		//add new column for shop order table
		add_filter('manage_edit-shop_order_columns', array(&$this, 'woocommerce_edit_order_columns'), 100);
		
		//manage the custom column
		add_action('manage_shop_order_posts_custom_column', array(&$this, 'manage_newly_added_column'), 100);
		
		//assign the auto paid option
		add_action('init', array(&$this, 'auto_change_the_paid_status'), 1000);	

		//making the paid column sortable
		add_filter('manage_edit-shop_order_sortable_columns', array(&$this, 'making_the_paid_button_sortable'), 1000);		
		add_filter('request', array(&$this, 'process_the_sorting'), 1000);
		
		//more bulk actions
		add_action('admin_footer', array(&$this, 'add_more_bulk_actions'), 5);
		add_action('load-edit.php', array(&$this, 'process_more_bulk_actions'), 100);
		add_action( 'admin_notices', array(&$this, 'show_bulk_admin_notices' ), 100);		
		
		//add_action('awaiting_payment_paid', array(&$this, 'awaiting_payment_paid'), 15, 1);
		
		
		//admin page to inlcude reports
		add_action('admin_menu', array(&$this, 'woocommerce_payment_status_admin_menu'));
		
		//save the screen option
		add_filter('set-screen-option', array(&$this, 'save_screen_options'), 10, 3);
		
		//add javascript
		add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
		
		
		//list table bulk action
		add_action('admin_init', array(&$this, 'admin_init_to_handle_bulk_action'));

		// Style Css
		add_action( 'admin_enqueue_scripts', array(&$this, 'ready_to_ship_icon') );
		
		
		//keep un paid orders traceable\
		add_action('woocommerce_checkout_update_order_meta', array(&$this, 'woocommerce_checkout_update_order_meta'), 100, 2);
		
		//stop email notification
		add_action('woocommerce_process_shop_order_meta', array(&$this, 'woocommerce_process_shop_order_meta_remove_hooks'), 2, 2);
				
	}
	
	
	//stop sending emails when staus changed to processing from pending
	function woocommerce_process_shop_order_meta_remove_hooks($post_id, $posted){
		global $woocommerce;
		//stop admin notification when manually change pending to processing (paid)
		
		$woocommerce->woocommerce_email = $woocommerce->mailer();
		
		
		if($_POST['order_status'] == 'processing'){
			
			remove_all_actions('woocommerce_order_status_pending_to_processing_notification');
			
		}		 
		
	}
	
	
	//saving key
	function woocommerce_checkout_update_order_meta($order_id, $posted){
		update_post_meta($order_id, '_purchase_order_payment_status', '0');
	}	
	
	function woocommerce_meta_boxes(){
		add_meta_box( 'woocommerce-purchase-order-pyament-status', __( 'Purchase Order Payment Status', 'woocommerce' ), array(&$this, 'woocommerce_purchae_order_payemnt_satus'), 'shop_order', 'side', 'high' );
	}	
	
	function woocommerce_purchae_order_payemnt_satus($post){
		include dirname(__FILE__) . '/includes/metabox.purchase-order-payment-status.php';
	}	
	
	function woocommerce_process_shop_order_meta( $post_id, $post ){	
		
		global $wpdb, $woocommerce;
		
		if(isset($_POST['purchase_order_payment_status'])){
			//get the previous status
			$status = get_post_meta($post->ID, '_purchase_order_payment_status', true);
			$is_paid = ($status == "1") ? true : false;
			
			update_post_meta($post_id, '_purchase_order_payment_status', '1');			
			do_action('order_custom_paid_status', $post_id);
						
			if(!$is_paid){
				
				//update time
				update_post_meta($post_id, '_purchase_order_payment_date', current_time('timestamp'));
				
				$order = new WC_Order( $post_id );
				$woocommerce->woocommerce_email = $woocommerce->mailer();

				//admin notification						
				$woocommerce->woocommerce_email->emails['WC_Email_New_Order']->template_html ='emails/new-emails/admin-paid-status.php';
				$woocommerce->woocommerce_email->emails['WC_Email_New_Order']->title = "New Payment";														
				$woocommerce->woocommerce_email->emails['WC_Email_New_Order']->subject = sprintf("%s Made for %s from %s",  $order->payment_method_title, $order->get_order_number(), $order->billing_first_name . " " . $order->billing_last_name);
				$woocommerce->woocommerce_email->emails['WC_Email_New_Order']->heading = sprintf('A new %s payment has been made - Keep an Eye out for it',  $order->payment_method_title);
				$woocommerce->woocommerce_email->emails['WC_Email_New_Order']->trigger($post_id);
				
				//customer notification
				$woocommerce->woocommerce_email->emails['WC_Email_Customer_Processing_Order']->template_html = 'emails/new-emails/customer-paid-status.php';
				$woocommerce->woocommerce_email->emails['WC_Email_Customer_Processing_Order']->recipient = $order->billing_email;
				$woocommerce->woocommerce_email->emails['WC_Email_Customer_Processing_Order']->heading = 'Thank you for your payment';
				$woocommerce->woocommerce_email->emails['WC_Email_Customer_Processing_Order']->subject = 'Thank you for your payment';
				$woocommerce->woocommerce_email->emails['WC_Email_Customer_Processing_Order']->trigger($post_id);			
			}
			
		}
		else{
			update_post_meta($post_id, '_purchase_order_payment_status', '0');
		}

		//saving the partial payment
		if(count($_POST['partial-payment-date']) > 0){
			
			//var_dump($_POST['partial-payment-paymenttype']); exit;
			
			$payment_details = array();
			$total_amount = 0;
			
			foreach($_POST['partial-payment-date'] as $key => $date){
				if(empty($_POST['partial-payment-amount'][$key]) || empty($date)) continue;
				
				$payment_details[] = array(
					'date' => $date,
					'amount' => (float) $_POST['partial-payment-amount'][$key],
					'type' => $_POST['partial-payment-paymenttype_tracking'][$key]
				);
				
				$total_amount += (float) $_POST['partial-payment-amount'][$key];
			}
						
			//saving the payment info
			update_post_meta($post_id, '_paid_amount', $total_amount);
			update_post_meta($post_id, '_partial_payment_info', $payment_details);
		}
			
	}	
	
	
	function get_partial_payment_info($post_id){
		return get_post_meta($post_id, '_partial_payment_info', true);
	}
	
	
	function woocommerce_edit_order_columns($columns){
		$new_columns = array();
		if(is_array($columns)){
			foreach($columns as $key => $column){
				$new_columns[$key] = $column;
				if($key == 'total_cost'){
					$new_columns['purchase_order_status'] = __('Paid?', 'woocommerce');
					$new_columns['amount_ptd'] = __('Amount PTD', 'woocommerce');
					$new_columns['difference_ptd'] = __('Difference', 'woocommerce');
				}
			}
		}
		return $new_columns;
	}
	
	function manage_newly_added_column($column){
		global $post, $woocommerce, $the_order;
				
		if($column == 'purchase_order_status'){
			$status = get_post_meta($post->ID, '_purchase_order_payment_status', true);
			echo ($status == "1") ? "Yes" : "No"; 
		}

		if($column == 'amount_ptd') {
			$status = get_post_meta($post->ID, '_paid_amount', true);
			$status = $status <= 0 || $status == "" ? 0 : $status;
			echo "$".$status; 
		}

		if($column == 'difference_ptd') {
			$paid_amount = get_post_meta($post->ID, '_paid_amount', true);
			$order_total = get_post_meta($post->ID, '_order_total', true);
			$difference = $paid_amount - $order_total;
			if( $difference < 0 ) {
				echo "- $".(number_format($difference,2)*(-1));
			} else {
				echo "+ $".number_format($difference,2);
			}
		}
	}
		
	function auto_change_the_paid_status(){
		if(isset($_GET['order']) && isset($_GET['key'])){
			$order_id = $_GET['order'];
			$payment_status = get_post_meta($order_id, "_delayed_payment_status", true);
			if($payment_status == "complete"){
				update_post_meta($order_id, '_purchase_order_payment_status', '1');
			}
		
		}
	}
		
	function making_the_paid_button_sortable($columns){
		$columns['purchase_order_status'] = 'purchase_order_status';
		return $columns;
	}
	
	function process_the_sorting($vars){
		global $typenow, $wp_query;
		if ( $typenow != 'shop_order' )
			return $vars;	
		
		
		if($vars['orderby'] == 'purchase_order_status'){
			$vars = array_merge( $vars, array(
					'meta_key' 	=> '_purchase_order_payment_status',
					'orderby' 	=> 'meta_value'
			) );
		}
				
		return $vars;
	}
	
	
	//add new bulk actions in shop orders table
	function add_new_bulk_actions($actions){
		
		$actions['paid_status'] = "Change to Paid";
		$actions['unpaid_status'] = "Change to Unpaid";
		return $actions;
	}
	
	//add more bulk actions in shop page 
	//currently it only support javacript
	function add_more_bulk_actions(){
		global $post_type;
		if ( 'shop_order' == $post_type ) {
			?>
		      <script type="text/javascript">
		      jQuery(document).ready(function() {
		        jQuery('<option>').val('order_paid').text('<?php _e( 'Mark Paid', 'woocommerce' )?>').appendTo("select[name='action']");
		        jQuery('<option>').val('order_unpaid').text('<?php _e( 'Mark Unpaid', 'woocommerce' )?>').appendTo("select[name='action']");
		
		        jQuery('<option>').val('order_paid').text('<?php _e( 'Mark Paid', 'woocommerce' )?>').appendTo("select[name='action2']");
		        jQuery('<option>').val('order_unpaid').text('<?php _e( 'Mark Unpaid', 'woocommerce' )?>').appendTo("select[name='action2']");
		      });
		      </script>
		      <?php
		    }
	}
	
	//process more bulk actions
	function process_more_bulk_actions(){
		$wp_list_table = _get_list_table('WP_Posts_List_Table');
		$action = $wp_list_table->current_action();
		
		switch($action){
			case 'order_paid':
				$new_status = '1';
				$report_action = 'payment_completed';
				break;
			case 'order_unpaid':
				$new_status = '0';
				$report_action = 'payment_incompleted';
				break;
			default:
				return;
		}
		
		$changed = 0;		
		$post_ids = array_map( 'absint', (array) $_REQUEST['post'] );

		foreach( $post_ids as $post_id ) {
			update_post_meta($post_id, '_purchase_order_payment_status', $new_status);
			$changed++;
		}
		
		$sendback = add_query_arg( array( 'post_type' => 'shop_order', $report_action => $changed, 'ids' => join( ',', $post_ids ) ), '' );
		wp_redirect( $sendback );
		exit();
		
	}
	
	//showing the bulk actions notices
	function show_bulk_admin_notices(){
		global $post_type, $pagenow;

		if ( isset( $_REQUEST['payment_incompleted'] ) || isset( $_REQUEST['payment_completed'] ) ) {
			$number = isset( $_REQUEST['payment_completed'] ) ? absint( $_REQUEST['payment_completed'] ) : absint( $_REQUEST['payment_incompleted'] );
			
			$status = isset( $_REQUEST['payment_completed'] ) ? 'paid' : 'not paid';
			
			if ( 'edit.php' == $pagenow && 'shop_order' == $post_type ) {
				$message = sprintf( _n( 'Purchase Order status changed.', '%s order are marked as %s.', $number ), number_format_i18n( $number ), $status );
				echo '<div class="updated"><p>' . $message . '</p></div>';
			}
		}
	}
	
	
	function awaiting_payment_paid($order){
		update_post_meta($order->id, '_purchase_order_payment_status', '1');
		update_post_meta($order->id, '_purchase_order_payment_date', current_time('timestamp'));
	}
	
	
	//admin menu to show list tables
	function woocommerce_payment_status_admin_menu(){
		global $woocommerce_payment_status_page;
		$woocommerce_payment_stauts_page = add_submenu_page('woocommerce', __( 'Payment Status', 'payment-status' ), __( 'Payment Status', 'payment-status' ), 'manage_woocommerce', 'woocommerce-payment-status', array(&$this, 'woocommerce_payment_status' ));
		
		//var_dump($woocommerce_payment_stauts_page); exit;
		
		//screen options
		//add_action( "load-$woocommerce_payment_status_page", array(&$this, 'status_page_screen_options') );
		add_action( "load-$woocommerce_payment_stauts_page", array(&$this, 'status_page_screen_options') );
	}
	
	
	//payment status submenu page to show the orders
	function woocommerce_payment_status(){
		include $this->get_this_directory() . 'includes/payment-status.php';
	}
	
	//return the base directory with slash included
	function get_this_directory(){
		return dirname(__FILE__) . '/';
	}
	
	
	//list table function
	function get_list_table($status){
		if(!class_exists('WooPaymentStatusListTable')){
			include $this->get_this_directory() . 'class.list-table.php';
		}
		
		return new WooPaymentStatusListTable($status);
	}
	
	
	//status page screen optons
	function status_page_screen_options(){
		$option = 'per_page';

		$args = array(
			'label' => 'Orders',
			'default' => 20,
			'option' => 'woo_paid_unpaid_per_page'
		);
		
		add_screen_option( $option, $args );
	}
	
	
	//save the screen option
	function save_screen_options($status, $option, $value){
		
		if($option == 'woo_paid_unpaid_per_page'){
			return $value;
		}
	}
	
	
	function get_uri(){
		return plugins_url('/', __FILE__);
	}
	
	//include javascript
	function admin_enqueue_scripts(){
		if($_GET['page'] == 'woocommerce-payment-status'){
			wp_register_script('woocommerce-payment-status-js', $this->get_uri() . 'asset/datepicker/js/jquery-ui-1.10.3.custom.min.js', array('jquery'));
			wp_enqueue_script('woocommerce-payment-status-js');
			
			wp_register_style('woocommerce-payment-status-css', $this->get_uri() . 'asset/datepicker/css/ui-lightness/jquery-ui-1.10.3.custom.min.css');
			wp_enqueue_style('woocommerce-payment-status-css');
		}
	}
	
	
	//handle bulk action
	function admin_init_to_handle_bulk_action(){
		if($_REQUEST['woocommerce-paid-status-bulk-action'] == 'y' && $_REQUEST['page'] == 'woocommerce-payment-status'){
			$sendback = remove_query_arg( array('woocommerce-paid-status-bulk-action'), wp_get_referer() );
			
			
			if(!$sendback){
				$sendback = admin_url('admin.php?page=woocommerce-payment-status');
			}
					
			if(isset($_REQUEST['date-filter']) || isset($_REQUEST['date-filter'])){
				$sendback = add_query_arg(array('start-date' => urlencode($_REQUEST['start-date']), 'end-date' => urlencode($_REQUEST['end-date']), 'order_status' => urlencode($_REQUEST['order_status']), 'payment_type' => urlencode($_REQUEST['payment_type'])), $sendback);
			}
			
			//get the list table
			$wp_list_table = $this->get_list_table($_REQUEST['order_status']);
			$doaction = $wp_list_table->current_action();
			
			if($doaction == 'print'){				
				return $this->handle_print();
			}
			
			$sendback = remove_query_arg( array('action', 'action2', '_wp_http_referer', '_wpnonce'), $sendback );
			return $this->do_redirect($sendback);
		}
		
		if($_REQUEST['page'] == 'woocommerce-payment-status' && (isset($_REQUEST['start-date']) || isset($_REQUEST['end-date'])) && isset($_REQUEST['_wp_http_referer'])){
			$sendback = remove_query_arg( array('action', 'action2', '_wp_http_referer', '_wpnonce'), $this->get_current_url() );
			
			return $this->do_redirect($sendback);
		}		
				
	}
	
	
	function get_current_url(){
		
		 $pageURL = 'http';
		 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		 $pageURL .= "://";
		 if ($_SERVER["SERVER_PORT"] != "80") {
		  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		 } else {
		  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		 }
		 return $pageURL;
		
	}
	
	
	//do the print thing
	function handle_print(){
		$order_ids = $_REQUEST['order_id'];		
		var_dump($order_ids);
		exit;
		
	}


	//redirecting
	function do_redirect($url){
		if(!function_exists('wp_redirect')){
			include ABSPATH . '/wp-includes/pluggable.php';
		}
		wp_redirect($url);
		die();
	}

	function ready_to_ship_icon() {
       	echo "<style type=\"text/css\"> mark.ready-to-ship { background: #f00 !important; } </style>";
       	
       	//enqueue script for purchase payment metabox
       	wp_register_script('pops_partial_payment_js', $this->get_uri() . 'js/admin_pops_partial_payment.js', array('jquery'));
       	wp_enqueue_script('pops_partial_payment_js');
       	
    }
	
	
}

global $woo_purchase_status;
$woo_purchase_status = new WooPurchaseOrderPaymentStatus();
