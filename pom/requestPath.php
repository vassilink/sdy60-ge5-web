<?php


/**
 * Αρχείο που αποκρίνεται σε μια ερώτηση POST του client.
 * Γυρίζει στον client το μονοπάτι που βρίσκεται μια κατάλληλη διαδρομή στον server για να το σχολιάσει ο χρήστης
 */


//Εάν υπάρχει ετικέτα (tag) - διάφορη του κενού που δείχνει τι λειτουργία θα πρέπει να ακολουθηθεί
if (isset($_POST['tag']) && $_POST['tag'] != '') {
    // Παίρνει την ετικέτα (tag)
    $tag = $_POST['tag'];

    // εισάγει τις συναρτήσεις χειρισμού της βάσης
	require_once 'include/DB_Functions.php';
	
    // response Array - το tag παίρνει την τιμή της αίτησης - στην αρχή δεν έχουμε ούτε επιτυχία, ούτε λάθος
    $response = array("tag" => $tag, "success" => 0, "error" => 0);
	
	// ελέγχει για τον τύπο του tag
    if ($tag == 'pathRequestRadius') {

        $player_id = $_POST['playerID'];
        $player_lat = $_POST['lat'];
        $player_lon = $_POST['long'];
		$rad = $_POST['rad'];

        $response = radiusPaths($player_id, $player_lat, $player_lon, $rad);
        echo json_encode($response,JSON_UNESCAPED_UNICODE);

	}
	else if ($tag == 'pathCycleRequestRadius') {

		$player_id = $_POST['playerID'];
		$player_lat = $_POST['lat'];
		$player_lon = $_POST['long'];
		$rad = $_POST['rad'];

		$response = radiusCyclePaths($player_id, $player_lat, $player_lon, $rad);
		echo json_encode($response,JSON_UNESCAPED_UNICODE);
		
	}
	else {
        	echo "Invalid Request";
    }
} else {
    echo "Access Denied";
}

?>
