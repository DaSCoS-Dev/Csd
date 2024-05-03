<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}
class Main_guest_views extends CI_View_assembler {

	public function __construct( $params = null ) {
		parent::__construct( $params );
		if ($params != null) {
			foreach ( $params as $var => $value ) {
				$this->$var = $value;
			}
		}
	}

	protected function main_guest_help( ) {
		// Simply
		return $this->load->view( "Help/guest_main_help", array (), true );
	}

}
?>