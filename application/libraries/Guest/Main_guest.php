<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}
class Main_guest extends Super_lib {
	public $model, $view;

	public function __construct( $params = null ) {
		if ($params != null) {
			foreach ( $params as $var => $value ) {
				$this->$var = $value;
			}
		} else {
			parent::__construct( $params );
		}
		$this->load->library( "Views_assembler/Guest/Main_guest_views", $this );
		// Rebuild dependencies and assign a shortcut...
		$this->build_dependency();
		$this->view = $this->main_guest_views;
	}

	protected function index(){
		$html = $this->view->build( "main_guest_help" );
		$this->show_html( $html );
		$this->enable_popover();
	}

}
?>