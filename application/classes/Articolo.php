<?php
/**
 * Classe Articolo
*/
class articolo  extends main_object {
	const model_name = "model_articoli";

	public function __construct( $super_lib = null ) {
		parent::__construct($super_lib);
		$this->super_lib->load->helper("date");		
	}

	public function get($id_articolo){
		$record = $this->model->get_record( $id_articolo )[0];
		$columns = array_column( $this->struct, 'name' );
		foreach ($record as $variabile => $valore){	
			$found_key = array_search($variabile, $columns);
			if (stripos($this->struct[$found_key]["definition"], "d") !== false){
				$this->$variabile = standard_date( "DATE_EURO_LONG", $valore );
			} else {
				$this->$variabile = $valore;
			}
		}
		return $this->articolo();
	}

	public function articolo(){
		// Unset unuseful vars
		$articolo = clone $this;
		unset($articolo->model);
		unset($articolo->struct);
		unset($articolo->super_lib);
		return $articolo;
	}

}
?>