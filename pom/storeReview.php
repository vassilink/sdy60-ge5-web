<?php

/**
 * Αρχείο που αποκρίνεται σε μια ερώτηση POST του client για την αποθήκευση μιας κριτικής ή μιας σχεδίασης στη βάση δεδομένων
 * Γυρίζει αν έγινε η αποθήκευση μέσω μιας απάντησης JSON
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
    if ($tag == 'storeReview' || $tag == 'storeCycleReview') {
        // Ο τύπος αιτήματος είναι εγγραφή νέας κριτικής
        $player_id = $_POST['player_id'];
        $path_id = $_POST['path_id'];
        $rated = $_POST['rated'];
        $rated_tags = $_POST['rated_tags'];
        $new_path = $_POST['new_path'];
        if($new_path >0) $new_path--; //0:do not update   1: No   2: Yes
        
        // εγγραφή κριτικής
        if ($tag == 'storeReview') {
            $review = storeReview(0, $player_id, $path_id, $rated, $rated_tags);
        } else {
            $review = storeReview(1, $player_id, $path_id, $rated, $rated_tags);
        }

        if($review == 0 || $review==4) {

            $points = 0;

            $rated = $_POST['rated'];
            //Check and Update balance of player (player who has update the reviewed path)
            switch($rated){
                case 1: $points -= 30; break;
                case 2: $points -= 15; break;
                case 3: $points = 0;   break;
                case 4: $points += 30; break;
                case 5: $points += 60; break;
                default:$points = 0;   break;
            }

            $rated_tags = $_POST['rated_tags'];
            switch($rated_tags){
                case 1: $points -= 30; break;
                case 2: $points -= 15; break;
                case 3: $points += 0;  break;
                case 4: $points += 40; break;
                case 5: $points += 80; break;
                default:$points += 0;  break;
            }

            if($points>0 || $points<0 || $new_path>0) {
                $path_id = $_POST['path_id'];
                $player_id = $_POST['player_id'];
                if ($tag == 'storeReview') {
                    updatePlayerBalanceByPath(0, $path_id, $points, $new_path);
                }else{
                    updatePlayerBalanceByPath(1, $path_id, $points, $new_path);
                }
            }

            if ($review == 0) {
                $response["success"] = 1;
                $response["message"] = "Review stored successfully. You gained 180 points."; //First Review
            }else{
                $response["success"] = 2;
                $response["message"] = "Review stored successfully. You gained 150 points."; //Review from other players also
            }
            echo json_encode($response, JSON_UNESCAPED_UNICODE);

        } else if($review==2) {
            $response["error"] = 1;
            $response["error_msg"] = "Oops! You are not allowed to review paths uploaded from you.";
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        } else if($review==3) {
            $response["error"] = 1;
            $response["error_msg"] = "Oops! This path has already reviewed from you.";
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response["error"] = 1;
            $response["error_msg"] = "Oops! An error occurred.";
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }

    }else if ($tag == 'storeSugPathReview' || $tag == 'storeSugPathCycleReview') {

        // Ο τύπος αιτήματος είναι εγγραφή νέας κριτικής
        $player_id = $_POST['player_id'];
        $path_id = $_POST['path_id']; //uid of pending path
        $rated = $_POST['rated'];
        $rated_tags = $_POST['rated_tags'];

        // εγγραφή κριτικής
        if ($tag == 'storeSugPathReview') {

            $review = storeSugReview(0, $path_id, $player_id, $rated, $rated_tags);
        } else {
            $review = storeSugReview(1, $path_id, $player_id, $rated, $rated_tags);
        }

        if($review == 0 || $review==4) {

            $points = 0;

            $rated = $_POST['rated'];
            //Check and Update balance of player (player who has update the reviewed path)
            switch($rated){
                case 1: $points = -30; break;
                case 2: $points = -15; break;
                case 3: $points = 10;  break;
                case 4: $points = 40;  break;
                case 5: $points = 80;  break;
                default:$points = 0;   break;
            }
            /*
            $rated_tags = $_POST['rated_tags'];
            switch($rated_tags){
                case 1: $points -= 30; break;
                case 2: $points -= 15; break;
                case 3: $points += 0;  break;
                case 4: $points += 40; break;
                case 5: $points += 80; break;
                default:$points += 0;  break;
            }
            */

            if($points>0 || $points<0) {
                $path_id = $_POST['path_id'];
                $player_id = $_POST['player_id'];
                if ($tag == 'storeSugPathReview') {
                    updatePlayerBalanceSugByPath(0, $path_id, $points);
                }else{
                    updatePlayerBalanceSugByPath(1, $path_id, $points);
                }
            }

            if($review == 0) {
                $response["success"] = 1;
                $response["message"] = "Review stored successfully. You gained 180 points."; //First Review
            }else{
                $response["success"] = 2;
                $response["message"] = "Review stored successfully. You gained 150 points."; //Review from other players also
            }
            echo json_encode($response, JSON_UNESCAPED_UNICODE);

        } else if($review==2) {
            $response["error"] = 1;
            $response["error_msg"] = "Oops! You are not allowed to review paths uploaded from you.";
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            $response["error"] = 2;
            $response["error_msg"] = "Oops! An error occurred.";
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
	}else if($tag=='storeSuggestedPath' || $tag=='storeSugCyclePath') {

        $path_id = $_POST['path_id'];
        $player_id = $_POST['player_id'];
        $s_lat = $_POST['s_lat'];
        $s_long = $_POST['s_long'];
        $e_lat = $_POST['e_lat'];
        $e_long = $_POST['e_long'];
        $pending = $_POST['pending'];
        $rate = 3;//Threshold of new suggested path
        $hdop = $_POST['hdop'];


        $time = date("Y-m-d H:i:s");//NOW(); //Η NOW () επιστρέφει την τρέχουσα ημερομηνία και ώρα.
        if ($tag == 'storeSuggestedPath') {

            $res = storeSuggestedPath(0, $path_id, $player_id, $s_lat, $s_long, $e_lat, $e_long, $time, $pending, $rate);

            // εισάγει την συνάρτηση για merge gpx
            require_once 'include/Merge_Gpx_Function.php';
            $mergeFileName = 'mergeFile/merge_gpx_p_pend.gpx'; //Το gpx αρχείο που θα περιέχει όλες τις διαδρομές
            mergeGpxPendFiles($mergeFileName, $s_lat, $s_long, $e_lat, $e_long, $time, $hdop, $path_id);//Το καινούργιο smooth αρχείο θα μπει στο merge
        } else {
            $res = storeSuggestedPath(1, $path_id, $player_id, $s_lat, $s_long, $e_lat, $e_long, $time, $pending, $rate);

            // εισάγει την συνάρτηση για merge gpx
            require_once 'include/Merge_Gpx_Function.php';
            $mergeFileName = 'mergeFile/merge_gpx_c_pend.gpx'; //Το gpx αρχείο που θα περιέχει όλες τις διαδρομές
            mergeGpxPendFiles($mergeFileName, $s_lat, $s_long, $e_lat, $e_long, $time, $hdop, $path_id);//Το καινούργιο smooth αρχείο θα μπει στο merge
        }

        if ($res == 1){
            $response["success"] = 1;
            $response["message"] = "Suggested Path uploaded successfully. You gained 250 points."; //First player who sketch for this path
        }else if($res == 2){
            $response["success"] = 2;
            $response["message"] = "Suggested Path uploaded successfully. You gained 200 points.";  //Already sketched path
		}else{
			$response["error"] = 1;
			$response["error_msg"] = "Oops! An error occurred.";
		}
		echo json_encode($response,JSON_UNESCAPED_UNICODE);
	}
	else {
        echo "Invalid Request";
    }
} else {
    echo "Access Denied";
}

?>