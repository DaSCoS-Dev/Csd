<?php
if (! defined( "BASEPATH" ))
	exit( "No direct script access allowed" );
/**
 * This is the main class that handles database interactions.
 * During the initialization phase of the "child" classes, the structure of the table and of the related indexes is recreated:
 * this data is then used dynamically to build the queries and related conditions.
 *
 * To query the database we use the functions get_, update_, insert_, delete_ (record), which are public methods accessible from "everywhere".
 * There are two basic methods, declared for each "child" class: _get_record_by_index and _get_record_by_primary.
 *
 * These two protected methods are used to construct a query based on primary and secondary indexes, in order to make it easier
 * to retrieve records.
 * They are declared in each "child" class to give the possibility to customize them specifically for each class and therefore,
 * even if they look like identical repetitions and could be declared as "parent" functions,
 * a small overload was preferred to having to rewrite them by hand in the in case it was necessary.
 *
 * @package Super_model
 * @author Da.S.Co.S.
 * @link http://www.dascos.info
 */
class Super_model extends CI_Model {
	protected $default_order_by, $table_name, $table_header, $table_info, $relationships, $primaryIndex, $hasphpMyAdminPma = null, $databaseTables;
	public $table_structure, $index_structure;
	private $use_phpmyadmin_features;

	/**
	 *
	 * @param array/objects $params        	
	 */
	public function __construct( $params = null ) {
		global $CFG;
		$this->benchmark();
		$framework_configured = $CFG->item( "framework_configured" );
		// Se arrivano dei parametri (array)...
		if ($params != null) {
			foreach ( $params as $var => $value ) {
				$this->$var = $value;
			}
		}
		$this->default_order_by = "";
		if (is_a( $this->super_lib, "Super_lib" )) {
			$class_name = strtolower( get_class( $this ) );
			$this->super_lib->$class_name = $this;
		}
		if (get_class( $this ) !== "Super_model" and $framework_configured) {
			$this->get_indexes();
			$this->get_structure( $this->table_name );
			$this->build_default_order_by();
			$this->relationships = $this->get_relationships();
			$this->primaryIndex = $this->get_table_indexes( "PRIMARY" ) [ 0 ];
			$this->findInfoByStructure( $this->table_name );
		} elseif ($framework_configured) {
			$this->databaseTables = $this->get_db_tables();
		}
		$this->build_dependency();
		// Set the global use_phpmyadmin_features....
		$this->use_phpmyadmin_features = $this->config->item( "use_phpmyadmin_features" );
	}

	private function benchmark($type = "enter"){
		$name = get_class( $this );
		$ci = get_instance();
		$ci->benchmark->mark( "Csd_{$name}_{$type}" );
	}
	
	/**
	 * PUBLIC functions, callable "outside" the models.
	 * Use only if absolutely necessary or if you don't (still?) have a specific Model
	 */
	public function get_table_indexes( $type = "all" ) {
		switch ( strtoupper( $type ) ) {
			case "ALL" :
				return $this->index_structure;
				break;
			case "PRI" :
			case "PRIMARY" :
				return $this->index_structure [ "PRIMARY" ];
				break;
			default :
				return $this->index_structure [ "INDEX" ] [ $type ];
				break;
		}
	}

	public function get_table_header( ) {
		return $this->table_header;
	}

	public function get_table_structure( ) {
		return $this->table_structure;
	}

	public function get_table_name( ) {
		return $this->table_name;
	}

	public function get_table_relationships( ) {
		return $this->relationships;
	}

	public function get_table_info( ) {
		return $this->table_info;
	}

	public function get_record( $identificativo, $tipo = "", $start = 0, $limit = 999999, $extra_params = null ) {
		if (is_null( $start )) {
			$start = 0;
		}
		if (is_null( $limit )) {
			$limit = 999999;
		}
		return $this->do_get_record( $identificativo, $tipo, $start, $limit, $extra_params );
	}

	public function save_record( $record, $tipo = "" ) {
		return $this->do_save_record( $record, $tipo );
	}

	public function update_record( $identificativo, $tipo = "" ) {
		return $this->do_update_record( $identificativo, $tipo );
	}

	public function delete_record( $record, $tipo = "" ) {
		return $this->do_delete_record( $record, $tipo );
	}

	public function symple_query( $sql ) {
		return $this->do_symple_query( $sql );
	}

	public function multi_query( $sql ) {
		return $this->do_multi_query( $sql );
	}

