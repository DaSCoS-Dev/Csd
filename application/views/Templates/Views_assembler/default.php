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
 * @package view assembler
 * @subpackage {$library_name_U}
 * @author Da.S.Co.S.
 * @link http://www.dascos.info
 */
class Main_{$library_name_L}_views extends CI_View_assembler {

	public function __construct( \$params = null ) {
		parent::__construct( \$params );
	}

	protected function general_structure( \$table_structure ) {
		return \$this->load->view( "{$library_name_U}/general_structure", array (
			"table_structure" => \$table_structure
		), true );
	}
	
	protected function table_structure( \$td_specs) {
		return \$this->load->view( "{$library_name_U}/table_structure", array (
			"tds" => \$td_specs->tds, 
			"tds_number" => \$td_specs->tds_number
		), true );
	}
	
	protected function edit_structure( \$object ) {
		return \$this->load->view( "{$library_name_U}/edit_structure", array (
				"id" => \$object
		), true );
	}
	
	protected function groupFields(\$object){
		return \$this->buildEditView(\$object);
	}
}
?>
EOF
;
print $class_code;
?>