<?php 
/**
 * This class handles partial payments
 * */

class WooPartialPayments{
	
	var $db_table;
	var $wpdb;
	
	function __construct(){
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->db_table = $wpdb->prefix . 'woocommerce_partial_payments';
	}
	
	/**
	 * creates a table to the database to store the partial payment info
	 * */
	function sync_db(){
		$sql = "create table if not exists $this->db_table(
			payment_id bigint not null primary key auto_increment,
			order_id bigint not null,
			date date not null,
			type varchar(200) not null,
			amount float not null
		)";
		
		return $this->wpdb->query($sql);
	}
	
	
	/**
	 * inserting the partial payment info
	 * @info array
	 * order_id identify the order id
	 * date selected date from admin panel
	 * type type of payments e.g bank transer, check, credit card
	 * amount partial amount to paid
	 * */
	function insert($info){
		//$type_casting = array('%d', '%s', '%s', '%f');
		$this->wpdb->insert($this->db_table, $info);
		return $this->wpdb->insert_id;
	}
	
	
	/**
	 * delete partial payment info 
	 * @$column column name
	 * @$value value of the associated column
	 * */
	function delete($column, $value){
		$sql = "delete from $this->db_table where $column like '$value'";
		return $this->wpdb->query($sql);
	}
	
	
	/**
	 * returns payments by different param
	 * @column column name of the parital payment table
	 * @value, value of the column
	 * */
	function get_payments_by($column, $value, $output_type = 'OBJECT'){
		$sql = "select * from $this->db_table where $column like '$value' order by date";
		return $this->wpdb->get_results($sql, $output_type);
	}
	
	
	/**
	 * payments by condition
	 * @where condition for query
	 * */
	function get_payments_by_condition($where){
		$sql = "select * from $this->db_table where {$where} order by date";
		return $this->wpdb->get_results($sql);
	}
	
}

?>