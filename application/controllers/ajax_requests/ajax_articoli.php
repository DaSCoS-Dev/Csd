<?php
if (! defined( 'BASEPATH' )) {
	exit( 'No direct script access allowed' );
}
class ajax_articoli extends CI_Controller {
	protected $model;

	public function __construct( ) {
		parent::__construct();
		// Carico il model
		$this->load->model( "Articoli/model_articoli" );
		$this->load->helper("date");
		$this->corrispondenza_nomi_colonne = $this->model_articoli->get_table_header();
	}

	public function auto_search( ) {
		if (! is_ajax()) {
			$this->session->set_userdata( "ko_msg", "Chiamata non valida. Risulti non loggato o hai provato ad accedere in modo non corretto a una funzione del Sistema!" );
			redirect( "/admin/logout" );
		}
		$result = new stdClass();
		$termine = $this->input->post( "term" );
		$id_cliente = $this->input->post( "secondary_parameter" );
		$id_riga_grafica = $this->input->post( "aux" );
		if (strlen( trim( $termine ) ) < 3) {
			$limit = 10;
		} else {
			$limit = 999999;
		}
		// Recupero i records
		if (trim( $id_cliente ) !== "") {
			$termine_ricerca = array (
					"search" => $termine,
					"id_cliente" => intval( $id_cliente ) 
			);
		} else {
			$termine_ricerca = $termine;
		}
		// Se sono nei noleggi, cerco gli articoli con flag relativo...
		if (strtolower( $this->session->userdata [ "posizione" ] ) === "noleggio") {
			$records = $this->model_articoli->get_record( $termine_ricerca, "articoli_auto_suggest_noleggio", 0, $limit );
		} else {
			$records = $this->model_articoli->get_record( $termine_ricerca, "articoli_auto_suggest", 0, $limit );
		}
		if (sizeof( $records ) === 0 and trim( $termine_ricerca ) != "") {
			// No results, articolo nuovo
			$result->results [ ] = array (
					"id" => "a",
					"text" => "{$termine_ricerca}: Nuovo Articolo",
					"id_riga" => $id_riga_grafica 
			);
		} elseif (sizeof( $records ) === 0 and trim( $termine_ricerca ) == "") {
			// No results, nessun articolo in assoluto
			$result->results [ ] = array ();
		} else {
			$sono_in = $this->session->userdata [ "posizione" ];
			foreach ( $records as $record ) {
				// Se sto compilando una fattura e sto "vedendo" un servizio di immobile, skippo
				if ($sono_in == "fattura" and stripos( $record->Codice_Univoco, "@#piattaforme#@" ) !== false) {
					continue;
				}
				if ($record->Prezzo_Cliente !== null) {
					$record->Prezzo = $record->Prezzo_Cliente;
				}
				$result->results [ ] = array (
						"id" => "{$record->ID}",
						"text" => "{$record->Nome}, {$record->Codice_Univoco}: {$record->Tipo} -- € {$record->Prezzo}",
						"Codice_Univoco" => $record->Codice_Univoco,
						"id_riga" => $id_riga_grafica,
						"id_cliente" => $id_cliente 
				);
			}
		}
		$json_result = json_encode( $result );
		print $json_result;
	}

	public function get_articoli_tabella( ) {
		if (! is_ajax()) {
			$this->session->set_userdata( "ko_msg", "Chiamata non valida. Risulti non loggato o hai provato ad accedere in modo non corretto a una funzione del Sistema!" );
			redirect( "/admin/logout" );
		}
		$filtro = array ();
		$post_dati = $_POST;
		$sorting = $this->corrispondenza_nomi_colonne [ $post_dati [ "order" ] [ 0 ] [ "column" ] ] [ "name" ];
		$sorting_dir = $post_dati [ "order" ] [ 0 ] [ "dir" ];
		$search = $post_dati [ "search" ] [ "value" ];
		if (trim( $sorting ) != "") {
			$this->model_articoli->change_order_by( array (
					$sorting => $sorting_dir 
			) );
		}
		// Qui faccio eventuale filtro di ricerca, così mi torna bello
		if (trim( $search ) != "" and strlen( trim( $search ) ) > 3) {
			$filtro [ "ricerca" ] = trim( $search );
		}
		// Controllo sul filtro (variabile) E sul filtro (in post)
		$articoli = $this->model_articoli->get_record( $filtro, "", $post_dati [ "start" ], $post_dati [ "length" ] );
		// Elaboro il risultato per costruire la risposta alla data table
		$total_records = $this->model_articoli->get_record( $filtro, "count" )->Total;
		// Passo i dati elaborati all'assemblatore delle viste
		$result = new stdClass();
		$result->sEcho = $post_dati [ "draw" ];
		$result->iTotalRecords = $total_records;
		if (isset( $filtro_tipo [ "ricerca" ] )) {
			$result->iTotalDisplayRecords = sizeof( $articoli );
		} else {
			$result->iTotalDisplayRecords = $total_records;
		}
		$result->aaData = array ();
		// for ($i = 0; $i < 50; $i++){
		foreach ( $articoli as $idx => $riga ) {
			if (is_int( $idx / 2 )) {
				$style_row = "table.dataTable tr.odd";
			} else {
				$style_row = "table.dataTable tr.even";
			}
			$standard_def = array (
					"DT_RowAttr" => array (
							"style" => $style_row 
					),
					"DT_RowClass" => $class_row,
					"DT_RowId" => "row_{$riga->ID}" 
			);
			$row_def = $this->build_row( $riga );
			$result->aaData [ ] = array_merge( $standard_def, $row_def );
		}
		$coded = json_encode( $result );
		print $coded;
	}

	private function build_row( $row ) {
		$prim_keys = $this->model_articoli->get_table_indexes( "PRI" );
		foreach ( $this->corrispondenza_nomi_colonne as $key => $value ) {
			if (stripos($value["definition"], "h") !== false){
				
			} else {
				$row_value = $row->{$value["name"]};
				if (in_array( $value [ "name" ], array_column( $prim_keys, 'Column_name' ) )) {
					$style = "style=\"cursor: pointer\" onclick=\"xajax_execute('Articoli/Main_articoli', 'edit_articolo', {$row_value})\"";
					$row_id = $row_value;
				} else {
					$style = "";
				}
				if (stripos($value["definition"], "d") !== false){
					$row_value = standard_date( "DATE_EURO_LONG", $row_value );
				}
				$display_row [ $key ] = <<<EOF
			<div {$style}>{$row_value}</div>
EOF
;
			}
		}
		// Actions
		$display_row[] = <<<EOF
		<div>
			<span alt="Edit Record" title="Edit Record" onclick="xajax_execute('Articoli/Main_articoli', 'edit_record', {$row_id}); ">{$this->view_assembler->modifica_documento()}</span>
			<span alt="Delete Record" title="Delete Record" onclick="modal_confirm('Conferma', 'Vuoi realmente cancellare il Contatto?<br>L operazione non &egrave; annullabile!!', conferma_eliminazione_contatto, '{$row_id}', information, { 'message' : 'Eliminazione del Contatto annullata come richiesto', 'redirect' : null, 'close_delay' : 2500 })">{$this->view_assembler->cancella_documento()}</span>
		</div>
EOF
;
		return $display_row;
	}
}
?>