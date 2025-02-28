<?php
if (! defined( "BASEPATH" )) {
	exit( "No direct script access allowed" );
}

/**
 * 
 * @package view assembler
 * @subpackage Exampletable
 * @author Da.S.Co.S.
 * @link http://www.dascos.info
 */
class Main_exampletable_views extends CI_View_assembler {

	public function __construct( $params = null ) {
		parent::__construct( $params );
	}

	protected function general_structure( $table_structure ) {
		return $this->load->view( "Exampletable/general_structure", array (
			"table_structure" => $table_structure
		), true );
	}
	
	protected function table_structure( $td_specs) {
		return $this->load->view( "Exampletable/table_structure", array (
			"tds" => $td_specs->tds, 
			"tds_number" => $td_specs->tds_number
		), true );
	}
	
	protected function edit_structure( $object ) {
		return $this->load->view( "Exampletable/edit_structure", array (
				"id" => $object
		), true );
	}
	
	protected function groupFields($object){
		return $this->buildEditView($object);
	}
}
?>