	public function get_unique_user_code( $codice = "" ) {
		return $this->do_get_unique_user_code( $codice );
	}

	/**
	 * Get the NON system tables
	 *
	 * @return array of rows (object)
	 */
	public function get_db_tables( ) {
		$sql = "
		SHOW TABLES WHERE `Tables_in_{$this->db->database}` NOT LIKE 'csd_%';
		";
		$query_resource = $this->db->query( $sql );
		return $query_resource->result( "object" );
	}

	public function get_relationships( ) {
		if ($this->hasphpMyAdminPma === null) {
			$this->checkRelationshipsTable();
		}
		if ($this->hasphpMyAdminPma) {
			$sql = "
				SELECT * FROM `phpmyadmin`.`pma__relation` WHERE 
					`master_db` = '{$this->db->database}' AND
					`master_table` = '{$this->table_name}'
				";
			$query_resource = $this->db->query( $sql );
			return $query_resource->result( "object" );
		} else {
			return $this->findRelationsByStructure();
		}
	}

	/**
	 * Each member is a stdClass, with
	 * db_name (usually this one)
	 * table_name (the "joined" one, corresponding to $hasRelations->foreign_table)
	 * display_field (the value of this field is the one we must use in the Select)
	 * 
	 * @param string $table_name        	
	 * @return array
	 */
	public function get_info( $table_name = "" ) {
		if ($this->hasphpMyAdminPma === null) {
			$this->checkRelationshipsTable();
		}
		if ($this->hasphpMyAdminPma and !is_array($this->table_structure [ "tableInfos" ])) {
			$sql = "
			SELECT * FROM `phpmyadmin`.`pma__table_info` WHERE
			`db_name` = '{$this->db->database}' AND
			`table_name` = '{$table_name}'
			";
			$query_resource = $this->db->query( $sql );
			$this->table_info = $query_resource->result( "object" );
			$this->table_structure [ "tableInfos" ] = $this->table_info;
		} elseif (!$this->hasphpMyAdminPma) {
			$this->table_info = $this->findInfoByStructure( $table_name );
		}
		return $this->table_info;
	}

	public function getPrimaryIndex( ) {
		return $this->primaryIndex;
	}

	/**
	 * Changes the default ordering for queryes.
	 * $array_order = array(
	 * column_name => ordering
	 * )
	 * where
	 * column_name = the column to use for sorting
	 * ordering = ASC|DESC
	 *
	 * @param array $array_order        	
	 */
	final public function change_order_by( $array_order = array() ) {
		if (sizeof( $array_order ) > 0) {
			foreach ( $array_order as $campo => $ordinamento ) {
				$order [ ] = "{$campo} {$ordinamento}";
			}
			$this->set_order_by( implode( ", ", $order ) );
		}
	}

	/**
	 * Change the default order for query.
	 *
	 * @param string $string        	
	 */
	final public function set_order_by( $string = "ID" ) {
		$this->default_order_by = $string;
	}

	/**
	 * $hasRelations = stdClass, with
	 * foreign_db (usually this one),
	 * foreign_table (the joined table)
	 * foreign_field (the field of the foreign table)
	 * master_db (this one)
	 * master_table ( $table_name)
	 * master_filed ( $column_name )
	 *
	 * @param string $targetValue        	
	 * @param array $tableRelations        	
	 * @return mixed|boolean
	 */
	public function checkRelations( $targetValue = "", $tableRelations = array() ) {
		$filteredArray = array_filter( $tableRelations, function ( $item ) use ($targetValue ) {
			return $item->master_field === $targetValue;
		} );
		if (! empty( $filteredArray )) {
			return reset( $filteredArray );
		} else {
			return false;
		}
	}

