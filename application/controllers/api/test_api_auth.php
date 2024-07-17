<?php
// URL dell'endpoint API
$url = 'http://framework.local/api/api/get_auth';

// Parametri POST
$postFields = [
		'code' => 'ZOcFt5oMrvqY',
		'user_id' => 1,
];

// Inizializza una sessione CURL
$ch = curl_init($url);

// Imposta le opzioni CURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

// Esegui la richiesta CURL
$response = curl_exec($ch);

// Controlla se ci sono errori
if (curl_errno($ch)) {
	echo 'Errore CURL: ' . curl_error($ch);
} else {
	// Decodifica la risposta JSON
	$responseData = json_decode($response, true);
	echo 'Risposta: ';
	print_r($responseData);
}

// Chiudi la sessione CURL
curl_close($ch);
?>