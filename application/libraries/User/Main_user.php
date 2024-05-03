<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}
class Main_user extends Super_lib {
	public $model, $view;

	public function __construct( $params = null ) {
		if ($params != null) {
			foreach ( $params as $var => $value ) {
				$this->$var = $value;
			}
		} else {
			parent::__construct( $params );
		}
	}

	protected function index(){
		
	}
	
}
?>