	public function getRelatedField( $hasRelations, $row ) {
		// we have a relationship between a field of this table and another filed in another table....
		/*$tableInfos = $this->get_info( $hasRelations->foreign_table );
		$joined_table_structure = "joined_table_structure_{$hasRelations->foreign_table}";
		// Do we have the info from the table $hasRelations->foreign_table structure?
		if ($tableInfos == null and sizeof( $this->$joined_table_structure [ "tableInfos" ] ) > 0) {
			// Ops, we got from the table
			$tableInfos = $this->$joined_table_structure [ "tableInfos" ];
		}
		$joinedTable = strtolower( $tableInfos [ 0 ]->table_name );
		$joinedTableModel = "model_{$joinedTable}";
		$masterField = $hasRelations->master_field;
		$displayField = $tableInfos [ 0 ]->display_field;*/		
		$joinedTable = strtolower($hasRelations->foreign_table);
		$joinedTableModel = "model_{$joinedTable}";
		// Do we have the correspondig model?
		if ($joinedTable !== null and $this->$joinedTableModel == null) {
			// No? Let's load it!
			$ucfirst = ucfirst( $joinedTable );
			$this->super_lib->load->model( "{$ucfirst}/{$joinedTableModel}" );
			$this->super_lib->build_dependency();
		}
		$displayField = $this->super_lib->$joinedTableModel->table_structure["tableInfos"][0]->display_field;
		$search = array (
				"PRIMARY" => array (
						"{$hasRelations->foreign_field}" => $row->{$hasRelations->master_field} 
				) 
		);
		$joinedRecords = $this->super_lib->$joinedTableModel->get_record( $search, "record_by_primary" ) [ 0 ];
		if (isset( $joinedRecords->$displayField )) {
			return $joinedRecords->$displayField;
		} else {
			return "n/a";
		}
	}

	public function getRelatedFieldName( $hasRelations ) {
		// we have a relationship between a field of this table and another filed in another table....
		$tableInfos = $this->get_info( $hasRelations->foreign_table );
		// Do we have the info from the table $hasRelations->foreign_table structure?
		if ($tableInfos == null and is_array( $this->table_structure [ "tableInfos" ] ) and sizeof( $this->table_structure [ "tableInfos" ] ) > 0) {
			// Ops, we got from the table
			$tableInfos = $this->table_structure [ "tableInfos" ];
		}
		if (! is_array( $tableInfos )) {
			return $hasRelations->master_field;
		}
		return "{$tableInfos[0]->display_field} from {$tableInfos[0]->table_name}";
	}

	/**
	 * PROTECTED functions
	 */
	/**
	 * Retrieve primary keys as defined in the table
	 *
	 * @param string $table_name        	
	 * @return boolean|array
	 */
	final protected function get_primary_key( $table_name = "" ) {
		return $this->do_get_primary_key( $table_name );
	}

	/**
	 * Retrieve secondary keys as defined in the table
	 *
	 * @param string $table_name        	
	 * @return boolean|array
	 */
	final protected function get_indexes( $table_name = "" ) {
		return $this->do_get_indexes( $table_name );
	}

	/**
	 * Return the table structure as defined in the database
	 *
	 * @param string $table_name        	
	 * @param string $instance_variable        	
	 * @return stdClass
	 */
	final protected function get_structure( $table_name = "", $instance_variable = "table_structure" ) {
		$table_struct = $this->do_get_table_structure( $table_name, $instance_variable );
		foreach ( $table_struct as $column => $structure ) {
			$this->table_header [ ] = array (
					"name" => $structure->Field,
					"definition" => $structure->Comment,
					"type" => $structure->Type 
			);
		}
	}

	/**
	 * Builds the default ordering for all queryes, based on tables primary keys
	 */
	final protected function build_default_order_by( ) {
		$ordering = array ();
		$this->get_primary_key();
		foreach ( $this->index_structure [ "PRIMARY" ] as $key => $value ) {
			$ordering [ $value->Column_name ] = "ASC";
		}
		$this->change_order_by( $ordering );
	}

	/**
	 *
	 * @param unknown $ref        	
	 */
	final protected function build_dependency( $ref = null ) {
		if (is_null( $ref )) {
			$this->ci = get_instance();
		} else {
			$this->ci = $ref;
		}
		foreach ( get_object_vars( $this->ci ) as $_ci_key => $_ci_var ) {
			if (! isset( $this->$_ci_key )) {
				$this->$_ci_key = $this->ci->$_ci_key;
			}
		}
	}

	/**
	 * Constructs the string to insert into the "where" condition of a record retrieval query
	 *
	 * @param unknown $index_values        	
	 * @return string
	 */
	final protected function build_index_query( $index_values ) {
		$where = array ();
		foreach ( $index_values as $c_name => $value ) {
			if (is_string( $value )) {
				$where [ ] = " `{$c_name}` = '{$value}' ";
			} elseif (is_array( $value ) and sizeof( $value ) >= 1) {
				// Ciclo sulle coppie "colonna" = "valore"
				foreach ( $value as $key_name => $search_value ) {
					if (is_string( $search_value )) {
						$where [ ] = " `{$key_name}` = '{$search_value}' ";
					} else {
						$where [ ] = " `{$key_name}` = {$search_value} ";
					}
				}
			} else {
				$where [ ] = " `{$c_name}` = {$value} ";
			}
		}
		$where_conditions = implode( "AND", $where );
		// Put parenthesis, to avoid other conditions that may alter the result in subsequent where clauses,
		// like " WHERE k1 = 2 and k2 = 'test' OR c1 = 'another' " -> " WHERE (k1 = 2 and k2 = 'test') OR c1 = 'another'
		$where_conditions = " ( {$where_conditions} ) ";
		return $where_conditions;
	}

