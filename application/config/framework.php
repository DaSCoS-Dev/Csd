<?php
if ( ! defined("BASEPATH")) {
	exit("No direct script access allowed");
}
/**
 * SPERIMENTALE!!!!
 * Serve per definire quali "moduli", cioè in pratica funzionalità/voci del menù, sono ABILITATE
 * IN PRODUZIONE. In questo modo posso sviluppare in test senza problemi e ogni modifica posso
 * portarla in produzione anche se incompleta.
 * Esempio: sto sviluppando la Prima Nota ma ho solo completato l'inserimento, che però serve 
 * anche in altre parti "nascoste": posso portare tutto in produzione perchè mi serve la parte "nascosta" 
 * ma non voglio ancora far vedere che esiste la sezione Prima Nota.
 * 
 * Array di array, del tipo
 * "voce principale del menù" => ("funzione1" => true), ("funzione2" => false)
 * significa che la voce di menù (o "main_classe" è attiva e all'interno la funzionalità "funzione1" è disponibile, la "funzione2" no
 * Qualcosa del genere, questa è l'idea base, tutta da sviluppare
 */
$moduli_attivi = array(
		
);
$config["cartella_utenti"] = "users_folders";
$config["azienda"] = "Da.S.Co.S.";
$config["piva"] = "IT01296270091";
$config["logo"] = "no_image-200x100.jpg";
$config["site_name"] = "DaSCoS_Framework";
$config["framework_configured"] = false;
?>