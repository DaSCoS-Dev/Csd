<?php
if (! defined( 'BASEPATH' )){
	exit( 'No direct script access allowed' );
}

	class Main_articoli extends Super_lib {
		protected $model, $views;
		
		public function __construct( $params = null ) {
			parent::__construct( $params );
			$this->load->model_options( "Articoli/model_articoli", $params );
			// Costruisco le dipendenze
			$this->build_dependency();
			// Assegno le variabili
			$this->model = $this->model_articoli;
			$this->views = $this->main_articoli_views;
			$this->articolo = $this->load_class( "Articolo" );
		}

		protected function list_record( ) {
			if ($this->in_same_function()) {
				$this->show_records_table( );
			} else {
				$table_headers = $this->model->get_table_header();
				// Assemblo la tabella
				$result_html = $this->views->build( "list_record", $table_headers);
				// Assegno la struttura della tabella
				$this->show_html($result_html);
				$table_options = $this->build_data_table_options($table_headers);
				if (sizeof($table_options)){
					$options = " {$table_options["type"]}, {$table_options["ralign"]}, ";
				}
				// Renderizzo la tabella
				$this->response->script( "do_data_table('show_records', [[ 0, 'asc' ]], 'articoli', [{$options} { 'aTargets': [ 0 ], 'bSortable': false, 'bSearchable': false  } ], null, 0 );" );
				// Costruisco il bottone "Aggiungi articolo"
				$bottone_salva = $this->views->build( "new_record_button", 0 );
				// Lo assegno
				$this->response->assign( "new_record", "innerHTML", $bottone_salva );
			}
		}

		protected function save_record( $form_values, $forza_reload = true ) {
			// Eseguo minime sostituzioni
			// ... eseguo il check dei campi obbligatori.
			$result_check = $this->check_form( $form_values );
			// Ho errori?
			if (sizeof( $result_check ) > 0) {
				$errori = "<br><ul>";
				$errori .= implode( "<li>", $result_check );
				$errori .= "</ul>";
				$this->response->script( "alert('{$errori}');" );
				// Ciclo sugli errori per evidneziare i campi
				foreach ( $result_check as $campo => $errore ) {
					$this->response->script( " $('#{$campo}').addClass('ui-state-error') " );
				}
				return false;
			}
			$new_id = $this->model_articoli->save_record( $form_values, "articolo" );
			// Restituisco un bel "ok Johnny, tutto apposto"
			$this->response->script( "popup('Salvataggio concluso', 'Salvataggio effettuato con successo.', 0, 2000)" );
			// Ricarico la lista in modo "trasparente" SE e' un nuovo contatto
			if (! isset( $form_values [ "ID" ] ) or intval( $form_values [ "ID" ] ) == 0) {
				$this->response->script( "filtra_articoli()" );
				if ($forza_reload) {
					// $this->response->script( "xajax_execute('Articoli/Main_articoli', 'edit_articolo', {$new_id})" );
				}
				$new_id = 0;
			}
			// clicco il bottoncino "annulla salvataggio" SPECIFICO per le fatture. Se non esiste non succede nulla :-)
			$this->response->script( "$('#close_articolo_{$new_id}').click()" );
		}

		protected function edit_record( $id_articolo, $fake = false ) {
			if (strtolower($this->am_in()) !== strtolower(get_class($this))) {
				// return $this->redirect_posizione( "Articoli/Main_articoli", "lista_articoli", "edit_articolo", $id_articolo );
				$this->list_record();
			}
			$pieces [ "articoli_form_pieces" ] = $this->get_articolo_form_pieces( $id_articolo );
			// che mi ritorna un array con (array)html, (array)campi_data
			$articolo = $pieces [ "articoli_form_pieces" ] [ "articolo" ]->Record;
			$listini = $pieces [ "articoli_form_pieces" ] [ "articolo" ]->Listini;
			if (intval( $id_articolo ) > 0 and intval( $articolo->ID_Tipo_Record ) != 436) {
				$pieces [ "statistiche_form_pieces" ] = $this->build_statistiche_articolo( $id_articolo );
			} elseif (intval( $id_articolo ) > 0 and intval( $articolo->ID_Tipo_Record ) == 436) {
				$pieces [ "statistiche_form_pieces" ] = $this->build_dettagli_prenotazioni( $id_articolo );
			}
			// Assegno alla tabella le righe base del Cliente.
			$form = $this->assembla_form_edit_articolo( $id_articolo, $pieces );
			$this->response->assign( "dati_articolo", "innerHTML", $form );
			// Creo il bottone per salvare
			$valori_bottone = array (
					"id_riga" => $id_articolo,
					"id_form" => "tabs_articolo",
					"fake" => $fake,
					"testo" => "articoli"
			);
			$bottone_salva = $this->views->build( "articolo_save_button", $valori_bottone );
			$bottone_salva .= $this->views->build( "articolo_close_button", $valori_bottone );
			// Lo assegno
			if ($fake) {
				$nome_div_bottoni = "bottoni_dati_cliente_fake";
			} else {
				$nome_div_bottoni = "bottoni_dati";
			}
			$bottone_salva .= $this->testo_campi_obbligatori();
			$this->response->assign( "{$nome_div_bottoni}", "innerHTML", $bottone_salva );
			$this->response->script( "$('#{$nome_div_bottoni}').show();" );
			$this->response->script( "$('#bottone_nuova').hide();" );
			$this->response->assign( "close_articolo_{$id_articolo}", "style", "background: lightcoral" );
			$this->response->assign( "save_articolo_{$id_articolo}", "style", "background: green; color: #eeeeee !important" );
			// Se è una fake, cioè un articolo NUOVO direttamente dalla fattura....
			if ($fake) {
				$this->response->script( "$('#contenitore_corpo_fattura').hide('blind');" );
				$this->response->script( "$('#tabs_fattura').tabs( 'disable');" );
				// Nascondo anche i bottoni della fattura....
				$this->response->script( "$('#bottoni_dati').hide('blind');" );
				$this->response->script( "$('#add_riga_corpo').hide('blind');" );
			}
			// Dropdownizzo il tipo articolo
			$this->response->script( "render_special_select('#tabs_articolo_form_pieces-1 select', 'form-select', false);" );
			// Bind dell'onchange sul nome per costruire il codice univoco (via ajax!)
			$this->response->script( "bind_ontyping('input_container_Nome', verifica_codice_articolo);" );
			$this->response->script( "bind_ontyping('input_container_Codice_Univoco', verifica_codice_articolo);" );
			$this->response->script( "bind_ontyping('input_container_Codice_Barre', verifica_codice_barre_articolo);" );
			// Bind onchange per Prezzo Acquisto, Prezzo e Ricarico
			$this->response->script( "bind_onchange('input_container_Prezzo', calcola_prezzo_ricarico, null, 150);" );
			$this->response->script( "bind_onchange('input_container_Prezzo_Acquisto', calcola_prezzo_ricarico, null, 150);" );
			$this->response->script( "bind_onchange('input_container_Ricarico', calcola_prezzo_ricarico, null, 150);" );
			$this->response->script( "bind_onchange('input_container_Prezzo_Vendita_Ivato', calcola_prezzo_ricarico, null, 150);" );
			// Bind onchange sul tipo articolo
			$this->response->script( "bind_onchange('input_container_ID_Tipo_Record', cambia_tipo_articolo, null, 150);" );
			// Disabilito il Prezzo, perchè lo calcolo in base a Prezzo_Acquisto e Ricarico?
			// Setto il formato numerico
			$this->response->script( "$('#Prezzo').numeric({allowThouSep:false});" );
			$this->response->script( "$('#Prezzo_Acquisto').numeric({allowThouSep:false});" );
			$this->response->script( "$('#Prezzo_Vendita_Ivato').numeric({allowThouSep:false});" );
			// Cosa sto vedendo?
			if (intval( $id_articolo ) !== 0) {
				$this->response->assign( "current_system_position", "innerHTML", "Modifica Articolo &apos;{$articolo->Nome}&apos;" );
				if (intval( $articolo->ID_Tipo_Record ) != 436) {
					$this->response->script( "setTimeout( function() { do_data_table_symple('tabella_articolo_statistiche', [[ 0, 'desc' ]], [ { 'sType': 'date', 'aTargets': [ 0 ] }, { 'sType': 'numeric', aTargets: [ 1, 2, 3, 4 ] }, { 'sClass': 'dt-body-right', 'aTargets': [ 1, 2, 3, 4 ] } ], null ) }, 150);" );
				}
			} else {
				$this->response->assign( "current_system_position", "innerHTML", "Nuovo Articolo" );
			}
			if (intval( $articolo->ID_Aliquota_Iva ) == 0) {
				$this->response->script( " $('#drop_down_ID_Aliquota_Iva').val({$this->get_iva_default()}).change(); " );
			}
			// Prendo l'indirizzo e mostro i campi
			$indirizzo = $this->recupera_indirizzo_immobile( $id_articolo );
			// Popolo la grafica per l'indirizzo
			$riga_indirizzo = $this->crea_grafica_indirizzo( $indirizzo, $id_articolo );
			// Recupero il Proprietario SE è immobile (e ovviamente id > 0)
			if (intval( $articolo->ID_Tipo_Record ) == 436 and intval( $id_articolo ) > 0) {
				$proprietario = $this->recupera_proprietario_immobile( $id_articolo );
			} else {
				$proprietario = $this->recupera_proprietario_immobile( $id_articolo );
			}
			// Creo la drop_down per il Proprietario
			$riga_proprietario = $this->crea_grafica_proprietario( $proprietario );
			// Recupero il calendario
			$calendari = $this->recupera_calendario_immobile( $id_articolo );
			// Popolo la grafica del calendario
			$riga_calendari = $this->crea_grafica_calendari( $calendari );
			// Creo gli eventuali listini personalizzati
			$righe_listini = $this->crea_grafica_listini( $listini );
			// Cerco i servizi abbinati, se è di tipo Immobile
			if (intval( $articolo->ID_Tipo_Record ) == 436) {
				// Prima conto...
				$conteggio = $this->model_articoli->get_record( $proprietario->ID, "conta_servizi_affitti_utente" );
				if ($conteggio->Totale == 0) {
					$record_servizi = $this->model_articoli->get_record( array (
							"id_cliente" => $proprietario->ID
					), "servizi_affitti" );
				} else {
					$record_servizi = $this->model_articoli->get_record( array (
							"id_cliente" => $proprietario->ID,
							"id_utente" => $this->id_utente,
							"tipo_join" => "INNER"
					), "servizi_affitti" );
				}
				$servizi = $this->build_servizi_immobili( $record_servizi );
			}
			// Mostro la grafica
			$id_univoco_riga = $indirizzo->main_record [ "id_indirizzi" ];
			$this->response->assign( "input_container_proprietario", "innerHTML", $riga_indirizzo );
			$this->response->assign( "magic_moment", "innerHTML", $riga_proprietario );
			$this->response->script( "$('#contenitore_drop_down_Indirizzi_ID_Tipo_Record_{$id_univoco_riga}').replaceWith( $('#magic_moment').html() )" );
			$this->response->assign( "magic_moment", "innerHTML", "<input id=\"ID_Relazione\" value=\"{$proprietario->ID_Relazione}\" type=\"hidden\" >" );
			$this->response->script( "$('#container_tipo_indirizzo_{$id_univoco_riga}').append( $('#magic_moment').html() )" );
			// $this->response->replace( "contenitore_drop_down_Indirizzi_ID_Tipo_Record_{$id_univoco_riga}", "innerHTML", "", $riga_proprietario );
			$this->response->assign( "label_Indirizzi_ID_Tipo_Record_{$id_univoco_riga}", "innerHTML", "Proprietario" );
			$this->popola_indirizzo( ( object ) $indirizzo->main_record );
			$this->popola_proprietario( $proprietario );
			$this->popola_calendari( $riga_calendari );
			$this->popola_listini( $righe_listini, $listini );
			$this->popola_servizi( $servizi, $record_servizi );
			$this->change_tipo_articolo( $articolo->ID_Tipo_Record, $id_articolo );
			$this->evidenzia_campi( $id_articolo );
			// Rendo "Tabs" ... i tabs
			$this->response->script( "$('#tabs_articolo').tabs({heightStyle: 'content',active: 0});" );
			// Scroll
			$this->show_edit_records( );
			// $this->response->script("setTimeout( function() { redraw_data_table('tabella_articolo_statistiche') }, 450 )");
		}

		protected function delete_articolo( $id_articolo ) {
			if ($this->session->userdata [ "posizione" ] !== "articolo") {
				return $this->redirect_posizione( "Articoli/Main_articoli", "list_record", "edit_articolo", $id_articolo );
			}
			$records_uso = $this->model_articoli->get_record( $id_articolo, "articolo_in_fatture" );
			if (sizeof( $records_uso ) > 0) {
				// Articolo usato in fatture, NON posso cancellarlo
				return $this->response->script( "alert('Impossibile cancellare l&apos;Articolo selezionato, sono state emesse o ricevute Fatture<br>Cancellare l&apos;Articolo porta a inconsistenze nel database (oltre a possibili problemi fiscali!)')" );
			} else {
				// Ok, mai usato, quindi posso cancellare
				$res = $this->model_articoli->delete_record( $id_articolo, "articolo" );
				if (! $res) {
					return $this->response->script( "alert('Impossibile cancellare l&apos;Articolo selezionato, si &egrave; verificato un problema con i dati principali.<br>Consigliamo di aprire un Ticket')" );
				}
			}
			$this->response->script( "popup('Cancellazione eseguita', 'Articolo cancellato con successo!')" );
			// Trucchetto
			$prec = $this->session->userdata [ "lib_precedente" ];
			$this->session->userdata [ "lib_precedente" ] = "cicciopasticcio";
			$this->list_record();
			$this->session->userdata [ "lib_precedente" ] = $prec;
		}

		private function build_data_table_options($headers){
			$table_options = $options = array();
			// $this->response->script( "do_data_table('show_records', [[ 2, 'asc' ]], 'articoli', [{ sType: 'numeric', aTargets: [ 6 ] }, { 'sClass': 'dt-body-right', 'aTargets': [ 6, 7, 8 ] }, { 'aTargets': [ 0, 9 ], 'bSortable': false, 'bSearchable': false  } ], null, 0 );" );
			foreach ($headers as $key => $value) {
				if (stripos($value["definition"], "n") !== false){
					$table_options["types"][] = $key;
					$table_options["ralign"][] = $key;
				}
			}
			if (sizeof($table_options["types"])){
				$targets = implode(",", $table_options["types"]);
				$options["type"] = "{ sType: 'numeric', aTargets: [ {$targets} ] }";
				$options["ralign"] = "{ 'sClass': 'dt-body-right', 'aTargets': [ {$targets} ] }";
			}
			return $options;
		}
		
		private function get_articolo_form_pieces( $id_articolo = 0 ) {
			// Recupero il record Articolo
			$articolo = $this->articolo->get( $id_articolo );
			// Costruisco la grafica (recupero i pezzi formattati)
			$html_pieces = $this->views->build( "form_articolo", $articolo );
			return array (
					"html" => $html_pieces,
					"articolo" => $articolo
			);
		}

		private function assembla_form_edit_articolo( $id_articolo = 0, $pieces ) {
			// Creo i tab
			$result = $this->views->build( "tabs_edit_articolo" );
			// Mi costruisco le variabili
			foreach ( $pieces as $sezione => $pezzi_html ) {
				// Costruisco la sezione specifica
				$html = $this->views->build( $sezione, $pezzi_html );
				// E sostituisco nel tab il codice appena creato
				$result = str_replace( "<!--{$sezione}-->", $html, $result );
				// Faccio un replace della sezione ...
				$sezione = str_ireplace( "form_pieces", "add_button", $sezione );
				// cosi' posso costruire il bottone "add"
				$add = $this->views->build( $sezione, $id_articolo );
				// E sostituisco nel tab il codice appena creato
				$result = str_replace( "<!--{$sezione}-->", $add, $result );
			}
			// Assemblato il tutto, ritorno il codice
			return $result;
		}

		private function check_form( $form, $campi_calendario = array() ) {
			$form_errors = array ();
			if (trim( $form [ "Nome" ] ) === "") {
				$form_errors [ "Nome" ] = "Il campo Nome &egrave; obbligatorio";
			}
			if (trim( $form [ "Codice_Univoco" ] ) === "") {
				$form_errors [ "Codice_Univoco" ] = "Il campo Codice Univoco &egrave; obbligatorio";
			}
			if (intval( $form [ "ID_Tipo_Record" ] ) === 0) {
				$form_errors [ "ID_Tipo_Record" ] = "Il campo Tipo &egrave; obbligatorio";
			}
			if (floatval( $form [ "Prezzo" ] ) <= 0 and intval( $form [ "ID_Tipo_Record" ] ) != 436) {
				$form_errors [ "Prezzo" ] = "Il campo Prezzo &egrave; obbligatorio e deve essere &rt; 0";
			}
			if (sizeof( $form [ "Listino" ] )) {
				foreach ( $form [ "Listino" ] as $id_listino => $valori ) {
					if (floatval( $valori [ "Prezzo" ] ) <= 0 or intval( $valori [ "ID_Cliente" ] ) <= 0) {
						$form_errors [ "riga_prezzo_listino_{$id_listino}" ] = "Per poter salvare e utilizzare i Prezzi Personalizzati, devi scegliere un Cliente e impostare un Prezzo!";
					}
				}
			}
			if (sizeof( $campi_calendario )) {
				foreach ( $campi_calendario as $key => $calendario ) {
					if (trim( $calendario [ "Piattaforma" ] ) == "" and trim( $calendario [ "Indirizzo" ] ) != "") {
						$form_errors [ "Ical_Piattaforma_{$key}" ] = "Il campo Piattaforma &egrave; obbligatorio";
					}
					if (trim( $calendario [ "Piattaforma" ] ) != "" and trim( $calendario [ "Indirizzo" ] ) == "") {
						$form_errors [ "Ical_Indirizzo_{$key}" ] = "Il campo Indirizzo &egrave; obbligatorio";
					}
				}
			}
			return $form_errors;
		}
	}
	?>