	/**
	 * Builds the "where" structure to use in a record update/delete query
	 *
	 * @param array $index_values        	
	 * @return array
	 */
	final protected function build_index_where_query( $index_values ) {
		$where_conditions = array ();
		foreach ( $index_values as $c_name => $value ) {
			if (is_string( $value )) {
				$where_conditions [ "{$c_name}" ] = "'{$value}'";
			} else {
				$where_conditions [ "{$c_name}" ] = "{$value}";
			}
		}
		return $where_conditions;
	}

	/**
	 * Verify that the incoming "where" conditions are consistent with the declaration of the table's indexes
	 *
	 * @param array $table_def        	
	 * @param array $search_def        	
	 * @return boolean
	 */
	final protected function do_index_consistency_check( $table_def, $search_def ) {
		$index_search_definition = array_keys( $search_def ) [ 0 ];
		foreach ( $table_def as $idx => $index_definition ) {
			if ($index_definition->Key_name === $index_search_definition) {
				$control [ $index_definition->Column_name ] = true;
			}
		}
		$diff_keys = array_diff_key( $control, $search_def [ $index_search_definition ] );
		if (sizeof( $diff_keys ) or ( sizeof( $control ) !== sizeof( $search_def [ $index_search_definition ] ) )) {
			if (is_a( $this->super_lib, "Super_lib" )) {
				// Register the error
				$class_name = get_class( $this );
				$bck_trace = debug_backtrace();
				$caller = $bck_trace [ 1 ];
				$funct = $caller [ "function" ];
				$line = $bck_trace [ 0 ] [ "line" ];
				$table_name = $index_definition->Table;
				$this->super_lib->do_exec( "error", "An error occurred while running the \'index_consistency_check\' in function <strong>{$funct}</strong>, class <strong>{$class_name}</strong>, line <strong>{$line}</strong>! <br>You tried to construct a \'record_by_index\' query passing an array that is not consistent with the index structure of the related table <strong>{$table_name}</strong>.", "ERROR IN DEFINITION", 15000 );
			}
			return false;
		} else {
			return true;
		}
	}

	final protected function build_where_from_record( $record, $keys ) {
		$where = array ();
		foreach ( $keys as $key => $value ) {
			if (isset( $record [ $value->Column_name ] )) {
				$where [ $value->Column_name ] = $record [ $value->Column_name ];
			}
		}
		return $where;
	}

	/**
	 * Clean up the incoming record before inserting or updating it in the database table.
	 * It retrieves the structure of the table and, looping through the data of the form, eliminates everything that is not defined in the structure.
	 * For each field defined in the table, it constructs the possible default value if missing.
	 *
	 * @param array $record        	
	 * @param array $table_structure        	
	 * @return array
	 */
	final protected function clean_record( $record, $table_structure = null ) {
		if (is_null( $table_structure )) {
			$this->get_structure();
			$table_structure = $this->table_structure;
		}
		foreach ( $table_structure as $struttura ) {
			if (isset( $record [ $struttura->Field ] )) {
				// Se il valore è null o "null" E nel db può essere null
				if (( ( $record [ $struttura->Field ] === null or strtolower( trim( $record [ $struttura->Field ] ) ) === "null" ) and $struttura->Null == "YES" ) or 
				// oppure se il valore è stringa vuota E nel db può essere null E nel db è un campo di tipo numerico (smallint, int ecc)
				( trim( $record [ $struttura->Field ] ) === "" and $struttura->Null == "YES" and ( stripos( $struttura->Type, "int" ) !== false or stripos( $struttura->Type, "float" ) !== false ) )) {
					// Allora prendo il suo valore di default impostato nel Db
					$ok_to_save [ $struttura->Field ] = $struttura->Default;
				} else {
					// Altrimenti lo prendo così come mi arriva
					if (stripos( $struttura->Type, "char" ) !== false) {
						$lunghezza_campo = substr( $struttura->Type, stripos( $struttura->Type, "(" ) + 1, strlen( $struttura->Type, ")" ) - 1 );
						$record [ $struttura->Field ] = substr( trim( $record [ $struttura->Field ] ), 0, $lunghezza_campo );
					}
					$ok_to_save [ $struttura->Field ] = $record [ $struttura->Field ];
				}
			}
		}
		return $ok_to_save;
	}

