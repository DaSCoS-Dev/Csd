<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}
	class Main_articoli_views extends CI_View_assembler {

		public function __construct( $params = null ) {
			parent::__construct( $params );
		}

		/**
		 * ok
		 * @param array $headers
		 * @return unknown
		 */
		protected function list_record($headers = array() ) {
			$table_headers = "";
			foreach ($headers as $key => $value) {
				if (stripos($value["definition"], "h") === false){
					$table_headers .= $this->table_header($value["name"]);
				}
			}
			$table_headers .= $this->table_header("Actions");
			return $this->ci->load->view( "Articoli/struttura", array("table_headers" => $table_headers), true );
		}

		protected function form_articolo( $articolo ) {
			// Il Form Articolo e' impossibile da cosrtuire dinamicamente...
			$campo_drop_down_tipo_articolo = $this->form_field_drop_down( "Articoli_ID_Tipo_Record", $articolo->Elenco_Tipi_Articolo, $articolo->Record->ID_Tipo_Record );
			$campo_drop_down_um_articolo = $this->form_field_drop_down( "Articoli_ID_Unita_Misura", $articolo->Elenco_Unita_Misura, $articolo->Record->ID_Unita_Misura );
			if (intval($articolo->Record->Noleggio) == 1) {
				$checked = "checked";
			} else {
				$checked = "";
			}
			$articolo->Record->Check_Box_Noleggio= $this->articolo_check_box_field( array (
					"id" => "Noleggio",
					"checked" => $checked,
					"valore" => $articolo->Record->Noleggio,
					"mandatory" => ""
			) );
			return $this->ci->load->view( "Articoli/form_articolo", array (
					"articolo" => $articolo->Record,
					"tipo_articolo" => $campo_drop_down_tipo_articolo,
					"um_articolo" => $campo_drop_down_um_articolo
			), true );
		}

		protected function tabs_edit_articolo( $tipologia_articolo = "" ) {
			return $this->ci->load->view( "Articoli/tabs_edit", array (
					"tipologia" => $tipologia_articolo
			), true );
		}

		protected function articoli_form_pieces( $pezzi ) {
			return $pezzi ["html"];
		}

		protected function new_record_button( $id_articolo ) {
			return $this->action_button( "New_Record", "New Record", $id_articolo, " xajax_execute('Articoli/Main_articoli', 'edit_record', {$id_articolo}) ", "New Record");
		}

		protected function articolo_close_button( $array_id_articolo_testo = array() ) {
			if ($array_id_articolo_testo ["fake"]) {
				return $this->form_button_normale( "close_articolo_{$array_id_articolo_testo["id_riga"]}", "Annulla Registrazione Articolo", "Annulla Nuovo Articolo", "onclick=\"setTimeout(function() {
				$('#contenitore_corpo_fattura').show('blind');
				$('#bottoni_dati').show('blind');
				$('#dati_articolo').hide('blind');
				$('#close_articolo_{$array_id_articolo_testo["id_riga"]}').hide('blind');
				$('#save_articolo_{$array_id_articolo_testo["id_riga"]}').hide('blind');
				$('#add_riga_corpo').show('blind');
				$('#tabs_fattura').tabs( 'enable');
				redraw_data_table('tabella_articoli');
			}, 500);\"" );
			} else {
				return $this->form_button_normale( "close_articolo_{$array_id_articolo_testo["id_riga"]}", "Annulla Registrazione Articolo", "Annulla", "onclick=\"setTimeout(function() {
				$('#dati_articolo').hide('blind', function() { $('#tabella_articoli_wrapper').show('blind'); redraw_data_table('tabella_articoli') } );
			}, 50);
			$('#bottoni_dati').hide();
			$('#bottone_nuova').show();
			$('#current_system_position').html('Lista {$array_id_articolo_testo["testo"]}');\"
			" );
			}
		}

	}
	?>