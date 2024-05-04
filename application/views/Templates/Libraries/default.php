<?php
if (! defined( "BASEPATH" )) {
	exit( "No direct script access allowed" );
}
$class_code = <<<EOF
<?php
if (! defined( "BASEPATH" )) {
	exit( "No direct script access allowed" );
}

/**
 * 
 * @package libraries
 * @subpackage {$library_name_U}
 * @author Da.S.Co.S.
 * @link http://www.dascos.info
 */
class Main_{$library_name_L} extends Super_lib {

	public function __construct( ) {
		parent::__construct( );
		\$this->load_class("{$library_name_U}/{$library_name_U}");
		\$this->load->model("{$library_name_U}/model_{$library_name_L}");
		// Build dependencyes
		\$this->build_dependency();
		// Variables
		\$this->model = \$this->model_{$library_name_L};
		\$this->views = \$this->main_{$library_name_L}_views;
		\$this->now = time();
		\$this->isProd = \$this->is_live();
	}

	/**
	 * PROTECTED functions
	 */

	protected function index( \$type = "table", \$input = null ) {
		switch ( \$type ) {
			case "table" :
				\$this->listObjects( \$input );
				break;
			case "edit" :
			case "new" :
				\$this->editObject( \$input );
				break;
			case "save" :
				\$this->saveObject( \$input );
				break;
			default :
				\$this->listObjects( \$input );
				break;
		}
	}

	/**
	 * PRIVATE functions
	 */
	
	private function listObjects( \$input = nulll ) {
	\$head = "";
		\$table_headers = \$this->model->get_table_header();
		foreach (\$table_headers as \$header){
			\$head .= \$this->view->build( "table_header", \$header );
		}
		\$t_struct = new stdClass();
		\$t_struct->tds = \$head;
		\$t_struct->tds_number = sizeof(\$table_headers);
		\$full_table_structure = \$this->view->build( "table_structure", \$t_struct );
		\$structure = \$this->view->build( "general_structure", array("table_structure" => \$full_table_structure) );
		\$this->response->assign( "div_home_page", "innerHTML", \$structure );
		\$this->response->assign( "sectionTitle", "innerHTML", "{$library_name_U} List" );
		\$this->show_records_table();
		// Trasformo in datatable....
		\$this->response->script( "do_data_table('record_table', [[1, 'asc']], '{$library_name_L}', [{ orderable: false, targets: [1, 2] }], '', null )" );
	}
				
	private function editObject( \$uniqueId = 0 ) {
		if (intval( \$uniqueId ) > 0) {
			\$key_array = array (
					"PRIMARY" => array (
							"id" => intval(\$uniqueId) 
					) 
			);
			\$object = \$this->model->get_record( intval( \$uniqueId ), "record_by_primary" );
		} else {
			\$object = new {$library_name_U}();
		}
		\$structure = \$this->view->build( "edit_structure", \$object );
		\$this->response->assign( "edit_record_wrapper", "innerHTML", \$structure );
		if (intval( \$uniqueId ) > 0) {
			\$this->response->assign( "sectionTitle", "innerHTML", "Edit {$library_name_U}" );
		} else {
			\$this->response->assign( "sectionTitle", "innerHTML", "New {$library_name_U}" );
		}
		\$this->show_edit_records();
	}
					
	private function saveObject( \$object = null ) {
		\$this->parseRecord( $\object );
		if (\$object [ "id" ] === false) {
			\$idDb = \$this->model->save_record( \$object );
		} else {
			\$idDb = \$this->model->update_record( \$object );
		}
		if (\$idDb !== false) {
			\$this->index();
			\$this->message( "Successfully saved !!", "Ok" );
		} else {
			\$this->error( "Got errors during saving of {$library_name_U}...", "Ko" );
		}
	}
}
?>

EOF
;
print $class_code;
?>