	/**
	 * PRIVATE functions
	 */
	private function findInfoByStructure( $tableName ) {
		$this->do_get_table_structure( $tableName, "table_structure" );
		if (is_array( $this->table_structure ) and !is_array($this->table_structure [ "tableInfos" ])) {
			foreach ( $this->table_structure as $key => $fieldDefinition ) {
				// Search for the "s" in the Comment definition
				$def = explode( ",", $fieldDefinition->Comment );
				if (array_search( "s", $def ) !== false) {
					$relationDefinition = new stdClass();
					$relationDefinition->db_name = $this->db->database;
					$relationDefinition->table_name = $tableName;
					$relationDefinition->display_field = $fieldDefinition->Field;
					$this->table_structure [ "tableInfos" ] [ ] = $relationDefinition;
				}
			}
		}
		return $this->table_structure[ "tableInfos" ] ;
	}

	private function findRelationsByStructure( ) {
		$structure = array ();
		if (! is_array( $this->databaseTables )) {
			return $structure;
		}
		foreach ( $this->table_structure as $field ) {
			// Search if the Field is like "id_table"
			$masterField = $field->Field;
			if (strpos( $masterField, "_" ) !== false) {
				// Check if we have a table named "table" against the Db
				$fieldPieces = explode( "_", $masterField );
				// the foreign_field
				$foreignField = array_shift( $fieldPieces );
				// the foreign_table
				$foreignTable = implode( "_", $fieldPieces );
				$key = "Tables_in_{$this->db->database}";
				foreach ( array_values( $this->databaseTables ) as $table ) {
					if ($table->$key == $foreignTable) {
						$relation = array (
								"foreign_db" => $this->db->database,
								"foreign_field" => $foreignField,
								"foreign_table" => $foreignTable,
								"master_db" => $this->db->database,
								"master_field" => $masterField,
								"master_table" => $this->table_name 
						);
						$structure [ ] = ( object ) $relation;
					}
				}
			}
		}
		return $structure;
	}

	private function checkRelationshipsTable( ) {
		// Early check....
		if (! $this->use_phpmyadmin_features) {
			$this->hasphpMyAdminPma = false;
			return false;
		}
		// Do we have the phpmyadmin table?
		$sql = "SHOW DATABASES LIKE 'phpmyadmin'";
		$query_resource = $this->db->query( $sql );
		$result = $query_resource->result( "object" );
		// Check
		if (sizeof( $result ) > 0) {
			// Do we have the phpmyadmin table?
			$sql = "
					SELECT 
						count(*) as `Total`
					FROM 
						`information_schema`.`TABLES`
					WHERE 
						`TABLE_SCHEMA` = 'phpmyadmin' 
					AND 
						( `TABLE_NAME` = 'pma__relation' 
							OR
					`TABLE_NAME` = 'pma__table_info')";
			$query_resource = $this->db->query( $sql );
			$result = $query_resource->result( "object" );
			// Check...against 2 cause we want BOTH tables
			if ($result [ 0 ]->Total == 2) {
				$this->hasphpMyAdminPma = true;
				return true;
			}
		}
		$this->hasphpMyAdminPma = false;
		return false;
	}

	/**
	 *
	 * @param string $sql        	
	 * @return boolean|resource
	 */
	private function do_symple_query( $sql ) {
		return $this->db->query( $sql );
	}

	private function do_multi_query( $sql ) {
		$this->db->multi_query( $sql );
		do {
			if ($result = $this->db->conn_id->store_result()) {
				$result_set = $result->fetch_all( MYSQLI_ASSOC );
				$result->free();
			}
		} while ( $this->db->conn_id->next_result() );
		return $result_set;
	}

	/**
	 *
	 * @param array/stdclass $record        	
	 * @param string $specific_save        	
	 * @return record_id
	 */
	private function do_save_record( $record, $specific_save = "" ) {
		if (is_a( $record, "stdClass" )) {
			$record = ( array ) $record;
		}
		$func_name = "_save_{$specific_save}";
		$this->db->flush_cache();
		return $this->$func_name( $record );
	}

