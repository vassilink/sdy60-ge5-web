<?php 
//Δημιουργία συνάρτησης για να συνδεθούμε στην βάση που θέλουμε
function connect() {
	static $mysqli;
	
	//require_once 'include/config.php';
	require_once 'config.php';
	
	// connecting to mysql
	if (empty($mysqli)) {
		$mysqli = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
	}
	
	mysqli_set_charset($mysqli, 'utf8mb4') OR exit('Cannot set Connection Character Set');
	
	// επιστρέφει τον χειριστή της βάσης
	return $mysqli;
}
