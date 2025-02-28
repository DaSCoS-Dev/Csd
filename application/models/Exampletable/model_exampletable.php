<?php
if (! defined( "BASEPATH" )) {
	exit( "No direct script access allowed" );
}

/**
 * 
 * @package models
 * @subpackage Exampletable
 * @author Da.S.Co.S.
 * @link http://www.dascos.info
 */
class Model_exampletable extends Super_model {

	public function __construct( $params = null ) {
		$this->table_name = "";
		$this->loadClass();
		parent::__construct( $params );
	}

	/**
	 * PROTECTED functions
	 */
	
	 protected function _get_exampletable( $iden = null, $start = 0, $limit = 999999, $count = false ) {
		$limits = "";
		if ($count) {
			$head_query = $this->head_query_count_exampletable();
		} else {
			$head_query = $this->head_query_exampletable();
			$limits = " LIMIT {$start}, {$limit} ";
		}
		$sql = "
		{$head_query}
		{$limits}
		";
		$query_resource = $this->db->query( $sql );
		return $query_resource->result( "Exampletable" );
	}
	 
	protected function _get_record_by_primary( $index_values = array() ) {
		$primary_index_names = $this->get_primary_key();
		$check = $this->do_index_consistency_check($primary_index_names, $index_values);
		if ($check === false){
			return false;
		}
		$where = $this->build_index_query($index_values);
		$sql = "
		SELECT
			*
		FROM
			`{$this->table_name}`
		WHERE
			{$where}
		ORDER BY
			{$this->default_order_by}
		";
		$query_resource = $this->db->query( $sql );
		return $query_resource->result( "Exampletable" );
	}

	protected function _get_record_by_index( $index_values = array() ) {
		$index_names = $this->get_indexes();
		$check = $this->do_index_consistency_check($index_names, $index_values);
		if ($check === false){
			return false;
		}
		$where = $this->build_index_query($index_values);
		$sql = "
		SELECT
			*
		FROM
			`{$this->table_name}`
		WHERE
			{$where}
		ORDER BY
			{$this->default_order_by}
		";
		$query_resource = $this->db->query( $sql );
		return $query_resource->result( "Exampletable" );
	}
	
	protected function _save_( $record ) {
		$this->clean_record($record);
		$this->db->insert( $this->table_name, $record );
		return $this->db->insert_id();
	}
	
	protected function _update_( $record ){
		$this->clean_record($record);
		$primary_index_names = $this->get_primary_key();
		$orderQuery = $this->build_where_from_record($record, $this->index_structure["PRIMARY"]);
		// Check the transaction ID to see if the order needs an insert or update
		$existingRecord = $this->db->get_where( $this->table_name, $orderQuery, 1, 0 );
		// If it exists, get the ID and do an update
		if ($existingRecord->num_rows > 0) {
			$result = $this->db->update( $this->table_name, $record, $orderQuery );
			if ($result) {
				return $record [$this->index_structure["PRIMARY"][0]->Column_name];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	protected function _delete_( $record ) {
		$this->clean_record($record);
		$primary_index_names = $this->get_primary_key();
		$orderQuery = $this->build_where_from_record($record, $this->index_structure["PRIMARY"]);
		$result = $this->db->delete( $this->table_name, $orderQuery );
		if ($result) {
			return true;
		} else {
			return false;
		}
	}
	
	private function loadClass(){
		$CI = & get_instance();
		$this->databaseTables = $CI->Super_model->databaseTables;
		$CI->super_lib->load_class("Exampletable/exampletable");
	}
				
	private function head_query_count_exampletable( ) {
		$primary_index_names = $this->get_primary_key();
		$primary_column = $primary_index_names[0]->Column_name;
		return "
		SELECT
			COUNT(`tbl`.`{$primary_column}`) AS `exampletable_total`
		FROM
			`{$this->table_name}` as `tbl` ";
	}

	private function head_query_exampletable( ) {
		return "
		SELECT
			`tbl`.*
		FROM
			`{$this->table_name}` as `tbl` ";
	}
	
}
?>