	/**
	 *
	 * @param array/stdclass $record        	
	 * @param string $specific_save        	
	 * @return record_id
	 */
	private function do_delete_record( $record, $specific_delete = "" ) {
		if (is_a( $record, "stdClass" )) {
			$record = ( array ) $record;
		}
		$func_name = "_delete_{$specific_delete}";
		$this->db->flush_cache();
		return $this->$func_name( $record );
	}

	/**
	 *
	 * @param unknow $id_to_search        	
	 * @param string $specific_type        	
	 * @param number $start        	
	 * @param number $limit        	
	 * @param array $extra_params        	
	 * @return boolean|record id|resource
	 */
	private function do_get_record( $id_to_search, $specific_type = "", $start = 0, $limit = 999999, $extra_params = null ) {
		$func_name = "_get_{$specific_type}";
		$this->db->flush_cache();
		return $this->$func_name( $id_to_search, $start, $limit, $extra_params );
	}

	/**
	 *
	 * @param unknown $id_to_update        	
	 * @param string $specific_type        	
	 * @return boolean|record id|resource
	 */
	private function do_update_record( $id_to_update, $specific_type = "" ) {
		$func_name = "_update_{$specific_type}";
		$this->db->flush_cache();
		return $this->$func_name( $id_to_update );
	}

	/**
	 * Returns the user identifier corresponding to that unique code
	 *
	 * @param string $codice        	
	 * @return id of csd_users table
	 */
	private function do_get_unique_user_code( $codice = "" ) {
		$sql = "
		SELECT
			`id`
		FROM
			`csd_users`
		WHERE
			`code` = '{$codice}'
		";
		$query_resource = $this->db->query( $sql );
		return $query_resource->result( "object" ) [ 0 ];
	}

	/**
	 * Finds and returns the primary key definition of the table $table_name
	 *
	 * @param string $table_name        	
	 * @return array of objects|boolean
	 */
	private function do_get_primary_key( $table_name ) {
		$struct = array ();
		if ($this->index_structure [ "PRIMARY" ] != null and is_array( $this->index_structure [ "PRIMARY" ] )) {
			return $this->index_structure [ "PRIMARY" ];
		}
		if (trim( $table_name ) == "") {
			$table_name = $this->table_name;
		}
		$struct = $this->do_get_indexes( $table_name, "PRIMARY" );
		if (sizeof( $struct ) === 0) {
			return false;
		} else {
			$this->index_structure [ "PRIMARY" ] = $struct;
			return $struct;
		}
	}

	/**
	 * Finds and returns the FULL key definition of the table $table_name (Primary and any other kind of keys)
	 *
	 * @param string $table_name        	
	 * @param string $index_name        	
	 * @return array of objects
	 */
	private function do_get_indexes( $table_name, $index_name = "INDEX" ) {
		if ($this->index_structure [ $index_name ] != null and is_array( $this->index_structure [ $index_name ] )) {
			return $this->index_structure [ $index_name ];
		}
		$where = $sql = "";
		if (trim( $table_name ) == "") {
			$table_name = $this->table_name;
		}
		if (trim( $index_name ) !== "INDEX") {
			$where = " WHERE `Key_name` = '{$index_name}' ";
		}
		$sql = "
			SHOW INDEX FROM `{$table_name}` {$where}
				";
		$query_resource = $this->db->query( $sql );
		foreach ( $query_resource->result( "object" ) as $index ) {
			if ($index->Key_name !== "PRIMARY") {
				$this->index_structure [ "INDEX" ] [ ] = $index;
			} else {
				$this->index_structure [ "PRIMARY" ] [ ] = $index;
			}
		}
		return $this->index_structure;
	}

	/**
	 * Retrieves the complete structure of a table and assigns it to the instance variable $this->instance_variable
	 *
	 * @param string $table        	
	 * @param string $instance_variable        	
	 * @return stdClass
	 */
	private function do_get_table_structure( $table, $instance_variable ) {
		if ($this->$instance_variable != null and is_array( $this->$instance_variable )) {
			return $this->$instance_variable;
		}
		$sql = "SHOW
		FULL COLUMNS
		FROM `{$table}`";
		$query_resource = $this->db->query( $sql );
		if ($query_resource->num_rows > 0) {
			$records = $query_resource->result( "object" );
		} else {
			$records = new stdClass();
		}
		$this->$instance_variable = $records;
		return $records;
	}
}
?>