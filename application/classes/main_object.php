<?php
/** 
 * Classe padre di ogni oggetto
 * Ha poche variabili e poche funzioni per restare snello
 */
class main_object{
	public $model, $struct, $super_lib;
	
	public function __construct($super_lib = null){
		if ($super_lib !== null){
			$this->model = $super_lib->{$this::model_name};
			$this->super_lib = $super_lib;
			if (is_null($this->model)){
				$name = ucfirst(array_pop(explode("_", $this::model_name)));
				$this->super_lib->load->model( "{$name}/" . $this::model_name );
				$this->super_lib->build_dependency();
				$this->model = $this->super_lib->{$this::model_name};				
			}
			$this->struct = $this->model->get_table_header();
			$this->super_lib->load->helper ( "date" );
		}
	}
}