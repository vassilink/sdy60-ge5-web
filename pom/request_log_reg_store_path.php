<?php
/**
 * File to handle API requests
 * Accepts GET and POST
 * 
 * Each request will be identified by TAG
 * Response will be JSON data
 */

// εισάγει τις συναρτήσεις χειρισμού της βάσης
require_once 'include/DB_Functions.php';

//Εάν υπάρχει ετικέτα (tag) - διάφορη του κενού που δείχνει τι λειτουργία θα πρέπει να ακολουθηθεί
if (isset($_POST['tag']) && $_POST['tag'] != '') {
	// Παίρνει την ετικέτα (tag)
	$tag = $_POST['tag'];
	
	// response Array - το tag παίρνει την τιμή της αίτησης - στην αρχή δεν έχουμε ούτε επιτυχία, ούτε λάθος
	$response = array("tag" => $tag, "success" => 0, "error" => 0);
	
	// VK - Start
	$mysqli = connect();
	$result = mysqli_query($mysqli, 'INSERT INTO logs(log_data, inserted_at) VALUES("tag = '.$tag.'", NOW())');
	// VK - End
	
	// ελέγχει για τον τύπο του tag
	if ($tag == 'log_reg') { // Login or Register
		$name = $_POST['name'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		
		// ελέγχει αν ο παίκτης υπάρχει ήδη
		if (isPlayerExisted($email)) {
			//Login
			$player = getPlayerByEmailAndPassword($email, $password);
			
			if ($player != false) {
				// ο παίκτης βρέθηκε
				// echo json με success = 1
				$response["success"] = 1;
				$response["uid"] = $player["uid"];
				$response["player"]["name"] = $player["name"];
				$response["player"]["email"] = $player["email"];
				$response["player"]["created_at"] = $player["created_at"];
				$response["player"]["updated_at"] = $player["updated_at"];
				$response["player"]["path_set"] = $player["path_set"];
			} else {
				// ο παίκτης δεν βρέθηκε
				// echo json με error = 1
				$response["error"] = 1;
				$response["error_msg"] = "Incorrect password!";
			}
		} else {
			//Register
			// εγγραφή παίκτη
			$player = storePlayer($name, $email, $password);
			
			if ($player) {
				// επιτυχής εγγραφή χρήστη
				$response["success"] = 2;
				$response["uid"] = $player["uid"];
				$response["player"]["name"] = $player["name"];
				$response["player"]["email"] = $player["email"];
				$response["player"]["created_at"] = $player["created_at"];
				$response["player"]["updated_at"] = $player["updated_at"];
				$response["player"]["path_set"] = $player["path_set"];
			} else {
				// ο παίκτης απέτυχε να εγγραφεί
				$response["error"] = 2;
				$response["error_msg"] = "Error occurred in Registration";
			}
		}
		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
	} else if ($tag == 'storageFile') {
		// εισάγει τις συναρτήσεις απόρριψης των trkpt
		
		//Βλέπει το uid του τελευταίου path μέχρι τώρα
		$uidOfLastPathUntilNow = uidOfLatsPath(0);
		
		//Ο αριθμός της διαδρομής που ανέβηκε τώρα
		$number_of_path = $uidOfLastPathUntilNow + 1;
		
		//--------------------------------------------------------------------------------------
		
		// VK - Start
		$mysqli = connect();
		$result = mysqli_query($mysqli, 'INSERT INTO logs(log_data, inserted_at) VALUES("file_uploaded_step_0", NOW())');
		// VK - End
		
		//Θα αποθηκεύσουμε το αρχείο που ανέβηκε από τον client (fused provider)
		$file = $_FILES['fileGoogle'];
		
		//Η σχετική διαδρομή που θα αποθηκευτούν τα αρχεία
		$file_path = 'uploads/';
		$temp = explode('.', $_FILES['fileGoogle']['name']); // Στο $temp2[0] θα περιέχεται το όνομα και στο $temp2[1] η επέκταση του αρχείου fused
		$extension = end($temp);
		$temp[0] = 'pathGoogle'.$number_of_path;
		$fileName = $temp[0].'.'.$temp[1]; // το όνομα του fused αρχείου θα είναι pathGoogle#
		
		//Η σχετική διαδρομή που θα αποθηκευτεί το αρχείο (μαζί με το όνομα του)
		$file_path = $file_path.$fileName;
		
		//Αποθήκευση του αρχείου fused στον φάκελο uploads
		move_uploaded_file($_FILES['fileGoogle']['tmp_name'], $file_path);
		
		// VK - Start
		$mysqli = connect();
		$result = mysqli_query($mysqli, 'INSERT INTO logs(log_data, inserted_at) VALUES("file_uploaded_step_1", NOW())');
		// VK - End
		
		$path_raw_gpx_google = $file_path;
		
		//--------------------------------------------------------------------------------------
		
		$player_id = $_POST['player_id'];
		$meters = $_POST['meters'];
		$tags = $_POST['tagsOfPath'];
		
		// Η απόρριψη και το smoothing θα γίνει στα fused αρχεία
		// Εδώ θα απορριπτούν κάποια σημεία -σύμφωνα με δύο συναρτήσεις- και θα δημιουργηθεί το προκύπτον αρχείο με όνομα: ονομα_mod
		require_once 'include/InacAndDPSmooth.php';
		
		$gpx = discardFaultPointsOfGpxFile($file_path, 47); // Αφού θέλουμε το fused αρχείο
		
		$file_name_without_extension = pathinfo($file_path, PATHINFO_FILENAME);
		
		$modified_file_name = "$file_name_without_extension"."_mod".".gpx";
		$path_smooth_gpx = 'uploads/'.$modified_file_name;
		
		DouglasPeuckerSmoothing($gpx, $path_smooth_gpx, 7, 12);
		
		$new_path = 0;
		
		//Αποθήκευση της διαδρομής στη βάση δεδομένων
		$path = storePath(0, $player_id, $path_raw_gpx_google, $path_smooth_gpx, $tags, $meters, $new_path);
		
		if ($path != false) {
			$response['success'] = 1;
			$response['message'] = 'Path uploaded successfully';
			
			// Για να μην περιμένει ο χρήστης όσο γίνεται η προσπάθεια snap αλλά και merge
			ignore_user_abort(true);
			set_time_limit(0);
			ob_start();
			
			// do initial processing here
			// echo $response; // send the response
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			header('Connection: close');
			header('Content-Length: '.ob_get_length());
			ob_end_flush();
			//ob_flush();
			//flush();
			
			//εισάγει την συνάρτηση για merge snapWaypoints
			require_once 'include/snapWpt.php';
			snapWayPoints(0, $path_smooth_gpx);
			
			// εισάγει την συνάρτηση για merge gpx
			require_once 'include/Merge_Gpx_Function.php';
			$mergeFileName = 'mergeFile/merge_gpx.gpx'; // Το gpx αρχείο που θα περιέχει όλες τις διαδρομές
			
			mergeGpxFiles($mergeFileName, $path_smooth_gpx); // Το καινούργιο smooth αρχείο θα μπει στο merge
			
			// Ενημερώνει τον πίνακα pathpoints με το αρχικό, το μεσσαίο και το τελευταίο σημείο του μονοπατιού
			$path_id = getLastPathId(0, $player_id, $path_smooth_gpx);
			
			if ($path_id > 0) {
				require_once 'extractPathPoints.php';
				$points = getLastPathPoints($path_smooth_gpx);
				
				if ($points != null) {
					storePathPoints(0, $path_id, $points[0], $points[1], $points[2], $points[3], $points[4], $points[5]);
				}
			}
			
			ob_flush();
			flush();
		} else {
			$response['error'] = 1;
			$response['error_msg'] = 'Oops! An error occurred.';
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
		}
	} else if ($tag == 'storageFileCycle') {
		// εισάγει τις συναρτήσεις απόρριψης των trkpt
		
		// Βλέπει το uid του τελευταίου path μέχρι τώρα
		$uidOfLastPathUntilNow = uidOfLatsPath(1);
		
		//--------------------------------------------------------------------------------------
		
		//Ο αριθμός της διαδρομής που ανέβηκε τώρα
		$number_of_path = $uidOfLastPathUntilNow + 1;
		
		//Θα αποθηκεύσουμε το αρχείο από τον fused provider (που ανέβηκε από τον client)
		$file = $_FILES['fileGoogle'];
		
		//Η σχετική διαδρομή που θα αποθηκευτούν τα αρχεία
		$file_path = 'uploads/';
		$temp = explode('.', $_FILES['fileGoogle']['name']); // Στο $temp[0] θα περιέχεται το όνομα και στο $temp[1] η επέκταση του αρχείου fused
		$extension = end($temp);
		$temp[0] = 'cycleGoogle'.$number_of_path;
		$fileName = $temp[0].'.'.$temp[1]; // το όνομα του fused αρχείου θα είναι pathGoogle#
		
		// Η σχετική διαδρομή που θα αποθηκευτεί το αρχείο (μαζί με το όνομα του)
		$file_path = $file_path.$fileName;
		
		// Αποθήκευση του αρχείου fused στον φάκελο uploads
		move_uploaded_file($_FILES['fileGoogle']['tmp_name'], $file_path);
		
		$path_raw_gpx_google = $file_path;
		
		//--------------------------------------------------------------------------------------
		
		$player_id = $_POST['player_id'];
		$meters = $_POST['meters'];
		$tags = $_POST['tagsOfPath'];
		
		// Η απόρριψη και το smoothing θα γίνει στα fused αρχεία
		// Εδώ θα απορριπτούν κάποια σημεία -σύμφωνα με δύο συναρτήσεις- και θα δημιουργηθεί το προκύπτον αρχείο με όνομα: ονομα_mod
		require_once 'include/InacAndDPSmooth.php';
		
		$gpx = discardFaultPointsOfGpxFile($file_path, 47); // Αφού θέλουμε το fused αρχείο
		
		$file_name_without_extension = pathinfo($file_path, PATHINFO_FILENAME);
		$modified_file_name = "$file_name_without_extension" . "_mod" . ".gpx";
		$path_smooth_gpx = 'uploads/'.$modified_file_name;
		
		DouglasPeuckerSmoothing($gpx, $path_smooth_gpx, 7, 12);
		
		$new_path = 0;
		
		// Αποθήκευση της διαδρομής στη βάση δεδομένων
		$path = storePath(1, $player_id, $path_raw_gpx_google, $path_smooth_gpx, $tags, $meters, $new_path);
		
		if ($path != false) {
			$response['success'] = 1;
			$response['message'] = 'Path uploaded successfully';
			
			// Για να μην περιμένει ο χρήστης όσο γίνεται η προσπάθεια snap αλλά και merge
			ignore_user_abort(true);
			set_time_limit(0);
			ob_start();
			// do initial processing here
			//echo $response; // send the response
			
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			header('Connection: close');
			header('Content-Length: '.ob_get_length());
			ob_end_flush();
			//ob_flush();
			//flush();
			
			//εισάγει την συνάρτηση για merge snapWaypoints
			require_once 'include/snapWpt.php';
			snapWayPoints(1, $path_smooth_gpx);
			
			// εισάγει την συνάρτηση για merge gpx
			require_once 'include/Merge_Gpx_Function.php';
			$mergeFileName = 'mergeFile/merge_gpx_cycle.gpx'; // Το gpx αρχείο που θα περιέχει όλες τις διαδρομές
			mergeGpxFiles($mergeFileName, $path_smooth_gpx); // Το καινούργιο smooth αρχείο θα μπει στο merge
			
			// Ενημερώνει τον πίνακα pathpoints με το αρχικό, το μεσαίο και το τελευταίο σημείο του μονοπατιού
			$path_id = getLastPathId(1, $player_id, $path_smooth_gpx);
			
			if ($path_id > 0) {
				require_once 'extractPathPoints.php';
				$points = getLastPathPoints($path_smooth_gpx);
				
				if ($points != null) {
					storePathPoints(1, $path_id, $points[0], $points[1], $points[2], $points[3], $points[4], $points[5]);
				}
			}
			
			ob_flush();
			flush();
		} else {
			$response['error'] = 1;
			$response['error_msg'] = 'Oops! An error occurred';
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
		}
	} else if ($tag == 'getPlayerBalance') {
		$player_id = $_POST['player_id'];
		$balance = getPlayerBalance($player_id);
		
		if ($balance != false) {
			$response['success'] = 1;
			$response['message'] = 'Player Balance';
			$response['balance'] = $balance;
		} else {
			$response['error'] = 1;
			$response['error_msg'] = 'No balance';
		}
		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
	/*
	} else if ($tag == 'setPlayerBalance') {
		$player_id = $_POST['player_id'];
		$balance = $_POST['balance'];
		$res = updatePlayerBalanceById($player_id, $balance);
		
		if ($res != false) {
			$response["success"] = 1;
		} else {
			$response["error"] = 1;
		}
		
		echo json_encode($response, JSON_UNESCAPED_UNICODE);
	*/
	} else if ($tag == 'getPathTypes') {
		$path_set = $_POST['path_set'];
		$response = getPathTypes($path_set);
		echo json_encode($response,JSON_UNESCAPED_UNICODE);
	} else {
		echo "Invalid Request";
	}
} else {
	// VK - Start
	$mysqli = connect();
	$result = mysqli_query($mysqli, 'INSERT INTO logs (log_data, inserted_at) VALUES ("tag is NOT POSTED or is EMPTY", NOW())');
	//VK - End
	
	echo 'Access Denied';
}
