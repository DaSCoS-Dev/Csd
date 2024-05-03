<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}
class ajax_url_list extends CI_Controller {

	public function __construct( ) {
		parent::__construct();
		// Carico il model
		$this->load->model( "Shortener/model_shortener" );
	}

	public function auto_search( ) {
		if (! is_ajax()) {
			$this->session->set_userdata( "ko_msg", "Chiamata non valida. Risulti non loggato o hai provato ad accedere in modo non corretto a una funzione del Sistema!" );
			redirect( "/admin/logout" );
		}
		$result = new stdClass();
		$termine = $this->input->post( "term" );
		$records = $this->model_shortener->get_record( $termine, "autosearch_long_url" );
		if (sizeof( $records ) === 0) {
			$result->results [ ] = array (
					"id" => 0,
					"text" => "Nothing to show :-(",
					"skippa" => true 
			);
		} else {
			foreach ( $records as $url ) {
				$result->results [ ] = array (
						"id" => "{$url->ID}",
						"text" => "{$url->Originale}" 
				);
			}
		}
		$json_result = json_encode( $result );
		print $json_result;
	}
}
?>